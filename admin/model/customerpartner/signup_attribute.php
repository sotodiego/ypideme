<?php
class ModelCustomerpartnerSignupAttribute extends Model {

    /**
     * [addVendorAttribute add vendorattribute details]
     * @param [type] $data [details]
     */
	public function addVendorAttribute($data){
        $this->db->query("INSERT INTO `" . DB_PREFIX . "custom_field` SET type = '" . $this->db->escape($data['type']) . "', value = '" . $this->db->escape($data['value']) . "', location = '" . $this->db->escape($data['location']) . "', status = '" . (int)$data['status'] . "', sort_order = '" . (int)$data['sort_order'] . "'");

		$custom_field_id = $this->db->getLastId();

   /**
    * [custumization]
    * @var [Start]
    */
    if($data['type'] == 'checkbox' || $data['type'] == 'select' || $data['type'] == 'radio') {
      $this->insertFilterStatus($custom_field_id,$data['filter_value_attr']);
    }

    /**
     * [custumization]
     * @var [end]
     */

		foreach ($data['vendorattribute_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "custom_field_description SET custom_field_id = '" . (int)$custom_field_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
		}

		if (isset($data['custom_field_customer_group'])) {
			foreach ($data['custom_field_customer_group'] as $custom_field_customer_group) {
				if (isset($custom_field_customer_group['customer_group_id'])) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "custom_field_customer_group SET custom_field_id = '" . (int)$custom_field_id . "', customer_group_id = '" . (int)$custom_field_customer_group['customer_group_id'] . "', required = '" . (int)(isset($custom_field_customer_group['required']) ? 1 : 0) . "'");
				}
			}
		}

		if (isset($data['vendorattribute_value'])) {
			foreach ($data['vendorattribute_value'] as $vendorattribute_value) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "custom_field_value SET custom_field_id = '" . (int)$custom_field_id . "', sort_order = '" . (int)$vendorattribute_value['sort_order'] . "'");

				$custom_field_value_id = $this->db->getLastId();

				foreach ($vendorattribute_value['custom_field_value_description'] as $language_id => $custom_field_value_description) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "custom_field_value_description SET custom_field_value_id = '" . (int)$custom_field_value_id . "', language_id = '" . (int)$language_id . "', custom_field_id = '" . (int)$custom_field_id . "', name = '" . $this->db->escape($custom_field_value_description['name']) . "'");
				}
			}
		}
	}

    /**
     * [editVendorAttribute edit vendor attribute details]
     * @param  [type] $custom_field_id [custom field id]
     * @param  [type] $data            [details]
     * @return [type]                  [no]
     */
	public function editVendorAttribute($custom_field_id, $data) {

		$this->db->query("UPDATE `" . DB_PREFIX . "custom_field` SET type = '" . $this->db->escape($data['type']) . "', value = '" . $this->db->escape($data['value']) . "', location = '" . $this->db->escape($data['location']) . "', status = '" . (int)$data['status'] . "', sort_order = '" . (int)$data['sort_order'] . "' WHERE custom_field_id = '" . (int)$custom_field_id . "'");

    /**
     * [custumization]
     * @var [start]
     */

     $attr_filter = $this->db->query("SELECT * FROM `" . DB_PREFIX . "custom_field_attr_filter` WHERE custom_field_id = '" . (int)$custom_field_id . "'");

     if($attr_filter->num_rows) {
          if($data['type'] == 'checkbox' || $data['type'] == 'select' || $data['type'] == 'radio') {
            $this->updateFilterStatus($custom_field_id,$data['filter_value_attr']);
          } else {
            $this->deleteFilterStatus($custom_field_id);
          }
     } else {
        if($data['type'] == 'checkbox' || $data['type'] == 'select' || $data['type'] == 'radio') {
          $this->insertFilterStatus($custom_field_id,$data['filter_value_attr']);
        }
     }

     /**
      * [custumization]
      * @var [end]
      */

		$this->db->query("DELETE FROM " . DB_PREFIX . "custom_field_description WHERE custom_field_id = '" . (int)$custom_field_id . "'");

		foreach ($data['vendorattribute_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "custom_field_description SET custom_field_id = '" . (int)$custom_field_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "custom_field_customer_group WHERE custom_field_id = '" . (int)$custom_field_id . "'");

		if (isset($data['custom_field_customer_group'])) {
			foreach ($data['custom_field_customer_group'] as $custom_field_customer_group) {
				if (isset($custom_field_customer_group['customer_group_id'])) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "custom_field_customer_group SET custom_field_id = '" . (int)$custom_field_id . "', customer_group_id = '" . (int)$custom_field_customer_group['customer_group_id'] . "', required = '" . (int)(isset($custom_field_customer_group['required']) ? 1 : 0) . "'");
				}
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "custom_field_value WHERE custom_field_id = '" . (int)$custom_field_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "custom_field_value_description WHERE custom_field_id = '" . (int)$custom_field_id . "'");

		if (isset($data['vendorattribute_value'])) {
			foreach ($data['vendorattribute_value'] as $vendorattribute_value) {
				if ($vendorattribute_value['custom_field_value_id']) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "custom_field_value SET custom_field_value_id = '" . (int)$vendorattribute_value['custom_field_value_id'] . "', custom_field_id = '" . (int)$custom_field_id . "', sort_order = '" . (int)$vendorattribute_value['sort_order'] . "'");
				} else {
					$this->db->query("INSERT INTO " . DB_PREFIX . "custom_field_value SET custom_field_id = '" . (int)$custom_field_id . "', sort_order = '" . (int)$vendorattribute_value['sort_order'] . "'");
				}

				$custom_field_value_id = $this->db->getLastId();

				foreach ($vendorattribute_value['custom_field_value_description'] as $language_id => $custom_field_value_description) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "custom_field_value_description SET custom_field_value_id = '" . (int)$custom_field_value_id . "', language_id = '" . (int)$language_id . "', custom_field_id = '" . (int)$custom_field_id . "', name = '" . $this->db->escape($custom_field_value_description['name']) . "'");
				}
			}
		}
	}

  /**
   * [custumization]
   * @var [start]
   */


  /**
   * [deleteFilterStatus To delete specific vendor atrribute for filters]
   * @param  [type] $custom_field_id [custom filed id]
   * @return [type]                  [bool]
   */
  public function deleteFilterStatus($custom_field_id) {

    $this->db->query("DELETE FROM `" . DB_PREFIX . "custom_field_attr_filter` WHERE custom_field_id = '" . (int)$custom_field_id . "'");
    $this->db->query("DELETE FROM `" . DB_PREFIX . "customerparner_to_customer_signup_attr_value` WHERE custom_signup_filter_id = '" . (int)$custom_field_id . "'");

  }

 /**
  * [insertFilterStatus Insert new details for status of filter for vendor custom attribute]
  * @param  [type] $custom_field_id [id for vedore custom field]
  * @param  [type] $filter_required [value for filter status]
  * @return [type]                  [bool]
  */
  public function insertFilterStatus($custom_field_id,$filter_required) {

       $this->db->query("INSERT INTO " . DB_PREFIX . "custom_field_attr_filter SET custom_field_id = '" . (int)$custom_field_id . "', filter_required = '" . (int)$filter_required . "'");

  }

  /**
   * [updateFilterStatus Update the details for status of filter for vendor custom filed]
   * @param  [type] $custom_field_id [id for vedore custom field]
   * @param  [type] $filter_required [new value for filter status]
   * @return [type]                  [bool]
   */
  public function updateFilterStatus($custom_field_id,$filter_required) {

       $this->db->query("UPDATE `" . DB_PREFIX . "custom_field_attr_filter` SET custom_field_id = '" . (int)$custom_field_id . "', filter_required = '" . (int)$filter_required . "' WHERE custom_field_id = '" . (int)$custom_field_id . "'");

  }

  /**
   * [custumization]
   * @var [End]
   */


    /**
     * [deleteVendorAttribute delete vendor attribute]
     * @param  [type] $custom_field_id [custom field id]
     * @return [type]                  [no]
     */
	public function deleteVendorAttribute($custom_field_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "custom_field` WHERE custom_field_id = '" . (int)$custom_field_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "custom_field_description` WHERE custom_field_id = '" . (int)$custom_field_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "custom_field_customer_group` WHERE custom_field_id = '" . (int)$custom_field_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "custom_field_value` WHERE custom_field_id = '" . (int)$custom_field_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "custom_field_value_description` WHERE custom_field_id = '" . (int)$custom_field_id . "'");

    /**
     * [custumization]
     * @var [type]
     */
     $this->deleteVendorAttributeFilter($custom_field_id);
    /**
     * [custumization]
     * @var [type]
     */

	}

 /**
  * [deleteVendorAttributeFilter To delete the vendor filter ]
  * @param  [type] $custom_field_id [id of field]
  * @return [type]                  [Boolen]
  */
  public function deleteVendorAttributeFilter($custom_field_id) {
    $this->db->query("DELETE FROM `" . DB_PREFIX . "custom_field_attr_filter` WHERE custom_field_id = '" . (int)$custom_field_id . "'");
   }
    /**
     * [getVendorAttribute get vendor attribute details]
     * @param  [type] $custom_field_id [custom field id]
     * @return [type]                  [array]
     */
	public function getVendorAttribute($custom_field_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "custom_field` cf LEFT JOIN " . DB_PREFIX . "custom_field_description cfd ON (cf.custom_field_id = cfd.custom_field_id) LEFT JOIN " . DB_PREFIX . "custom_field_attr_filter cfa ON (cfa.custom_field_id = cf.custom_field_id) WHERE cf.custom_field_id = '" . (int)$custom_field_id . "' AND cfd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

    /**
     * [getVendorAttributes get vendor attributes]
     * @param  array  $data [details]
     * @return [type]       [no]
     */
	public function getVendorAttributes($data = array()) {
		if (empty($data['filter_customer_group_id'])) {
			$sql = "SELECT * FROM `" . DB_PREFIX . "custom_field` cf LEFT JOIN " . DB_PREFIX . "custom_field_description cfd ON (cf.custom_field_id = cfd.custom_field_id) WHERE cfd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND location='mp_sc'";
		} else {
			$sql = "SELECT * FROM " . DB_PREFIX . "custom_field_customer_group cfcg LEFT JOIN `" . DB_PREFIX . "custom_field` cf ON (cfcg.custom_field_id = cf.custom_field_id) AND  LEFT JOIN " . DB_PREFIX . "custom_field_description cfd ON (cf.custom_field_id = cfd.custom_field_id) WHERE cfd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND location='mp_sc'";
		}

	if (!empty($data['filter_name'])) {
			$sql .= " AND cfd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_customer_group_id'])) {
			$sql .= " AND cfcg.customer_group_id = '" . (int)$data['filter_customer_group_id'] . "'";
		}

		$sort_data = array(
			'cfd.name',
			'cf.type',
			'cf.location',
			'cf.status',
			'cf.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY cfd.name";
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


    /**
     * [getVendorAttributeDescriptions get vendor attribute description]
     * @param  [type] $custom_field_id [custom_field_id]
     * @return [type]                  [array]
     */
	public function getVendorAttributeDescriptions($custom_field_id) {
		$custom_field_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "custom_field_description WHERE custom_field_id = '" . (int)$custom_field_id . "'");

		foreach ($query->rows as $result) {
			$custom_field_data[$result['language_id']] = array('name' => $result['name']);
		}

		return $custom_field_data;
	}

    /**
     * [getCustomFieldCustomerGroups fetch custom field group]
     * @param  [type] $custom_field_id [custom field id]
     * @return [type]                  [array]
     */
	public function getCustomFieldCustomerGroups($custom_field_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "custom_field_customer_group` WHERE custom_field_id = '" . (int)$custom_field_id . "'");

		return $query->rows;
	}

    /**
     * [getVendorAttributeValueDescriptions get vendor attribute value description]
     * @param  [type] $custom_field_id [custom field id]
     * @return [type]                  [array]
     */
	public function getVendorAttributeValueDescriptions($custom_field_id) {

		$vendorattribute_data = array();

		$vendorattribute_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "custom_field_value WHERE custom_field_id = '" . (int)$custom_field_id . "'");

		foreach ($vendorattribute_query->rows as $custom_field_value) {
			$custom_field_value_description_data = array();

			$custom_field_value_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "custom_field_value_description WHERE custom_field_value_id = '" . (int)$custom_field_value['custom_field_value_id'] . "'");

			foreach ($custom_field_value_description_query->rows as $custom_field_value_description) {
				$custom_field_value_description_data[$custom_field_value_description['language_id']] = array('name' => $custom_field_value_description['name']);
			}

			$vendorattribute_data[] = array(
				'custom_field_value_id'          => $custom_field_value['custom_field_value_id'],
				'custom_field_value_description' => $custom_field_value_description_data,
				'sort_order'                     => $custom_field_value['sort_order']
			);
		}

		return $vendorattribute_data;
	}

    /**
     * [getTotalVendorAttributes get total vendor attribute]
     * @return [type] [integer]
     */
	public function getTotalVendorAttributes() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "custom_field` where location='mp_sc'");

		return $query->row['total'];
	}

}
