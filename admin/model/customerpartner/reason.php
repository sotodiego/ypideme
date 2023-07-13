<?php
class ModelCustomerpartnerReason extends Model {

  public function obtainReasonId($status) {
    $this->db->query("INSERT INTO " . DB_PREFIX . "mp_reason SET status = '" . (int)$status . "'");
		$return  = $this->db->getLastId();
    return $return;
  }

  public function addSellerReasonEntry($reason_id)   {
    $this->db->query("INSERT INTO `" . DB_PREFIX . "customerpartner_to_reason` SET `reason_id` = " . (int)$reason_id . ",seller_id = 0");
  }

  public function addReasonDescription($reason_id ,$data) {
    foreach ($data['reason_description'] as $language_id => $value) {
      $this->db->query("INSERT INTO " . DB_PREFIX . "mp_reason_description SET reason_id = '" . (int)$reason_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['title']) . "', description = '" . $this->db->escape($value['description']) . "'");
    }
  }

	public function addReason($data) {
		$this->load->model('customerpartner/htmlfilter');

		if (isset($data['reason_description']) && is_array($data['reason_description'])) {
		  foreach ($data['reason_description'] as $key => $reason_description) {
		    if (isset($reason_description['description']) && $reason_description['description']) {
		      $data['reason_description'][$key]['description'] = trim($this->db->escape(htmlentities($this->model_customerpartner_htmlfilter->HTMLFilter(html_entity_decode($reason_description['description']),'',true))), '\n');
		    }
		  }
		}

		$reason_id = $this->obtainReasonId($data['status']);

    $this->addSellerReasonEntry($reason_id);

    $this->addReasonDescription($reason_id ,$data);

		return $reason_id;
	}

	public function editReason($reason_id, $data) {

		$this->load->model('customerpartner/htmlfilter');

		if (isset($data['reason_description']) && is_array($data['reason_description'])) {
		  foreach ($data['reason_description'] as $key => $reason_description) {
		    if (isset($reason_description['description']) && $reason_description['description']) {
		      $data['reason_description'][$key]['description'] = trim($this->db->escape(htmlentities($this->model_customerpartner_htmlfilter->HTMLFilter(html_entity_decode($reason_description['description']),'',true))), '\n');
		    }
		  }
		}

		$this->db->query("UPDATE " . DB_PREFIX . "mp_reason SET status = '" . (int)$data['status'] . "' WHERE reason_id = '" . (int)$reason_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "mp_reason_description WHERE reason_id = '" . (int)$reason_id . "'");

		foreach ($data['reason_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "mp_reason_description SET reason_id = '" . (int)$reason_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['title']) . "', description = '" . $this->db->escape($value['description']) . "'");
		}

	}

	public function deleteReason($reason_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customerpartner_to_reason` WHERE `reason_id` = " . (int)$reason_id . "");
		$this->db->query("DELETE FROM " . DB_PREFIX . "mp_reason WHERE reason_id = '" . (int)$reason_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "mp_reason_description WHERE reason_id = '" . (int)$reason_id . "'");
	}

	public function getreason($reason_id) {
	 $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "mp_reason WHERE reason_id = '" . (int)$reason_id . "'");
	 return $query->row;
	}

  public function obtainProductReasonId($product_id) {

    $sql = "SELECT reason_id FROM " . DB_PREFIX . "mp_product_update_reason  WHERE product_id = '" . (int)$product_id . "'";

    $query = $this->db->query($sql);

    return isset($query->row['reason_id']) ? $query->row['reason_id'] : 0;
  }

  public function obtainReasons() {
      $sql = "SELECT mp_r.reason_id as id,mprd.title as title FROM " . DB_PREFIX . "mp_reason mp_r LEFT JOIN " . DB_PREFIX . "mp_reason_description mprd ON (mp_r.reason_id = mprd.reason_id) LEFT JOIN " . DB_PREFIX . "customerpartner_to_reason c2r ON (mprd.reason_id=c2r.reason_id) WHERE mprd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

    //   $sql .= " AND c2r.seller_id = ". (int)$this->customer->getId() ;

        $sql .= " AND mp_r.status = 1 GROUP BY mp_r.reason_id ORDER BY mprd.title";

      $query = $this->db->query($sql);

      return $query->rows;
  }

	public function getReasons($data = array()) {
      $sql = "SELECT * FROM " . DB_PREFIX . "mp_reason mp_r LEFT JOIN " . DB_PREFIX . "mp_reason_description mprd ON (mp_r.reason_id = mprd.reason_id) LEFT JOIN " . DB_PREFIX . "customerpartner_to_reason c2r ON (mprd.reason_id=c2r.reason_id) WHERE mprd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

  		// $sql .= " AND c2r.seller_id = ". (int)$this->customer->getId() ;

  		if (!empty($data['filter_name'])) {
  			$sql .= " AND mprd.title LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
  		}

  		if (isset($data['filter_status'])) {
  			$sql .= " AND mp_r.status = '" . (int)$data['filter_status'] . "'";
			}

			$sort_data = array(
				'mprd.title',
			);

			$sql .= " GROUP BY mp_r.reason_id ORDER BY mprd.title";

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

	public function getreasonDescriptions($reason_id) {
		$reason_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mp_reason_description WHERE reason_id = '" . (int)$reason_id . "'");

		foreach ($query->rows as $result) {
			$reason_description_data[$result['language_id']] = array(
				'title'            => $result['title'],
				'description'      => $result['description'],
			);
		}

		return $reason_description_data;
	}



	public function getTotalReasons($data) {

		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "mp_reason mp_r LEFT JOIN " . DB_PREFIX . "mp_reason_description mprd ON (mp_r.reason_id = mprd.reason_id) LEFT JOIN " . DB_PREFIX . "customerpartner_to_reason c2r ON (c2r.reason_id = mp_r.reason_id) WHERE mprd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		// $sql .= " AND c2r.seller_id = ". (int)$this->customer->getId() ;

		if (!empty($data['filter_name'])) {
			$sql .= " AND mprd.title LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (isset($data['filter_status'])) {
			$sql .= " AND mp_r.status = '" . (int)$data['filter_status'] . "'";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getUrlAlias($keyword) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "url_alias WHERE keyword = '" . $this->db->escape($keyword) . "'");

		return $query->row;
	}

	public function getLayouts($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "layout";

		$sort_data = array('name');

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY name";
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

	public function getSellerreasons($id = 0) {
	  $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mp_reason i LEFT JOIN " . DB_PREFIX . "mp_reason_description id ON (i.reason_id = id.reason_id) LEFT JOIN " . DB_PREFIX . "mp_reason_to_store i2s ON (i.reason_id = i2s.reason_id) LEFT JOIN " . DB_PREFIX . "customerpartner_to_reason c2r ON (c2r.reason_id = i.reason_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c2r.seller_id = " . (int)$id . " AND i2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND i.status = '1' ORDER BY i.sort_order, LCASE(id.title) ASC");

	  return $query->rows;
	}
}