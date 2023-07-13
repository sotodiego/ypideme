<?php
require_once DIR_SYSTEM . 'ocMpTrait.php';
class ControllerAccountCustomerpartnerOrderinfo extends Controller {

	use OcMpTrait;

	public function index() {
		$this->checkMpModuleStatus();

    $data = array();

		$data = $this->language->load('account/customerpartner/orderinfo');

		if (!$this->customer->isLogged()) {
			if(isset($this->request->get['order_id'])){
				$this->session->data['redirect'] = $this->url->link('account/customerpartner/orderinfo&order_id='.$this->request->get['order_id'], '', true);
			}
			$this->response->redirect($this->url->link('account/login', '', true));
		} else {
			if(!isset($this->request->get['order_id']) || empty($this->request->get['order_id'])){
				$this->response->redirect($this->url->link('account/customerpartner/orderlist', '', true));
			}
		}

		$this->load->model('account/customerpartner');

		$data['chkIsPartner'] = $this->model_account_customerpartner->chkIsPartner();

		if(!$data['chkIsPartner'] || (isset($this->session->data['marketplace_seller_mode']) && !$this->session->data['marketplace_seller_mode']))
			$this->response->redirect($this->url->link('account/account', '', true));

		if (isset($this->request->get['order_id'])) {
			$order_id = (int)$this->request->get['order_id'];
		} else {
			$order_id = 0;
		}

      	if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

			if($order_id){

				if(isset($this->request->post['tracking'])){
					$count = $this->model_account_customerpartner->addOdrTracking($order_id,$this->request->post['tracking']);
					if ($count) {
							$this->session->data['success'] = $this->language->get('text_success');
					}
				}

		 		$this->response->redirect($this->url->link('account/customerpartner/orderinfo&order_id='.$order_id, '', true));
			}

		}

		if(isset($this->session->data['success'])){
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		}else{
			$data['success'] = '';
		}

		$data['order_id'] = $order_id;

		$this->load->model('account/order');

		$order_info = $this->model_account_customerpartner->getOrder($order_id);
    // no order info redirect to the order-list page
		if(empty($order_info)){
		  $this->response->redirect($this->url->link('account/customerpartner/orderlist', '', true));
	  }

		$this->document->setTitle($this->language->get('text_order'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', true),
			'separator' => false
		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_account'),
			'href'      => $this->url->link('account/account', '', true),
			'separator' => $this->language->get('text_separator')
		);

		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('account/customerpartner/orderlist', $url, true),
			'separator' => $this->language->get('text_separator')
		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_order'),
			'href'      => $this->url->link('account/customerpartner/orderinfo', 'order_id=' . $order_id . $url, true),
			'separator' => $this->language->get('text_separator')
		);


		$data['errorPage'] = false;

		if($this->config->get('marketplace_available_order_status')) {
  			$data['marketplace_available_order_status'] = $this->config->get('marketplace_available_order_status');
  			$data['marketplace_order_status_sequence'] = $this->config->get('marketplace_order_status_sequence');
      	}

	    if ($this->config->get('marketplace_cancel_order_status') &&$this->config->get('marketplace_available_order_status')) {

  			$data['marketplace_cancel_order_status'] = $this->config->get('marketplace_cancel_order_status');

  			$cancel_order_statusId_key_available =  array_search($this->config->get('marketplace_cancel_order_status'),$data['marketplace_available_order_status'],true);

  			if ($cancel_order_statusId_key_available === 0 || $cancel_order_statusId_key_available) {

  			    unset($data['marketplace_available_order_status'][$cancel_order_statusId_key_available]);
  			    unset($data['marketplace_order_status_sequence'][$this->config->get('marketplace_cancel_order_status')]);

  			}

  			foreach ($data['marketplace_order_status_sequence'] as $key => $value) {

               if ($value['order_status_id'] == $this->config->get('marketplace_cancel_order_status')) {

               	   unset($data['marketplace_order_status_sequence'][$key]);

               }
  			}

  		}else{
  			$data['marketplace_cancel_order_status'] = '';
  		}

		if ($order_info) {

			$data['wksellerorderstatus'] = $this->config->get('marketplace_sellerorderstatus');

			if ($order_info['invoice_no']) {
				$data['invoice_no'] = $order_info['invoice_prefix'] . $order_info['invoice_no'];
			} else {
				$data['invoice_no'] = '';
			}

			$data['date_added'] = date($this->language->get('date_format_short'), strtotime($order_info['date_added']));

			if ($order_info['payment_address_format']) {
      			$format = $order_info['payment_address_format'];
    		} else {
				$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
			}

    		$find = array(
	  			'{firstname}',
	  			'{lastname}',
	  			'{company}',
      			'{address_1}',
      			'{address_2}',
     			'{city}',
      			'{postcode}',
      			'{zone}',
				'{zone_code}',
      			'{country}'
			);

			$replace = array(
	  			'firstname' => $order_info['payment_firstname'],
	  			'lastname'  => $order_info['payment_lastname'],
	  			'company'   => $order_info['payment_company'],
      			'address_1' => $order_info['payment_address_1'],
      			'address_2' => $order_info['payment_address_2'],
      			'city'      => $order_info['payment_city'],
      			'postcode'  => $order_info['payment_postcode'],
      			'zone'      => $order_info['payment_zone'],
				'zone_code' => $order_info['payment_zone_code'],
      			'country'   => $order_info['payment_country']
			);

			$data['payment_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

      		$data['payment_method'] = $order_info['payment_method'];

			if ($order_info['shipping_address_format']) {
      			$format = $order_info['shipping_address_format'];
    		} else {
				$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
			}

    		$find = array(
	  			'{firstname}',
	  			'{lastname}',
	  			'{company}',
      			'{address_1}',
      			'{address_2}',
     			'{city}',
      			'{postcode}',
      			'{zone}',
				'{zone_code}',
      			'{country}'
			);

			$replace = array(
	  			'firstname' => $order_info['shipping_firstname'],
	  			'lastname'  => $order_info['shipping_lastname'],
	  			'company'   => $order_info['shipping_company'],
      			'address_1' => $order_info['shipping_address_1'],
      			'address_2' => $order_info['shipping_address_2'],
      			'city'      => $order_info['shipping_city'],
      			'postcode'  => $order_info['shipping_postcode'],
      			'zone'      => $order_info['shipping_zone'],
				'zone_code' => $order_info['shipping_zone_code'],
      			'country'   => $order_info['shipping_country']
			);

			$data['shipping_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

			$data['shipping_method'] = $order_info['shipping_method'];
			$data['seller_shipping'] = false;
			if ($this->config->get('shipping_wk_custom_shipping_label_status') && $this->config->get('shipping_wk_custom_shipping_seller_label') && $this->config->get('shipping_wk_custom_shipping_status')) {

				if (isset($order_info['shipping_code']) && $order_info['shipping_code'] == 'wk_custom_shipping.wk_custom_shipping') {
					$data['seller_shipping'] = true;
					$data['custom_shipping_label'] = $this->url->link('account/customerpartner/shippinglabel&order_id='.$order_id, '', true);
				}
			}

			$data['products'] = array();

			$products = $this->model_account_customerpartner->getSellerOrderProducts($order_id);

			// Uploaded files
			$this->load->model('tool/upload');

      		foreach ($products as $product) {
				$option_data = array();

				$options = $this->model_account_order->getOrderOptions($order_id, $product['order_product_id']);

         		 // code changes due to download file error
         		foreach ($options as $option) {
          			if ($option['type'] != 'file') {
						$option_data[] = array(
							'name'  => $option['name'],
							'value' => $option['value'],
							'type'  => $option['type']
						);
					} else {
						$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);
						if ($upload_info) {
							$option_data[] = array(
								'name'  => $option['name'],
								'value' => $upload_info['name'],
								'type'  => $option['type'],
								'href'  => $this->url->link('account/customerpartner/orderinfo/download','&code=' . $upload_info['code'], true)
							);
						}
					}
        		}

        		$product_tracking = $this->model_account_customerpartner->getOdrTracking($data['order_id'],$product['product_id'],$this->customer->getId());

        		if($product['paid_status'] == 1) {
        			$paid_status = $this->language->get('text_paid');
        		} else {
        			$paid_status = $this->language->get('text_not_paid');
        		}

        		$data['products'][] = array(
          			'product_id'     => $product['product_id'],
          			'name'     => $product['name'],
          			'model'    => $product['model'],
          			'option'   => $option_data,
          			'tracking' => isset($product_tracking['tracking']) ? $product_tracking['tracking'] : '',
          			'quantity' => $product['quantity'],
          			'paid_status' => $paid_status,
          			'price'    => $this->currency->format($product['c2oprice'], $order_info['currency_code'], $order_info['currency_value']),
					'total'    => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']),
					'order_product_status' => $product['order_product_status'],
        		);
      		}

			// Voucher
			$data['vouchers'] = array();

			$vouchers = $this->model_account_order->getOrderVouchers($order_id);

			foreach ($vouchers as $voucher) {
				$data['vouchers'][] = array(
					'description' => $voucher['description'],
					'amount'      => $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value'])
				);
			}

      		$data['totals'] = array();

					$totals = $this->model_account_customerpartner->getOrderTotals($order_id);

					if ($totals) {

					  if (isset($totals[0]['shipping_applied']) && $totals[0]['shipping_applied']) {
					    $data['totals'][] = array(
					      'title' => $totals[0]['shipping'],
					      'text'  => $this->currency->format($totals[0]['shipping_applied'], $order_info['currency_code'], $order_info['currency_value']),
					    );
					  }

					  if (isset($totals[0]['total'])) {
					    $data['totals'][] = array(
					      'title' => $this->language->get('column_total'),
					      'text'  => $this->currency->format($totals[0]['total'], $order_info['currency_code'], $order_info['currency_value']),
					    );
					  }
					}

			$data['comment'] = nl2br($order_info['comment']);

			$data['histories'] = array();

			$results = $this->model_account_customerpartner->getOrderHistories($order_id);

      		foreach ($results as $result) {
        		$data['histories'][] = array(
          			'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
          			'status'     => $result['status'],
          			'comment'    => nl2br($result['comment'])
        		);
      		}

      		//list of status

      		$this->load->model('mp_localisation/order_status');

      		$data['order_statuses'] = $this->model_mp_localisation_order_status->getOrderStatuses();

      		$data['marketplace_complete_order_status'] = $this->config->get('marketplace_complete_order_status');

      		$data['order_status_id'] = $order_info['order_status_id'];

      	}else{
      		$data['errorPage'] = true;
      		$data['order_status_id'] = '';
      	}
				if(isset($data['marketplace_order_status_sequence'])) {
					foreach ($data['marketplace_order_status_sequence'] as $key1 => $value1) {
						foreach ($data['order_statuses'] as $key => $value) {
							if($value1['order_status_id'] == $value['order_status_id']){
								$data['marketplace_order_status_sequence'][$key1]['name'] = $value['name'];
							}
						}
					}
			  } else {
			  	$data['marketplace_order_status_sequence'] = array();
			  }

		$data['action'] = $this->url->link('account/customerpartner/orderinfo&order_id='.$order_id, '', true);
		$data['continue'] = $this->url->link('account/customerpartner/orderlist', '', true);
		$data['order_invoice'] = $this->url->link('account/customerpartner/soldinvoice&order_id='.$order_id, '', true);
		$data['shipping_note'] = $this->url->link('account/customerpartner/orderinfo/shipping&order_id='.$order_id, '', true);

		if(isset($this->session->data['warning']) && $this->session->data['warning']){
			$data['warning'] = $this->session->data['warning'];
			unset($this->session->data['warning']);
		}else{
			$data['warning'] = '';
		}

  		/*
  		Access according to membership plan
  		 */
  		$data['isMember'] = true;

		  if($this->config->get('module_wk_seller_grouper_group_status')) {
      		$data['module_wk_seller_grouper_group_status'] = true;
      		$this->load->model('account/customer_group');
			    $isMember = $this->model_account_customer_group->getSellerMembershipGroup($this->customer->getId());
			    if($isMember) {
							$allowedAccountMenu = $this->model_account_customer_group->getaccountMenu($isMember['gid']);
							if($allowedAccountMenu['value']) {
									$accountMenu = explode(',',$allowedAccountMenu['value']);
									if($accountMenu && !in_array('orderhistory:orderhistory', $accountMenu)) {
										$data['isMember'] = false;
									}
							}
					} else {
				     $data['isMember'] = false;
			    }
      	} else {
      		if(!is_array($this->config->get('marketplace_allowed_account_menu')) || !in_array('orderhistory', $this->config->get('marketplace_allowed_account_menu'))) {
      			$this->response->redirect($this->url->link('account/account','', true));
      		}
      	}

      	/*
      	end here
      	 */
  	$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$data['separate_view'] = false;

		$data['separate_column_left'] = '';

		if ($this->config->get('marketplace_separate_view') && isset($this->session->data['marketplace_separate_view']) && $this->session->data['marketplace_separate_view'] == 'separate') {
		  $data['separate_view'] = true;
		  $data['column_left'] = '';
		  $data['column_right'] = '';
		  $data['content_top'] = '';
		  $data['content_bottom'] = '';
		  $data['separate_column_left'] = $this->load->controller('account/customerpartner/column_left');
		  $data['footer'] = $this->load->controller('account/customerpartner/footer');
		  $data['header'] = $this->load->controller('account/customerpartner/header');
		}
		$this->response->setOutput($this->load->view('account/customerpartner/orderinfo' , $data));

	}

	public function history() {
		$this->language->load('account/customerpartner/orderinfo');
		$this->load->model('account/customerpartner');

		$json = array();

		if ($this->config->get('marketplace_cancel_order_status')) {
			$marketplace_cancel_order_status = $this->config->get('marketplace_cancel_order_status');
		}else{
			$marketplace_cancel_order_status = '';
		}

        if (isset($this->request->post['comment']) && !empty($this->request->post['comment']) && empty($this->request->post['product_ids'])) {

        	$getOrderStatusId = $this->model_account_customerpartner->getOrderStatusId((int)$this->request->get['order_id']);

        	$this->request->post['order_status_id'] = $getOrderStatusId['order_status_id'];

        	$this->model_account_customerpartner->addOrderHistory((int)$this->request->get['order_id'],$this->request->post);

        	$this->model_account_customerpartner->addSellerOrderStatus((int)$this->request->get['order_id'],'',$this->request->post);

        	$json['success'] = $this->language->get('text_success_history');

        }elseif(isset($this->request->post['product_ids']) && !empty($this->request->post['product_ids'])){

        	$products = explode(",", $this->request->post['product_ids']);

        	$this->load->model('mp_localisation/order_status');
            $order_statuses = $this->model_mp_localisation_order_status->getOrderStatuses();

        	foreach ($order_statuses as $value) {

				if (in_array($this->request->post['order_status_id'], $value)) {

					$seller_change_order_status_name = $value['name'];

				}
			}

					if (isset($seller_change_order_status_name) && $seller_change_order_status_name) {
						if ($this->request->post['order_status_id'] == $marketplace_cancel_order_status) {
							 $this->changeOrderStatus($this->request->get,$this->request->post,$products,$marketplace_cancel_order_status,$seller_change_order_status_name);
						}else{
							 $this->changeOrderStatus($this->request->get,$this->request->post,$products,$marketplace_cancel_order_status,$seller_change_order_status_name);
						}
					}

        	$json['success'] = $this->language->get('text_success_history');

        }else{

        	$json['error'] = $this->language->get('error_product_select');
        }

		$this->response->setOutput(json_encode($json));
  	}

  	private function changeOrderStatus($get,$post,$products,$marketplace_cancel_order_status,$seller_change_order_status_name){


         /**
          * First step - Add seller changing status for selected products
          */
	     $this->model_account_customerpartner->addsellerorderproductstatus($get['order_id'],$post['order_status_id'],$products);


	     // Second Step - add comment for each selected products
		 $this->model_account_customerpartner->addSellerOrderStatus($get['order_id'],$post['order_status_id'],$post,$products,$seller_change_order_status_name);

         // Thired Step - Get status Id that will be the whole order status id after changed the order product status by seller
		 $getWholeOrderStatus = $this->model_account_customerpartner->getWholeOrderStatus($get['order_id'],$marketplace_cancel_order_status);


         // Fourth Step - add comment in order_history table and send mails to admin(If admin notify is enable) and customer
		 $this->model_account_customerpartner->addOrderHistory($get['order_id'],$post,$seller_change_order_status_name);


         // Fifth Step - Update whole order status in order table
         if ($getWholeOrderStatus) {
             $this->model_account_customerpartner->changeWholeOrderStatus($get['order_id'],$getWholeOrderStatus);
         }

  	}

  	// file download code
  	public function download() {
		$this->load->model('tool/upload');

		if (isset($this->request->get['code'])) {
			$code = $this->request->get['code'];
		} else {
			$code = 0;
		}

		$upload_info = $this->model_tool_upload->getUploadByCode($code);

		if ($upload_info) {
			$file = DIR_UPLOAD . $upload_info['filename'];
			$mask = basename($upload_info['name']);

			if (!headers_sent()) {
				if (is_file($file)) {
					header('Content-Type: application/octet-stream');
					header('Content-Description: File Transfer');
					header('Content-Disposition: attachment; filename="' . ($mask ? $mask : basename($file)) . '"');
					header('Content-Transfer-Encoding: binary');
					header('Expires: 0');
					header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
					header('Pragma: public');
					header('Content-Length: ' . filesize($file));

					readfile($file, 'rb');
					exit;
				} else {
					exit('Error: Could not find file ' . $file . '!');
				}
			} else {
				exit('Error: Headers already sent out!');
			}
		} else {
			$this->load->language('error/not_found');

			$this->document->setTitle($this->language->get('heading_title'));

			$data['heading_title'] = $this->language->get('heading_title');

			$data['error_warning_authenticate'] = $this->language->get('error_warning_authenticate');

			$data['text_not_found'] = $this->language->get('text_not_found');

			$data['breadcrumbs'] = array();

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('error/not_found', 'user_token=' . $this->session->data['user_token'], true)
			);

			$data['header'] = $this->load->controller('common/header');

		$data['separate_view'] = false;

		$data['separate_column_left'] = '';

		if ($this->config->get('marketplace_separate_view') && isset($this->session->data['marketplace_separate_view']) && $this->session->data['marketplace_separate_view'] == 'separate') {
		  $data['separate_view'] = true;
		  $data['column_left'] = '';
		  $data['column_right'] = '';
		  $data['content_top'] = '';
		  $data['content_bottom'] = '';
		  $data['separate_column_left'] = $this->load->controller('account/customerpartner/column_left');
		  $data['footer'] = $this->load->controller('account/customerpartner/footer');
		  $data['header'] = $this->load->controller('account/customerpartner/header');
		}
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['footer'] = $this->load->controller('common/footer');

			$this->response->setOutput($this->load->view('error/not_found.tpl', $data));
		}
	}
	public function shipping() {
		$this->load->language('account/customerpartner/shiplist');

		$data['title'] = $this->language->get('text_shipping');

		if ($this->request->server['HTTPS']) {
			$data['base'] = HTTPS_SERVER;
		} else {
			$data['base'] = HTTP_SERVER;
		}

		$data['direction'] = $this->language->get('direction');
		$data['lang'] = $this->language->get('code');

		$this->load->model('checkout/order');

		$this->load->model('catalog/product');

		$this->load->model('setting/setting');

		$data['orders'] = array();

		$orders = array();

		if (isset($this->request->get['order_id'])) {
			$orders[] = $this->request->get['order_id'];
		}

		foreach ($orders as $order_id) {
			$order_info = $this->model_checkout_order->getOrder($order_id);

			// Make sure there is a shipping method
			if ($order_info && $order_info['shipping_code']) {
				$store_info = $this->model_setting_setting->getSetting('config', $order_info['store_id']);

				if ($store_info) {
					$store_address = $store_info['config_address'];
					$store_email = $store_info['config_email'];
					$store_telephone = $store_info['config_telephone'];
				} else {
					$store_address = $this->config->get('config_address');
					$store_email = $this->config->get('config_email');
					$store_telephone = $this->config->get('config_telephone');
				}

				if ($order_info['invoice_no']) {
					$invoice_no = $order_info['invoice_prefix'] . $order_info['invoice_no'];
				} else {
					$invoice_no = '';
				}

				if ($order_info['shipping_address_format']) {
					$format = $order_info['shipping_address_format'];
				} else {
					$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
				}

				$find = array(
					'{firstname}',
					'{lastname}',
					'{company}',
					'{address_1}',
					'{address_2}',
					'{city}',
					'{postcode}',
					'{zone}',
					'{zone_code}',
					'{country}'
				);

				$replace = array(
					'firstname' => $order_info['shipping_firstname'],
					'lastname'  => $order_info['shipping_lastname'],
					'company'   => $order_info['shipping_company'],
					'address_1' => $order_info['shipping_address_1'],
					'address_2' => $order_info['shipping_address_2'],
					'city'      => $order_info['shipping_city'],
					'postcode'  => $order_info['shipping_postcode'],
					'zone'      => $order_info['shipping_zone'],
					'zone_code' => $order_info['shipping_zone_code'],
					'country'   => $order_info['shipping_country']
				);

				$shipping_address = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

				$this->load->model('tool/upload');

				$product_data = array();

				$products = $this->model_checkout_order->getOrderProducts($order_id);

				foreach ($products as $product) {
					$option_weight = '';

					$product_info = $this->model_catalog_product->getProduct($product['product_id']);

					if ($product_info) {
						$option_data = array();

						$options = $this->model_checkout_order->getOrderOptions($order_id, $product['order_product_id']);

						foreach ($options as $option) {
							if ($option['type'] != 'file') {
								$value = $option['value'];
							} else {
								$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

								if ($upload_info) {
									$value = $upload_info['name'];
								} else {
									$value = '';
								}
							}

							$option_data[] = array(
								'name'  => $option['name'],
								'value' => $value
							);
							$this->load->model('account/customerpartnerorder');

							$product_option_value_info = $this->model_account_customerpartnerorder->getProductOptionValue($product['product_id'], ['product_option_value_id']);

							if ($product_option_value_info) {
								if ($product_option_value_info['weight_prefix'] == '+') {
									$option_weight += $product_option_value_info['weight'];
								} elseif ($product_option_value_info['weight_prefix'] == '-') {
									$option_weight -= $product_option_value_info['weight'];
								}
							}
						}

						$product_data[] = array(
							'name'     => $product_info['name'],
							'model'    => $product_info['model'],
							'option'   => $option_data,
							'quantity' => $product['quantity'],
							'location' => $product_info['location'],
							'sku'      => $product_info['sku'],
							'upc'      => $product_info['upc'],
							'ean'      => $product_info['ean'],
							'jan'      => $product_info['jan'],
							'isbn'     => $product_info['isbn'],
							'mpn'      => $product_info['mpn'],
							'weight'   => $this->weight->format(($product_info['weight'] + (float)$option_weight) * $product['quantity'], $product_info['weight_class_id'], $this->language->get('decimal_point'), $this->language->get('thousand_point'))
						);
					}
				}

				$data['orders'][] = array(
					'order_id'	       => $order_id,
					'invoice_no'       => $invoice_no,
					'date_added'       => date($this->language->get('date_format_short'), strtotime($order_info['date_added'])),
					'store_name'       => $order_info['store_name'],
					'store_url'        => rtrim($order_info['store_url'], '/'),
					'store_address'    => nl2br($store_address),
					'store_email'      => $store_email,
					'store_telephone'  => $store_telephone,
					'email'            => $order_info['email'],
					'telephone'        => $order_info['telephone'],
					'shipping_address' => $shipping_address,
					'shipping_method'  => $order_info['shipping_method'],
					'product'          => $product_data,
					'comment'          => nl2br($order_info['comment'])
				);
			}
		}

		$this->response->setOutput($this->load->view('account/customerpartner/order_shipping', $data));
	}
	public function validate(){
		$this->load->language('account/customerpartner/orderinfo');
		if(isset($this->request->post['tracking']) && $this->request->post['tracking'])
		foreach ($this->request->post['tracking'] as $key => $value) {
			if(!$value || ctype_space($value) || !is_numeric($value) || $value < 0){
				$this->session->data['warning'] =  $this->language->get('error_order_information');
			}
		}
		if(isset($this->session->data['warning']) && $this->session->data['warning']){
			return false;
		}else{
			return true;
		}
	}
}
?>
