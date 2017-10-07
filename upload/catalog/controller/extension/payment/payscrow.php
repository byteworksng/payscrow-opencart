<?php

class ControllerExtensionPaymentPayscrow extends Controller
{
    public function index()
    {
        $this->load->language('extension/payment/payscrow');

        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['text_new_card'] = $this->language->get('text_new_card');

        $this->load->model('checkout/order');
        $this->load->model('extension/payment/payscrow');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        if ($this->config->get('payscrow_live_demo') == 1) {
            $data['action'] = $this->config->get('payscrow_live_url');
        } else {
            $data['action'] = $this->config->get('payscrow_demo_url');
        }
        $payscrowModel = $this->model_extension_payment_payscrow;
        $shipping = $payscrowModel->getShippingAmount($this->session->data['order_id']);

        $data['isDownloadable'] = $payscrowModel->isDownloadable();

        $data['amount'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
        $data['shipping'] = $this->currency->format($shipping['value'], $order_info['currency_code'], $order_info['currency_value'], false);
        $data['currency'] = $this->model_extension_payment_payscrow->mapCurrency($order_info['currency_code']);
        $data['merchant_id'] = $this->config->get('payscrow_merchant_id');
        $data['delivery_duration'] = $this->config->get('payscrow_delivery_duration');
        $data['timestamp'] = date('Y:m:d-H:i:s');
        $data['order_id'] = $this->config->get('payscrow_order_prefix').$this->session->data['order_id'].'T'.$data['timestamp'].mt_rand(1, 999);

        $data['url_notify'] = $this->url->link('extension/payment/payscrow/notify', '', true);
        $data['product_detail'] = $order_info['product_detail'];

        if (preg_match("/Mobile|Android|BlackBerry|iPhone|Windows Phone/", $this->request->server['HTTP_USER_AGENT'])) {
            $data['mobile'] = true;
        } else {
            $data['mobile'] = false;
        }

        if ($this->config->get('payscrow_auto_settle') == 1) {
            $data['txntype'] = 'sale';
        } else {
            $data['txntype'] = 'preauth';
        }

        $tmp = $data['merchant_id'].$data['timestamp'].$data['amount'].$data['currency'].$this->config->get('payscrow_secret');
        $ascii = bin2hex($tmp);
        $data['hash'] = sha1($ascii);

        $data['version'] = 'OPENCART-C-'.VERSION;

        $data['bcompany'] = $order_info['payment_company'];
        $data['bname'] = $order_info['payment_firstname'].' '.$order_info['payment_lastname'];
        $data['baddr1'] = substr($order_info['payment_address_1'], 0, 30);
        $data['baddr2'] = substr($order_info['payment_address_2'], 0, 30);
        $data['bcity'] = substr($order_info['payment_city'], 0, 30);
        $data['bstate'] = substr($order_info['payment_zone'], 0, 30);
        $data['bcountry'] = $order_info['payment_iso_code_2'];
        $data['bzip'] = $order_info['payment_postcode'];
        $data['email'] = $order_info['email'];

        if ($this->cart->hasShipping()) {
            $data['sname'] = $order_info['shipping_firstname'].' '.$order_info['shipping_lastname'];
            $data['saddr1'] = substr($order_info['shipping_address_1'], 0, 30);
            $data['saddr2'] = substr($order_info['shipping_address_2'], 0, 30);
            $data['scity'] = substr($order_info['shipping_city'], 0, 30);
            $data['sstate'] = substr($order_info['shipping_zone'], 0, 30);
            $data['scountry'] = $order_info['shipping_iso_code_2'];
            $data['szip'] = $order_info['shipping_postcode'];
        } else {
            $data['sname'] = $order_info['payment_firstname'].' '.$order_info['payment_lastname'];
            $data['saddr1'] = substr($order_info['payment_address_1'], 0, 30);
            $data['saddr2'] = substr($order_info['payment_address_2'], 0, 30);
            $data['scity'] = substr($order_info['payment_city'], 0, 30);
            $data['sstate'] = substr($order_info['payment_zone'], 0, 30);
            $data['scountry'] = $order_info['payment_iso_code_2'];
            $data['szip'] = $order_info['payment_postcode'];
        }

        return $this->load->view('extension/payment/payscrow', $data);
    }

    public function notify()
    {
        $this->load->model('extension/payment/payscrow');

        $this->load->model('checkout/order');

        $this->load->language('extension/payment/payscrow');

        $message = '';

        if ($this->config->get('payscrow_debug') == 1) {
            $this->model_extension_payment_payscrow->logger(print_r($this->request->post, 1));
        }

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $response = file_get_contents('php://input');
            $params = json_decode($response, true);

            $orderId = isset($params['ref']) ? $params['ref'] : null; // Generally sent by gateway
            //                        lets validate the response is from payscrow
            if (isset($params['transactionId'])) {
                $gatewayUrl = "https://www.payscrow.net/api/paymentconfirmation?transactionId={$params['transactionId']}";
                $result =  $this->verifyRequest($gatewayUrl);
            } else {
                $result = false;
            }

            if (isset($params['statusCode'])) {
                $statusDescription = "Payscrow confirmed this order as: {$params[ 'statusDescription' ]}";
                $this->load->language('extension/payment/payscrow');
                $this->load->language('extension/payment/payscrow');
                $order_id_parts = explode('T', $orderId);

                $order_id = str_replace($this->config->get('payscrow_order_prefix'), "", $order_id_parts[0]);
                $order_info = $this->model_checkout_order->getOrder($order_id);

                switch ($params['statusCode']) {
                    case '00':
                        if ($result && $result['statusCode'] == $params['statusCode']) {


                            $message .= $this->language->get('text_response_code_full').$params['statusDescription'].'<br />';
                            $message .= $this->language->get('text_response_proc_code').$params['statusCode'].'<br />';
                            $message .= $this->language->get('text_response_ref').$params['ref'].'<br />';

                            // todo: gateway doesn't send detailed transaction info. this would be enabled in the future
                            //$fd_order_id = $this->model_extension_payment_payscrow->addOrder($order_info, $this->request->post['oid'], $this->request->post['tdate']);
                            //
                            //$this->model_extension_payment_payscrow->addTransaction($fd_order_id, 'payment', $order_info);


                            $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('payscrow_order_status_success_settled_id'), $message, true);

                            if (isset($params['statusDescription']) && ! empty($params['statusDescription'])) {
                                $this->session->data['success'] = $statusDescription;
                            } else {
                                $this->session->data['success'] = $this->language->get('success');
                            }
                        } else {
                            $this->model_extension_payment_payscrow->logger('Data is missing from request . ');
                        }
                        break;
                    case '01':
                        $fd_order = $this->model_extension_payment_payscrow->getOrder($order_id);

                        $this->model_extension_payment_payscrow->updateCaptureStatus($order_id, 1);

                        $this->model_extension_payment_payscrow->addTransaction($fd_order['payscrow_order_id'], 'refund', $order_info);


                        $this->model_checkout_order->addOrderHistory($order_id, 11, $message, false);

                        break;
                    case '03':
                        if ($result && $result['statusCode'] == $params['statusCode']) {
                            $message = $params['statusDescription'].'<br />';
                            $message .= $this->language->get('text_response_code_full').$params['statusCode'];

                            $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('payscrow_order_status_decline_id'), $message);


                            if (isset($params['statusDescription']) && ! empty($params['statusDescription'])) {
                                $this->session->data['error'] = $statusDescription;
                            } else {
                                $this->session->data['error'] = $this->language->get('error_failed');
                            }
                        } else {
                            $this->model_extension_payment_payscrow->logger('Data is missing from request . ');
                        }
                        break;
                }
            }
        }

        if ($this->request->server['REQUEST_METHOD'] == 'GET') {
            $params = $this->request->get;
            switch ($params['statusCode']) {
                case '00':
                    $success_url = $this->url->link('checkout/success', '', true);
                    $this->response->redirect($success_url);
                    break;
                case '03':
                    $fail_url = $this->url->link('checkout/checkout', '', true);
                    $this->response->redirect($fail_url);

                    break;
                default:
                    break;
            }
        }
    }

    private function verifyRequest($gatewayUrl)
    {
        if (function_exists('curl_init')) {
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $gatewayUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => 1,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                ],
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.9) Gecko/20071025 Firefox/2.0.0.9',
            ]);
            $result = curl_exec($curl);
            if ($errno = curl_errno($curl)) {
                $error_message = curl_strerror($errno);

                return "cURL error ({$errno}):\n {$error_message}";
            }
            curl_close($curl);
        } else {
            $result = file_get_contents($gatewayUrl);
        }
        if ($result) {
            $result = json_decode($result, true);
        }

        return $result;
    }
}