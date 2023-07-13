<?php
class ControllerExtensionModuleWkpricealert extends Controller {
  private  $error = array();
  public function index() {

    $data = array();

    $data = array_merge($data, $this->load->language('extension/module/wk_pricealert'));

    $this->load->model('localisation/currency');

    if ($this->customer->isLogged()) {
      $data['logged'] = $this->customer->isLogged();
    } else {
      $data['logged'] = $this->customer->isLogged();
    }

    $language_id = $this->config->get('config_language_id');

    if ($this->request->get['product_id']) {
      $product_id = $this->request->get['product_id'];
    } else {
      $product_id = 0;
    }

    // $sellerId = $this->model_account_wk_pricealert->getSellerByProduct($product_id);
    $this->registry->set('prolert', new Productalert($this->registry));

    $data['pricealert_allowed'] = false;

    $pricealert_entry = $this->prolert->getAlertProduct($product_id);

    if ($pricealert_entry) {
        $alert_status = $this->prolert->getProductAlertStatus($product_id);
        if($alert_status) {
          $data['pricealert_allowed'] = true;
        }
    }

    if (!$this->customer->isLogged() && !$this->config->get('wk_pricealert_allow_guest')) {
        $data['pricealert_allowed'] = false;
		}

    $result_product_selected_option = array();
		$data['product_selected_option'] = array();

		if(isset($data['pricealert_allowed']) && $data['pricealert_allowed']) {
			$result_product_selected_option = $this->prolert->priceAlertSelectedOption($product_id);
		}

		if(!empty($result_product_selected_option)) {
			foreach ($result_product_selected_option as $key => $value) {
				$option = (array) json_decode($value['product_selected_option']);
				foreach ($option as $key => $value) {
					$data['product_selected_option'][$key][] = $value;
				}
			}
		}

    $data['code'] = $this->session->data['currency'];

    $data['action'] = $this->url->link('extension/module/wk_pricealert/submitQuote', '', true);

    $data['currencies'] = array();

    $results = $this->model_localisation_currency->getCurrencies();

    foreach ($results as $result) {
      if ($result['status']) {
        $data['currencies'][] = array(
          'title'        => $result['title'],
          'code'         => $result['code'],
          'symbol'	   => $result['symbol_left'] ? $result['symbol_left'] : $result['symbol_right']
        );
      }
    }

    $data['options'] = array();

    $product_options = $this->load->controller('account/wk_pricealert/getAlloption',$product_id);

    if(isset($product_options['options']) && is_array($product_options['options'])) {
      foreach($product_options['options'] as $product_option) {
        if($product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'select')
        $data['options'][] = $product_option;
      }
    }

    return $this->load->view('extension/module/wk_pricealert', $data);
  }

  public function submitQuote() {

    $this->load->language('extension/module/wk_pricealert');

    if($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {

     $this->registry->set('prolert', new Productalert($this->registry));

     $product_options = array();

     // option code for the futher updates
     // if(isset($this->request->post['form_data'])){
     //   foreach($this->request->post['form_data'] as $options) {
     //     if($options['name'] != 'wk_pricealert_price' && $options['name'] != 'wk_pricealert_currency')
     //     $product_options[] = array('option_id' => $options['name'], 'option_value_id' => $options['value']);
     //   }
     //   unset($this->request->post['form_data']);
     // }

     $this->request->post['option'] = $product_options;

     $check_requests = $this->prolert->checkRequests($this->request->post);

     if ($check_requests) {
        $json['error'] = $this->language->get('error_requests');
     } else {
        $insert = $this->prolert->insertQuote($this->request->post);
        if ($insert == '2') {
          $json['success'] = $this->language->get('text_success_update');
        } else {
          $json['success'] = $this->language->get('text_success');
        }

        if (isset($json['success'])) {
          $language_id = $this->config->get('config_language_id');
          $subject = $this->config->get('wk_pricealert_email_notification_subject')[$language_id];
          $email = $this->config->get('wk_pricealert_email_notification')[$language_id];

          $find_email = array(
            '{vendor_name}',
            '{customer_name}',
            '{product_name}',
            '{product_link}',
            '{product_image}'
            );

          $details = $this->prolert->productDetails($this->request->post['product_id']);

          $this->load->model('tool/image');

          $replace_email = array(
            'vendor_name'		=> $details['vendor_name'],
            'customer_name'		=> $this->customer->isLogged() ? $this->customer->getFirstName() . ' ' .$this->customer->getLastName() : $this->request->post['iname'],
            'product_name'		=> '<a href="'. $this->url->link('product/product', 'product_id='. $this->request->post['product_id'], true) .'">' . $details['name'] . '</a>',
            'product_link'		=> $this->url->link('product/product', 'product_id='. $this->request->post['product_id'], true),
            'product_image'		=> $details['image'] ? '<a href="'. $this->url->link('product/product', 'product_id='. $this->request->post['product_id'], true) .'">' . '<img src="' . $this->model_tool_image->resize($details['image'], 200, 200) . '">' . '</a>' : ''
          );

          $html = html_entity_decode(str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find_email, $replace_email, $email)))));
          $email_data = array(
            'email_to' => $details['email'],
            'email_from' => $this->config->get('config_email'),
            'sender_name' => $this->config->get('config_name'),
            'subject' => $subject,
            'html' => $html,
            'text' => html_entity_decode($html)
            );

          $this->prolert->sendMail($email_data);
        }
      }
   } else {
     if ($this->error){
       $json = $this->error;
     }

     if (isset($this->error['error_limit_exceed'])){
       $json['error'] = $this->error['error_limit_exceed'];
     }
   }

   $this->response->addHeader('Content-Type: application/json');
   $this->response->setOutput(json_encode($json));
 }

 public function validate() {
    if (!$this->customer->isLogged()) {
      if (!isset($this->request->post['name']) && !$this->request->post['name']) {
        return false;
      }
      if (!isset($this->request->post['email']) && !$this->request->post['email']) {
        return false;
      }
      $this->request->post['iname'] = isset($this->request->post['iname']) ? trim($this->request->post['iname']) : '';

      if(utf8_strlen($this->request->post['iname']) < 1 || utf8_strlen($this->request->post['iname']) > 32) {
        $this->error['error_iname'] = $this->language->get('error_name');
        return false;
      }
    }

    $this->registry->set('prolert', new Productalert($this->registry));
    $product_id = isset($this->request->post['product_id']) ? $this->request->post['product_id'] : 0;
    $total_request_in_month = $this->prolert->getTotalRequest($product_id);

    if (!$this->customer->isLogged()) {
       $key_hash = 'wk_pricealert_unregistered_request';
    } else {
       $key_hash = 'wk_pricealert_registered_request';
    }

    if ($this->config->get($key_hash) <= $total_request_in_month) {
        $this->error['error_limit_exceed'] = $this->language->get('error_limit_exceed');
        return false;
    }

    if (!isset($this->request->post['price']) && !$this->request->post['price']) {
      return false;
    }
    if (!isset($this->request->post['currency']) && !$this->request->post['currency']) {
      return false;
    }
    if (!isset($this->request->post['product_id']) && !$this->request->post['product_id']) {
      return false;
    }

    $price = $this->db->query("SELECT price FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$this->request->post['product_id'] . "'")->row;

    if (isset($price['price'])) {
      if($this->request->post['price'] > $price['price']) {
        $this->error['error_price'] = $this->language->get('error_price_max');
        return false;
      }
    }

    $exist = $this->db->query("SELECT quote_id, requests,responded,accept,reject FROM " . DB_PREFIX . "pricealert_quote WHERE customer_email = '" . $this->db->escape($this->request->post['iemail']) . "' AND product_id = '" . (int)$this->request->post['product_id'] . "'")->row;

    if (isset($exist['quote_id'])) {
      if ($exist['responded'] || $exist['accept'] || $exist['reject']) {
        $this->error['error_limit_exceed'] = $this->language->get('error_request_submited');
        return false;
      }
    }


    return true;
  }

  public function accontPageAjax()  {

    $json = array();
    $json = $this->load->language('extension/module/wk_pricealert');
    $json['wk_pricealert_status']	= $this->config->get('module_wk_pricealert_status');
    $json['href'] = $this->url->link('account/wk_pricealert','',true);
    $json['page_title'] = $this->language->get('heading_title');
    $json['title'] = $this->language->get('heading_request');
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
  }

  public function addJsFile(&$route = false, &$data = false, &$output = false) {
        if ($this->config->get('module_wk_pricealert_status')) {
          $data['scripts'][] = 'catalog/view/javascript/webkul/wkpa.js';
          // $data['scripts'][] ='catalog/view/javascript/webkul/wkpa_account.js';
        }
  }

  public function addAccountPageJsFile(&$route = false, &$data = false, &$output = false) {
        if ($this->config->get('module_wk_pricealert_status')) {
          $this->document->addScript('catalog/view/javascript/webkul/wkpa_account.js' ,'header');
        }
  }

}
