<?php
/**
 * @version [3.0.0.0] [Supported opencart version 3.x.x.x]
 * @category Webkul
 * @package Opencart-Marketplace Pro
 * @author [Webkul] <[<http://webkul.com/>]>
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
class ModelCustomerpartnerConfig extends Model {

  public function getSetting($code, $store_id = 0) {
    $setting_data = array();

    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int)$store_id . "' AND `code` = '" . $this->db->escape($code) . "'");

    foreach ($query->rows as $result) {
      if (!$result['serialized']) {
        $setting_data[$result['key']] = $result['value'];
      } else {
        $setting_data[$result['key']] = json_decode($result['value'], true);
      }
    }
    return $setting_data;
  }

  public function getSettingValue($key, $store_id = 0) {
    $query = $this->db->query("SELECT value FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int)$store_id . "' AND `key` = '" . $this->db->escape($key) . "'");

    if ($query->num_rows) {
      return $query->row['value'];
    } else {
      return null;
    }
  }

}
