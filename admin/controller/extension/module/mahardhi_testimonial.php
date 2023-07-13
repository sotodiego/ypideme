<?php
class ControllerExtensionModuleMahardhiTestimonial extends Controller {
	private $error = array();

	public function install() {
		$this->load->model('catalog/mahardhi_testimonial');
		$this->load->model('user/user_group');

		// Add Permission
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'catalog/mahardhi_testimonial');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'catalog/mahardhi_testimonial');

		$this->model_catalog_mahardhi_testimonial->install();
	}

	public function uninstall() {
		$this->load->model('catalog/mahardhi_testimonial');
		$this->load->model('user/user_group');

		// Remove permission
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'catalog/mahardhi_testimonial');
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'catalog/mahardhi_testimonial');

		$this->model_catalog_mahardhi_testimonial->uninstall();
	}

	public function index() {
		$this->load->language('extension/module/mahardhi_testimonial');

		$this->load->model('setting/setting');
		$this->load->model('setting/module');
		$this->load->model('catalog/mahardhi_testimonial');

		$this->document->setTitle($this->language->get('heading_title'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('mahardhi_testimonial', $this->request->post);
			} else {
				$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
			}
			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		$this->load->model('localisation/language');

		$data['languages'] = array();

		$languages = $this->model_localisation_language->getLanguages();

		foreach ($languages as $language){
			if ($language['status']) {
				$data['languages'][] = array(
					'name'  => $language['name'],
					'language_id' => $language['language_id'],
					'code' => $language['code']
				);
			}
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}
		

		if (isset($this->error['width'])) {
			$data['error_width_height'] = $this->error['width'];
		} else {
			$data['error_width_height'] = '';
		}

		if (isset($this->error['height'])) {
			$data['error_width_height'] = $this->error['height'];
		} else {
			$data['error_width_height'] = '';
		}

		$data['action'] = $this->url->link('extension/module/mahardhi_testimonial', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		$data['testimoniallist'] = $this->url->link('catalog/mahardhi_testimonial', 'user_token=' . $this->session->data['user_token'], true);

		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
			$data['action'] = $this->url->link('extension/module/mahardhi_testimonial', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($module_info)) {
			$data['name'] = $module_info['name'];
		} else {
			$data['name'] = '';
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($module_info)) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = 1;
		}

		if (isset($this->request->post['width'])) {
			$data['width'] = $this->request->post['width'];
		} elseif (!empty($module_info)) {
			$data['width'] = $module_info['width'];
		} else {
			$data['width'] = 150;
		}

		if (isset($this->request->post['height'])) {
			$data['height'] = $this->request->post['height'];
		} elseif (!empty($module_info)) {
			$data['height'] = $module_info['height'];
		} else {
			$data['height'] = 150;
		}

		if (isset($this->request->post['rows'])) {
			$data['rows'] = $this->request->post['rows'];
		} elseif (!empty($module_info)) {
			$data['rows'] = $module_info['rows'];
		} else {
			$data['rows'] = 1;
		}

		if (isset($this->request->post['limit'])) {
			$data['limit'] = $this->request->post['limit'];
		} elseif (!empty($module_info)) {
			$data['limit'] = $module_info['limit'];
		} else {
			$data['limit'] = 10;
		}

		if (isset($this->request->post['items'])) {
			$data['items'] = $this->request->post['items'];
		} elseif (!empty($module_info)) {
			$data['items'] = $module_info['items'];
		} else {
			$data['items'] = 1;
		}

		if (isset($this->request->post['auto'])) {
			$data['auto'] = $this->request->post['auto'];
		} elseif (!empty($module_info)) {
			$data['auto'] = $module_info['auto'];
		} else {
			$data['auto'] = 1;
		}

		if (isset($this->request->post['speed'])) {
			$data['speed'] = $this->request->post['speed'];
		} elseif (!empty($module_info)) {
			$data['speed'] = $module_info['speed'];
		} else {
			$data['speed'] = 3000;
		}

		if (isset($this->request->post['navigation'])) {
			$data['navigation'] = $this->request->post['navigation'];
		} elseif (!empty($module_info)) {
			$data['navigation'] = $module_info['navigation'];
		} else {
			$data['navigation'] = 1;
		}

		if (isset($this->request->post['pagination'])) {
			$data['pagination'] = $this->request->post['pagination'];
		} elseif (!empty($module_info)) {
			$data['pagination'] = $module_info['pagination'];
		} else {
			$data['pagination'] = 0;
		}

		if (isset($this->request->post['title'])) {
			$data['title'] = $this->request->post['title'];
		} elseif (!empty($module_info) && isset($module_info['title'])) {
			$data['title'] = $module_info['title'];
		} else {
			$data['title'] = array();
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'href'      => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
			'text'      => $this->language->get('text_home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('extension/module/mahardhi_testimonial', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['header'] = $this->load->controller('common/header');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/mahardhi_testimonial', $data));
	}
	
	private function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/mahardhi_testimonial')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['width']) {
			$this->error['width'] = $this->language->get('error_width_height');
		}

		if (!$this->request->post['height']) {
			$this->error['height'] = $this->language->get('error_width_height');
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		return !$this->error;
	}
}
?>