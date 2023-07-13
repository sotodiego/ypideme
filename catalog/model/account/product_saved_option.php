<?php
class ModelAccountProductsavedoption extends Model {
	public function getGiftProductsOptions($id) {
		$options = $this->db->query("SELECT options, giftteasor_id, parent_options, parent_id, child_id FROM ". DB_PREFIX . "giftteasor_related WHERE id ='". $id ."'")->row;
		return $options;
	}

	public function getCrosssellProductsOptions($id) {
		$options = $this->db->query("SELECT options, crosssell_id, parent_options, parent_id, child_id FROM ". DB_PREFIX . "crosssell_related WHERE id ='". $id ."'")->row;
		return $options;
	}

	public function getUpsellProductsOptions($id) {
		$options = $this->db->query("SELECT options, upsell_id, child_id FROM ". DB_PREFIX . "upsell_related WHERE id ='". $id ."'")->row;
		return $options;
	}

	public function mapMarketingToolProducts($cart_id, $type, $i_id) {
			$this->db->query("INSERT " . DB_PREFIX . "cart_mapping SET cart_id = '".$this->db->escape(json_encode($cart_id))."' , customer_id = '" . (int)$this->customer->getId() . "', session = '" . $this->db->escape($this->session->getId()) . "', `type` = '" . $this->db->escape($type) . "', i_id = '" . (int)$i_id . "'");
	}

	public function getMappedCart() {
			return $this->db->query("SELECT * FROM ". DB_PREFIX . "cart_mapping WHERE customer_id = '" . (int)$this->customer->getId() . "' AND  session = '" . $this->db->escape($this->session->getId()) . "'")->rows;
	}

	public function deleteMapping($id) {
			$this->db->query("DELETE FROM " . DB_PREFIX . "cart_mapping WHERE id = '" . (int)$id . "'");
	}

	public function substractQuantity($type, $id) {
    // for the cross sell orders
		if($type == 'U') {

				$quantity = 0;
				if($this->db->query("SELECT quantity FROM ". DB_PREFIX . "vendor_upsell WHERE upsell_id ='". (int)$id ."'")->row) {
					$quantity = $this->db->query("SELECT quantity FROM ". DB_PREFIX . "vendor_upsell WHERE upsell_id ='". (int)$id ."'")->row['quantity'];
				}

				if($quantity) {
						$this->db->query("UPDATE " . DB_PREFIX . "vendor_upsell SET quantity = '" . (int)($quantity - 1) . "' WHERE upsell_id = '" . (int)$id . "'");
				}
		}
	}
}
