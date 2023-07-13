<?php
/**
 * Webkul Software.
 * @category  Webkul
 * @author    Webkul
 * @copyright Copyright (c) 2010-2016 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
class ModelAccountCustomerpartnerPaRequest extends Model {
  // __constructer
  public function __construct($registory) {
    parent::__construct($registory);
    // $this->load->model('catalog/product');
    // $this->helper_catalog = $this->model_catalog_product;
  }

  public function getProducts($data = array()) {

    $sql = "SELECT pq.*,pd.name,p.image,p.price,p.status as pro_status FROM " . DB_PREFIX . "pricealert_quote pq LEFT JOIN " . DB_PREFIX . "product p ON (pq.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND pq.vendor_id = ".(int)$this->customer->getId()."";

    if (!empty($data['filter_name'])) {
      $sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
    }

    if (isset($data['filter_price']) && !is_null($data['filter_price'])) {
      $sql .= " AND p.price LIKE '" . $this->db->escape($data['filter_price']) . "%'";
    }

    if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
      $sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
    }

    $sql .= " GROUP BY pq.quote_id";

    $sort_data = array(
      'pd.name',
      'p.price',
      'p.status',
      'p.sort_order'
    );

    if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
      $sql .= " ORDER BY " . $data['sort'];
    } else {
      $sql .= " ORDER BY pd.name";
    }

    if (isset($data['order']) && ($data['order'] == 'DESC')) {
      $sql .= " DESC";
    } else {
      $sql .= " ASC";
    }

    if (isset($data['start']) || isset($data['limit'])) {
      if ($data['start'] < 0) {
        $data['start'] = 0;
      }

      if ($data['limit'] < 1) {
        $data['limit'] = 20;
      }

      $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
    }

    $query = $this->db->query($sql);

    return $query->rows;
  }

  public function getTotalProducts($data = array()) {
    $sql = "SELECT COUNT(pq.quote_id) AS total FROM " . DB_PREFIX . "pricealert_quote pq  LEFT JOIN " . DB_PREFIX . "product p ON (pq.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)";

    $sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'  AND pq.vendor_id = ".(int)$this->customer->getId()."";

    if (!empty($data['filter_name'])) {
      $sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
    }

    if (isset($data['filter_price']) && !is_null($data['filter_price'])) {
      $sql .= " AND p.price LIKE '" . $this->db->escape($data['filter_price']) . "%'";
    }

    if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
      $sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
    }

    $query = $this->db->query($sql);

    return $query->row['total'];
  }

  public function getProductSpecials($product_id) {
    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "' ORDER BY priority, price");
    return $query->rows;
  }

  public function delete($product_id) {
   $this->db->query("DELETE  FROM " . DB_PREFIX . "pricealert_quote WHERE quote_id = '" . (int)$product_id . "'");
  }


}
