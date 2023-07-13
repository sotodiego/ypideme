<?php
class ControllerBlogSetting extends Controller {
	
	private $error = array();

    public function install() {

        $this->load->model('blog/setting');
        $this->load->model('user/user_group');

        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'blog/setting');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'blog/setting');

    }
    public function uninstall() {
        $this->load->model('blog/setting');
        $this->load->model('user/user_group');

        // Remove permission
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'blog/setting');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'blog/setting');
       
    }

	public function index() {

		$this->load->language('blog/setting');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {

	       $this->model_setting_setting->editSetting('module_blog', $this->request->post);

	       $this->session->data['success'] = $this->language->get('text_success');

	       $this->response->redirect($this->url->link('blog/setting', 'user_token=' . $this->session->data['user_token'], true));
	    }

		if(isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['blog_limit'])) {
            $data['error_blog_limit'] = $this->error['blog_limit'];
        } else {
            $data['error_blog_limit'] = '';
        }

        if (isset($this->error['blog_meta_title'])) {
            $data['error_blog_meta_title'] = $this->error['blog_meta_title'];
        } else {
            $data['error_blog_meta_title'] = '';
        }

        if (isset($this->error['blog_meta_description'])) {
            $data['error_blog_meta_description'] = $this->error['blog_meta_description'];
        } else {
            $data['error_blog_meta_description'] = '';
        }

        if (isset($this->error['blog_meta_keyword'])) {
            $data['error_blog_meta_keyword'] = $this->error['blog_meta_keyword'];
        } else {
            $data['error_blog_meta_keyword'] = '';
        }

        if (isset($this->error['blog_width'])) {
            $data['error_blog_width'] = $this->error['blog_width'];
        } else {
            $data['error_blog_width'] = '';
        }

        if (isset($this->error['blog_height'])) {
            $data['error_blog_height'] = $this->error['blog_height'];
        } else {
            $data['error_blog_height'] = '';
        }

        if (isset($this->error['blog_detail_width'])) {
            $data['error_blog_detail_width'] = $this->error['blog_detail_width'];
        } else {
            $data['error_blog_detail_width'] = '';
        }

        if (isset($this->error['blog_detail_height'])) {
            $data['error_blog_detail_height'] = $this->error['blog_detail_height'];
        } else {
            $data['error_blog_detail_height'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_edit'),
            'href' => $this->url->link('blog/setting', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('blog/setting', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token']. '&type=module', true);

        if (isset($this->request->post['module_blog_limit'])) {
            $data['module_blog_limit'] = $this->request->post['module_blog_limit'];
        } else {
            $data['module_blog_limit'] = $this->config->get('module_blog_limit');
        }

        if (isset($this->request->post['module_blog_meta_title'])) {
            $data['module_blog_meta_title'] = $this->request->post['module_blog_meta_title'];
        } else {
            $data['module_blog_meta_title'] = $this->config->get('module_blog_meta_title');
        }

        if (isset($this->request->post['module_blog_meta_description'])) {
            $data['module_blog_meta_description'] = $this->request->post['module_blog_meta_description'];
        } else {
            $data['module_blog_meta_description'] = $this->config->get('module_blog_meta_description');
        }

        if (isset($this->request->post['module_blog_meta_keyword'])) {
            $data['module_blog_meta_keyword'] = $this->request->post['module_blog_meta_keyword'];
        } else {
            $data['module_blog_meta_keyword'] = $this->config->get('module_blog_meta_keyword');
        }

        if (isset($this->request->post['module_blog_width'])) {
            $data['module_blog_width'] = $this->request->post['module_blog_width'];
        } else {
            $data['module_blog_width'] = $this->config->get('module_blog_width');
        }

        if (isset($this->request->post['module_blog_height'])) {
            $data['module_blog_height'] = $this->request->post['module_blog_height'];
        } else {
            $data['module_blog_height'] = $this->config->get('module_blog_height');
        }  

        if (isset($this->request->post['module_blog_detail_width'])) {
            $data['module_blog_detail_width'] = $this->request->post['module_blog_detail_width'];
        } else {
            $data['module_blog_detail_width'] = $this->config->get('module_blog_detail_width');
        }  

        if (isset($this->request->post['module_blog_detail_height'])) {
            $data['module_blog_detail_height'] = $this->request->post['module_blog_detail_height'];
        } else {
            $data['module_blog_detail_height'] = $this->config->get('module_blog_detail_height');
        } 

        if (isset($this->request->post['module_blog_date_format'])) {
            $data['module_blog_date_format'] = $this->request->post['module_blog_date_format'];
        } else {
            $data['module_blog_date_format'] = $this->config->get('module_blog_date_format');
        }   

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('blog/setting', $data));

	}	
	

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'blog/setting')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['module_blog_limit']) {
            $this->error['blog_limit'] = $this->language->get('error_blog_limit');
        }

        if (!$this->request->post['module_blog_meta_title']) {
            $this->error['blog_meta_title'] = $this->language->get('error_blog_meta_title');
        }

        if (!$this->request->post['module_blog_meta_description']) {
            $this->error['blog_meta_description'] = $this->language->get('error_blog_meta_description');
        }

        if (!$this->request->post['module_blog_meta_keyword']) {
            $this->error['blog_meta_keyword'] = $this->language->get('error_blog_meta_keyword');
        }

        if (!$this->request->post['module_blog_width']) {
            $this->error['blog_width'] = $this->language->get('error_blog_width');
        }

        if (!$this->request->post['module_blog_height']) {
            $this->error['blog_height'] = $this->language->get('error_blog_height');
        }

        if (!$this->request->post['module_blog_detail_width']) {
            $this->error['blog_detail_width'] = $this->language->get('error_blog_detail_width');
        }

        if (!$this->request->post['module_blog_detail_height']) {
            $this->error['blog_detail_height'] = $this->language->get('error_blog_detail_height');
        }		

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}	
}
