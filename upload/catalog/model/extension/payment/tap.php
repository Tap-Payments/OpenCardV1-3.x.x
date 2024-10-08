<?php
class ModelExtensionPaymentTap extends Model {
	public function getMethod($address, $total) {
		$this->load->language('extension/payment/tap');


		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('payment_tap_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");
         
		if ($this->config->get('payment_tap_total') > 0 && $this->config->get('payment_tap_total') > $total) {
			$status = false;
		} elseif (!$this->config->get('payment_tap_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} 
		else {
			$status = false;
		}

		$method_data = array();
		$status = true;
		
        
		if ($status) {
			$method_data = array(
				'code'       => 'tap',
				'terms'      => '',
				'title'      => '<img src="https://www.gotapnow.com/web/tap.png" />',
				'sort_order' => $this->config->get('payment_tap_sort_order')
			);
		}

		return $method_data;
	}
}
