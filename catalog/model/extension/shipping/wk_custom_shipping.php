<?php
class ModelExtensionShippingwkcustomshipping extends Model {

	function getQuote($address,$seller = array()) {

		//to stop it's default functionality
		if($this->config->get('wk_multi_shipping_status') AND !$seller){
			return false;
		}

		unset($this->session->data['seller_custom_shipping']);

		$method_data = $result_csv = array();
		$error = false;

		$this->language->load('shipping/wk_custom_shipping');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('shipping_wk_custom_shipping_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if (!$this->config->get('shipping_wk_custom_shipping_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$shipping_postcode = $address;

		if(isset($shipping_postcode['postcode'])){
			$zipcod = $shipping_postcode['postcode'];
		}else{
			$zipcod = 0;
		}

		if(!$seller){
			$this->load->model('account/customerpartnerorder');
			$seller = $this->model_account_customerpartnerorder->sellerAdminData($this->cart->getProducts());
		}

		$total = $totalfortext = 0;
		$addSeller = '';

		if($this->config->get('shipping_wk_custom_shipping_method')=='flat'){
			foreach($seller as $pro_det){

				$name = $this->db->query("SELECT firstname , lastname  FROM " . DB_PREFIX . "customer WHERE customer_id = '".$pro_det['seller']."'")->row;

				if ($name) {
					$seller_name['seller_name'] = ucfirst($name['firstname']) . ' ' . ucfirst($name['lastname']);
				} else {
					$seller_name['seller_name'] = 'Admin';

				}

				if($this->config->get('wk_multi_shipping_status')){
					$addSeller = $pro_det['seller'].'_';
				}

				$res_error = false;

				if($pro_det['seller']=='Admin'){
					$result_csv[$pro_det['seller']] = array('shipping_price' => $this->config->get('shipping_wk_custom_shipping_admin_flatrate'));

					$this->session->data['seller_custom_shipping']['Admin']['shipping_price'] = $this->config->get('shipping_wk_custom_shipping_admin_flatrate');
				}else{
					$csv_id = $pro_det['seller'];
					$res_csv = $this->db->query("SELECT amount as shipping_price,tax_class_id FROM " . DB_PREFIX . "customerpartner_flatshipping WHERE status = 1 and partner_id = '$csv_id'" );
					if(isset($res_csv->row['shipping_price'])){
						$result_csv[$pro_det['seller']] = $res_csv->row;

						$this->session->data['seller_custom_shipping'][$seller_name['seller_name']]['shipping_price'] = $res_csv->row['shipping_price'];
					}else{
						$res_error = true;
					}
				}

				if($res_error){
					$error = '';
					if($this->config->get('shipping_wk_custom_shipping_error_msg')!=''){
						$error = $error. $this->config->get('shipping_wk_custom_shipping_error_msg');
					}
					else{
						$error = $error. 'For Shipping method "'.$this->config->get('shipping_wk_custom_shipping_title').'" : '. $seller_name['seller_name']." Vendor does not provide services for zip : ".$zipcod.', so this method is not selectable.';
					}
				}

			}

		}

		if($this->config->get('shipping_wk_custom_shipping_method')=='matrix'){
			foreach($seller as $pro_det){

				$name = $this->db->query("SELECT firstname , lastname  FROM " . DB_PREFIX . "customer WHERE customer_id = '".$pro_det['seller']."'")->row;

				if ($name) {
					$seller_name['seller_name'] = ucfirst($name['firstname']) . ' ' . ucfirst($name['lastname']);
				} else {
					$seller_name['seller_name'] = 'Admin';

				}

				if($this->config->get('wk_multi_shipping_status')){
					$addSeller = $pro_det['seller'].'_';
				}

				$csv_id = $pro_det['seller'];
				$pro_weight = $pro_det['weight'];

				$res_csv = $this->db->query("SELECT price as shipping_price FROM " . DB_PREFIX . "customerpartner_shipping WHERE seller_id = '".(int)$csv_id."' AND weight_to >= '".(float)$pro_weight."' AND  weight_from <= '".(float)$pro_weight."' AND country_code = '".$this->db->escape($shipping_postcode['iso_code_2'])."' AND ( (convert(zip_to,unsigned) >= '".(int)$zipcod."' AND convert(zip_from,unsigned) <= '".(int)$zipcod."') or (zip_to LIKE '%".$zipcod."' OR zip_from LIKE '%".$zipcod."'))");
				$result_csv[$pro_det['seller']] = $res_csv->row;

				if (isset($res_csv->row['shipping_price'])) {
					if ($csv_id == 'Admin') {
						$this->session->data['seller_custom_shipping']['Admin']['shipping_price'] = $res_csv->row['shipping_price'];
					} else {
						$this->session->data['seller_custom_shipping'][$seller_name['seller_name']]['shipping_price'] = $res_csv->row['shipping_price'];
					}
				}

				$res_error = true;
				if(!isset($res_csv->row['shipping_price']))
					$res_error = false;

				if ($csv_id != 'Admin') {
				  $seller_shipping_status = $this->db->query("SELECT status FROM " . DB_PREFIX . "customerpartner_flatshipping WHERE partner_id =" . $csv_id)->row;

				  if(isset($seller_shipping_status['status']) && !$seller_shipping_status['status']){
				    $res_error = false;
				  }
				}

				if(!$res_error){
					$error = '';
					if($this->config->get('shipping_wk_custom_shipping_error_msg')!=''){
						$error = $error.$this->config->get('shipping_wk_custom_shipping_error_msg');
					}
					else{
						if ($csv_id == 'Admin') {
							$error = $error.'For Shipping method "'.$this->config->get('shipping_wk_custom_shipping_title').'" : '. $csv_id." does not provide services for zip : ".$zipcod.', so this method is not selectable.';
						} else {
							$error = $error.'For Shipping method "'.$this->config->get('shipping_wk_custom_shipping_title').'" : '. $seller_name['seller_name']." Vendor does not provide services for zip : ".$zipcod.', so this method is not selectable.';
						}
					}
				}
			}
		}

		if($this->config->get('shipping_wk_custom_shipping_method')=='both'){
			foreach($seller as $pro_det){

				$name = $this->db->query("SELECT firstname , lastname  FROM " . DB_PREFIX . "customer WHERE customer_id = '".$pro_det['seller']."'")->row;

				if ($name) {
					$seller_name['seller_name'] = ucfirst($name['firstname']) . ' ' . ucfirst($name['lastname']);
				} else {
					$seller_name['seller_name'] = 'Admin';

				}

				if($this->config->get('wk_multi_shipping_status')){
					$addSeller = $pro_det['seller'].'_';
				}

				$res_error = false;
				$csv_id = $pro_det['seller'];
				$pro_weight = $pro_det['weight'];

				$res_csv = $this->db->query("SELECT price as shipping_price FROM " . DB_PREFIX . "customerpartner_shipping WHERE seller_id = '".(int)$csv_id."' AND weight_to >= '".(float)$pro_weight."' AND  weight_from <= '".(float)$pro_weight."' AND country_code = '".$this->db->escape($shipping_postcode['iso_code_2'])."' AND ( (convert(zip_to,unsigned) >= '".(int)$zipcod."' AND convert(zip_from,unsigned) <= '".(int)$zipcod."') or (zip_to LIKE '%".$zipcod."' OR zip_from LIKE '%".$zipcod."'))");

				if(isset($res_csv->row['shipping_price'])){
					$result_csv[$pro_det['seller']] = $res_csv->row;

					$this->session->data['seller_custom_shipping'][$seller_name['seller_name']]['shipping_price'] = $res_csv->row['shipping_price'];
				}else{
					if($pro_det['seller']=='Admin'){
						$result_csv[$pro_det['seller']] = array('shipping_price' => $this->config->get('shipping_wk_custom_shipping_admin_flatrate'));

						$this->session->data['seller_custom_shipping']['Admin']['shipping_price'] = $this->config->get('shipping_wk_custom_shipping_admin_flatrate');
					}else{
						$res_csv = $this->db->query("SELECT amount as shipping_price,tax_class_id FROM " . DB_PREFIX . "customerpartner_flatshipping WHERE status = 1 and partner_id = '".$csv_id."'" );
						if(isset($res_csv->row['shipping_price'])){
							$result_csv[$pro_det['seller']] = $res_csv->row;

							$this->session->data['seller_custom_shipping'][$seller_name['seller_name']]['shipping_price'] = $res_csv->row['shipping_price'];
						}else{
							$res_error = true;
						}
					}
				}

				//if price is not avilable in both table matrix and flat then error generate
				if($res_error){
					$error = '';
					if($this->config->get('shipping_wk_custom_shipping_error_msg')!=''){
						$error =  $error.$this->config->get('shipping_wk_custom_shipping_error_msg');
					}
					else{
						$error =  $error. 'For Shipping method "'.$this->config->get('shipping_wk_custom_shipping_title').'" : '. $seller_name['seller_name']." Vendor does not provide services for zip : ".$zipcod.', so this method is not selectable.';
					}
				}

			}

		}

		if($result_csv)
		$this->session->data['event_shipping_amount'] = 0;

		foreach($result_csv as $sellkey => $res_res) {

			$name = $this->db->query("SELECT firstname , lastname  FROM " . DB_PREFIX . "customer WHERE customer_id = '".$sellkey."'")->row;

				if ($name) {
					$seller_name['seller_name'] = ucfirst($name['firstname']) . ' ' . ucfirst($name['lastname']);
				} else {
					$seller_name['seller_name'] = 'Admin';

				}

			$event_shipping = array();
			$event_shipping_amount = 0;
				if ($this->config->get('shipping_wk_custom_shipping_event_status'))  {
					$this->load->model('account/customer');
				  if ($this->config->get('shipping_wk_custom_shipping_seller_details') && $sellkey != 'Admin' ) {

						$this->load->model('account/add_shipping_mod');
						$shipping = array();
						$sellername = $this->model_account_customer->getCustomer($sellkey);

					  $shipping = $this->model_account_add_shipping_mod->getEventShipping($sellkey);

				      if (isset($shipping) && !empty($shipping) && $shipping['status']) {
				        foreach ($shipping as $shipkey => $shipvalue) {
				          if ($shipkey == 'shipping') {
							$shipping[$shipkey] = (array)json_decode($shipvalue);

				            foreach ($shipping[$shipkey] as $eventkey => $eventvalue) {
				              if ($eventvalue->datefrom <= date('Y-m-d')   && $eventvalue->dateto >= date('Y-m-d')) {
				                $shipping[$shipkey] = array(
				                  'datefrom' => $eventvalue->datefrom,
				                  'dateto'   => $eventvalue->dateto,
				                  'prefix'	 => $eventvalue->prefix,
				                  'type'     => $eventvalue->type,
				                  'amount' 	 => $eventvalue->amount,
				                );
				              }
				            }
				          } else {
				            $shipping[$shipkey] = $shipvalue ;
				          }
				        }
				        $event_shipping[$seller_name['seller_name']] = $shipping;
				      } else {
				        $event_shipping[$seller_name['seller_name']] = array();
				      }
				  } else if($sellkey == 'Admin' || (!$this->config->get('shipping_wk_custom_shipping_seller_details') && $sellkey != 'Admin')) {

				    	$event_shipping[$sellkey]['status'] = true;
						$event_shipping = $this->config->get('shipping_wk_custom_shipping_event_based');

						if (isset($event_shipping) && is_array($event_shipping)) {
							foreach ($event_shipping as $key => $value) {
								if ($value['datefrom'] <= date('Y-m-d')   && $value['dateto'] >= date('Y-m-d')) {
									$event_shipping['Admin']['shipping'] = $event_shipping[$key];
								}
								unset($event_shipping[$key]);
					    	}
						}
					}

				  if(isset($event_shipping) && !empty($event_shipping)) {
				    foreach ($event_shipping as $key => $value) {
				      if (isset($value['shipping']) && !empty($value['shipping'])) {

							$this->session->data['seller_custom_shipping'][$key]['prefix'] = $value['shipping']['prefix'];

							if ($value['shipping']['type'] == 'f' && $value['shipping']['prefix'] == '-' && $res_res['shipping_price'] >= $value['shipping']['amount']) {
								$res_res['shipping_price'] = (float)$res_res['shipping_price'] - $value['shipping']['amount'];

								$this->session->data['seller_custom_shipping'][$key]['event_shipping_amount'] = $event_shipping_amount = $value['shipping']['amount'];

							}
							if ($value['shipping']['type'] == 'f' && $value['shipping']['prefix'] == '+') {
								$res_res['shipping_price'] = (float)($res_res['shipping_price'] + $value['shipping']['amount']);

								$this->session->data['seller_custom_shipping'][$key]['event_shipping_amount'] = $event_shipping_amount = $value['shipping']['amount'];

							}
							if ($value['shipping']['type'] == 'p') {
								$per = (float)(($res_res['shipping_price']*$value['shipping']['amount'])/100);

								$this->session->data['seller_custom_shipping'][$key]['event_shipping_amount'] = $event_shipping_amount = $per;


								if ($value['shipping']['prefix'] == '-' && $per <= $res_res['shipping_price']) {
									$res_res['shipping_price'] = (float)($res_res['shipping_price'] - $per);

								}
								if ($value['shipping']['prefix'] == '+') {
									$res_res['shipping_price'] = (float)($res_res['shipping_price'] + $per);

								}
							}
				        }
				      }
				    }
				  }

				if(isset($res_res['shipping_price'])) {

					$total 	 =  $total + $res_res['shipping_price'];
					$this->session->data['event_shipping_amount'] += $event_shipping_amount;
					$totalfortext += $this->tax->calculate($res_res['shipping_price'], $this->config->get('shipping_wk_custom_shipping_tax_class_id'), $this->config->get('config_tax'));
				}
			}

			if ($error || !$this->config->get('shipping_wk_custom_shipping_status')) {
				unset($this->session->data['seller_custom_shipping']);
			}

			if (isset($this->session->data['seller_custom_shipping']) && $this->session->data['seller_custom_shipping']) {
				foreach ($this->session->data['seller_custom_shipping'] as $key => $value) {
					$this->session->data['seller_custom_shipping'][$key]['shipping_price'] = $this->currency->format($value['shipping_price'],$this->session->data['currency']);
					$this->session->data['seller_custom_shipping'][$key]['cost'] = $value['shipping_price'];

					$this->session->data['seller_custom_shipping'][$key]['event_shipping_amount'] = isset($value['event_shipping_amount']) ? $this->currency->format($value['event_shipping_amount'],$this->session->data['currency']) : $this->currency->format(0,$this->session->data['currency']);

					$this->session->data['seller_custom_shipping'][$key]['prefix'] = isset($value['prefix']) ? $value['prefix'] : '';
				}
			}

		if ($status) {
			$quote_data = array();
			//small bug fixed to display prices correctly can display amount difference but persons will get correct price..
			$total += $totalfortext - $this->tax->calculate($total, $this->config->get('shipping_wk_custom_shipping_tax_class_id'), $this->config->get('config_tax'));
      		$quote_data['wk_custom_shipping'] = array(
        		'code'         => $addSeller.'wk_custom_shipping.wk_custom_shipping',
        		'title'        => $this->config->get('shipping_wk_custom_shipping_title'),
        		'cost'         => $total,
        		'tax_class_id' => $this->config->get('shipping_wk_custom_shipping_tax_class_id'),
				'text'         => $this->currency->format($totalfortext,$this->session->data['currency']),
      		);

      		$method_data = array(
        		'code'       => $addSeller.'wk_custom_shipping',
        		'title'      => $this->config->get('shipping_wk_custom_shipping_title'),
        		'quote'      => $quote_data,
				'sort_order' => $this->config->get('shipping_wk_custom_shipping_sort_order'),
        		'error'      => $error,

      		);
		}

		return $method_data;
	}
}
?>
