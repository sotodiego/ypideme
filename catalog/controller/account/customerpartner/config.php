<?php
/**
 * @version [3.0.0.0] [Supported opencart version 3.x.x.x]
 * @category Webkul
 * @package Opencart-Marketplace Pro
 * @author [Webkul] <[<http://webkul.com/>]>
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

 require_once DIR_SYSTEM . 'ocMpTrait.php';
class ControllerAccountCustomerpartnerConfig extends Controller {
  use OcMpTrait;
   /**
    * [private set error varibale in case of fault]
    * @var [type]
    */
   private $error = array();
  /**
   * [private data variable to store all the registeres]
   * @var [type]
   */
   private $data  = array();
   /**
    * [private set error flag for the conditions]
    * @var [type]
    */
   private $flag = true;

   private $module_key =  'marketplace';
   /**
    * [private get all the requered configuration from the store]
    * @var [type]
    */
   private $config_setting = array();
   /**
    * [private product id of the requested item]
    * @var [type]
    */
    public function __construct($registory) {
       parent::__construct($registory);
       $this->load->model('customerpartner/config');
       $this->load->model('account/customerpartner');
       $this->partner_helper = $this->model_account_customerpartner;
       $this->config_helper = $this->model_customerpartner_config;
       $this->loadLangauge();
       $this->checkMpModuleStatus();
    }

    public function loadLangauge() {
       $this->data = $this->language->load('account/customerpartner/config');
    }

    public function isLogged() {
      if (!$this->customer->isLogged()) {
  			$this->session->data['redirect'] = $this->url->link('account/customerpartner/config', '', true);
  			$this->response->redirect($this->url->link('account/login', '', true));
  		}
    }

    public function isPartner() {
     $this->data['chkIsPartner'] = $this->partner_helper->chkIsPartner();
     if(!$this->data['chkIsPartner'] || (isset($this->session->data['marketplace_seller_mode']) && !$this->session->data['marketplace_seller_mode'])){
       $this->response->redirect($this->url->link('account/account', '', true));
     }
    }

    public function getConfig() {
      $this->data['configurations'] = $this->config_helper->getSetting($this->module_key, $this->config->get('config_store_id'));
    }

    public function getBreadcrumbs() {
      $this->data['breadcrumbs'] = array();

      $this->data['breadcrumbs'][] = array(
        'text'      => $this->data['text_home'],
        'href'      => $this->url->link('common/home', '', true),
      );

      $this->data['breadcrumbs'][] = array(
        'text'      => $this->data['text_account'],
        'href'      => $this->url->link('account/account', '', true),
      );

      $this->data['breadcrumbs'][] = array(
        'text'      => $this->data['heading_title'],
        'href'      => $this->url->link('account/customerpartner/config', '', true),
      );
    }

    public function getCommonControllers() {
      $this->data['separate_view'] = false;
      $this->data['column_left']    = $this->load->controller('common/column_left');
      $this->data['column_right']   = $this->load->controller('common/column_right');
      $this->data['content_top']    = $this->load->controller('common/content_top');
      $this->data['content_bottom'] = $this->load->controller('common/content_bottom');
      $this->data['footer']         = $this->load->controller('common/footer');
      $this->data['header']         = $this->load->controller('common/header');
    }

    public function getSeprateViewControllers() {
      $this->data['separate_view'] = true;
      $this->data['column_left'] = '';
      $this->data['column_right'] = '';
      $this->data['content_top'] = '';
      $this->data['content_bottom'] = '';
      $this->data['separate_column_left'] = $this->load->controller('account/customerpartner/column_left');
      $this->data['footer'] = $this->load->controller('account/customerpartner/footer');
      $this->data['header'] = $this->load->controller('account/customerpartner/header');
    }

    public function getStoreNames() {
      $this->data['seller_product_store'] = array(
  			  'own_store' => $this->data['entry_ownstore'],
  		 	  'choose_store' => $this->data['entry_choosestore'],
  			  'multi_store' => $this->data['entry_mulistore'],
  		);
    }

    public function getProductTables() {
      $product_table = $this->db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '".DB_DATABASE."' AND table_name = '".DB_PREFIX."product'")->rows;

  		$product_table = array_slice($product_table, 2, -3);

  		$this->data['product_table'] = array();

  		foreach($product_table as $key => $value){
  			if ($value['COLUMN_NAME'] != 'status') {
  				$this->data['product_table'][] = $value['COLUMN_NAME'];
  			}
  		}

  		$this->data['product_table'][] = 'keyword';
    }

    public function getProductTabs($value='') {
      	$this->data['product_tabs'] = array('links', 'attribute', 'options', 'discount', 'special', 'images', 'custom-field');
    }

    public function index() {

      $this->document->setTitle(strip_tags($this->data['heading_title']));

      $this->data['configurations'] = array();
      $this->data['separate_column_left'] = '';

      $this->isLogged();

      $this->isPartner();

      $this->getBreadcrumbs();

      if($this->data['chkIsPartner']) {
         $this->getConfig();

         $this->getStoreNames();

         $this->getProductTables();

         $this->getProductTabs();
      }

      if ($this->config->get('marketplace_separate_view') && isset($this->session->data['marketplace_separate_view']) && $this->session->data['marketplace_separate_view'] == 'separate') {
        $this->getSeprateViewControllers();
      } else {
        $this->getCommonControllers();
      }

  		$this->response->setOutput($this->load->view('account/customerpartner/config', $this->data));
    }


 }
