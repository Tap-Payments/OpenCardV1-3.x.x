<?php
class ControllerExtensionPaymentTap extends Controller {
	public function index() {
		$data['button_confirm'] = $this->language->get('button_confirm');
		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		//$data['amount'] = $order_info['total'];
		$data['amount'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
		$data['currencycode'] = $order_info['currency_code'];
		$data['products'] = $this->cart->getProducts();
		$data['order_id'] = $this->session->data['order_id'];
		$data['test_public_key'] = $this->config->get('payment_tap_test_public_key');
		$data['entry_post_url'] = $this->config->get('payment_tap_post_url');
		$data['entry_ui_mode'] = $this->config->get('payment_tap_ui_mode');
	
		if ($this->config->get('payment_tap_test'))
			{
				$active_sk = $this ->config->get('payment_tap_test_secret_key');
				$active_pk = $this ->config->get('payment_tap_test_public_key');	
			}


			else{
				$active_sk = $this ->config->get('payment_tap_secret_live_key');
				$active_pk = $this ->config->get('payment_tap_public_live_key');
				
			}

			$data ['active_sk'] = $active_sk;
			$data ['active_pk'] = $active_pk;
			$charge_mode = $this ->config->get('payment_tap_charge_mode');
			
	
		if ($data['currencycode'] == "KWD" || $data['currencycode'] == "BHD" || $data['currencycode']=="OMR" || $data['currencycode'] == 'JOD'){
			$Total_price = number_format((float)$data['amount'], 3, '.', '');
		}
		else {
			$Total_price = number_format((float)$data['amount'], 2, '.', '');
		}
		
        $ref = '';
        $Hash = 'x_publickey'.$active_pk.'x_amount'.$Total_price.'x_currency'.$data['currencycode'].'x_transaction'.$ref.'x_post'.$data['entry_post_url'];

       	$hash = hash_hmac('sha256', $Hash, $active_sk);
       	$country_code = $order_info['shipping_iso_code_3'];
       	
		$data['itemprice1'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
		$data['itemname1'] ='Order ID - '.$order_info['order_id'];
		$data['currencycode'] = $order_info['currency_code'];
		$data['ordid'] = $order_info['order_id'];
		$data['entey_charge_mode'] = $this->config->get('payment_tap_charge_mode');

		$data['cstemail'] = $order_info['email'];
		$data['cstname'] = html_entity_decode($order_info['payment_firstname'], ENT_QUOTES, 'UTF-8');
		$data['cstlname'] = html_entity_decode($order_info['payment_lastname'], ENT_QUOTES, 'UTF-8');
		$data['cstmobile'] = $order_info['telephone'];
		$data['cntry'] = $order_info['payment_iso_code_2'];
		$counteries = [
				[
					"country" => "Egypt", 
					"code" => "20", 
					"iso" => "EG" 
				],
				[
				   "country" => "Kuwait", 
				   "code" => "965", 
				   "iso" => "KW" 
				],
				[
				   "country" => "Saudi Arabia", 
				   "code" => "966", 
				   "iso" => "SA" 
				],
				[
   					"country" => "United Arab Emirates", 
					"code" => "971", 
					"iso" => "AE" 
				],
				[
				   "country" => "Bahrain", 
				   "code" => "973", 
				   "iso" => "BH" 
				],
				[
				   "country" => "Oman", 
				   "code" => "968", 
				   "iso" => "OM" 
				],
				[
				   "country" => "Qatar", 
				   "code" => "974", 
				   "iso" => "QA" 
				],
				[
				   "country" => "Jordan", 
				   "code" => "962", 
				   "iso" => "JD" 
				],
				[
				   "country" => "Lebnon", 
				   "code" => "961", 
				   "iso" => "LB" 
				]


		];
		$country_code = '';
		foreach($counteries as $country) {
			if($country['iso'] == $data['cntry']) {
				$country_code = $country['code'];
			}
		}
		$trans_object = [];
		if ($charge_mode == 'Authorize') {
            $request_url = "https://api.tap.company/v2/authorize";
		}
		else {
			$request_url = "https://api.tap.company/v2/charges";
		}
		
		$data['returnurl'] = $this->url->link('extension/payment/tap/callback');
		//echo $this->config->get('config_language_id');exit;
		$language_code  = $this->session->data['language'];

		if ($language_code == 'en-gb') {
			$data['language'] = 'en';
		}
        else {
        	$data['language'] = 'ar';
        }
        $trans_object["amount"]                 = $Total_price;
        $trans_object["currency"]               = $data['currencycode'];
        $trans_object["threeDsecure"]           = true;
        $trans_object["save_card"]              = false;
        $trans_object["description"]            = $data['order_id'];
        $trans_object["statement_descriptor"]   = 'Sample';
        $trans_object["metadata"]["udf1"]       = 'test';
        $trans_object["metadata"]["udf2"]          = 'test';
        $trans_object["reference"]["transaction"]  = $ref;
        $trans_object["reference"]["order"]        = $data['order_id'];
        $trans_object["hashstring"]        = $hash;
        $trans_object["receipt"]["email"]          = false;
        $trans_object["receipt"]["sms"]            = true;
        $trans_object["customer"]["first_name"]    = $data['cstname'];
        $trans_object["customer"]["last_name"]    = $data['cstlname'];
        $trans_object["customer"]["email"]        = $data['cstemail'];
        $trans_object["customer"]["phone"]["country_code"]       = $country_code;
        $trans_object["customer"]["phone"]["number"] = $data['cstmobile'];
        $trans_object["source"]["id"] = 'src_all';
        if ($charge_mode == 'Authorize') {
        	$trans_object["authorize_debit"] = false;
            $trans_object["auto"]["type"] = "VOID";
            $trans_object["auto"]["time"] = "100";
        }
        $trans_object["post"]["url"] = $data['entry_post_url'];
        $trans_object["redirect"]["url"] = $data['returnurl'];

        $curl = curl_init();

        curl_setopt_array($curl, array(
                CURLOPT_URL => $request_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($trans_object),
                CURLOPT_HTTPHEADER => array(
                            "authorization: Bearer ".$active_sk,
                            "content-type: application/json", 
                            "lang_code:".$data['language'] 
                ),
            )
        );

        $response = curl_exec($curl);
        $response = json_decode($response);
      	$data['transaction'] = $response->transaction->url;
       	return $this->load->view('extension/payment/tap', $data);

	}
	public function webhook() { 
		$this->log->write('Is $var a null? Here is the value ');
		var_dump($this->request->post);exit;
		print_r(json_decode(file_get_contents('php://input'), true));

	}

	public function callback() {
		$this->load->language('payment/tap');
		if (isset($this->request->get['tap_id'])) {
			$tap_id = $this->request->get['tap_id'];
			$order_id = $this->session->data['order_id'];
		} else {
			$order_id = 0;
		}
		if ($this->config->get('payment_tap_test'))
			{
				$active_sk = $this ->config->get('payment_tap_test_secret_key');
			}
			else{
				$active_sk = $this ->config->get('payment_tap_secret_live_key');
			}

			$data ['active_sk'] = $active_sk;
			$charge_mode = $this->config->get('payment_tap_charge_mode');
			if ($charge_mode == 'Authorize') {
            $request_url = "https://api.tap.company/v2/authorize/";
			}
			else {
				$request_url = "https://api.tap.company/v2/charges/";
			}
		$curl = curl_init();

		curl_setopt_array($curl, array(
  			CURLOPT_URL => $request_url.$tap_id,
  				CURLOPT_RETURNTRANSFER => true,
  				CURLOPT_ENCODING => "",
  				CURLOPT_MAXREDIRS => 10,
  				CURLOPT_TIMEOUT => 30,
  				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  				CURLOPT_CUSTOMREQUEST => "GET",
  				CURLOPT_POSTFIELDS => "{}",
  				CURLOPT_HTTPHEADER => array(
    					"authorization: Bearer ".$active_sk,

  				),
			)
		);
		$transaction_response = curl_exec($curl);
		$err = curl_error($curl);
		//echo '<pre>';var_dump($transaction_response);exit;

		curl_close($curl);

		if ($err) {
  			echo "cURL Error #:" . $err;
		} else {
  			$transaction_response = json_decode($transaction_response);
  			$order_id = $transaction_response->reference->order;
		}
	
		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($order_id);

		$order_total = $order_info['total'];
		$order_currency = $order_info['currency_code'];
		$response_currency = $transaction_response->currency;

		if ($order_currency == "KWD" || $order_currency == "BHD" || $order_currency=="OMR" || $order_currency == 'JOD'){
			$order_total = number_format((float)$order_total, 3, '.', '');
		}
		else {
			$order_total = number_format((float)$order_total, 2, '.', '');
		}
	

	
		if (($order_total == $transaction_response->amount) && ($order_currency == $response_currency)) { 
			if ($order_info && ($transaction_response->status == 'CAPTURED' || $transaction_response->status == 'AUTHORIZED')) {
				$error = '';
				$comment = 'Tap payment successful'.("<br>").('ID').(':'). ($_GET['tap_id'].("<br>").('Payment Type :') . ($transaction_response->source->payment_method).("<br>").('Payment Ref:'). ($transaction_response->reference->payment));
				$this->model_checkout_order->addOrderHistory($order_id, $this->config->get('payment_tap_order_status_id'), $comment);

				$this->response->redirect($this->url->link('checkout/success'));
			} 
			else {
				$error = $this->language->get('text_unable');
				$this->model_checkout_order->addOrderHistory($order_id, 10,'Transaction Failed');
			}
		}
		else {
			if ($charge_mode == 'charge' && $transaction_response->status == 'CAPTURED') {
				$refund_url = "https://api.tap.company/v2/refunds/";
				$refund_object["charge_id"]                 = $tap_id;
				$refund_object["amount"]   = $transaction_response->amount;
	        	$refund_object["currency"]               = $response_currency;
	        	$refund_object["reason"]           = "Order currency and response currency mismatch(fraudulent)";
	        	$refund_object["post_url"] = ""; 
				
				$curl = curl_init();
				curl_setopt_array($curl, array(
		  			CURLOPT_URL => $refund_url,
		  				CURLOPT_RETURNTRANSFER => true,
		  				CURLOPT_ENCODING => "",
		  				CURLOPT_MAXREDIRS => 10,
		  				CURLOPT_TIMEOUT => 30,
		  				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  				CURLOPT_CUSTOMREQUEST => "POST",
		  				CURLOPT_POSTFIELDS => json_encode($refund_object),
		  				CURLOPT_HTTPHEADER => array(
		    					"authorization: Bearer ".$active_sk,
		    					"content-type: application/json"
		  				),
					)
				);

				$response = curl_exec($curl);
				$response = json_decode($response);
				$err = curl_error($curl);
				curl_close($curl);
				$this->model_checkout_order->addOrderHistory($order_id, 8, $refund_object["reason"].'---Refunded---'.$transaction_response->id);
				$error = $this->language->get('text_unable');
			}
			if ($charge_mode == 'Authorize' && $transaction_response->status == 'AUTHORIZED') {
				$void_url = 'https://api.tap.company/v2/authorize/'.$tap_id.'/void';
				$curl = curl_init();
				curl_setopt_array($curl, array(
		  			CURLOPT_URL => $void_url,
		  				CURLOPT_RETURNTRANSFER => true,
		  				CURLOPT_ENCODING => "",
		  				CURLOPT_MAXREDIRS => 10,
		  				CURLOPT_TIMEOUT => 30,
		  				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  				CURLOPT_CUSTOMREQUEST => "POST",
		  				CURLOPT_POSTFIELDS => "{}",
		  				CURLOPT_HTTPHEADER => array(
		    					"authorization: Bearer ".$active_sk,
		    					"content-type: application/json"
		  				),
					)
				);

				$response = curl_exec($curl);
				$response = json_decode($response);
				$err = curl_error($curl);
				curl_close($curl);
				$this->model_checkout_order->addOrderHistory($order_id, 8,'---Void---'.$response->id);
				$error = $this->language->get('text_unable');
			}
			
		}

		if (!empty($error)) {
			$data['breadcrumbs'] = array();

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/home')
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_basket'),
				'href' => $this->url->link('checkout/cart')
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_checkout'),
				'href' => $this->url->link('checkout/checkout', '', 'SSL')
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_failed'),
				'href' => $this->url->link('checkout/success')
			);

			$data['heading_title'] = $this->language->get('text_failed');

			$data['text_message'] = sprintf($this->language->get('text_failed_message'), $error, $this->url->link('information/contact'));

			$data['button_continue'] = $this->language->get('button_continue');

			$data['continue'] = $this->url->link('common/home');
			
			



			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

            if (isset($this->request->post['payment_tap_charge_mode'])) {
			$data['payment_tap_charge_mode'] = $this->request->post['payment_tap_charge_mode'];

		    } else {
			$data['payment_tap_charge_mode'] = $this->config->get('payment_tap_charge_mode');
		    }

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/common/success')) {
				$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/common/success', $data));
			} else {
				$this->response->setOutput($this->load->view('common/success', $data));
			}
		} 
	}
}