<?php
class ControllerExtensionModuleUpsell extends Controller {
	public function index() {

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('extension/module/upsell', '', true);
			$this->response->redirect($this->url->link('account/login', '', true));
		}

		if (!$this->config->get('module_wk_upsell_upsell_status') || !$this->config->get('module_marketplace_status')) {
			$this->session->data['redirect'] = $this->url->link('extension/module/upsell', '', true);
			$this->response->redirect($this->url->link('account/account', '', true));
		}
		$this->load->language('extension/module/upsell');

		$language_id = $this->config->get('config_language_id');

		$this->document->setTitle($this->config->get('wk_listing_menulink_title')[$language_id]);

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->config->get('wk_listing_menulink_title')[$language_id],
			'href' => $this->url->link('extenstion/module/upsell', '', true)
		);

		$data['text_upsell'] = $this->config->get('wk_listing_menulink_title')[$language_id];
		$data['wk_productslider_productnum'] = 50;
		$theme = $this->config->get('wk_listing_theme');
		$data['wk_productslider_productrow'] = $this->config->get('wk_listing_bundles_page');
		$data['wk_productslider_auto'] = 1;
		$data['button_cart'] = $this->language->get('button_cart');
		$data['countdown_status'] = $this->config->get('wk_upsell_countdown_status');
		$data['quantity_status'] = $this->config->get('wk_upsell_units_status');
		$countdown_format = $this->config->get('wk_upsell_countdown_syntax')[$language_id];
		$countdowntime_format = $this->config->get('wk_upsell_countdowntime_syntax')[$language_id];
		$quantity_format = $this->config->get('wk_upsell_units_syntax')[$language_id];
		$data['listing_status'] = $this->config->get('wk_listing_status');
		$data['uheader1'] = html_entity_decode($this->config->get('wk_listing_upselling_details')[$language_id]);
		$data['uheader2'] = html_entity_decode($this->config->get('wk_listing_upselling_details2')[$language_id]);

		// $find_count = array(
		// 	'{days}',
		// 	'{hours}',
		// 	'{minutes}',
		// 	'{seconds}'
		// 	);
		//
		// $replace_count = array(
		// 	'days'		=> '%D',
		// 	'hours'		=> '%H',
		// 	'minutes'	=> '%M',
		// 	'seconds'	=> '%S'
		// 	);

		$countdown_format = html_entity_decode(str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim($countdown_format))));

		$data['countdowntime_format'] = $countdowntime_format = html_entity_decode(str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim($countdowntime_format))));

		$this->document->addStyle('catalog/view/javascript/jquery/owl-carousel/owl.carousel.css');
		$this->document->addScript('catalog/view/javascript/jquery/owl-carousel/owl.carousel.min.js');
		$this->document->addScript('catalog/view/javascript/promo/countdown.js');

		$this->document->addStyle('catalog/view/theme/default/stylesheet/promo/' . $theme . '.css');

		//LOAD MODEL FILES
		$this->load->model('account/promotional');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');

		$iwidth = $this->config->get('wk_listing_picture_width') ? $this->config->get('wk_listing_picture_width') : 200;
		$iheight = $this->config->get('wk_listing_picture_height') ? $this->config->get('wk_listing_picture_height') : 200;

		$current_date = date('Y-m-d H:i:s');
		$date_now = strtotime($current_date);

		$products = $this->model_account_promotional->getUpsellProducts();

		$data['products'] = array();

		if (empty($products)) {
		 $this->session->data['redirect'] = $this->url->link('extension/module/upsell', '', true);
		 $this->response->redirect($this->url->link('account/customerpartner/upsell', '', true));
	  }

		foreach ($products as $product_info) {
			if ($product_info) {
				if ($product_info['image'] && is_file(DIR_IMAGE . $product_info['image'])) {
					$image = $this->model_tool_image->resize($product_info['image'], $iwidth, $iheight);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $iwidth, $iheight);
				}

				if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if ((float)$product_info['special']) {
					$special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}

				if ($this->config->get('config_review_status')) {
					$rating = $product_info['rating'];
				} else {
					$rating = false;
				}

				if (isset($_COOKIE['time_diff'])) {
					$time_diff = $_COOKIE['time_diff'] * 3600;
				}

				$date_before = strtotime($product_info['date_end']);

				if (isset($time_diff) && $time_diff) {
					$date_end = $date_before + $time_diff;
				} else {
					$date_end = $date_before;
				}

				$end_date = date('Y-m-d H:i:s', $date_end);
				$duration = ($date_before - $date_now);

				if ($duration > (86400)) {
					$day = true;
				} else {
					$day = false;
				}

				$data['products'][] = array(
					'product_id'	=> $product_info['product_id'],
					'thumb'			=> $image,
					'name'			=> $product_info['name'],
					'description'	=> utf8_substr(strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8')), 0, 100) . '..',
					'price'			=> $price,
					'minimum'		=> $product_info['minimum'] > 0 ? $product_info['minimum'] : 1,
					'countdown_status'	=> $product_info['countdown_status'],
					'quantity_status'	=> $product_info['quantity_status'],
					'quantity'		=> $product_info['quantity'],
					'formatted_quantity' => html_entity_decode(str_replace('{units}', $product_info['quantity'], $quantity_format)),
					'date_end'		=> $end_date,
					'dateSeconds'	=> $date_before,
					'special'		=> $special,
					'rating'		=> $rating,
					'href'			=> $this->url->link('product/product', 'product_id=' . $product_info['product_id'], true),
					'countdown_format'	=> $day ? $countdown_format : $countdowntime_format,
					'duration'  => $duration,
					'product_option' => json_decode($product_info['options'], true),
					'product_option_name' => json_decode($product_info['option_name'], true),
					'id' => $product_info['id']
				);
			}
		}

		if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
			$data['base'] = $this->config->get('config_ssl');
		} else {
			$data['base'] = $this->config->get('config_url');
		}

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('extension/module/upsell', $data));
	}
}
