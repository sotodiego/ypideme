<?php
/**
 * @version [1.0.0.0] [Supported opencart version 3.x.x.x]
 * @category Webkul
 * @package Opencart-Webkul
 * @author [Webkul] <[<http://webkul.com/>]>
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

class Wklayout {

  private $layout_id      = 0;
  private $module_code    = '';
  private $position       = '';
  private $route          = '';
  private $store_id       = 0;
  private $flag           = TRUE;

  private $common_keyset = array(
    'route',
    'code',
    'position',
  );

  function __construct($registry) {
		$this->db = $registry->get('db');
  }

  function setter($layouthelper) {

    //validate all the keys are set or not
    $this->validateLayoutKeys($layouthelper);

    if(!empty($layouthelper) && $this->flag) {
      foreach ($layouthelper as $key => $value) {
        $this->{$key}  = $value;
      }
      $this->layout_id = $this->getLayoutID();
    }
  }

  public function getLayoutID() {
     $result = $this->db->query("SELECT layout_id FROM " . DB_PREFIX . "layout  WHERE `store_id`='" . (int)$this->store_id . "' AND `route`='" . $this->db->escape($this->route) . "'")->row;
     return isset($result['layout_id']) ? $result['layout_id'] : 0;
  }

  public function validateLayoutKeys($layouthelper) {

    foreach ($this->common_keyset as $k => $key) {
       if($this->flag && isset($layouthelper[$key]) && $layouthelper[$key]){
          $this->flag = TRUE;
       } else{
         $this->flag = FALSE;
       }
    }
  }

  public function setModuleLayout($layout_helper) {

     $this->setter($layout_helper);
     
     $this->registry->set('layoutHook',new Wklayout($this->registry));

     $layout_helper = array(
       'store_id'  => 0,
       'route'     => 'product/product',
       'code'      => $this->module_code,
       'position'  => 'content_bottom',
     );

     $this->layoutHook->setModuleLayout($layout_helper);

     if($this->flag){
       $this->db->query("INSERT INTO " . DB_PREFIX . "layout_route SET `layout_id`='" . (int)$this->layout_id . "', `store_id`='" . (int)$this->store_id . "', `route`='" . $this->db->escape($this->route) . "'");
       $this->db->query("INSERT INTO " . DB_PREFIX . "layout_module SET `layout_id`='" . (int)$this->layout_id . "', `code`='" . $this->db->escape($this->module_code) ."', `position`='" . $this->db->escape($this->position) ."', `sort_order`='0'");
     }
  }
}


 ?>
