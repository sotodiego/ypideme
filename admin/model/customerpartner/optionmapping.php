<?php
  class ModelCustomerpartnerOptionmapping extends Model {

    public function getTotalCategoryOptions($data = array()) {
        $sql = "SELECT c2c.*,cd.name FROM " . DB_PREFIX . "wk_category_option_mapping c2c LEFT JOIN ".DB_PREFIX."category_description cd ON (c2c.category_id = cd.category_id) WHERE cd.language_id = ".$this->config->get('config_language_id');

        if (!empty($data['filter_attribute_id'])) {
            $sql .= " AND c2c.option_id LIKE '%" . $data['filter_option_id'] . "%'";
        }

        if (!empty($data['filter_category_id'])) {
            $sql .= " AND c2c.category_id LIKE '%" . $data['filter_category_id'] . "%'";
        }

        $query = $this->db->query($sql);

        return $query->num_rows;
    }

   public function getCategoryAttribute($category_id = 0) {
      if ($category_id) {
          $category_attributes = $this->db->query("SELECT attribute_id FROM ".DB_PREFIX."wk_category_attribute_mapping WHERE category_id =".(int)$category_id)->row;

          if (isset($category_attributes['attribute_id']) && $category_attributes['attribute_id']) {
              $sql = "SELECT *, (SELECT agd.name FROM " . DB_PREFIX . "attribute_group_description agd WHERE agd.attribute_group_id = a.attribute_group_id AND agd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS attribute_group FROM " . DB_PREFIX . "attribute a LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) WHERE ad.language_id = '" . (int)$this->config->get('config_language_id') . "' AND a.attribute_id IN (".$category_attributes['attribute_id'].")";

              $query = $this->db->query($sql);

              return $query->rows;
        }
     }
  }

  public function getCategoryOptions($data = array()) {
      $sql = "SELECT c2c.*,cd.name FROM " . DB_PREFIX . "wk_category_option_mapping c2c LEFT JOIN ".DB_PREFIX."category_description cd ON (c2c.category_id = cd.category_id) WHERE cd.language_id = ".$this->config->get('config_language_id');

      if (!empty($data['filter_option_id'])) {
          $sql .= " AND c2c.option_id LIKE '%" . $data['filter_option_id'] . "%'";
      }
      if (!empty($data['filter_category_id'])) {
          $sql .= " AND c2c.category_id LIKE '%" . $data['filter_category_id'] . "%'";
      }

      if (!empty($data['filter_category'])) {
          $sql .= " AND cd.name LIKE '%" . $data['filter_category'] . "%'";
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

  public function deleteCategoryOption($category_ids = 0) {
    $this->_deleteOptionMapping(" AND category_id IN (".$category_ids.")");
  }

  public function _getDeleteSql() {
    return "DELETE FROM ".DB_PREFIX."wk_category_option_mapping WHERE 1 ";
  }

  public function _deleteOptionMapping($conditions = '')   {
    $sql  = $this->_getDeleteSql();
    $sql .= $conditions ? $conditions : '';
    $this->db->query($sql);
  }

  public function _addCategoryOption($data) {
    if (isset($data['option_ids']) && $data['option_ids'] && isset($data['product_category']) && $data['product_category']) {
         $option_id = (is_array($data['option_ids']) && in_array('0', $data['option_ids'])) ? 0 : implode(',', $data['option_ids']);
            if (is_array($data['product_category']) && in_array('0', $data['product_category'])) {
                $this->_deleteOptionMapping();
                $this->db->query("INSERT INTO ".DB_PREFIX."wk_category_option_mapping SET category_id = '0',option_id = '".$option_id."'");
            } else {
                foreach ($data['product_category'] as $key => $value) {
                    $this->_deleteOptionMapping(" AND category_id = ".(int)$value);
                    $this->db->query("INSERT INTO ".DB_PREFIX."wk_category_option_mapping SET category_id = '".(int)$value."',option_id = '".$option_id."'");
                }
            }
        }
  }

  public function getOptionName($option_id = 0) {
      if ($option_id) {
          return $this->db->query("SELECT name FROM ".DB_PREFIX."option_description WHERE option_id = " . (int)$option_id . " AND language_id = ".$this->config->get('config_language_id'))->row;
      }
  }
  public function getCategoryOption($category_id = 0) {
      $data = array();
      if ($category_id) {
          $data =  $this->db->query("SELECT * FROM ".DB_PREFIX."wk_category_option_mapping WHERE category_id = '" . (int)$category_id . "'")->rows;
      }
      return  $data;
  }
}
