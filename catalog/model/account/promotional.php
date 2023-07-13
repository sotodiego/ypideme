<?php
/**
 * default image folder for storing all product images that will be visible in the listing
 */
const PROMOIMAGEFOLDER = 'catalog/promo/';

class ModelAccountPromotional extends Model {
/**
 * Adds an upsell product to a parent product
 * @param array $data contain upsell details
 */
	public function addUpsell($data) {
		$renamedOImage = array();
		$files = $this->request->files;

		if (isset($files['image']['name']) && $files['image']['name']) {
			foreach ($files['image']['name'] as $index => $image) {
				if ($image) {
					$renamedImg = rand(100000, 999999) . basename(preg_replace('~[^\w\./\\\\]+~', '', $image));

					move_uploaded_file($files['image']['tmp_name'][$index], DIR_IMAGE . PROMOIMAGEFOLDER . $renamedImg);
				} else {
					$renamedImg = '';
				}

				$renamedOImage[$index] = $renamedImg;
			}
		}

		if ($data['product_child'])
			$child_products = implode(',', $data['product_child']);
		else
			$child_products = '';

		if ($data['product_parent'])
			$parent_products = implode(',', $data['product_parent']);
		else
			$parent_products = '';

		if (isset($data['countdown_status'])) {
			$countdown_status = (int)$data['countdown_status'];
		} else {
			$countdown_status = 0;
		}

		if (isset($data['quantity'])) {
			$quantity = (int)$data['quantity'];
		} else {
			$quantity = 0;
		}

		if (isset($data['quantity_status']) && $data['quantity_status']) {
			$quantity_status = (int)$data['quantity_status'];
		} else {
			$quantity_status = 0;
			$quantity = 0;
		}

		// manages time difference according to zone
		if (isset($_COOKIE['time_diff'])) {
			$time_diff = $_COOKIE['time_diff'] * 3600;
		}

		$date_start = strtotime($data['date_start']);
		$date_end = strtotime($data['date_end']);

		// saving the start date and end date according to php date
		if (isset($time_diff) && $time_diff) {
			$date_start = $date_start - $time_diff;
			$date_end = $date_end - $time_diff;
		}

		$start_date = date('Y-m-d H:i:s', $date_start);
		$end_date = date('Y-m-d H:i:s', $date_end);

		$this->db->query("INSERT INTO `" . DB_PREFIX . "vendor_upsell` SET `vendor_id` = '" . (int)$this->customer->getId() . "', `countdown_status` = '" . (int)$countdown_status . "', `date_start` = '" . $start_date . "', `date_end` = '" . $end_date . "', `quantity_status` = '" . (int)$quantity_status . "', `quantity` = '" . (int)$quantity . "', `parent_products` = '" . $parent_products . "', `child_products` = '" . $child_products . "', `date_added` = NOW()");

		$upsell_id = $this->db->getLastId();

		// inserts as per the upsell id
		if ($data['product_parent']) {
			foreach ($data['product_parent'] as $parent) {
				foreach ($data['product_child'] as $child_key => $child) {
					if ($renamedOImage[$child_key]) {
						$image = PROMOIMAGEFOLDER . $this->db->escape(html_entity_decode($renamedOImage[$child_key], ENT_QUOTES, 'UTF-8'));
					} elseif ($data['photo'][$child_key]) {
						$image = $data['photo'][$child_key];
					} else {
						$image = '';
					}
          if(isset($data['option'][$child_key])) {
						$product_option_value = html_entity_decode($data['option'][$child_key]);
					} else {
						$product_option_value = '';
					}
					if(isset($data['option_name'][$child_key])) {
						$product_option_name = $data['option_name'][$child_key];
					} else {
						$product_option_name = '';
					}
					if (!$product_option_value) {
						$product_option_value = json_encode(array());
					}

					$this->db->query("INSERT INTO `" . DB_PREFIX . "upsell_related` SET upsell_id = '" . (int)$upsell_id . "', parent_id = '" . $parent . "', child_id = '" . $child ."',`options` = '" . $this->db->escape($product_option_value) . "',`option_name` = '" . $this->db->escape($product_option_name) . "', image = '" . $image . "'");
				}
			}
		}
	}

/**
 * Edits a pre-existing upsell entry
 * @param  array $data      contains upsell detail
 * @param  [integer] $upsell_id [upsell id]
 * @return [null]            [none]
 */
	public function editUpsell($data, $upsell_id) {
		$renamedOImage = array();
		$files = $this->request->files;

		if (isset($files['image']['name']) && $files['image']['name']) {
			foreach ($files['image']['name'] as $index => $image) {
				if ($image) {
					$renamedImg = rand(100000, 999999) . basename(preg_replace('~[^\w\./\\\\]+~', '', $image));

					move_uploaded_file($files['image']['tmp_name'][$index], DIR_IMAGE . PROMOIMAGEFOLDER . $renamedImg);
				} else {
					$renamedImg = '';
				}

				$renamedOImage[$index] = $renamedImg;
			}
		}

		if ($data['product_child']) {
			$child_products = implode(',', $data['product_child']);
		} else {
			$child_products = '';
		}

		if ($data['product_parent']) {
			$parent_products = implode(',', $data['product_parent']);
		} else {
			$parent_products = '';
		}

		if (isset($data['countdown_status'])) {
			$countdown_status = (int)$data['countdown_status'];
		} else {
			$countdown_status = 0;
		}

		if (isset($data['quantity'])) {
			$quantity = (int)$data['quantity'];
		} else {
			$quantity = 0;
		}

		if (isset($data['quantity_status']) && $data['quantity_status']) {
			$quantity_status = (int)$data['quantity_status'];
		} else {
			$quantity_status = 0;
			$quantity = 0;
		}

		if (isset($_COOKIE['time_diff'])) {
			$time_diff = $_COOKIE['time_diff'] * 3600;
		}

		$date_start = strtotime($data['date_start']);
		$date_end = strtotime($data['date_end']);

		// manages the time according to zones
		if (isset($time_diff) && $time_diff) {
			$date_start = $date_start - $time_diff;
			$date_end = $date_end - $time_diff;
		}

		$start_date = date('Y-m-d H:i:s', $date_start);
		$end_date = date('Y-m-d H:i:s', $date_end);

		$this->db->query("UPDATE `" . DB_PREFIX . "vendor_upsell` SET `countdown_status` = '" . (int)$countdown_status . "', `date_start` = '" . $start_date . "', `date_end` = '" . $end_date . "', `quantity_status` = '" . (int)$quantity_status . "', `quantity` = '" . (int)$quantity . "', `parent_products` = '" . $parent_products . "', `child_products` = '" . $child_products . "', `date_added` = NOW() WHERE upsell_id = '" . (int)$upsell_id . "'");

		// deletes all entries in upsell_related table of current upsell id
		$this->db->query("DELETE FROM " . DB_PREFIX . "upsell_related WHERE upsell_id = '" . (int)$upsell_id . "'");

		// inserts/updates all the entries in the upsell_related table
		if ($data['product_parent']) {
			foreach ($data['product_parent'] as $parent) {
				foreach ($data['product_child'] as $child_key => $child) {
					if (isset($renamedOImage[$child_key]) && $renamedOImage[$child_key]) {
						$image = PROMOIMAGEFOLDER . $this->db->escape(html_entity_decode($renamedOImage[$child_key], ENT_QUOTES, 'UTF-8'));
					} elseif ($data['photo'][$child_key]) {
						$image = $data['photo'][$child_key];
					} else {
						$image = '';
					}

					$product_option_value = html_entity_decode($data['option'][$child_key]);
					$product_option_name = $data['option_name'][$child_key];
					if (!$product_option_value) {
						$product_option_value = json_encode(array());
					}

					$sql = "INSERT INTO `" . DB_PREFIX . "upsell_related` SET upsell_id = '" . (int)$upsell_id . "', parent_id = '" . $parent . "', `options` = '" . $this->db->escape($product_option_value) . "', `option_name` = '" . $this->db->escape($product_option_name) . "', child_id = '" . $child . "'";

					if ($image) {
						$sql .= ", image = '" . $image . "'";
					} elseif (isset($data['image'][$child_key]) && $data['image'][$child_key]) {
						// if no image is uploaded on edit then image path is gathered from the image index
						$sql .= ", image = '" . $data['image'][$child_key] . "'";
					} else {
						// if still didn't manage to find then nothing will be store
						$sql .= ", image = ''";
					}
					$this->db->query($sql);
				}
			}
		}
	}

/**
 * Fetches all upsells of a particular vendor
 * @param  array  $filter contains filter details
 * @return array         contains upsell rows
 */
	public function getAllUpsell($filter = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_upsell WHERE `vendor_id` = '" . $this->customer->getId() . "'";

		return $this->db->query($sql)->rows;
	}

  public function updateUpsellViewCount($product_id = false) {
		$this->db->query("UPDATE ".DB_PREFIX."upsell_related SET view_count = (view_count + 1) WHERE parent_id = '".(int)$product_id."' ");
	}

	public function _getAllUpsell($filter = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_upsell vu LEFT JOIN " . DB_PREFIX . "product_description pd ON FIND_IN_SET(pd.product_id,vu.parent_products) WHERE `vendor_id` = '" . $this->customer->getId() . "' AND language_id = 1";

		if($filter['filter_product']){
			$sql .= " AND pd.name LIKE '%".$this->db->escape($filter['filter_product'])."%'";
		}

    if($filter['start_date']){
			$date_start  = strtotime($filter['start_date']);
			$start_date = date('Y-m-d H:i:s', $date_start);
			$sql .= " AND vu.date_start <= '" . $this->db->escape($start_date) . "' ";
		}

		if($filter['end_date']){
			$date_start = strtotime($filter['end_date']);
			$end_date   = date('Y-m-d H:i:s', $date_start);
			$sql .= " AND vu.date_end >= '" . $this->db->escape($end_date) . "' ";
		}

    $sql .= " ORDER BY pd.name DESC";

	  $sql .= " LIMIT ".$filter['start'].", " . $filter['limit'];

		return $this->db->query($sql)->rows;
	}

	public function _getAllUpsellTotal($filter = array()) {

		$sql = "SELECT count(*) as total FROM " . DB_PREFIX . "vendor_upsell vu LEFT JOIN " . DB_PREFIX . "product_description pd ON FIND_IN_SET(pd.product_id,vu.parent_products) WHERE `vendor_id` = '" . $this->customer->getId() . "' AND language_id = 1";

		if($filter['filter_product']){
			$sql .= " AND pd.name LIKE '%".$this->db->escape($filter['filter_product'])."%'";
		}

    if($filter['start_date']){
			$sql .= " AND vu.date_start <= '" . $this->db->escape($filter['start_date']) . "' ";
		}
		if($filter['end_date']){
			$sql .= " AND vu.date_end >= '" . $this->db->escape($filter['end_date']) . "' ";
		}

    $sql .= " ORDER BY pd.name DESC";

	  $sql .= " LIMIT ".$filter['start'].", " . $filter['limit'];

		return $this->db->query($sql)->row;
	}

/**
 * Gets total number of upsells
 * @param  array  $filter contains filter data
 * @return interger         contain the count of upsell
 */
	public function getAllUpsellTotal($filter = array()) {
		$sql = "SELECT count(*) as total FROM " . DB_PREFIX . "vendor_upsell WHERE `vendor_id` = '" . $this->customer->getId() . "'";

		return $this->db->query($sql)->row['total'];
	}
/**
 * fetches a particular upsell with upsell id
 * @param  integer $upsell_id contains upsell id
 * @return array            returns the details of an upsell entry
 */
	public function getUpsell($upsell_id) {
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_upsell WHERE `vendor_id` = '" . $this->customer->getId() . "' AND upsell_id = '" . (int)$upsell_id . "'";

		return $this->db->query($sql)->row;
	}

/**
 * Deletes the upsell with its upsell ID
 * @param  integer $upsell_id contains upsell ID
 * @return null            none
 */
	public function deleteUpsell($upsell_id) {
		$checkUpsell = $this->db->query("SELECT * FROM " . DB_PREFIX . "vendor_upsell WHERE upsell_id = '" . (int)$upsell_id . "' AND vendor_id = '" . $this->customer->getId() . "'");
		if ($checkUpsell->num_rows) {
			$this->db->query("DELETE FROM " . DB_PREFIX . "vendor_upsell WHERE upsell_id = '" . (int)$upsell_id . "'");
			$this->db->query("DELETE FROM " . DB_PREFIX . "upsell_related WHERE upsell_id = '" . (int)$upsell_id . "'");
		}
	}

/**
 * adds a cross sell entry in DB
 * @param array $data contains cross sell details
 */
	public function addCrosssell($data) {
		$renamedOImage = array();
		$files = $this->request->files;

		if (isset($files['image']['name']) && $files['image']['name']) {
			foreach ($files['image']['name'] as $index => $image) {
				if ($image) {
					$renamedImg = rand(100000, 999999) . basename(preg_replace('~[^\w\./\\\\]+~', '', $image));
					/**
					 * upload cross sell bundle product images
					 */
					move_uploaded_file($files['image']['tmp_name'][$index], DIR_IMAGE . PROMOIMAGEFOLDER . $renamedImg);
				} else {
					$renamedImg = '';
				}

				$renamedOImage[$index] = $renamedImg;
			}
		}

		if ($data['product_child'])
			$child_products = implode(',', $data['product_child']);
		else
			$child_products = '';

		if (isset($data['countdown_status'])) {
			$countdown_status = (int)$data['countdown_status'];
		} else {
			$countdown_status = 0;
		}

		if (isset($data['quantity_status'])) {
			$quantity_status = (int)$data['quantity_status'];
		} else {
			$quantity_status = 0;
		}

		if (isset($data['quantity'])) {
			$quantity = (int)$data['quantity'];
		} else {
			$quantity = 0;
		}

		if (isset($data['quantity_status']) && $data['quantity_status']) {
			$quantity_status = (int)$data['quantity_status'];
		} else {
			$quantity_status = 0;
			$quantity = 0;
		}

		if (isset($_COOKIE['time_diff'])) {
			$time_diff = $_COOKIE['time_diff'] * 3600;
		}

		$date_start = strtotime($data['date_start']);
		$date_end = strtotime($data['date_end']);

		if (isset($time_diff) && $time_diff) {
			$date_start = $date_start - $time_diff;
			$date_end = $date_end - $time_diff;
		}

		$start_date = date('Y-m-d H:i:s', $date_start);
		$end_date = date('Y-m-d H:i:s', $date_end);

		$this->db->query("INSERT INTO `" . DB_PREFIX . "vendor_crosssell` SET `vendor_id` = '" . (int)$this->customer->getId() . "', `countdown_status` = '" . (int)$countdown_status . "', `date_start` = '" . $start_date . "', `date_end` = '" . $end_date . "', `quantity_status` = '" . (int)$quantity_status . "', `quantity` = '" . (int)$quantity . "', `parent_product` = '" . (int)$data['parent_id'] . "', `child_products` = '" . $child_products . "', `date_added` = NOW()");

		$crosssell_id = $this->db->getLastId();

		if ($data['product_child']) {
			foreach ($data['product_child'] as $child_key => $child) {
				$converted_price = $this->currency->convert($data['price_child'][$child_key], $this->session->data['currency'], $this->config->get('config_currency'));
				$bundle_price = round($converted_price, 2);
				if (isset($renamedOImage[$child_key])) {
					$image = PROMOIMAGEFOLDER . $this->db->escape(html_entity_decode($renamedOImage[$child_key], ENT_QUOTES, 'UTF-8'));
				} elseif ($data['bundle_photo'][$child_key]) {
					$image = $data['bundle_photo'][$child_key];
				} else {
					$image = '';
				}

				$product_option_value = html_entity_decode($data['bundle_option'][$child_key]);
				$product_option_name = $data['bundle_option_name'][$child_key];
				$parent_options = html_entity_decode($data['parent_option']);

				if (!$product_option_value) {
					$product_option_value = json_encode(array());
				}
				if (!$parent_options) {
					$parent_options = json_encode(array());
				}

				$parent_product = $this->getProductName($data['parent_id'], true);

				$this->db->query("INSERT INTO `" . DB_PREFIX . "crosssell_related` SET crosssell_id = '" . (int)$crosssell_id . "', parent_id = '" . (int)$data['parent_id'] . "', child_id = '" . $child ."', bundle_price = '" . $bundle_price . "', vendor_price = '" . $data['price_child'][$child_key] . "',`options` = '" . $this->db->escape($product_option_value) . "',`option_name` = '" . $this->db->escape($product_option_name) . "',`parent_options_name` = '" . $this->db->escape($data['parent_option_name']) . "',`parent_options` = '" . $this->db->escape($parent_options) . "', parent_name = '" . $this->db->escape(isset($parent_product['name']) ? html_entity_decode($parent_product['name'], ENT_QUOTES, 'UTF-8') : $data['parent_products']) . "', child_name = '" . $this->db->escape(html_entity_decode($data['name_child'][$child_key], ENT_QUOTES, 'UTF-8')) . "', image = '". $image ."'");
			}
		}
	}

/**
 * Edits a cross sell with the cross sell ID
 * @param  array $data         contains the details of cross sell to be added
 * @param  integer $crosssell_id contains the cross sell ID
 * @return null               none
 */
	public function editCrosssell($data, $crosssell_id) {
		$renamedOImage = array();
		$files = $this->request->files;

		if (isset($files['image']['name']) && $files['image']['name']) {
			foreach ($files['image']['name'] as $index => $image) {
				if ($image) {
					$renamedImg = rand(100000, 999999) . basename(preg_replace('~[^\w\./\\\\]+~', '', $image));
					/**
					 * upload cross sell bundle product images
					 */
					move_uploaded_file($files['image']['tmp_name'][$index], DIR_IMAGE . PROMOIMAGEFOLDER . $renamedImg);
				} else {
					$renamedImg = '';
				}

				$renamedOImage[$index] = $renamedImg;
			}
		}

		if ($data['product_child'])
			$child_products = implode(',', $data['product_child']);
		else
			$child_products = '';

		if (isset($data['countdown_status'])) {
			$countdown_status = (int)$data['countdown_status'];
		} else {
			$countdown_status = 0;
		}

		if (isset($data['quantity'])) {
			$quantity = (int)$data['quantity'];
		} else {
			$quantity = 0;
		}

		if (isset($data['quantity_status']) && $data['quantity_status']) {
			$quantity_status = (int)$data['quantity_status'];
		} else {
			$quantity_status = 0;
			$quantity = 0;
		}

		if (isset($_COOKIE['time_diff'])) {
			$time_diff = $_COOKIE['time_diff'] * 3600;
		}

		$date_start = strtotime($data['date_start']);
		$date_end = strtotime($data['date_end']);

		if (isset($time_diff) && $time_diff) {
			$date_start = $date_start - $time_diff;
			$date_end = $date_end - $time_diff;
		}

		$start_date = date('Y-m-d H:i:s', $date_start);
		$end_date = date('Y-m-d H:i:s', $date_end);

		$this->db->query("UPDATE `" . DB_PREFIX . "vendor_crosssell` SET `countdown_status` = '" . (int)$countdown_status . "', `date_start` = '" . $start_date . "', `date_end` = '" . $end_date . "', `quantity_status` = '" . (int)$quantity_status . "', `quantity` = '" . (int)$quantity . "', `parent_product` = '" . (int)$data['parent_id'] . "', `child_products` = '" . $child_products . "', `date_added` = NOW() WHERE crosssell_id = '" . (int)$crosssell_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "crosssell_related WHERE crosssell_id = '" . (int)$crosssell_id . "'");

		if ($data['product_child']) {
			foreach ($data['product_child'] as $child_key => $child) {
				$converted_price = $this->currency->convert($data['price_child'][$child_key], $this->session->data['currency'], $this->config->get('config_currency'));
				$bundle_price = round($converted_price, 2);
				if (isset($renamedOImage[$child_key]) && $renamedOImage[$child_key]) {
					$image = PROMOIMAGEFOLDER . $this->db->escape(html_entity_decode($renamedOImage[$child_key], ENT_QUOTES, 'UTF-8'));
				} elseif (isset($data['bundle_photo'][$child_key]) && $data['bundle_photo'][$child_key]) {
					$image = $data['bundle_photo'][$child_key];
				} else {
					$image = '';
				}
				$product_option_value = html_entity_decode($data['bundle_option'][$child_key]);
				$product_option_name = $data['bundle_option_name'][$child_key];
				$parent_options = html_entity_decode($data['parent_option']);

				if (!$product_option_value) {
					$product_option_value = json_encode(array());
				}
				if (!$parent_options) {
					$parent_options = json_encode(array());
				}

				$parent_product = $this->getProductName($data['parent_id'], true);

				$sql = "INSERT INTO `" . DB_PREFIX . "crosssell_related` SET crosssell_id = '" . (int)$crosssell_id . "', parent_id = '" . (int)$data['parent_id'] . "', child_id = '" . (int)$child ."', bundle_price = '" . $bundle_price . "', vendor_price = '" . $data['price_child'][$child_key] . "',`options` = '" . $this->db->escape($product_option_value) . "',`option_name` = '" . $this->db->escape($product_option_name) . "',`parent_options_name` = '" . $this->db->escape($data['parent_option_name']) . "',`parent_options` = '" . $this->db->escape($parent_options) . "', parent_name = '" . $this->db->escape(isset($parent_product['name']) ? html_entity_decode($parent_product['name'], ENT_QUOTES, 'UTF-8') : $data['parent_products']) . "', child_name = '" . $this->db->escape(html_entity_decode($data['name_child'][$child_key], ENT_QUOTES, 'UTF-8')) . "'";

				if ($image) {
					$sql .= ", image = '" . $this->db->escape(html_entity_decode($image, ENT_QUOTES, 'UTF-8')) . "'";
				} elseif (isset($data['image'][$child_key]) && $data['image'][$child_key]) {
					$sql .= ", image = '" . $this->db->escape(html_entity_decode($data['image'][$child_key], ENT_QUOTES, 'UTF-8')) . "'";
				} else {
					$sql .= ", image = ''";
				}
				$this->db->query($sql);
			}
		}
	}

/**
 * Fetches the cross sell details of a vendor
 * @param  array  $filter contains filter data
 * @return array         returns the rows of cross sell data
 */
	public function getAllCrosssell($filter = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_crosssell WHERE `vendor_id` = '" . $this->customer->getId() . "'";

		return $this->db->query($sql)->rows;
	}

/**
 * Fetches the number of cross sell of particular vendor
 * @param  array  $filter contains the filter data
 * @return integer         returns the number of cross sells of particular seller
 */
	public function getAllCrosssellTotal($filter = array()) {
		$sql = "SELECT count(*) as total FROM " . DB_PREFIX . "vendor_crosssell WHERE `vendor_id` = '" . $this->customer->getId() . "'";

		return $this->db->query($sql)->row['total'];
	}

/**
 * fetches the details of a cross by its cross sell ID
 * @param  integer $crosssell_id contains the cross sell ID
 * @return array               returns the cross sell data
 */
	public function getCrosssell($crosssell_id) {
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_crosssell WHERE `vendor_id` = '" . $this->customer->getId() . "' AND crosssell_id = '" . (int)$crosssell_id . "'";

		return $this->db->query($sql)->row;
	}

/**
 * Deletes a cross sell as per its cross sell ID
 * @param  integer $crosssell_id contains cross sell id
 * @return null               none
 */
	public function deleteCrosssell($crosssell_id) {
		$checkCrosssell = $this->db->query("SELECT * FROM " . DB_PREFIX . "vendor_crosssell WHERE crosssell_id = '" . (int)$crosssell_id . "' AND vendor_id = '" . $this->customer->getId() . "'");
		if ($checkCrosssell->num_rows) {
			$this->db->query("DELETE FROM " . DB_PREFIX . "vendor_crosssell WHERE crosssell_id = '" . (int)$crosssell_id . "'");
			$this->db->query("DELETE FROM " . DB_PREFIX . "crosssell_related WHERE crosssell_id = '" . (int)$crosssell_id . "'");
		}
	}

/**
 * Adds an gift teaser to a parent product
 * @param array $data contain gift teaser details
 */
	public function addGiftteasor($data) {
		$renamedOImage = array();
		$files = $this->request->files;

		if (isset($files['image']['name']) && $files['image']['name']) {
			foreach ($files['image']['name'] as $index => $image) {
				if ($image) {
					$renamedImg = rand(100000, 999999) . basename(preg_replace('~[^\w\./\\\\]+~', '', $image));
					move_uploaded_file($files['image']['tmp_name'][$index], DIR_IMAGE . PROMOIMAGEFOLDER . $renamedImg);
				} else {
					$renamedImg = '';
				}

				$renamedOImage[$index] = $renamedImg;
			}
		}

		if ($data['product_child']) {
			$child_products = implode(',', $data['product_child']);
		} else {
			$child_products = '';
		}

		if ($data['product_parent']) {
			$parent_products = implode(',', $data['product_parent']);
		} else {
			$parent_products = '';
		}

		if (isset($data['countdown_status'])) {
			$countdown_status = (int)$data['countdown_status'];
		} else {
			$countdown_status = 0;
		}

		if (isset($data['quantity'])) {
			$quantity = (int)$data['quantity'];
		} else {
			$quantity = 0;
		}

		if (isset($data['quantity_status']) && $data['quantity_status']) {
			$quantity_status = (int)$data['quantity_status'];
		} else {
			$quantity_status = 0;
			$quantity = 0;
		}

		if (isset($_COOKIE['time_diff'])) {
			$time_diff = $_COOKIE['time_diff'] * 3600;
		}

		$date_start = strtotime($data['date_start']);
		$date_end = strtotime($data['date_end']);

		if (isset($time_diff) && $time_diff) {
			$date_start = $date_start - $time_diff;
			$date_end = $date_end - $time_diff;
		}

		$start_date = date('Y-m-d H:i:s', $date_start);
		$end_date = date('Y-m-d H:i:s', $date_end);

		$this->db->query("INSERT INTO `" . DB_PREFIX . "vendor_giftteasor` SET `vendor_id` = '" . (int)$this->customer->getId() . "', `countdown_status` = '" . (int)$countdown_status . "', `date_start` = '" . $start_date . "', `date_end` = '" . $end_date . "', `quantity_status` = '" . (int)$quantity_status . "', `quantity` = '" . (int)$quantity . "', `parent_products` = '" . $parent_products . "', `child_products` = '" . $child_products . "', `date_added` = NOW()");

		$giftteasor_id = $this->db->getLastId();

		if ($data['product_parent']) {
			foreach ($data['product_parent'] as $parent_key => $parent) {
				foreach ($data['product_child'] as $child_key => $child) {
					if ($renamedOImage[$child_key]) {
						$image = PROMOIMAGEFOLDER . $this->db->escape(html_entity_decode($renamedOImage[$child_key], ENT_QUOTES, 'UTF-8'));
					} elseif ($data['photo'][$child_key]) {
						$image = $data['photo'][$child_key];
					} else {
						$image = '';
					}

					$child_option_value = html_entity_decode($data['child_option'][$child_key]);
					$child_option_name = $data['child_option_name'][$child_key];
					$parent_option_value = html_entity_decode($data['parent_option'][$parent_key]);
					$parent_option_name = $data['parent_option_name'][$parent_key];

					if (!$parent_option_value) {
						$parent_option_value = json_encode(array());
					}

					if (!$child_option_value) {
						$child_option_value = json_encode(array());
					}

					$this->db->query("INSERT INTO `" . DB_PREFIX . "giftteasor_related` SET giftteasor_id = '" . (int)$giftteasor_id . "', `parent_id` = '" . $parent . "', `options` = '" . $this->db->escape($child_option_value) . "', `option_name` = '" . $this->db->escape($child_option_name) . "', `parent_options_name` = '" . $this->db->escape($parent_option_name) . "', `parent_options` = '" . $this->db->escape($parent_option_value) . "', child_id = '" . $child . "', image = '" . $image . "'");
				}
			}
		}
	}

/**
 * Edits a pre-existing gift teaser entry
 * @param  array $data      contains gift teaser detail
 * @param  [integer] $giftteasor_id [giftteaser id]
 * @return [null]            [none]
 */
	public function editGiftteasor($data, $giftteasor_id) {
		$renamedOImage = array();
		$files = $this->request->files;

		if (isset($files['image']['name']) && $files['image']['name']) {
			foreach ($files['image']['name'] as $index => $image) {
				if ($image) {
					$renamedImg = rand(100000, 999999) . basename(preg_replace('~[^\w\./\\\\]+~', '', $image));
					move_uploaded_file($files['image']['tmp_name'][$index], DIR_IMAGE . PROMOIMAGEFOLDER . $renamedImg);
				} else {
					$renamedImg = '';
				}

				$renamedOImage[$index] = $renamedImg;
			}
		}

		if ($data['product_child']) {
			$child_products = implode(',', $data['product_child']);
		} else {
			$child_products = '';
		}

		if ($data['product_parent']) {
			$parent_products = implode(',', $data['product_parent']);
		} else {
			$parent_products = '';
		}

		if (isset($data['countdown_status'])) {
			$countdown_status = (int)$data['countdown_status'];
		} else {
			$countdown_status = 0;
		}

		if (isset($data['quantity'])) {
			$quantity = (int)$data['quantity'];
		} else {
			$quantity = 0;
		}

		if (isset($data['quantity_status']) && $data['quantity_status']) {
			$quantity_status = (int)$data['quantity_status'];
		} else {
			$quantity_status = 0;
			$quantity = 0;
		}

		if (isset($_COOKIE['time_diff'])) {
			$time_diff = $_COOKIE['time_diff'] * 3600;
		}

		$date_start = strtotime($data['date_start']);
		$date_end = strtotime($data['date_end']);

		if (isset($time_diff) && $time_diff) {
			$date_start = $date_start - $time_diff;
			$date_end = $date_end - $time_diff;
		}

		$start_date = date('Y-m-d H:i:s', $date_start);
		$end_date = date('Y-m-d H:i:s', $date_end);

		$this->db->query("UPDATE `" . DB_PREFIX . "vendor_giftteasor` SET `countdown_status` = '" . (int)$countdown_status . "', `date_start` = '" . $start_date . "', `date_end` = '" . $end_date . "', `quantity_status` = '" . (int)$quantity_status . "', `quantity` = '" . (int)$quantity . "', `parent_products` = '" . $parent_products . "', `child_products` = '" . $child_products . "', `date_added` = NOW() WHERE giftteasor_id = '" . (int)$giftteasor_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "giftteasor_related WHERE giftteasor_id = '" . (int)$giftteasor_id . "'");

		if ($data['product_parent'])
			foreach ($data['product_parent'] as $parent_key => $parent) {
				foreach ($data['product_child'] as $child_key => $child) {
					if (isset($renamedOImage[$child_key]) && $renamedOImage[$child_key]) {
						$image = PROMOIMAGEFOLDER . $this->db->escape(html_entity_decode($renamedOImage[$child_key], ENT_QUOTES, 'UTF-8'));
					} elseif (isset($data['photo'][$child_key]) && $data['photo'][$child_key]) {
						$image = $data['photo'][$child_key];
					} else {
						$image = '';
					}

					$child_option_value = html_entity_decode($data['child_option'][$child_key]);
					$child_option_name = $data['child_option_name'][$child_key];
					$parent_option_value = html_entity_decode($data['parent_option'][$parent_key]);
					$parent_option_name = $data['parent_option_name'][$parent_key];

					if (!$parent_option_value) {
						$parent_option_value = json_encode(array());
					}

					if (!$child_option_value) {
						$child_option_value = json_encode(array());
					}

					$sql = "INSERT INTO `" . DB_PREFIX . "giftteasor_related` SET giftteasor_id = '" . (int)$giftteasor_id . "', parent_id = '" . $parent . "', `options` = '" . $this->db->escape($child_option_value) . "', `option_name` = '" . $this->db->escape($child_option_name) . "', `parent_options_name` = '" . $this->db->escape($parent_option_name) . "', `parent_options` = '" . $this->db->escape($parent_option_value) . "', child_id = '" . $child . "'";

					if ($image) {
						$sql .= ", image = '" . $image . "'";
					} elseif (isset($data['image'][$child_key]) && $data['image'][$child_key]) {
						$sql .= ", image = '" . $data['image'][$child_key] . "'";
					} else {
						$sql .= ", image = ''";
					}

					$this->db->query($sql);
				}
			}
	}

/**
 * Fetches all gift teasers of a particular vendor
 * @param  array  $filter contains filter details
 * @return array         contains gift teaser rows
 */
	public function getAllGiftteasor($data = array()) {
		if(!empty($data['filter_child_product'])){
			$sub_sql = " gr.child_id = pd.product_id";
		}else{
			$sub_sql = " gr.parent_id = pd.product_id";
		}

		$sql = "SELECT DISTINCT gr.parent_id, gr.*, vg.*, pd.*, p.price FROM " . DB_PREFIX . "giftteasor_related gr LEFT JOIN ".DB_PREFIX."vendor_giftteasor vg ON (gr.giftteasor_id = vg.giftteasor_id) LEFT JOIN " . DB_PREFIX . "product p on (gr.parent_id = p.product_id) LEFT JOIN ".DB_PREFIX."product_description pd ON(".$sub_sql.") WHERE `vendor_id` = '" . $this->customer->getId() . "' AND pd.language_id = '".(int)$this->config->get('config_language_id')."' ";

		if(!empty($data['filter_parent_product'])){
		  $sql .= " AND pd.name LIKE '%".$this->db->escape($data['filter_parent_product'])."%'";
		}

		if(!empty($data['filter_child_product'])){
			$sql .= " AND pd.name LIKE '%".$this->db->escape($data['filter_child_product'])."%'";
		}

		if(!empty($data['filter_date_from'])){
		  $date_start  	= strtotime($data['filter_date_from']);

			if (isset($_COOKIE['time_diff'])) {
				$time_diff = $_COOKIE['time_diff'] * 3600;
				$date_start = $date_start - $time_diff;
			}
		  $start_date 	= date('Y-m-d H:i:s', $date_start);
		  $sql .= " AND vg.date_start >= '" . $this->db->escape($start_date) . "' ";
		}

		if(!empty($data['filter_date_to'])){
		  $date_end 	= strtotime($data['filter_date_to']);

			if (isset($_COOKIE['time_diff'])) {
				$time_diff = $_COOKIE['time_diff'] * 3600;
				$date_end = $date_end - $time_diff;
			}
		  $end_date   = date('Y-m-d H:i:s', $date_end);
		  $sql .= " AND vg.date_end <= '" . $this->db->escape($end_date) . "' ";
		}

		if(!empty($data['filter_child_product'])){
				$sql .= " GROUP BY gr.child_id ORDER BY vg.giftteasor_id DESC";
		}else{
				$sql .= " GROUP BY gr.parent_id ORDER BY vg.giftteasor_id DESC";
		}

		if (isset($data['filter_start']) || isset($data['filter_limit'])) {
			if (!$data['filter_start']) {
				$data['filter_start'] = 0;
			}

			if ($data['filter_limit'] < 1) {
				$data['filter_limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['filter_start'] . "," . (int)$data['filter_limit'];
		}

		return $this->db->query($sql)->rows;
	}

/**
 * Gets total number of gift teasers
 * @param  array  $filter contains filter data
 * @return interger         contain the count of gift teaser
 */
	public function getAllGiftteasorTotal($filter = array()) {
		if(!empty($data['filter_child_product'])){
			$sub_sql = " gr.child_id = pd.product_id";
		}else{
			$sub_sql = " gr.parent_id = pd.product_id";
		}

		$sql = "SELECT count(DISTINCT gr.parent_id) as total FROM " . DB_PREFIX . "giftteasor_related gr LEFT JOIN ".DB_PREFIX."vendor_giftteasor vg ON (gr.giftteasor_id = vg.giftteasor_id) LEFT JOIN " . DB_PREFIX . "product p on (gr.parent_id = p.product_id) LEFT JOIN ".DB_PREFIX."product_description pd ON(".$sub_sql.") WHERE `vendor_id` = '" . $this->customer->getId() . "' AND pd.language_id = '".(int)$this->config->get('config_language_id')."' ";

		if(!empty($data['filter_parent_product'])){
			$sql .= " AND pd.name LIKE '%".$this->db->escape($data['filter_parent_product'])."%'";
		}

		if(!empty($data['filter_child_product'])){
			$sql .= " AND pd.name LIKE '%".$this->db->escape($data['filter_child_product'])."%'";
		}

		if(!empty($data['filter_date_from'])){
			$date_start  	= strtotime($data['filter_date_from']);

			if (isset($_COOKIE['time_diff'])) {
				$time_diff = $_COOKIE['time_diff'] * 3600;
				$date_start = $date_start - $time_diff;
			}
			$start_date 	= date('Y-m-d H:i:s', $date_start);
			$sql .= " AND vg.date_start >= '" . $this->db->escape($start_date) . "' ";
		}

		if(!empty($data['filter_date_to'])){
			$date_end 	= strtotime($data['filter_date_to']);

			if (isset($_COOKIE['time_diff'])) {
				$time_diff = $_COOKIE['time_diff'] * 3600;
				$date_end = $date_end - $time_diff;
			}
			$end_date   = date('Y-m-d H:i:s', $date_end);
			$sql .= " AND vg.date_end <= '" . $this->db->escape($end_date) . "' ";
		}

		if(!empty($data['filter_child_product'])){
				$sql .= " ORDER BY vg.giftteasor_id DESC";
		}else{
				$sql .= " ORDER BY vg.giftteasor_id DESC";
		}

		return $this->db->query($sql)->row['total'];
	}

/**
 * fetches a particular gift teaser with gift teaser id
 * @param  integer $giftteasor_id contains gift teaser id
 * @return array            returns the details of an gift teaser entry
 */
	public function getGiftteasor($giftteasor_id) {
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_giftteasor WHERE `vendor_id` = '" . $this->customer->getId() . "' AND giftteasor_id = '" . (int)$giftteasor_id . "'";

		return $this->db->query($sql)->row;
	}

/**
 * Deletes the gift teaser with its gift teaser ID
 * @param  integer $giftteasor_id contains gift teaser ID
 * @return null            none
 */
	public function deleteGiftteasor($giftteasor_id) {
		$checkGift = $this->db->query("SELECT * FROM " . DB_PREFIX . "vendor_giftteasor WHERE giftteasor_id = '" . (int)$giftteasor_id . "' AND vendor_id = '" . $this->customer->getId() . "'");
		if ($checkGift->num_rows) {
			$this->db->query("DELETE FROM " . DB_PREFIX . "vendor_giftteasor WHERE giftteasor_id = '" . (int)$giftteasor_id . "'");
			$this->db->query("DELETE FROM " . DB_PREFIX . "giftteasor_related WHERE giftteasor_id = '" . (int)$giftteasor_id . "'");
		}
	}

/**
 * Fetches the product's name and quanity as per its product id
 * @param  integer $product_id product's ID
 * @return string             returns the name of the product
 */
	public function getProductName($product_id, $quantity_status = 0) {
		$product = $this->db->query("SELECT pd.name, p.quantity FROM " . DB_PREFIX ."product_description pd LEFT JOIN " . DB_PREFIX . "product p ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "customerpartner_to_product c2p ON (p.product_id = c2p.product_id) WHERE p.product_id = '" . (int)$product_id ."' AND pd.language_id = '" . $this->config->get('config_language_id') . "' AND c2p.customer_id = '" . $this->customer->getId() . "'")->row;

		if ($quantity_status) {
			return array('name' => $product['name'], 'quantity' => $product['quantity']);
		} else {
			if (isset($product['name'])) {
				return $product['name'];
			} else {
				return false;
			}
		}
	}

/**
 * get cross sell products details based on its parent's ID, cross sell's ID and child's ID
 * @param  integer $parent_id    parent's ID
 * @param  integer $crosssell_id cross sell's ID
 * @param  interger $child_id     child's ID
 * @return array               returns the cross sell details
 */
	public function getProductCrosssell($parent_id, $crosssell_id, $child_id = 0, $return_rows = false) {
		$sub_sql = " (cr.parent_id = p.product_id)";
		if ($child_id) {
				$sub_sql = " (cr.child_id = p.product_id)";
		}

		$sql = "SELECT cr.*, p.price FROM " . DB_PREFIX ."crosssell_related cr LEFT JOIN ".DB_PREFIX."product p ON ".$sub_sql." WHERE cr.parent_id = '" . (int)$parent_id . "' AND cr.crosssell_id = '" . (int)$crosssell_id . "'";

		if ($child_id) {
				$sql .= " AND cr.child_id = '" . $child_id . "'";
		}

		if ($return_rows) {
			$crosssell = $this->db->query($sql)->rows;
		} else {
			$crosssell = $this->db->query($sql)->row;
		}

		return $crosssell;
	}

/**
 * fetches up sell parent and child product
 * @param  integer $upsell_id upsell's ID
 * @param  integer $child_id  child product's ID
 * @return array            returns the row data for particular up sell and child ID
 */
	public function getProductUpsell($upsell_id, $child_id) {
		$sql = "SELECT * FROM " . DB_PREFIX . "upsell_related WHERE child_id = '" . (int)$child_id . "' AND upsell_id = '" . (int)$upsell_id . "'";

		$upsell = $this->db->query($sql)->row;

		return $upsell;
	}

/**
 * fetches the gift teaser parent and child product
 * @param  integer $giftteasor_id gift teaser ID
 * @param  integer $child_id      child's ID
 * @return array                returns the gift teaser details
 */
	public function getProductGiftteasor($giftteasor_id, $child_id, $return_rows = false) {
		$sql = "SELECT * FROM " . DB_PREFIX ."giftteasor_related WHERE child_id = '" . (int)$child_id ."' AND giftteasor_id = '" . (int)$giftteasor_id . "'";

		if ($return_rows):
			$giftteasor = $this->db->query($sql)->rows;
	  else:
			$giftteasor = $this->db->query($sql)->row;
		endif;

		return $giftteasor;
	}

	public function getGiftRelated($giftteasor_id, $parent = 0) {
		$sql = "SELECT DISTINCT ";

		if ($parent) {
			$sql .= "parent_id, parent_options, parent_options_name";
		} else {
			$sql .= "child_id, options, option_name, image";
		}

		$sql .= " FROM " . DB_PREFIX . "giftteasor_related WHERE giftteasor_id = '" . (int)$giftteasor_id . "'";

		return $this->db->query($sql)->rows;
	}

/**
 * fetches up sell products from a given product ID to be shown on the product page (widgets)
 * @param  integer $product_id product's ID
 * @return array             returns the data of upsell products for a parent product
 */
	function getUpsells($product_id) {
		$product_data = array();

		$now = date('Y-m-d H:i:s');

		if ($this->config->get('wk_widget_display_type')) {
			$order = ' ORDER BY vu.date_added DESC';
		} else {
			$order = '';
		}

		if ($this->config->get('wk_widget_upselling_widget')) {
			$limit = ' LIMIT 0,' . $this->config->get('wk_widget_upselling_widget');
		} else {
			$limit = '';
		}

		/**
		 * product_array will contain the product ids of child products to be shown on the parent product page
		 * @var array
		 */
		$product_array = array();

		// showing products as per the manage deployment
		if ($this->config->get('wk_udeploy')) {
			if ($this->config->get('wk_udeploy') == 2) { // Offers associated in the same category
				$category = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'")->rows;

				$products = array();

				foreach ($category as $cat) {
					$cat_products = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE category_id = '" . (int)$cat['category_id'] . "'")->rows;

					foreach ($cat_products as $cat_product) {
						$products[] = $cat_product['product_id'];
					}
				}

				$product_array = array_unique($products);
			} elseif ($this->config->get('wk_udeploy') == 3) { // Offers not associated neither the same category nor the product
				$category = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'")->rows;

				$cats = array();

				foreach ($category as $cat) {
					array_push($cats, $cat['category_id']);
				}

				$cats = array_unique($cats);

				$categories = implode("','", $cats);

				$products = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE category_id NOT IN ('" . $categories . "')")->rows;

				$pros = array();

				foreach ($products as $pro) {
					array_push($pros, $pro['product_id']);
				}

				$product_array = array_unique($pros);
			} elseif ($this->config->get('wk_udeploy') == 4) { // Offers in this categories
				$origin 		= $this->config->get('wk_udeploy_origin');
				$destiny 		= $this->config->get('wk_udeploy_destiny');

				if ($origin == 1) { // Origin products
					$origin_products 	= $this->config->get('wk_udeploy_origin_product');
					$product_array 		= explode(',', $origin_products);

				} elseif ($origin == 2) { // Origin categories
					$origin_categorys 		= $this->config->get('wk_udeploy_origin_category');
					$origin_subcategorys 	= $this->config->get('wk_udeploy_origin_subcategory');
					$origin_categorys 		= $origin_categorys . ',' . $origin_subcategorys;
					$category_array 			= explode(',', $origin_categorys);

					$products = array();

					foreach ($category_array as $cat) {
							$cat_products = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE category_id = '" . (int)$cat . "'")->rows;

							foreach ($cat_products as $cat_product) {
								$products[] = $cat_product['product_id'];
							}
					}

					$product_array = array_unique($products);
				}

				if ($destiny == 1) { // Destiny products
					$destiny_products = $this->config->get('wk_udeploy_destiny_product');
					$products 				= explode(',', $destiny_products);
					$product 					= in_array($product_id, $products);
					if (!$product) {
						return;
					}
				} elseif ($destiny == 2) { // Destiny categories
					$destiny_categorys 			= $this->config->get('wk_udeploy_destiny_category');
					$destiny_subcategorys 	= $this->config->get('wk_udeploy_destiny_subcategory');
					$destiny_categorys 			= $destiny_categorys . ',' . $destiny_subcategorys;
					$category_array 				= explode(',', $destiny_categorys);

					$products = array();

					foreach ($category_array as $cat) {
						$cat_products = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE category_id = '" . $cat . "'")->rows;

						foreach ($cat_products as $cat_product) {
							$products[] = $cat_product['product_id'];
						}
					}

					$product = in_array($product_id, $products);
					if (!$product) {
						return;
					}
				}
			} else {
				$product_array[] = $product_id;
			}
		} else {
			$product_array[] = $product_id;
		}

		/**
		 * will contain the unique child products
		 * @var array
		 */
		$return_array = array();

		foreach ($product_array as $product_id) {
			if (count($return_array) > 20) {
				break;
			}

			$product_seller = $this->db->query("SELECT customer_id FROM " . DB_PREFIX . "customerpartner_to_product WHERE product_id = '" . (int)$product_id . "'")->row;

			if (isset($product_seller['customer_id']) && $product_seller['customer_id']) {
				$seller_id = $product_seller['customer_id'];
			} else {
				$seller_id = 0;
			}

			// fetching details as per the product ids in the $product_array array()
			$query = $this->db->query("SELECT DISTINCT ur.id, ur.options, ur.option_name, ur.image, p.price, p.tax_class_id, p.product_id, pd.name, pd.description, p.minimum, vu.countdown_status, vu.quantity_status, vu.quantity, vu.date_end, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special FROM " . DB_PREFIX . "upsell_related ur LEFT JOIN " . DB_PREFIX . "product p ON (ur.child_id = p.product_id) LEFT JOIN " . DB_PREFIX . "customerpartner_to_product c2p ON (p.product_id = c2p.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (pd.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "vendor_upsell vu ON (vu.upsell_id = ur.upsell_id) WHERE p.quantity >= vu.quantity AND p.quantity > 0 AND c2p.customer_id = '" . (int)$seller_id . "' AND ur.parent_id = '" . (int)$product_id . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND vu.quantity > 0 AND vu.date_start < '" . $now . "' AND vu.date_end > '" . $now . "' " . $order . $limit)->rows;

			foreach ($query as $entry) {
				$return_array[$entry['id']] = $entry;
			}
		}

		// foreach ($return_array as $return_value_key => $return_value) {
		//
		// 	$eliminate = false;
		//
		// 	if($return_value['quantity_status'] && $return_value['quantity'] <= 0) {
		// 	  $eliminate = true;
		// 	}
		//
		// 	foreach (json_decode($return_value['options'], true) as $child_key => $child_value) {
		// 		$child_status = $this->wallet->checkSubProductStatus($return_value['product_id'], $child_key, $child_value);
		// 		if(!$child_status)
		// 			$eliminate = true;
		// 	}
		//
		// 	if($eliminate) {
		// 		unset($return_array[$return_value_key]);
		// 	}
		// }

		return $return_array;
	}

/**
 * Fetches the cross sell products to be shown on the product page of parent
 * @param  integer $product_id product's ID
 * @return array             returns the bundles to be visible on the parent product page
 */
	function getCrosssells($product_id) {
		$product_data = array();

		$now = date('Y-m-d H:i:s');

		if ($this->config->get('wk_widget_display_type')) {
			$order = ' ORDER BY vc.date_added DESC';
		} else {
			$order = '';
		}

		if ($this->config->get('wk_cwidget_crossselling_widget')) {
			$limit = ' LIMIT 0,'.$this->config->get('wk_cwidget_crossselling_widget');
		} else {
			$limit = '';
		}

		$product_array = array();

		$product_array[] = $product_id;

			// $category = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'")->rows;

			// $products = array();

			// foreach ($category as $cat) {
			// 	$cat_products = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE category_id = '" . (int)$cat['category_id'] . "'")->rows;

			// 	foreach ($cat_products as $cat_product) {
			// 		$products[] = $cat_product['product_id'];
			// 	}
			// }

			// $product_array = array_unique($products);


		$return_array = array();

		foreach ($product_array as $product_id) {
			if (count($return_array) > 20) {
				break;
			}

			$product_seller = $this->db->query("SELECT customer_id FROM " . DB_PREFIX . "customerpartner_to_product WHERE product_id = '" . (int)$product_id . "'")->row;

			if (isset($product_seller['customer_id']) && $product_seller['customer_id']) {
				$seller_id = $product_seller['customer_id'];
			} else {
				$seller_id = 0;
			}

			$now = date('Y-m-d H:i:s');

			$query = $this->db->query("SELECT DISTINCT cr.id, cr.options, cr.option_name, cr.parent_options, cr.parent_options_name, cr.image, p.price, p.tax_class_id, p.product_id, pd.name, pd.description, p.minimum, vc.countdown_status, vc.quantity_status, vc.quantity, vc.date_end, cr.bundle_price, po.product_option_id as `option`, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating FROM " . DB_PREFIX . "crosssell_related cr LEFT JOIN " . DB_PREFIX . "product p ON (cr.child_id = p.product_id) LEFT JOIN " . DB_PREFIX . "customerpartner_to_product c2p ON (p.product_id = c2p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "vendor_crosssell vc ON (vc.crosssell_id = cr.crosssell_id) LEFT JOIN " . DB_PREFIX . "product_option po ON (po.product_id = p.product_id) WHERE p.quantity >= vc.quantity AND p.quantity > 0 AND c2p.customer_id = '" . (int)$seller_id . "' AND cr.parent_id = '" . (int)$product_id . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND pd.language_id = '" . $this->config->get('config_language_id') . "' AND vc.quantity > 0 AND vc.date_start < '" . $now . "' AND vc.date_end > '" . $now . "' GROUP BY cr.id " . $order . $limit)->rows;

			foreach ($query as $entry) {
				$return_array[$entry['id']] = $entry;
			}
		}

		// foreach ($return_array as $return_value_key => $return_value) {
		//
		// 	$eliminate = false;
		//
		// 	if($return_value['quantity_status'] && $return_value['quantity'] <= 0) {
		// 		$eliminate = true;
		// 	}
		//
		// 	foreach (json_decode($return_value['parent_options']) as $parent_key => $parent_value) {
		// 		$parent_status = $this->wallet->checkSubProductStatus($product_id, $parent_key, $parent_value);
		//
		// 		if(!$parent_status)
		// 			$eliminate = true;
		// 	}
		//
		// 	foreach (json_decode($return_value['options'], true) as $child_key => $child_value) {
		// 		$child_status = $this->wallet->checkSubProductStatus($return_value['product_id'], $child_key, $child_value);
		// 		if(!$child_status)
		// 			$eliminate = true;
		// 	}
		//
		// 	if($eliminate) {
		// 		unset($return_array[$return_value_key]);
		// 	}
		// }

		return $return_array;
	}

/**
 * Fetches all the Up sell products to be visible on the listing page
 * @return array returns the up sell products to be visible on the up sell listing page
 */
	public function getUpsellProducts()	{
		$now = date('Y-m-d H:i:s');

		if ($this->config->get('wk_widget_display_type')) {
			$order = ' ORDER BY vu.date_added DESC';
		} else {
			$order = '';
		}

		$query = $this->db->query("SELECT DISTINCT ur.id, ur.options, ur.option_name, ur.image, c2p.customer_id, p.price, p.tax_class_id, p.product_id, pd.name, pd.description, p.minimum, vu.countdown_status, vu.quantity_status, vu.quantity, vu.date_end, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special FROM " . DB_PREFIX . "upsell_related ur LEFT JOIN " . DB_PREFIX . "product p ON (ur.child_id = p.product_id) LEFT JOIN " . DB_PREFIX . "customerpartner_to_product c2p ON (p.product_id = c2p.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (pd.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "vendor_upsell vu ON (vu.upsell_id = ur.upsell_id) WHERE p.quantity >= vu.quantity AND p.quantity > 0 AND pd.language_id = '" . $this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND vu.quantity > 0 AND vu.date_start < '" . $now . "' AND vu.date_end > '" . $now . "'" . $order . " LIMIT 0, 50")->rows;

		return $query;
	}

/**
 * Fetches all the cross sell products to be visible on the listing page
 * @return array returns the cross sell products to be visible on the cross sell listing page
 */
	public function getCrosssellProducts() {
		$now = date('Y-m-d H:i:s');

		if ($this->config->get('wk_cwidget_display_type')) {
			$order = ' ORDER BY vc.date_added DESC';
		} else {
			$order = '';
		}

		$query = $this->db->query("SELECT DISTINCT cr.id, cr.options, cr.option_name, cr.parent_options, cr.parent_options_name,cr.image, c2p.customer_id, p.price, p.tax_class_id, p.product_id, pd.name, pd.description, p.minimum, vc.countdown_status, vc.quantity_status, vc.quantity, vc.date_end, cr.parent_id, cr.bundle_price, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special FROM " . DB_PREFIX . "crosssell_related cr LEFT JOIN " . DB_PREFIX . "product p ON (cr.child_id = p.product_id) LEFT JOIN " . DB_PREFIX . "customerpartner_to_product c2p ON (p.product_id = c2p.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (pd.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "vendor_crosssell vc ON (vc.crosssell_id = cr.crosssell_id) LEFT JOIN " . DB_PREFIX . "product_option po ON (po.product_id = p.product_id) WHERE p.quantity >= vc.quantity AND p.quantity > 0 AND pd.language_id = '" . $this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' " . $order . " LIMIT 0, 50")->rows;
		// AND vc.date_start < '" . $now . "' AND vc.date_end > '" . $now . "'" . $order . " LIMIT 0, 50")->rows;

		return $query;
	}

/**
 * Fetches seller's id by product's ID
 * @param  integer $product_id contains the product ID
 * @return [integer/boolean]             returns customer ID if exists otherwise false
 */
	public function getSellerByProduct($product_id) {
		$sql = "SELECT customer_id FROM " . DB_PREFIX . "customerpartner_to_product WHERE product_id = '" . $product_id . "'";
		$query = $this->db->query($sql)->row;

		if (isset($query['customer_id'])) {
			return $query['customer_id'];
		} else {
			return false;
		}
	}
/**
 * tells whether the product contain options
 * @param  integer $product_id  Contains product ID
 * @return boolean             [returns true or false]
 */
	public function hasOption($product_id) {
		$sql = "SELECT product_option_id FROM " . DB_PREFIX . "product_option WHERE product_id = '". $product_id ."'";
		$query = $this->db->query($sql);

		if ($query->row) {
			return true;
		} else {
			return false;
		}
	}
/**
 * checks whether the products exist in any combination of upsell, cross-sell, or gift
 * @param  array $data contains the product ids
 * @return int       returns the number of combinations
 */
	public function checkPromoProduct($data) {

		$total_combination = 0;

		foreach ($data as $product_id) {
			$upsell_rows = $this->db->query("SELECT * FROM " . DB_PREFIX . "upsell_related WHERE parent_id = '" . (int)$product_id . "' OR child_id = '" . (int)$product_id . "'");

			$crosssell_rows = $this->db->query("SELECT * FROM " . DB_PREFIX . "crosssell_related WHERE parent_id = '" . (int)$product_id . "' OR child_id = '" . (int)$product_id . "'");

			$gift_rows = $this->db->query("SELECT * FROM " . DB_PREFIX . "giftteasor_related WHERE parent_id = '" . (int)$product_id . "' OR child_id = '" . (int)$product_id . "'");

			$total_combination = $total_combination + $upsell_rows->num_rows + $crosssell_rows->num_rows + $gift_rows->num_rows;
		}
		return $total_combination;
	}

	public function getSellerProducts($data) {
		$date_now = date('Y-m-d H:i:s');

		$sql = "SELECT p.product_id, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "customerpartner_to_product c2p ON (c2p.product_id = p.product_id) LEFT JOIN ".DB_PREFIX."product_to_store p2s ON (p.product_id = p2s.product_id)";

		if (isset($data['filter_category_id']) AND $data['filter_category_id']) {
			$sql .= " LEFT JOIN " . DB_PREFIX ."product_to_category p2c ON (p.product_id = p2c.product_id)";
		}

		$sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.date_available <= NOW() ";

		if (isset($data['filter_name']) AND !empty($data['filter_name'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
			$sql .= " AND p.quantity = '" . $this->db->escape($data['filter_quantity']) . "'";
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
		}

		if (isset($data['filter_stock'])) {
			$sql .= " AND p.quantity > '0' ";
		}

		if (isset($data['filter_low_stock'])) {
			$sql .= " AND p.quantity <= '" . (int)$this->config->get('marketplace_low_stock_quantity') . "'";
		}

		if (!isset($data['customer_id']) || !$data['customer_id'])
			$sql .= " AND c2p.customer_id = ". $this->customer->getId() ;
		else
			$sql .= " AND c2p.customer_id = ". (int)$data['customer_id'] ;

		$sql .= " GROUP BY p.product_id";

		$sort_data = array(
			'pd.name',
			'p.model',
			'p.price',
			'p.quantity',
			'p.status',
			'p.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY pd.name";
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

		$product_data = array();

		$query = $this->db->query($sql);

		$this->load->model('account/customerpartner');

		foreach ($query->rows as $result) {
			$product_data[$result['product_id']] = $this->model_account_customerpartner->getProduct($result['product_id']);
		}

		return $product_data;
	}
}
