<?php
class ControllerAccountCustomerpartnerProductoptions extends Controller {
	public function getProductOptions() {
		$product_id = $this->request->get['product_id'];

			$data = $this->getAlloption($product_id);
			$data['product_id'] = $product_id;
			$json = $this->load->view('account/customerpartner/product_option', $data);
			$this->response->setOutput($json);
	}

	public function getAlloption($product_id) {
		$this->load->language('product/product');
		$data['text_loading'] = $this->language->get('text_loading');
		$data['button_upload'] = $this->language->get('button_upload');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');
		$product_info = $this->model_catalog_product->getProduct($product_id);

		$data['options'] = array();

		foreach ($this->model_catalog_product->getProductOptions($product_id) as $option) {
			$product_option_value_data = array();

			foreach ($option['product_option_value'] as $option_value) {
				if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
					if ((($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) && (float)$option_value['price']) {
						// $taxable = $this->tax->calculate($option_value['price'], isset($product_info['tax_class_id']) ? $product_info['tax_class_id'] : 0, $this->config->get('config_tax') ? 'P' : false, $product_id);
						$taxable = $option_value['price'];
						$price = $this->currency->format($taxable, $this->session->data['currency']);
					} else {
						$price = false;
					}
					$product_option_value_data[] = array(
						'product_option_value_id' => $option_value['product_option_value_id'],
						'option_value_id'         => $option_value['option_value_id'],
						'name'                    => $option_value['name'],
						'image'                   => $this->model_tool_image->resize($option_value['image'], 50, 50),
						'price'                   => $price,
						'price_prefix'            => $option_value['price_prefix']
					);
				}
			}
			$data['options'][] = array(
				'product_option_id'    => $option['product_option_id'],
				'product_option_value' => $product_option_value_data,
				'option_id'            => $option['option_id'],
				'name'                 => $option['name'],
				'type'                 => $option['type'],
				'value'                => $option['value'],
				'required'             => $option['required']
			);
		}
		return $data;
	}

	function addCrosssellBundle() {
		$this->load->language('account/customerpartner/product_saved_option');
		$this->load->model('account/product_saved_option');

		if (isset($this->request->post['id']) && $this->request->post['id']) {
			$related_id = $this->request->post['id'];
		} else {
			$related_id = 0;
		}

		$json = array();
		$crosssell = $this->model_account_product_saved_option->getCrosssellProductsOptions($related_id);

		if ($crosssell) {

			$parent_id = $crosssell['parent_id'];
			$child_id = $crosssell['child_id'];

			if ($crosssell['parent_options']) {
				$parent_options = json_decode($crosssell['parent_options'], true);
			} else {
				$parent_options = array();
			}

			if ($crosssell['options']) {
				$child_options = json_decode($crosssell['options'], true);
			} else {
				$child_options = array();
			}

			$cart_id = array();

			$cart_id[] = $this->cart->add($parent_id, 1, $parent_options);

			$cart_id[] = $this->cart->add($child_id, 1, $child_options);

			if(!empty($cart_id)) {
				$this->model_account_product_saved_option->mapMarketingToolProducts($cart_id, 'C', $crosssell['crosssell_id']);
			}

			$sql = "SELECT bundle_id FROM " . DB_PREFIX . "customer_bundles WHERE related_id = '" . $related_id . "'";

			if ($this->customer->isLogged()) {
				$sql .= " AND customer_id = '" . $this->customer->getId() . "'";
			} else {
				$sql .= " AND customer_id = '0' AND session_id = '" . $this->db->escape($this->session->getId()) . "'";
			}

			$exist = $this->db->query($sql)->row;

			if (isset($exist['bundle_id'])) {
				$this->db->query("UPDATE " . DB_PREFIX . "customer_bundles SET customer_id = '" . $this->customer->getId() . "', related_id = '" . $related_id . "', quantity = (quantity + 1), session_id = '" . $this->db->escape($this->session->getId()) . "' WHERE bundle_id = '" . $exist['bundle_id'] . "'");
			} else {
				$this->db->query("INSERT INTO " . DB_PREFIX . "customer_bundles SET customer_id = '" . $this->customer->getId() . "', related_id = '" . $related_id . "', quantity = '1', session_id = '" . $this->db->escape($this->session->getId()) . "'");
			}

			$json['success'] = $this->language->get('text_success_crossell_to_cart');
			$json['total'] = $this->cartTotal();
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	function addUpsellBundle() {
		$this->load->language('account/customerpartner/product_saved_option');
		$this->load->model('account/product_saved_option');

		$json = array();
		$upsell = $this->model_account_product_saved_option->getUpsellProductsOptions($this->request->post['id']);

		if ($upsell) {

			$product_id = $upsell['child_id'];
			$child_options = json_decode($upsell['options'], true);

			$cart_id = array();

			$cart_id[] = $this->cart->add($product_id, 1, $child_options);

			if(!empty($cart_id)) {
				$this->model_account_product_saved_option->mapMarketingToolProducts($cart_id, 'U', $upsell['upsell_id']);
			}

			$json['success'] = $this->language->get('text_success_upsell_to_cart');

			$json['total'] = $this->cartTotal();
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function checkOptions() {
		$json = array();
		$json['price'] = '0';
		$json['cost'] = '';
		$this->load->model('catalog/product');

		$this->load->language('checkout/cart');

		if (isset($this->request->post['option'])) {
			$option = array_filter($this->request->post['option']);
		} else {
			$option = array();
		}

		$product_info = $this->model_catalog_product->getProduct($this->request->post['product_id']);
		$product_options = $this->model_catalog_product->getProductOptions($this->request->post['product_id']);

		$json['option'] = $option;
		$json['option_name'] = '</br>'.$product_info['name'] . '( ';
		$json['product_name'] = $product_info['name'];

		foreach ($product_options as $product_option) {
			if (isset($option[$product_option['product_option_id']])) {
				$value = $option[$product_option['product_option_id']];
			} else {
				$value = '';
			}

			if ($product_option['required'] && empty($value)) {
				$json['error']['option'][$product_option['product_option_id']] = sprintf($this->language->get('error_required'), $product_option['name']);
			} else {
				if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'image') {
					foreach ($product_option['product_option_value'] as $option_value) {
						if ((float)$option_value['price']) {
							$taxable = $option_value['price'];
							$price = $this->currency->format($taxable, $this->session->data['currency']);
						} else {
							$price = false;
						}

						if ($option_value['product_option_value_id'] == $value) {
							$json['option_name'] .= $option_value['name'] . ' - ' . $price . ', ';
							$json['cost'] = $price;
							$json['price'] = $option_value['price'];
						}
					}
				} elseif ($product_option['type'] == 'checkbox' && is_array($value)) {
						foreach ($product_option['product_option_value'] as $option_value) {
								if (in_array($option_value['product_option_value_id'], $value)) {
										if ((float)$option_value['price']) {
											$taxable = $option_value['price'];
											$price = $this->currency->format($taxable, $this->session->data['currency']);
											$json['price'] = $taxable;
										} else {
											$price = false;
										}
										$json['option_name'] .= $option_value['name'] . ' - ' . $price . ', ';
										$json['cost'] = $price;
										$json['price'] = $option_value['price'];
								}
						}
				} elseif ($product_option['type'] == 'text' || $product_option['type'] == 'textarea' || $product_option['type'] == 'file' || $product_option['type'] == 'date' || $product_option['type'] == 'datetime' || $product_option['type'] == 'time') {

					$virtual_option = $this->db->query("SELECT * FROM " . DB_PREFIX . "download_description WHERE download_id = '" . $value . "' AND language_id = '" . $this->config->get('config_language_id') . "'")->row;

					if ($virtual_option && is_numeric($value) && (gettype($value) == 'integer')) {
							$option_price = $this->db->query("SELECT price FROM " . DB_PREFIX . "virtual_product_to_download WHERE download_id = '" . (int)$virtual_option['download_id'] . "'")->row;
							if (isset($option_price['price']) && $option_price['price']) {
								$taxable 	= (float)$option_price['price'];
								$price 		= $this->currency->format($taxable, $this->session->data['currency']);
								$json['price'] = $taxable;
							} else {
								$price = false;
							}
							$json['option_name'] .= $virtual_option['name'] . ' - ' . $price . ', ';
							$json['cost'] = $price;
							$json['price'] = (float)$option_price['price'];
					} elseif ($product_option['option_id'] != $this->config->get('virtual_option_id')) {
							$json['option_name'] .= $value . ', ';
					}
				}
			}
		}

		$json['option_name'] = rtrim($json['option_name'], ', ') . ' )';

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	protected function cartTotal() {
		$this->load->language('checkout/cart');
		// Unset all shipping and payment methods
		unset($this->session->data['shipping_method']);
		unset($this->session->data['shipping_methods']);
		unset($this->session->data['payment_method']);
		unset($this->session->data['payment_methods']);

		// Totals
		$this->load->model('setting/extension');

		$totals = array();
		$taxes = $this->cart->getTaxes();
		$total = 0;

		// Because __call can not keep var references so we put them into an array.
		$total_data = array(
			'totals' => &$totals,
			'taxes'  => &$taxes,
			'total'  => &$total
		);

		// Display prices
		if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
			$sort_order = array();

			$results = $this->model_setting_extension->getExtensions('total');

			foreach ($results as $key => $value) {
				$sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
			}

			array_multisort($sort_order, SORT_ASC, $results);

			foreach ($results as $result) {
				if ($this->config->get('total_' . $result['code'] . '_status')) {
					$this->load->model('extension/total/' . $result['code']);

					// We have to put the totals in an array so that they pass by reference.
					$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
				}
			}

			$sort_order = array();

			foreach ($totals as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $totals);
		}

		$cart_total = sprintf($this->language->get('text_items'), $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0), $this->currency->format($total, $this->session->data['currency']));

		return $cart_total;
	}
}
