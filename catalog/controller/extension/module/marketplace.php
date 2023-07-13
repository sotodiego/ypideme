<?php
class ControllerExtensionModuleMarketplace extends Controller {

	private $data = array();

	public function index() {

		if ($this->config->get('marketplace_scf_status') && isset($this->request->get['route']) && $this->request->get['route'] == 'product/category') {
			 $this->load->language('extension/module/scf');
			 $this->data['heading_title'] = $this->language->get('heading_title');

			if (isset($this->request->get['path'])) {
				$path =$this->request->get['path'];
				$parts = explode('_', (string)$this->request->get['path']);
			} else {
				$parts = array();
				$path = '';
			}

			if (isset($parts[0])) {
				$this->data['category_id'] = $parts[0];
			} else {
				$this->data['category_id'] = 0;
			}

			if (isset($parts[1])) {
				$this->data['child_id'] = $parts[1];
			} else {
				$this->data['child_id'] = 0;
			}

			if (isset($this->request->get['scf_id'])) {
				$seller_id = $this->request->get['scf_id'];
			} else {
				$seller_id = 0;
			}

			$this->data['scf_id'] = $seller_id;

			$this->load->model('customerpartner/master');

			$sellers = $this->model_customerpartner_master->getOldPartner();

			$this->data['sellers'][] = array(
				'customer_id' => 0,
				'name'        => $this->config->get('config_name'),
				'href'        => $this->url->link('product/category&path=' . $path,'scf_id=0',true)
			);

			foreach ($sellers as $seller) {
				$this->data['sellers'][] = array(
					'customer_id' => $seller['customer_id'],
					'name'        => $seller['firstname']. ' ' . $seller['lastname'],
					'href'        => $this->url->link('product/category&path=' . $path,'scf_id='.$seller['customer_id'],true)
				);
			}
			return $this->load->view('extension/module/scf', $this->data);
		}

		$data = array_merge($this->load->language('account/customerpartner/notification'));

		$this->load->model('account/customerpartner');

		$this->load->model('customerpartner/master');

		$this->language->load('extension/module/marketplace');

    $data = array_merge($this->language->load('extension/module/marketplace'),$this->load->language('account/customerpartner/notification'));

		$data['logged'] = $this->customer->isLogged();
		$data['contact_mail'] = true;

		$data['send_mail'] = $this->url->link('account/customerpartner/sendmail','',true);
		$data['redirect_user'] = $this->url->link('account/login','',true);

		$data['launchModal'] = false;

		$data['hasApplied'] = $this->model_account_customerpartner->IsApplyForSellership();

		if($this->config->get('config_template') == 'journal2' || $this->config->get('config_template') == 'hodeco') {
			if(isset($this->session->data['openModal2']) && $this->session->data['openModal2']) {
				$this->session->data['openModal2'] = false;
				$data['launchModal'] = false;
		    }

			if($this->model_account_customerpartner->chkIsPartner()) {
				if(!isset($this->session->data['openModal']) || $this->session->data['openModal']) {
					$this->session->data['openModal'] = false;
					$this->session->data['openModal2'] = true;
					$data['launchModal'] = true;
				}
			}
		} else {
			$data['launchModal'] = false;
			if($this->model_account_customerpartner->chkIsPartner()) {
				if(!isset($this->session->data['openModal']) || $this->session->data['openModal']) {
					$this->session->data['openModal'] = false;
					$data['launchModal'] = true;
				}
			}
		}

		if (isset($this->session->data['marketplace_seller_mode']) && !$this->session->data['marketplace_seller_mode']) {
			$data['launchModal'] = false;
		}

		if (isset($this->request->get['route']) && $this->request->get['route'] != 'account/account') {
			$data['launchModal'] = false;
		}

		$mp_language = array();

		if(isset($this->request->get['route']) AND (substr($this->request->get['route'],0,8)=='account/')) {

			if($this->config->get('marketplace_account_menu_sequence')) {
				foreach ($this->config->get('marketplace_account_menu_sequence') as $key => $lang_value) {
					if ($key == 'manageshipping') {
						if ($this->config->get('shipping_wk_custom_shipping_status') && $this->config->get('shipping_wk_custom_shipping_seller_details'))
							$mp_language[$key] = $this->language->get('text_'.$key);
							
					} else {
						$mp_language[$key] = $this->language->get('text_'.$key);
					}
					
				}
				$data['marketplace_account_menu_sequence'] = $mp_language;
			}

			$data['isMember'] = false;
			if($this->config->get('module_wk_seller_group_status')) {
	      		$data['module_wk_seller_group_status'] = true;
	      		$this->load->model('account/customer_group');
				$isMember = $this->model_account_customer_group->getSellerMembershipGroup($this->customer->getId());
				if($isMember) {
					$allowedAccountMenu = $this->model_account_customer_group->getaccountMenu($isMember['gid']);
					if($allowedAccountMenu['value']) {
						$accountMenu = explode(',',$allowedAccountMenu['value']);
						foreach ($accountMenu as $key => $menu) {
							$aMenu = explode(':', $menu);
							$data['marketplace_allowed_account_menu'][$aMenu[0]] = $aMenu[1];
						}
					}
					$data['isMember'] = true;
				} else {
					$data['isMember'] = false;
				}
	      	}

	      	if ($this->model_account_customerpartner->chkIsPartner() && !$data['isMember'] && $this->config->get('module_wk_seller_group_status')) {

				$data['marketplace_allowed_account_menu']['membership'] = 'membership';
			}

			if($this->config->get('marketplace_allowed_account_menu') && !$this->config->get('module_wk_seller_group_status')) {
			  $data['marketplace_allowed_account_menu'] = $this->config->get('marketplace_allowed_account_menu');
		  }


			$data['mail_for'] = '&contact_admin=true';
			$data['want_partner'] = $this->url->link('account/customerpartner/become_partner','',true);

			$data['account_menu_href'] = array(
				'profile' => $this->url->link('account/customerpartner/profile', '', true),
				'dashboard' => $this->url->link('account/customerpartner/dashboard', '', true),
				'orderhistory' => $this->url->link('account/customerpartner/orderlist', '', true),
				'transaction' => $this->url->link('account/customerpartner/transaction', '', true),
				'category' => $this->url->link('account/customerpartner/category', '', true),
				'productlist' => $this->url->link('account/customerpartner/productlist', '', true),
				'addproduct' => $this->url->link('account/customerpartner/addproduct', '', true),
				'downloads' => $this->url->link('account/customerpartner/download', '', true),
				'manageshipping' => $this->url->link('account/customerpartner/add_shipping_mod', '', true),
				'asktoadmin' => $this->url->link('account/customerpartner/addproduct', '', true),
				'notification' => $this->url->link('account/customerpartner/notification', '', true),
				'information' => $this->url->link('account/customerpartner/information', '', true),
				'review' => $this->url->link('account/customerpartner/review', '', true),
				'income' => $this->url->link('account/customerpartner/income','',true),
			);

			if($this->config->get('module_wk_seller_group_status')) {
				$data['module_wk_seller_group_status'] = true;
				$data['account_menu_href']['membership'] = $this->url->link('account/customerpartner/wk_membership_catalog','',true);
				$data['account_menu_href']['membership_quote'] = $this->url->link('account/customerpartner/wk_membership_quote','',true);
	        	$data['account_menu_href']['membership'] = $this->url->link('account/customerpartner/wk_membership_catalog','',true);
	      } else {
	        	$data['module_wk_seller_group_status'] = false;
	        	if(isset($data['account_menu_href']['membership'])) {
	        		unset($data['account_menu_href']['membership']);
	        	}
	        	if(isset($data['marketplace_account_menu_sequence']['membership'])) {
	        		unset($data['marketplace_account_menu_sequence']['membership']);
				}
				
				if(isset($data['account_menu_href']['membership_quote'])) {
	        		unset($data['account_menu_href']['membership_quote']);
	        	}
				
				if(isset($data['account_menu_href']['membership_quote'])) {
	        		unset($data['account_menu_href']['membership_quote']);
	        	}
	        	if(isset($data['marketplace_account_menu_sequence']['membership_quote'])) {
	        		unset($data['marketplace_account_menu_sequence']['membership_quote']);
				}

	      }

	    	$data['mostViewedProducts'] = $this->model_account_customerpartner->getMostViewedProducts($this->customer->getId());
	    	$data['lowStockProducts'] = $this->model_account_customerpartner->getLowStockProducts($this->customer->getId());
	    	$data['totalProductsLowStock'] = $data['lowStockProducts']['count'];

	    	$data['sellerProfile'] = $this->model_account_customerpartner->getProfile();

	    	$this->load->model("tool/image");

	    	if ($data['sellerProfile']) {
		    	if(isset($data['sellerProfile']['avatar']) && $data['sellerProfile']['avatar']) {
		    		$data['sellerProfile']['avatar'] = $this->model_tool_image->resize($data['sellerProfile']['avatar'],100,100);
		    	} else if($this->config->get('marketplace_default_image_name')) {
		    		$data['sellerProfile']['avatar'] = $this->model_tool_image->resize($this->config->get('marketplace_default_image_name'),100,100);
		    	} else {
		    		$data['sellerProfile']['avatar'] = $this->model_tool_image->resize('no_image.png',100,100);
		    	}
	    	}else{
	    		$data['sellerProfile']['avatar'] = $this->model_tool_image->resize('no_image.png',100,100);
	    		$data['sellerProfile']['firstname'] = '';
	    		$data['sellerProfile']['lastname'] = '';
	    	}

	    	$data['moreProductUrl'] = $this->url->link('account/customerpartner/productlist', '', true);
				/*
				Promotional Mod
				add link to existing seller's menu array
				 */
				if($this->config->get('module_wk_upsell_upsell_status')) {
						$data['wk_upsell_upsell_status'] = true;
						$data['account_menu_href']['wk_upsell'] = $this->url->link('account/customerpartner/upsell', '', true);
				} else {
						$data['wk_upsell_upsell_status'] = false;
						if(isset($data['account_menu_href']['wk_upsell'])) {
								unset($data['account_menu_href']['wk_upsell']);
						}
						if(isset($data['marketplace_account_menu_sequence']['wk_upsell'])) {
								unset($data['marketplace_account_menu_sequence']['wk_upsell']);
						}
				}

				if($this->config->get('module_wk_crosssell_crosssell_status')) {
						$data['wk_crosssell_crosssell_status'] = true;
						$data['account_menu_href']['wk_crosssell'] = $this->url->link('account/customerpartner/crosssell', '', true);
				} else {
						$data['wk_crosssell_crosssell_status'] = false;
						if(isset($data['account_menu_href']['wk_crosssell'])) {
								unset($data['account_menu_href']['wk_crosssell']);
						}
						if(isset($data['marketplace_account_menu_sequence']['wk_crosssell'])) {
								unset($data['marketplace_account_menu_sequence']['wk_crosssell']);
						}
				}
				/*
				end here
				 */


				 if($this->config->get('module_marketplace_status') && $this->config->get('module_wk_pricealert_status') && $this->config->get('wk_pricealert_allow_seller')) {
 						$data['wk_pricealert'] = true;
 						$data['account_menu_href']['pricealert'] = $this->url->link('account/customerpartner/pricealert', '', true);
						$data['account_menu_href']['pa_request'] = $this->url->link('account/customerpartner/pa_request', '', true);
 				} else {
 						$data['wk_pricealert'] = false;
 						if(isset($data['account_menu_href']['pricealert'])) {
 								unset($data['account_menu_href']['pricealert']);
 						}
 						if(isset($data['marketplace_account_menu_sequence']['pricealert'])) {
 								unset($data['marketplace_account_menu_sequence']['pricealert']);
 						}
						if(isset($data['account_menu_href']['pa_request'])) {
 								unset($data['account_menu_href']['pa_request']);
 						}
 						if(isset($data['marketplace_account_menu_sequence']['pa_request'])) {
 								unset($data['marketplace_account_menu_sequence']['pa_request']);
 						}
 			}

			$data['chkIsPartner'] = $this->model_account_customerpartner->chkIsPartner();

			$data['marketplace_seller_mode'] = isset($this->session->data['marketplace_seller_mode']) ? $this->session->data['marketplace_seller_mode'] : 1;

		} elseif(isset($this->request->get['route']) AND $this->request->get['route']=='product/product' AND isset($this->request->get['product_id']) && $this->config->get('marketplace_seller_info_by_module') && !$this->config->get('marketplace_seller_info_hide')) {

			$data['mail_for'] = '&contact_seller=true';
			$data['text_ask_question'] = $this->language->get('text_ask_seller');

			if(!$data['logged'])
				$data['text_ask_seller'] = $this->language->get('text_ask_seller_log');

			$id = $this->model_customerpartner_master->getPartnerIdBasedonProduct($this->request->get['product_id']);

			if (isset($id['id']) && ($id['id'] == $this->customer->getId())) {

				$data['contact_mail'] = false;
			}else{
				$data['contact_mail'] = $this->config->get('marketplace_customercontactseller');
			}

			if($this->config->get('marketplace_product_show_seller_product')) {
				$data['show_seller_product'] = $this->config->get('marketplace_product_show_seller_product');
			} else {
				$data['show_seller_product'] = false;
			}
			$this->load->model('tool/image');
			if(isset($id['id']) AND $id['id']){

				$partner = $this->model_customerpartner_master->getProfile($id['id']);
				if($partner){

					if($this->config->get('marketplace_product_name_display')) {
						if($this->config->get('marketplace_product_name_display') == 'sn') {
							$data['displayName'] = $partner['firstname']." ".$partner['lastname'];
						} else if($this->config->get('marketplace_product_name_display') == 'cn') {
							$data['displayName'] = $partner['companyname'];
						} else {
							$data['displayName'] = $partner['companyname']." (".$partner['firstname']." ".$partner['lastname'].")";
						}
					}

					if($this->config->get('marketplace_product_image_display')) {
						$partner['companylogo'] = $partner[$this->config->get('marketplace_product_image_display')];
					}

					if ($partner['companylogo'] && file_exists(DIR_IMAGE . $partner['companylogo'])) {
						$partner['thumb'] = $this->model_tool_image->resize($partner['companylogo'],100,100);
						// $partner['avatar'] = HTTP_SERVER.'image/'.$partner['avatar'];
					} else if($this->config->get('marketplace_default_image_name')) {
						$partner['thumb'] = $this->model_tool_image->resize($this->config->get('marketplace_default_image_name'), 100,100);
					} else {
						$partner['thumb'] = $this->model_tool_image->resize('no_image.png',100,100);
					}

					$data['seller_id'] = $id['id'];

					$partner['sellerHref'] = $this->url->link('customerpartner/profile&id='.$id['id'],'',true);
					$data['collectionHref'] = $this->url->link('customerpartner/profile&id='.$id['id'],'&collection',true);
					$partner['name'] = $partner['firstname'].' '.$partner['lastname'];
					$partner['total_products'] = $this->model_customerpartner_master->getPartnerCollectionCount($id['id']);
					$partner['feedback_total'] = round($this->model_customerpartner_master->getAverageFeedback($id['id']));

					$data['text_seller_information'] = $this->language->get('text_seller_information');

					$this->load->model('customerpartner/information');

					$data['informations'] = array();

					$informations = $this->model_customerpartner_information->getSellerInformations($data['seller_id']);

					if ($informations) {
					  $count = 0;

					  foreach ($informations as $result) {
					    $data['informations'][] = array(
					      'title' => $result['title'],
					      'href'  => $this->url->link('information/information', 'information_id=' . $result['information_id'])
					    );

					    $count++;

					    if ($count == 3) {
					      break;
					    }
					  }
					}

					$data['partner'] = $partner;

					$filter_array = array( 'start' => 0,
										   'limit' => 4,
										   'customer_id' => $id['id'],
										   'filter_status' => 1,
										   'filter_store' => $this->config->get('config_store_id')

										   );

					$latest = $this->model_account_customerpartner->getProductsSeller($filter_array);

					$data['latest'] = array();

					if($latest){

						$this->load->model("catalog/product");

						foreach($latest as $key => $result){

							if($result['product_id']==$this->request->get['product_id'])
								continue;

							$product_info = $this->model_catalog_product->getProduct($result['product_id']);

							if (isset($product_info['price']) && $product_info['price']) {
							  $result['price'] = $product_info['price'];
							}

							if ($result['image'] && is_file(DIR_IMAGE.$result['image'])) {
								$image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_'.$this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_'.$this->config->get('config_theme') . '_image_product_height'));
							} else {
								$image = $this->model_tool_image->resize('no_image.png', $this->config->get('theme_'.$this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_'.$this->config->get('config_theme') . '_image_product_height'));
							}

							if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
								$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')),$this->session->data['currency']);
							} else {
								$price = false;
							}

							if ((float)$result['special']) {
								$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')),$this->session->data['currency']);
							} else {
								$special = false;
							}

							if ($this->config->get('config_tax')) {
								$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'],$this->session->data['currency']);
							} else {
								$tax = false;
							}

							if ($this->config->get('config_review_status')) {
								$rating = (int)$result['rating'];
							} else {
								$rating = false;
							}

							$data['latest'][] = array(
								'product_id'  => $result['product_id'],
								'thumb'       => $image,
								'name'        => $result['name'],
								'description' => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('theme_'.$this->config->get('config_theme') . '_product_description_length')) . '..',
								'price'       => $price,
								'special'     => $special,
								'tax'         => $tax,
								'rating'      => $result['rating'],
								'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'], true)
							);
						}
					}
				}

			}else
				return;

		}

		$data['view_all'] = $this->url->link('account/customerpartner/notification','',true);

		if ($this->customer->getId()) {
			$this->load->model('account/notification');

			$this->load->model('mp_localisation/order_status');

			$data['processing_status_total'] = $this->model_account_notification->getTotalSellerActivity(array('2'));

			$data['complete_status_total'] = $this->model_account_notification->getTotalSellerActivity(array('5'));

			$data['return_total'] = $this->model_account_notification->getTotalSellerActivity(array('return'));

			$data['notification_total'] = 0;

			$data['notification_total'] = $this->model_account_notification->getTotalSellerActivity() + $this->model_account_notification->getSellerProductActivityTotal() + $this->model_account_notification->getSellerReviewsTotal()-$this->model_account_notification->getViewedNotifications();

			if ($data['notification_total'] < 0) {
			  $data['notification_total'] = 0;
			}

			$data['seller_notifications'] = array();

			$seller_notifications = $this->model_account_notification->getSellerActivity(array(),3);

			if ($seller_notifications) {
				foreach ($seller_notifications as $key => $seller_notification) {

					$date_diff = (array)(new DateTime($seller_notification['date_added']))->diff(new DateTime());

					if (isset($date_diff['y']) && $date_diff['y']) {
					  $seller_notification['date_added'] = $date_diff['y'].' year(s)';
					} elseif (isset($date_diff['m']) && $date_diff['m']) {
					  $seller_notification['date_added'] = $date_diff['m'].' month(s)';
					} elseif (isset($date_diff['d']) && $date_diff['d']) {
					  $seller_notification['date_added'] = $date_diff['d'].' day(s)';
					} elseif (isset($date_diff['h']) && $date_diff['h']) {
					  $seller_notification['date_added'] = $date_diff['h'].' hour(s)';
					} elseif (isset($date_diff['i']) && $date_diff['i']) {
					  $seller_notification['date_added'] = $date_diff['i'].' minute(s)';
					}else {
					  $seller_notification['date_added'] = $date_diff['s'].' second(s)';
					}

					if ($seller_notification['key'] == 'order_account') {
						$seller_notification['data'] = json_decode($seller_notification['data'],1);
						$data['seller_notifications'][] = sprintf($this->language->get('text_order_add_mp'),$seller_notification['data']['order_id'],$seller_notification['data']['order_id'],$seller_notification['data']['name'],$seller_notification['date_added']);
					} elseif ($seller_notification['key'] == 'return_account') {
						$seller_notification['data'] = json_decode($seller_notification['data'],1);
						$order_id = $this->model_account_notification->getReturnOrderId($seller_notification['data']['return_id']);
						$data['seller_notifications'][] = sprintf($this->language->get('text_order_return_mp'),$seller_notification['data']['name'],$order_id['order_id'],$seller_notification['data']['return_id'],$order_id['product'],$seller_notification['date_added']);
					} elseif ($seller_notification['key'] == 'order_status') {
						$seller_notification['data'] = json_decode($seller_notification['data'],1);
						$status = $this->model_mp_localisation_order_status->getOrderStatus($seller_notification['data']['status']);
						if ($status) {
							$data['seller_notifications'][] = sprintf($this->language->get('text_order_status_mp'),$seller_notification['data']['order_id'],$seller_notification['data']['order_id'],$status['name'],$seller_notification['date_added']);
						}
					}
				}
			}

			$data['seller_product_reviews'] = array();

			$data['product_stock_total'] = $this->model_account_notification->getProductStockTotal();

			$data['review_total'] = $this->model_account_notification->getReviewTotal();

			$data['approval_total'] = $this->model_account_notification->getApprovalTotal();

			$seller_product_reviews = $this->model_account_notification->getSellerProductActivity(array(),3);

			$data['product_review_total'] = $this->model_account_notification->getSellerProductActivityTotal();

			if ($seller_product_reviews) {
				foreach ($seller_product_reviews as $key => $seller_product_review) {
					$date_diff = (array)(new DateTime($seller_product_review['date_added']))->diff(new DateTime());

					if (isset($date_diff['y']) && $date_diff['y']) {
					  $seller_product_review['date_added'] = $date_diff['y'].' year(s)';
					} elseif (isset($date_diff['m']) && $date_diff['m']) {
					  $seller_product_review['date_added'] = $date_diff['m'].' month(s)';
					} elseif (isset($date_diff['d']) && $date_diff['d']) {
					  $seller_product_review['date_added'] = $date_diff['d'].' day(s)';
					} elseif (isset($date_diff['h']) && $date_diff['h']) {
					  $seller_product_review['date_added'] = $date_diff['h'].' hour(s)';
					} elseif (isset($date_diff['i']) && $date_diff['i']) {
					  $seller_product_review['date_added'] = $date_diff['i'].' minute(s)';
					}else {
					  $seller_product_review['date_added'] = $date_diff['s'].' second(s)';
					}
					$seller_product_review['data'] = json_decode($seller_product_review['data'],1);
					if ($seller_product_review['key'] == 'product_review') {
						$data['seller_product_reviews'][] = sprintf($this->language->get('text_product_review'),$seller_product_review['id'],$seller_product_review['data']['author'],$seller_product_review['data']['product_id'],$seller_product_review['data']['product_name'],$seller_product_review['date_added']);
					} elseif($seller_product_review['key'] == 'product_stock') {
						$data['seller_product_reviews'][] = sprintf($this->language->get('text_product_stock'),$seller_product_review['data']['product_id'],$seller_product_review['data']['product_name'],$seller_product_review['date_added']);
					} elseif ($seller_product_review['key'] == 'product_approve') {
						$data['seller_product_reviews'][] = sprintf($this->language->get('text_product_approve'),$seller_product_review['data']['product_id'],$seller_product_review['data']['product_name'],$seller_product_review['date_added']);
					}
				}
			}

			$data['seller_reviews'] = array();

			$seller_reviews = $this->model_account_notification->getSellerReviews(array(),3);

			$data['seller_review_total'] = $this->model_account_notification->getSellerReviewsTotal();

			if ($seller_reviews) {
				foreach ($seller_reviews as $key => $seller_review) {
					if ($seller_review) {
						$date_diff = (array)(new DateTime($seller_review['createdate']))->diff(new DateTime());

						if (isset($date_diff['y']) && $date_diff['y']) {
						  $seller_review['createdate'] = $date_diff['y'].' year(s)';
						} elseif (isset($date_diff['m']) && $date_diff['m']) {
						  $seller_review['createdate'] = $date_diff['m'].' month(s)';
						} elseif (isset($date_diff['d']) && $date_diff['d']) {
						  $seller_review['createdate'] = $date_diff['d'].' day(s)';
						} elseif (isset($date_diff['h']) && $date_diff['h']) {
						  $seller_review['createdate'] = $date_diff['h'].' hour(s)';
						} elseif (isset($date_diff['i']) && $date_diff['i']) {
						  $seller_review['createdate'] = $date_diff['i'].' minute(s)';
						}else {
						  $seller_review['createdate'] = $date_diff['s'].' second(s)';
						}

						$data['seller_reviews'][] = sprintf($this->language->get('text_seller_review_mp'),$seller_review['id'],$seller_review['customer_id'],$seller_review['name'],$seller_review['createdate']);
					}
				}
			}

			$categories = $this->model_account_notification->getSellerCategoryActivity();

			$categories_total = $this->model_account_notification->getSellerCategoryActivityTotal();

			if ($categories) {
			  foreach ($categories as $key => $category) {

			    $date_diff = (array)(new DateTime($category['date_added']))->diff(new DateTime());

			    if (isset($date_diff['y']) && $date_diff['y']) {
			      $category['date_added'] = $date_diff['y'].' year(s)';
			    } elseif (isset($date_diff['m']) && $date_diff['m']) {
			      $category['date_added'] = $date_diff['m'].' month(s)';
			    } elseif (isset($date_diff['d']) && $date_diff['d']) {
			      $category['date_added'] = $date_diff['d'].' day(s)';
			    } elseif (isset($date_diff['h']) && $date_diff['h']) {
			      $category['date_added'] = $date_diff['h'].' hour(s)';
			    } elseif (isset($date_diff['i']) && $date_diff['i']) {
			      $category['date_added'] = $date_diff['i'].' minute(s)';
			    }else {
			      $category['date_added'] = $date_diff['s'].' second(s)';
			    }
			    $category['data'] = json_decode($category['data'],1);

			    if (isset($category['data']['category_name']) && $category['data']['category_name']) {
			      $data['seller_reviews'][] = sprintf($this->language->get('text_category_approve'),$category['data']['category_name'], $category['data']['category_name'],$category['date_added']);
			    }
			  }
			} else {
        // return false;
			}
		}
		return $this->load->view('extension/module/marketplace', $data);
	}
	public function sellmenu(){

		if ($this->request->server['HTTPS']) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}

		$this->load->model('account/notification');

		$this->load->language('extension/module/marketplace');

		$this->load->model('account/customerpartner');

		$data['module_marketplace_status'] = $this->config->get('module_marketplace_status');

		/**
			* membership code for account menu
			* @param  {[type]} $this [description]
			* @return {[type]}       [description]
			*/

		if($this->config->get('module_wk_seller_group_status')) {
			$data['marketplace_allowed_account_menu'] = array();
			$this->load->model('account/customer_group');
			$isMember = $this->model_account_customer_group->getSellerMembershipGroup($this->customer->getId());
			if($isMember) {
				$allowedAccountMenu = $this->model_account_customer_group->getaccountMenu($isMember['gid']);
				if($allowedAccountMenu['value']) {
					$accountMenu = explode(',',$allowedAccountMenu['value']);
					foreach ($accountMenu as $key => $menu) {
						$aMenu = explode(':', $menu);
						$data['marketplace_allowed_account_menu'][$aMenu[0]] = $aMenu[1];
					}
				}
			} else {
				$data['marketplace_allowed_account_menu'] = $this->config->get('marketplace_allowed_account_menu');
			}
		} else {
			$data['marketplace_allowed_account_menu'] = $this->config->get('marketplace_allowed_account_menu');
		}
		
		// Marketplace Custom Shipping
		if (!$this->config->get('shipping_wk_custom_shipping_status') || !$this->config->get('shipping_wk_custom_shipping_seller_details')) {
			unset($data['marketplace_allowed_account_menu']['manageshipping']);
		}

		/**
			* membership code ends here
			*/
		$data['mp_addproduct'] = $this->url->link('account/customerpartner/addproduct', '', true);
		$data['mp_productlist'] = $this->url->link('account/customerpartner/productlist', '', true);
		$data['mp_dashboard'] = $this->url->link('account/customerpartner/dashboard', '', true);
		$data['mp_add_shipping_mod'] = $this->url->link('account/customerpartner/add_shipping_mod','', true);
		$data['mp_orderhistory'] = $this->url->link('account/customerpartner/orderlist','', true);
		$data['mp_download'] = $this->url->link('account/customerpartner/download','', true);
		$data['mp_profile'] = $this->url->link('account/customerpartner/profile','',true);
		$data['mp_want_partner'] = $this->url->link('account/customerpartner/become_partner','',true);
		$data['mp_transaction'] = $this->url->link('account/customerpartner/transaction','',true);
		$data['mp_information'] = $this->url->link('account/customerpartner/information','',true);
		$data['mp_category'] = $this->url->link('account/customerpartner/category','',true);
		$data['mp_review'] = $this->url->link('account/customerpartner/review','',true);
		$data['menusell'] = $this->url->link('customerpartner/sell', '', true);
		$data['mp_income'] = $this->url->link('account/customerpartner/income','',true);

		if ($this->config->get('module_marketplace_status') && $this->config->get('marketplace_separate_view')) {
				 if (preg_match('/route=account\/customerpartner/',$this->request->server['QUERY_STRING'])) {
						$data['separate_view'] = $server . 'index.php?' . str_replace(array('&amp;view=separate', '&amp;view=default', '&view=separate', '&view=default'), '', $this->request->server['QUERY_STRING']) . '&view=separate';
				  } else {
						$data['separate_view'] = $this->url->link('account/customerpartner/dashboard', 'view=separate', true);
					}

				if (isset($this->request->get['view']) && $this->request->get['view']) {
					$this->session->data['marketplace_separate_view'] = $this->request->get['view'];
				}
		}

		$data['notification'] = '';

		$data['notification_total'] = 0;

		if($this->config->get('module_marketplace_status')){
				$data['logged'] = $this->customer->isLogged() ? 1 : 0;
				$data['chkIsPartner'] = $this->model_account_customerpartner->chkIsPartner();
				$data['marketplace_seller_mode'] = isset($this->session->data['marketplace_seller_mode']) ? $this->session->data['marketplace_seller_mode'] : 1;
				if ($data['chkIsPartner'] && $data['marketplace_seller_mode']) {
					$data['notification'] = $this->load->controller('account/customerpartner/notification/notifications');

					$data['notification_total'] = $this->model_account_notification->getTotalSellerActivity() + $this->model_account_notification->getSellerProductActivityTotal() + $this->model_account_notification->getSellerReviewsTotal()-$this->model_account_notification->getViewedNotifications();

					if ($data['notification_total'] < 0) {
						$data['notification_total'] = 0;
					}
				}
		}
						// Membership code to display membershi plans on registration
						if ($this->config->get('module_wk_seller_group_status') && $this->config->get('module_wk_seller_group_membership_on_registration')) {
							$this->load->language('account/customerpartner/wk_membership_catalog');
								$membershipgroups = $this->load->controller('account/customerpartner/wk_membership_catalog/getMembershipGroups');
							$data['group_list'] = isset($membershipgroups['group_list']) ? $membershipgroups['group_list'] : array();
							$data['button_cart'] = $this->language->get('button_cart');
							$data['text_view_membership'] = $this->language->get('text_view_membership');
							$data['register_view'] = true;
							$data['membership_groups'] = $this->load->view('account/customerpartner/wk_membership_groups', $data);
						}
		

		return $this->load->view('customerpartner/sellmenu', $data);
	}

	public function registerseller(){
		$data['marketplace_becomepartnerregistration'] = false;

		$data['module_marketplace_status'] = false;

		if($this->config->get('module_marketplace_status') && $this->config->get('marketplace_becomepartnerregistration')){
		    $data['marketplace_becomepartnerregistration'] = $this->config->get('marketplace_becomepartnerregistration');

				$data['module_marketplace_status'] = $this->config->get('module_marketplace_status');

				$this->load->language('account/customerpartner/become_partner');

		    if (isset($this->request->post['shoppartner'])) {
		        $data['shoppartner'] = $this->request->post['shoppartner'];
		    } else {
		        $data['shoppartner'] = '';
		    }

		    if (isset($this->request->post['tobecomepartner'])) {
		        $data['tobecomepartner'] = $this->request->post['tobecomepartner'];
		    } else {
		        $data['tobecomepartner'] = '';
		    }
				/*
				* $this->error is private variable of register controller so it is not accessible here
				* need to show custom error message for validation
				*/
				if(isset($this->request->post['shoppartner']) && utf8_strlen($this->request->post['shoppartner'])<=3 &&  isset($this->request->post['tobecomepartner']) && $this->request->post['tobecomepartner']==1){
						$data['error_shoppartner'] = $this->language->get('error_validshop');
				}else if(isset($this->request->post['shoppartner']) && utf8_strlen($this->request->post['shoppartner']) >1 && isset($this->request->post['tobecomepartner']) && $this->request->post['tobecomepartner'] ==1){
						$this->load->model('customerpartner/master');
						if($this->model_customerpartner_master->getShopData($this->request->post['shoppartner'])){
							$data['error_shoppartner'] = $this->language->get('error_noshop');
						}
				} else {
						$data['error_shoppartner'] = '';
				}

				// Membership code to display membershi plans on registration
				if ($this->config->get('module_wk_seller_group_status') && $this->config->get('module_wk_seller_group_membership_on_registration')) {
					$this->load->language('account/customerpartner/wk_membership_catalog');
						$membershipgroups = $this->load->controller('account/customerpartner/wk_membership_catalog/getMembershipGroups');
					$data['group_list'] = isset($membershipgroups['group_list']) ? $membershipgroups['group_list'] : array();
					$data['button_cart'] = $this->language->get('button_cart');
					$data['text_view_membership'] = $this->language->get('text_view_membership');
					$data['register_view'] = true;
					$data['membership_groups'] = $this->load->view('account/customerpartner/wk_membership_groups', $data);
				}

		}
		return $this->load->view('customerpartner/registerseller', $data);
	}

	public function sellerprofile(){


	  if($this->config->get('module_marketplace_status')) {

	      $this->load->model('account/customerpartner');

	      $this->load->language('customerpartner/profile');

				if (isset($this->request->get['product_id']) && $this->request->get['product_id']) {
				  $check_seller = $this->model_account_customerpartner->getProductSellerDetails($this->request->get['product_id']);

				  $seller_id = 0;

				  if (isset($check_seller['customer_id']) && $check_seller['customer_id']) {
				      $seller_id = $check_seller['customer_id'];
				  }
				  $data['text_seller_information'] = $this->language->get('text_seller_information');

				  $this->load->model('customerpartner/information');

				  $data['informations'] = array();

				  $informations = $this->model_customerpartner_information->getSellerInformations($seller_id);

				  if ($informations) {
				    $count = 0;

				    foreach ($informations as $result) {
				      $data['informations'][] = array(
				        'title' => $result['title'],
				        'href'  => $this->url->link('information/information', 'information_id=' . $result['information_id'])
				      );

				      $count++;

				      if ($count == 3) {
				        break;
				      }
				    }
				  }
				}

	      if ($this->config->get('shipping_wk_custom_shipping_status') && isset($this->session->data['shipping_address']['postcode']) && isset($this->request->get['product_id']) && $this->request->get['product_id']) {

	          $check_seller = $this->model_account_customerpartner->getProductSellerDetails($this->request->get['product_id']);

	          $seller_id = 0;

	          if (isset($check_seller['customer_id']) && $check_seller['customer_id']) {
	              $seller_id = $check_seller['customer_id'];
	          }

	          $weight = 0;

	          if (isset($product_info['weight']) && $product_info['weight']) {
	              $weight = $product_info['weight'];
	          }

	          $max_days = $this->model_account_customerpartner->getMinDays($seller_id,$this->session->data['shipping_address']['postcode'],$weight);

	          if (isset($max_days['max_days']) && $max_days['max_days']) {
	              $date = new DateTime(date('Y-m-d', strtotime("+".$max_days['max_days']." days")));

	              $data['delivery_date'] = $date->format('Y-m-d');

	              $data['text_delivery_date'] = $this->language->get('text_delivery_date');
	          }
	      }
	          $data['showSellerInfo'] = false;
	          $data['wk_custome_field_wkcustomfields'] = true;
	          $customFields = array();
	          $data['customFields'] = array();

	          $customFields = $this->model_account_customerpartner->getProductCustomFields($this->request->get['product_id']);

	          foreach ($customFields as $key => $value) {
	              $customFieldsName = $this->model_account_customerpartner->getCustomFieldName($value['fieldId']);

	              $customFieldsOptionId = $this->model_account_customerpartner->getCustomFieldOptionId($this->request->get['product_id'],$value['fieldId']);

	              $customFieldValue = '';
	              foreach ($customFieldsOptionId as $key => $option) {
	                      if(is_numeric($option['option_id'])){
	                          $customFieldValue .= $this->model_account_customerpartner->getCustomFieldOption($option['option_id']).", ";
	                      }else{
	                          $customFieldValue = $option['option_id'];
	                      }
	              }
	              $data['customFields'][] = array(
	                  'fieldName' =>  $customFieldsName,
	                  'fieldValue'    =>  trim($customFieldValue,', '),
	              );
	          }

	          $checkSellerOwnProduct = $this->model_account_customerpartner->checkSellerOwnProduct($this->request->get['product_id']);

	          if ($checkSellerOwnProduct && !$this->config->get('marketplace_sellerbuyproduct')) {
	              $data['allowedProductBuy'] = false;
	          }else{
	              $data['allowedProductBuy'] = true;
	          }

	          /**
	           * add seller information on the product page through code end
	           */

	          if ($this->config->get('marketplace_seller_info_by_module') && !$this->config->get('marketplace_seller_info_hide')) {

	              $check_seller = $this->model_account_customerpartner->getProductSellerDetails($this->request->get['product_id']);

	              $this->load->model('customerpartner/master');

	              if ($check_seller) {

	                  $this->load->model('customerpartner/master');

	                  $partner = $this->model_customerpartner_master->getProfile($check_seller['customer_id']);

	                  switch ($this->config->get('marketplace_product_name_display')) {
	                      case 'sn':
	                          $data['info_name'] = $partner['firstname']. ' ' .$partner['lastname'];
	                          break;

	                      case 'cn':
	                          $data['info_name'] = $partner['companyname'];
	                          break;

	                      case 'sncn':
	                          $data['info_name'] = $partner['firstname']. ' ' .$partner['lastname'].' '.'And'.' '.$partner['companyname'];
	                          break;
	                  }

	                  switch ($this->config->get('marketplace_product_image_display')) {
	                      case 'avatar':
	                          if ($partner['avatar'] && file_exists(DIR_IMAGE . $partner['avatar'])) {
	                              $data['info_image'] = $this->model_tool_image->resize($partner['avatar'], 80, 80);
	                          } else if($this->config->get('marketplace_default_image_name') && file_exists(DIR_IMAGE . $this->config->get('marketplace_default_image_name'))) {
	                              if($partner['avatar'] != 'removed') {
	                                  $data['info_image'] = $this->model_tool_image->resize($this->config->get('marketplace_default_image_name'), 80, 80);
	                              } else {
	                                  $data['info_image'] = '';
	                              }
	                          }else{
	                              $data['info_image'] = $this->model_tool_image->resize($this->config->get('config_logo'), 80, 80);
	                          }
	                          break;

	                      case 'companylogo':
	                          if ($partner['companylogo'] && file_exists(DIR_IMAGE . $partner['companylogo'])) {
	                              $data['info_image'] = $this->model_tool_image->resize($partner['companylogo'], 80, 80);
	                          } else if($this->config->get('marketplace_default_image_name') && file_exists(DIR_IMAGE . $this->config->get('marketplace_default_image_name'))) {
	                              if($partner['companylogo'] != 'removed') {
	                                  $data['info_image'] = $this->model_tool_image->resize($this->config->get('marketplace_default_image_name'), 80, 80);
	                              } else {
	                                  $data['info_image'] = '';
	                              }
	                          }else{
	                              $data['info_image'] = $this->model_tool_image->resize($this->config->get('config_logo'), 80, 80);
	                          }
	                          break;

	                      case 'companybanner':
	                          if ($partner['companybanner'] && file_exists(DIR_IMAGE . $partner['companybanner'])) {
	                              $data['info_image'] = $this->model_tool_image->resize($partner['companybanner'], 80, 80);
	                          } else if($this->config->get('marketplace_default_image_name') && file_exists(DIR_IMAGE . $this->config->get('marketplace_default_image_name'))) {
	                              if($partner['companybanner'] != 'removed') {
	                                  $data['info_image'] = $this->model_tool_image->resize($this->config->get('marketplace_default_image_name'), 80, 80);
	                              } else {
	                                  $data['info_image'] = '';
	                              }
	                          }else{
	                              $data['info_image'] = $this->model_tool_image->resize($this->config->get('config_logo'), 80, 80);
	                          }
	                          break;
	                  }

	                  $data['review_fields'] = $this->model_customerpartner_master->getAllReviewFields();

	                  foreach ($data['review_fields'] as $key => $review_field) {
	                    $data['review_fields'][$key]['field_value'] = $this->model_customerpartner_master->getAllAverageFeedback($check_seller['customer_id'],$review_field['field_id']);
	                  }

	                  $data['info_feedback_total'] = round($this->model_customerpartner_master->getAverageFeedback($check_seller['customer_id']));

	                  $data['info_total_products'] = $this->model_customerpartner_master->getPartnerCollectionCount($check_seller['customer_id']);

	                  $data['info_heading_text'] = $this->language->get('text_seller_info_heading');
	                  $data['info_price_text']   = $this->language->get('text_seller_info_price');
	                  $data['info_value_text']   = $this->language->get('text_seller_info_value');
	                  $data['info_quality_text'] = $this->language->get('text_seller_info_quality');
	                  $data['info_product_text'] = $this->language->get('text_seller_info_product');
	                  $data['showSellerRating']  = $this->config->get('text_seller_info_product');
	                  $data['loadProfile'] = $this->url->link('customerpartner/profile&id='.$check_seller['customer_id'],'',true);

	                  $data['showSellerInfo'] = true;
	              }
	          }
	  }else{
	      $data['wk_custome_field_wkcustomfields'] = false;
	      $data['allowedProductBuy'] = true;
	      $data['showSellerInfo'] = false;
	  }
	  return $this->load->view('customerpartner/sellerprofile', $data);
	}


}
?>