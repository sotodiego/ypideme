<?php
require_once DIR_SYSTEM . 'ocMpTrait.php';
class ControllerAccountCustomerpartnerTopsearch extends Controller {
  use OcMpTrait;
  /**
   * [private to store errors]
   * @var [array]
   */
  private $error    = array();

  private $storeurl = '';

  private $breadcrumnbs = array(
          'text_account'     =>  'account/account',
          'text_plist'       =>  'account/customerpartner/productlist',
          'heading_title'    =>  'account/customerpartner/topsearch',

  );

  /**
   * [private data vaiable will store all the values which will used in the TPL ]
   * @var [array]
   */
  private $data    = array();
  /**
   * [private string variable used for the filter]
   * @var [array]
   */
  private $stingTypeVar = array(
              'filter_name'      => null,
              'filter_count'     => null,
              'filter_term'     => null,
            );
  /**
   * [private numeric type variables used in the filter]
   * @var [array]
   */
  private $numericTypeVar = array(
                          'start'  => 0,
                          'sort'          => 'pd.name',
                          'order'         => 'ASC',
                          'page'          => 1,
                          'limit'  => 5,
            );

  public function __construct($registory) {
     parent::__construct($registory);

     $this->load->model('account/productpartner');

     $this->load->model('account/customerpartner');

    $this->registry->set('ocutilities', new Ocutilities($this->registry));

     $this->load->model('catalog/product');

     $this->_ctlgProduct    = $this->model_catalog_product;

     $this->_prodPartner    = $this->model_account_productpartner;

     $this->_customPartner  = $this->model_account_customerpartner;

     $this->data            = $this->load->language('account/customerpartner/topsearch');
  }

  public function __isPartner(){
     $this->data['chkIsPartner'] = $this->_customPartner->chkIsPartner();
  }

  public function __loadAjaxUrl() {
     $this->data['ajax_url'] = 'account/customerpartner/topsearch/_loadData';
  }

	public function index() {

		$this->document->setTitle($this->data['heading_title']);

    $this->__isPartner();

    $this->checkMpModuleStatus();

		if(!$this->data['chkIsPartner'] || (isset($this->session->data['marketplace_seller_mode']) && !$this->session->data['marketplace_seller_mode'])){
       $this->response->redirect($this->url->link('account/account', '', true));
    }

    $this->document->addScript('catalog/view/javascript/wk_marketplace/topsearch_list.js');

    $this->document->addStyle('catalog/view/javascript/wk_marketplace/topsearch.css');

    $add = array(
            'heading_title_add'    =>  'account/customerpartner/topsearch/add',
    );

    array_push($add,$this->breadcrumnbs);

    $this->__createBreadcrumbs();

    $this->__loadAjaxUrl();

    $this->setBackAction('account/customerpartner/productlist');

		$this->data['separate_view']       = false;

		$this->data['separate_column_left'] = '';

		if ($this->config->get('marketplace_separate_view') && isset($this->session->data['marketplace_separate_view']) && $this->session->data['marketplace_separate_view'] == 'separate') {
		  $this->data['separate_view'] = true;
		}

    $this->data['separate_view'] ? $this->_getSeprateViewCommonController() : $this->_getAllCommonController();

		$this->response->setOutput($this->load->view('account/customerpartner/topsearch', $this->data));
  }

  public function setBackAction($back){
    $this->data['back'] = $this->url->link($back, '', true);
  }

  public function _loadData() {

    $json['topsearch']    = array();

    $json['success']      = 0;

    $json['button_edit']  = $this->data['button_edit'];

    $json['text_time']    = $this->data['text_time'];

    $json['entry_offer']  = $this->data['entry_offer'];

    $json['help_offer']   = $this->data['help_offer'];

    $filter = array_merge($this->stingTypeVar,$this->numericTypeVar);

    foreach ($filter as $getvar => $value) {
      $$getvar = $this->ocutilities->_setPostRequestVar($getvar,$value);
    }

    $filterData = array(
      'filter_name'	        => $filter_name,
      'filter_count'        => $filter_count,
      'filter_term'         => $filter_term,
      'sort'                => $sort,
      'order'               => $order,
      'start'               => $start,
      'limit'               => $limit,
      'page'                => $page,
    );

    $return_type    = 'total';

    $json['total']  = $totals = $this->_prodPartner->_getTopSearch($filterData, $return_type);

    $return_type    = 'rows';

    $results        = $this->_prodPartner->_getTopSearch($filterData,$return_type);

    if ($results) {
      $json['success'] = 1;
      foreach ($results as $result) {
        $terms = explode(',',$result['search_terms']);
        $json['topsearch'][] = array(
          'id'         => $result['id'],
          'seller_id'  => $result['seller_id'],
          'name'       => $result['name'],
          'product_id' => $result['product_id'],
          'href'       => $this->url->link('product/product','&product_id='.$result['product_id']),
          'action'     => $this->url->link('account/customerpartner/addproduct','&topsearch=1&product_id='.$result['product_id']),
          'offer'      => $this->url->link('account/customerpartner/addproduct','&top_offer=1&topsearch=1&product_id='.$result['product_id']),
          'count'      => $result['count'],
          'terms'      => $terms,
        );
      }
    }

   $this->response->setOutput(json_encode($json));
  }

  public function __createBreadcrumbs() {

    foreach ($this->breadcrumnbs as $language => $url) {
      $this->data['breadcrumbs'][] = array(
         		'text'      => $this->data[$language],
  			    'href'      => $this->url->link($url, $this->storeurl, true),
     	);
    }

  }
  public function _getSeprateViewCommonController() {

    $this->data['column_left']    = '';

    $this->data['column_right']   = '';

    $this->data['content_top']    = '';

    $this->data['content_bottom'] = '';

    $this->data['separate_column_left'] = $this->load->controller('account/customerpartner/column_left');

    $this->data['footer'] = $this->load->controller('account/customerpartner/footer');

    $this->data['header'] = $this->load->controller('account/customerpartner/header');
  }

  public function _getAllCommonController() {

    $this->data['column_left']    = $this->load->controller('common/column_left');

    $this->data['column_right']   = $this->load->controller('common/column_right');

    $this->data['content_top']    = $this->load->controller('common/content_top');

    $this->data['content_bottom'] = $this->load->controller('common/content_bottom');

    $this->data['footer']         = $this->load->controller('common/footer');

    $this->data['header']         = $this->load->controller('common/header');
  }

  public function _processSearch($path, $filter_data, $products) {

    if($this->config->get('module_marketplace_status') && isset($this->request->get['search']) && $this->request->get['search']){
      if(!empty($products)){
        foreach ($products as $key => $value) {
          $this->_isSellersProduct($value['product_id']);
          $serch_term = $this->request->get['search'];
          $this->_manageInTopSearchElem($value['product_id'],$this->data['seller_id'],$serch_term);
        }
      }
    }
  }

  public function _isSellersProduct($prod_id) {
    $seller = $this->_prodPartner->_getProductsOwner($prod_id);
    $this->data['seller_id'] = isset($seller['customer_id']) && $seller['customer_id'] ? $seller['customer_id'] : 0;
    return $this->data['seller_id'];
  }

  public function _manageInTopSearchElem($product_id,$seller,$serch_term) {
     if($this->_prodPartner->_alreadyExist($product_id)){
         $this->_prodPartner->_updateProductsInSearch($product_id,$seller,$serch_term);
     } else {
         $this->_prodPartner->_addProductsInSearch($product_id,$seller,$serch_term);
     }
  }

}
