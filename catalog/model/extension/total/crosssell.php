<?php
class ModelExtensionTotalCrosssell extends Model {
	public function getTotal($total) {
  if ($this->config->get('module_marketplace_status') && $this->config->get('module_wk_crosssell_crosssell_status')) {
		$this->load->language('extension/total/crosssell');

		$date_now = date('Y-m-d H:i:s');

		$bundle_price = 0.00;

		$sql = "SELECT cb.related_id, cb.quantity, cr.parent_id, cr.parent_options, cr.bundle_price, cr.child_id, cr.options, vc.quantity_status, vc.quantity as q_allowed FROM " . DB_PREFIX . "customer_bundles cb LEFT JOIN " . DB_PREFIX . "crosssell_related cr ON (cb.related_id = cr.id) LEFT JOIN " . DB_PREFIX . "vendor_crosssell vc ON (cr.crosssell_id = vc.crosssell_id) WHERE (vc.quantity_status = 0 || vc.quantity > 0)";

		if ($this->customer->isLogged()) {
			$sql .= " AND cb.customer_id = '" . $this->customer->getId() . "'";
		} else {
			$sql .= " AND cb.customer_id = '0' AND cb.session_id = '" . $this->db->escape($this->session->getId()) . "'";
		}

		$customer_bundles = $this->db->query($sql)->rows;

		$cart_products = $this->db->query("SELECT * FROM " . DB_PREFIX . "cart WHERE customer_id = '" . $this->customer->getId() . "'")->rows;

		foreach ($customer_bundles as $bundle_key => $bundle) {
			$parent = array();
			$child = array();
			foreach ($cart_products as $cart_key => $product) {

				if ($bundle['quantity'] < $product['quantity']) {
					$product['quantity'] = $bundle['quantity'];
				}

				$quantity = $product['quantity'];

				if (!$parent && $bundle['parent_id'] == $product['product_id'] && $bundle['parent_options'] == $product['option']) {
					if ($child) {
						foreach ($this->cart->getProducts() as $cart) {
							if ($cart['cart_id'] == $product['cart_id']) {
								$price = $cart['actual_price'];
								break;
							}
						}

						if ($product['quantity'] == $child['quantity']) {
							unset($cart_products[$cart_key]);
							unset($cart_products[$child['key']]);
						} elseif ($product['quantity'] > $child['quantity']) {
							unset($cart_products[$child['key']]);
							$cart_products[$cart_key]['quantity'] = $product['quantity'] - $child['quantity'];
							$quantity = $child['quantity'];
						} elseif ($product['quantity'] < $child['quantity']) {
							unset($cart_products[$cart_key]);
							$cart_products[$child['key']]['quantity'] = $child['quantity'] - $product['quantity'];
						}

						if ($bundle['quantity_status'] && $quantity > $bundle['q_allowed']) {
							$bundle_price += ($child['price'] + $price - $bundle['bundle_price']) * $bundle['q_allowed'];
						} else {
							$bundle_price += ($child['price'] + $price - $bundle['bundle_price']) * $quantity;
						}

						unset($customer_bundles[$bundle_key]);
						break;
					} else {
						foreach ($this->cart->getProducts() as $cart) {
							if ($cart['cart_id'] == $product['cart_id']) {
								$parent = array(
									'key'      => $cart_key,
									'quantity' => $product['quantity'],
									'price'    => $cart['actual_price']
								);
								break;
							}
						}
					}
				}
				if (!$child && $bundle['child_id'] == $product['product_id'] && $bundle['options'] == $product['option']) {
					if ($parent) {
						foreach ($this->cart->getProducts() as $cart) {
							if ($cart['cart_id'] == $product['cart_id']) {
								$price = $cart['actual_price'];
								break;
							}
						}

						if ($product['quantity'] == $parent['quantity']) {
							unset($cart_products[$cart_key]);
							unset($cart_products[$parent['key']]);
						} elseif ($product['quantity'] > $parent['quantity']) {
							unset($cart_products[$parent['key']]);
							$cart_products[$cart_key]['quantity'] = $product['quantity'] - $parent['quantity'];
							$quantity = $parent['quantity'];
						} elseif ($product['quantity'] < $parent['quantity']) {
							unset($cart_products[$cart_key]);
							$cart_products[$parent['key']]['quantity'] = $parent['quantity'] - $product['quantity'];
						}

						if ($bundle['quantity_status'] && $quantity > $bundle['q_allowed']) {
							$bundle_price += ($parent['price'] + $price - $bundle['bundle_price']) * $bundle['q_allowed'];
						} else {
							$bundle_price += ($parent['price'] + $price - $bundle['bundle_price']) * $quantity;
						}

						unset($customer_bundles[$bundle_key]);
						break;
					} else {
						foreach ($this->cart->getProducts() as $cart) {
							if ($cart['cart_id'] == $product['cart_id']) {
								$child = array(
									'key'      => $cart_key,
									'quantity' => $product['quantity'],
									'price'    => $cart['actual_price']
								);
								break;
							}
						}
					}
				}
			}
		}

		if (isset($customer_bundles) && is_array($customer_bundles)) {
			foreach ($customer_bundles as $bundle) {
				$sql = "DELETE FROM " . DB_PREFIX . "customer_bundles WHERE related_id = '" . $bundle['related_id'] . "'";

				if ($this->customer->isLogged()) {
					$sql .= " AND customer_id = '" . $this->customer->getId() . "'";
				} else {
					$sql .= " AND customer_id = '0' AND session_id = '" . $this->db->escape($this->session->getId()) . "'";
				}

				$this->db->query($sql);
			}
		}
/**
 * Nothing to return if bundle price is greater than 0
 */
		if ($bundle_price > 0) {
			$total['totals'][] = array(
				'code'       => 'crosssell',
				'title'      => $this->language->get('text_crosssell'),
				'value'      => -$bundle_price,
				'sort_order' => $this->config->get('crosssell_sort_order')
			);

			$total['total'] -= $bundle_price;
		}
	}
	}

	public function subtractStock() {
		$date_now = date('Y-m-d H:i:s');

		$bundle_price = 0.00;

		$sql = "SELECT cb.related_id, cb.quantity, cr.parent_id, cr.parent_options, cr.bundle_price, cr.child_id, cr.options, vc.quantity_status, vc.quantity as q_allowed FROM " . DB_PREFIX . "customer_bundles cb LEFT JOIN " . DB_PREFIX . "crosssell_related cr ON (cb.related_id = cr.id) LEFT JOIN " . DB_PREFIX . "vendor_crosssell vc ON (cr.crosssell_id = vc.crosssell_id) WHERE vc.date_end > '" . $date_now . "' AND (vc.quantity_status = 0 || vc.quantity > 0)";

		if ($this->customer->isLogged()) {
			$sql .= " AND cb.customer_id = '" . $this->customer->getId() . "'";
		} else {
			$sql .= " AND cb.customer_id = '0' AND cb.session_id = '" . $this->db->escape($this->session->getId()) . "'";
		}

		$customer_bundles = $this->db->query($sql)->rows;

		$cart_products = $this->db->query("SELECT * FROM " . DB_PREFIX . "cart WHERE customer_id = '" . $this->customer->getId() . "'")->rows;

		foreach ($customer_bundles as $bundle_key => $bundle) {
			$bundle_quantity = 0;
			$parent = array();
			$child = array();
			foreach ($cart_products as $cart_key => $product) {
				if (isset($this->session->data['gifts']) && in_array($product['cart_id'], $this->session->data['gifts'])) {
					if ($product['quantity'] > $this->session->data['gift_quant'][$product['cart_id']]) {
						$product['quantity'] = $product['quantity'] - $this->session->data['gift_quant'][$product['cart_id']];
					} else {
						break;
					}
				}

				if ($bundle['quantity'] < $product['quantity']) {
					$product['quantity'] = $bundle['quantity'];
				}

				$quantity = $product['quantity'];

				if (!$parent && $bundle['parent_id'] == $product['product_id'] && $bundle['parent_options'] == $product['option']) {
					if ($child) {
						foreach ($this->cart->getProducts() as $cart) {
							if ($cart['cart_id'] == $product['cart_id']) {
								$price = $cart['actual_price'];
								break;
							}
						}

						if ($product['quantity'] == $child['quantity']) {
							unset($cart_products[$cart_key]);
							unset($cart_products[$child['key']]);
						} elseif ($product['quantity'] > $child['quantity']) {
							unset($cart_products[$child['key']]);
							$cart_products[$cart_key]['quantity'] = $product['quantity'] - $child['quantity'];
							$quantity = $child['quantity'];
						} elseif ($product['quantity'] < $child['quantity']) {
							unset($cart_products[$cart_key]);
							$cart_products[$child['key']]['quantity'] = $child['quantity'] - $product['quantity'];
						}

						if ($bundle['quantity_status'] && $quantity > $bundle['q_allowed']) {
							$bundle_quantity = $bundle['q_allowed'];
						} else {
							$bundle_quantity = $quantity;
						}

						unset($customer_bundles[$bundle_key]);
						break;
					} else {
						foreach ($this->cart->getProducts() as $cart) {
							if ($cart['cart_id'] == $product['cart_id']) {
								$parent = array(
									'key'      => $cart_key,
									'quantity' => $product['quantity'],
									'price'    => $cart['actual_price']
								);
								break;
							}
						}
					}
				}
				if (!$child && $bundle['child_id'] == $product['product_id'] && $bundle['options'] == $product['option']) {
					if ($parent) {
						foreach ($this->cart->getProducts() as $cart) {
							if ($cart['cart_id'] == $product['cart_id']) {
								$price = $cart['actual_price'];
								break;
							}
						}

						if ($product['quantity'] == $parent['quantity']) {
							unset($cart_products[$cart_key]);
							unset($cart_products[$parent['key']]);
						} elseif ($product['quantity'] > $parent['quantity']) {
							unset($cart_products[$parent['key']]);
							$cart_products[$cart_key]['quantity'] = $product['quantity'] - $parent['quantity'];
							$quantity = $parent['quantity'];
						} elseif ($product['quantity'] < $parent['quantity']) {
							unset($cart_products[$cart_key]);
							$cart_products[$parent['key']]['quantity'] = $parent['quantity'] - $product['quantity'];
						}

						if ($bundle['quantity_status'] && $quantity > $bundle['q_allowed']) {
							$bundle_quantity = $bundle['q_allowed'];
						} else {
							$bundle_quantity = $quantity;
						}

						unset($customer_bundles[$bundle_key]);
						break;
					} else {
						foreach ($this->cart->getProducts() as $cart) {
							if ($cart['cart_id'] == $product['cart_id']) {
								$child = array(
									'key'      => $cart_key,
									'quantity' => $product['quantity'],
									'price'    => $cart['actual_price']
								);
								break;
							}
						}
					}
				}
			}

			if ($bundle_quantity) {
				$related = $this->db->query("SELECT * FROM " . DB_PREFIX . "crosssell_related WHERE id = '" . (int)$bundle['related_id'] . "'")->row;

				if ($related) {
					$this->db->query("UPDATE " . DB_PREFIX . "vendor_crosssell SET quantity = (quantity - " . $bundle_quantity . ") WHERE crosssell_id = '" . (int)$related['crosssell_id'] . "'");
				}
			}
		}

		// if (isset($this->session->data['order_id'])) {
		// 	$order_id = $this->session->data['order_id'];
		// } else {
		// 	$order_id = 0;
		// }

		// $order_product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

		// $cart_products = array();
		// $sub_products_quant = array();

		// foreach ($order_product_query->rows as $product) {
		// 	for ($quant = 0; $quant < $product['quantity']; $quant++) {
		// 		array_push($cart_products, $product['product_id']);
		// 	}
		// 	if (isset($sub_products_quant[$product['product_id']])) {
		// 		$sub_products_quant[$product['product_id']] += $product['quantity'];
		// 	} else {
		// 		$sub_products_quant[$product['product_id']] = $product['quantity'];
		// 	}
		// }

		// $unique_products = array_unique($cart_products);
		// $cart_implode = implode("','", $unique_products);
		// $date_now = date('Y-m-d H:i:s');

		// if ($unique_products) {
			// up sell products
			// $upsells = $this->db->query("SELECT ur.parent_id, ur.child_id, ur.upsell_id FROM " . DB_PREFIX . "upsell_related ur LEFT JOIN " . DB_PREFIX . "vendor_upsell vu ON (ur.upsell_id = vu.upsell_id) WHERE (ur.parent_id IN ('" . $cart_implode . "') OR ur.child_id IN ('" . $cart_implode . "')) AND vu.date_end > '" . $date_now . "' AND (vu.quantity_status = 0 || vu.quantity > 0)")->rows;

			// $upsell_update = array();

			// foreach ($upsells as $upsell) {
			// 	if (in_array($upsell['parent_id'], $unique_products)) {
			// 		$upsell_update[$upsell['upsell_id']][] = $sub_products_quant[$upsell['parent_id']];
			// 	}
			// 	if (in_array($upsell['child_id'], $unique_products)) {
			// 		$upsell_update[$upsell['upsell_id']][] = $sub_products_quant[$upsell['child_id']];
			// 	}
			// }

			// foreach ($upsell_update as $key => $value) {
			// 	$subtractStock = max($value);
			// 	$this->db->query("UPDATE " . DB_PREFIX . "vendor_upsell SET quantity = (quantity - " . (int)$subtractStock . ") WHERE upsell_id = '" . (int)$key . "'");
			// }

			// cross sell products
			// $crosssells = $this->db->query("SELECT cr.parent_id, cr.child_id, cr.crosssell_id FROM " . DB_PREFIX . "crosssell_related cr LEFT JOIN " . DB_PREFIX . "vendor_crosssell vc ON (cr.crosssell_id = vc.crosssell_id) WHERE (cr.parent_id IN ('" . $cart_implode . "') OR cr.child_id IN ('" . $cart_implode . "')) AND vc.date_end > '" . $date_now . "' AND (vc.quantity_status = 0 || vc.quantity > 0)")->rows;

			// $crosssell_update = array();

			// foreach ($crosssells as $crosssell) {
			// 	if (in_array($crosssell['parent_id'], $unique_products)) {
			// 		$crosssell_update[$crosssell['crosssell_id']][] = $sub_products_quant[$crosssell['parent_id']];
			// 	}
			// 	if (in_array($crosssell['child_id'], $unique_products)) {
			// 		$crosssell_update[$crosssell['crosssell_id']][] = $sub_products_quant[$crosssell['child_id']];
			// 	}
			// }

			// foreach ($crosssell_update as $key => $value) {
			// 	$subtractStock = max($value);
			// 	$this->db->query("UPDATE " . DB_PREFIX . "vendor_crosssell SET quantity = (quantity - " . (int)$subtractStock . ") WHERE crosssell_id = '" . (int)$key . "'");
			// }

			// gift products
			// $giftteasors = $this->db->query("SELECT ur.parent_id, ur.child_id, ur.giftteasor_id FROM " . DB_PREFIX . "giftteasor_related ur LEFT JOIN " . DB_PREFIX . "vendor_giftteasor vu ON (ur.giftteasor_id = vu.giftteasor_id) WHERE (ur.parent_id IN ('" . $cart_implode . "') OR ur.child_id IN ('" . $cart_implode . "')) AND vu.date_end > '" . $date_now . "' AND (vu.quantity_status = 0 || vu.quantity > 0)")->rows;

			// $giftteasor_update = array();

			// foreach ($giftteasors as $giftteasor) {
			// 	if (in_array($giftteasor['parent_id'], $unique_products)) {
			// 		$giftteasor_update[$giftteasor['giftteasor_id']][] = $sub_products_quant[$giftteasor['parent_id']];
			// 	}
			// 	if (in_array($giftteasor['child_id'], $unique_products)) {
			// 		$giftteasor_update[$giftteasor['giftteasor_id']][] = $sub_products_quant[$giftteasor['child_id']];
			// 	}
			// }

			// foreach ($giftteasor_update as $key => $value) {
			// 	$subtractStock = max($value);
			// 	$this->db->query("UPDATE " . DB_PREFIX . "vendor_giftteasor SET quantity = (quantity - " . (int)$subtractStock . ") WHERE giftteasor_id = '" . (int)$key . "'");
			// }
		// }
	}
}
