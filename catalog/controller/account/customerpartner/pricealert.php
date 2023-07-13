<?php
/**
 * Webkul Software.
 * @category  Webkul
 * @author    Webkul
 * @copyright Copyright (c) 2010-2016 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
require_once DIR_SYSTEM . 'webkul/ocPAControllerTrait.php';

class ControllerAccountCustomerpartnerPricealert extends Controller {
  // use controller traits for the common fucntionality
  use ocPAControllerTrait;
  //private error varibales
  private $error = array();
  // private module code variable
  private $module_code = 'wk_pricealert';
  //set module paths
  private $file_path = 'account/customerpartner/pricealert';
  // private data variable
  private $data = array();
  // __constructer
  public function __construct($registory) {
    parent::__construct($registory);
    $this->data = $this->load->language($this->file_path);
    //load module model for the creating/doping table and managing sql
    $this->load->model($this->file_path);
    // set a object for using model funtions
    $this->helper_pricealert = $this->model_account_customerpartner_pricealert;

    $this->load->model('account/customerpartner');
    $this->_customPartner  = $this->model_account_customerpartner;
    // set the regisrtry to avail by whole class functions
    $this->registry->set('prolert', new Productalert($this->registry));

    if(!is_array($this->config->get('marketplace_allowed_account_menu')) || !in_array('pricealert', $this->config->get('marketplace_allowed_account_menu'))) {
      $this->response->redirect($this->url->link('account/account','', true));
    }
  }
  // index fuction
  public function index() {
    $this->document->setTitle($this->language->get('heading_title'));
    $status = true;
    if($this->config->get('module_marketplace_status') && $this->config->get('module_wk_pricealert_status') && $this->config->get('wk_pricealert_allow_seller')) {
      $status = false;
    }
    if($status){
      $this->response->redirect($this->url->link('account/account', '', true));
    }
    $this->getList();
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

    $this->data['chkIsPartner'] = $this->_customPartner->chkIsPartner();

    $this->data['isMember'] = true;

    if(!$this->data['chkIsPartner'] || (isset($this->session->data['marketplace_seller_mode']) && !$this->session->data['marketplace_seller_mode'])){
       $this->response->redirect($this->url->link('account/account', '', true));
    }

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
      'href' => $this->url->link('account/account', '', true)
    );

    $this->data['breadcrumbs'][] = array(
      'text' => $this->language->get('heading_title'),
      'href' => $this->url->link($this->file_path, '' . $url, true)
    );

    // $this->data['add'] = $this->url->link($this->file_path.'/add', '' . $url, true);
    $this->data['back'] = $this->url->link('account/account', '' . $url, true);
    $this->data['delete'] = $this->url->link($this->file_path.'/delete', '' . $url, true);

    $this->data['products'] = array();

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

      $alert_status = $this->prolert->getProductAlertStatus($result['product_id']);

      $this->data['products'][] = array(
        'product_id'   => $result['product_id'],
        'image'        => $image,
        'name'         => $result['name'],
        'price'        => $result['price'],
        'special'      => $special,
        'alert_status' => $alert_status,
        'status'       => $result['status'] ? $this->language->get('text_allow') : $this->language->get('text_disallow'),
      );
    }

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

    $this->data['sort_name'] = $this->url->link($this->file_path, '' . '&sort=pd.name' . $url, true);
    $this->data['sort_price'] = $this->url->link($this->file_path, '' . '&sort=p.price' . $url, true);
    $this->data['sort_status'] = $this->url->link($this->file_path, '' . '&sort=p.status' . $url, true);
    $this->data['sort_order'] = $this->url->link($this->file_path, '' . '&sort=p.sort_order' . $url, true);

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
    $pagination->url = $this->url->link($this->file_path, '' . $url . '&page={page}', true);

    $this->data['pagination'] = $pagination->render();

    $this->data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($product_total - $this->config->get('config_limit_admin'))) ? $product_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $product_total, ceil($product_total / $this->config->get('config_limit_admin')));

    $this->data['filter_name'] = $filter_name;
    $this->data['filter_price'] = $filter_price;
    $this->data['filter_status'] = $filter_status;

    $this->data['sort'] = $sort;
    $this->data['order'] = $order;

    $this->data['separate_view']       = false;

		$this->data['separate_column_left'] = '';

		if ($this->config->get('marketplace_separate_view') && isset($this->session->data['marketplace_separate_view']) && $this->session->data['marketplace_separate_view'] == 'separate') {
		  $this->data['separate_view'] = true;
		}

    if($this->data['separate_view']) {
      $this->_getSeprateViewCommonController();
    } else {
      $this->_loadCommonControllers();
      $this->_loadCustomerCommonControllers();
    }

    $this->response->setOutput($this->load->view($this->file_path.'_list', $this->data));
  }

  /**
   * [renderAlertHTML to render the HTML code on the product form this will be called by event with a JS ajax file]
   * @return [type] [json object]
   */
  public function renderAlertHTML() {
    // json array return all the data within
    $json = array();
    // load all the language variable into json array
    $json = $this->load->language('account/customerpartner/pricealert');
    // load model
    $this->load->model('account/customerpartner/pricealert');
    // get module status from the config
    $json['wk_pricealert_status'] = $this->config->get('module_wk_pricealert_status');

    $json['wk_pricealert_allowed_seller'] = $this->config->get('wk_pricealert_allow_seller');

    //check if product id is in the url string
    $json['product_id'] = isset($this->request->get['product_id']) && $this->request->get['product_id'] ? $this->request->get['product_id'] : 0;
    // initilize the variable
    $json['is_alert_product'] = 0;
    // if module is enabled then do the opration
    if ($json['wk_pricealert_status'] && $json['wk_pricealert_allowed_seller']) {
      // if product id was in the url
       if($json['product_id']) {
         //check if product already has the alert entry in the db
         $json['is_alert_product'] = $this->prolert->getProductAlertStatus($json['product_id']);
       } else {
         $json['is_alert_product'] = 1;
       }
    }
    //return json response
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
  }

  public function __isPartner(){
     $this->data['chkIsPartner'] = $this->_customPartner->chkIsPartner();
  }

  public function delete() {

    if (isset($this->request->post['selected']) && $this->validateDelete()) {
      foreach ($this->request->post['selected'] as $product_id) {
        $this->prolert->removeAlertProduct($product_id);
      }

      $this->session->data['success'] = $this->data['text_success'];

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

      $this->response->redirect($this->url->link($this->path, '' . $url, true));
    }
    $this->getList();
  }

  protected function validateDelete() {
		if (!$this->user->hasPermission('modify', $this->file_path)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		return !$this->error;
	}

  public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name']) || isset($this->request->get['filter_model'])) {

			if (isset($this->request->get['filter_name'])) {
				$filter_name = $this->request->get['filter_name'];
			} else {
				$filter_name = '';
			}

			if (isset($this->request->get['limit'])) {
				$limit = $this->request->get['limit'];
			} else {
				$limit = 5;
			}

			$filter_data = array(
				'filter_name'  => $filter_name,
				'start'        => 0,
				'limit'        => $limit
			);

      $this->load->model('catalog/product');
      $this->load->model('account/customerpartner');
			$results = $this->helper_pricealert->getProducts($filter_data);

			foreach ($results as $result) {
				$option_data = array();

				$product_options = $this->model_catalog_product->getProductOptions($result['product_id']);

				foreach ($product_options as $product_option) {
					$option_info = $this->model_account_customerpartner->getOption($product_option['option_id']);

					if ($option_info) {
						$product_option_value_data = array();

						foreach ($product_option['product_option_value'] as $product_option_value) {
							$option_value_info = $this->model_account_customerpartner->getOptionValue($product_option_value['option_value_id']);

							if ($option_value_info) {
								$product_option_value_data[] = array(
									'product_option_value_id' => $product_option_value['product_option_value_id'],
									'option_value_id'         => $product_option_value['option_value_id'],
									'name'                    => $option_value_info['name'],
									'price'                   => (float)$product_option_value['price'] ? $this->currency->format($product_option_value['price'], $this->config->get('config_currency')) : false,
									'price_prefix'            => $product_option_value['price_prefix']
								);
							}
						}

						$option_data[] = array(
							'product_option_id'    => $product_option['product_option_id'],
							'product_option_value' => $product_option_value_data,
							'option_id'            => $product_option['option_id'],
							'name'                 => $option_info['name'],
							'type'                 => $option_info['type'],
							'value'                => $product_option['value'],
							'required'             => $product_option['required']
						);
					}
				}

				$json[] = array(
					'product_id' => $result['product_id'],
					'name'       => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
					'option'     => $option_data,
					'price'      => $result['price']
				);
			}
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
