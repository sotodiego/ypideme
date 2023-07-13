<?php
class ControllerExtensionModuleWkcrosssell extends Controller {
	private $error = array();

	public function install() {
		$this->load->model('extension/module/wk_crosssell');
		$this->model_extension_module_wk_crosssell->createTables();
		if(!is_dir(DIR_IMAGE. 'catalog/promo')){
			mkdir(DIR_IMAGE. 'catalog/promo');
		}
	}

	public function uninstall() {
		$this->load->model('extension/module/wk_crosssell');
		$this->model_extension_module_wk_crosssell->deleteTables();

		if(is_dir(DIR_IMAGE. 'catalog/promo')) {
			rmdir(DIR_IMAGE. 'catalog/promo');
		}
	}

  public function seniziteEditorForScriptValue($_array_editor = array()) {
		foreach ($_array_editor as $_post_key) {
			if(isset($this->request->post[$_post_key]) && is_array($this->request->post[$_post_key])){
				foreach ($this->request->post[$_post_key] as $key => $value) {
					if(isset($this->request->post[$_post_key][$key])){
						$this->request->post[$_post_key][$key] = preg_replace("/script.*?\/script/ius", " ", trim($this->request->post[$_post_key][$key]));
					}
				}
			}
		}
  }

	public function index() {
		$lang_array = $this->load->language('extension/module/wk_crosssell');

		$this->document->setTitle(strip_tags($this->language->get('heading_title')));

		$this->load->model('setting/setting');

		$this->load->model('localisation/language');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

			foreach ($this->request->post as $code => $code_value) {
				$substr = substr($code, 0, 8);
				break;
			}

			if ($substr == 'module_w') {
				 $sub_substr = substr($code, 0, 15);
			   if ($sub_substr == 'module_wk_cross') {

             $_array_editor = array (
							 'wk_crosssell_countdown_syntax',
							 'wk_crosssell_units_syntax',
							 'wk_crosssell_countdowntime_syntax'
					   );

						 $this->seniziteEditorForScriptValue($_array_editor);

					   $this->request->post['module_wk_crosssell_status'] = $this->request->post['module_wk_crosssell_crosssell_status'];
					   $this->model_setting_setting->editSetting('module_wk_crosssell', $this->request->post);
						 $this->model_setting_setting->editSetting('wk_crosssell', $this->request->post);
			  	}
			} else if ($substr == 'wk_cwidg') {

				$_array_editor = array (
					'wk_cwidget_crossselling_details',
				);

				$this->seniziteEditorForScriptValue($_array_editor);

				 $this->model_setting_setting->editSetting('wk_cwidget', $this->request->post);
			} else if ($substr == 'wk_clist') {

				$_array_editor = array (
					'wk_clisting_crossselling_details',
				);

        $this->seniziteEditorForScriptValue($_array_editor);

				$this->model_setting_setting->editSetting('wk_clisting', $this->request->post);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('extension/module/wk_crosssell', 'user_token=' . $this->session->data['user_token'], 'SSL'));
		}

		foreach ($lang_array as $key => $value) {
			$data[$key] = $value;
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$this->document->addStyle('view/javascript/promo/promo.css');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_module'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/wk_crosssell', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['action_control'] = $this->url->link('extension/module/wk_crosssell', 'user_token=' . $this->session->data['user_token'], 'SSL');
		$data['action_widgets'] = $this->url->link('extension/module/wk_crosssell', 'user_token=' . $this->session->data['user_token'], 'SSL');
		$data['action_listings'] = $this->url->link('extension/module/wk_crosssell', 'user_token=' . $this->session->data['user_token'], 'SSL');
		$data['action_deploy'] = $this->url->link('extension/module/wk_crosssell/deploy', 'user_token=' . $this->session->data['user_token'], 'SSL');
		$data['export'] = $this->url->link('extension/module/wk_crosssell/export', 'user_token=' . $this->session->data['user_token'], 'SSL');

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		$data['user_token'] = $this->session->data['user_token'];

		$data['marketplace_status'] = $this->config->get('module_marketplace_status');

    if( $data['marketplace_status'] ) {

			$wk_crosssell_array = array(
					'crosssell_applicable_status',
					'crosssell_applicable',
					'tax_status',
					'countdown_status',
					'countdown_applicable_status',
					'countdown_applicable',
					'countdown_syntax',
					'countdowntime_syntax',
					'units_status',
					'units_applicable_status',
					'units_applicable',
					'units_syntax'
				);

			if (isset($this->request->post['module_wk_crosssell_crosssell_status'])) {
				$data['wk_crosssell_crosssell_status'] = $this->request->post['module_wk_crosssell_crosssell_status'];
			} else {
				$data['wk_crosssell_crosssell_status'] = $this->config->get('module_wk_crosssell_crosssell_status');
			}

			foreach ($wk_crosssell_array as $sell_value) {
				if (isset($this->request->post['wk_crosssell_' . $sell_value])) {
					$data['wk_crosssell_' . $sell_value] = $this->request->post['wk_crosssell_' . $sell_value];
				} else {
					$data['wk_crosssell_' . $sell_value] = $this->config->get('wk_crosssell_' . $sell_value);
				}
			}

      $data['catalog_link'] = HTTP_CATALOG.'index.php?route=extension/module/crosssell';

			$this->load->model('extension/module/wk_sell');

			$vendor_crosssell_applicable = $this->config->get('wk_crosssell_vendor_crosssell_applicable');

			$data['vendor_crosssell_applicables'] = array();

			if ($vendor_crosssell_applicable)
				foreach ($vendor_crosssell_applicable as $vendor_applicable) {
					$data['vendor_crosssell_applicables'][] = $this->model_extension_module_wk_sell->getVendor($vendor_applicable);
				}

			$vendor_countdown_applicable = $this->config->get('wk_crosssell_vendor_countdown_applicable');

			$data['vendor_countdown_applicables'] = array();

			if ($vendor_countdown_applicable)
			foreach ($vendor_countdown_applicable as $vendor_applicable) {
				$data['vendor_countdown_applicables'][] = $this->model_extension_module_wk_sell->getVendor($vendor_applicable);
			}

			$vendor_units_applicable = $this->config->get('wk_crosssell_vendor_units_applicable');

			$data['vendor_units_applicables'] = array();

			if ($vendor_units_applicable)
			foreach ($vendor_units_applicable as $vendor_applicable) {
				$data['vendor_units_applicables'][] = $this->model_extension_module_wk_sell->getVendor($vendor_applicable);
			}

			$wk_cwidget_array = array(
					'title',
					'crossselling_widget',
					'display_type',
					'picture_width',
					'picture_height',
					'custom_css',
					'crossselling_details',
					'crossselling_details2'
				);

			if (isset($this->request->post['wk_cwidget_status'])) {
				$data['wk_cwidget_status'] = $this->request->post['wk_cwidget_status'];
			} else {
				$data['wk_cwidget_status'] = $this->config->get('wk_cwidget_status');
			}

			foreach ($wk_cwidget_array as $widget_value) {
				if (isset($this->request->post['wk_cwidget_' . $widget_value])) {
					$data['wk_cwidget_' . $widget_value] = $this->request->post['wk_cwidget_' . $widget_value];
				} else {
					$data['wk_cwidget_' . $widget_value] = $this->config->get('wk_cwidget_' . $widget_value);
				}
			}

			$wk_clisting_array = array(
					'menulink_title',
					'sort_menu',
					'bundles_page',
					'picture_width',
					'picture_height',
					'custom_css',
					'crossselling_details',
					'crossselling_details2',
					'theme'
				);

			if (isset($this->request->post['wk_clisting_status'])) {
				$data['wk_clisting_status'] = $this->request->post['wk_clisting_status'];
			} else {
				$data['wk_clisting_status'] = $this->config->get('wk_clisting_status');
			}

			foreach ($wk_clisting_array as $listing_value) {
				if (isset($this->request->post['wk_clisting_' . $listing_value])) {
					$data['wk_clisting_' . $listing_value] = $this->request->post['wk_clisting_' . $listing_value];
				} else {
					$data['wk_clisting_' . $listing_value] = $this->config->get('wk_clisting_' . $listing_value);
				}
			}

			$category_data = array();

			$data['category'] = $this->model_extension_module_wk_sell->getCategories();

			foreach ($data['category'] as $category) {

				$category_data[$category['category_id']] = $this->model_extension_module_wk_sell->getSubCategories($category['category_id']);
			}

			$data['category_data'] = $category_data;

			$data['product_data'] = $this->model_extension_module_wk_sell->getProducts();

			$this->load->model('tool/image');

			$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
			$data['languages'] = $this->model_localisation_language->getLanguages();

			if (isset($this->request->post['config_logo'])) {
				$data['config_logo'] = $this->request->post['config_logo'];
			} else {
				$data['config_logo'] = $this->config->get('config_logo');
			}

			if (isset($this->request->post['config_logo']) && is_file(DIR_IMAGE . $this->request->post['config_logo'])) {
				$data['logo'] = $this->model_tool_image->resize($this->request->post['config_logo'], 100, 100);
			} elseif ($this->config->get('config_logo') && is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
				$data['logo'] = $this->model_tool_image->resize($this->config->get('config_logo'), 100, 100);
			} else {
				$data['logo'] = $this->model_tool_image->resize('no_image.png', 100, 100);
			}
	  }
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/wk_crosssell', $data));
	}

	public function deploy() {
		$lang_array = $this->load->language('extension/module/wk_crosssell');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

			$fields = array(
				'origin_product',
				'origin_category',
				'origin_subcategory',
				'destiny_product',
				'destiny_category',
				'destiny_subcategory'
				);

			foreach ($fields as $field) {
				if (isset($this->request->post['wk_cdeploy_' . $field]))
					$this->request->post['wk_cdeploy_' . $field] = implode(',', $this->request->post['wk_cdeploy_' . $field]);
			}

			$this->model_setting_setting->editSetting('wk_cdeploy', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('extension/module/wk_crosssell', 'user_token=' . $this->session->data['user_token'], 'SSL'));
		}

		$this->index();
	}

	public function vendor() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('extension/module/wk_sell');

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
				'filter_name'  => $filter_name,
				'start'        => 0,
				'limit'        => $limit
			);

			$results = $this->model_extension_module_wk_sell->getVendors($data);

			foreach ($results as $result) {

				$json[] = array(
					'vendor_id' => $result['customer_id'],
					'name'       => $result['name']
				);
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function stats()	{
		$this->load->model('extension/module/wk_crosssell');
		$this->load->model('extension/module/wk_sell');

		if (isset($this->request->get['start_limit'])) {
			$data['start'] = $this->request->get['start_limit'];
		} else {
			$data['start'] = 0;
		}

		$stats = $this->model_extension_module_wk_crosssell->getStats($data);
		$statistics = array();

		if ($stats) {
			foreach ($stats as $stat) {
				$childs = array();

				$parent = $this->model_extension_module_wk_sell->getProductName($stat['parent_product']);

				$child_products = explode(',', $stat['child_products']);
				foreach ($child_products as $child) {
					$childs[] = $this->model_extension_module_wk_sell->getProductName($child);
				}

				$childs = implode(', ', $childs);

				$statistics['table_data'][] = array(
					'vendor_id'	  => $stat['vendor_id'],
					'store_id'	  => $stat['store_id'],
					'vendor_name'	=> $stat['vendor_name'],
					'date_added'	=> $stat['date_added'],
					'date_start'	=> $stat['date_start'],
					'date_end'		=> $stat['date_end'],
					'countdown'		=> $stat ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
					'units'			=> $stat['quantity'],
					'parents'		=> $parent,
					'childs'		=> $childs,
					'delete'		=> $this->url->link('extension/module/wk_crosssell/delete', 'crosssell_id=' . $stat['crosssell_id'] . '&user_token='. $this->session->data['user_token'], true)
					);
			}
			$statistics['total'] = count($stats);
			$statistics['all'] = $this->model_extension_module_wk_crosssell->getTotalStats();
		} else {
			$statistics = false;
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($statistics));
	}

	public function export()	{

		$this->load->language('extension/module/wk_crosssell');
		$this->load->model('extension/module/wk_crosssell');
		$this->load->model('extension/module/wk_sell');

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {

			$data = array(
				'start'		=> 0,
				'limit'		=> 9999,
				'from_date'	=> $this->request->post['date_from'],
				'till_date'	=> $this->request->post['date_till']
				);

			$stats = $this->model_extension_module_wk_crosssell->getStats($data);

			$statistics = array();

			if ($stats) {
				foreach ($stats as $stat) {

					$childs = array();

					$parent = $this->model_extension_module_wk_sell->getProductName($stat['parent_product']);

					$child_products = explode(',', $stat['child_products']);
					foreach ($child_products as $child) {
						$childs[] = $this->model_extension_module_wk_sell->getProductName($child);
					}

					$childs = implode(' || ', $childs);

					$statistics['table_data'][] = array(
						$this->language->get('column_vendor')		=> $stat['vendor_name'],
						$this->language->get('column_added_on')		=> $stat['date_added'],
						$this->language->get('column_start_date')	=> $stat['date_start'],
						$this->language->get('column_end_date')		=> $stat['date_end'],
						$this->language->get('column_countdown')	=> $stat ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
						$this->language->get('column_units')		=> $stat['quantity'],
						$this->language->get('column_parent_product')	=> $parent,
						$this->language->get('column_child_product')	=> $childs,
						$this->language->get('column_condition')	=> 'Cross sell'
						);
				}
				$exports = $statistics['table_data'];
			}

			// file name for download
			$fileName = "upsell_export_data(" . date('Y-m-d H:i:s') . ").xls";

			if (isset($exports)) {
				function filterData(&$str) {
					$str = preg_replace("/\t/", "\\t", $str);
					$str = preg_replace("/\r?\n/", "\\n", $str);
					if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
				}

				// headers for download
				header("Content-Disposition: attachment; filename=\"$fileName\"");
				header("Content-Type: application/vnd.ms-excel");

				$flag = false;
				foreach($exports as $row) {
					if(!$flag) {
						// display column names as first row
						echo implode("\t", array_keys($row)) . "\n";
						$flag = true;
					}
					// filter data
					array_walk($row, 'filterData');
					echo implode("\t", array_values($row)) . "\n";
				}
				exit;
			}
		}
		$this->error['warning'] = $this->language->get('error_export_warning');
		$this->index();
	}

	public function delete() {

		$this->load->language('extension/module/wk_crosssell');
		if (isset($this->request->get['crosssell_id']) && $this->request->get['crosssell_id']) {
			$this->load->model('extension/module/wk_crosssell');
			$this->model_extension_module_wk_crosssell->deleteCrosssell($this->request->get['crosssell_id']);
			$this->session->data['success'] = $this->language->get('text_success_delete');
			$this->response->redirect($this->url->link('extension/module/wk_crosssell', 'user_token=' . $this->session->data['user_token'], 'SSL'));
		}
		$this->error['warning'] = $this->language->get('text_warning_delete');
		$this->response->redirect($this->url->link('extension/module/wk_crosssell', 'user_token=' . $this->session->data['user_token'], 'SSL'));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/wk_crosssell')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}
}
