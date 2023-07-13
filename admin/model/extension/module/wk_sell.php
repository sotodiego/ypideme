<?php
class ModelExtensionModuleWksell extends Model {
/**
 * Fetches the details of vendors present
 * @param  array  $data contains the filter data
 * @return array       contains vendor details
 */
	public function getVendors($data) {
		$sql = "SELECT *, CONCAT(c.firstname, ' ', c.lastname) AS name FROM " . DB_PREFIX . "customer c RIGHT JOIN " . DB_PREFIX . "customerpartner_to_customer c2c ON (c.customer_id = c2c.customer_id) WHERE 1=1 ";

		if (!empty($data['filter_name'])) {
			$sql .= " AND CONCAT(c.firstname, ' ', c.lastname) LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sql .= " ORDER BY name";

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
 * fetches vendor name
 * @param  array $vendor_id contains vendor ID
 * @return array            contains vendor name
 */
	public function getVendor($vendor_id) {
		$sql = "SELECT c.customer_id as vendor_id, CONCAT(c.firstname, ' ', c.lastname) AS name FROM " . DB_PREFIX . "customer c LEFT JOIN " . DB_PREFIX . "customerpartner_to_customer c2c ON (c.customer_id = c2c.customer_id) WHERE c.customer_id ='" . $vendor_id ."'";
		$query = $this->db->query($sql);

		return $query->row;
	}

/**
 * Fetches all the categories present
 * @return array return the list of categories
 */
	public function getCategories()	{
		$sql = "SELECT c.category_id,cd.name FROM " . DB_PREFIX . "category c LEFT JOIN ". DB_PREFIX ."category_description cd ON (c.category_id = cd.category_id) WHERE c.parent_id = '0' AND cd.language_id = '" . $this->config->get('config_language_id') . "'";
		$query = $this->db->query($sql);

		return $query->rows;
	}

/**
 * Fetches the sub-categories present for a given category
 * @param  integer $category_id contains the category ID
 * @return array              returns the list of sub-categories
 */
	public function getSubCategories($category_id)	{
		$sql = "SELECT c.category_id,cd.name FROM " . DB_PREFIX . "category c LEFT JOIN ". DB_PREFIX ."category_description cd ON (c.category_id = cd.category_id) WHERE c.parent_id = '". $category_id ."' AND cd.language_id = '" . $this->config->get('config_language_id') . "'";
		$query = $this->db->query($sql);

		return $query->rows;
	}

/**
 * Fetches the list of all the products along with their name and ID
 * @return array returns the product name and ID
 */
	public function getProducts()	{
		$sql = "SELECT p.product_id,pd.name FROM " . DB_PREFIX . "product p LEFT JOIN ". DB_PREFIX ."product_description pd ON (p.product_id = pd.product_id) WHERE pd.language_id = '" . $this->config->get('config_language_id') . "'";
		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getProductName($product_id)	{
		$product = $this->db->query("SELECT name FROM " . DB_PREFIX . "product_description WHERE language_id = '" . $this->config->get('config_language_id') . "' AND product_id = '" . (int)$product_id . "'")->row;

		if (isset($product['name'])) {
			return $product['name'];
		} else {
			return false;
		}
	}
}
