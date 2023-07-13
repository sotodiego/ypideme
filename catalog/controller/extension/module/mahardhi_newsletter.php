<?php
class ControllerExtensionModuleMahardhiNewsLetter extends Controller {
	
	private $error = array();
	
	public function index($setting) {
		
		
		$this->language->load('extension/module/mahardhi_newsletter');

		$this->load->model('account/mahardhi_newsletter');	   	

		$this->document->addScript('catalog/view/javascript/mahardhi/jquery.cookie.js');	

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_theme') . '/template/extension/module/mahardhi_newsletter.twig')) {
		    if(isset($setting['popup']) && $setting['popup']==1) {
				return $this->load->view('extension/module/mahardhi_newsletterpopup');			
			}else {
				return $this->load->view('extension/module/mahardhi_newsletter');
			}
		} else {
			return;
		}

	}

	public function subscribe() {

		$this->language->load('extension/module/mahardhi_newsletter');
		$this->load->model('account/mahardhi_newsletter');

		if (isset($this->request->post['newsletter_usr_email']) and filter_var($this->request->post['newsletter_usr_email'],FILTER_VALIDATE_EMAIL)) {

			if (!$this->model_account_mahardhi_newsletter->checkalreadyemail($this->request->post)) {
				
				$this->model_account_mahardhi_newsletter->subscribe($this->request->post);
					
				echo('$(".newsletter-message").html("<div class=\"alert alert-success alert-dismissible\"> '.$this->language->get('success_subscribe').'<button type=\"button\" class=\"close\" data-dismiss=\"alert\">×</button></div>");$(".newsletter-message")[0].reset();');
			}
			else{
				echo('$(".newsletter-message").html("<div class=\"alert alert-danger alert-dismissible\"> '.$this->language->get('already_subscribe').'<button type=\"button\" class=\"close\" data-dismiss=\"alert\">×</button></div>");$(".newsletter-message")[0].reset();');
			}
		}
		else {
				
			echo('$(".newsletter-message").html("<div class=\"alert alert-danger alert-dismissible\"> '.$this->language->get('error_message').'<button type=\"button\" class=\"close\" data-dismiss=\"alert\">×</button></div>")');
				
		}
	}

	public function subscribepopup() {		
	  
		$this->language->load('extension/module/mahardhi_newsletter');
	 
		$this->load->model('account/mahardhi_newsletter');
	  
		if (isset($this->request->post['newsletter_usr_popup_email']) and filter_var($this->request->post['newsletter_usr_popup_email'],FILTER_VALIDATE_EMAIL)) {
				$this->request->post['newsletter_usr_email'] = $this->request->post['newsletter_usr_popup_email']; 
				
				
			   if (!$this->model_account_mahardhi_newsletter->checkalreadyemail($this->request->post)) {
				 
					$this->model_account_mahardhi_newsletter->subscribe($this->request->post);
					
					echo('$(".newsletter-popup-message").html("<div class=\"alert alert-success alert-dismissible\"> '.$this->language->get('success_subscribe').'<button type=\"button\" class=\"close\" data-dismiss=\"alert\">×</button></div>");$(".newsletter-popup-message")[0].reset();');				 
					
				 
				} else {
					  
					  echo('$(".newsletter-popup-message").html("<div class=\"alert alert-danger alert-dismissible\"> '.$this->language->get('already_subscribe').'<button type=\"button\" class=\"close\" data-dismiss=\"alert\">×</button></div>");$(".newsletter-popup-message")[0].reset();');
					  
				}
			   
			} else {
				
				echo('$(".newsletter-popup-message").html("<div class=\"alert alert-danger alert-dismissible\"> '.$this->language->get('error_message').'<button type=\"button\" class=\"close\" data-dismiss=\"alert\">×</button></div>")');
				
			}	  
	}

	protected function loadmodule() {
		
		$this->language->load('extension/module/mahardhi_newsletter');

      	$data['heading_title'] = $this->language->get('heading_title');	
		
      	
      	$data['entry_email'] = $this->language->get('entry_email');	
		$data['entry_subscribe'] = $this->language->get('entry_subscribe');	
		$data['entry_unsubscribe'] = $this->language->get('entry_unsubscribe');	
		$data['option_unsubscribe'] = $this->config->get('option_unsubscribe');	
		$data['text_subscribe'] = $this->language->get('text_subscribe');			
		
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_theme') . '/template/extension/module/mahardhi_newsletter.twig')) {
			return $this->load->view('extension/module/mahardhi_newsletter', $data);
		} else {
			return;
		}
	}
}
