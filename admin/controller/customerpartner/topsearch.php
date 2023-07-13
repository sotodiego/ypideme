<?php
class ControllerCustomerpartnerTopsearch extends Controller {
  /**
   * [private to store errors]
   * @var [array]
   */
  private $error    = array();

  private $storeurl = '';

  private $breadcrumnbs = array(
          'text_account'     =>  'common/dashboard',
          'heading_title'    =>  'customerpartner/topsearch',

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

     $this->registry->set('wkloader', new Wkloader($this->registry));

     $this->registry->set('ocutilities', new Ocutilities($this->registry));

     $this->wkloader  = $this->registry->get('wkloader');

     $this->wkloader->model('account/productpartner','catalog');

     $this->wkloader->model('account/customerpartner','catalog');

     $this->wkloader->model('catalog/product','catalog');

     $this->_ctlgProduct    = $this->model_catalog_product;

     $this->_prodPartner    = $this->model_account_productpartner;

     $this->_customPartner  = $this->model_account_customerpartner;

     $this->data            = $this->load->language('customerpartner/topsearch');
  }

  public function __isPartner(){
     $this->data['chkIsPartner'] = $this->_customPartner->chkIsPartner();
  }

	public function index() {

		$this->document->setTitle($this->data['heading_title']);

    $this->__addScriptStyle();

    $this->__loadAjaxUrl();

    $this->__setCheckIndex();

    $this->__createBreadcrumbs();

    $this->_getAllCommonController() ;

		$this->response->setOutput($this->load->view('customerpartner/topsearch', $this->data));
  }

  public function __setCheckIndex(){
     $this->data['_adminCheck'] = 1;
  }

  public function __addScriptStyle() {
    $this->document->addScript('../catalog/view/javascript/wk_marketplace/topsearch_list.js');
    $this->document->addStyle('../catalog/view/javascript/wk_marketplace/topsearch.css');
  }

  public function __loadAjaxUrl() {
     $this->data['ajax_url'] = 'customerpartner/topsearch/_loadData&user_token='.$this->session->data['user_token'];
  }

  public function _loadData() {
    $this->__setCheckIndex();

    $json['topsearch']    = array();

    $json['success']      = 0;

    $json['button_edit']  = $this->data['button_edit'];

    $json['text_time']    = $this->data['text_time'];

    $json['entry_offer']  = $this->data['entry_offer'];

    $json['help_offer']   = $this->data['help_offer'];

    $json['_adminCheck']  = $this->data['_adminCheck'];

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
      'condition'           =>'',
    );

    $return_type    = 'total';

    $json['total']  = $totals = $this->_prodPartner->_getTopSearch($filterData, $return_type, 0);

    $return_type    = 'rows';

    $results = $this->_prodPartner->_getTopSearch($filterData, $return_type, 0);

    if ($results) {
      $json['success'] = 1;
      foreach ($results as $result) {

          $terms = explode(',',$result['search_terms']);

          $names = $this->_prodPartner->_getCutomerName($result['seller_id']);

          $json['topsearch'][] = array(
              'id'         => $result['id'],
              'seller_id'  => $result['seller_id'],
              'seller'     => $names ? '<b style="color:red;">'.$names['firstname']. ' '.$names['firstname'] . ' </b> Seller Item' : '<b style="color:green;">Own Item </b>',
              'name'       => $result['name'],
              'product_id' => $result['product_id'],
              'href'       => $this->url->link('catalog/product/edit','user_token=' . $this->session->data['user_token'].'&product_id='.$result['product_id']),
              'action'     => $this->url->link('catalog/product/edit','user_token=' . $this->session->data['user_token'].'&topsearch=1&product_id='.$result['product_id']),
               'offer'     => 0,
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
  			    'href'      => $this->url->link($url, 'user_token=' . $this->session->data['user_token'].$this->storeurl, true),
     	);
    }
  }

  public function _getAllCommonController() {
      $this->data['column_left']    = $this->load->controller('common/column_left');
      $this->data['footer']         = $this->load->controller('common/footer');
      $this->data['header']         = $this->load->controller('common/header');
  }
}
