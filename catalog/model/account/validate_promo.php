<?php
class ModelAccountValidatePromo extends Model {

	public function _getProductInfo($child) {
		return $this->db->query("SELECT p.quantity, pd.name FROM " . DB_PREFIX . "customerpartner_to_product cp LEFT JOIN " . DB_PREFIX . "product p ON (cp.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.status = '1' AND cp.product_id = '" . (int)$child . "'")->row;
	}

	public function _getEcardProductInfo($product_id, $date_end) {
		return $this->db->query("SELECT * FROM " . DB_PREFIX . "ecard_product WHERE availability_to >= '" . $this->db->escape($date_end) . "' AND product_id = '" . (int)$product_id . "'")->row;
	}

	public function _GetVartualInfo($parent_id, $date_end) {
		return $this->db->query("SELECT * FROM " . DB_PREFIX . "virtual_product WHERE expiry_date >= '" . $this->db->escape($date_end) . "' AND product_id = '" . (int)$parent_id . "'")->row;
	}

	public function _getPhsicalProductInfo($parent_id, $date_end) {
		$date = date('Y-m-d', strtotime($date_end));
    // return $this->db->query("SELECT * FROM " . DB_PREFIX . "product WHERE  date_available = '" . $this->db->escape($date) . "' AND product_id = '" . (int)$parent_id . "'")->row;
		return $this->db->query("SELECT * FROM " . DB_PREFIX . "product WHERE  product_id = '" . (int)$parent_id . "'")->row;
	}

	public function validateCrosssells() {
	    $post_data 	= $this->request->post;
	    $error 			= array();
			foreach ($post_data['product_child'] as $key => $child_product_id) {
          $isValid = $this->_getPhsicalProductInfo($child_product_id, $post_data['date_end']);
					if (!$isValid) {
							$error['error_product_childs'][$child_product_id] = true;
					}
			}
    	return $error;
  }

  public function validateCrosssellParent($parent_id) {
    $data = $this->request->post;
		$error['error'] = false;
		$error['quantity'] = false;

    $product = $this->_getProductInfo($parent_id);

    if (!$product) {
      $error['error'] = true;
    } else {
				$isValid = $this->_getPhsicalProductInfo($parent_id, $data['date_end']);
				if (!$isValid) {
					$error['error'] = true;
				}
    }

    return $error;
  }

  public function validateCrosssellQuantity() {
    $this->load->language('account/customerpartner/crosssell');
		$product_names = '';
		$child_products = isset($data['product_child']) && is_array($data['product_child']);
		$parent_product = isset($data['parent_id']) && $data['parent_id'];
    $data = $this->request->post;
    $error_quantity = '';

		if (isset($data['quantity_status']) && $data['quantity_status']) {
			if ($data['quantity'] && isset($data['product_child'])) {
				if ($child_products) {
					foreach ($data['product_child'] as $product_id) {
						$product = $this->model_account_promotional->getProductName($product_id, 1);
						$product = $this->db->query("SELECT p.quantity, pd.name FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "customerpartner_to_product cp ON (p.product_id = cp.product_id) WHERE p.product_id = '" . (int)$product_id . "'")->row;

							if ($product['quantity'] < $data['quantity']) {
								$product_names .= $product['name'] . '(' . $product['quantity'] . '), ';
							}

					}
				}

				if ($parent_product) {
					$product = $this->model_account_promotional->getProductName($data['parent_id'], 1);
					if ($product['quantity'] < $data['quantity']) {
						$product_names .= $product['name'] . '(' . $product['quantity'] . '), ';
					}
				}

				if ($product_names) {
					$product_names = rtrim($product_names, ', ');
					$error_quantity = sprintf($this->language->get('error_quantity'), $product_names);
				}
			} else {
				$error_quantity = $this->language->get('error_zero_quantity');
			}
		} else {
			if ($child_products) {
				foreach ($data['product_child'] as $product_id) {
					$product = $this->model_account_promotional->getProductName($product_id, 1);
					if ($product['quantity'] < 1) {
						$product_names .= $product['name'] . ', ';
					}
				}
			}
			if ($parent_product) {
				if ($child_products && in_array($data['parent_id'], $data['product_child'])) {
					// continue;
				}
				$product = $this->model_account_promotional->getProductName($data['parent_id'], 1);
				if ($data['quantity_status'] && $product['quantity'] < $data['quantity']) {
					$product_names .= $product['name'] . ', ';
				}
			}
			if ($product_names) {
				$product_names = rtrim($product_names, ', ');
				$error_quantity = sprintf($this->language->get('error_no_quantity'), $product_names);
			}
		}

    return $error_quantity;
  }
}
