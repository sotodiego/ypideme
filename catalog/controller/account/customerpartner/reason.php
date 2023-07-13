<?php
require_once DIR_SYSTEM . 'ocMpTrait.php';
class ControllerAccountCustomerpartnerReason extends Controller {
	use OcMpTrait;
	private $error = array();

	public function index() {
		$this->checkMpModuleStatus();
		$this->load->language('account/customerpartner/reason');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('customerpartner/reason');

		$this->getList();
	}

	public function add() {

		$this->load->language('account/customerpartner/reason');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('customerpartner/reason');

		if ($this->request->post) {
		  function clean(&$item) {
		    $item = strip_tags(trim($item));
		  }
		  array_walk_recursive($this->request->post, 'clean');
		}

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {

			$this->model_customerpartner_reason->addReason($this->request->post);

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

			$this->response->redirect($this->url->link('account/customerpartner/reason', $url, true));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('account/customerpartner/reason');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('customerpartner/reason');

		if ($this->request->post) {
		  function clean(&$item) {
		    $item = strip_tags(trim($item));
		  }

		  array_walk_recursive($this->request->post, 'clean');
		}

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {

			$this->model_customerpartner_reason->editReason($this->request->get['reason_id'], $this->request->post);

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

			$this->response->redirect($this->url->link('account/customerpartner/reason', $url, true));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('account/customerpartner/reason');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('customerpartner/reason');

		if (isset($this->request->post['selected'])) {
			foreach ($this->request->post['selected'] as $reason_id) {
				$this->model_customerpartner_reason->deleteReason($reason_id);
			}

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

			$this->response->redirect($this->url->link('account/customerpartner/reason', $url, true));
		}

		$this->getList();
	}

	protected function getList() {

		$data = $this->load->language('account/customerpartner/reason');

		$this->load->model('account/customerpartner');

    $this->load->model('customerpartner/reason');

		$data['chkIsPartner'] = $this->model_account_customerpartner->chkIsPartner();

		if(!$data['chkIsPartner'] || (isset($this->session->data['marketplace_seller_mode']) && !$this->session->data['marketplace_seller_mode']))
		$this->response->redirect($this->url->link('account/account', '', true));

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'mprd.title';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . urlencode(html_entity_decode($this->request->get['filter_status'], ENT_QUOTES, 'UTF-8'));
		}

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
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', true),

		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_account'),
			'href'      => $this->url->link('account/account', '', true),
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('account/customerpartner/reason', $url, true),
		);

		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = null;
		}

		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = null;
		}

		$data['add'] = $this->url->link('account/customerpartner/reason/add', $url, true);
		$data['delete'] = $this->url->link('account/customerpartner/reason/delete', $url, true);

		$data['reasons'] = array();

		$filter_data = array(
			'filter_name'	  => $filter_name,
			'filter_status'	  => $filter_status,
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$reason_total = $this->model_customerpartner_reason->getTotalReasons($filter_data);

		$results = $this->model_customerpartner_reason->getReasons($filter_data);

		foreach ($results as $result) {
			$data['reasons'][] = array(
				'reason_id' => $result['reason_id'],
				'title'          => $result['title'],
				'status'         => $result['status'],
				'edit'           => $this->url->link('account/customerpartner/reason/edit', 'reason_id=' . $result['reason_id'] . $url, true)
			);
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

		$data['sort_title'] = $this->url->link('account/customerpartner/reason', 'sort=id.title' . $url, true);
		$data['sort_sort_order'] = $this->url->link('account/customerpartner/reason', 'sort=i.sort_order' . $url, true);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $reason_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('account/customerpartner/reason', $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($reason_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($reason_total - $this->config->get('config_limit_admin'))) ? $reason_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $reason_total, ceil($reason_total / $this->config->get('config_limit_admin')));

		$data['filter_name'] = $filter_name;
		$data['filter_status'] = $filter_status;
		$data['sort'] = $sort;
		$data['order'] = $order;

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

		$this->response->setOutput($this->load->view('account/customerpartner/reason_list', $data));
	}

	protected function getForm() {

		$data = $this->load->language('account/customerpartner/reason');

		$this->load->model('account/customerpartner');

		$data['chkIsPartner'] = $this->model_account_customerpartner->chkIsPartner();

		if(!$data['chkIsPartner'] || (isset($this->session->data['marketplace_seller_mode']) && !$this->session->data['marketplace_seller_mode']))
		$this->response->redirect($this->url->link('account/account', '', true));

		$data['heading_title'] = $data['text_form'] = !isset($this->request->get['reason_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['title'])) {
			$data['error_title'] = $this->error['title'];
		} else {
			$data['error_title'] = array();
		}

		if (isset($this->error['description'])) {
			$data['error_description'] = $this->error['description'];
		} else {
			$data['error_description'] = array();
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

		$this->document->addScript('catalog/view/javascript/wk_summernote/summernote.js');

		$this->document->addStyle('catalog/view/javascript/wk_summernote/summernote.css');

		$this->document->addStyle('catalog/view/theme/default/stylesheet/MP/sell.css');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', true),
			'separator' => false
		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_account'),
			'href'      => $this->url->link('account/account', '', true),
			'separator' => $this->language->get('text_separator')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('account/customerpartner/reason', $url, true),
			'separator' => $this->language->get('text_separator')
		);

		if (!isset($this->request->get['reason_id'])) {
			$data['action'] = $this->url->link('account/customerpartner/reason/add', $url, true);
		} else {
			$data['action'] = $this->url->link('account/customerpartner/reason/edit', 'reason_id=' . $this->request->get['reason_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('account/customerpartner/reason', $url, true);

		if (isset($this->request->get['reason_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$reason_info = $this->model_customerpartner_reason->getreason($this->request->get['reason_id']);
			if (empty($reason_info)) {
				$this->response->redirect($this->url->link('account/customerpartner/reason', $url, true));
			}
		}

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['reason_description'])) {
			$data['reason_description'] = $this->request->post['reason_description'];
		} elseif (isset($this->request->get['reason_id'])) {
			$data['reason_description'] = $this->model_customerpartner_reason->getreasonDescriptions($this->request->get['reason_id']);
		} else {
			$data['reason_description'] = array();
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($reason_info)) {
			$data['status'] = $reason_info['status'];
		} else {
			$data['status'] = false;
		}

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

		$this->response->setOutput($this->load->view('account/customerpartner/reason_form', $data));
	}

	protected function validateForm() {

		foreach ($this->request->post['reason_description'] as $language_id => $value) {
			if ((utf8_strlen($value['title']) < 3) || (utf8_strlen($value['title']) > 64)) {
				$this->error['title'][$language_id] = $this->language->get('error_title');
			}

			if (utf8_strlen(strip_tags(html_entity_decode($value['description']))) < 3) {
				$this->error['description'][$language_id] = $this->language->get('error_description');
			}
      if (utf8_strlen(strip_tags(html_entity_decode($value['title']))) < 3) {
				$this->error['title'][$language_id] = $this->language->get('error_title');
			}
		}
		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}

}
