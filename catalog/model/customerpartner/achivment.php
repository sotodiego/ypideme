<?php
class ModelCustomerpartnerAchivment extends Model {
	public function upload($seller_id,$path_file) {
    //Table structure for table `customerpartner_to_achivement`
    $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX ."customerpartner_to_achivement` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `seller_id` int(11) NOT NULL,
      `path` varchar(255) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1") ;

    $total = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "customerpartner_to_achivement WHERE seller_id = " . (int)$seller_id . "")->row['total'];

    $function_name =  $total ? 'updateAchivment' : 'addAchivment';

    $this->{$function_name}($seller_id, $path_file);
  }

  public function addAchivment($seller_id, $path_file) {
      $this->db->query("INSERT INTO " . DB_PREFIX . "customerpartner_to_achivement SET path='".$this->db->escape($path_file)."',seller_id = " . (int)$seller_id . "");
  }

  public function getAchivment($seller_id) {
    return $this->db->query("SELECT * FROM " . DB_PREFIX . "customerpartner_to_achivement WHERE seller_id = " . (int)$seller_id . "")->row;
  }

  public function updateAchivment($seller_id, $path_file) {
      $this->db->query("UPDATE " . DB_PREFIX . "customerpartner_to_achivement SET path='".$this->db->escape($path_file)."',seller_id = '" . (int)$seller_id . "' WHERE seller_id = " . (int)$seller_id . "");
  }

}
