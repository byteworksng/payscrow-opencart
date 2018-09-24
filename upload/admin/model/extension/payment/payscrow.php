<?php
class ModelExtensionPaymentPayscrow extends Model {
	public function install() {
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "payscrow_order` (
			  `payscrow_order_id` INT(11) NOT NULL AUTO_INCREMENT,
			  `order_id` INT(11) NOT NULL,
			  `order_ref` CHAR(50) NOT NULL,
			  `order_ref_previous` CHAR(50) NOT NULL,
			  `pasref` VARCHAR(50) NOT NULL,
			  `pasref_previous` VARCHAR(50) NOT NULL,
			  `tdate` DATETIME NOT NULL,
			  `date_added` DATETIME NOT NULL,
			  `date_modified` DATETIME NOT NULL,
			  `capture_status` INT(1) DEFAULT NULL,
			  `void_status` INT(1) DEFAULT NULL,
			  `currency_code` CHAR(3) NOT NULL,
			  `authcode` VARCHAR(30) NOT NULL,
			  `account` VARCHAR(30) NOT NULL,
			  `total` DECIMAL( 10, 2 ) NOT NULL,
			  PRIMARY KEY (`payscrow_order_id`)
			) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");

		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "payscrow_order_transaction` (
			  `payscrow_order_transaction_id` INT(11) NOT NULL AUTO_INCREMENT,
			  `payscrow_order_id` INT(11) NOT NULL,
			  `date_added` DATETIME NOT NULL,
			  `type` ENUM('auth', 'payment', 'void') DEFAULT NULL,
			  `amount` DECIMAL( 10, 2 ) NOT NULL,
			  PRIMARY KEY (`payscrow_order_transaction_id`)
			) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");

	}

	public function uninstall() {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "payscrow_order`;");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "payscrow_order_transaction`;");
	}


    /**
     * This API implementation is subject to change and  reserved for future support by payment gateway API.
     * It should not be relied on.
     * @param $order_id
     * @return bool|SimpleXMLElement
     */
	public function void($order_id) {
	    // reserved for future purpose when supported by API
		$payscrow_order = $this->getOrder($order_id);

		if (!empty($payscrow_order)) {
			$timestamp = strftime("%Y%m%d%H%M%S");
			$merchant_id = $this->config->get('payscrow_merchant_id');

			$this->logger('Void hash construct: ' . $timestamp . ' . ' . $merchant_id . ' . ' . $payscrow_order['order_ref'] . ' . . . ');

			$tmp = $timestamp . ' . ' . $merchant_id . ' . ' . $payscrow_order['order_ref'] . ' . . . ';
			$hash = sha1($tmp);
			$tmp = $hash;
			$hash = sha1($tmp);

			$xml = '';
			$xml .= '<request type="void" timestamp="' . $timestamp . '">';
			$xml .= '<merchantid>' . $merchant_id . '</merchantid>';
			$xml .= '<account>' . $payscrow_order['account'] . '</account>';
			$xml .= '<orderid>' . $payscrow_order['order_ref'] . '</orderid>';
			$xml .= '<pasref>' . $payscrow_order['pasref'] . '</pasref>';
			$xml .= '<authcode>' . $payscrow_order['authcode'] . '</authcode>';
			$xml .= '<sha1hash>' . $hash . '</sha1hash>';
			$xml .= '</request>';

			$this->logger('Void XML request:\r\n' . print_r(simplexml_load_string($xml), 1));

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "https://www.payscrow.net/customer/transactions/start");
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, "OpenCart " . VERSION);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			$response = curl_exec ($ch);
			curl_close ($ch);

			return simplexml_load_string($response);
		} else {
			return false;
		}
	}

	public function updateVoidStatus($payscrow_order_id, $status) {
		$this->db->query("UPDATE `" . DB_PREFIX . "payscrow_order` SET `void_status` = '" . (int)$status . "' WHERE `payscrow_order_id` = '" . (int)$payscrow_order_id . "'");
	}

    /**
     * This API implementation is subject to change and reserved for future support by payment gateway API.
     * It should not be relied on.
     * @param $order_id
     * @param $amount
     * @return bool|SimpleXMLElement
     */
	public function capture($order_id, $amount) {
       // reserved for future purpose when supported by API
		$payscrow_order = $this->getOrder($order_id);

		if (!empty($payscrow_order) && $payscrow_order['capture_status'] == 0) {
			$timestamp = strftime("%Y%m%d%H%M%S");
			$merchant_id = $this->config->get('payscrow_merchant_id');


			if ($payscrow_order['settle_type'] == 2) {
				$this->logger('Capture hash construct: ' . $timestamp . ' . ' . $merchant_id . ' . ' . $payscrow_order['order_ref'] . ' . ' . (int)round($amount*100) . ' . ' . (string)$payscrow_order['currency_code'] . ' . ');

				$tmp = $timestamp . ' . ' . $merchant_id . ' . ' . $payscrow_order['order_ref'] . ' . ' . (int)round($amount*100) . ' . ' . (string)$payscrow_order['currency_code'] . ' . ';
				$hash = sha1($tmp);
				$tmp = $hash;
				$hash = sha1($tmp);

				$settle_type = 'multisettle';
				$xml_amount = '<amount currency="' . (string)$payscrow_order['currency_code'] . '">' . (int)round($amount*100) . '</amount>';
			} else {
				//$this->logger('Capture hash construct: ' . $timestamp . ' . ' . $merchant_id . ' . ' . $payscrow_order['order_ref'] . ' . . . ');
				$this->logger('Capture hash construct: ' . $timestamp . ' . ' . $merchant_id . ' . ' . $payscrow_order['order_ref'] . ' . ' . (int)round($amount*100) . ' . ' . (string)$payscrow_order['currency_code'] . ' . ');

				$tmp = $timestamp . ' . ' . $merchant_id . ' . ' . $payscrow_order['order_ref'] . ' . ' . (int)round($amount*100) . ' . ' . (string)$payscrow_order['currency_code'] . ' . ';
				$hash = sha1($tmp);
				$tmp = $hash;
				$hash = sha1($tmp);

				$settle_type = 'settle';
				$xml_amount = '<amount currency="' . (string)$payscrow_order['currency_code'] . '">' . (int)round($amount*100) . '</amount>';
			}

			$xml = '';
			$xml .= '<request type="' . $settle_type . '" timestamp="' . $timestamp . '">';
			$xml .= '<merchantid>' . $merchant_id . '</merchantid>';
			$xml .= '<account>' . $payscrow_order['account'] . '</account>';
			$xml .= '<orderid>' . $payscrow_order['order_ref'] . '</orderid>';
			$xml .= $xml_amount;
			$xml .= '<pasref>' . $payscrow_order['pasref'] . '</pasref>';
			$xml .= '<autosettle flag="1" />';
			$xml .= '<authcode>' . $payscrow_order['authcode'] . '</authcode>';
			$xml .= '<sha1hash>' . $hash . '</sha1hash>';
			$xml .= '</request>';

			$this->logger('Settle XML request:\r\n' . print_r(simplexml_load_string($xml), 1));

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "http://www.payscrow.net/customer/transactions/start");
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, "OpenCart " . VERSION);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			$response = curl_exec ($ch);
			curl_close ($ch);

			return simplexml_load_string($response);
		} else {
			return false;
		}
	}

	public function updateCaptureStatus($payscrow_order_id, $status) {
		$this->db->query("UPDATE `" . DB_PREFIX . "payscrow_order` SET `capture_status` = '" . (int)$status . "' WHERE `payscrow_order_id` = '" . (int)$payscrow_order_id . "'");
	}

	public function getOrder($order_id) {
		$this->logger('getOrder - ' . $order_id);

		$qry = $this->db->query("SELECT * FROM `" . DB_PREFIX . "payscrow_order` WHERE `order_id` = '" . (int)$order_id . "' LIMIT 1");

		if ($qry->num_rows) {
			$order = $qry->row;
			$order['transactions'] = $this->getTransactions($order['payscrow_order_id']);

			$this->logger(print_r($order, 1));

			return $order;
		} else {
			return false;
		}
	}

	private function getTransactions($payscrow_order_id) {
		$qry = $this->db->query("SELECT * FROM `" . DB_PREFIX . "payscrow_order_transaction` WHERE `payscrow_order_id` = '" . (int)$payscrow_order_id . "'");

		if ($qry->num_rows) {
			return $qry->rows;
		} else {
			return false;
		}
	}

	public function addTransaction($payscrow_order_id, $type, $total) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "payscrow_order_transaction` SET `payscrow_order_id` = '" . (int)$payscrow_order_id . "', `date_added` = now(), `type` = '" . $this->db->escape($type) . "', `amount` = '" . (float)$total . "'");
	}

	public function logger($message) {
		if ($this->config->get('payscrow_debug') == 1) {
			$log = new Log('payscrow.log');
			$log->write($message);
		}
	}

	public function getTotalCaptured($payscrow_order_id) {
		$query = $this->db->query("SELECT SUM(`amount`) AS `total` FROM `" . DB_PREFIX . "payscrow_order_transaction` WHERE `payscrow_order_id` = '" . (int)$payscrow_order_id . "' AND (`type` = 'payment' OR `type` = 'refund')");

		return (float)$query->row['total'];
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
}