<?php
/**
 * Webkul Software.
 * @category  Webkul
 * @author    Webkul
 * @copyright Copyright (c) 2010-2016 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
require_once DIR_SYSTEM . 'webkul/ocPAControllerTrait.php';

class ControllerCustomerpartnerPaRequest extends Controller {
  // use controller traits for the common fucntionality
  use ocPAControllerTrait;
  //private error varibales
  private $error = array();
  // private module code variable
  private $status_code = 'wk_pricealert_status';
  //set module paths
  private $file_path = 'customerpartner/pa_request';
  // private data variable
  private $data = array();
  // __constructer
  public function __construct($registory) {
    parent::__construct($registory);
    $this->data = $this->load->language($this->file_path);
    //load module model for the creating/doping table and managing sql
    $this->load->model($this->file_path);
    // set a object for using model funtions
    $this->helper_pricealert = $this->model_customerpartner_pa_request;
    // set the regisrtry to avail by whole class functions
    $this->registry->set('prolert', new Productalert($this->registry));
  }
  // index fuction
  public function index() {
    $this->document->setTitle($this->language->get('heading_title'));
    $this->getList();
  }

  public function _loadCommonControllers() {
    $this->data['column_left']    = $this->load->controller('common/column_left');
    $this->data['footer']         = $this->load->controller('common/footer');
    $this->data['header']         = $this->load->controller('common/header');
  }

  protected function getList() {
    if (isset($this->request->get['filter_name'])) {
      $filter_name = $this->request->get['filter_name'];
    } else {
      $filter_name = null;
    }

    if (isset($this->request->get['filter_price'])) {
      $filter_price = $this->request->get['filter_price'];
    } else {
      $filter_price = null;
    }

    if (isset($this->request->get['filter_status'])) {
      $filter_status = $this->request->get['filter_status'];
    } else {
      $filter_status = null;
    }

    if (isset($this->request->get['sort'])) {
      $sort = $this->request->get['sort'];
    } else {
      $sort = 'pd.name';
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

    if (isset($this->request->get['filter_price'])) {
      $url .= '&filter_price=' . $this->request->get['filter_price'];
    }

    if (isset($this->request->get['filter_status'])) {
      $url .= '&filter_status=' . $this->request->get['filter_status'];
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

    $this->data['breadcrumbs'] = array();

    $this->data['breadcrumbs'][] = array(
      'text' => $this->language->get('text_home'),
      'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
    );

    $this->data['breadcrumbs'][] = array(
      'text' => $this->language->get('heading_title'),
      'href' => $this->url->link($this->file_path, 'user_token=' . $this->session->data['user_token'] . $url, true)
    );

    // $this->data['add'] = $this->url->link($this->file_path.'/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
    $this->data['back'] = $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'] . $url, true);
    $this->data['delete'] = $this->url->link($this->file_path.'/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

    $this->data['requests'] = array();

    $filter_data = array(
      'filter_name'	  => $filter_name,
      'filter_price'	  => $filter_price,
      'filter_status'   => $filter_status,
      'sort'            => $sort,
      'order'           => $order,
      'start'           => ($page - 1) * $this->config->get('config_limit_admin'),
      'limit'           => $this->config->get('config_limit_admin')
    );

    $this->load->model('tool/image');

    $product_total = $this->helper_pricealert->getTotalProducts($filter_data);

    $results = $this->helper_pricealert->getProducts($filter_data);

    if(!empty($results)) {
      foreach ($results as $result) {
        if (is_file(DIR_IMAGE . $result['image'])) {
          $image = $this->model_tool_image->resize($result['image'], 40, 40);
        } else {
          $image = $this->model_tool_image->resize('no_image.png', 40, 40);
        }

        $special = false;

        $product_specials = $this->helper_pricealert->getProductSpecials($result['product_id']);

        foreach ($product_specials  as $product_special) {
          if (($product_special['date_start'] == '0000-00-00' || strtotime($product_special['date_start']) < time()) && ($product_special['date_end'] == '0000-00-00' || strtotime($product_special['date_end']) > time())) {
            $special = $product_special['price'];

            break;
          }
        }

        $this->data['requests'][] = array(
          'quote_id'     => $result['quote_id'],
          'product_id'   => $result['product_id'],
          'customer_id'  => $result['customer_id'],
          'image'        => $image,
          'name'         => $result['name'],
          'price'        => $result['price'],
          'customer'     => $result['customer_name'],
          'email'        => $result['customer_email'],
          'special'      => $special,
          'currency'     => $result['currency'],
          'date_added'   => $result['date_added'],
          'requests'     => $result['requests'],
          'vendor_id'    => $result['vendor_id'],
          'alert_status' => $result['status'],
          'responded'    => $result['responded'],
          'accept'       => $result['accept'] ,
          'reject'       => $result['reject'],
          'coupon_id'    => $result['coupon_id'],
          'options'      => json_decode($result['product_option'],true),
          'quote_price'  => $result['quote_price'],
          'status'       => $result['pro_status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
          'ctype'        => $result['customer_id'] ? $this->language->get('text_register') : $this->language->get('text_guest'),
          'view'         => $this->url->link($this->file_path.'/view', 'user_token=' . $this->session->data['user_token'] .'&quote_id='. $result['quote_id'] .$url, true),
        );
      }
    }

    $this->data['user_token'] = $this->session->data['user_token'];

    if (isset($this->error['warning'])) {
      $this->data['error_warning'] = $this->error['warning'];
    } else {
      $this->data['error_warning'] = '';
    }

    if (isset($this->session->data['success'])) {
      $this->data['success'] = $this->session->data['success'];

      unset($this->session->data['success']);
    } else {
      $this->data['success'] = '';
    }

    if (isset($this->request->post['selected'])) {
      $this->data['selected'] = (array)$this->request->post['selected'];
    } else {
      $this->data['selected'] = array();
    }

    $url = '';

    if (isset($this->request->get['filter_name'])) {
      $url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
    }

    if (isset($this->request->get['filter_price'])) {
      $url .= '&filter_price=' . $this->request->get['filter_price'];
    }

   if (isset($this->request->get['filter_status'])) {
      $url .= '&filter_status=' . $this->request->get['filter_status'];
    }

    if ($order == 'ASC') {
      $url .= '&order=DESC';
    } else {
      $url .= '&order=ASC';
    }

    if (isset($this->request->get['page'])) {
      $url .= '&page=' . $this->request->get['page'];
    }

    $this->data['sort_name'] = $this->url->link($this->file_path, 'user_token=' . $this->session->data['user_token'] . '&sort=pd.name' . $url, true);
    $this->data['sort_price'] = $this->url->link($this->file_path, 'user_token=' . $this->session->data['user_token'] . '&sort=p.price' . $url, true);
    $this->data['sort_status'] = $this->url->link($this->file_path, 'user_token=' . $this->session->data['user_token'] . '&sort=p.status' . $url, true);
    $this->data['sort_order'] = $this->url->link($this->file_path, 'user_token=' . $this->session->data['user_token'] . '&sort=p.sort_order' . $url, true);

    $url = '';

    if (isset($this->request->get['filter_name'])) {
      $url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
    }

    if (isset($this->request->get['filter_price'])) {
      $url .= '&filter_price=' . $this->request->get['filter_price'];
    }

    if (isset($this->request->get['filter_status'])) {
      $url .= '&filter_status=' . $this->request->get['filter_status'];
    }

    if (isset($this->request->get['sort'])) {
      $url .= '&sort=' . $this->request->get['sort'];
    }

    if (isset($this->request->get['order'])) {
      $url .= '&order=' . $this->request->get['order'];
    }

    $pagination = new Pagination();
    $pagination->total = $product_total;
    $pagination->page = $page;
    $pagination->limit = $this->config->get('config_limit_admin');
    $pagination->url = $this->url->link($this->file_path, 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

    $this->data['pagination'] = $pagination->render();

    $this->data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($product_total - $this->config->get('config_limit_admin'))) ? $product_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $product_total, ceil($product_total / $this->config->get('config_limit_admin')));

    $this->data['filter_name'] = $filter_name;
    $this->data['filter_price'] = $filter_price;
    $this->data['filter_status'] = $filter_status;

    $this->data['sort'] = $sort;
    $this->data['order'] = $order;

    $this->_loadCommonControllers();

    $this->response->setOutput($this->load->view($this->file_path.'_list', $this->data));
  }

  public function view() {

    $url = '';

    $this->document->setTitle($this->language->get('text_view'));

    $quote_id = 0;

    if(isset($this->request->get['quote_id']) && (int)$this->request->get['quote_id']){
      $quote_id = (int)$this->request->get['quote_id'];
      $url .= '&quote_id='.(int)$this->request->get['quote_id'];
    }

    $this->data['text_form'] = $this->language->get('text_edit');

    $this->data['user_token'] = $this->session->data['user_token'];

    $this->data['breadcrumbs'] = array();

    $this->data['breadcrumbs'][] = array(
      'text' => $this->language->get('text_home'),
      'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
    );

    $this->data['breadcrumbs'][] = array(
      'text' => $this->language->get('heading_title'),
      'href' => $this->url->link($this->file_path, 'user_token=' . $this->session->data['user_token'] . $url, true)
    );
    $this->data['breadcrumbs'][] = array(
      'text' => $this->language->get('text_view'),
      'href' => $this->url->link($this->file_path.'/view', 'user_token=' . $this->session->data['user_token'] . $url, true)
    );

    $this->data['response'] =  array();

    $this->data['response_flag'] = 0;

    if ($quote_id) {

      $this->data['response'] =  $this->prolert->getRequestedQuotes($quote_id,0,1);

      if(!empty($this->data['response'])){
        foreach ($this->data['response'] as $key => $value) {
          if(isset($this->data['response']['currency']) && $key == 'quote_price') {
            $this->data['quote_price'] =   (float)$value ? $this->currency->format($value, $this->data['response']['currency']) : false;
          } else if($key == 'price') {
            $this->data['price'] =   (float)$value ? $this->currency->format($value, $this->config->get('config_currency')) : false;
          } else {
            $this->data[$key] = $value;
          }
        }
        $this->data['product_url'] = HTTP_CATALOG . 'index.php?route=product/product&product_id=' .$this->data['product_id'];

        if(!empty($this->data['response'])){
          $this->data['response_flag'] = 1;
        }
      } else {
        $this->data['response_flag'] = 0;
      }
    }
  

    $this->data['cancel'] = $this->url->link($this->file_path, 'user_token=' . $this->session->data['user_token'], true);
    $this->data['user_token']= $this->session->data['user_token'];
    $this->_loadCommonControllers();

    $this->response->setOutput($this->load->view($this->file_path.'_view', $this->data));
  }

  public function update() {
    $this->load->language($this->file_path);

		$json = array();
    $json['error'] = $this->language->get('text_error_update');
    if (isset($this->request->post['quote_id']) && isset($this->request->post['response_status_id'])) {
      $data['responded'] = 1;
      $data['quote_id']  = $this->request->post['quote_id'];
      $data['accepted'] = 0;
      $data['rejected'] = 1;
      if ($this->request->post['response_status_id']) {
        $data['accepted'] = 1;
        $data['rejected'] = 0;
      }

      $this->prolert->setQuoteResponse($data);
      //when update then notify customer id accept is the response
      $product_id =$this->prolert->getQuoteDetail($data['quote_id'], 'product_id');

      $email = $this->prolert->getQuoteDetail($data['quote_id'], 'customer_email');

      //here $notify will used for the further updates
      if($data['accepted'] ) {
        $notify = 1; //this is accept email sent to customer
      } else {
        $notify = 2; //this is reject email sent to customer
      }

      $this->prolert->notifyCustomer($product_id,  $data['quote_id'], $email,$notify);

      $json['success'] = $this->language->get('text_status_update');
      $json['error'] = 0;
    }

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
  }


}
