<?php
class ControllerExtensionPaymentTap extends Controller {
	private $error = array();

	public function index() {

		$this->load->language('extension/payment/tap');

		$this->document->setTitle($this->language->get('heading_title'));
		$arr = array();	
				
		foreach($this->request->post as $key => $value)
		{
			if($key == 'payment_paytm_merchant2')
			{				
				 $arr[$key] = encrypt_e($value, $const1);		
				 continue;
			}
			$arr[$key] = $value;
		} 

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('payment_tap', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
		}

		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_all_zones'] = $this->language->get('text_all_zones');
		$data['text_yes'] = $this->language->get('text_yes');
		$data['text_no'] = $this->language->get('text_no');
		$data['entry_test_secret_key'] = $this->language->get('entry_test_secret_key');
		$data['entry_test_public_key'] = $this->language->get('entry_test_public_key');


		$data['entry_secret_live_key'] = $this->language->get('entry_secret_live_key');
		$data['entry_public_live_key'] = $this->language->get('entry_public_live_key');
		$data['entry_mid'] = $this->language->get('entry_mid');
		$data['entry_total'] = $this->language->get('entry_total');
		$data['entry_order_status'] = $this->language->get('entry_order_status');
		$data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$data['entry_status'] = $this->language->get('entry_status');
		
		$data['entry_post_url'] = $this->language->get('entry_post_url');
		$data['entey_charge_mode'] = $this->language->get('entry_charge_mode');
        $data['entry_ui_mode'] = $this->language->get('entry_ui_mode');
		$data['help_password'] = $this->language->get('help_password');
		$data['help_total'] = $this->language->get('help_total');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['error_test_secret_key'])) {
			$data['error_test_secret_key'] = $this->error['error_test_secret_key'];
		} else {
			$data['error_test_secret_key'] = '';
		}
		
		if (isset($this->error['error_test_public_key'])) {
			$data['error_test_public_key'] = $this->error['error_test_public_key'];
		} else {
			$data['error_test_public_key'] = '';
		}

		if (isset($this->error['error_secret_live_key'])) {
			$data['error_secret_live_key'] = $this->error['error_secret_live_key'];
		} else {
			$data['error_secret_live_key'] = '';
		}
		if (isset($this->error['erroe_post_url'])) {
			$data['erroe_post_url'] = $this->error['erroe_post_url'];
		} else {
			$data['erroe_post_url'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_payment'),
			'href' => $this->url->link('extension/payment',  'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('payment/tap', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/payment/tap', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('extension/payment',  'user_token=' . $this->session->data['user_token'], true);

		if (isset($this->request->post['payment_tap_test_secret_key'])) {
			$data['payment_tap_test_secret_key'] = $this->request->post['payment_tap_test_secret_key'];
		} else {
			$data['payment_tap_test_secret_key'] = $this->config->get('payment_tap_test_secret_key');
		}
		
		if (isset($this->request->post['payment_tap_test_public_key'])) {
			$data['payment_tap_test_public_key'] = $this->request->post['payment_tap_test_public_key'];
		} else {
			$data['payment_tap_test_public_key'] = $this->config->get('payment_tap_test_public_key');
		}
		if (isset($this->request->post['payment_tap_secret_live_key'])) {
			$data['payment_tap_secret_live_key'] = $this->request->post['payment_tap_secret_live_key'];
		} else {
			$data['payment_tap_secret_live_key'] = $this->config->get('payment_tap_secret_live_key');
		}

		if (isset($this->request->post['payment_tap_public_live_key'])) {
			$data['payment_tap_public_live_key'] = $this->config->get('payment_tap_public_live_key');
		// } else {
		// 	$data['payment_tap_password'] = md5(mt_rand());
		 }
		else {
			$data['payment_tap_public_live_key'] = $this->config->get('payment_tap_public_live_key');
		}

		if (isset($this->request->post['tap_test'])) {
			$data['payment_tap_test'] = $this->request->post['payment_tap_test'];
		} else {
			$data['payment_tap_test'] = $this->config->get('payment_tap_test');
		}

		if (isset($this->request->post['payment_tap_total'])) {
			$data['payment_tap_total'] = $this->request->post['payment_tap_total'];
		} else {
			$data['payment_tap_total'] = $this->config->get('payment_tap_total');
		}

		if (isset($this->request->post['payment_tap_order_status_id'])) {
			$data['payment_tap_order_status_id'] = $this->request->post['payment_tap_order_status_id'];
		} else {
			$data['payment_tap_order_status_id'] = $this->config->get('payment_tap_order_status_id');
		}
		if (isset($this->request->post['payment_tap_post_url'])) {
			$data['payment_tap_post_url'] = $this->request->post['payment_tap_post_url'];
		} else {
			$data['payment_tap_post_url'] = $this->config->get('payment_tap_post_url');
		}





        
        // Arry
      //  $data['chargemodes'] = array("Charge", "Authorize");
        //var_dump($data['chargemodes']);exit;

        
        
     
       
    

		if (isset($this->request->post['payment_tap_charge_mode'])) {
			$data['payment_tap_charge_mode'] = $this->request->post['payment_tap_charge_mode'];
		} else {
			$data['payment_tap_charge_mode'] = $this->config->get('payment_tap_charge_mode');
		}

		if (isset($this->request->post['payment_tap_ui_mode'])) {
			$data['payment_tap_ui_mode'] = $this->request->post['payment_tap_ui_mode'];
		} else {
			$data['payment_tap_ui_mode'] = $this->config->get('payment_tap_ui_mode');
		}
		





		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['tap_geo_zone_id'])) {
			$data['tap_geo_zone_id'] = $this->request->post['tap_geo_zone_id'];
		} else {
			$data['tap_geo_zone_id'] = $this->config->get('tap_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['payment_tap_status'])) {
			$data['payment_tap_status'] = $this->request->post['payment_tap_status'];
		} else {
			$data['payment_tap_status'] = $this->config->get('payment_tap_status');
		}


		    
		

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/tap', $data));
	}
	public function install() {
        $this->load->model('extension/payment/tap');
    }

    public function uninstall() {
        $this->load->model('extension/payment/tap');
    }


	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/tap')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['payment_tap_test_secret_key']) {
			$this->error['payment_tap_test_secret_key'] = $this->language->get('error_payment_tap_test_secret_key');
		}
		
		if (!$this->request->post['payment_tap_test_public_key']) {
			$this->error['payment_tap_test_public_key'] = $this->language->get('error_payment_tap_test_public_key');
		}

		if (!$this->request->post['payment_tap_secret_live_key']) {
			$this->error['payment_tap_secret_live_key'] = $this->language->get('error_payment_tap_secret_live_key');
		}

		if (!$this->request->post['payment_tap_public_live_key']) {
			$this->error['payment_tap_public_live_key'] = $this->language->get('error_payment_tap_public_live_key');
		}

        if (!$this->request->post['payment_tap_post_url']) {
			$this->error['payment_tap_post_url'] = $this->language->get('erroe_post_url');
		}


		if (!$this->request->post['payment_tap_charge_mode']) {
			$this->error['payment_tap_charge_mode'] = $this->language->get('error_charge_mode');
		}

		if (!$this->request->post['payment_tap_ui_mode']) {
			$this->error['payment_tap_ui_mode'] = $this->language->get('error_ui_mode');
		}


		


		return !$this->error;
	}
}
