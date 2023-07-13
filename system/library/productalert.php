<?php

class Productalert {

  private $data = array();

	public function __construct($registry) {
    $this->loader = new Loader($registry);
		$registry->set('load', $this->loader);
		$this->load 	= $registry->get('load');
    $this->url 	= $registry->get('url');
		$this->config = $registry->get('config');
		$this->customer = $registry->get('customer');
    $this->currency = $registry->get('currency');
		$this->session = $registry->get('session');
		$this->db = $registry->get('db');
    $this->request = $registry->get('request');
  }

  public function getAlertProduct($product_id) {
     $sql = "SELECT count(*) as total FROM ".DB_PREFIX."customeralert_products WHERE product_id = " . (int)$product_id . "";
     $result = $this->db->query($sql)->row;
     return $result['total'];
  }

  public function getProductAlertStatus($product_id) {
     $sql = "SELECT status FROM ".DB_PREFIX."customeralert_products WHERE product_id = " . (int)$product_id . "";
     $result = $this->db->query($sql)->row;
     return isset($result['status']) ? $result['status'] : 0;
  }

  public function getVendorId($product_id) {
     $enabled = 1;
     $sql = "SELECT vendor_id FROM ".DB_PREFIX."customeralert_products WHERE product_id = " . (int)$product_id . " AND status = ".(int)$enabled."";
     $result = $this->db->query($sql)->row;
     return isset($result['vendor_id']) ? $result['vendor_id'] : 0;
  }

  public function addAlertProduct($data) {
    $this->db->query("INSERT INTO " . DB_PREFIX . "customeralert_products SET product_id = '" . (int)$data['product_id'] . "',vendor_id = '" . (int)$data['created_by'] . "',date_added = NOW(),date_modify = NOW(),status = '" . (int)$data['status'] . "'");
  }

  public function updateAlertProduct($data) {
		$this->db->query("UPDATE " . DB_PREFIX . "customeralert_products SET date_modify = NOW(),status = '" . (int)$data['status'] . "' WHERE product_id = '" . (int)$data['product_id'] . "'");
	}

	public function removeAlertProduct($product_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "customeralert_products WHERE product_id = '" . (int)$product_id . "'");
	}

  public function getQuoteDetail($quote_id,$field) {
		$result = $this->db->query("SELECT {$field} FROM " . DB_PREFIX . "pricealert_quote WHERE quote_id = '" . (int)$quote_id . "'")->row;
    return isset($result[$field]) ? $result[$field] : '';
	}

  public function setQuoteResponse($data) {
    $this->db->query("UPDATE " . DB_PREFIX . "pricealert_quote SET date_modify = NOW(),responded = '" . (int)$data['responded'] . "',accept = '" . (int)$data['accepted'] . "',reject = '" . (int)$data['rejected'] . "' WHERE quote_id = '" . (int)$data['quote_id'] . "'");
    return 1;
	}
  public function getTotalRequest() {
    $email = '';

    $_month = date("m");

    $_year = date("Y");

    $result = 0;

    if(!$this->customer->isLogged() && isset($this->request->post['email']) && $this->request->post['email']) {
      $email = $this->request->post['iemail'];
    } else {
      $email = $this->customer->getEmail();
    }

    if ( $email ) {
      $result = $this->db->query("SELECT count(*) as total FROM " . DB_PREFIX . "pricealert_quote WHERE customer_email = '" . $this->db->escape($email) . "' AND month(date_added) = '".(int)$_month."' AND year(date_added) = '".(int)$_year."'")->row['total'];
    }
    return $result;
	}

  /**
   * checks if the request limit of quote is crossed or not
   * @param  array $data contains product and customer details
   * @return boolean       return true/false
   */
  public function checkRequests($data) {

      $flag = true;

  		if ($this->customer->isLogged()) {
  			$customer_email = $this->customer->getEmail();
  			$limit = $this->config->get('wk_pricealert_registered_request');
  		} else {
  			$customer_email = $data['iemail'];
  			$limit = $this->config->get('wk_pricealert_unregistered_request');
  		}

  		$product_id = isset($data['product_id']) ? $data['product_id'] : 0;

  		$exist = $this->db->query("SELECT quote_id, requests,responded,accept,reject FROM " . DB_PREFIX . "pricealert_quote WHERE customer_email = '" . $this->db->escape($customer_email) . "' AND product_id = '" . (int)$product_id . "'")->row;

  		$total_request = $this->db->query("SELECT * FROM " . DB_PREFIX . "pricealert_quote WHERE customer_email = '" . $this->db->escape($customer_email) . "' AND month(date_added) = '". (int)date('m') ."' ")->num_rows;

      if (isset($exist['quote_id'])) {
        if ($exist['responded'] || $exist['accept'] || $exist['reject']) {
          $flag = false;
        }
      }

  		if($flag && ($limit <= $total_request)) {
  			$flag =  true;
  		} else {
        $flag = false;
      }
      return $flag;
   }

   public function getProduct($product_id) {
 		$query = $this->db->query("SELECT DISTINCT *, pd.name AS name, p.image, m.name AS manufacturer, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special, (SELECT points FROM " . DB_PREFIX . "product_reward pr WHERE pr.product_id = p.product_id AND pr.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "') AS reward, (SELECT ss.name FROM " . DB_PREFIX . "stock_status ss WHERE ss.stock_status_id = p.stock_status_id AND ss.language_id = '" . (int)$this->config->get('config_language_id') . "') AS stock_status, (SELECT wcd.unit FROM " . DB_PREFIX . "weight_class_description wcd WHERE p.weight_class_id = wcd.weight_class_id AND wcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS weight_class, (SELECT lcd.unit FROM " . DB_PREFIX . "length_class_description lcd WHERE p.length_class_id = lcd.length_class_id AND lcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS length_class, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, (SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review r2 WHERE r2.product_id = p.product_id AND r2.status = '1' GROUP BY r2.product_id) AS reviews, p.sort_order FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");

 		if ($query->num_rows) {
 			return array(
 				'product_id'       => $query->row['product_id'],
 				'name'             => $query->row['name'],
 				'description'      => $query->row['description'],
 				'meta_title'       => $query->row['meta_title'],
 				'meta_description' => $query->row['meta_description'],
 				'meta_keyword'     => $query->row['meta_keyword'],
 				'tag'              => $query->row['tag'],
 				'model'            => $query->row['model'],
 				'sku'              => $query->row['sku'],
 				'upc'              => $query->row['upc'],
 				'ean'              => $query->row['ean'],
 				'jan'              => $query->row['jan'],
 				'isbn'             => $query->row['isbn'],
 				'mpn'              => $query->row['mpn'],
 				'location'         => $query->row['location'],
 				'quantity'         => $query->row['quantity'],
 				'stock_status'     => $query->row['stock_status'],
 				'image'            => $query->row['image'],
 				'manufacturer_id'  => $query->row['manufacturer_id'],
 				'manufacturer'     => $query->row['manufacturer'],
 				'price'            => ($query->row['discount'] ? $query->row['discount'] : $query->row['price']),
 				'special'          => $query->row['special'],
 				'reward'           => $query->row['reward'],
 				'points'           => $query->row['points'],
 				'tax_class_id'     => $query->row['tax_class_id'],
 				'date_available'   => $query->row['date_available'],
 				'weight'           => $query->row['weight'],
 				'weight_class_id'  => $query->row['weight_class_id'],
 				'length'           => $query->row['length'],
 				'width'            => $query->row['width'],
 				'height'           => $query->row['height'],
 				'length_class_id'  => $query->row['length_class_id'],
 				'subtract'         => $query->row['subtract'],
 				'rating'           => round($query->row['rating']),
 				'reviews'          => $query->row['reviews'] ? $query->row['reviews'] : 0,
 				'minimum'          => $query->row['minimum'],
 				'sort_order'       => $query->row['sort_order'],
 				'status'           => $query->row['status'],
 				'date_added'       => $query->row['date_added'],
 				'date_modified'    => $query->row['date_modified'],
 				'viewed'           => $query->row['viewed']
 			);
 		} else {
 			return false;
 		}
 	}
  /**
   * Notifies a customer whether his/her quote is accepted or rejected
   * @param  integer $product_id contains product ID
   * @param  string $email      contains customer E-mail
   * @param  integer $notify     tells whether the quote is accepted(1) or rejected(2)
   * @return null             none
   */
  	public function notifyCustomer($product_id, $request_id, $email, $notify) {

  		$language_id = $this->config->get('config_language_id');

      if (strpos($_SERVER['PHP_SELF'], '\/admin\/') !== false) {
        $link = HTTP_CATALOG.'index.php?route=product/product&product_id='. $product_id;
      } else {
        $link = $this->url->link('product/product&product_id=' .$product_id, '',true);
      }

  		if ($notify == 1) { // this one to notify customer accept massage

  			$acception_subject = $this->config->get('wk_pricealert_email_accept_subject')[$language_id];

  			$acception_mail = $this->config->get('wk_pricealert_email_accept_text')[$language_id];

  			$customer = $this->db->query("SELECT * FROM " . DB_PREFIX . "pricealert_quote WHERE product_id = '" . (int)$product_id . "' AND quote_id = '" . (int)$request_id . "' AND customer_email = '" . $this->db->escape($email) . "'")->row;

  			$quote_price = $this->currency->convert($customer['quote_price'], $customer['currency'], $this->config->get('config_currency'));

  			$product_detail = $this->getProduct($product_id);

  			$start = date('Y-m-d');
  			$str = strtotime($start);
  			$str = $this->config->get('wk_pricealert_coupon_validity') * 24 * 3600 + $str;
  			$end = date('Y-m-d', $str);

  			$coupon['name'] = $this->config->get('wk_pricealert_coupon_name')[$language_id];
  			$coupon['code'] = rand(111111111, 999999999);
  			$coupon['type'] = 'F';
  			$coupon['total'] = 0;
  			$coupon['logged'] = $customer['customer_id'] ? 1 : 0;
  			$coupon['discount'] = $product_detail['price'] - $quote_price;
  			$coupon['shipping'] = 0;
  			$coupon['date_start'] = $start;
  			$coupon['date_end'] = $end;
  			$coupon['uses_total'] = 1;
  			$coupon['uses_customer'] = 1;
  			$coupon['status'] = 1;
  			$coupon['product_id'] = $product_id;

  			$coupon_id = $this->addCoupon($coupon);

  			$find_email = array(
  				'{customer_name}',
  				'{product_name}',
  				'{product_link}',
  				'{product_image}',
  				'{coupon_code}',
  				'{coupon_validity_date}',
  				'{times_notification}'
  				);

  			$this->db->query("UPDATE " . DB_PREFIX . "pricealert_quote SET status = '1', responded = '1', accept = '1', coupon_id = '" . (int)$coupon_id . "' WHERE product_id = '" . (int)$product_id . "' AND  quote_id = '" . (int)$request_id . "' AND customer_email = '" . $this->db->escape($email) . "'");

  			$details = $this->productDetails($product_id);

  			$replace_email = array(
  				'customer_name'		=> $customer['customer_name'],
  				'product_name'		=> '<a href="'.$link.'">' . $details['name'] . '</a>',
  				'product_link'		=> $link,
  				'product_image'		=> $details['image'] ? '<a href="'.$link.'">' . '<img src="' . $this->resize($details['image'], 200, 200) . '">' . '</a>' : '',
  				'coupon_code'		=> $coupon['code'],
  				'coupon_validity_date'=> $coupon['date_end'],
  				'times_notification'=> $this->config->get('wk_pricealert_notification_times')
  				);

  			$html = html_entity_decode(str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find_email, $replace_email, $acception_mail)))));
  			$email_data = array(
  				'email_to' => $email,
  				'email_from' => $this->config->get('config_email'),
  				'sender_name' => $this->config->get('config_name'),
  				'subject' => $acception_subject,
  				'html' => $html,
  				'text' => html_entity_decode($html)
  				);

  		} elseif ($notify == 2) { // this one to notify rejection
  			$rejection_subject = $this->config->get('wk_pricealert_email_reject_subject')[$language_id];
  			$rejection_mail = $this->config->get('wk_pricealert_email_reject_text')[$language_id];

  			$find_email = array(
  				'{customer_name}',
  				'{product_name}',
  				'{product_link}',
  				'{product_image}',
  				'{times_notification}'
  				);

  			$this->db->query("UPDATE " . DB_PREFIX . "pricealert_quote SET status = '1', responded = '1', reject = '1' WHERE product_id = '" . (int)$product_id . "' AND  quote_id = '" . (int)$request_id . "' AND customer_email = '" . $this->db->escape($email) . "'");

  			$details = $this->productDetails($product_id);

  			$customer = $this->db->query("SELECT customer_name FROM " . DB_PREFIX . "pricealert_quote WHERE product_id = '" . (int)$product_id . "' AND  quote_id = '" . (int)$request_id . "' AND customer_email = '" . $this->db->escape($email) . "'")->row;
        if (strpos($_SERVER['PHP_SELF'], '\/admin\/') !== false) {
          $link = HTTP_CATALOG.'index.php?route=product/product&product_id='. $product_id;
        } else {
          $link = $this->url->link('product/product&product_id=' .$product_id, '',true);
        }
  			$replace_email = array(
  				'customer_name'		=> $customer['customer_name'],
  				'product_name'		=> '<a href="'. $link .'">' . $details['name'] . '</a>',
  				'product_link'		=> $link,
  				'product_image'		=> $details['image'] ? '<a href="'. $link .'">' . '<img src="' . $this->resize($details['image'], 200, 200) . '">' . '</a>' : '',
  				'times_notification'=> $this->config->get('wk_pricealert_notification_times')
  				);

  			$html = html_entity_decode(str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find_email, $replace_email, $rejection_mail)))));
  			$email_data = array(
  				'email_to' => $email,
  				'email_from' => $this->config->get('config_email'),
  				'sender_name' => $this->config->get('config_name'),
  				'subject' => $rejection_subject,
  				'html' => $html,
  				'text' => html_entity_decode($html)
  				);
  		}

  	$this->sendMail($email_data);
  }

  public function resize($filename, $width, $height) {
    if (!is_file(DIR_IMAGE . $filename) || substr(str_replace('\\', '/', realpath(DIR_IMAGE . $filename)), 0, strlen(DIR_IMAGE)) != DIR_IMAGE) {
      return;
    }

    $extension = pathinfo($filename, PATHINFO_EXTENSION);

    $image_old = $filename;
    $image_new = 'cache/' . utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '-' . (int)$width . 'x' . (int)$height . '.' . $extension;

    if (!is_file(DIR_IMAGE . $image_new) || (filectime(DIR_IMAGE . $image_old) > filectime(DIR_IMAGE . $image_new))) {
      list($width_orig, $height_orig, $image_type) = getimagesize(DIR_IMAGE . $image_old);

      if (!in_array($image_type, array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF))) {
        return DIR_IMAGE . $image_old;
      }

      $path = '';

      $directories = explode('/', dirname($image_new));

      foreach ($directories as $directory) {
        $path = $path . '/' . $directory;

        if (!is_dir(DIR_IMAGE . $path)) {
          @mkdir(DIR_IMAGE . $path, 0777);
        }
      }

      if ($width_orig != $width || $height_orig != $height) {
        $image = new Image(DIR_IMAGE . $image_old);
        $image->resize($width, $height);
        $image->save(DIR_IMAGE . $image_new);
      } else {
        copy(DIR_IMAGE . $image_old, DIR_IMAGE . $image_new);
      }
    }

    $image_new = str_replace(' ', '%20', $image_new);  // fix bug when attach image on email (gmail.com). it is automatic changing space " " to +

    if ($this->request->server['HTTPS']) {
      return $this->config->get('config_ssl') . 'image/' . $image_new;
    } else {
      return $this->config->get('config_url') . 'image/' . $image_new;
    }
  }
  /**
   * adds a coupon from front end if the quote is accepted for given price
   * @param array $data contains coupon data
   * @return  integer returns the coupon's ID
   */
  public function addCoupon($data) {
  		$this->db->query("INSERT INTO " . DB_PREFIX . "coupon SET name = '" . $this->db->escape($data['name']) . "', code = '" . $this->db->escape($data['code']) . "', discount = '" . (float)$data['discount'] . "', type = '" . $this->db->escape($data['type']) . "', total = '" . (float)$data['total'] . "', logged = '" . (int)$data['logged'] . "', shipping = '" . (int)$data['shipping'] . "', date_start = '" . $this->db->escape($data['date_start']) . "', date_end = '" . $this->db->escape($data['date_end']) . "', uses_total = '" . (int)$data['uses_total'] . "', uses_customer = '" . (int)$data['uses_customer'] . "', status = '" . (int)$data['status'] . "', date_added = NOW()");

  		$coupon_id = $this->db->getLastId();

  		$this->db->query("INSERT INTO " . DB_PREFIX . "coupon_product SET coupon_id = '" . (int)$coupon_id . "', product_id = '" . (int)$data['product_id'] . "'");

  		return $coupon_id;
  }
  /**
  * inserts a quote/price comparison from the customer
  * @param  array $data contains product and customer details(if exists)
  * @return integer       returns the integer based on insert/update
  */
 	public function insertQuote($data) {

 		if(isset($data['option']) && !empty($data['option'])) {
 			$data['option'] = json_encode($data['option']);
 		} else {
 			$data['option'] = json_encode(array());
 		}

 		$now = date('Y-m-d H:i:s');

 		if ($this->customer->isLogged()) {
 			$customer_id = $this->customer->getId();
 			$customer_name = $this->customer->getFirstName() . ' ' . $this->customer->getLastName();
 			$customer_email = $this->customer->getEmail();
 		} else {
 			$customer_id = 0;
 			$customer_name = $data['iname'];
 			$customer_email = $data['iemail'];
 		}

 		$exist = $this->db->query("SELECT * FROM " . DB_PREFIX . "pricealert_quote WHERE customer_email = '" . $this->db->escape($customer_email) . "' AND product_id = '" . (int)$data['product_id'] . "'")->row;
    $vendor_id = 0;
    if($this->config->get('module_marketplace_status')){
      $vendor_id = $this->getSellerByProduct($data['product_id']);
    }
 		if (isset($exist['quote_id']) && $exist['product_option'] == $data['option'] && !$exist['accept']) {
 			$requests = $exist['requests'] + 1;
 			$this->db->query("UPDATE `" . DB_PREFIX . "pricealert_quote` SET `customer_name` = '" . $this->db->escape($customer_name) . "', `quote_price` = '" . (int)$data['price'] . "', `currency` = '" . $data['currency'] . "', `date_added` = '" . $now . "', requests = '" . (int)$requests . "',vendor_id = '" . (int)$vendor_id . "' WHERE customer_email = '" . $this->db->escape($customer_email) . "' AND product_id = '" . (int)$data['product_id'] . "'");
 			return 2;//for the update
 		} else {

 			$this->db->query("INSERT INTO `" . DB_PREFIX . "pricealert_quote` SET `product_id` = '" . (int)$data['product_id'] . "', `product_option` = '" . $this->db->escape($data['option']) . "', `customer_id` = '" . (int)$customer_id . "', `customer_name` = '" . $this->db->escape($customer_name) . "', `customer_email` = '" . $this->db->escape($customer_email) . "', `quote_price` = '" . (float)$data['price'] . "', `currency` = '" . $data['currency'] . "', `date_added` = '" . $now . "', requests = '1',responded = '0',accept = '0', reject = '0',vendor_id = '" . (int)$vendor_id . "'");
 		}
 		return 1;//for new entry
 	}
  /**
   * fetches seller ID based on product ID
   * @param  integer $product_id product's ID
   * @return integer/boolean             returns customer ID if exist otherwise false
   */
  public function getSellerByProduct($product_id) {
  		$sql = "SELECT customer_id FROM " . DB_PREFIX . "customerpartner_to_product WHERE product_id = '" . (int)$product_id . "'";
  		$query = $this->db->query($sql)->row;

  		if (isset($query['customer_id'])) {
  			return $query['customer_id'];
  		} else {
  			return 0;
  		}
  }

  public function priceAlertSelectedOption($product_id) {
    $sql = "SELECT product_selected_option FROM " . DB_PREFIX . "pricealert_products WHERE product_id = '" . (int)$product_id . "' and status = 1 and date_till >= NOW()";
    $query = $this->db->query($sql);

    if($query->num_rows) {
      return $query->rows;
    } else {
      return false;
    }
  }
  /**
   * Fetches limited product and vendor details based on product ID
   * @param  integer $product_id contains product's ID
   * @return array             returns product's name, image and vendor's name
   */
  public function productDetails($product_id)	{
      if($this->config->get('module_marketplace_status')){
        $details = $this->db->query("SELECT pd.name, p.image, CONCAT(c.firstname, ' ', c.lastname) as vendor_name, c.email FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "customerpartner_to_product c2p ON (p.product_id = c2p.product_id) LEFT JOIN " . DB_PREFIX . "customer c ON (c2p.customer_id = c.customer_id) WHERE p.product_id = '" . (int)$product_id . "'")->row;
      } else {
        $details = $this->db->query("SELECT pd.name, p.image, p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.product_id = '" . (int)$product_id . "'")->row;
        $details['vendor_name'] = $this->config->get('config_name');
        $details['email'] = $this->config->get('config_email');
      }

  		return $details;
  }

  public function getRequestedQuotes($quote_id=0,$vendor_id = 0, $active = 0,$customer_id = 0) {
		$sql = "SELECT *, pq.status FROM " . DB_PREFIX . "pricealert_quote pq LEFT JOIN " . DB_PREFIX . "product_description pd ON (pq.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product p ON (pq.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "customeralert_products pp ON (pq.product_id = pp.product_id) WHERE pd.language_id = '" . $this->config->get('config_language_id') . "' AND pq.quote_id = ".(int)$quote_id." AND pq.vendor_id = ".$this->customer->getId();

		if ($vendor_id) {
			$sql .= " AND pq.vendor_id = '" . $vendor_id . "'";
		}

    if ($customer_id) {
			$sql .= " AND pq.customer_id = '" . $customer_id . "'";
		}

		$quotes = $this->db->query($sql)->row;

		return $quotes;
	}

  /**
   * Sends mail as per the given details
   * @param  array $data contains details to send mail
   * @return null       none
   */
  public function sendMail($data)	{

		if ($data['subject'] && $data['email_from'] && $data['email_to']) {
      $mail = new Mail($this->config->get('config_mail_engine'));
      $mail->parameter = $this->config->get('config_mail_parameter');
      $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
      $mail->smtp_username = $this->config->get('config_mail_smtp_username');
      $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
      $mail->smtp_port = $this->config->get('config_mail_smtp_port');
      $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

      $mail->setTo($data['email_to']);
      $mail->setFrom($data['email_from']);
      $mail->setSender(html_entity_decode($data['sender_name'], ENT_QUOTES, 'UTF-8'));
      $mail->setSubject(html_entity_decode($data['subject'], ENT_QUOTES, 'UTF-8'));
      $mail->setHtml($data['html']);
      $mail->setText($data['text']);
      $mail->send();
		}
  }




}
