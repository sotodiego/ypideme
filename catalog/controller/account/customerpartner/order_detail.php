<?php
require_once DIR_SYSTEM . 'ocMpTrait.php';
class ControllerAccountCustomerpartnerOrderdetail extends Controller {
	use OcMpTrait;

	private $error = array();

	public function index() {
		$this->checkMpModuleStatus();

		$data = array();
		$data = array_merge($data,$this->load->language('account/customerpartner/order_detail'));

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/customerpartner/order_detail', '', true);
			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->model('account/customerpartner');

		$this->document->setTitle($data['heading_title']);

      	$data['breadcrumbs'] = array();

      	$data['breadcrumbs'][] = array(
        	'text'      => $data['text_home'],
			'href'      => $this->url->link('common/home', '', true),
      	);

      	$data['breadcrumbs'][] = array(
        	'text'      => $data['text_account'],
			'href'      => $this->url->link('account/account', '', true),
      	);

      	$data['breadcrumbs'][] = array(
			'text' => $data['text_orderinfo'],
			'href' => $this->url->link('account/order/info', 'order_id=' . $this->request->get['order_id'], true)
		);

      	$data['breadcrumbs'][] = array(
        	'text'      => $data['text_orderdetaillist'],
			'href'      => $this->url->link('account/customerpartner/order_detail', '', true),
      	);

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'o.order_id';
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

		$filter_array = array(
			'sort'            => $sort,
			'order'           => $order,
			'start'           => ($page - 1) * 10,
			'limit'           => 10
		);

		$orders = $this->model_account_customerpartner->getOrderDetails($filter_array,$this->request->get['order_id'],$this->request->get['product_id']);

		$orderstotal = $this->model_account_customerpartner->getOrderDetailsTotal($filter_array,$this->request->get['order_id'],$this->request->get['product_id']);

		$data['orders'] = $orders;

		$this->load->model('mp_localisation/order_status');
		$data['order_statuses'] = $this->model_mp_localisation_order_status->getOrderStatuses();

		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_dateadded'] = $this->url->link('account/customerpartner/order_detail', '' . '&sort=date_added&order_id='.$this->request->get['order_id'].'&product_id='.$this->request->get['product_id'].'' . $url, true);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $orderstotal;
		$pagination->page = $page;
		$pagination->limit = 10;
		$pagination->url = $this->url->link('account/customerpartner/orderlist'.$url,'&page={page}', true);

		$data['pagination'] = $pagination->render();
		$data['results'] = sprintf($data['text_pagination'], ($orderstotal) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($orderstotal - 10)) ? $orderstotal : ((($page - 1) * 10) + 10), $orderstotal, ceil($orderstotal / 10));

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['current'] = $this->url->link('account/customerpartner/order_detail', '', true);

		$data['back'] = $this->url->link('account/account', '', true);

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

		$this->response->setOutput($this->load->view('account/customerpartner/order_detail' , $data));

	}
}
?>
