<?php
class ModelExtensionModuleWkupsell extends Model {

/**
 * creates the up sell tables
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

		$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX ."upsell_related` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
		  `upsell_id` int(11) NOT NULL,
		  `parent_id` int(11) NOT NULL,
		  `child_id` int(11) NOT NULL,
		  `image` varchar(255) NOT NULL,
			`options` text,
		  `option_name` text,
			`view_count` int(11) NOT NULL,
			PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1");

		$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX ."vendor_upsell` (
		  `upsell_id` int(11) NOT NULL AUTO_INCREMENT,
		  `vendor_id` int(11) NOT NULL,
		  `countdown_status` tinyint(1) NOT NULL,
		  `date_start` datetime NOT NULL,
		  `date_end` datetime NOT NULL,
		  `quantity_status` tinyint(1) NOT NULL,
		  `quantity` int(11) NOT NULL,
		  `parent_products` varchar(512) NOT NULL,
		  `child_products` varchar(512) NOT NULL,
		  `date_added` datetime NOT NULL,
		  PRIMARY KEY (`upsell_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1");
	}

/**
 * deletes the tables
 * @return null none
 */
	public function deleteTables()	{
		$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX ."upsell_related`");
		$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX ."vendor_upsell`");
	}

	public function getStats($data = array()) {
		$sql = "SELECT vu.*, CONCAT(c.firstname, ' ', c.lastname) as vendor_name, c.store_id FROM " . DB_PREFIX . "vendor_upsell vu LEFT JOIN " . DB_PREFIX . "customer c ON (vu.vendor_id = c.customer_id) WHERE 1 = 1";

		if (isset($data['from_date'])) {
			$sql .= " AND vu.date_added >= '" . $data['from_date'] . "'";
		}

		if (isset($data['till_date'])) {
			$sql .= " AND vu.date_added <= '" . $data['till_date'] . "'";
		}

		$sql .= " ORDER BY vu.date_added DESC";

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
		$sql = "SELECT count(*) as total FROM " . DB_PREFIX . "vendor_upsell";

		$query = $this->db->query($sql)->row;

		return $query['total'];
	}

	public function deleteUpsell($upsell_id) {

		$this->db->query("DELETE FROM " . DB_PREFIX . "vendor_upsell WHERE upsell_id = '" . $upsell_id . "'");
	}
}
