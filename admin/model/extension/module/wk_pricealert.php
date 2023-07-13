<?php
class ModelExtensionModuleWkpricealert extends Model {

  public function createTables() {

    $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX ."customeralert_products` (
      `pricealert_id` int(11) NOT NULL AUTO_INCREMENT,
      `vendor_id` int(11) NOT NULL,
      `product_id` int(11) NOT NULL,
      `date_added` date NOT NULL,
      `date_modify` date NOT NULL,
      `status` tinyint(1) NOT NULL,
      PRIMARY KEY (`pricealert_id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1");

    $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX ."pricealert_products` (
      `pricealert_id` int(11) NOT NULL AUTO_INCREMENT,
      `vendor_id` int(11) NOT NULL,
      `product_id` int(11) NOT NULL,
      `product_name` varchar(255) NOT NULL,
      `product_selected_option` varchar(255) NOT NULL,
      `date_from` datetime NOT NULL,
      `date_till` datetime NOT NULL,
      `date_added` datetime NOT NULL,
      `views` int(11) NOT NULL,
      `status` tinyint(1) NOT NULL,
      PRIMARY KEY (`pricealert_id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1");

    $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX ."pricealert_quote` (
      `quote_id` int(11) NOT NULL AUTO_INCREMENT,
      `product_id` int(11) NOT NULL,
      `product_option` varchar(255) NOT NULL,
      `customer_id` int(11) NOT NULL,
      `customer_name` varchar(255) NOT NULL,
      `customer_email` varchar(255) NOT NULL,
      `quote_price` decimal(10,2),
      `currency` varchar(127) NOT NULL,
      `date_added` datetime NOT NULL,
      `date_modify` datetime NOT NULL,
      `requests` int(11) NOT NULL,
      `vendor_id` int(11) NOT NULL,
      `status` tinyint(1) NOT NULL,
      `responded` tinyint(1) NOT NULL,
      `accept` tinyint(1) NOT NULL,
      `reject` tinyint(1) NOT NULL,
      `coupon_id` int(11) NOT NULL,
      `commission` int NOT NULL,
      PRIMARY KEY (`quote_id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1");

  }

  public function deleteTables() {
    $this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX ."customeralert_products`");
    $this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX ."pricealert_quote`");
    $this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX ."pricealert_products`");
  }

  public function getPriceAlerts() {
    $sql = "SELECT *, CONCAT(c.firstname, ' ', c.lastname) as vendor_name, (SELECT count(*) FROM " . DB_PREFIX . "pricealert_quote pq WHERE pq.product_id = pp.product_id) as requests FROM " . DB_PREFIX . "pricealert_products pp LEFT JOIN " . DB_PREFIX . "customer c ON (c.customer_id = pp.vendor_id) WHERE pp.status = '1'";
    return $this->db->query($sql)->rows;
  }
}
