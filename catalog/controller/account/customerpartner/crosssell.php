<?php

require_once DIR_SYSTEM . 'ocMpTrait.php';

class ControllerAccountCustomerpartnerCrosssell extends Controller {

  use OcMpTrait;

	private $error = array();

	public function index() {

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/customerpartner/crosssell', '', true);
			$this->response->redirect($this->url->link('account/login', '', true));
		}

    if (!in_array('wk_crosssell', $this->config->get('marketplace_allowed_account_menu')) || !$this->config->get('module_wk_crosssell_crosssell_status') || !$this->config->get('module_marketplace_status')) {
      $this->session->data['redirect'] = $this->url->link('account/customerpartner/crosssell', '', true);
      $this->response->redirect($this->url->link('account/account', '', true));
    }

		$data = $this->load->language('account/customerpartner/crosssell');

		$this->load->model('account/customerpartner');

		$data['chkIsPartner'] = $this->model_account_customerpartner->chkIsPartner();

		if(!$data['chkIsPartner'])
			$this->response->redirect($this->url->link('account/account'));

		// adds the time format in the cookie which can be used afterwards
		$this->document->addScript('catalog/view/javascript/promo/time_manager.js');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->getList();
	}

	public function add() {
		$this->load->language('account/customerpartner/crosssell');

    if (!$this->customer->isLogged()) {
      $this->session->data['redirect'] = $this->url->link('account/customerpartner/crosssell', '', true);
      $this->response->redirect($this->url->link('account/login', '', true));
    }

    if (!in_array('wk_crosssell', $this->config->get('marketplace_allowed_account_menu')) ||!$this->config->get('module_wk_crosssell_crosssell_status') || !$this->config->get('module_marketplace_status')) {
      $this->session->data['redirect'] = $this->url->link('account/customerpartner/crosssell', '', true);
      $this->response->redirect($this->url->link('account/account', '', true));
    }

		$this->load->model('account/customerpartner');

		$chkIsPartner = $this->model_account_customerpartner->chkIsPartner();

		if(!$chkIsPartner)
			$this->response->redirect($this->url->link('account/account', '', true));

		if($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validated()) {
			$this->load->model('account/promotional');
			$this->model_account_promotional->addCrosssell($this->request->post);
			$this->session->data['success'] = $this->language->get('text_success_add');

			$this->response->redirect($this->url->link('account/customerpartner/crosssell', '', true));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('account/customerpartner/crosssell');

    if (!$this->customer->isLogged()) {
      $this->session->data['redirect'] = $this->url->link('account/customerpartner/crosssell', '', true);
      $this->response->redirect($this->url->link('account/login', '', true));
    }

    if (!in_array('wk_crosssell', $this->config->get('marketplace_allowed_account_menu')) ||!$this->config->get('module_wk_crosssell_crosssell_status') || !$this->config->get('module_marketplace_status')) {
      $this->session->data['redirect'] = $this->url->link('account/customerpartner/crosssell', '', true);
      $this->response->redirect($this->url->link('account/account', '', true));
    }

		$this->load->model('account/customerpartner');
		$this->load->model('account/promotional');

		$chkIsPartner = $this->model_account_customerpartner->chkIsPartner();

		if(!$chkIsPartner)
			$this->response->redirect($this->url->link('account/account', '', true));

		if (isset($this->request->get['crosssell_id']) && $this->request->get['crosssell_id']) {
			$checkCrosssell = $this->model_account_promotional->getCrosssell($this->request->get['crosssell_id']);
			if (!$checkCrosssell) {
				$this->response->redirect($this->url->link('account/customerpartner/crosssell', '', true));
			}
		}

		if($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validated() && isset($this->request->get['crosssell_id'])) {
			$this->model_account_promotional->editCrosssell($this->request->post, $this->request->get['crosssell_id']);
			$this->session->data['success'] = $this->language->get('text_success_update');
			$this->response->redirect($this->url->link('account/customerpartner/crosssell', '', true));
		}
		$this->getForm();
	}

	public function delete() {
		if($this->request->server['REQUEST_METHOD'] == 'POST') {
			$this->load->language('account/customerpartner/crosssell');
			if(isset($this->request->post['selected']) && $this->request->post['selected']) {
				$this->load->model('account/promotional');
				foreach ($this->request->post['selected'] as $key => $crosssell_id) {
					$this->model_account_promotional->deleteCrosssell($crosssell_id);
				}
				$this->session->data['success'] = $this->language->get('text_success_delete');
				$this->response->redirect($this->url->link('account/customerpartner/crosssell', '', true));
			} else {
				$this->error['error_warning'] = $this->language->get('text_error_delete');
				$this->index();
			}
		}
	}

	public function getList() {

		$data = $this->load->language('account/customerpartner/crosssell');

		$data['wkIsMember'] = true;

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', '', true)
		);

    $data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('account/customerpartner/crosssell', '', true)
		);

		$errorVariable = array(
			'error_product_child',
			'error_product_childs',
			'error_product_parent'
		);

		foreach ($errorVariable as $key => $value) {
			if(isset($this->error[$value])) {
				$data[$value] = $this->error[$value];
			} else {
				$data[$value] = '';
			}
		}

		if(isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$filterValues = array();

		$this->load->model('account/promotional');
		$crosssells = $this->model_account_promotional->getAllCrosssell($filterValues);
		$crosssell_total = $this->model_account_promotional->getAllCrosssellTotal($filterValues);

		$data['crosssells'] = array();

		if($crosssells) {
			foreach ($crosssells as $key => $detail) {
				$child_products = explode(',', $detail['child_products']);

				$child_prod = array();

				$parent_prod = $this->model_account_promotional->getProductName($detail['parent_product']);

				if ($child_products)
					foreach ($child_products as $child_product) {
						$child = $this->model_account_promotional->getProductName($child_product);

						if ($child) {
							$child_prod[] = $child;
						}
					}

				if (!$parent_prod || !$child_prod) {
					continue;
				}

				$child_products = implode(', ', $child_prod);

				if (isset($_COOKIE['time_diff'])) {
					$time_diff = $_COOKIE['time_diff'] * 3600;
				}

				$date_start = strtotime($detail['date_start']);
				$date_end = strtotime($detail['date_end']);

				if (isset($time_diff) && $time_diff) {
					$date_start = $date_start + $time_diff;
					$date_end = $date_end + $time_diff;
				}

				$start_date = date('Y-m-d H:i', $date_start);
				$end_date = date('Y-m-d H:i', $date_end);

				$data['crosssells'][] = array (
					'crosssell_id'    => $detail['crosssell_id'],
					'countdown'       => $detail['countdown_status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
					'quantity'        => $detail['quantity'],
					'quantity_status' => $detail['quantity_status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
					'date_start'      => $start_date,
					'date_end'        => $end_date,
					'parent_products' => $parent_prod,
					'child_products'  => $child_products,
					'edit'            => $this->url->link('account/customerpartner/crosssell/edit', 'crosssell_id='.$detail['crosssell_id'], true)
					);
			}
		}

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$data['add'] = $this->url->link('account/customerpartner/crosssell/add', '', true);
		$data['export'] = $this->url->link('account/customerpartner/stats/export', '', true);
		$data['delete'] = $this->url->link('account/customerpartner/crosssell/delete', '', true);

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

		$this->response->setOutput($this->load->view('account/customerpartner/crosssell' , $data));
	}

	public function getForm() {
		$data = $this->load->language('account/customerpartner/crosssell');

		$this->document->setTitle($this->language->get('heading_title_insert'));
		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment.js');
		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
		$this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');
		$this->document->addStyle('catalog/view/theme/default/stylesheet/MP/sell.css');
		$this->document->addStyle('catalog/view/theme/default/stylesheet/promo/promo.css');

    if (isset($this->request->get['crosssell_id']) && $this->request->get['crosssell_id']) {
      $data['heading_title_insert'] = $this->language->get('heading_title_update');
		}

		$data['wkIsMember'] = true;

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $data['heading_title'],
			'href' => $this->url->link('account/customerpartner/crosssell', '', true)
		);

		$errorVariable = array (
			'error_product_child',
			'error_product_childs',
			'error_product_parent',
			'error_date_start',
			'error_date_end',
			'error_quantity'
		);

		foreach ($errorVariable as $key => $value) {
			if(isset($this->error[$value])) {
				$data[$value] = $this->error[$value];
			} else {
				$data[$value] = '';
			}
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['action'] = $this->url->link('account/customerpartner/crosssell/add', '', true);

		$this->load->model('account/customerpartner');

		$crosssell = array();

		if(isset($this->request->get['crosssell_id']) && $this->request->get['crosssell_id']) {
			$this->document->setTitle($this->language->get('heading_title_update'));
			$data['crosssell_id'] = $this->request->get['crosssell_id'];
			$this->load->model('account/promotional');
			$crosssell = $this->model_account_promotional->getCrosssell($this->request->get['crosssell_id']);

			$crosssell['parent_product'] = $crosssell['parent_product'];
			$crosssell['child_products'] = explode(',', $crosssell['child_products']);

			$data['action'] = $this->url->link('account/customerpartner/crosssell/edit', 'crosssell_id=' . $this->request->get['crosssell_id'] , true);
		}

		$postValues = array (
			'child_products',
			'parent_product',
			'countdown_status',
			'quantity_status',
			'quantity'
		);

		foreach ($postValues as $key => $value) {
				if(isset($this->request->post[$value])) {
					$data[$value] = $this->request->post[$value];
				} elseif (isset($crosssell[$value])) {
					$data[$value] = $crosssell[$value];
				} else {
					$data[$value] = '';
				}
		}

		if (isset($_COOKIE['time_diff'])) {
			$time_diff = $_COOKIE['time_diff'] * 3600;
		}

		if(isset($this->request->post['date_start'])) {
			$data['date_start'] = $this->request->post['date_start'];
		} elseif (isset($crosssell['date_start'])) {

			$date_start = strtotime($crosssell['date_start']);
			if (isset($time_diff) && $time_diff) {
				$date_start = $date_start + $time_diff;
			}
			$start_date = date('Y-m-d H:i', $date_start);
			$data['date_start'] = $start_date;
		} else {
			$data['date_start'] = '';
		}

		if(isset($this->request->post['date_end'])) {
			$data['date_end'] = $this->request->post['date_end'];
		} elseif (isset($crosssell['date_end'])) {
			$date_end = strtotime($crosssell['date_end']);
			if (isset($time_diff) && $time_diff) {
				$date_end = $date_end + $time_diff;
			}
			$end_date = date('Y-m-d H:i', $date_end);
			$data['date_end'] = $end_date;
		} else {
			$data['date_end'] = '';
		}

		$this->load->model('account/promotional');
		$this->load->model('tool/image');

		$data['child_prods'] = array();

		$renamedOImage = array();

		if ($data['child_products'] && is_array($data['child_products'])) {
			$last_child_product = array();
			foreach ($data['child_products'] as $child_product) {
					if (in_array($child_product, $last_child_product)) {
						continue;
					}
					$last_child_product[] = $child_product;
					$children = $this->model_account_promotional->getProductCrosssell($data['parent_product'], $data['crosssell_id'], $child_product, true);

					foreach ($children as $child) {
							if ($child['image'] && is_file(DIR_IMAGE . $child['image'])) {
								$child_image = $this->model_tool_image->resize($child['image'], 80, 80);
							} else {
								$child_image = $this->model_tool_image->resize('placeholder.png', 80, 80);
							}

							if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
								$price = $this->currency->format($this->tax->calculate($child['price'], '', $this->config->get('config_tax')), $this->session->data['currency']);
							} else {
								$price = false;
							}

							$data['child_prods'][] = array(
								'child_id'       => $child_product,
								'child_name'     => $child['child_name'],
								'bundle_price'   => $child['vendor_price'],
								'image'          => $child['image'],
								'thumb'          => $child_image,
								'child_price'    => $price,
								'product_option' => $child['options'],
								'product_option_name' => html_entity_decode($child['option_name'])
							);
					}
			}
		} elseif (isset($this->request->post['product_child'])) {
			foreach ($this->request->post['product_child'] as $child_key => $child_product) {
				$getProductDetails = $this->model_account_customerpartner->getProduct($child_product);

				if (!empty($getProductDetails) && ($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($getProductDetails['price'], '', $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				$data['child_prods'][] = array(
					'child_id'       => $child_product,
					'child_name'     => $this->request->post['name_child'][$child_key],
					'bundle_price'   => $this->request->post['price_child'][$child_key],
					'image'          => $this->request->post['bundle_photo'][$child_key],
					'thumb'          => $this->request->post['bundle_photo'][$child_key] ? $this->model_tool_image->resize($this->request->post['bundle_photo'][$child_key], 80, 80) : $this->model_tool_image->resize('placeholder.png', 80, 80),
					'child_price'    => $price,
					'product_option' => $this->request->post['bundle_option'][$child_key],
					'product_option_name' => html_entity_decode($this->request->post['bundle_option_name'][$child_key])
				);
			}
		}

		$data['placeholder_image'] = $this->model_tool_image->resize('placeholder.png', 80, 80);

		if ($data['parent_product']) {
			$parent = $this->model_account_promotional->getProductCrosssell($data['parent_product'], $data['crosssell_id']);

			if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
				$parent_price = $this->currency->format($this->tax->calculate($parent['price'], '', $this->config->get('config_tax')), $this->session->data['currency']);
			} else {
				$parent_price = false;
			}

			$parent_options = $parent['parent_options'];
			$parent_options_name = $parent['parent_options_name'];
			$data['parent_prods'] = array(
				'parent_id'           => $data['parent_product'],
				'parent_name'         => $parent['parent_name'] . ' ('. $parent_price .')',
				'parent_options'      => $parent_options,
				'parent_options_name' => html_entity_decode($parent_options_name)
			);
		} elseif (isset($this->request->post['parent_id'])) {
			$data['parent_prods'] = array(
				'parent_id'           => $this->request->post['parent_id'],
				'parent_name'         => $this->request->post['parent_products'],
				'parent_options'      => $this->request->post['parent_option'],
				'parent_options_name' => html_entity_decode($this->request->post['parent_option_name'])
			);
		} else {
			$data['parent_prods'] = array(
				'parent_id'           => '',
				'parent_name'         => '',
				'parent_options'      => '',
				'parent_options_name' => ''
			);
		}

		if ($this->config->get('wk_crosssell_crosssell_applicable_status')) {
			if ($this->config->get('wk_crosssell_vendor_crosssell_applicable') && in_array($this->customer->getId(), $this->config->get('wk_crosssell_vendor_crosssell_applicable'))) {
				$data['crosssell_allowed'] = false;
				$data['error_warning'] = $this->language->get('not_allowed');
			} else {
				$data['crosssell_allowed'] = true;
			}
		} else {
			$data['crosssell_allowed'] = false;
			$data['error_warning'] = $this->language->get('not_allowed');
		}

		if ($this->config->get('wk_crosssell_countdown_applicable_status')) {
			if ($this->config->get('wk_crosssell_vendor_countdown_applicable') && in_array($this->customer->getId(), $this->config->get('wk_crosssell_vendor_countdown_applicable'))) {
				$data['countdown_allowed'] = false;
			} else {
				$data['countdown_allowed'] = true;
			}
		} else {
			$data['countdown_allowed'] = false;
		}

		if ($this->config->get('wk_crosssell_units_applicable_status')) {
			if ($this->config->get('wk_crosssell_vendor_units_applicable') && in_array($this->customer->getId(), $this->config->get('wk_crosssell_vendor_units_applicable'))) {
				$data['units_allowed'] = false;
			} else {
				$data['units_allowed'] = true;
			}
		} else {
			$data['units_allowed'] = false;
		}

		$data['cancel'] = $this->url->link('account/customerpartner/crosssell', '', true);

		// Use if multi language
		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();

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
		$this->response->setOutput($this->load->view('account/customerpartner/crosssell_form' , $data));
	}

/**
 * This will show the cross sell products that are associated with the product
 * @return HTML 	contains the child product's view part to be visible in the modal
 */
	public function info() {
		$data = $this->load->language('account/customerpartner/crosssell');

		$this->load->model('account/customerpartner');
		$this->load->model('account/promotional');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');

		if (isset($this->request->get['product_id']) && $this->request->get['product_id']) {
			$product_id = $this->request->get['product_id'];
		} else {
			$product_id = 0;
		}

		$widget_status = $this->config->get('wk_cwidget_status');

		if ($product_id && $widget_status) {
			$this->load->model('catalog/product');

			if ($this->config->get('wk_cwidget_picture_width')) {
				$width = $this->config->get('wk_cwidget_picture_width');
			} else {
				$width = 80;
			}

			if ($this->config->get('wk_cwidget_picture_height')) {
				$height = $this->config->get('wk_cwidget_picture_height');
			} else {
				$height = 80;
			}

			$parent = $this->model_account_customerpartner->getProduct($product_id);

			if ($parent['quantity'] < 1) {
				// break;
			}

			$parent['option'] = $this->model_account_promotional->hasOption($product_id);

			if ($parent['image'] && is_file(DIR_IMAGE . $parent['image'])) {
				$image = $this->model_tool_image->resize($parent['image'], $width, $height);
			} else {
				$image = $this->model_tool_image->resize('placeholder.png', $width, $height);
			}

			if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
				$parent_with_tax = $this->tax->calculate($parent['price'], $parent['tax_class_id'], $this->config->get('config_tax'), $product_id);
				$price = $this->currency->format($parent_with_tax, $this->session->data['currency']);
			} else {
				$price = false;
			}

			if ($this->config->get('config_review_status')) {
				$rating = (int)$parent['rating'];
			} else {
				$rating = false;
			}

			$data['parent'] = array(
					'product_id'  => $parent['product_id'],
					'option'  	  => $parent['option'],
					'thumb'       => $image,
					'name'        => $parent['name'],
					'price'       => $price,
					'rating'      => $rating,
					'href'        => $this->url->link('product/product', 'product_id=' . $parent['product_id'])
				);

			$language_id = $this->config->get('config_language_id');

			$data['tax_status'] = $this->config->get('wk_crosssell_tax_status');
			$data['countdown_status'] = $this->config->get('wk_crosssell_countdown_status');
			$data['quantity_status'] = $this->config->get('wk_crosssell_units_status');
			$countdown_format = $this->config->get('wk_crosssell_countdown_syntax')[$language_id];
			$countdowntime_format = $this->config->get('wk_crosssell_countdowntime_syntax')[$language_id];
			$quantity_format = $this->config->get('wk_crosssell_units_syntax')[$language_id];

			$countdown_format = html_entity_decode(str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim($countdown_format))));

			$countdowntime_format = html_entity_decode(str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim($countdowntime_format))));

			$current_date = date('Y-m-d H:i:s');
			$date_now = strtotime($current_date);

			$this->load->model('account/option_price');

			$results = $this->model_account_promotional->getCrosssells($product_id);

			$data['products'] = array();

			foreach ($results as $result) {

				if ($result['image'] && is_file(DIR_IMAGE . $result['image'])) {
					$image = $this->model_tool_image->resize($result['image'], $width, $height);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $width, $height);
				}

				$option_price = $this->model_account_option_price->getOptionPrice($result['options'], $result['product_id']);

				$result['price'] += $option_price;

				// if ($result['type'] == 'virtual' && isset($result['options']) && $result['options']) {
				// 		foreach (json_decode($result['options'], true) as $option) {
				// 				$option_price = $this->db->query("SELECT price FROM " . DB_PREFIX . "virtual_product_to_download WHERE product_id = '" . $result['product_id'] . "' AND download_id = '" . $option . "'")->row;
				//
				// 				if (isset($option_price['price'])) {
				// 					$result['price'] = $option_price['price'];
				// 				}
				// 		}
				// }
				//
				// if ($parent['type'] == 'virtual' && isset($result['parent_options']) && $result['parent_options']) {
				// 	foreach (json_decode($result['parent_options'], true) as $option) {
				// 		$option_price = $this->db->query("SELECT price FROM " . DB_PREFIX . "virtual_product_to_download WHERE product_id = '" . $parent['product_id'] . "' AND download_id = '" . $option . "'")->row;
				//
				// 		if (isset($option_price['price'])) {
				// 			$parent['price'] = $option_price['price'];
				// 			$parent_with_tax = $this->tax->calculate($parent['price'], $parent['tax_class_id'], $this->config->get('config_tax'), $parent['product_id']);
				// 			$data['parent']['price'] = $this->currency->format($parent_with_tax, $this->session->data['currency']);
				// 		}
				// 	}
				// }

				if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
					$product_with_tax = $this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax'), $result['product_id']);
					$price = $this->currency->format($product_with_tax, $this->session->data['currency']);
				} else {
					$price = false;
				}

				if ($result['bundle_price']) {
					$saved = $result['price'] + $parent['price'] - $result['bundle_price'];
					$bundle_with_tax = $parent_with_tax + $product_with_tax - $saved;

					// $bundle_with_tax = $this->tax->calculate($result['bundle_price'], $result['tax_class_id'], $this->config->get('config_tax'), $result['product_id']);
					$bundle_price = $this->currency->format($bundle_with_tax, $this->session->data['currency']);
				} else {
					$bundle_price = false;
				}

				if ($result['bundle_price']) {
					// $you_save = $this->currency->format($this->tax->calculate($result['price'] + $parent['price'] - $result['bundle_price'], $result['tax_class_id'], $this->config->get('config_tax'), $result['product_id']), $this->session->data['currency']);
					$you_save = $this->currency->format(($result['price'] + $parent['price'] - $result['bundle_price']), $this->session->data['currency']);
				} else {
					$you_save = false;
				}

				if ($this->config->get('config_review_status')) {
					$rating = (int)$result['rating'];
				} else {
					$rating = false;
				}

				if (isset($_COOKIE['time_diff'])) {
					$time_diff = $_COOKIE['time_diff'] * 3600;
				}

				$date_end = strtotime($result['date_end']);
				$duration = ($date_end - $date_now);

				if ($duration > (86400)) {
					$day = true;
				} else {
					$day = false;
				}

				if (isset($time_diff) && $time_diff) {
					$date_end = $date_end + $time_diff;
				}

				$end_date = date('Y-m-d H:i', $date_end);

				$data['products'][] = array(
					'product_id'         => $result['product_id'],
					'thumb'              => $image,
					'name'               => $result['name'],
					'description'        => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, 100) . '..',
					'price'              => $price,
					'bundle_price'       => $bundle_price,
					'you_save'	         => $you_save,
					'minimum'            => $result['minimum'] > 0 ? $result['minimum'] : 1,
					'rating'             => $rating,
					'countdown_status'   => $result['countdown_status'],
					'quantity_status'    => $result['quantity_status'],
					'quantity'		       => $result['quantity'],
					'formatted_quantity' => html_entity_decode(str_replace('{units}', $result['quantity'], $quantity_format)),
					'date_end'		       => $end_date,
					'href'               => $this->url->link('product/product', 'product_id=' . $result['product_id']),
					'countdown_format'   => $day ? $countdown_format : $countdowntime_format,
					'duration'           => $duration,
					'product_option'     => json_decode($result['options'], true),
					'product_option_name' => str_replace('-','',html_entity_decode($result['option_name'])),
					'parent_product_option' => json_decode($result['parent_options'], true),
					'parent_product_option_name' => str_replace('-','',html_entity_decode($result['parent_options_name'])),
					'id' => $result['id']
				);
			}

			$this->response->setOutput($this->load->view('account/customerpartner/crosssellmodal', $data));
		} else {
			$data['heading_title'] = $this->language->get('text_error');
			$data['text_error'] = $this->language->get('text_error');

			$this->response->setOutput($this->load->view('account/customerpartner/not_found', $data));
		}
	}

	public function validated() {
		$this->load->model('account/promotional');
		$this->load->model('account/validate_promo');

		if (empty($this->request->post['product_child'])) {
			$this->error['error_product_child'] = $this->language->get('error_product_child');
		} else {
			$error = $this->model_account_validate_promo->validateCrosssells();
			if (isset($error['quantity'])) {
				$this->error['error_quantity'] = $this->language->get('error_quantity');
			}
			$this->error = array_merge($this->error, $error);
		}

		if (empty($this->request->post['parent_id'])) {
				$this->error['error_product_parent'] = $this->language->get('error_product_parent');
		} else {
				$parent_id = $this->request->post['parent_id'];
				$error = $this->model_account_validate_promo->validateCrosssellParent($parent_id);

				if ($error['error']) {
					$this->error['error_product_parent'] = $this->language->get('error_expired');
				}
				if ($error['quantity']) {
					$this->error['error_quantity'] = $this->language->get('error_quantity');
				}
		}

		if (isset($this->request->post['price_child'])) {
			foreach ($this->request->post['price_child'] as $child_price) {
				if (!is_numeric($child_price)) {
					$this->error['error_product_child'] = $this->language->get('error_price_child');
				}
			}
		}
		if($this->request->post['countdown_status']){
			$date_limit = strtotime(date("Y-m-d"));

			if (!$this->error && strtotime($this->request->post['date_start']) < $date_limit) {
				$this->error['error_date_start'] = $this->language->get('error_date_start');
			}
	
			if (!$this->error && strtotime($this->request->post['date_end']) < $date_limit) {
				$this->error['error_date_end'] = $this->language->get('error_date_end');
			}
	
			if (!$this->error && strtotime($this->request->post['date_end']) < strtotime($this->request->post['date_start'])) {
		  		$this->error['error_date_start'] = $this->language->get('error_date_start_l');
			}
	  }

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		if($this->error) {
			return false;
		} else {
			return true;
		}
	}

	public function autocomplete() {
		$json = array();

		if ($this->customer->isLogged() && isset($this->request->get['filter_name'])) {

			$this->load->model('account/promotional');
			$this->load->model('catalog/product');
			$this->load->model('tool/image');

			if (isset($this->request->get['filter_name'])) {
				$filter_name = $this->request->get['filter_name'];
			} else {
				$filter_name = '';
			}

			if (isset($this->request->get['limit'])) {
				$limit = $this->request->get['limit'];
			} else {
				$limit = 20;
			}

			$data = array(
				'filter_name'   => $filter_name,
				'filter_stock'  => true,
				'filter_status' => 1,
				'start'         => 0,
				'limit'         => $limit
			);

			$results = $this->model_account_promotional->getSellerProducts($data);

			foreach ($results as $result) {
				if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], '', $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if ($result['image'] && is_file(DIR_IMAGE . $result['image'])) {
					$image = $this->model_tool_image->resize($result['image'], 80, 80);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', 80, 80);
				}

				$json[] = array(
					'product_id' => $result['product_id'],
					'name'       => (int)$result['price'] ? strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')) .'('.$price.')' : strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
					'price'      => $price,
					'thumb'      => $image,
					'image'      => $result['image']
				);
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
?>
