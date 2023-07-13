<?php
class ControllerExtensionModuleMahardhiProductTab extends Controller {
	public function index($setting) {

		if(!isset($this->request->get['route']) || $this->request->get['route'] != 'product/product'){
			$this->document->addScript('catalog/view/javascript/mahardhi/tabs.js');
		}

		$this->language->load('extension/module/mahardhi_product_tab');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');

		//Latest Products
		$data['latest_products'] = array();
		if($setting['latest_products']) {

			$filter_data = array(
				'sort'  => 'p.date_added',
				'order' => 'DESC',
				'start' => 0,
				'limit' => $setting['limit']
			);

			$latest_results = $this->model_catalog_product->getLatestProducts($setting['limit']);

			if ($latest_results) {
				foreach ($latest_results as $result) {
					if ($result['image']) {
						$image = $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height']);
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
					}

					//added for image swap				
					$images = $this->model_catalog_product->getProductImages($result['product_id']);
	
					if(isset($images[0]['image']) && !empty($images)){
						$images = $images[0]['image'];
					}else
				    {
				     $images = $image;
				    }						
					//

					if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
						$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$price = false;
					}

					if ((float)$result['special']) {
						$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$special = false;
					}

					if ($this->config->get('config_tax')) {
						$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
					} else {
						$tax = false;
					}

					if ($this->config->get('config_review_status')) {
						$rating = $result['rating'];
					} else {
						$rating = false;
					}

					$data['latest_products'][] = array(
						'product_id'    => $result['product_id'],
						'thumb'         => $image,
						'thumb_swap'  => $this->model_tool_image->resize($images , $setting['width'], $setting['height']),
						'name'          => $result['name'],
						'description'   => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
						'price'         => $price,
						'special'       => $special,
						// 'percentsaving' => round((($result['price'] - $result['special'])/$result['price'])*100, 0),
						'tax'           => $tax,
						'rating'        => $rating,
						'href'          => $this->url->link('product/product', 'product_id=' . $result['product_id'])
					);
				}
			}
		}

		// Special Products
		$data['special_products'] = array();
		if($setting['special_products']) {

			$special_data = array(
				'sort'  => 'pd.name',
				'order' => 'ASC',
				'start' => 0,
				'limit' => $setting['limit']
			);

			$special_results = $this->model_catalog_product->getProductSpecials($special_data);

			if ($special_results) {
				foreach ($special_results as $result) {
					if ($result['image']) {
						$image = $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height']);
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
					}

					//added for image swap				
					$images = $this->model_catalog_product->getProductImages($result['product_id']);
	
					if(isset($images[0]['image']) && !empty($images)){
						$images = $images[0]['image'];
					}else
				    {
				     $images = $image;
				    }						
					//

					if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
						$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$price = false;
					}

					if ((float)$result['special']) {
						$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$special = false;
					}

					if ($this->config->get('config_tax')) {
						$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
					} else {
						$tax = false;
					}

					if ($this->config->get('config_review_status')) {
						$rating = $result['rating'];
					} else {
						$rating = false;
					}

					$data['special_products'][] = array(
						'product_id'  => $result['product_id'],
						'thumb'       => $image,
						'thumb_swap'  => $this->model_tool_image->resize($images , $setting['width'], $setting['height']),
						'name'        => $result['name'],
						'description' => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',	 
						'price'       => $price,
						'special'     => $special,
						'tax'         => $tax,
						'rating'      => $rating,
						'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'])
					);
				}
			}
		}

		// BestSeller
		$data['bestseller_products'] = array();
		if($setting['bestseller_products']) {

			$results = $this->model_catalog_product->getBestSellerProducts($setting['limit']);

			if ($results) {
				foreach ($results as $result) {
					if ($result['image']) {
						$image = $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height']);
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
					}

					//added for image swap				
					$images = $this->model_catalog_product->getProductImages($result['product_id']);
	
					if(isset($images[0]['image']) && !empty($images)){
						$images = $images[0]['image'];
					}else
				    {
				     $images = $image;
				    }						
					//

					if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
						$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$price = false;
					}

					if ((float)$result['special']) {
						$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$special = false;
					}

					if ($this->config->get('config_tax')) {
						$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
					} else {
						$tax = false;
					}

					if ($this->config->get('config_review_status')) {
						$rating = $result['rating'];
					} else {
						$rating = false;
					}

					$data['bestseller_products'][] = array(
						'product_id'  => $result['product_id'],
						'thumb'       => $image,
						'thumb_swap'  => $this->model_tool_image->resize($images , $setting['width'], $setting['height']),
						'name'        => $result['name'],
						'description' => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',

						'price'       => $price,
						'special'     => $special,
						'tax'         => $tax,
						'rating'      => $rating,
						'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id']),
						'quickview'        => $this->url->link('product/quick_view', 'product_id=' . $result['product_id'])
					);
				}
			}
		}

		// Featured Products
		$data['featured_products'] = array();
		if($setting['featured_products'] && $setting['product']) {
			
			if (!empty($setting['product'])) {
				$products = array_slice($setting['product'], 0, (int)$setting['limit']);
				foreach ($products as $product_id) {
					$product_info = $this->model_catalog_product->getProduct($product_id);

					if ($product_info) {
						if ($product_info['image']) {
							$image = $this->model_tool_image->resize($product_info['image'], $setting['width'], $setting['height']);
						} else {
							$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
						}

						//added for image swap				
						$images = $this->model_catalog_product->getProductImages($result['product_id']);
		
						if(isset($images[0]['image']) && !empty($images)){
							$images = $images[0]['image'];
						}else
					    {
					     $images = $image;
					    }						
						//

						if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
							$price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
						} else {
							$price = false;
						}

						if ((float)$product_info['special']) {
							$special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
						} else {
							$special = false;
						}

						if ($this->config->get('config_tax')) {
							$tax = $this->currency->format((float)$product_info['special'] ? $product_info['special'] : $product_info['price'], $this->session->data['currency']);
						} else {
							$tax = false;
						}

						if ($this->config->get('config_review_status')) {
							$rating = $product_info['rating'];
						} else {
							$rating = false;
						}

						$data['featured_products'][] = array(
							'product_id'  => $product_info['product_id'],
							'thumb_swap'  => $this->model_tool_image->resize($images , $setting['width'], $setting['height']),
							'thumb'       => $image,
							'name'        => $product_info['name'],
							'description' => utf8_substr(strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
							'price'       => $price,
							'special'     => $special,
							'tax'         => $tax,
							'rating'      => $rating,
							'href'        => $this->url->link('product/product', 'product_id=' . $product_info['product_id'])
						);
					}
				}
			}
		}		
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_theme') . '/template/extension/module/mahardhi_product_tab.twig')) {
			return $this->load->view('extension/module/mahardhi_product_tab', $data);
		} else {
			return;
		}

	}
}
