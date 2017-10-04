<?php
class ModelExtensionPaymentPayscrow extends Model {
	public function getMethod($address, $total) {
		$this->load->language('extension/payment/payscrow');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('payscrow_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if ($this->config->get('payscrow_total') > 0 && $this->config->get('payscrow_total') > $total) {
			$status = false;
		} elseif (!$this->config->get('payscrow_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = array();

		if ($status) {
			$method_data = array(
				'code'       => 'payscrow',
				'title'      => $this->language->get('text_title'),
				'terms'      => '',
				'sort_order' => $this->config->get('payscrow_sort_order')
			);
		}

		return $method_data;
	}

	public function addOrder($order_info, $order_ref, $transaction_date) {
		if ($this->config->get('payscrow_auto_settle') == 1) {
			$settle_status = 1;
		} else {
			$settle_status = 0;
		}

		$this->db->query("INSERT INTO `" . DB_PREFIX . "payscrow_order` SET `order_id` = '" . (int)$order_info['order_id'] . "', `order_ref` = '" . $this->db->escape($order_ref) . "', `tdate` = '" . $this->db->escape($transaction_date) . "', `date_added` = now(), `date_modified` = now(), `capture_status` = '" . (int)$settle_status . "', `currency_code` = '" . $this->db->escape($order_info['currency_code']) . "', `total` = '" . $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false) . "'");

		return $this->db->getLastId();
	}

	public function getOrder($order_id) {
		$order = $this->db->query("SELECT * FROM `" . DB_PREFIX . "payscrow_order` WHERE `order_id` = '" . (int)$order_id . "' LIMIT 1");

		return $order->row;
	}

	public function addTransaction( $ps_order_id, $type, $order_info = array()) {
		if (!empty($order_info)) {
			$amount = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
		} else {
			$amount = 0.00;
		}

		$this->db->query("INSERT INTO `" . DB_PREFIX . "payscrow_order_transaction` SET `payscrow_order_id` = '" . (int)$ps_order_id . "', `date_added` = now(), `type` = '" . $this->db->escape( $type) . "', `amount` = '" . (float)$amount . "'");
	}

	public function addHistory($order_id, $order_status_id, $comment) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . (int)$order_id . "', order_status_id = '" . (int)$order_status_id . "', notify = '0', comment = '" . $this->db->escape($comment) . "', date_added = NOW()");
	}

	public function logger($message) {
		if ($this->config->get('payscrow_debug') == 1) {
			$log = new Log('payscrow.log');
			$log->write($message);
		}
	}

	public function mapCurrency($code) {
		$currency = array(
			'NGN' => 566
		);

		if (array_key_exists($code, $currency)) {
			return $currency[$code];
		} else {
			return false;
		}
	}


	public function responseHash($total, $currency, $txn_date, $approval_code) {
		$tmp = $total . $this->config->get('payscrow_secret') . $currency . $txn_date . $this->config->get('payscrow_merchant_id') . $approval_code;

		$ascii = bin2hex($tmp);

		return sha1($ascii);
	}

	public function updateVoidStatus($order_id, $status) {
		$this->db->query("UPDATE `" . DB_PREFIX . "payscrow_order` SET `void_status` = '" . (int)$status . "' WHERE `order_id` = '" . (int)$order_id . "'");
	}

	public function updateCaptureStatus($order_id, $status) {
		$this->db->query("UPDATE `" . DB_PREFIX . "payscrow_order` SET `capture_status` = '" . (int)$status . "' WHERE `order_id` = '" . (int)$order_id . "'");
	}
}