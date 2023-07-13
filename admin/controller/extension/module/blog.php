<?php
class ControllerExtensionModuleBlog extends Controller {

	private $error = array();
	public function install() {
		$this->load->model('blog/article');
		$this->load->model('blog/blog');
		$this->load->model('setting/setting');
		$this->load->model('blog/comment');
		$this->load->model('user/user_group');		

		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'blog/article');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'blog/article');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'blog/blog');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'blog/blog');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'blog/comment');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'blog/comment');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'blog/setting');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'blog/setting');


		$this->model_blog_article->install();

		$data = array(
            'module_blog_limit' 			=> '10',
            'module_blog_meta_title'    	=> 'Blog',
            'module_blog_meta_description' 	=> 'Blog Description',
            'module_blog_meta_keyword'    	=> 'Blog Keyword',
            'module_blog_width' 			=> '300',
            'module_blog_height'    		=> '300',
            'module_blog_detail_width' 		=> '800',
            'module_blog_detail_height'    	=> '800',
            'module_blog_date_format'		=> 'd M Y'
        );

        $this->model_setting_setting->editSetting('module_blog', $data, 0);
	}

	public function uninstall() {
		$this->load->model('blog/article');
		$this->load->model('blog/blog');
		$this->load->model('blog/comment');
		$this->load->model('user/user_group');

		// Remove permission
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'blog/article');
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'blog/article');
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'blog/blog');
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'blog/blog');
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'blog/comment');
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'blog/comment');
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'blog/setting');
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'blog/setting');


		$this->model_blog_article->uninstall();
	}

	public function index(){
		
		$this->load->language('extension/module/blog');

		$this->document->setTitle($this->language->get('heading_title'));

		
		$this->load->model('setting/module');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('blog', $this->request->post);
			} else {
				$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token']. '&type=module', true));
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
			$data['error_width'] = $this->error['width'];
		} else {
			$data['error_width'] = '';
		}

		if (isset($this->error['height'])) {
			$data['error_height'] = $this->error['height'];
		} else {
			$data['error_height'] = '';
		}

		if (isset($this->error['title'])) {
			$data['error_title'] = $this->error['title'];
		} else {
			$data['error_title'] = '';
		}
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_module'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true)
		);

		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/blog', 'user_token=' . $this->session->data['user_token'], true)
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/blog', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
			);
		}

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/blog', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/blog', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token']. '&type=module', true);
		$data['addcat'] = $this->url->link('blog/blog', 'user_token=' . $this->session->data['user_token'], true);
		$data['addarticle'] = $this->url->link('blog/article', 'user_token=' . $this->session->data['user_token'], true);
		$data['comment'] = $this->url->link('blog/comment', 'user_token=' . $this->session->data['user_token'], true);
		$data['blog_setting'] = $this->url->link('blog/setting', 'user_token=' . $this->session->data['user_token'], true);
		

		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);			
		}

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($module_info)) {
			$data['name'] = $module_info['name'];
		} else {
			$data['name'] = '';
		}

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['module_description'])) {
			$data['module_description'] = $this->request->post['module_description'];
		} elseif (!empty($module_info)) {
			$data['module_description'] = $module_info['module_description'];
		} else {
			$data['module_description'] = array();
		}
		

		if (isset($this->request->post['width'])) {
			$data['width'] = $this->request->post['width'];
		} elseif (!empty($module_info)) {
			$data['width'] = $module_info['width'];
		} else {
			$data['width'] = 200;
		}

		if (isset($this->request->post['height'])) {
			$data['height'] = $this->request->post['height'];
		} elseif (!empty($module_info)) {
			$data['height'] = $module_info['height'];
		} else {
			$data['height'] = 200;
		}			

		if (isset($this->request->post['rows'])) {
            $data['rows'] = $this->request->post['rows'];
        } elseif (!empty($module_info)) {
            $data['rows'] = $module_info['rows'];
        } else {
            $data['rows'] = 1;
        }

        if (isset($this->request->post['items'])) {
            $data['items'] = $this->request->post['items'];
        } elseif (!empty($module_info)) {
            $data['items'] = $module_info['items'];
        } else {
            $data['items'] = 4;
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

        if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($module_info)) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = '';
		}

		
		if (isset($this->request->post['date_format'])) {
			$data['date_format'] = $this->request->post['date_format'];
		} elseif (!empty($module_info)) {
			$data['date_format'] = $module_info['date_format'];
		} else {
			$data['date_format'] = 'd M Y';
		}		

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

        if (isset($this->error['width'])) {
            $data['error_width'] = $this->error['width'];
        } else {
            $data['error_width'] = '';
        }

        if (isset($this->error['height'])) {
            $data['error_height'] = $this->error['height'];
        } else {
            $data['error_height'] = '';
        }        	

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/blog', $data));

	}

	protected function validate(){
		if (!$this->user->hasPermission('modify', 'extension/module/blog')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		foreach ($this->request->post['module_description'] as $language_id => $value) {			
			if ((utf8_strlen($value['title']) < 1) || (utf8_strlen($value['title']) > 255)) {
				$this->error['title'][$language_id] = $this->language->get('error_title');
			}
		}	

		if (!$this->request->post['width']) {
			$this->error['width'] = $this->language->get('error_width');
		}

		if (!$this->request->post['height']) {
			$this->error['height'] = $this->language->get('error_height');
		}	

        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }
		return !$this->error;
	}
}
