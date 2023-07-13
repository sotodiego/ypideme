<?php
class ModelAccountProductpartner extends Model {

  private $queryTotal = 'total';

  private $queryQows  = 'rows';

	public function _getProductsOwner($product_id = 0) {
		$sellers = $this->db->query("SELECT cu.customer_id FROM ".DB_PREFIX."product p LEFT JOIN ".DB_PREFIX."customerpartner_to_product c2p ON (p.product_id = c2p.product_id) LEFT JOIN ".DB_PREFIX."customer cu ON(cu.customer_id = c2p.customer_id) RIGHT JOIN " . DB_PREFIX . "customerpartner_to_customer c2c ON (c2c.customer_id = cu.customer_id) WHERE p.product_id='".(int)$product_id."'")->row;
    return  $sellers;
	}

  public function _addProductsInSearch($product_id,$seller,$serch_term, $count =1) {

    $this->db->query("INSERT INTO ".DB_PREFIX."wk_product_top_search SET `product_id` = '".(int)$product_id."',`seller_id` = '".(int)$seller."',`count` = '".(int)$count."',`search_terms` = '".$this->db->escape($serch_term)."'");
	}

  public function _alreadyExist($product_id)  {
    return $this->db->query("SELECT COUNT(*) as total FROM ".DB_PREFIX."wk_product_top_search  WHERE product_id='".(int)$product_id."'")->row['total'];
  }

  public function _delete($product_id)  {
     $this->db->query("DELETE FROM " . DB_PREFIX . "wk_product_top_search WHERE product_id='".(int)$product_id."'");
  }

  public function _getTopSearchItem($product_id)  {
    return $this->db->query("SELECT count as total,search_terms as keywords FROM ".DB_PREFIX."wk_product_top_search  WHERE product_id='".(int)$product_id."'")->row;
  }

  public function _buildSqlQuery($condition = '')  {
    $sql = "SELECT pd.name,pts.* FROM ".DB_PREFIX."wk_product_top_search pts LEFT JOIN ".DB_PREFIX."product_description pd ON (pd.product_id = pts.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' ";

    return $condition ? $sql.$condition : $sql;
  }

  public function _buildSortingSqlQuery($data) {
    $sql = '';

    $sql .= (!empty($data['filter_name'])) ? " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'" :  " ";

    $sql .= (!empty($data['filter_term'])) ? " AND pts.search_terms LIKE '%" . $this->db->escape($data['filter_term']) . "%'" :  " ";

    $sql .= (isset($data['filter_count']) && $data['filter_count']) ? " AND pts.count = '" . (int)$data['filter_count'] . "'" :  " ";

    $sort_data = array(
         'pd.name',
         'pts.search_terms',
         'pts.count',
    );

    $sql .= (isset($data['sort']) && in_array($data['sort'], $sort_data)) ?  " ORDER BY " . $data['sort'] :  " ORDER BY pd.name";

    $sql .= (isset($data['order']) && ($data['order'] == 'DESC')) ?  " DESC" :  " ASC";

    isset($data['start']) || isset($data['limit']) ?  ( (($data['start'] < 0) ? $data['start'] = 0  : $data['start'] = $data['start']) AND (($data['limit'] < 1) ? $data['limit'] = 20 : $data['limit'] = $data['limit']) AND ($sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'])): 0 ;

    return $sql;
 }

  public function _getTopSearch($data, $type = '', $chk = 1)  {
    $couditions = $chk ? " AND pts.seller_id = '".(int)$this->customer->getId()."'" : '';
    $sql  = $this->_buildSqlQuery($couditions);

    $sql .= $this->_buildSortingSqlQuery($data);

    return $type == $this->queryTotal ? $this->db->query($sql)->num_rows : $this->db->query($sql)->rows;
  }

  public function _getCutomerName($seller_id)  {
    if($seller_id){
        $sql = "SELECT firstname,lastname FROM  ".DB_PREFIX."customer WHERE  customer_id = '".(int)$seller_id."'";
        return $this->db->query($sql)->row;
    }
    return 0;
  }

  public function _updateProductsInSearch($product_id,$seller,$serch_term, $count =1) {

    $search_item = $this->_getTopSearchItem($product_id);

    $allterms = explode(',',$search_item['keywords']);

    $all_search_terms = !in_array($serch_term, $allterms) ? $serch_term. ',' . $search_item['keywords'] : $search_item['keywords'];

    $total_counts = $search_item['total'] + 1;

    $this->_delete($product_id);

    $this->_addProductsInSearch($product_id,$seller,$all_search_terms,$total_counts);
	}



}

?>
