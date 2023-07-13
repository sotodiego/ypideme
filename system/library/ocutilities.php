<?php
/**
 * @version [1.0.0.0] [Supported opencart version 3.x.x.x]
 * @category Webkul
 * @package Opencart-Webkul
 * @author [Webkul] <[<http://webkul.com/>]>
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

class Ocutilities {

	protected $registry;

	public function __construct($registry) {
		$this->registry = $registry;
		$this->config 	= $registry->get('config');
		$this->db 		  = $registry->get('db');
		$this->request 	= $registry->get('request');
		$this->session 	= $registry->get('session');
	}

  /**
  * [_setGetRequestVarToNull assign a get request variable value if set otherwise null]
  * @param [type] $key [get var key]
  */
  public function _setGetRequestVarToNull($key) {
  		return isset($this->request->get[$key]) ? $this->request->get[$key] : null;
  }

	/**
	 * [_setGetRequestVar assign a get request variable value if set otherwise default val]
	 * @param [type] $key [get var key2]
	 * @param [type] $val [default values if not set]
	 */
  public function _setGetRequestVar($key,$val) {
  		return isset($this->request->get[$key]) ? $this->request->get[$key] : $val;
  }

	public function _setPostRequestVar($key,$val) {
			return isset($this->request->post[$key]) ? $this->request->post[$key] : $val;
	}
  public function _setGetRequestVarWithStatus($key,$defult_value,$status) {
      return isset($this->request->get[$key]) && (isset($this->request->get['status']) && $this->request->get['status'] == $status) ? $this->request->get[$key] : $defult_value;
  }

  public function _setStringURLs($filter_var) {
		return isset($this->request->get[$filter_var]) ? '&' . $filter_var . '=' . urlencode(html_entity_decode($this->request->get[$filter_var], ENT_QUOTES, 'UTF-8')): '';
	}

	public function _setNumericURLs($filter_var) {
		return isset($this->request->get[$filter_var]) ? '&' . $filter_var . '=' . $this->request->get[$filter_var]: '';
	}

  public function _appendNumericVarToUrlWithStatus($filter_var,$status) {
		return isset($this->request->get[$filter_var]) && (isset($this->request->get['status']) && $this->request->get['status'] == $status)? '&' . $filter_var . '=' . $this->request->get[$filter_var]: '';
	}

  public function _setSession($key,$val) {
      $this->sessio->data[$key] = $val;
  }

	public function _getSession($key) {
		return  isset($this->sessio->data[$key]) ? $this->sessio->data[$key] : '';
  }

	public function _unsetSession($key) {
		unset($this->sessio->data[$key]);
  }

	public function _isSetSession($key) {
		return isset($this->sessio->data[$key]) ? TRUE : FALSE;
  }

	public function _isSessionHasValue($key) {
		return $this0>isSetSession($key) && $this->sessio->data[$key] ? TRUE : FALSE;
  }

	public function _isSetPOST($key) {
		return isset($this->request->post[$key]) ? TRUE : FALSE;
  }

	public function _isSetGET($key) {
		return isset($this->request->post[$key]) ? TRUE : FALSE;
  }

	public function _isPOSTHasValue($key) {
		return isset($this->request->post[$key]) && $this->request->post[$key]? TRUE : FALSE;
  }

	public function _isHasValue($key) {
		return isset($this->request->post[$key]) && $this->request->post[$key]? TRUE : FALSE;
  }

	public function _GetPostValue($key) {
		return _isPOSTHasValue($key) ? $key : '';
  }

	public function _isGETHasValue($key) {
		return isset($this->request->get[$key]) ? TRUE : FALSE;
  }

  public function _manageSessionVariable($key,$default) {
    if (isset($this->session->data[$key])) {
 		  $return = $this->session->data[$key];
 		  unset($this->session->data[$key]);
 	  } else {
 		  $return = $default;
 	  }
		return $return;
	}

	public function _setSessionVal($frst_key,$sec_key,$val = '') {
     return isset($this->session->data[$frst_key][$sec_key]) ? $this->session->data[$frst_key][$sec_key] : $val;
  }

	public function getCartSellersProduct( $products ) {
     $seller_data = array();

     foreach ($products as $key => $product) {

		  	 $seller_id = $this->getProductSellerID($product['product_id']);

         $seller_data[$seller_id][$product['product_id']][] = array(
					 'count' => $product['quantity'],
					 'total' => $product['total'],
					 'price' => $product['price'],
				 );
     }
    return $seller_data;
  }

	public function getProductSellerID( $product_id ) {
  	 $sellers = $this->db->query("SELECT cu.customer_id FROM ".DB_PREFIX."product p LEFT JOIN ".DB_PREFIX."customerpartner_to_product c2p ON (p.product_id = c2p.product_id) LEFT JOIN ".DB_PREFIX."customer cu ON(cu.customer_id = c2p.customer_id) RIGHT JOIN " . DB_PREFIX . "customerpartner_to_customer c2c ON (c2c.customer_id = cu.customer_id) WHERE p.product_id='".(int)$product_id."'")->row;
     $seller_id =  isset($sellers['customer_id']) ? $sellers['customer_id'] : 0;
     return $seller_id;
  }

	public function getProductTotal() {

		if (isset($this->request->post['product_id'])) {
			$product_id = (int)$this->request->post['product_id'];
		} else {
			$product_id = 0;
		}
		if (isset($this->request->post['quantity'])) {
			$quantity = (int)$this->request->post['quantity'];
		} else {
			$quantity = 0;
		}

		if (isset($this->request->post['option'])) {
			$option = array_filter($this->request->post['option']);
		} else {
			$option = array();
		}


		$product_data = array();

		$product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_store p2s LEFT JOIN " . DB_PREFIX . "product p ON (p2s.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND p2s.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.date_available <= NOW() AND p.status = '1'");

		if ($product_query->num_rows) {

				$option_price = 0;

				$option_data = array();

				foreach ($option as $product_option_id => $value) {
					$option_query = $this->db->query("SELECT po.product_option_id, po.option_id, od.name, o.type FROM " . DB_PREFIX . "product_option po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE po.product_option_id = '" . (int)$product_option_id . "' AND po.product_id = '" . (int)$product_id . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "'");

					if ($option_query->num_rows) {
						if ($option_query->row['type'] == 'select' || $option_query->row['type'] == 'radio') {
							$option_value_query = $this->db->query("SELECT pov.option_value_id, ovd.name, pov.quantity, pov.subtract, pov.price, pov.price_prefix, pov.points, pov.points_prefix, pov.weight, pov.weight_prefix FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_option_value_id = '" . (int)$value . "' AND pov.product_option_id = '" . (int)$product_option_id . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

							if ($option_value_query->num_rows) {
								if ($option_value_query->row['price_prefix'] == '+') {
									$option_price += $option_value_query->row['price'];
								} elseif ($option_value_query->row['price_prefix'] == '-') {
									$option_price -= $option_value_query->row['price'];
								}
							}
						} elseif ($option_query->row['type'] == 'checkbox' && is_array($value)) {
							foreach ($value as $product_option_value_id) {
								$option_value_query = $this->db->query("SELECT pov.option_value_id, pov.quantity, pov.subtract, pov.price, pov.price_prefix, pov.points, pov.points_prefix, pov.weight, pov.weight_prefix, ovd.name FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (pov.option_value_id = ovd.option_value_id) WHERE pov.product_option_value_id = '" . (int)$product_option_value_id . "' AND pov.product_option_id = '" . (int)$product_option_id . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

								if ($option_value_query->num_rows) {
									if ($option_value_query->row['price_prefix'] == '+') {
										$option_price += $option_value_query->row['price'];
									} elseif ($option_value_query->row['price_prefix'] == '-') {
										$option_price -= $option_value_query->row['price'];
									}
								}
							}
						}
					}
				}

				$price = $product_query->row['price'];
        // Product Discount price
				$product_discount_query = $this->db->query("SELECT price FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "' AND customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND quantity <= '" . (int)$quantity . "' AND ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW())) ORDER BY quantity DESC, priority ASC, price ASC LIMIT 1");

				if ($product_discount_query->num_rows) {
					$price = $product_discount_query->row['price'];
				}

				// Product Specials
				$product_special_query = $this->db->query("SELECT price FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "' AND customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW())) ORDER BY priority ASC, price ASC LIMIT 1");

				if ($product_special_query->num_rows) {
					$price = $product_special_query->row['price'];
				}
			  $total = (($price + $option_price) * $quantity);
			} else {
				$total = 0;
			}
		return $total;
	}






}
