<?php
class ModelAccountCategorymapping extends Model {

   public function getCategoryOptions($category_id = 0) {
     $options = array();
     if ($category_id) {
      $category_options = $this->db->query("SELECT option_id FROM ".DB_PREFIX."wk_category_option_mapping WHERE category_id =".(int)$category_id)->row;

      if (isset($category_options['option_id']) && $category_options['option_id'] ) {
          $sql = "SELECT option_id,name FROM " . DB_PREFIX . "option_description od  WHERE od.language_id = '" . (int)$this->config->get('config_language_id') . "' AND od.option_id IN (".$category_options['option_id'].")";
          $query = $this->db->query($sql);
          return $query->rows;
       }
     }
   }

   public function getOption($option_id) {
    if($option_id){
      $sql = "SELECT * FROM `" . DB_PREFIX . "option` o LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE od.language_id = '" . (int)$this->config->get('config_language_id') . "' AND o.option_id = '".(int)$option_id."' ";

   		$query = $this->db->query($sql);

   		return $query->row;
    }
 	}
}

 ?>
