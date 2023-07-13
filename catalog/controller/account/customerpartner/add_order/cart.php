<?php
/**
 * @version [Supported opencart version 3.x.x.x.]
 * @category Webkul
 * @package Opencart Marketplace Module Add Order controller
 * @author [Webkul] <[<http://webkul.com/>]>
 * @copyright Copyright (c) 2010-2019 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

class ControllerAccountCustomerpartnerAddOrderCart extends Controller {

	private $order_currency = '';
	
    public function __construct($registry) {
       parent::__construct($registry); 

       $this->load->model('account/customerpartner/add_order/cart');

	   $this->add_order_cart = $this->model_account_customerpartner_add_order_cart;
	   
	   $this->order_currency = (isset($this->session->data['add_order_customer']['currency'])) ? $this->session->data['add_order_customer']['currency']: $this->session->data['currency'];
    }

    public function add() {
		$this->load->language('api/cart');

        $json = array();
					
		if (isset($this->request->post['product'])) {
                
            $this->add_order_cart->clear();

			foreach ($this->request->post['product'] as $product) {
				if (isset($product['option'])) {
					$option = $product['option'];
				} else {
					$option = array();
				}

				$this->add_order_cart->add($product['product_id'], $product['quantity'], $option);
			}

			$json['success'] = $this->language->get('text_success');

			unset($this->session->data['add_order_customer']['shipping_method']);
			unset($this->session->data['add_order_customer']['shipping_methods']);
			unset($this->session->data['add_order_customer']['payment_method']);
			unset($this->session->data['add_order_customer']['payment_methods']);
		} elseif (isset($this->request->post['product_id'])) {
                
            $this->load->model('catalog/product');

			$product_info = $this->model_catalog_product->getProduct($this->request->post['product_id']);

			if ($product_info) {
				if (isset($this->request->post['quantity'])) {
					$quantity = $this->request->post['quantity'];
				} else {
					$quantity = 1;
				}

				if (isset($this->request->post['option'])) {
					$option = array_filter($this->request->post['option']);
				} else {
					$option = array();
				}

				$product_options = $this->model_catalog_product->getProductOptions($this->request->post['product_id']);

				foreach ($product_options as $product_option) {
					if ($product_option['required'] && empty($option[$product_option['product_option_id']])) {
						$json['error']['option'][$product_option['product_option_id']] = sprintf($this->language->get('error_required'), $product_option['name']);
					}
				}

				if (!isset($json['error']['option'])) {
					$this->add_order_cart->add($this->request->post['product_id'], $quantity, $option);

					$json['success'] = $this->language->get('text_success');

					unset($this->session->data['add_order_customer']['shipping_method']);
					unset($this->session->data['add_order_customer']['shipping_methods']);
					unset($this->session->data['add_order_customer']['payment_method']);
					unset($this->session->data['add_order_customer']['payment_methods']);
				}
			} else {
				$json['error']['store'] = $this->language->get('error_store');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function edit() {
		$this->load->language('api/cart');

        $json = array();
        
		$this->add_order_cart->update($this->request->post['key'], $this->request->post['quantity']);

		$json['success'] = $this->language->get('text_success');

		unset($this->session->data['add_order_customer']['shipping_method']);
		unset($this->session->data['add_order_customer']['shipping_methods']);
		unset($this->session->data['add_order_customer']['payment_method']);
		unset($this->session->data['add_order_customer']['payment_methods']);
		unset($this->session->data['add_order_customer']['reward']);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function remove() {
		$this->load->language('api/cart');

		$json = array();
            // Remove
            
		if (isset($this->request->post['key'])) {
			$this->add_order_cart->remove($this->request->post['key']);

			unset($this->session->data['add_order_customer']['vouchers'][$this->request->post['key']]);

			$json['success'] = $this->language->get('text_success');

			unset($this->session->data['add_order_customer']['shipping_method']);
			unset($this->session->data['add_order_customer']['shipping_methods']);
			unset($this->session->data['add_order_customer']['payment_method']);
			unset($this->session->data['add_order_customer']['payment_methods']);
			unset($this->session->data['add_order_customer']['reward']);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function products() {
		$this->load->language('api/cart');

		$json = array();
            
        // Stock
		if (!$this->add_order_cart->hasStock() && (!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning'))) {
			$json['error']['stock'] = $this->language->get('error_stock');
		}

		// Products
		$json['products'] = array();

		$products = $this->add_order_cart->getProducts();

		foreach ($products as $product) {
		    $product_total = 0;

			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}

			if ($product['minimum'] > $product_total) {
				$json['error']['minimum'][] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);
			}

			$option_data = array();

			foreach ($product['option'] as $option) {
				$option_data[] = array(
					'product_option_id'       => $option['product_option_id'],
					'product_option_value_id' => $option['product_option_value_id'],
					'name'                    => $option['name'],
					'value'                   => $option['value'],
					'type'                    => $option['type']
				);
			}

			$json['products'][] = array(
				'cart_id'    => $product['cart_id'],
				'product_id' => $product['product_id'],
				'name'       => $product['name'],
				'model'      => $product['model'],
				'option'     => $option_data,
				'quantity'   => $product['quantity'],
				'stock'      => $product['stock'] ? true : !(!$this->config->get('config_stock_checkout') ||$this->config->get('config_stock_warning')),
				'shipping'   => $product['shipping'],
				'price'      => $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->order_currency),
				'total'      => $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) * $product['quantity'], $this->order_currency),
				'reward'     => $product['reward']
			);
		}

		// Voucher
		$json['vouchers'] = array();

		if (!empty($this->session->data['add_order_customer']['vouchers'])) {
		    foreach ($this->session->data['add_order_customer']['vouchers'] as $key => $voucher) {
				$json['vouchers'][] = array(
					'code'             => $voucher['code'],
					'description'      => $voucher['description'],
					'from_name'        => $voucher['from_name'],
					'from_email'       => $voucher['from_email'],
					'to_name'          => $voucher['to_name'],
					'to_email'         => $voucher['to_email'],
					'voucher_theme_id' => $voucher['voucher_theme_id'],
					'message'          => $voucher['message'],
					'price'            => $this->currency->format($voucher['amount'], $this->order_currency),
					'amount'           => $voucher['amount']
				);
			}
		}

		// Totals
		$this->load->model('setting/extension');

		$totals = array();
		$taxes = $this->add_order_cart->getTaxes();
		$total = 0;

		// Because __call can not keep var references so we put them into an array. 
		$total_data = array(
			'totals' => &$totals,
			'taxes'  => &$taxes,
			'total'  => &$total
		);
			
		$sort_order = array();

		$results = $this->model_setting_extension->getExtensions('total');

		foreach ($results as $key => $value) {
			$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
		}

		array_multisort($sort_order, SORT_ASC, $results);

		foreach ($results as $result) {
			if ($this->config->get('total_' . $result['code'] . '_status')) {				
					
				if($result['code'] == 'sub_total' || $result['code'] == 'shipping') {
					$this->load->model('account/customerpartner/add_order/' . $result['code']);
					$this->{'model_account_customerpartner_add_order_' . $result['code']}->getTotal($total_data,true);
				} else {
					
					$this->load->model('extension/total/' . $result['code']);
					// We have to put the totals in an array so that they pass by reference.
					$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
				}				
			}
		}

		$sort_order = array();

		foreach ($totals as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}

		array_multisort($sort_order, SORT_ASC, $totals);

		$json['totals'] = array();

	    foreach ($totals as $total) {
			$json['totals'][] = array(
				'title' => $total['title'],
				'text'  => $this->currency->format($total['value'], $this->order_currency)
			);
        }
            
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
?>