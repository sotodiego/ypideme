<?php 
/**
 * @version [Supported opencart version 3.x.x.x.]
 * @category Webkul
 * @package Opencart Marketplace Module Add Order controller
 * @author [Webkul] <[<http://webkul.com/>]>
 * @copyright Copyright (c) 2010-2019 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

class ControllerAccountCustomerpartnerAddOrder extends Controller {

    private $data = array();
    private $error = array();

    public function __construct($registry){
        parent::__construct($registry);

        $this->data = $this->load->language('account/customerpartner/add_order');

        $this->load->model('account/customerpartner/add_order');

        $this->document->addStyle('catalog/view/theme/default/stylesheet/MP/sell.css');

        if (!$this->customer->isLogged() || !$this->config->get('module_marketplace_status')) {
			$this->session->data['redirect'] = $this->url->link('account/customerpartner/add_order', '', true);
			$this->response->redirect($this->url->link('account/login', '', true));
        }

        if (!$this->config->get('marketplace_seller_create_order')) {
			$this->response->redirect($this->url->link('account/account', '', true));
        }
        
        $this->load->library('ocutilities');
    }

    public function index() {

        $this->load->model('account/customerpartner');

		$this->data['chkIsPartner'] = $this->model_account_customerpartner->chkIsPartner();

		if(!$this->data['chkIsPartner'] || (isset($this->session->data['marketplace_seller_mode']) && !$this->session->data['marketplace_seller_mode'])) {
            $this->response->redirect($this->url->link('account/account', '', true));
		}
		if(isset($this->session->data['coupon'])) {
			unset($this->session->data['coupon']);
		}
		if(isset($this->session->data['reward'])) {
			unset($this->session->data['reward']);
		}
		if(isset($this->session->data['voucher'])) {
			unset($this->session->data['voucher']);
		}
		
		$this->cart->clear();
		$this->session->data['vouchers'] = array();

        $this->document->setTitle($this->data['heading_title']);

        $this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
        	'text'      => $this->data['text_home'],
			'href'      => $this->url->link('common/home', '', true),
        	'separator' => false
      	);

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->data['text_account'],
			'href'      => $this->url->link('account/account', '', true),
        	'separator' => $this->data['text_separator'],
      	);

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->data['heading_title'],
			'href'      => $this->url->link('account/customerpartner/add_order', '', true),
        	'separator' => $this->data['text_separator'],
        );

        $this->data['cancel'] = $this->url->link('account/customerpartner/orderlist','',true);

        $this->data['store_id'] = $this->config->get('config_store_id');

        $this->load->model('localisation/country');

		$this->data['countries'] = $this->model_localisation_country->getCountries();

		$this->load->model('localisation/currency');

		$this->data['currencies'] = $this->model_localisation_currency->getCurrencies();
		
		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();


		// Custom Fields
		$this->load->model('account/custom_field');

		$this->data['custom_fields'] = array();

		$filter_data = array(
			'sort'  => 'cf.sort_order',
			'order' => 'ASC'
		);

		$custom_fields = $this->model_account_custom_field->getCustomFields($filter_data);

		foreach ($custom_fields as $custom_field) {
			$this->data['custom_fields'][] = array(
				'custom_field_id'    => $custom_field['custom_field_id'],
				'custom_field_value' => $custom_field['custom_field_value'],
				'name'               => $custom_field['name'],
				'value'              => $custom_field['value'],
				'type'               => $custom_field['type'],
				'location'           => $custom_field['location'],
				'sort_order'         => $custom_field['sort_order']
			);
		}
        
        $api_info = $this->model_account_customerpartner_add_order->getApi($this->config->get('config_api_id'));

		if ($api_info) {
			$session = $this->session;
					
			$this->model_account_customerpartner_add_order->deleteApiSessionBySessonId($session->getId());
			
			$this->model_account_customerpartner_add_order->addApiSession($api_info['api_id'], $session->getId(), $this->request->server['REMOTE_ADDR']);
			
			$session->data['api_id'] = $api_info['api_id'];

			$this->data['api_token'] = $session->getId();
		} else {
			$this->data['api_token'] = '';
		}

        $this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['content_top'] = $this->load->controller('common/content_top');
		$this->data['content_bottom'] = $this->load->controller('common/content_bottom');
		$this->data['footer'] = $this->load->controller('common/footer');
		$this->data['header'] = $this->load->controller('common/header');

        $this->data['separate_view'] = false;

        $this->data['separate_column_left'] = '';

        if ($this->config->get('marketplace_separate_view') && isset($this->session->data['marketplace_separate_view']) && $this->session->data['marketplace_separate_view'] == 'separate') {
            $this->data['separate_view'] = true;
            $this->data['column_left'] = '';
            $this->data['column_right'] = '';
            $this->data['content_top'] = '';
            $this->data['content_bottom'] = '';
            $this->data['separate_column_left'] = $this->load->controller('account/customerpartner/column_left');
            $this->data['footer'] = $this->load->controller('account/customerpartner/footer');
            $this->data['header'] = $this->load->controller('account/customerpartner/header');
        }

        $this->response->setOutput($this->load->view('account/customerpartner/add_order',$this->data));
    }

    public function autocompleteCustomer() {
		$json = array();

		if (isset($this->request->get['filter_name']) || isset($this->request->get['filter_email'])) {
			if (isset($this->request->get['filter_name'])) {
				$filter_name = $this->request->get['filter_name'];
			} else {
				$filter_name = '';
			}

			if (isset($this->request->get['filter_email'])) {
				$filter_email = $this->request->get['filter_email'];
			} else {
				$filter_email = '';
			}
			
			if (isset($this->request->get['filter_affiliate'])) {
				$filter_affiliate = $this->request->get['filter_affiliate'];
			} else {
				$filter_affiliate = '';
			}

			$filter_data = array(
				'filter_name'      => $filter_name,
				'filter_email'     => $filter_email,
				'filter_affiliate' => $filter_affiliate,
				'start'            => 0,
				'limit'            => 5
			);

			$results = $this->model_account_customerpartner_add_order->getCustomers($filter_data);

			foreach ($results as $result) {
				$json[] = array(
					'customer_id'       => $result['customer_id'],
					'customer_group_id' => $result['customer_group_id'],
					'name'              => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
					'customer_group'    => $result['customer_group'],
					'firstname'         => $result['firstname'],
					'lastname'          => $result['lastname'],
					'email'             => $result['email'],
					'telephone'         => $result['telephone'],
					'custom_field'      => json_decode($result['custom_field'], true),
					'address'           => $this->model_account_customerpartner_add_order->getAddresses($result['customer_id'])
				);
			}
		}

		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
    }
    
    public function autocompleteProduct() {
		$json = array();

		if (isset($this->request->get['filter_name']) || isset($this->request->get['filter_model'])) {
			$this->load->model('catalog/product');
			$this->load->model('account/customerpartner');

			if (isset($this->request->get['filter_name'])) {
				$filter_name = $this->request->get['filter_name'];
			} else {
				$filter_name = '';
			}

			if (isset($this->request->get['filter_model'])) {
				$filter_model = $this->request->get['filter_model'];
			} else {
				$filter_model = '';
			}

			if (isset($this->request->get['limit'])) {
				$limit = $this->request->get['limit'];
			} else {
				$limit = 5;
			}

			$filter_data = array(
				'filter_name'  => $filter_name,
				'filter_model' => $filter_model,
				'start'        => 0,
				'limit'        => $limit
			);

			$results = $this->model_account_customerpartner->getProductsSeller($filter_data);

			foreach ($results as $result) {
				$option_data = array();

				$product_options = $this->model_catalog_product->getProductOptions($result['product_id']);

				foreach ($product_options as $product_option) {
					$option_info = $this->model_account_customerpartner_add_order->getOption($product_option['option_id']);

					if ($option_info) {
						$product_option_value_data = array();

						foreach ($product_option['product_option_value'] as $product_option_value) {
							$option_value_info = $this->model_account_customerpartner_add_order->getOptionValue($product_option_value['option_value_id']);

							if ($option_value_info) {
								$product_option_value_data[] = array(
									'product_option_value_id' => $product_option_value['product_option_value_id'],
									'option_value_id'         => $product_option_value['option_value_id'],
									'name'                    => $option_value_info['name'],
									'price'                   => (float)$product_option_value['price'] ? $this->currency->format($product_option_value['price'], $this->config->get('config_currency')) : false,
									'price_prefix'            => $product_option_value['price_prefix']
								);
							}
						}

						$option_data[] = array(
							'product_option_id'    => $product_option['product_option_id'],
							'product_option_value' => $product_option_value_data,
							'option_id'            => $product_option['option_id'],
							'name'                 => $option_info['name'],
							'type'                 => $option_info['type'],
							'value'                => $product_option['value'],
							'required'             => $product_option['required']
						);
					}
				}

				$json[] = array(
					'product_id' => $result['product_id'],
					'name'       => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
					'model'      => $result['model'],
					'option'     => $option_data,
					'price'      => $result['price']
				);
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function country() {
		$json = array();

		$this->load->model('localisation/country');

		$country_info = $this->model_localisation_country->getCountry($this->request->get['country_id']);

		if ($country_info) {
			$this->load->model('localisation/zone');

			$json = array(
				'country_id'        => $country_info['country_id'],
				'name'              => $country_info['name'],
				'iso_code_2'        => $country_info['iso_code_2'],
				'iso_code_3'        => $country_info['iso_code_3'],
				'address_format'    => $country_info['address_format'],
				'postcode_required' => $country_info['postcode_required'],
				'zone'              => $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']),
				'status'            => $country_info['status']
			);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function address() {
		$json = array();

		if (!empty($this->request->get['address_id'])) {
			$this->load->model('account/customerpartner/add_order');

			$json = $this->model_account_customerpartner_add_order->getAddress($this->request->get['address_id']);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function customfield() {
		$json = array();

		$this->load->model('account/custom_field');

		// Customer Group
		if (isset($this->request->get['customer_group_id'])) {
			$customer_group_id = $this->request->get['customer_group_id'];
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

		$custom_fields = $this->model_account_custom_field->getCustomFields(array('filter_customer_group_id' => $customer_group_id));

		foreach ($custom_fields as $custom_field) {
			$json[] = array(
				'custom_field_id' => $custom_field['custom_field_id'],
				'required'        => empty($custom_field['required']) || $custom_field['required'] == 0 ? false : true
			);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}