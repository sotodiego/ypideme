<?php
class ModelCustomerpartnerEventHandler extends Model {

  public function isSellerProduct($product_id = 0) {
    $sellers = $this->db->query("SELECT cu.customer_id FROM ".DB_PREFIX."product p LEFT JOIN ".DB_PREFIX."customerpartner_to_product c2p ON (p.product_id = c2p.product_id) LEFT JOIN ".DB_PREFIX."customer cu ON(cu.customer_id = c2p.customer_id) RIGHT JOIN " . DB_PREFIX . "customerpartner_to_customer c2c ON (c2c.customer_id = cu.customer_id) WHERE p.product_id='".(int)$product_id."'")->row;
    return isset($sellers['customer_id']) ? 1 : 0;
  }

  public function getProductId($cart_id, $quantity) {
  	$product = $this->db->query("SELECT product_id FROM `" . DB_PREFIX . "cart` WHERE cart_id = '" . (int)$cart_id . "' AND api_id = '" . (isset($this->session->data['api_id']) ? (int)$this->session->data['api_id'] : 0) . "' AND customer_id = '" . (int)$this->customer->getId() . "' AND session_id = '" . $this->db->escape($this->session->getId()) . "'")->row;
    return isset($product['product_id']) ? $product['product_id'] : 0;
	}
  public function getProductQuant($cart_id, $quantity) {
  	$product = $this->db->query("SELECT quantity FROM `" . DB_PREFIX . "cart` WHERE cart_id = '" . (int)$cart_id . "' AND api_id = '" . (isset($this->session->data['api_id']) ? (int)$this->session->data['api_id'] : 0) . "' AND customer_id = '" . (int)$this->customer->getId() . "' AND session_id = '" . $this->db->escape($this->session->getId()) . "'")->row;
    return isset($product['quantity']) ? $product['quantity'] : 0;
	}

}
