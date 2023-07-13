<?php
/**
 * Webkul Software.
 * @category  Webkul
 * @author    Webkul
 * @copyright Copyright (c) 2010-2016 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
class ModelAccountCustomerpartnerRestriction extends Model {
  private $data = array();
  private $price_status = 0;
  private $quant_status = 0;
  private $quant = 0;
  private $price = 0;

  public function __construct($registry) {
    parent::__construct($registry);
      $this->registry->set('senatize', new Senatizer($registry));
  }

  public function restrict() {
    $seller_id = $this->customer->getId();
    if($seller_id) {
      $total = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "customerpartner_to_purchage_restriction WHERE seller_id = '" . (int)$seller_id . "'")->row['total'];
      $this->postWrapper();
      if ($total) {
         $this->update($seller_id);
      } else {
         $this->add($seller_id);
      }
    }
  }

  public function postWrapper() {

    $this->price_status = isset($this->request->post['priceStatus']) ? $this->request->post['priceStatus'] : 0;

    if(isset($this->request->post['price'])) {
      $this->price = $this->request->post['price'];
    }

    $this->quant_status = isset($this->request->post['quantStatus']) ? $this->request->post['quantStatus'] : 0; 

    if(isset($this->request->post['quantity'])) {
      $this->quant = $this->request->post['quantity'];
    }

  }

  public function add($seller_id) {
    $this->db->query("INSERT INTO " . DB_PREFIX . "customerpartner_to_purchage_restriction SET seller_id = '" . (int)$seller_id . "',price = '" . (float)$this->price . "',quant = '" . (float)$this->quant . "',price_status = '" . (float)$this->price_status . "',quant_status = '" . (float)$this->quant_status . "'");
  }

  public function update($seller_id) {
    $this->db->query("UPDATE " . DB_PREFIX . "customerpartner_to_purchage_restriction SET price = '" . (float)$this->price . "',quant = '" . (float)$this->quant . "',price_status = '" . (float)$this->price_status . "',quant_status = '" . (float)$this->quant_status . "' WHERE seller_id = '" . (int)$seller_id . "'");
  }

  public function getRestrictions($seller_id) {
    return $this->db->query("SELECT * FROM " . DB_PREFIX . "customerpartner_to_purchage_restriction WHERE seller_id = '" . (int)$seller_id . "'")->row;
  }


}
