<?php
class ControllerAccountCustomerpartnerScf extends Controller {
  public function manage_SCF_Url(&$path = '', &$data = array(), &$output = false) {
    $status = false; $url_append = '&scf_id=0';
    //check route as category page status

    if (isset($this->request->get['path']) && $this->request->get['path']) {
       $status = true;
    }
    //check marketplace status
    if ($status && $this->config->get('marketplace_status')) {
       $status = true;
    }
    //check seller filter is allowed from mp configurations
    if ($status && $this->config->get('marketplace_scf_status')){
       $status = true;
    }
    //check if get url has the seller id
    if ($status && isset($this->request->get['scf_id'])){
       $status = true;
       $url_append = '&scf_id='.$this->request->get['scf_id'];
    }
    //check if path for the category is set
    if($status && isset($data['category_id'])) {
      $status = true;
    }
    //check if category array is set
    if($status && isset($data['categories']) && is_array($data['categories'])) {
      $status = true;
    }

    $this->load->model('catalog/category');
    // if status and seller id is true then manipulate the product array
    if ($status && isset($this->request->get['scf_id'])) {
      foreach ($data['categories'] as $key => $value) {
    		$category = $this->model_catalog_category->getCategory($value['category_id']);
        if(isset($data['categories'][$key]['href']) && $data['categories'][$key]['href']){
          $data['categories'][$key]['href'] = $value['href'].$url_append;
        }

        if(!empty($value['children'])) {
          foreach ($value['children'] as $child_key => $child_category) {
            $sub_category = $this->model_catalog_category->getCategory($child_category['category_id']);

            if(isset($data['categories'][$key]['children'][$child_key]['href']) && $data['categories'][$key]['children'][$child_key]['href']){
              $data['categories'][$key]['children'][$child_key]['href'] = $child_category['href'].$url_append;
            }
          }
        }
      }
    }
  }

  public function manageCategoryProductTotal(&$path = '', &$data = array(), &$output = false) {

    $status = false;
    //check route as category page status
    if (isset($this->request->get['path']) && $this->request->get['path']) {
       $status = true;
    }
    //check marketplace status
    if ($status && $this->config->get('marketplace_status')) {
       $status = true;
    }
    //check seller filter is allowed from mp configurations
    if ($status && $this->config->get('marketplace_scf_status')){
       $status = true;
    }
    //check if get url has the seller id
    if ($status && isset($this->request->get['scf_id'])){
       $status = true;
    }

    if ($status && isset($this->request->get['path'])){
       $status = true;
    }

    // if status and seller id is true then manipulate the product array
    if ($status && isset($data[0]['filter_category_id'])) {
      if(isset($this->request->get['scf_id']) && ($this->request->get['scf_id'] || !$this->request->get['scf_id'])) {
        $data[0]['filter_seller_id'] =  $this->request->get['scf_id'];
        $output = $this->getTotalProducts($data[0]);
      }
    }
  }

  public function manageCategoryPageUrls(&$path = '', &$data = array(), &$output = false) {

    $url_href = array(
      'breadcrumbs',
      'categories',
      'products',
      'limits',
      'sorts',
    );

    $status = false; $url_append = '&scf_id=0';
    //check route as category page status
    if (isset($this->request->get['path']) && $this->request->get['path']) {
       $status = true;
    }
    //check marketplace status
    if ($status && $this->config->get('marketplace_status')) {
       $status = true;
    }
    //check seller filter is allowed from mp configurations
    if ($status && $this->config->get('marketplace_scf_status')){
       $status = true;
    }
    //check if get url has the seller id
    if ($status && isset($this->request->get['scf_id'])){
       $status = true;
       $url_append = '&scf_id='.$this->request->get['scf_id'];
    }
    //check if path for the category is set
    if($status && isset($data['category_id'])) {
      $status = true;
    }

    //check if category array is set
    if($status && isset($data['categories']) && is_array($data['categories'])) {
      $status = true;
    }

    // if status and seller id is true then manipulate the product array
    if ($status && isset($this->request->get['scf_id'])) {
      foreach ($url_href as $variable) {
        if (isset($data[$variable]) && is_array($data[$variable])) {
           foreach ($data[$variable] as ${$variable.'_key'} => ${$variable.'_value'}) {
             if(isset($data[$variable][${$variable.'_key'}]['href']) && $data[$variable][${$variable.'_key'}]['href']){
               $data[$variable][${$variable.'_key'}]['href'] = ${$variable.'_value'}['href'].$url_append;
             }
           }
        }
      }
    }
  }

  public function getTotalProducts($data = array()) {
		$sql = "SELECT COUNT(DISTINCT p.product_id) AS total";

		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (cp.category_id = p2c.category_id)";
			} else {
				$sql .= " FROM " . DB_PREFIX . "product_to_category p2c";
			}

			if (!empty($data['filter_filter'])) {

        $sql .= " LEFT JOIN " . DB_PREFIX . "product_filter pf ON (p2c.product_id = pf.product_id) LEFT JOIN " . DB_PREFIX . "product p ON (pf.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "customerpartner_to_product c2p ON (c2p.product_id = p.product_id) ";

			} else {
        $sql .= " LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "customerpartner_to_product c2p ON (c2p.product_id = p.product_id)";
			}
		} else {
      $sql .= " FROM " . DB_PREFIX . "product p  LEFT JOIN " . DB_PREFIX . "customerpartner_to_product c2p ON (c2p.product_id = p.product_id)";
		}

		$sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

    if(isset($data['filter_seller_id']) && $data['filter_seller_id']) {
      $sql .= " AND c2p.customer_id = '" . (int)$data['filter_seller_id'] . "'";
    } else {
      $sql .= " AND c2p.customer_id IS NULL ";
    }

		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " AND cp.path_id = '" . (int)$data['filter_category_id'] . "'";
			} else {
				$sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
			}

			if (!empty($data['filter_filter'])) {
				$implode = array();

				$filters = explode(',', $data['filter_filter']);

				foreach ($filters as $filter_id) {
					$implode[] = (int)$filter_id;
				}

				$sql .= " AND pf.filter_id IN (" . implode(',', $implode) . ")";
			}
		}

		if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
			$sql .= " AND (";

			if (!empty($data['filter_name'])) {
				$implode = array();

				$words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_name'])));

				foreach ($words as $word) {
					$implode[] = "pd.name LIKE '%" . $this->db->escape($word) . "%'";
				}

				if ($implode) {
					$sql .= " " . implode(" AND ", $implode) . "";
				}

				if (!empty($data['filter_description'])) {
					$sql .= " OR pd.description LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
				}
			}

			if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
				$sql .= " OR ";
			}

			if (!empty($data['filter_tag'])) {
				$implode = array();

				$words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_tag'])));

				foreach ($words as $word) {
					$implode[] = "pd.tag LIKE '%" . $this->db->escape($word) . "%'";
				}

				if ($implode) {
					$sql .= " " . implode(" AND ", $implode) . "";
				}
			}

			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.model) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.sku) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.upc) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.ean) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.jan) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.isbn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.mpn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}

			$sql .= ")";
		}

		if (!empty($data['filter_manufacturer_id'])) {
			$sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

  public function manipulate_SCF_Products(&$path = '', &$data = array(), &$output = false) {
    $status = false; $seller_id = 0;
    //check route as category page status
    if (isset($this->request->get['path']) && $this->request->get['path']) {
       $status = true;
    }
    //check marketplace status
    if ($status && $this->config->get('marketplace_status')) {
       $status = true;
    }
    //check seller filter is allowed from mp configurations
    if ($status && $this->config->get('marketplace_scf_status')){
       $status = true;
    }
    //check if get url has the seller id
    if ($status && isset($this->request->get['scf_id'])){
       $seller_id = $this->request->get['scf_id'];
       $status = true;
    }
    // if status and seller id is true then manipulate the product array
    if ($status && isset($this->request->get['scf_id'])) {
       foreach ($output as $product_id => $value) {
         $status = $this->getAllSellerProductID($seller_id, $product_id);
         if (!$status) {
            unset($output[$product_id]);
         }
       }
    }
  }

  public function getAllSellerProductID($seller_id ,$product_id) {

    $sql = "SELECT c2p.product_id as product_id FROM " . DB_PREFIX . "customerpartner_to_product c2p LEFT JOIN ".DB_PREFIX."product p ON(c2p.product_id = p.product_id) LEFT JOIN ".DB_PREFIX."product_to_store p2s ON (p.product_id = p2s.product_id) WHERE c2p.product_id = '".(int)$product_id."'";
    if($seller_id) {
      $sql .= " AND c2p.customer_id = '".(int)$seller_id."'";
    }
    $sql .= " AND p.status = 1 AND p2s.store_id = '".$this->config->get('config_store_id')."'";

    $status = $this->db->query($sql)->num_rows;

    if ($seller_id) {
      return $status;
    } else {
      return !$status;
    }
  }
}
