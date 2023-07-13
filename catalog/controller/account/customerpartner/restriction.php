<?php
class ControllerAccountCustomerpartnerRestriction extends Controller {
  private $error = array();
  public function update() {
   // this is the must value
   $this->sentizePostData();

   if($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {
     $this->load->language('account/customerpartner/restriction');

     $this->load->model('account/customerpartner/restriction');

     $this->model_account_customerpartner_restriction->restrict();
     $json['success'] = $this->language->get('text_success');
   } else {
      foreach ($this->error as $key => $value) {
        $json[$key] = $this->error[$key];
      }
   }

   $this->response->addHeader('Content-Type: application/json');
   $this->response->setOutput(json_encode($json));
  }

  public function sentizePostData() {
    $this->registry->set('senatize', new Senatizer($this->registry));
    foreach ($this->request->post as $post_key => $post_value) {
      if(isset($this->request->post[$post_key])) {
        if($post_key == 'restrcition_price'){
          $this->request->post[$post_key] = $this->senatize->number_float($this->request->post[$post_key]);
        } else {
          $this->request->post[$post_key] = $this->senatize->number_int($this->request->post[$post_key]);
        }
      }
    }
  }

  public function validate() {
    $flag = true;
    if(isset($this->request->post['restrcition_price']) && $this->request->post['restrcition_price']) {
       if (!filter_var($this->request->post['restrcition_price'], FILTER_VALIDATE_INT)) {
        $this->error['error_price'] = $this->language->get('error_price');
        $flag = false;
       }
    }

    if(isset($this->request->post['restrcition_quant']) && $this->request->post['restrcition_quant']) {
       if (!filter_var($this->request->post['restrcition_quant'], FILTER_VALIDATE_FLOAT)) {
        $this->error['error_quantity'] = $this->language->get('error_quantity');
        $flag = false;
       }
    }
   return $flag;
  }

}
