<?php
class ControllerExtensionModuleMahardhiSearch extends Controller {
	public function index() {
		$this->load->model('catalog/product');
		$this->load->model('catalog/category');
		$this->load->model('tool/image');
		$this->load->language('extension/module/mahardhi_search');
		$data['text_search'] = $this->language->get('text_search');
		$data['text_category'] = $this->language->get('text_category');

		if (isset($this->request->get['category_id'])) {
			$category_id = $this->request->get['category_id'];
		} else {
			$category_id = 0;
		}
		$data['category_id'] = $category_id;

		// 3 Level Category Search
		$data['categories'] = array();
		$categories_1 = $this->model_catalog_category->getCategories(0);
		foreach ($categories_1 as $category_1) {
			$level_2_data = array();
			$categories_2 = $this->model_catalog_category->getCategories($category_1['category_id']);

			foreach ($categories_2 as $category_2) {
				$level_3_data = array();
				$categories_3 = $this->model_catalog_category->getCategories($category_2['category_id']);

				foreach ($categories_3 as $category_3) {
					$level_3_data[] = array(
						'category_id' => $category_3['category_id'],
						'name'        => $category_3['name'],
					);
				}

				$level_2_data[] = array(
					'category_id' => $category_2['category_id'],
					'name'        => $category_2['name'],
					'children'    => $level_3_data
				);
			}

			$data['categories'][] = array(
				'category_id' => $category_1['category_id'],
				'name'        => $category_1['name'],
				'children'    => $level_2_data
			);
		}

		$data['products'] = array();
		if (isset($this->request->get['filter_name']) && strlen($this->request->get['filter_name']) >= 1) {
			$filter_category = isset($this->request->get['filter_category']) ? $this->request->get['filter_category'] : 0;
			$data_search = array(
				'filter_name' => $this->request->get['filter_name'],
				'filter_category_id' => $filter_category,
				'start'       => 0,
				'limit'       => 5
			);
			$results = $this->model_catalog_product->getProducts($data_search);

			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_height'));
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_height'));
				}

				if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if ((float)$result['special']) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}

				$data['products'][] = array(
					'name'    => $result['name'],
					'image'   => $image,
					'price'   => $price,
					'special' => $special,
					'href'    => $this->url->link('product/product', 'product_id=' . $result['product_id']),
				);
			}
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($data['products']));
		}

		$data['categorySearch'] = $this->config->get('module_mahardhi_search_category');
		
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_theme') . '/template/extension/module/mahardhi_search.twig')) {
			return $this->load->view('extension/module/mahardhi_search', $data);
		} else {
			return;
		}
	}
}
