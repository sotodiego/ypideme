<?php
class ModelExtensionModuleWkcrosssell extends Model {

/**
 * creates the cross sell tables
 * @return null none
 */
	public function createTables() {
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "giftteasor_related` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
		  `giftteasor_id` int(11) NOT NULL,
		  `parent_id` int(11) NOT NULL,
		  `child_id` int(11) NOT NULL,
		  `image` varchar(255) NOT NULL,
			`options` text,
		  `option_name` text,
			`parent_options` text,
		  `parent_options_name` text,
			PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "crosssell_related` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
		  `crosssell_id` int(11) NOT NULL,
		  `parent_id` int(11) NOT NULL,
		  `child_id` int(11) NOT NULL,
		  `bundle_price` decimal(20,4) NOT NULL,
		  `vendor_price` decimal(20,4) NOT NULL,
		  `parent_name` varchar(255) NOT NULL,
		  `child_name` varchar(255) NOT NULL,
		  `image` varchar(255) NOT NULL,
			`options` text,
		  `option_name` text,
			`parent_options` text,
		  `parent_options_name` text,
			PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "vendor_crosssell` (
		  `crosssell_id` int(11) NOT NULL AUTO_INCREMENT,
		  `vendor_id` int(11) NOT NULL,
		  `countdown_status` tinyint(1) NOT NULL,
		  `date_start` datetime NOT NULL,
		  `date_end` datetime NOT NULL,
		  `quantity_status` tinyint(1) NOT NULL,
		  `quantity` int(11) NOT NULL,
		  `parent_product` int(11) NOT NULL,
		  `child_products` varchar(512) NOT NULL,
		  `date_added` datetime NOT NULL,
		  PRIMARY KEY (`crosssell_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "customer_bundles` (
		  `bundle_id` int(11) NOT NULL AUTO_INCREMENT,
		  `related_id` int(11) NOT NULL,
		  `customer_id` int(11) NOT NULL,
		  `session_id` int(11) NOT NULL,
		  `quantity` int(11) NOT NULL,
		  PRIMARY KEY (`bundle_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1");

		$this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "cart_mapping` (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`cart_id` VARCHAR(255) NOT NULL,
			`session` VARCHAR(255),
			`customer_id` INT(11),
			`type` VARCHAR(10),
			`i_id` INT(11),
			PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");
	}

/**
 * deletes the tables
 * @return null none
 */
	public function deleteTables()	{
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "crosssell_related`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "vendor_crosssell`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "customer_bundles`");
	}

	public function getStats($data = array()) {
		$sql = "SELECT vc.*, CONCAT(c.firstname, ' ', c.lastname) as vendor_name, c.store_id FROM " . DB_PREFIX . "vendor_crosssell vc LEFT JOIN " . DB_PREFIX . "customer c ON (vc.vendor_id = c.customer_id) WHERE 1 = 1";

		if (isset($data['from_date'])) {
			$sql .= " AND vc.date_added >= '" . $data['from_date'] . "'";
		}

		if (isset($data['till_date'])) {
			$sql .= " AND vc.date_added <= '" . $data['till_date'] . "'";
		}

		$sql .= " ORDER BY vc.date_added DESC";

		if ($data['start']) {
			$sql .= " LIMIT " . $data['start'] . ", ";
		} else {
			$sql .= " LIMIT 0, ";
		}

		if (isset($data['limit'])) {
			$sql .= $data['limit'];
		} else {
			$sql .= '20';
		}

		$query = $this->db->query($sql)->rows;

		return $query;
	}

	public function getTotalStats() {
		$sql = "SELECT count(*) as total FROM " . DB_PREFIX . "vendor_crosssell";

		$query = $this->db->query($sql)->row;

		return $query['total'];
	}

	public function deleteCrosssell($crosssell_id) {

		$this->db->query("DELETE FROM " . DB_PREFIX . "vendor_crosssell WHERE crosssell_id = '" . $crosssell_id . "'");
	}
}
