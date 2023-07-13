<?php
class ControllerExtensionShippingwkcustomshipping extends Controller {

	private $error = array();
	private $data = array();


	public function __construct($registry) {
		parent::__construct($registry);
   
		//load extension language here
		$this->data = $this->load->language('extension/shipping/wk_custom_shipping');
   
		//load extension model here
		$this->load->model('extension/shipping/wk_custom_shipping');
   
		$this->load->model('setting/setting');
   
		$this->load->model('setting/extension');
   
	  }
   
   
	   public function install() {
   
		 $extensions = $this->model_setting_extension->getInstalled('module');
   
		 // check for the POS insatlled
			 if (!in_array('marketplace', $extensions)) {
				 die('<h3>' . $this->language->get('error_installation') . '</h3>');
			 }
   
		 $this->model_extension_shipping_wk_custom_shipping->cerateTable();
	   }
   
	   public function uninstall() {
   
		 // check for the POS uninstalled
		 $extensions = $this->model_setting_extension->getInstalled('module');
   
		 if (!in_array('marketplace', $extensions)) {
				 die('<h3>' . $this->language->get('error_installation') . '</h3>');
			 }
   
		 $this->model_extension_shipping_wk_custom_shipping->deleteTable();
   
	   }

	public function index() {

		$this->document->setTitle($this->language->get('heading_title'));

		$extensions = $this->model_setting_extension->getInstalled('module');

      // check for the POS insatlled
  		if (!in_array('marketplace', $extensions)) {
  			die('<h3>' . $this->language->get('error_installation') . '</h3>');
      }

      if (in_array('shipping_wk_custom_shipping',$extensions )) {
        $chkInstalled = $this->model_extension_shipping_wk_custom_shipping->isInstalled();

      if (!$chkInstalled)
        die('<h3>' . $this->language->get('error_installation_custom') . '</h3>');
	  }
	  

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

			$this->model_setting_setting->editSetting('shipping_wk_custom_shipping', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'].'&type=shipping', true));
		}
 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}
		
		$config = array (
		 'shipping_wk_custom_shipping_title',
		 'shipping_wk_custom_shipping_method_title',
		 'shipping_wk_custom_shipping_method',
		 'shipping_wk_custom_shipping_admin_flatrate',
		 'shipping_wk_custom_shipping_tax_class_id',
		 'shipping_wk_custom_shipping_error_msg',
		 'shipping_wk_custom_shipping_geo_zone_id',
		 'shipping_wk_custom_shipping_status',
		 'shipping_wk_custom_shipping_seller_status',
		 'shipping_wk_custom_shipping_seller_details',
		 'shipping_wk_custom_shipping_sort_order',
		 'shipping_wk_custom_shipping_event_status',
		 'shipping_wk_custom_shipping_event_based',
		 'shipping_wk_custom_shipping_priority_status',
		 'shipping_wk_custom_shipping_high_priority_day',
		 'shipping_wk_custom_shipping_high_priority_amount',
		 'shipping_wk_custom_shipping_mid_priority_day',
		 'shipping_wk_custom_shipping_mid_priority_amount',
		 'shipping_wk_custom_shipping_low_priority_day',
		 'shipping_wk_custom_shipping_low_priority_amount',
		 'shipping_wk_custom_shipping_label_status',
		 'shipping_wk_custom_shipping_seller_label',
		 'shipping_wk_custom_shipping_seller_custom_logo',
		);
  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
		 'text'      => $this->language->get('text_home'),
		 'href'      => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
      	 'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       	 'text'      => $this->language->get('text_shipping'),
		 'href'      => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'].'&type=shipping', true),
      	 'separator' => ' :: '
   		);

   		$this->data['breadcrumbs'][] = array(
       	 'text'      => $this->language->get('heading_title'),
		 'href'      => $this->url->link('extension/shipping/wk_custom_shipping', 'user_token=' . $this->session->data['user_token'], true),
		 'separator' => ' :: '
   		);

		$this->data['action'] = $this->url->link('extension/shipping/wk_custom_shipping', 'user_token=' . $this->session->data['user_token'], true);

		$this->data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'].'&type=shipping', true);

		if (isset($this->error['method_name'])) {
			$this->data['error_method_name'] = $this->error['method_name'];
		} else {
			$this->data['error_method_name'] = '';
		}

		if (isset($this->error['title'])) {
			$this->data['error_title'] = $this->error['title'];
		} else {
			$this->data['error_title'] = '';
		}

		if (isset($this->error['method'])) {
			$this->data['error_method'] = $this->error['method'];
		} else {
			$this->data['error_method'] = '';
		}

		if (isset($this->error['admin_flatrate'])) {
			$this->data['error_admin_flatrate'] = $this->error['admin_flatrate'];
		} else {
			$this->data['error_admin_flatrate'] = '';
		}

		if (isset($this->error['custom_error_msg'])) {
			$this->data['custom_error_msg'] = $this->error['custom_error_msg'];
		} else {
			$this->data['custom_error_msg'] = '';
		}

		if (isset($this->session->data['event_shipping_error']) && $this->session->data['event_shipping_error']) {
			$this->data['error_event_shipping'] = true;
			unset($this->session->data['event_shipping_error']);
		} else {
			$this->data['error_event_shipping'] = '';
		}
		foreach ($config as $key => $value) {
			if (isset($this->request->post[$value])) {
				$this->data[$value] = $this->request->post[$value];

			} else if ($this->config->get($value)){
				$this->data[$value] = $this->config->get($value);
			} else {
				$this->data[$value] = '';
			}
		}
		
		$this->load->model('localisation/tax_class');

		$this->data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/shipping/wk_custom_shipping', $this->data));

	}

	protected function validate() {
		
		$event_based_shipping = array();

		$event_shipping = array();
		if (!$this->user->hasPermission('modify', 'extension/shipping/wk_custom_shipping')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['shipping_wk_custom_shipping_method_title'] || ctype_space($this->request->post['shipping_wk_custom_shipping_method_title']) || strlen($this->request->post['shipping_wk_custom_shipping_method_title']) < 5 || strlen($this->request->post['shipping_wk_custom_shipping_method_title']) > 50) {
			$this->error['method_name'] = $this->language->get('error_method_name');
		}

		if (!$this->request->post['shipping_wk_custom_shipping_title'] || ctype_space($this->request->post['shipping_wk_custom_shipping_title']) || strlen($this->request->post['shipping_wk_custom_shipping_title']) < 5 || strlen($this->request->post['shipping_wk_custom_shipping_title']) > 50) {
			$this->error['title'] = $this->language->get('error_title');
		}

		if (ctype_space($this->request->post['shipping_wk_custom_shipping_error_msg']) || strlen($this->request->post['shipping_wk_custom_shipping_error_msg']) < 0 || strlen($this->request->post['shipping_wk_custom_shipping_error_msg']) > 50) {
			$this->error['custom_error_msg'] = $this->language->get('error_custom_msg');
		}

		if (!$this->request->post['shipping_wk_custom_shipping_method']) {
			$this->error['method'] = $this->language->get('error_method');

		}elseif(($this->request->post['shipping_wk_custom_shipping_method']=='flat' OR $this->request->post['shipping_wk_custom_shipping_method']=='both') AND (!(int)$this->request->post['shipping_wk_custom_shipping_admin_flatrate'])){
			$this->error['admin_flatrate'] = $this->language->get('error_admin_flatrate');
		}

		if (isset($this->request->post['shipping_wk_custom_shipping_event_status']) && $this->request->post['shipping_wk_custom_shipping_event_status']) {
		// Event Based Shipping Validation
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
				
				if (isset($value['prefix']) ) {
					$event_shipping[$key]['prefix'] = $value['prefix'];
				}
			
					
				if (isset($value['type']) && $value['type']) {
					$event_shipping[$key]['type'] = $value['type'];
				}

					
				if (isset($value['amount']) && $value['amount'] && $value['amount'] > 0 ) {
					$event_shipping[$key]['amount'] = $value['amount'];
				} else {
						
					$event_shipping[$key]['amount'] = 0;
					$error = true;
				}
					
				if (isset($event_shipping[$key]['datefrom']) && isset($event_shipping[$key]['dateto']) && 				$event_shipping[$key]['datefrom'] >= $event_shipping[$key]['dateto']) {
					$error = true;
				}
			
	
				$this->request->post['shipping_wk_custom_shipping_event_based'] = $event_shipping;
				
				if ($error) {
					$this->session->data['event_shipping_error'] = true;
					if (isset($this->error['warning']) && $this->error['warning'])
					$this->error['warning'] = $this->error['warning'];
					else
					$this->error['warning'] = $this->language->get('error_event_fields');

				}
				// Event Based Shipping Validation ends here
			}
		}
		return !$this->error ;
	}
}
?>
