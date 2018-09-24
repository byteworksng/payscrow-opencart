<?php

class ControllerExtensionPaymentPayscrow extends Controller
{
    private $error = [];

    public function index()
    {
        $this->load->language('extension/payment/payscrow');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('payscrow', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('extension/extension', 'token='.$this->session->data['token'].'&type=payment', true));
        }

        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_live'] = $this->language->get('text_live');
        $data['text_demo'] = $this->language->get('text_demo');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');
        $data['text_all_zones'] = $this->language->get('text_all_zones');

        $data['text_card_type'] = $this->language->get('text_card_type');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_notification_url'] = $this->language->get('text_notification_url');
        $data['text_merchant_id'] = $this->language->get('text_merchant_id');
        $data['text_delivery_duration'] = $this->language->get('text_delivery_duration');
        $data['text_settle_delayed'] = $this->language->get('text_settle_delayed');
        $data['text_settle_auto'] = $this->language->get('text_settle_auto');

        $data['entry_merchant_id'] = $this->language->get('entry_merchant_id');
        $data['entry_order_prefix'] = $this->language->get('entry_order_prefix');
        $data['entry_delivery_duration'] = $this->language->get('entry_delivery_duration');
        $data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_debug'] = $this->language->get('entry_debug');
        $data['entry_live_demo'] = $this->language->get('entry_live_demo');
        $data['entry_auto_settle'] = $this->language->get('entry_auto_settle');
        $data['entry_live_url'] = $this->language->get('entry_live_url');
        $data['entry_demo_url'] = $this->language->get('entry_demo_url');
        $data['entry_total'] = $this->language->get('entry_total');
        $data['entry_sort_order'] = $this->language->get('entry_sort_order');

        $data['entry_status_success_settled'] = $this->language->get('entry_status_success_settled');
        $data['entry_status_success_unsettled'] = $this->language->get('entry_status_success_unsettled');
        $data['entry_status_decline'] = $this->language->get('entry_status_decline');
        $data['entry_status_decline_pending'] = $this->language->get('entry_status_decline_pending');
        $data['entry_status_decline_stolen'] = $this->language->get('entry_status_decline_stolen');
        $data['entry_status_decline_bank'] = $this->language->get('entry_status_decline_bank');
        $data['entry_status_void'] = $this->language->get('entry_status_void');

        $data['help_total'] = $this->language->get('help_total');
        $data['help_debug'] = $this->language->get('help_debug');
        $data['help_notification'] = $this->language->get('help_notification');
        $data['help_settle'] = $this->language->get('help_settle');
        $data['help_order_prefix'] = $this->language->get('help_order_prefix');

        $data['tab_account'] = $this->language->get('tab_account');
        $data['tab_order_status'] = $this->language->get('tab_order_status');
        $data['tab_payment'] = $this->language->get('tab_payment');
        $data['tab_advanced'] = $this->language->get('tab_advanced');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');



        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $this->load->model('localisation/geo_zone');

        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['error_merchant_id'])) {
            $data['error_merchant_id'] = $this->error['error_merchant_id'];
        } else {
            $data['error_merchant_id'] = '';
        }

        if (isset($this->error['error_order_prefix'])) {
            $data['error_order_prefix'] = $this->error['error_order_prefix'];
        } else {
            $data['error_order_prefix'] = '';
        }

        if (isset($this->error['error_delivery_duration'])) {
            $data['error_delivery_duration'] = $this->error['error_delivery_duration'];
        } else {
            $data['error_delivery_duration'] = '';
        }

        if (isset($this->error['error_live_url'])) {
            $data['error_live_url'] = $this->error['error_live_url'];
        } else {
            $data['error_live_url'] = '';
        }

        if (isset($this->error['error_demo_url'])) {
            $data['error_demo_url'] = $this->error['error_demo_url'];
        } else {
            $data['error_demo_url'] = '';
        }
        if (isset($this->error['error_notify_url'])) {
            $data['error_notify_url'] = $this->error['error_notify_url'];
        } else {
            $data['error_notify_url'] = '';
        }

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token='.$this->session->data['token'], true),
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('extension/extension', 'token='.$this->session->data['token'].'&type=payment', true),
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/payscrow', 'token='.$this->session->data['token'], true),
        ];

        $data['action'] = $this->url->link('extension/payment/payscrow', 'token='.$this->session->data['token'], true);

        $data['cancel'] = $this->url->link('extension/extension', 'token='.$this->session->data['token'].'&type=payment', true);

        if (isset($this->request->post['payscrow_merchant_id'])) {
            $data['payscrow_merchant_id'] = $this->request->post['payscrow_merchant_id'];
        } else {
            $data['payscrow_merchant_id'] = $this->config->get('payscrow_merchant_id');
        }
        if (isset($this->request->post['payscrow_delivery_duration'])) {
            $data['payscrow_delivery_duration'] = $this->request->post['payscrow_delivery_duration'];
        } else {
            $data['payscrow_delivery_duration'] = $this->config->get('payscrow_delivery_duration');
        }

        if (empty($data['payscrow_delivery_duration'])) {
            $data['payscrow_delivery_duration'] = '7';
        }

        if (isset($this->request->post['payscrow_live_demo'])) {
            $data['payscrow_live_demo'] = $this->request->post['payscrow_live_demo'];
        } else {
            $data['payscrow_live_demo'] = $this->config->get('payscrow_live_demo');
        }

        if (isset($this->request->post['payscrow_geo_zone_id'])) {
            $data['payscrow_geo_zone_id'] = $this->request->post['payscrow_geo_zone_id'];
        } else {
            $data['payscrow_geo_zone_id'] = $this->config->get('payscrow_geo_zone_id');
        }

        if (isset($this->request->post['payscrow_sort_order'])) {
            $data['payscrow_sort_order'] = $this->request->post['payscrow_sort_order'];
        } else {
            $data['payscrow_sort_order'] = $this->config->get('payscrow_sort_order');
        }

        if (isset($this->request->post['payscrow_status'])) {
            $data['payscrow_status'] = $this->request->post['payscrow_status'];
        } else {
            $data['payscrow_status'] = $this->config->get('payscrow_status');
        }

        if (isset($this->request->post['payscrow_debug'])) {
            $data['payscrow_debug'] = $this->request->post['payscrow_debug'];
        } else {
            $data['payscrow_debug'] = $this->config->get('payscrow_debug');
        }

        if (isset($this->request->post['payscrow_order_status_success_settled_id'])) {
            $data['payscrow_order_status_success_settled_id'] = $this->request->post['payscrow_order_status_success_settled_id'];
        } else {
            $data['payscrow_order_status_success_settled_id'] = $this->config->get('payscrow_order_status_success_settled_id');
        }
        if (empty($data['payscrow_order_status_success_settled_id'])) {
            $data['payscrow_order_status_success_settled_id'] = 11;
        }

        if (isset($this->request->post['payscrow_order_status_success_unsettled_id'])) {
            $data['payscrow_order_status_success_unsettled_id'] = $this->request->post['payscrow_order_status_success_unsettled_id'];
        } else {
            $data['payscrow_order_status_success_unsettled_id'] = $this->config->get('payscrow_order_status_success_unsettled_id');
        }
        if (empty($data['payscrow_order_status_success_unsettled_id'])) {
            $data['payscrow_order_status_success_unsettled_id'] = 12;
        }

        if (isset($this->request->post['payscrow_order_status_decline_id'])) {
            $data['payscrow_order_status_decline_id'] = $this->request->post['payscrow_order_status_decline_id'];
        } else {
            $data['payscrow_order_status_decline_id'] = $this->config->get('payscrow_order_status_decline_id');
        }

        if (empty($data['payscrow_order_status_decline_id'])) {
            $data['payscrow_order_status_decline_id'] = 7;
        }

        if (isset($this->request->post['payscrow_order_status_void_id'])) {
            $data['payscrow_order_status_void_id'] = $this->request->post['payscrow_order_status_void_id'];
        } else {
            $data['payscrow_order_status_void_id'] = $this->config->get('payscrow_order_status_void_id');
        }
        if (empty($data['payscrow_order_status_void_id'])) {
            $data['payscrow_order_status_void_id'] = 7;
        }


        if (isset($this->request->post['payscrow_live_url'])) {
            $data['payscrow_live_url'] = $this->request->post['payscrow_live_url'];
        } else {
            $data['payscrow_live_url'] = $this->config->get('payscrow_live_url');
        }

        if (empty($data['payscrow_live_url'])) {
            $data['payscrow_live_url'] = 'https://www.payscrow.net/customer/transactions/start';
        }

        if (isset($this->request->post['payscrow_demo_url'])) {
            $data['payscrow_demo_url'] = $this->request->post['payscrow_demo_url'];
        } else {
            $data['payscrow_demo_url'] = $this->config->get('payscrow_demo_url');
        }

        if (empty($data['payscrow_demo_url'])) {
            $data['payscrow_demo_url'] = 'http://ps.ofekpc.com/customer/transactions/start';
        }

        if (isset($this->request->post['payscrow_notify_url'])) {
            $data['payscrow_notify_url'] = $this->request->post['payscrow_notify_url'];
        } else {
            $data['payscrow_notify_url'] = $this->config->get('payscrow_notify_url');
        }

        if (empty($data['payscrow_notify_url'])) {
            $data['payscrow_notify_url'] = 'extension/payment/payscrow/notify';;
        }

        if (isset($this->request->post['payscrow_order_prefix'])) {
            $data['payscrow_order_prefix'] = $this->request->post['payscrow_order_prefix'];
        } else {
            $data['payscrow_order_prefix'] = $this->config->get('payscrow_order_prefix');
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/payscrow', $data));
    }

    public function install()
    {
        $this->load->model('extension/payment/payscrow');
        $this->model_extension_payment_payscrow->install();
    }

    public function uninstall()
    {
        $this->load->model('extension/payment/payscrow');
        $this->model_extension_payment_payscrow->uninstall();
    }

    public function order()
    {
        if ($this->config->get('payscrow_status')) {
            $this->load->model('extension/payment/payscrow');

            $payscrow_order = $this->model_extension_payment_payscrow->getOrder($this->request->get['order_id']);

            if (! empty($payscrow_order)) {
                $this->load->language('extension/payment/payscrow');

                $payscrow_order['total_captured'] = $this->model_extension_payment_payscrow->getTotalCaptured($payscrow_order['payscrow_order_id']);
                $payscrow_order['total_formatted'] = $this->currency->format($payscrow_order['total'], $payscrow_order['currency_code'], 1, true);
                $payscrow_order['total_captured_formatted'] = $this->currency->format($payscrow_order['total_captured'], $payscrow_order['currency_code'], 1, true);

                $data['payscrow_order'] = $payscrow_order;
                $data['merchant_id'] = $this->config->get('payscrow_merchant_id');
                $data['currency'] = $this->model_extension_payment_payscrow->mapCurrency($payscrow_order['currency_code']);
                $data['amount'] = number_format($payscrow_order['total'], 2);

                $data['request_timestamp'] = date("Y:m:d-H:i:s");

                $data['hash'] = sha1(bin2hex($data['merchant_id'].$data['request_timestamp'].$data['amount'].$data['currency']));

                $data['void_url'] = $this->url->link('extension/payment/payscrow/void', 'token='.$this->session->data['token'], true);
                $data['capture_url'] = $this->url->link('extension/payment/payscrow/capture', 'token='.$this->session->data['token'], true);

                if ($this->config->get('payscrow_notify_url')){
                    $data['notify_url'] = $this->config->get('payscrow_notify_url');
                }else {
                    $data['notify_url'] = HTTPS_CATALOG.'index.php?route=extension/payment/payscrow/notify';
                }

                if ($this->config->get('payscrow_live_demo') == 1) {
                    $data['action_url'] = $this->config->get('payscrow_live_url');
                } else {
                    $data['action_url'] = $this->config->get('payscrow_demo_url');
                }

                if (isset($this->session->data['void_success'])) {
                    $data['void_success'] = $this->session->data['void_success'];

                    unset($this->session->data['void_success']);
                } else {
                    $data['void_success'] = '';
                }

                if (isset($this->session->data['void_error'])) {
                    $data['void_error'] = $this->session->data['void_error'];

                    unset($this->session->data['void_error']);
                } else {
                    $data['void_error'] = '';
                }

                if (isset($this->session->data['capture_success'])) {
                    $data['capture_success'] = $this->session->data['capture_success'];

                    unset($this->session->data['capture_success']);
                } else {
                    $data['capture_success'] = '';
                }

                if (isset($this->session->data['capture_error'])) {
                    $data['capture_error'] = $this->session->data['capture_error'];

                    unset($this->session->data['capture_error']);
                } else {
                    $data['capture_error'] = '';
                }

                $data['text_payment_info'] = $this->language->get('text_payment_info');
                $data['text_order_ref'] = $this->language->get('text_order_ref');
                $data['text_order_total'] = $this->language->get('text_order_total');
                $data['text_total_captured'] = $this->language->get('text_total_captured');
                $data['text_capture_status'] = $this->language->get('text_capture_status');
                $data['text_void_status'] = $this->language->get('text_void_status');
                $data['text_transactions'] = $this->language->get('text_transactions');
                $data['text_yes'] = $this->language->get('text_yes');
                $data['text_no'] = $this->language->get('text_no');
                $data['text_column_amount'] = $this->language->get('text_column_amount');
                $data['text_column_type'] = $this->language->get('text_column_type');
                $data['text_column_date_added'] = $this->language->get('text_column_date_added');
                $data['button_capture'] = $this->language->get('button_capture');
                $data['button_void'] = $this->language->get('button_void');
                $data['text_confirm_void'] = $this->language->get('text_confirm_void');
                $data['text_confirm_capture'] = $this->language->get('text_confirm_capture');

                $data['order_id'] = $this->request->get['order_id'];
                $data['token'] = $this->request->get['token'];

                return $this->load->view('extension/payment/payscrow_order', $data);
            }
        }
    }

    public function void()
    {
        $this->load->language('extension/payment/payscrow');

        if ($this->request->post['status'] == 'FAILED') {
            if (isset($this->request->post['fail_reason'])) {
                $this->session->data['void_error'] = $this->request->post['fail_reason'];
            } else {
                $this->session->data['void_error'] = $this->language->get('error_void_error');
            }
        }

        if ($this->request->post['status'] == 'DECLINED') {
            $this->session->data['void_success'] = $this->language->get('success_void');
        }

        $this->response->redirect($this->url->link('sale/order/info', 'order_id='.$this->request->post['order_id'].'&token='.$this->session->data['token'], true));
    }

    public function capture()
    {
        $this->load->language('extension/payment/payscrow');

        if ($this->request->post['status'] == 'FAILED') {
            if (isset($this->request->post['fail_reason'])) {
                $this->session->data['capture_error'] = $this->request->post['fail_reason'];
            } else {
                $this->session->data['capture_error'] = $this->language->get('error_capture_error');
            }
        }

        if ($this->request->post['status'] == 'APPROVED') {
            $this->session->data['capture_success'] = $this->language->get('success_capture');
        }

        $this->response->redirect($this->url->link('sale/order/info', 'order_id='.$this->request->post['order_id'].'&token='.$this->session->data['token'], true));
    }

    protected function validate()
    {
        if (! $this->user->hasPermission('modify', 'extension/payment/payscrow')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (! $this->request->post['payscrow_merchant_id']) {
            $this->error['error_merchant_id'] = $this->language->get('error_merchant_id');
        }

        if (! $this->request->post['payscrow_live_url']) {
            $this->error['error_live_url'] = $this->language->get('error_live_url');
        }

        if (! $this->request->post['payscrow_demo_url']) {
            $this->error['error_demo_url'] = $this->language->get('error_demo_url');
        }

        if (! $this->request->post['payscrow_notify_url']) {
            $this->error['error_notify_url'] = $this->language->get('error_notify_url');
        }

        return ! $this->error;
    }
}