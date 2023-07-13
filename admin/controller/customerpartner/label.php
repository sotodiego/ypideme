<?php
class ControllerCustomerpartnerLabel extends Controller {
    private $data = array();
    
	public function index() {

        $data = array();
    
        $data = array_merge($data,$this->load->language('customerpartner/order'));
     
        $data['shipping_applied'] = 0;

        $this->load->model('tool/image');

        $this->load->model('customerpartner/order');
        
		if (isset($this->request->get['order_id'])) {
			$order_id = (int)$this->request->get['order_id'];
		} else {
            $order_id = 0;
            $this->response->redirect($this->url->link('customerpartner/order/info&order_id='.$order_id,'user_token=' . $this->session->data['user_token'], true));
		}
        
		$data['order_info'] = $order_info = $this->model_customerpartner_order->getOrder($order_id);

		if ($order_info) {

            $data['order_id'] = $order_id;
            
            $data['seller_info'] = $this->model_customerpartner_order->getSellerAddress();

			if ($order_info['invoice_no']) {
				$data['invoice_no'] = $order_info['invoice_prefix'] . $order_info['invoice_no'];
			} else {
				$data['invoice_no'] = $order_info['invoice_prefix'] .'-'. $order_info['order_id'];;
            }
            
            if ($order_info['shipping_applied']) {
                $data['shipping_applied'] = $this->currency->format($order_info['shipping_applied'], $order_info['currency_code'], $order_info['currency_value']);
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

			$data['products'] = array();

			$products = $this->model_customerpartner_order->getSellerOrderProducts($order_id);

			// Uploaded files
			$this->load->model('tool/upload');

      		foreach ($products as $product) {
				$option_data = array();

				$options = $this->model_customerpartner_order->getOrderOptions($order_id, $product['order_product_id']);

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

                $product_tracking = $this->model_customerpartner_order->getOdrTracking($data['order_id'],$product['product_id']);
               
                $data['seller_info'] = $this->model_customerpartner_order->getSellerAddress($product['product_id']);
				
				if ($this->config->get('shipping_wk_custom_shipping_seller_custom_logo')) {
					$seller_image = $this->model_customerpartner_order->getSellerLogo($product['product_id']);
	
					if ($seller_image && file_exists(DIR_IMAGE . $seller_image)) {
						$data['seller_image'] = $this->model_tool_image->resize($seller_image, 100, 100);
					}
				}

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

      		$data['order_status_id'] = $order_info['order_status_id'];

      		$data['user_token'] = $this->session->data['user_token'];
        }
        
        if ($this->config->get('config_image')) {
			$data['store_image'] = $this->model_tool_image->resize($this->config->get('config_image'), 100, 100);
		} else if (file_exists(DIR_IMAGE . 'no_image.png')) {
			$data['store_image'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}
		
		if(isset($data['seller_image'])) {
			$data['store_image'] = $data['seller_image'];
		}
	
        if ($this->request->server['HTTPS']) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}
		
        $data['direction'] = 'ltr';
		$data['lang'] = 'en';
		$data['base'] = $server;
        $data['store_name'] = $this->config->get('config_name');
        
        $this->response->setOutput($this->load->view('customerpartner/label' , $data));
    }

}

