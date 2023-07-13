<?php
require_once DIR_SYSTEM . 'ocMpTrait.php';
class ControllerAccountCustomerpartnerUpsell extends Controller {
	use OcMpTrait;
	private $error = array();

	public function index() {

		if (!in_array('wk_upsell', $this->config->get('marketplace_allowed_account_menu')) ||!$this->config->get('module_wk_upsell_upsell_status') || !$this->config->get('module_marketplace_status')) {
      $this->session->data['redirect'] = $this->url->link('account/customerpartner/upsell', '', true);
      $this->response->redirect($this->url->link('account/account', '', true));
    }

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/customerpartner/upsell', '', true);
			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$data = $this->load->language('account/customerpartner/upsell');
		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment.js');
		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
		$this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');
		$this->document->addStyle('catalog/view/theme/default/stylesheet/MP/sell.css');
		$this->document->addStyle('catalog/view/theme/default/stylesheet/promo/promo.css');
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
		$this->load->language('account/customerpartner/upsell');

		if($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validated()) {
			$this->load->model('account/promotional');
			$this->model_account_promotional->addUpsell($this->request->post);
			$this->session->data['success'] = $this->language->get('text_success_add');
			$this->response->redirect($this->url->link('account/customerpartner/upsell', '', true));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('account/customerpartner/upsell');

		if (!$this->config->get('wk_upsell_upsell_status') || !$this->config->get('marketplace_status')) {
      $this->session->data['redirect'] = $this->url->link('account/customerpartner/upsell', '', true);
      $this->response->redirect($this->url->link('account/account', '', true));
    }

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/customerpartner/upsell', '', true);
			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->model('account/customerpartner');
		$this->load->model('account/promotional');

		$chkIsPartner = $this->model_account_customerpartner->chkIsPartner();

		if(!$chkIsPartner)
			$this->response->redirect($this->url->link('account/account', '', true));

		if (isset($this->request->get['upsell_id']) && $this->request->get['upsell_id']) {
			$checkUpsell = $this->model_account_promotional->getUpsell($this->request->get['upsell_id']);
			if (!$checkUpsell) {
				$this->response->redirect($this->url->link('account/customerpartner/upsell', '', true));
			}
		}

		if($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validated() && isset($this->request->get['upsell_id'])) {
			$this->model_account_promotional->editUpsell($this->request->post, $this->request->get['upsell_id']);
			$this->session->data['success'] = $this->language->get('text_success_update');
			$this->response->redirect($this->url->link('account/customerpartner/upsell', '', true));
		}
		$this->getForm();
	}

	public function delete() {
		if($this->request->server['REQUEST_METHOD'] == 'POST') {
			$this->load->language('account/customerpartner/upsell');
			if(isset($this->request->post['selected']) && $this->request->post['selected']) {
				$this->load->model('account/promotional');
				foreach ($this->request->post['selected'] as $key => $upsell_id) {
					$this->model_account_promotional->deleteUpsell($upsell_id);
				}
				$this->session->data['success'] = $this->language->get('text_success_delete');
				$this->response->redirect($this->url->link('account/customerpartner/upsell', '', true));
			} else {
				$this->error['error_warning'] = $this->language->get('text_error_delete');
				$this->index();
			}
		}
	}

	public function getList() {
		$data = $this->load->language('account/customerpartner/upsell');

		$data['wkIsMember'] = true;

		$url = '';

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
			'href' => $this->url->link('account/customerpartner/upsell', '', true)
		);

		$errorVariable = array (
			'error_product_child',
			'error_product_parent',
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

        $data['export'] = $this->url->link('account/customerpartner/mtools/upselltoXLS', '', true);

		$this->load->model('account/promotional');

		$upsells = $this->model_account_promotional->getAllUpsell();

		$data['upsells'] = array ();
     
		if($upsells) {
			foreach ($upsells as $key => $detail) {
				$parent_prods = explode(',', $detail['parent_products']);

				$child_prod = $parent_prod = array();

				if ($parent_prods)
					foreach ($parent_prods as $parent_prodd) {
						$parent = $this->model_account_promotional->getProductName($parent_prodd);

						if ($parent) {
							$parent_prod[] = $parent;
						}
					}

				if (!$parent_prod || !$parent_prod) {
					continue;
				}

				$parent_prods = implode(', ', $parent_prod);

				
				$child_products = explode(',', $detail['child_products']);
				
				if ($child_products)
					foreach ($child_products as $child_product) {
						$child = $this->model_account_promotional->getProductName($child_product);

						if ($child) {
							$child_prod[] = $child;
						}
					}
                if (!$parent_prod || !$parent_prod) {
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

				$data['upsells'][] = array (
					'upsell_id'    => $detail['upsell_id'],
					'countdown_status'       => $detail['countdown_status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
					'quantity'        => $detail['quantity'],
					'quantity_status' => $detail['quantity_status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
					'date_start'      => $start_date,
					'date_end'        => $end_date,
					'parent_products' => $parent_prods,
					'child_products'  => $child_products,
					'edit'            => $this->url->link('account/customerpartner/upsell/edit', 'upsell_id='.$detail['upsell_id'], true)
					);

			}
		}

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$data['add'] = $this->url->link('account/customerpartner/upsell/add', '', true);
		$data['delete'] = $this->url->link('account/customerpartner/upsell/delete', '', true);

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
		$this->response->setOutput($this->load->view('account/customerpartner/upsell' , $data));
	}

	public function getForm() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/customerpartner/upsell', '', true);
			$this->response->redirect($this->url->link('account/login', '', true));
		}

		if (!in_array('wk_upsell', $this->config->get('marketplace_allowed_account_menu')) ||!$this->config->get('module_wk_upsell_upsell_status') || !$this->config->get('module_marketplace_status')) {
			$this->session->data['redirect'] = $this->url->link('account/customerpartner/upsell', '', true);
			$this->response->redirect($this->url->link('account/account', '', true));
		  }

		$this->load->model('account/customerpartner');

		$chkIsPartner = $this->model_account_customerpartner->chkIsPartner();

		if(!$chkIsPartner)
			$this->response->redirect($this->url->link('account/account'));

		$data = $this->load->language('account/customerpartner/upsell');

  	$data['wkIsMember'] = true;

		$this->document->setTitle($this->language->get('heading_title_insert'));
		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment.js');
		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
		$this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');
		$this->document->addStyle('catalog/view/theme/default/stylesheet/MP/sell.css');
		$this->document->addStyle('catalog/view/theme/default/stylesheet/promo/promo.css');

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
			'href' => $this->url->link('account/customerpartner/upsell', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => 'Add',
			'href' => $this->url->link('account/customerpartner/upsell/add', '', true)
		);

		$errorVariable = array (
			'error_product_child',
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

		$data['action'] = $this->url->link('account/customerpartner/upsell/add', '', true);

		$upsell = array();

		if(isset($this->request->get['upsell_id']) && $this->request->get['upsell_id']) {
			$this->document->setTitle($this->language->get('heading_title_update'));
			$data['upsell_id'] = $this->request->get['upsell_id'];
			$this->load->model('account/promotional');
			$upsell = $this->model_account_promotional->getUpsell($this->request->get['upsell_id']);

			if ($upsell) {
				$upsell['parent_products'] = explode(',', $upsell['parent_products']);
				$upsell['child_products'] = explode(',', $upsell['child_products']);
			}

			$data['action'] = $this->url->link('account/customerpartner/upsell/edit', 'upsell_id='.$this->request->get['upsell_id'] , true);
		}

		$postValues = array (
			'child_products',
			'parent_products',
			'countdown_status',
			'quantity_status',
			'quantity'
		);

		foreach ($postValues as $key => $value) {
			if(isset($this->request->post[$value])) {
				$data[$value] = $this->request->post[$value];
			} elseif (isset($upsell[$value])) {
				$data[$value] = $upsell[$value];
			} else {
				$data[$value] = '';
			}
		}

		if (isset($this->request->post['product_child'])) {
			$data['child_products'] = $this->request->post['product_child'];
		}

		if (isset($this->request->post['product_parent'])) {
			$data['parent_products'] = $this->request->post['product_parent'];
		}

		if (isset($_COOKIE['time_diff'])) {
			$time_diff = $_COOKIE['time_diff'] * 3600;
		}

		if(isset($this->request->post['date_start'])) {
			$data['date_start'] = $this->request->post['date_start'];
		} elseif (isset($upsell['date_start'])) {

			$date_start = strtotime($upsell['date_start']);
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
		} elseif (isset($upsell['date_end'])) {
			$date_end = strtotime($upsell['date_end']);
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

		if (isset($this->request->post['product_child']) && is_array($this->request->post['product_child'])) {
			foreach ($this->request->post['product_child'] as $child_key => $child_product) {

				if ($this->request->post['photo'][$child_key] && is_file(DIR_IMAGE . $this->request->post['photo'][$child_key])) {
					$image = $this->model_tool_image->resize($this->request->post['photo'][$child_key], 80, 80);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', 80, 80);
				}

				$child_name = $this->model_account_promotional->getProductName($child_product);

				$data['child_prods'][] = array(
					'child_id'            => $child_product,
					'child_name'          => $child_name,
					'image'               => isset($this->request->post['photo']) && isset($child_key) && isset($this->request->post['photo'][$child_key]) ? $this->request->post['photo'][$child_key] : '',
					'thumb'               => $image,
					'product_option'      => isset($this->request->post['option']) && isset($child_key) && isset($this->request->post['option'][$child_key]) ?$this->request->post['option'][$child_key] : '',
					'product_option_name' => isset($this->request->post['option_name']) && isset($child_key) && isset($this->request->post['option_name'][$child_key]) ? html_entity_decode($this->request->post['option_name'][$child_key]) : '',
					);
			}
		} elseif ($this->request->server['REQUEST_METHOD'] != 'POST' && $data['child_products'] && is_array($data['child_products'])) {
			foreach ($data['child_products'] as $child_product) {
				$child = $this->model_account_promotional->getProductUpsell($data['upsell_id'], $child_product);
				$child_name = $this->model_account_promotional->getProductName($child_product);

				if ($child['image'] && is_file(DIR_IMAGE . $child['image'])) {
					$image = $this->model_tool_image->resize($child['image'], 80, 80);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', 80, 80);
				}

				$data['child_prods'][] = array(
					'child_id'            => $child_product,
					'child_name'          => $child_name,
					'image'               => $child['image'],
					'thumb'               => $image,
					'product_option'      => $child['options'],
					'product_option_name' => html_entity_decode($child['option_name'])
				);
			}
		}

		$data['placeholder_image'] = $this->model_tool_image->resize('placeholder.png', 80, 80);

		// $data['child_prods'] = array();
		$data['parent_prods'] = array();

		// if ($data['child_products']) {
		// 	foreach ($data['child_products'] as $child_product) {
		// 		$child_name = $this->model_account_promotional->getProductName($child_product);
		// 		$data['child_prods'][] = array(
		// 			'child_id' => $child_product,
		// 			'child_name' => $child_name
		// 		);
		// 	}
		// }

		if ($data['parent_products'] && is_array($data['parent_products'])) {
			foreach ($data['parent_products'] as $parent_product) {
				$parent_name = $this->model_account_promotional->getProductName($parent_product);
				$data['parent_prods'][] = array('parent_id' => $parent_product, 'parent_name' => $parent_name);
			}
		}

		if ($this->config->get('wk_upsell_upsell_applicable_status')) {
			if ($this->config->get('wk_upsell_vendor_upsell_applicable') && in_array($this->customer->getId(), $this->config->get('wk_upsell_vendor_upsell_applicable'))) {
				$data['upsell_allowed'] = false;
				$data['error_warning'] = $this->language->get('not_allowed');
			} else {
				$data['upsell_allowed'] = true;
			}
		} else {
			$data['upsell_allowed'] = false;
			$data['error_warning'] = $this->language->get('not_allowed');
		}

		if ($this->config->get('wk_upsell_countdown_applicable_status')) {
			if ($this->config->get('wk_upsell_vendor_countdown_applicable') && in_array($this->customer->getId(), $this->config->get('wk_upsell_vendor_countdown_applicable'))) {
				$data['countdown_allowed'] = false;
			} else {
				$data['countdown_allowed'] = true;
			}
		} else {
			$data['countdown_allowed'] = false;
		}

		if ($this->config->get('wk_upsell_units_applicable_status')) {
			if ($this->config->get('wk_upsell_vendor_units_applicable') && in_array($this->customer->getId(), $this->config->get('wk_upsell_vendor_units_applicable'))) {
				$data['units_allowed'] = false;
			} else {
				$data['units_allowed'] = true;
			}
		} else {
			$data['units_allowed'] = false;
		}

		$data['cancel'] = $this->url->link('account/customerpartner/upsell', '', true);

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

		$this->response->setOutput($this->load->view('account/customerpartner/upsell_form' , $data));
	}

/**
 * This will show the upsell products that are associated with the product
 * @return HTML 	contains the child product's view part to be visible in the modal
 */
	public function info()	{
		$data = $this->load->language('account/customerpartner/upsell');

		$this->load->model('account/promotional');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');

		if (isset($this->request->get['product_id']) && $this->request->get['product_id']) {
			$product_id = $this->request->get['product_id'];
		} else {
			$product_id = 0;
		}

		$widget_status = $this->config->get('wk_widget_status');

		if ($product_id && $widget_status) {
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

			$language_id = $this->config->get('config_language_id');

			$data['tax_status'] = $this->config->get('wk_upsell_tax_status');
			$data['countdown_status'] = $this->config->get('wk_upsell_countdown_status');
			$data['quantity_status'] = $this->config->get('wk_upsell_units_status');
			$countdown_format = $this->config->get('wk_upsell_countdown_syntax')[$language_id];
			$countdowntime_format = $this->config->get('wk_upsell_countdowntime_syntax')[$language_id];
			$quantity_format = $this->config->get('wk_upsell_units_syntax')[$language_id];

			$current_date = date('Y-m-d H:i:s');
			$date_now = strtotime($current_date);

			$this->load->model('account/option_price');

			$countdown_format = html_entity_decode(str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim($countdown_format))));

			$countdowntime_format = html_entity_decode(str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim($countdowntime_format))));

			$results = $this->model_account_promotional->getUpsells($product_id);

			if ($results)
			foreach ($results as $result) {
				if ($result['image'] && is_file(DIR_IMAGE . $result['image'])) {
					$image = $this->model_tool_image->resize($result['image'], $width, $height);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $width, $height);
				}

				$option_price = $this->model_account_option_price->getOptionPrice($result['options'], $result['product_id']);

				$result['price'] += $option_price;

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

				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
				} else {
					$tax = false;
				}



			//tax manager code

				if ($this->config->get('wk_tax_manager_status')) {

				$this->load->model('localisation/new_tax_rate');

				$display_taxes = $this->model_localisation_new_tax_rate->getCartDisplayOption($this->config->get('config_store_id'));

				if ($special) {
					$price = $result['special'];

					$tax_rates = $this->tax->getRates($result['special'], $result['tax_class_id'], $result['product_id']);
				} else if($price && isset($result['tax_class_id']) && $result['tax_class_id']){
					$price = $result['price'];

						$tax_rates = $this->tax->getRates($result['price'], $result['tax_class_id'], $result['product_id']);
				} else {
						$tax_rates = array();
				}

					$tax = 0;

					if ($this->config->get('wk_tax_manager_status') && $display_taxes && $tax_rates) {

						foreach ($display_taxes as $display_tax) {
							if (isset($tax_rates[$display_tax['tax_rate_id']]['amount']) && $tax_rates[$display_tax['tax_rate_id']]['amount']) {
									if($display_tax['display_tax_option'] == 'sp_ds') {
									$service_type = $this->model_localisation_new_tax_rate->getServiceType($display_tax['tax_rate_id']);

									if (isset($service_type['service_type']) && $service_type['service_type']) {
										$tax += $tax_rates[$display_tax['tax_rate_id']]['amount'];
									}
								} else {
									$service_type = $this->model_localisation_new_tax_rate->getServiceType($display_tax['tax_rate_id']);

									if (isset($service_type['service_type']) && $service_type['service_type']) {
										$price += $tax_rates[$display_tax['tax_rate_id']]['amount'];
									}
								}
							}
						}

						$price = $this->currency->format(($price+$tax), $this->session->data['currency']);

						if (round($tax, $this->currency->getDecimalPlace($this->session->data['currency'])) > 0) {
							$tax = $this->currency->format($tax, $this->session->data['currency']);
						} else {
							$tax = '';
						}

					} else {
						$price = $this->currency->format($price, $this->session->data['currency']);
					}
				}
				//tax manager code

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
					'special'            => $special,
					'tax'                => $tax,
					'minimum'            => $result['minimum'] > 0 ? $result['minimum'] : 1,
					'rating'             => $rating,
					'countdown_status'   => $result['countdown_status'],
					'quantity_status'    => $result['quantity_status'],
					'quantity'           => $result['quantity'],
					'formatted_quantity' => html_entity_decode(str_replace('{units}', $result['quantity'], $quantity_format)),
					'date_end'           => $end_date,
					'href'               => $this->url->link('product/product', 'product_id=' . $result['product_id']),
					'countdown_format'   => $day ? $countdown_format : $countdowntime_format,
					'duration'           => $duration,
					'product_option'     => json_decode($result['options'], true),
					'product_option_name' => html_entity_decode($result['option_name']),
					'id' => $result['id']
				);
			}
			$this->response->setOutput($this->load->view('account/customerpartner/upsellmodal', $data));
		} else {
			$data['heading_title'] = $this->language->get('text_error');
			$data['text_error'] = $this->language->get('text_error');

			$this->response->setOutput($this->load->view('account/customerpartner/not_found', $data));
		}
	}

	public function validated() {
		$this->load->model('account/promotional');

		if (empty($this->request->post['product_child'])) {
			$this->error['error_product_child'] = $this->language->get('error_product_child');
		}

		if (empty($this->request->post['product_parent'])) {
			$this->error['error_product_parent'] = $this->language->get('error_product_parent');
		}
		// echo "<pre>";
		// print_r($this->request->post);
		// echo "</pre>";
		// die();

		if (isset($this->request->post['countdown_status']) && $this->request->post['countdown_status']) {
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

		$product_names = '';

		$child_products = isset($this->request->post['product_child']) && is_array($this->request->post['product_child']);
		$parent_products = isset($this->request->post['product_parent']) && is_array($this->request->post['product_parent']);

		if (isset($this->request->post['quantity_status']) && $this->request->post['quantity_status']) {
			if ($this->request->post['quantity']) {
				if ($child_products) {
					foreach ($this->request->post['product_child'] as $product_id) {
						$product = $this->model_account_promotional->getProductName($product_id, 1);
						if ($product['quantity'] < $this->request->post['quantity']) {
							$product_names .= $product['name'] . '(' . $product['quantity'] . '), ';
						}
					}
				}
				if ($parent_products) {
					foreach ($this->request->post['product_parent'] as $product_id) {
						if ($child_products && in_array($product_id, $this->request->post['product_child'])) {
							continue;
						}
						$product = $this->model_account_promotional->getProductName($product_id, 1);
						if ($this->request->post['quantity_status'] && $product['quantity'] < $this->request->post['quantity']) {
							$product_names .= $product['name'] . '(' . $product['quantity'] . '), ';
						}
					}
				}
				if ($product_names) {
					$product_names = rtrim($product_names, ', ');
					$this->error['error_quantity'] = sprintf($this->language->get('error_quantity'), $product_names);
				}
			} else {
				$this->error['error_quantity'] = $this->language->get('error_zero_quantity');
			}
		} else {
			if ($child_products) {
				foreach ($this->request->post['product_child'] as $product_id) {
					$product = $this->model_account_promotional->getProductName($product_id, 1);
					if ($product['quantity'] < 1) {
						$product_names .= $product['name'] . ', ';
					}
				}
			}
			if ($parent_products) {
				foreach ($this->request->post['product_parent'] as $product_id) {
					if ($child_products && in_array($product_id, $this->request->post['product_child'])) {
						continue;
					}
					$product = $this->model_account_promotional->getProductName($product_id, 1);
					if ($product['quantity'] < 1) {
						$product_names .= $product['name'] . ', ';
					}
				}
			}
			if ($product_names) {
				$product_names = rtrim($product_names, ', ');
				$this->error['error_quantity'] = sprintf($this->language->get('error_no_quantity'), $product_names);
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

		if (isset($this->request->get['filter_name']) || isset($this->request->get['filter_model']) || isset($this->request->get['filter_category_id'])) {
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
				if ($result['image'] && is_file(DIR_IMAGE . $result['image'])) {
					$image = $this->model_tool_image->resize($result['image'], 80, 80);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', 80, 80);
				}

				$json[] = array(
					'product_id' => $result['product_id'],
					'name'       => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
					'thumb'      => $image,
					'image'      => $result['image']
				);
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
} // Class end here
?>
