<?php
class ControllerAccountCustomerpartnerSendmail extends Controller {

	public function index() {
      $this->load->language('customerpartner/mail');
	   if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$data = array();

			$this->load->model('customerpartner/mail');

			$this->load->model('account/customerpartner');

			if(isset($this->request->post['contact_admin'])) {
				$customer_id = $this->customer->getEmail();
				$commission = $this->model_account_customerpartner->getSellerCommission($customer_id);
				$data = array(
					'seller_message' => $this->request->post['message'],
					'seller_id' => $this->customer->getId(),
					'customer_id' => false,
				    'mail_id' => $this->config->get('marketplace_mail_seller_to_admin'),
				    'mail_from' => $this->customer->getEmail(),
				    'mail_to' => $this->config->get('marketplace_adminmail'),
				);
				$values = array(
					'subject' => $this->request->post['subject'],
					'message' => $this->request->post['message'],
					'commission' => $commission,
				);
				$this->model_customerpartner_mail->mail($data,$values);
			}

			if(isset($this->request->post['contact_seller']) AND isset($this->request->post['seller'])) {
				$seller_email = $this->model_account_customerpartner->getsellerEmail($this->request->post['seller']);

				$commission = $this->model_account_customerpartner->getSellerCommission($this->request->post['seller']);

				$data = array(
					'message' => $this->request->post['message'],
					'seller_id' => $this->request->post['seller'],
					'customer_id' => $this->customer->getId(),
				    'mail_id' => $this->config->get('marketplace_mail_cutomer_to_seller'),
				    'mail_from' => $this->customer->getEmail(),
				    'mail_to' => $seller_email,
				);

				$values = array (
					'message' => $this->request->post['message'],
					'subject' => $this->request->post['subject'],
					'customer_name' => $this->customer->getFirstName().' '.$this->customer->getLastName(),
					'commission' => $commission,
				);

				$this->model_customerpartner_mail->mail($data,$values);

				if($this->config->get('marketplace_mailadmincustomercontactseller')) {
					$data['mail_to'] = $this->config->get('marketplace_adminmail');
					$this->model_customerpartner_mail->mail($data,$values);
				}
			}
		} else {
     return $this->language->get('error_fileds');
		}

	}
	public function validateForm() {
		 $flag_validator = false;

		 if(isset($this->request->post['subject'])) {
        $this->senatize('subject');
				if($this->request->post['subject'] != '') {
					$flag_validator = true;
				}
		 } else {
			 $flag_validator = true;
		 }

		 if($flag_validator && isset($this->request->post['message'])) {
			  $this->senatize('message');
	      if($this->request->post['message'] != '') {
					$flag_validator = true;
				}
		 } else {
			 $flag_validator = true;
		 }

	  return $flag_validator;
	}

	public function senatize($post) {
    $this->request->post[$post] = preg_replace("/script.*?\/script/ius", " ", trim($this->request->post[$post])) ? : $this->request->post[$post];
	}


}
?>
