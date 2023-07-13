<?php
class ControllerAccountCustomerpartneraddshippingmod extends Controller {

	private $error = array();

	public function index() {

		if (!$this->customer->isLogged() || !$this->config->get('module_marketplace_status') || ! $this->config->get('shipping_wk_custom_shipping_seller_details') || !$this->config->get('shipping_wk_custom_shipping_status')) {
			$this->session->data['redirect'] = $this->url->link('account/customerpartner/add_shipping_mod', '', true);
			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->model('account/customerpartner');

		$data['chkIsPartner'] = $this->model_account_customerpartner->chkIsPartner();

		if(!$data['chkIsPartner'] || (isset($this->session->data['marketplace_seller_mode']) && !$this->session->data['marketplace_seller_mode']))
			$this->response->redirect($this->url->link('account/account', '', true));

		$this->document->addStyle('catalog/view/theme/default/stylesheet/MP/sell.css');

		$this->load->model('account/add_shipping_mod');

		$data = array_merge($data, $this->language->load('account/customerpartner/add_shipping_mod'));

		
		$this->document->setTitle($this->language->get('heading_title'));

		$matrix_filter_array = array(
							  'filter_country',
							  'filter_zip_to',
							  'filter_zip_from',
							  'filter_price',
							  'filter_weight_to',
							  'filter_weight_from',
							  'page',
							  'sort',
							  'order',
							  'start',
							  'limit',
							  );

		$url = '';

		foreach ($matrix_filter_array as $unsetKey => $key) {

			if (isset($this->request->get[$key])) {
				$matrix_filter_array[$key] = $this->request->get[$key];
			} else {
				if ($key=='page')
					$matrix_filter_array[$key] = 1;
				elseif($key=='sort')
					$matrix_filter_array[$key] = 'cs.id';
				elseif($key=='order')
					$matrix_filter_array[$key] = 'ASC';
				elseif($key=='start')
					$matrix_filter_array[$key] = ($matrix_filter_array['page'] - 1) * 10;
				elseif($key=='limit')
					$matrix_filter_array[$key] = 10;
				else
					$matrix_filter_array[$key] = null;
			}
			unset($matrix_filter_array[$unsetKey]);

			if(isset($this->request->get[$key])){
				if ($key=='filter_country')
					$url .= '&'.$key.'=' . urlencode(html_entity_decode($matrix_filter_array[$key], ENT_QUOTES, 'UTF-8'));
				else
					$url .= '&'.$key.'='. $matrix_filter_array[$key];
			}
		}

		$results = $this->model_account_add_shipping_mod->viewdata($matrix_filter_array);

		$product_total = $this->model_account_add_shipping_mod->viewtotalentry($matrix_filter_array);

		$data['result_shipping'] = array();

		if ($results){
			foreach ($results as $result) {

				$data['result_shipping'][] = array(
													'selected' => false,
													'id' => $result['id'],
													'price' => $result['price'],
													'country' => $result['country_code'],
													'zip_to' => $result['zip_to'],
													'zip_from' => $result['zip_from'],
													'weight_from' => $result['weight_from'],
													'weight_to' => $result['weight_to'],
													'max_days'	=> $result['max_days'],
												);

			}
		}

		$data['shippings'] = $this->getShippings($this->customer->getId());
		// Event Shipping Pagination

		$event_pagination = new Pagination();
		$event_pagination->total = count($data['shippings']['event_shipping']);
		$event_pagination->page = $matrix_filter_array['page'];
		$event_pagination->limit = 10;
		$event_pagination->text = $this->language->get('text_pagination');
		$event_pagination->url = $this->url->link('account/customerpartner/add_shipping_mod', $url . '&page={page}', true);

		$data['event_pagination'] = $event_pagination->render();
		$data['event_results'] = sprintf($this->language->get('text_pagination'), (count($data['shippings']['event_shipping'])) ? (($matrix_filter_array['page'] - 1) * 10) + 1 : 0, ((($matrix_filter_array['page'] - 1) * 10) > (count($data['shippings']['event_shipping']) - 10)) ? count($data['shippings']['event_shipping']) : ((($matrix_filter_array['page'] - 1) * 10) + 10), count($data['shippings']['event_shipping']), ceil(count($data['shippings']['event_shipping']) / 10));
		// Ends Here

      	$data['breadcrumbs'] = array();

      	$data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', true),
        	'separator' => false
      	);

      	$data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_account'),
			'href'      => $this->url->link('account/account', '', true),
        	'separator' => $this->language->get('text_separator')
      	);

      	$data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('account/customerpartner/add_shipping_mod'.$url, '', true),
        	'separator' => $this->language->get('text_separator')
      	);

		if (isset($this->session->data['attention'])) {
			$data['attention'] = $this->session->data['attention'];
			unset($this->session->data['attention']);
		}else{
			$data['attention'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		}else{
			$data['success'] = '';
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['add'] = $this->url->link('account/customerpartner/add_shipping_mod/add', '', true);

		$data['action'] = $this->url->link('account/customerpartner/add_shipping_mod', '', true);

		$data['delete'] = $this->url->link('account/customerpartner/add_shipping_mod/delete', '', true);

		$data['back'] = $this->url->link('account/account', '', true);


		$url = '';

		foreach ($matrix_filter_array as $key => $value) {
			if(isset($this->request->get[$key])){
				if(!isset($this->request->get['order']) AND isset($this->request->get['sort']))
					$url .= '&order=DESC';
				if ($key=='filter_name' || $key=='filter_country')
					$url .= '&'.$key.'=' . urlencode(html_entity_decode($matrix_filter_array[$key], ENT_QUOTES, 'UTF-8'));
				elseif($key=='order')
					$url .= $value=='ASC' ? '&order=DESC' : '&order=ASC';
				elseif($key!='sort')
					$url .= '&'.$key.'='. $matrix_filter_array[$key];
			}
		}

		$data['sort_name'] = $this->url->link('account/customerpartner/add_shipping_mod', '&sort=name' . $url, true);
		$data['sort_country_code'] = $this->url->link('account/customerpartner/add_shipping_mod', '&sort=cs.country_code' . $url, true);
		$data['sort_price'] = $this->url->link('account/customerpartner/add_shipping_mod', '&sort=cs.price' . $url, true);
		$data['sort_zip_to'] = $this->url->link('account/customerpartner/add_shipping_mod', '&sort=cs.zip_to' . $url, true);
		$data['sort_zip_from'] = $this->url->link('account/customerpartner/add_shipping_mod', '&sort=cs.zip_from' . $url, true);
		$data['sort_weight_to'] = $this->url->link('account/customerpartner/add_shipping_mod', '&sort=cs.weight_to' . $url, true);
		$data['sort_weight_from'] = $this->url->link('account/customerpartner/add_shipping_mod', '&sort=cs.weight_from' . $url, true);

		$url = '';

		foreach ($matrix_filter_array as $key => $value) {
			if(isset($this->request->get[$key])){
				if(!isset($this->request->get['order']) AND isset($this->request->get['sort']))
					$url .= '&order=DESC';
				if ($key=='filter_name' || $key=='filter_country')
					$url .= '&'.$key.'=' . urlencode(html_entity_decode($matrix_filter_array[$key], ENT_QUOTES, 'UTF-8'));
				elseif($key!='page')
					$url .= '&'.$key.'='. $matrix_filter_array[$key];
			}
		}

		$matrix_pagination = new Pagination();
		$matrix_pagination->total = $product_total;
		$matrix_pagination->page = $matrix_filter_array['page'];
		$matrix_pagination->limit = 10;
		$matrix_pagination->text = $this->language->get('text_pagination');
		$matrix_pagination->url = $this->url->link('account/customerpartner/add_shipping_mod', $url . '&page={page}', true);

		$data['matrix_pagination'] = $matrix_pagination->render();
		$data['matrix_results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($matrix_filter_array['page'] - 1) * 10) + 1 : 0, ((($matrix_filter_array['page'] - 1) * 10) > ($product_total - 10)) ? $product_total : ((($matrix_filter_array['page'] - 1) * 10) + 10), $product_total, ceil($product_total / 10));

		foreach ($matrix_filter_array as $key => $value) {
			if($key!='start' AND $key!='end')
				$data[$key] = $value;
		}

		$data['isMember'] = true;
		if($this->config->get('module_wk_seller_group_status')) {
      		$data['module_wk_seller_group_status'] = true;
      		$this->load->model('account/customer_group');
			$isMember = $this->model_account_customer_group->getSellerMembershipGroup($this->customer->getId());
			if($isMember) {
				$allowedAccountMenu = $this->model_account_customer_group->getaccountMenu($isMember['gid']);
				if($allowedAccountMenu['value']) {
					$accountMenu = explode(',',$allowedAccountMenu['value']);
					if($accountMenu && !in_array('manageshipping:manageshipping', $accountMenu)) {
						$data['isMember'] = false;
					}
				}
			} else {
				$data['isMember'] = false;
			}
      	} else {
      		if(!is_array($this->config->get('marketplace_allowed_account_menu')) || !in_array('manageshipping', $this->config->get('marketplace_allowed_account_menu'))) {
      			$this->response->redirect($this->url->link('account/account','', true));
      		}
      	}

		$data['column_left'] = $this->load->Controller('common/column_left');
		$data['column_right'] = $this->load->Controller('common/column_right');
		$data['content_top'] = $this->load->Controller('common/content_top');
		$data['content_bottom'] = $this->load->Controller('common/content_bottom');
		$data['footer'] = $this->load->Controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$data['separate_view'] = false;

		$data['separate_column_left'] = '';

		if ($this->config->get('marketplace_separate_view') && isset($this->session->data['marketplace_separate_view']) && $this->session->data['marketplace_separate_view'] == 'separate') {
			$data['separate_view'] = true;
			$data['column_left'] = '';
			$data['column_right'] = '';
			$data['content_top'] = '';
			$data['content_bottom'] = '';
			$data['separate_column_left'] = $this->load->controller('account/customerpartner/column_left');
			$data['footer'] = $this->load->controller('account/customerpartner/footer');
			$data['header'] = $this->load->controller('account/customerpartner/header');
		}

		$this->response->setOutput($this->load->view('account/customerpartner/add_shipping_mod' , $data));
	}

	public function add() {

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/customerpartner/add_shipping_mod', '', true);
			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->model('account/customerpartner');

		$data['chkIsPartner'] = $this->model_account_customerpartner->chkIsPartner();

		if(!$data['chkIsPartner'] || (isset($this->session->data['marketplace_seller_mode']) && !$this->session->data['marketplace_seller_mode']))
			$this->response->redirect($this->url->link('account/account', '', true));

		$this->document->addStyle('catalog/view/theme/default/stylesheet/MP/sell.css');
		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment.js');
		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
		$this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');

		$this->load->model('account/add_shipping_mod');

		$data = array_merge($data, $this->language->load('account/customerpartner/add_shipping_mod'));

		$data['heading_title'] = $this->language->get('heading_title'). $this->language->get('heading_title_1');

		$this->document->setTitle($this->language->get('heading_title'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			
			if (isset($this->request->post['shipping_add_flatrate'])){
     			$this->request->post['shipping_add_flatrate'] = $this->currency->convert($this->request->post['shipping_add_flatrate'],$this->session->data['currency'],$this->config->get('config_currency'));
     			$this->model_account_add_shipping_mod->addFlatShipping($this->customer->getId(),$this->request->post['shipping_add_flatrate'],$this->request->post['status']);
      		}
			if (isset($this->request->post['seller_event_shipping_status']) && isset($this->request->post['seller_shipping_event_based'])) {
				$this->model_account_add_shipping_mod->addEventShipping($this->customer->getId(),$this->request->post['seller_shipping_event_based'],$this->request->post['seller_event_shipping_status']);
			}
			
			if (isset($this->request->post['seller_priority_shipping_status']) && isset($this->request->post['seller_shipping_priority_based'])) {
				$this->model_account_add_shipping_mod->addPriorityShipping($this->customer->getId(),$this->request->post['seller_shipping_priority_based'],$this->request->post['seller_priority_shipping_status']);
			}

			$files = $this->request->files;

			if(isset($files['up_file']['tmp_name']) AND $files['up_file']['tmp_name']){

				// csv check
				$csv_extention = explode('.', $files['up_file']['name']);

				if(isset($csv_extention[1]) AND $csv_extention[1] == 'csv'){

					$this->session->data['csv_post_shipping'] = $this->request->post;
					if ( $file = fopen( $files['up_file']['tmp_name'] , 'r' ) ) {

						// necessary if a large csv file
		            	set_time_limit(0);
		            	$separator = 'webkul';
		            	if(isset($this->request->post['separator']))
							$separator = $this->request->post['separator'];

						if(strlen($separator)>1){
							$this->error['warning'] = $this->language->get('entry_error_separator');
						}else{
							// remove chracters from separator
							$separator = preg_replace('/[a-z A-Z .]+/', ' ',$separator);
							if(strlen($separator)<1 || $separator==' ')
								$separator = ';';

							$this->session->data['csv_file_shipping'] = array();
							while ( ($line = fgetcsv ($file, 4096, $separator)) !== FALSE) {
								$this->session->data['csv_file_shipping'][] = $line;
							}

						}
					}
					$this->response->redirect($this->url->link('account/customerpartner/add_shipping_mod/matchdata', '', true));
				} else{
					$this->error['warning'] = $this->language->get('entry_error_csv');
				}
			} else {

           		$this->session->data['success'] = $this->language->get('text_success');

				$this->session->data['attention'] = $this->language->get('text_shipping_attention');

				$this->response->redirect($this->url->link('account/customerpartner/add_shipping_mod', '', true));

			}

		}
		
		$shippings = $this->getShippings($this->customer->getId());
		
		$form_arr = array(
			'shipping_add_flatrate',
			'shipping_add_flatrate_amount',
			'status',
			'seller_event_shipping_status',
			'seller_shipping_event_based',
			'seller_priority_shipping_status',
			'seller_shipping_high_priority_day',
			'seller_shipping_high_priority_amount',
			'seller_shipping_mid_priority_day',
			'seller_shipping_mid_priority_amount',
			'seller_shipping_low_priority_amount',
			'seller_shipping_low_priority_day',
			'event_shipping',
		);

		foreach ($form_arr as $key => $value) {
			if (isset($this->request->post[$value])) {

				if ($value == 'seller_shipping_event_based') {
					$data['shippings']['event_shipping'] = $this->request->post[$value];
				} else {
					$data['shippings'][$value] =  $this->request->post[$value];
				}
				
			} else if (isset($shippings[$value])) {
				
				if ($value == 'event_shipping') {
					if (!isset($data['shippings']['event_shipping'])) {
						$data['shippings'][$value] = $shippings['event_shipping'];
					}
					
				} else {
					$data['shippings'][$value] = $shippings[$value];
				}
				
			} else {

				if ($value == 'seller_shipping_event_based' || $value = 'event_shipping') {
					$data['shippings'][$value] = array();
				} else {
					$data['shippings'][$value] = '';
				}
			}
		 }
		 
	    $data['breadcrumbs'] = array();

	    $data['breadcrumbs'][] = array(
	      'text'      => $this->language->get('text_home'),
	      'href'      => $this->url->link('common/home', '', true),
	      'separator' => false
	    );

	    $data['breadcrumbs'][] = array(
	      'text'      => $this->language->get('text_account'),
	      'href'      => $this->url->link('account/account', '', true),
	      'separator' => $this->language->get('text_separator')
	    );

	    $data['breadcrumbs'][] = array(
	      'text'      => $this->language->get('heading_title'),
	      'href'      => $this->url->link('account/customerpartner/add_shipping_mod', '', true),
	      'separator' => $this->language->get('text_separator')
	    );

		if (isset($this->session->data['error_warning'])) {
			$this->error['warning'] = $this->session->data['error_warning'];
			unset($this->session->data['error_warning']);
		}

		if (isset($this->session->data['event_shipping_error']) && $this->session->data['event_shipping_error']) {
			$data['error_shipping'] = true;
			unset($this->session->data['event_shipping_error']);
		} else {
			$data['error_shipping'] = false;
		}

		if (isset($this->session->data['attention'])) {
			$data['attention'] = $this->session->data['attention'];
			unset($this->session->data['attention']);
		} else {
			$data['attention'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['action'] = $this->url->link('account/customerpartner/add_shipping_mod/add', '', true);

		$data['back'] = $this->url->link('account/customerpartner/add_shipping_mod', '', true);

		$data['isMember'] = true;
		if($this->config->get('module_wk_seller_group_status')) {
      		$data['module_wk_seller_group_status'] = true;
      		$this->load->model('account/customer_group');
			$isMember = $this->model_account_customer_group->getSellerMembershipGroup($this->customer->getId());
			if($isMember) {
				$allowedAccountMenu = $this->model_account_customer_group->getaccountMenu($isMember['gid']);
				if($allowedAccountMenu['value']) {
					$accountMenu = explode(',',$allowedAccountMenu['value']);
					if($accountMenu && !in_array('manageshipping:manageshipping', $accountMenu)) {
						$data['isMember'] = false;
					}
				}
			} else {
				$data['isMember'] = false;
			}
    	}
	
		$data['column_left'] = $this->load->Controller('common/column_left');
		$data['column_right'] = $this->load->Controller('common/column_right');
		$data['content_top'] = $this->load->Controller('common/content_top');
		$data['content_bottom'] = $this->load->Controller('common/content_bottom');
		$data['footer'] = $this->load->Controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$data['separate_view'] = false;

		$data['separate_column_left'] = '';

		if ($this->config->get('marketplace_separate_view') && isset($this->session->data['marketplace_separate_view']) && $this->session->data['marketplace_separate_view'] == 'separate') {
			$data['separate_view'] = true;
			$data['column_left'] = '';
			$data['column_right'] = '';
			$data['content_top'] = '';
			$data['content_bottom'] = '';
			$data['separate_column_left'] = $this->load->controller('account/customerpartner/column_left');
			$data['footer'] = $this->load->controller('account/customerpartner/footer');
			$data['header'] = $this->load->controller('account/customerpartner/header');
		}

		$this->response->setOutput($this->load->view('account/customerpartner/add_shipping_form' , $data));

	}

	public function matchdata(){

		$this->load->language('account/customerpartner/add_shipping_mod');

		if (isset($this->session->data['csv_post_shipping']) AND isset($this->session->data['csv_file_shipping'])) {

			$post = $this->session->data['csv_post_shipping'];
			$files = $this->session->data['csv_file_shipping'];
			$fields = false;
			if(isset($files[0]))
				$fields = $files[0];

		    $num = count($fields);
		    //separator check
		    if($num < 2 ){
		    	$this->error['warning'] = $this->language->get('entry_error_separator');
		    	$this->index();
		    }else{
			    $this->stepTwo($fields);
			}
		} else {
			$this->error['warning'] = $this->language->get('error_somithing_wrong');
			$this->index();
		}

	}

	public function stepTwo($fields = array()) {

		if (!isset($this->session->data['csv_file_shipping']))
			return $this->matchdata();

		$this->load->language('account/customerpartner/add_shipping_mod');

		$data['heading_title'] = $this->language->get('heading_title'). $this->language->get('heading_title_2');

		$this->document->setTitle($this->language->get('heading_title'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $fields == array()) {

			//insert shipping
			foreach ($this->request->post as $chkpost) {
				if($chkpost==''){
					$this->error['warning'] = $this->language->get('error_fileds');
					break;
				}
			}

			if (isset($this->error['warning']) AND $this->error['warning']) {
				$fields = $this->session->data['csv_file_shipping'][0];
			} else {

				$message = $this->matchDataTwo();

				if ($message['success'])
					$this->session->data['success'] = $this->language->get('text_shipping').$message['success'];
				if ($message['warning'])
					$this->session->data['error_warning'] = $this->language->get('fields_error').$message['warning'];
				if ($message['update'])
					$this->session->data['attention'] = $this->language->get('text_attention').$message['update'];

				unset($this->session->data['csv_file_shipping']);
				unset($this->session->data['csv_post_shipping']);

				$this->response->redirect($this->url->link('account/customerpartner/add_shipping_mod', '', true));

			}

		}

		$data['heading_title'] = $this->language->get('heading_title'). $this->language->get('heading_title_2');
		$data['error_warning_authenticate'] = $this->language->get('error_warning_authenticate');
		$data['text_mpshipping']=$this->language->get('text_mpshipping');
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_back');
		$data['text_separator_info'] = $this->language->get('text_separator_info');

 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

  		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', true),
      		'separator' => false
   		);

   		$data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_account'),
			'href'      => $this->url->link('account/account', '', true),
        	'separator' => $this->language->get('text_separator')
      	);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('account/customerpartner/add_shipping_mod', '', true),
      		'separator' => ' :: '
   		);

		// send fields data
		$data['fields'] = $fields;

		// shipping data
		$data['shippingTable'] = array('country_code','zip_to','zip_from','price','weight_to','weight_from','max_days');

		$data['action'] = $this->url->link('account/customerpartner/add_shipping_mod/stepTwo', '', true);

		$data['cancel'] = $this->url->link('account/customerpartner/add_shipping_mod', '', true);

		$data['isMember'] = true;
		if($this->config->get('module_wk_seller_group_status')) {
      		$data['module_wk_seller_group_status'] = true;
      		$this->load->model('account/customer_group');
			$isMember = $this->model_account_customer_group->getSellerMembershipGroup($this->customer->getId());
			if($isMember) {
				$allowedAccountMenu = $this->model_account_customer_group->getaccountMenu($isMember['gid']);
				if($allowedAccountMenu['value']) {
					$accountMenu = explode(',',$allowedAccountMenu['value']);
					if($accountMenu && !in_array('manageshipping:manageshipping', $accountMenu)) {
						$data['isMember'] = false;
					}
				}
			} else {
				$data['isMember'] = false;
			}
      	}

		$data['column_left'] = $this->load->Controller('common/column_left');
		$data['column_right'] = $this->load->Controller('common/column_right');
		$data['content_top'] = $this->load->Controller('common/content_top');
		$data['content_bottom'] = $this->load->Controller('common/content_bottom');
		$data['footer'] = $this->load->Controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$data['separate_view'] = false;

		$data['separate_column_left'] = '';

		if ($this->config->get('marketplace_separate_view') && isset($this->session->data['marketplace_separate_view']) && $this->session->data['marketplace_separate_view'] == 'separate') {
			$data['separate_view'] = true;
			$data['column_left'] = '';
			$data['column_right'] = '';
			$data['content_top'] = '';
			$data['content_bottom'] = '';
			$data['separate_column_left'] = $this->load->controller('account/customerpartner/column_left');
			$data['footer'] = $this->load->controller('account/customerpartner/footer');
			$data['header'] = $this->load->controller('account/customerpartner/header');
		}

		$this->response->setOutput($this->load->view('account/customerpartner/add_shipping_mod_next' , $data));

	}

	private function matchDataTwo(){

		$this->load->model('account/add_shipping_mod');
		$this->load->language('account/customerpartner/add_shipping_mod');

		if(!isset($this->session->data['csv_file_shipping']))
			$this->response->redirect($this->url->link('account/customerpartner/add_shipping_mod', '', true));

		$files = $this->session->data['csv_file_shipping'];
		$post = $this->request->post;

		// remove index line from array
		$fields = $files[0];
		$files = array_slice($files, 1);

		$shippingDatas = array();
		$i = 0;
		$num = count($files);

	    foreach ($files as $line) {
	    	$entry = true;

	    	foreach($post as $postchk){
	    		if(!isset($line[$postchk]) || trim($line[$postchk])==''){
	    			$entry = false;
	    			break;
	    		}
	    	}

	    	if($entry){
	    		$shippingDatas[$i] = array();
	    		foreach($post as $key=>$postchk){
		    		$shippingDatas[$i][$key] = $line[$postchk];
	    		}
	    		$i++;
	    	}

	    }

	    $updatechk = 0;
	    foreach ($shippingDatas as $newShipping) {
	    	$result = $this->model_account_add_shipping_mod->addShipping($newShipping);
	    	if($result)
	    		$updatechk++;
	    }

	    return array('success' => $i-$updatechk,
	    			 'warning' => $num-$i,
	    			 'update' => $updatechk,
	    			);
	}

	public function delete() {

    	$this->load->model('account/add_shipping_mod');
		$this->load->language('account/customerpartner/add_shipping_mod');

		$url='';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		if (isset($this->request->post['selected'])) {
			foreach ($this->request->post['selected'] as $id) {
				$this->model_account_add_shipping_mod->deleteentry($id);
	  		}

			$this->session->data['success'] = $this->language->get('text_success_delete');

			$this->response->redirect($this->url->link('account/customerpartner/add_shipping_mod', '' . $url, true));
		}

    	$this->response->redirect($this->url->link('account/customerpartner/add_shipping_mod', '' . $url, true));
	}
	
	public function getShippings($customer_id = 0) {
		$this->load->model('account/add_shipping_mod');

		$flatrate = $this->model_account_add_shipping_mod->getFlatShipping($customer_id);
		$event_shipping = $this->model_account_add_shipping_mod->getEventShipping($customer_id);
		$priority_shipping = $this->model_account_add_shipping_mod->getPriorityShipping($customer_id);
		

		$data['shipping_add_flatrate'] = 0;
		if(isset($flatrate['amount'])) {
			$data['shipping_add_flatrate_amount'] = $data['shipping_add_flatrate'] = sprintf ("%.2f", $this->currency->convert($flatrate['amount'],$this->config->get('config_currency'),$this->session->data['currency']));
			$data['shipping_add_flatrate'] = $this->currency->format($flatrate['amount'],$this->session->data['currency']);
		}

		$data['status'] = 0;

		if(isset($flatrate['status'])){
			$data['status'] = $flatrate['status'];
		}

		if (isset($event_shipping['shipping'])) {
			
			$event_shipping['shipping'] = (array)json_decode($event_shipping['shipping']);
			$data['seller_event_shipping_status'] = $event_shipping['status'];
			
			if (!empty($event_shipping['shipping'])) {
				foreach ($event_shipping['shipping'] as $key => $value) {
					$data['event_shipping'][] = (array)$value;
				}
			} else if (isset($this->request->post['seller_shipping_event_based'])) {
			
				$data['event_shipping'] = $this->request->post['seller_shipping_event_based'];
			} else {	
				$data['event_shipping'] = array();
			}
		} else {
			$data['event_shipping'] = array();
		}
		
		if (isset($priority_shipping['high']) && $priority_shipping['high']) {
			$priority_shipping['high'] = (array)json_decode($priority_shipping['high']);
      		$data['seller_shipping_high_priority_day'] = $priority_shipping['high']['day'];
			$data['seller_shipping_high_priority_amount'] = $priority_shipping['high']['amount'];
		}
		if (isset($priority_shipping['low']) && $priority_shipping['low']) {
			$priority_shipping['low'] = (array)json_decode($priority_shipping['low']);
      		$data['seller_shipping_low_priority_day'] = $priority_shipping['low']['day'];
			$data['seller_shipping_low_priority_amount'] = $priority_shipping['low']['amount'];
		}
		if (isset($priority_shipping['medium']) && $priority_shipping['medium']) {
			$priority_shipping['medium'] = (array)json_decode($priority_shipping['medium']);
      		$data['seller_shipping_mid_priority_day'] = $priority_shipping['medium']['day'];
			$data['seller_shipping_mid_priority_amount'] = $priority_shipping['medium']['amount'];
		}
		
		if (isset($priority_shipping['status']) && $priority_shipping['status']) {
			$data['seller_priority_shipping_status'] = $priority_shipping['status'] ;
		}
		
		return $data ;
	}

	public function validate() {

		$event_based_shipping = array();
		$event_shipping = array();

		if (isset($this->request->post['seller_priority_shipping_status'])) {
			$priority_shipping = array();
			$priority_shipping['high'] = array(
				'day'		 => $this->request->post['seller_shipping_high_priority_day'],
				'amount' => $this->request->post['seller_shipping_high_priority_amount'],
			);
			$priority_shipping['mid'] = array(
				'day'		 => $this->request->post['seller_shipping_mid_priority_day'],
				'amount' => $this->request->post['seller_shipping_mid_priority_amount'],
			);
			$priority_shipping['low'] = array(
				'day'		 => $this->request->post['seller_shipping_low_priority_day'],
				'amount' => $this->request->post['seller_shipping_low_priority_amount'],
			);
			$this->request->post['seller_shipping_priority_based'] = $priority_shipping;

		}

		if (isset($this->request->post['date_from']) && is_array($this->request->post['date_from'])) {
			$temp =  $this->request->post['date_from'];
			foreach ($temp as $key => $value) {
				$event_based_shipping[$key]['datefrom'] = $value;
			}
		}

		if (isset($this->request->post['date_to']) && is_array($this->request->post['date_to'])) {
			$temp =  $this->request->post['date_to'];
			foreach ($temp as $key => $value) {
				$event_based_shipping[$key]['dateto'] = $value;
			}
		}

		if (isset($this->request->post['prefix']) && is_array($this->request->post['prefix'])) {
			$temp =  $this->request->post['prefix'];
			foreach ($temp as $key => $value) {
				$event_based_shipping[$key]['prefix'] = $value;
			}
		}
		if (isset($this->request->post['type']) && is_array($this->request->post['type'])) {
			$temp =  $this->request->post['type'];
			foreach ($temp as $key => $value) {
				$event_based_shipping[$key]['type'] = $value;
			}
		}

		if (isset($this->request->post['amount']) && is_array($this->request->post['amount'])) {
			$temp =  $this->request->post['amount'];
			foreach ($temp as $key => $value) {
				$event_based_shipping[$key]['amount'] = $value;
			}
		}

		foreach ($event_based_shipping as $key => $value) {
			$error = false ;

			if (isset($value['datefrom']) && $value['datefrom'] && isset($value['dateto']) && $value['dateto']) {
				if (isset($event_shipping) && !empty($event_shipping)) {
					foreach ($event_shipping as $datekey => $datevalue) {

						if ((isset($datevalue['datefrom']) && isset($datevalue['dateto']) && ($value['datefrom'] >= $datevalue['datefrom']) && $value['datefrom'] <= $datevalue['dateto']) || ($datevalue['datefrom'] >= $value['datefrom'] && $datevalue['datefrom'] <= $value['dateto'])) {
							$this->session->data['event_shipping_error'] = true;
							$this->error['warning'] = $this->language->get('error_date_range');
							$error = true;
						}

						if ((isset($datevalue['datefrom']) && isset($datevalue['dateto']) && ($value['dateto'] >= $datevalue['datefrom']) && $value['datefrom'] <= $datevalue['dateto']) || ($datevalue['dateto'] >= $value['datefrom'] && $datevalue['dateto'] <= $value['dateto'])) {
							$this->session->data['event_shipping_error'] = true;
							$this->error['warning'] = $this->language->get('error_date_range');
							$error = true;
						}
						
					}
				}
				$event_shipping[$key]['datefrom'] = $value['datefrom'];
				$event_shipping[$key]['dateto'] = $value['dateto'];
			} else {
				$event_shipping[$key]['dateto'] = 0;
				$event_shipping[$key]['datefrom'] = 0;
				$error = true;
			}
			
			if (isset($value['prefix'])) {
				$event_shipping[$key]['prefix'] = $value['prefix'];
			}
			
			
			if (isset($value['type'])) {
				$event_shipping[$key]['type'] = $value['type'];
			}
			
			if (isset($value['amount']) && $value['amount'] && $value['amount'] > 0 ) {
				$event_shipping[$key]['amount'] = $value['amount'];
			} else {
				$event_shipping[$key]['amount'] = $value['amount'];
				$error = true;
			}
			
			if (isset($event_shipping[$key]['datefrom']) && isset($event_shipping[$key]['dateto']) && $event_shipping[$key]['datefrom'] >= $event_shipping[$key]['dateto']) {
				$error = true;
			}
			
			$this->request->post['seller_shipping_event_based'] = $event_shipping ;

			if ($error && !isset($this->error['warning'])) {
				$this->session->data['event_shipping_error'] = true;
				$this->error['warning'] = $this->language->get('error_event_fields');
			}
		
		}

		return !$this->error ;
		
	}
}
?>
