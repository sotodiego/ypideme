<?php
class ControllerBlogComment extends Controller {
	private $error = array();

	public function install() {
		$this->load->model('blog/comment');
		$this->load->model('user/user_group');

		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'blog/comment');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'blog/comment');


		$this->model_blog_article->install();
	}

	public function uninstall() {
		$this->load->model('blog/comment');
		$this->load->model('user/user_group');

		// Remove permission
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'blog/comment');
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'blog/comment');

		$this->model_blog_article->uninstall();
	}

	public function index() {
		$this->load->language('blog/comment');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('blog/comment');

		$this->getList();

	}

	public function add() {
		$this->load->language('blog/comment');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('blog/comment');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			
			$this->model_blog_comment->addComment($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url ='';			

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('blog/comment', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}
	public function edit() {
		$this->load->language('blog/comment');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('blog/comment');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_blog_comment->editComment($this->request->get['comment_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';


			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('blog/comment', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('blog/comment');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('blog/comment');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			$url = '';
			foreach ($this->request->post['selected'] as $comment_id) {
				$this->model_blog_comment->deleteComment($comment_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('blog/comment', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	protected function getList() {

		$url = '';

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'author';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
			$url .= '&order=' . $this->request->get['order'];
		} else {
			$order = 'DESC';
		}

		if (isset($this->request->get['status'])) {
			$status = $this->request->get['status'];
			$url .= '&status=' . $this->request->get['status'];
		} else {
			$status = 'DESC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
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

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_article'),
			'href' => $this->url->link('blog/article', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('blog/comment', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['add'] = $this->url->link('blog/comment/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('blog/comment/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true);


		$data['comments'] = array();

		$filter_data = array(
			'sort'              => $sort,
			'order'             => $order,
			'status' 			=> $status,
			'start'             => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'             => $this->config->get('config_limit_admin')
		);

		$comment_total = $this->model_blog_comment->getTotalComments();	
		$results = $this->model_blog_comment->getComments($filter_data);

		
		foreach ($results as $result) {
			$data['comments'][] = array(
				'comment_id' =>$result['comment_id'],
				'name'		=>$result['name'],
				'article'       => $result['article_id'],
				'author'     => $result['author'],
				'email'     => $result['email'],
				'comment_total' => $comment_total,
				'sort_order'  		=> $result['sort_order'],
				'status'     => ($result['status']) ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'edit'       => $this->url->link('blog/comment/edit', 'user_token=' . $this->session->data['user_token'] . '&comment_id=' . $result['comment_id'] . $url, true)
			);
		}

		$data['user_token'] = $this->session->data['user_token'];

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

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}	

		$data['sort_name'] = $this->url->link('blog/comment', 'user_token=' . $this->session->data['user_token'] . '&sort=author' . $url, true);
		$data['sort_email'] = $this->url->link('blog/comment', 'user_token=' . $this->session->data['user_token'] . '&sort=email' . $url, true);
		$data['sort_order'] = $this->url->link('blog/comment', 'user_token=' . $this->session->data['user_token'] . '&sort=sort_order' . $url, true);
		$data['sort_status'] = $this->url->link('blog/comment', 'user_token=' . $this->session->data['user_token'] . '&sort=status' . $url, true);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['status'])) {
			$url .= '&status=' . $this->request->get['status'];
		}

		$pagination = new Pagination();
		$pagination->total = $comment_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('blog/article', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($comment_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($comment_total - $this->config->get('config_limit_admin'))) ? $comment_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $comment_total, ceil($comment_total / $this->config->get('config_limit_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('blog/comment_list', $data));
	}

	protected function getForm() {
		
		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_form'] = !isset($this->request->get['comment_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		$data['text_none'] = $this->language->get('text_none');
		$data['text_default'] = $this->language->get('text_default');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');

		$data['entry_author'] = $this->language->get('entry_author');		
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');	

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}


		if (isset($this->error['author'])) {
			$data['error_author'] = $this->error['author'];
		} else {
			$data['error_author'] = '';
		}

		if (isset($this->error['comment_text'])) {
			$data['error_text'] = $this->error['comment_text'];
		} else {
			$data['error_text'] = '';
		}

		if (isset($this->error['email'])) {
			$data['error_email'] = $this->error['email'];
		} else {
			$data['error_email'] = '';
		}

		$url = '';


		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
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

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_article'),
			'href' => $this->url->link('blog/article', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('blog/comment', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['comment_id'])) {
			$data['action'] = $this->url->link('blog/comment/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('blog/comment/edit', 'user_token=' . $this->session->data['user_token'] . '&comment_id=' . $this->request->get['comment_id'] . $url, true);
		}


		$data['cancel'] = $this->url->link('blog/comment', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['comment_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$comment_info = $this->model_blog_comment->getComment($this->request->get['comment_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];

		$this->load->model('blog/comment');
		
		
		if (isset($this->request->post['article'])) {
			$articles = $this->request->post['article'];			
		} elseif (!empty($comment_info)) {			
			$articles = $comment_info['article_id'];
		} else {
			$articles = '';
		}
		$this->load->model('blog/article');
		if($articles){
			$articles_info = $this->model_blog_article->getArticle($articles);		

			$data['articles'][] = array(
                'article_id' => $articles_info['article_id'],
                'name'       => $articles_info['name']
            );
		}

		if (isset($this->request->post['author'])) {
			$data['author'] = $this->request->post['author'];
		} elseif (!empty($comment_info)) {
			$data['author'] = $comment_info['author'];
		} else {
			$data['author'] = '';
		}


		if (isset($this->request->post['comment_text'])) {
			$data['comment_text'] = $this->request->post['comment_text'];
		} elseif (!empty($comment_info)) {
			$data['comment_text'] = $comment_info['comment_text'];
		} else {
			$data['comment_text'] = '';
		}

		if (isset($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		} elseif (!empty($comment_info)) {
			$data['email'] = $comment_info['email'];
		} else {
			$data['email'] = '';
		}
		

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($comment_info)) {
			$data['status'] = $comment_info['status'];
		} else {
			$data['status'] = false;
		}

		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($category_list_info)) {
			$data['sort_order'] = $category_list_info['sort_order'];
		} else {
			$data['sort_order'] = 1;
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('blog/comment_form', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'blog/comment')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['author']) < 3) || (utf8_strlen($this->request->post['author']) > 64)) {
			$this->error['author'] = $this->language->get('error_author');
		}

		if ((utf8_strlen($this->request->post['comment_text']) < 25)) {
			$this->error['comment_text'] = $this->language->get('error_text');
		}		

		if ((utf8_strlen($this->request->post['email']) < 1) || !preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $this->request->post['email'])) {
            $this->error['email'] = $this->language->get('error_email');
        }

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'blog/comment')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}
