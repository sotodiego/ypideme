<?php
class ControllerExtensionModuleMahardhiNewsLetter extends Controller {
	private $error = array();

	public function install() {		
		$this->load->model('newsletter/mahardhi_newsletter');
		$this->load->model('user/user_group');

		//add permission
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'newsletter/mahardhi_newsletter');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'newsletter/mahardhi_newsletter');

		$this->model_newsletter_mahardhi_newsletter->install();
	}

	public function uninstall() {		
		$this->load->model('newsletter/mahardhi_newsletter');
		$this->load->model('user/user_group');

		// Remove permission
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'newsletter/mahardhi_newsletter');
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'newsletter/mahardhi_newsletter');

		$this->model_newsletter_mahardhi_newsletter->uninstall();
	}
	
	public function index() {
		$this->load->language('extension/module/mahardhi_newsletter');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');	
		$this->load->model('setting/module');
		$this->load->model('newsletter/mahardhi_newsletter');
		

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('mahardhi_newsletter', $this->request->post);
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

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_name'] = $this->language->get('entry_name');
		$data['entry_popup'] = $this->language->get('entry_popup');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/mahardhi_newsletter', 'user_token=' . $this->session->data['user_token'], true)
			);
		}else{
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/mahardhi_newsletter', 'user_token=' . $this->session->data['user_token']. '&module_id=' . $this->request->get['module_id'], true)
			);
		}

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/mahardhi_newsletter', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/mahardhi_newsletter', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
		}

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($module_info['name'])) {
			$data['name'] = $module_info['name'];
		} else {
			$data['name'] = '';
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($module_info['status'])) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = '';
		}		
		
		if (isset($this->request->post['popup'])) {
			$data['popup'] = $this->request->post['popup'];
		} elseif (!empty($module_info['popup'])) {
			$data['popup'] = $module_info['popup'];
		} else {
			$data['popup'] = '';
		}
		

		//Get Subscriber List
		$user_total = $this->model_newsletter_mahardhi_newsletter->getTotalUsers();
		
		if (isset($this->request->get['page'])) {			
			$page = $this->request->get['page'];			
		} else {			
			$page = 1;			
		}
		
		$data['page_nav'] = ($page-1) * $this->config->get('config_limit_admin');
		
		$url = '';
		
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
		$data['pages'] = array(
			'start'           => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'           => $this->config->get('config_limit_admin')
		);
		
		$results = $this->model_newsletter_mahardhi_newsletter->getList($data);		
		//print_r($results);

		$data['users'] = array();

		foreach ($results as $result) {		 
		 	$action = array();
			
			$action[] = array(
				'text' => $this->language->get('text_delete'),
				'href' => $this->url->link('extension/module/mahardhi_newsletter/unsubscribe', 'user_token=' . $this->session->data['user_token'] . '&user_id=' . $result['id'] . '&module_id=' . $this->request->get['module_id'] . $url, true)
			);
			
			$data['users'][] = array(
				'id' 			=> $result['id'],				
				'email'      => $result['email'],
				'selected'   	=> isset($this->request->post['selected']) && in_array($result['id'], $this->request->post['selected']),
				'action'     	=> $action,
				'start'         => ($page - 1) * $this->config->get('config_limit_admin'),
				'limit'         => $this->config->get('config_limit_admin')
			);
		
		}

		$data['user_token'] = $this->session->data['user_token'];
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/mahardhi_newsletter', $data));
	}

	public function unsubscribe() {
		
		$this->load->model('newsletter/mahardhi_newsletter');		
		
		$data = '';
		
		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			
			foreach ($this->request->post['selected'] as $user_id) {
				$this->model_newsletter_mahardhi_newsletter->delete($user_id);                
	  		}
			
			$this->session->data['success'] = $this->language->get('text_success');
			
			$url = '';
			
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
			
			$this->redirect($this->url->link('extension/module/mahardhi_newsletter', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id']  . $url, true));
			
		} else if( isset($this->request->get['user_id']) ) {
			
			$data = $this->request->get['user_id'];
			
			$this->model_newsletter_mahardhi_newsletter->delete($data);
			
			if (isset($this->request->get['page'])) {
				$page = $this->request->get['page'];
			} else {
				$page = 1;
			}
			
			$url = '';
			
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
			
			$this->response->redirect($this->url->link('extension/module/mahardhi_newsletter', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id']  . $url, true));
			
		
		} else {
		
			$this->index();
			
		}		
		
	}

	protected function validateDelete() {
    	if (!$this->user->hasPermission('modify', 'extension/module/mahardhi_newsletter')) {
      		$this->error['warning'] = $this->language->get('error_permission');  
    	}
		
		return !$this->error;
  	}

	protected function validate() {
		
		if (!$this->user->hasPermission('modify', 'extension/module/mahardhi_newsletter')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		return !$this->error;
	}
}
