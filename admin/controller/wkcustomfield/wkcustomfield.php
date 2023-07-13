<?php

Class ControllerWkcustomfieldWkcustomfield extends Controller{

	private $error = array();

	public function index(){
			
		$this->load->language("wkcustomfield/wkcustomfield");
		$this->document->setTitle($this->language->get('heading_title'));
		$this->getList();
	}

	public function getList() {

		$this->load->language("wkcustomfield/wkcustomfield");

		$this->load->model("wkcustomfield/wkcustomfield");

		$data['user_token'] = $this->session->data['user_token'];

		if(isset($this->request->get['sort'])){
			$data['sort'] = $sort = $this->request->get['sort'];
		}else{
			$sort = null;	
			$data['sort'] = 'ASC';		
		}

		if(isset($this->request->get['order'])){
			$data['order'] = $order = $this->request->get['order'];
		}else{
			$order = null;
			$data['order'] = 'cfd.fieldName';
		}

		if(isset($this->request->get['fieldName'])){
			$data['fieldName'] = $fieldName = trim($this->request->get['fieldName']);
		}else{
			$fieldName = null;
		}

		if(isset($this->request->get['fieldType'])){
			$data['fieldType'] = $fieldType = $this->request->get['fieldType'];
		}else{
			$fieldType = null;
		}
		
		if(isset($this->request->get['forSeller'])){
			$data['forSeller'] = $forSeller = $this->request->get['forSeller'];
		}else{
			$forSeller = null;
		}

		if(isset($this->request->get['page'])){
			$data['page'] = $page = $this->request->get['page'];
		}else{
			$page = null;
		}

		$filterValues = array(
			'fieldName' => $fieldName,
			'fieldType' => $fieldType,
			'forSeller' => $forSeller,
			'page'      => $page,
			'sort' 		=> $sort,
			'order' 	=> $order,
			'start'     => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'     => $this->config->get('config_limit_admin')
		);


		$optionList = array();
		$optionList = $this->model_wkcustomfield_wkcustomfield->getOptionList($filterValues);
		$getOptionListTotals = $this->model_wkcustomfield_wkcustomfield->getOptionListTotals($filterValues);

		$data['optionList'] = array();
		foreach ($optionList as $key => $option) {
			$data['optionList'][] = array(
				'id' => $option['id'],
				'fieldName' => $option['fieldName'],
				'fieldType'	=> $option['fieldType'],
				'forSeller' => $option['forSeller'],
				'edit'		=> $this->url->link("wkcustomfield/wkcustomfield/getForm",'user_token='.$this->session->data['user_token'] . "&id=".$option['id'], true),
			);
		}

		//breadcrum

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
		 'href'      => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
		   'separator' => false
		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
		 'href'      => $this->url->link('wkcustomfield/wkcustomfield', 'user_token=' . $this->session->data['user_token'], true),
		   'separator' => ' :: '
		);

		$data['insert'] = $this->url->link("wkcustomfield/wkcustomfield/getForm","user_token=". $this->session->data['user_token'], true);

		$data['action'] = $this->url->link("wkcustomfield/wkcustomfield/delete","user_token=". $this->session->data['user_token'], true);

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->session->data['warning'])) {
			$data['warning'] = $this->session->data['warning'];
			unset($this->session->data['warning']);
		} else {
			$data['warning'] = '';
		}

		// $this->template = 'wkcustomfield/wkcustomfield';
		$data['header']	=	$this->load->controller('common/header');
		$data['column_left']	=	$this->load->controller('common/column_left');
		$data['footer']	=	$this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('wkcustomfield/wkcustomfield', $data));
	}


	public function insert(){

		$this->load->model("wkcustomfield/wkcustomfield");
		$this->load->language("wkcustomfield/wkcustomfield");

		if($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateForm()){

			if(isset($this->request->get['id'])){

				$this->model_wkcustomfield_wkcustomfield->updateField($this->request->post);
				$this->session->data['success'] = $this->language->get('success_update');
				$this->response->redirect($this->url->link('wkcustomfield/wkcustomfield', 'user_token=' . $this->session->data['user_token'] , true));
			}else{
				$this->model_wkcustomfield_wkcustomfield->insertField($this->request->post);
				$this->session->data['success'] = $this->language->get('success_insert');
				$this->response->redirect($this->url->link('wkcustomfield/wkcustomfield', 'user_token=' . $this->session->data['user_token'] , true));
			}
		}
		$this->getForm();
	}

	public function getForm(){
		$this->load->language("wkcustomfield/wkcustomfield");

		$this->load->model("wkcustomfield/wkcustomfield");

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
			'separator' => false
		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('wkcustomfield/wkcustomfield', 'user_token=' . $this->session->data['user_token'], true),
			'separator' => ' :: '
		);

		if(isset($this->request->get['id']) && $this->request->get['id'] != ''){
			$this->document->setTitle($this->language->get('heading_title_edit'));
			$data['heading_title']	=	$this->language->get('heading_title');
			$data['lower_heading_title']	=	$this->language->get('heading_title_edit');
			$data['button_save']	=	$this->language->get('button_update');
			$data['button_back']	=	$this->language->get('button_back');

			$data['breadcrumbs'][] = array(
			   'text'      => $this->language->get('heading_title_edit'),
			'href'      => $this->url->link('wkcustomfield/wkcustomfield/getForm','user_token=' . $this->session->data['user_token'] . '&id=' . (int)$this->request->get['id'], true),
			  'separator' => ' :: '
			   );
			if($this->config->get('wk_custome_field_wkaccesspermission')){
				$data['wkaccesspermission'] = $this->config->get('wk_custome_field_wkaccesspermission');
			}
			$data['back'] = $this->url->link("wkcustomfield/wkcustomfield","user_token=". $this->session->data['user_token'], true);
			$data['action'] = $this->url->link("wkcustomfield/wkcustomfield/insert" ,"user_token=". $this->session->data['user_token'] . "&id=". (int)$this->request->get['id'], true);

			$data['optionDetails']	=	array();
			$optionDetails = array();
			$data['optionValue']  = array();
			$fieldoptions = array();

			$optionDetails = $this->model_wkcustomfield_wkcustomfield->getOptionDetails((int)$this->request->get['id']);
			$fieldoptions = $this->model_wkcustomfield_wkcustomfield->getFieldOptions((int)$this->request->get['id']);
			$data['fieldId'] = '';
			if(!empty($fieldoptions)){
				foreach ($fieldoptions as $key => $values) {
					foreach ($values as $key => $value) {
						$data['optionValue'][$value['optionId']][$value['language_id']] = $value['optionValue'];
					}
				}
			}
			$data['fieldId'] = isset($optionDetails[0]['fieldId']) ? $optionDetails[0]['fieldId'] : 0;

			foreach ($optionDetails as $key => $option) {

				$data['fieldName'][$option['language_id']] = $option['fieldName'];
				$data['isRequired'] = $option['isRequired'];
				$data['forSeller'] = $option['forSeller'];
				$data['fieldType'] = $option['fieldType'];
			}

			foreach ($optionDetails as $key => $option) {
					$data['fieldDes'][$option['language_id']] = $option['fieldDescription'];
				}

			if($data['fieldType'] == 'text' || $data['fieldType'] == 'textarea'){
				$data['optionValue'] = array();
			}

		}else{

			$this->document->setTitle($this->language->get('heading_title_insert'));
			$data['error_insert'] = $this->language->get('error_insert');;

			$data['breadcrumbs'][] = array(
			   'text'      => $this->language->get('heading_title_insert'),
			'href'      => $this->url->link('wkcustomfield/wkcustomfield/getForm','user_token=' . $this->session->data['user_token'], true),
			  'separator' => ' :: '
			   );

			$data['heading_title']	=	$this->language->get('heading_title');
			$data['lower_heading_title']	=	$this->language->get('heading_title_insert');

			$data['button_save']	=	$this->language->get('button_save');
			$data['button_back']	=	$this->language->get('button_back');
			if($this->config->get('wk_custome_field_wkaccesspermission')){
				$data['wkaccesspermission'] = $this->config->get('wk_custome_field_wkaccesspermission');
			}

			$data['back'] = $this->url->link("wkcustomfield/wkcustomfield","user_token=". $this->session->data['user_token'], true);
			$data['action'] = $this->url->link("wkcustomfield/wkcustomfield/insert","user_token=". $this->session->data['user_token'], true);

		}

		$form_arr = array(
		 'isRequired',
		 'fieldType',
		 'forSeller',
		 'fieldName',
		 'fieldDes',
		 'optionValue',
		);

		foreach($form_arr as $form) {
			if(isset($this->request->post[$form])) {
				$data [$form] = $this->request->post[$form];
			} elseif (!isset($data[$form])) {
				$data [$form] = '';
			}
		}

		$err_indexes = array(
		 'isRequired',
		 'fieldType',
		 'forSeller',
		 'fieldName',
		 'fieldDes',
		 'optionValue',
		 'optionValues',
		);

		foreach($err_indexes as $err) {
			if(isset($this->error[$err])) {
				$data['error_' . $err] = $this->error[$err];
			} else {
				$data['error_' . $err] = '';
			}
		}

		$data['header']	=	$this->load->controller('common/header');
		$data['column_left']	=	$this->load->controller('common/column_left');
		$data['footer']	=	$this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('wkcustomfield/wkcustomfield_form', $data));
	}	

	public function delete(){

		$this->load->language('wkcustomfield/wkcustomfield');
		if($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateDeleteForm()) {
			$this->load->model('wkcustomfield/wkcustomfield');
			$fieldName = '';
			if(isset($this->request->post['selected'])){
				foreach ($this->request->post['selected'] as $key => $value) {
					$fieldName .= $this->model_wkcustomfield_wkcustomfield->deleteFieldDetails($value).", ";
				}
				$fieldName = trim($fieldName,', ');
				$this->session->data['success'] = $this->language->get('success_delete')."- [ ". $fieldName ." ] !";
				$this->response->redirect($this->url->link("wkcustomfield/wkcustomfield","user_token=".$this->session->data['user_token'], true) );
			}else{
				$this->session->data['warning'] = $this->language->get('error_delete');
				$this->response->redirect($this->url->link("wkcustomfield/wkcustomfield","user_token=".$this->session->data['user_token'] , true) );
			}
		} else {
			$this->session->data['warning'] = (isset($this->error['warning'])) ? $this->error['warning'] : $this->language->get('error_delete');
			$this->response->redirect($this->url->link("wkcustomfield/wkcustomfield","user_token=".$this->session->data['user_token'] , true) );
		}
	}

	public function getOptions(){
		if($this->request->server['REQUEST_METHOD'] == 'POST' && $this->request->post['value'] != ''){
			$this->load->model('wkcustomfield/wkcustomfield');
			$options = array();
			$options = $this->model_wkcustomfield_wkcustomfield->getOptions($this->request->post['value']);
			$this->response->setOutput(json_encode($options));
		}
	}

	protected function validateDeleteForm() {
		
		if (!$this->user->hasPermission('modify', 'wkcustomfield/wkcustomfield')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
	
	protected function validateForm() {
		
		if (!$this->user->hasPermission('modify', 'wkcustomfield/wkcustomfield')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if(isset($this->request->post['isRequired'])) {
			if($this->request->post['isRequired'] == ''){
				$this->error['isRequired'] = $this->language->get('error_select');
			}
		} else {
			$this->error['isRequired'] = $this->language->get('error_select');
		}

		if(isset($this->request->post['fieldType'])) {
			if($this->request->post['fieldType'] == '' || !in_array($this->request->post['fieldType'],array('select','checkbox','radio','text','textarea','date','time','datetime'))){
				$this->error['fieldType'] = $this->language->get('error_select');
			}
		} else {
			$this->error['fieldType'] = $this->language->get('error_select');
		}

		if(isset($this->request->post['forSeller'])) {
			if($this->request->post['forSeller'] == ''){
				$this->error['forSeller'] = $this->language->get('error_select');
			}
		} else {
			$this->error['forSeller'] = $this->language->get('error_select');
		}

		$this->load->model('localisation/language');
		$languages = $this->model_localisation_language->getLanguages();

		if(isset($this->request->post['fieldName']) && !empty($this->request->post['fieldName'])) {
			foreach ($this->request->post['fieldName'] as $key => $value) {
				if($value == '' || $value == null || utf8_strlen(trim($value)) < 2 || utf8_strlen($value)  > 64){
					$this->error['fieldName'][$key] = $this->language->get('error_field_name');
				}
			}
		} else {
			foreach($languages as $language) {
				$this->error['fieldName'][$language['language_id']] = $this->language->get('error_field_name');
			}
		}

		if(isset($this->request->post['fieldDes']) && !empty($this->request->post['fieldDes'])) {
			foreach ($this->request->post['fieldDes'] as $key => $value) {
				if($value == '' || $value == null || utf8_strlen(trim($value)) < 2 || utf8_strlen($value)  > 64){
					$this->error['fieldDes'][$key] = $this->language->get('error_field_name');
				}
			}
		} else {
			foreach($languages as $language) {
				$this->error['fieldDes'][$language['language_id']] = $this->language->get('error_field_name');
			}
		}

		if(isset($this->request->post['fieldType'])) {
			if (($this->request->post['fieldType'] == 'select' || $this->request->post['fieldType'] == 'radio' || $this->request->post['fieldType'] == 'checkbox')) {
				if(isset($this->request->post['optionValue'])){
					if(!empty($this->request->post['optionValue'])) {
						foreach ($this->request->post['optionValue'] as $key => $values) {
							foreach ($values as $language_id => $value) {
								if($value == '' || $value == null || utf8_strlen(trim($value)) < 2 || utf8_strlen($value)  > 64){
									$this->error['optionValue'][$key][$language_id] = $this->language->get('error_option_value_name');
								}
							}
						}
					} else {
						$this->error['optionValues'] = $this->language->get('error_field_values');
					}						
				} else {	
					$this->error['optionValues'] = $this->language->get('error_field_values');
				}
			}
		}

		return !$this->error;
	}
}
?>