<?php
class ControllerWkPricealertEvent extends Controller {

  public function index() {

  }

  public function addCommonMenu(&$route = false, &$data = false, &$output = false) {
    // check if module is not enabled
    if(!$this->config->get('module_marketplace_status') && $this->config->get('module_wk_pricealert_status')) {
        // Catalog
  			$alert = array();

  			if ($this->user->hasPermission('access', 'customerpartner/pricealert')) {
  				$alert[] = array(
  					'name'	   => '<i class="fa fa-folder-open"></i> Alert Products',
  					'href'     => $this->url->link('customerpartner/pricealert', 'user_token=' . $this->session->data['user_token'], true),
  					'children' => array()
  				);
  			}

  			if ($this->user->hasPermission('access','customerpartner/pa_request')) {
  				$alert[] = array(
  					'name'	   => '<i class="fa fa-comments fw"></i> Request',
  					'href'     => $this->url->link('customerpartner/pa_request', 'user_token=' . $this->session->data['user_token'], true),
  					'children' => array()
  				);
  			}
        $menu = array(array(
          'id'       => 'prece-alert-webkul',
          'icon'	   => 'fa fa-bell',
          'name'	   => 'Price Alert',
          'href'     => '',
          'children' => $alert,
        ));
        array_splice($data['menus'],1,0,$menu);
    }
  }

  public function addProductPageJs(&$route = false, &$data = false, &$output = false) {
    if($this->config->get('module_wk_pricealert_status')) {
       $this->document->addScript('view/javascript/pricealert/wkpa.js');
    }
  }
  /**
   * [addAlertProduct this funtion will run trough event whenever any product will be added newly]
   * @param  boolean $route  [event called from]
   * @param  boolean $data   [event fucntion data]
   * @param  boolean $output [return]
   * @return [type]          [nothing]
   */
  public function addAlertProduct(&$route = false, &$data = false, &$output = false) {
    //always check the module status to run the events
    if ($this->config->get('module_wk_pricealert_status')) {
      //set regisrty to use library funtinality
      $this->registry->set('prolert', new Productalert($this->registry));
      //init the value
      $data['is_alert_product'] = 0 ; //0
      // addProduct always return product so the o/p is treated as product_id
      $data['product_id'] = isset($output) ? $output : 0;
      //alert for the product
      if ($this->config->get('module_marketplace_status')){
          $data['created_by']       =  $this->prolert->getSellerByProduct($data['product_id']);
      } else {
        $data['created_by'] = 0;
      }
      // post value for the alert
      $data['status'] = isset($this->request->post['pricealert']) ? $this->request->post['pricealert'] : 0;
      // run query if we have valid >0 product id
      if ($data['product_id']) {
        $this->prolert->addAlertProduct($data);
      }
    }
  }

  /**
   * [updateAlertProduct this funtion will run trough event whenever any product will be updated]
   * @param  boolean $route  [event called from]
   * @param  boolean $data   [event fucntion data]
   * @param  boolean $output [return]
   * @return [type]          [nothing]
   */
  public function updateAlertProduct(&$route = false, &$data = false, &$output = false) {
    //always check the module status to run the events
    if ($this->config->get('module_wk_pricealert_status')){
      //set regisrty to use library funtinality
      $this->registry->set('prolert', new Productalert($this->registry));
      // in case od the edit product usrl always has the product id
      $data['product_id']       = isset($this->request->get['product_id']) ? $this->request->get['product_id'] : 0;
      //init the value used in the below calls
      if ($this->config->get('module_marketplace_status')){
          $data['created_by']       =  $this->prolert->getSellerByProduct($data['product_id']);
      } else {
        $data['created_by'] = 0;
      }
      $data['is_alert_product'] = 0 ;
      // post value for the alert
      $data['status']           = isset($this->request->post['pricealert']) ? $this->request->post['pricealert'] : 0;
      // check if we have alreay entry for the alert in db
      if ($data['product_id']) {
         $data['is_alert_product'] = $this->prolert->getAlertProduct($data['product_id']);
      }
      // add alert is not added for the alert update otherwise
      if($data['is_alert_product']) {
        $this->prolert->updateAlertProduct($data);
      } else {
        $this->prolert->addAlertProduct($data);
      }
    }
  }

  public function deleteAlertProduct(&$route = false, &$data = false, &$output = false) {
      //check the module status
      if ($this->config->get('module_wk_pricealert_status'))  {
         //set regisrty to use library funtinality
         $this->registry->set('prolert', new Productalert($this->registry));
         //product ids comes into a array while we try to delete it
         foreach ($data as $key => $product_id) {
            if($this->prolert->getAlertProduct($product_id)){
              $this->prolert->removeAlertProduct($product_id);
            }
         }
      }
  }


}
 ?>
