<?php
class ControllerExtensionModuleCategoryTab extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/category_tab');

		$this->load->model('catalog/category');

		$this->load->model('catalog/product');
		
		$this->load->model('tool/image');

		$data['categories'] = array();

		if (!$setting['limit']) {
			$setting['limit'] = 4;
		}

		if (!empty($setting['category'])) {
			//$categories = array_slice($setting['category'], 0, (int)$setting['limit']);

			$categories = $setting['category'];
			$data['template_name'] = $setting['name'];


			foreach ($categories as $category_id) {

				$category = $this->model_catalog_category->getCategory($category_id);

				$datainfo['name'] = $category['name'];
				
				if ($category['catimg']) {
					$img = $this->model_tool_image->resize($category['catimg'], $setting['module_category_width'], $setting['module_category_height']);
				} else {
					$img = $this->model_tool_image->resize('placeholder.png', $setting['module_category_width'], $setting['module_category_height']);
				}

				$filter_data = array(
					'filter_category_id'  => $category_id,
					'filter_sub_category' => true,
					'limit'               => (int)$setting['limit'],
					'start'               => 0
				);

				$category_info = $this->model_catalog_product->getProducts($filter_data);

				if ($category_info) {
					$datainfo['products'] = array(); // reset datainfo ['products'] so that there is no duplicate
					foreach ($category_info as $key => $value) {
 						if ($value['image']) {
							$image = $this->model_tool_image->resize($value['image'], $setting['width'], $setting['height']);
						} else {
							$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
						}
						
						$images = $this->model_catalog_product->getProductImages($value['product_id']);
				   
						if(isset($images[0]['image']) && !empty($images[0]['image'])){
						  $images = $images[0]['image'];
						  $thumb_swap = $this->model_tool_image->resize($images, $setting['width'], $setting['height']);
						} else {
						  $thumb_swap="";
						}


						if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
							$price = $this->currency->format($this->tax->calculate($value['price'], $value['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
						} else {
							$price = false;
						}

						if ((float)$value['special']) {
							$special = $this->currency->format($this->tax->calculate($value['special'], $value['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
						} else {
							$special = false;
						}
						
						if ((float)$value['special']) {
							$data['percent'] = round(100 - ($value['special']*100/$value['price']));
						} else {
							$data['percent'] = false;
						}

						$datainfo['products'][] = array(
							'product_id'  => $value['product_id'],
							'thumb'       => $image,
							'thumb_swap'  => $thumb_swap,
							'name'        => $value['name'],
							'description' => utf8_substr(strip_tags(html_entity_decode($value['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
							'price'       => $price,
							'percent'     => $data['percent'],
							'special'     => $special,
							'rating'      => $value['rating'],
							'product_quantity'  => $value['quantity'],
							'product_stock'  => $value['stock_status'],
							'text_stock'  => $this->language->get('text_stock'),
							'href'        => $this->url->link('product/product', 'product_id=' . $value['product_id'])
						);
					}
				}
				$data['categories'][] = $datainfo;

				$data['category'][] = array(
					'name'        => $datainfo['name'],
					'cat_img'     => $img				
				);
			}
		}

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_theme') . '/template/extension/module/category_tab.twig')) {
	      return $this->load->view('extension/module/category_tab', $data);
	  	} else {
		    return;
	  	}
	}
}