<?php
class ControllerAccountCustomerpartnerShippingLabel extends Controller {

	private $data = array();

	public function index() {

    if (!$this->customer->isLogged()) {
			$this->response->redirect($this->url->link('account/login', '', true));
		}

		if ($this->config->get('wk_custom_shipping_label_status') && $this->config->get('wk_custom_shipping_seller_label') && $this->config->get('marketplace_status')) {
			
		}

    $data = $this->language->load('account/customerpartner/orderinfo');
    $this->load->model('account/customerpartner');
		$this->load->model('tool/image');

		$data['chkIsPartner'] = $this->model_account_customerpartner->chkIsPartner();
		$data['order_id'] =0;
		$data['shipping_applied'] = 0;
		if(!$data['chkIsPartner'] || (isset($this->session->data['marketplace_seller_mode']) && !$this->session->data['marketplace_seller_mode']))
			$this->response->redirect($this->url->link('account/account', '', true));

    if (isset($this->request->get['order_id']) && $this->request->get['order_id']) {
			$data['order_id'] = $this->request->get['order_id'];
			$order_info =	$data['order_info'] = $this->model_account_customerpartner->getOrder($this->request->get['order_id']);
			if($order_info){

				$data['errorPage'] = false;
				$data['name'] = $order_info['firstname'].' '.$order_info['lastname'];
				$data['email'] = $order_info['email'];
	
				if ($order_info['invoice_no']) {
					$data['invoice_no'] = $order_info['invoice_prefix'] . $order_info['invoice_no'];
				} else {
					$data['invoice_no'] = $order_info['invoice_prefix'].'-'.$data['order_id'];
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
	
				$this->load->model('account/address');
				$store_address = $this->model_account_address->getAddress($this->customer->getAddressId());
				$seller_details = $this->model_account_customerpartner->getProfile();
	
				$replace_store_address = array(
						'firstname' => $store_address['firstname'],
						'lastname' => $store_address['lastname'],
						'company' => $seller_details['companyname'],
						'address_1' => $store_address['address_1'],
						'address_2' => $store_address['address_2'],
						'city' => $store_address['city'],
						'postcode' => $store_address['postcode'],
						'zone' => $store_address['zone'],
						'zone_code' => $store_address['zone_code'],
						'country' => $store_address['country']
				);
	
				$formatted_store_address = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace_store_address, $format))));
						$data['payment_method'] = $order_info['payment_method'];
				$order_info['store_address'] = $formatted_store_address;
				$order_info['store_email'] = $this->customer->getEmail();
				$order_info['store_fax'] = '';
				$order_info['store_telephone'] = $this->customer->getTelephone();
				$order_info['store_url'] = $this->url->link('customerpartner/profile&id='.$this->customer->getId(), '', true);
				
				if ($order_info['shipping_applied']) {
					$data['shipping_applied'] = $this->currency->format($order_info['shipping_applied'], $order_info['currency_code'], $order_info['currency_value']);
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
	
				$data['shipping_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));
	
				$data['shipping_method'] = $order_info['shipping_method'];
			}

			if (!$data['order_info']) 
				$this->response->redirect($this->url->link('account/account', '', true));
			
				
			$data['seller_info'] = $this->model_account_customerpartner->getSellerAddress();

		
			$products = $this->model_account_customerpartner->getSellerOrderProducts($this->request->get['order_id']);
			
			 if ($this->config->get('shipping_wk_custom_shipping_seller_custom_logo')) {
				$seller_image = $this->model_account_customerpartner->getSellerLogo();

				if ($seller_image && file_exists(DIR_IMAGE . $seller_image)) {
					$data['seller_image'] = $this->model_tool_image->resize($seller_image, 100, 100);
				}
			}

			$data['products'] = $products;
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

    $this->document->setTitle($this->language->get('text_order'));
    $this->response->setOutput($this->load->view('account/customerpartner/shippinglabel' , $data));
  }

}
?>
