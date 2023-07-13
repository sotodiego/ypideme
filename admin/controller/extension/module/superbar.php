<?php
class ControllerExtensionModuleSuperbar extends Controller {
	private $error = array();

	public function index() {

		$data = array();
		$data = array_merge($data, $this->load->language('extension/module/superbar'));

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('superbar', $this->request->post);
			$this->model_setting_setting->editSetting('module_superbar', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('extension/module/superbar', 'user_token=' . $this->session->data['user_token'], 'SSL'));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_module'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/superbar', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		$data['action'] = $this->url->link('extension/module/superbar', 'user_token=' . $this->session->data['user_token'], 'SSL');

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', 'SSL');

		$data['border_styles'] = array('none', 'solid', 'dotted', 'inset', 'outset', 'groove', 'ridge');

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$this->load->model('tool/image');

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		$config_array = array(
				'width',
				'border_color',
				'border_style',
				'button_color',
				'color',
				'design_layout_icon',
				'icon_width',
				'icon_height',
				'upsell_tooltip',
				'crosssell_tooltip',
				'gift_tooltip',
				'custom_tooltip',
				'upsell_icon',
				'crosssell_icon',
				'gift_icon',
				'custom_icon',
				'position_hori',
				'hori',
				'position_verti',
				'verti',
				'time',
				'content_time',
				'struck_time',
				'vendor_applicable_status',
				'vendor_applicable'
			);

		if (isset($this->request->post['module_superbar_status'])) {
	 		 $data['superbar_status'] = $this->request->post['module_superbar_status'];
	 	} else {
	 		 $data['superbar_status'] = $this->config->get('module_superbar_status');
	 	}

		foreach ($config_array as $config_key) {
			if (isset($this->request->post['superbar_' . $config_key])) {
				$data['superbar_' . $config_key] = $this->request->post['superbar_' . $config_key];
			} else {
				$data['superbar_' . $config_key] = $this->config->get('superbar_' . $config_key);
			}
		}

		$vendor_applicables = $data['superbar_vendor_applicable'];

		$this->load->model('extension/module/wk_sell');

		$data['vendor_applicables'] = array();

		if ($vendor_applicables)
			foreach ($vendor_applicables as $vendor_applicable) {
				$data['vendor_applicables'][] = $this->model_extension_module_wk_sell->getVendor($vendor_applicable);
			}

		$placeholders = array('design_layout', 'upsell', 'crosssell', 'gift', 'custom');

		foreach ($placeholders as $placeholder) {
			if ($data['superbar_'.$placeholder.'_icon']) {
				$data[$placeholder.'_placeholder'] = $this->model_tool_image->resize($data['superbar_'.$placeholder.'_icon'], 100, 100);
			} else {
				$data[$placeholder.'_placeholder'] = $data['placeholder'];
			}
		}

		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/superbar', $data));
	}

	protected function validate() {

		if (!$this->user->hasPermission('modify', 'extension/module/superbar')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		//store the post value
		$_post  = $this->request->post;

    //float value regex
		$regex_pattern = '/^\d+((\.)?\d{1,8})?$/';

    // float values in the post
    $_array_float = array (
			'superbar_width',
		  'superbar_hori',
			'superbar_verti',
			'superbar_icon_width',
			'superbar_icon_height'
		);

    //loop all the float key for the validation
    foreach ($_array_float as $_pkey) {

			if (!isset($_post[$_pkey]) || !$_post[$_pkey]) {
  		    $this->error['warning'] = $this->language->get('error_'.$_pkey);
      } elseif (!preg_match($regex_pattern, $_post[$_pkey]) || $_post[$_pkey] > 999999) {
  			  $this->error['warning'] = $this->language->get('error_'.$_pkey);
  		}
    }

    // integer keys from the post
		$regex_pattern = '/^[0-9]+$/';

		// int values in the post
		$_array_int = array(
			'superbar_time',
			'superbar_content_time',
			'superbar_struck_time',
		);

		//loop all the float key for the validation
		foreach ($_array_int as $_p_int_key) {
			if (!isset($_post[$_p_int_key]) || !$_post[$_p_int_key]) {
					$this->error['warning'] = $this->language->get('error_'.$_p_int_key);
			} elseif (!preg_match($regex_pattern, $_post[$_p_int_key]) || $_post[$_p_int_key] > 999999) {
					$this->error['warning'] = $this->language->get('error_'.$_p_int_key);
			}
		}

    // $regex_pattern = '/^[a-zA-Z0-9. ]*$/';

		return !$this->error;
	}
}
