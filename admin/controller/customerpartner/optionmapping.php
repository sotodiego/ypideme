<?php
class ControllerCustomerpartnerOptionmapping extends Controller {
  /**
   * [private to store errors]
   * @var [array]
   */
	private $error    = array();

  private $storeurl = '';

  private $breadcrumnbs = array(
    'text_home'     =>  'common/dashboard',
    'heading_title' =>  'customerpartner/optionmapping',
  );

  /**
   * [private data vaiable will store all the values which will used in the TPL ]
   * @var [array]
   */
	private $data    = array();
  /**
   * [private string variable used for the filter]
   * @var [array]
   */
  private $stingTypeVar = array(
    'filter_option'      => null,
    'filter_option_id'   => null,
    'filter_category'    => null,
    'filter_category_id' => null
  );
  /**
   * [private numeric type variables used in the filter]
   * @var [array]
   */
  private $numericTypeVar = array(
    'start'  => 0,
    'sort'   => 'pd.name',
    'order'  => 'ASC',
    'page'   => 1,
    'limit'  => 5,
  );
   /**
    * [__construct default cusntroctor to intilize the laguage and models used in the file]
    * @param [type] $registory [registory var]
    */
	public function __construct($registory) {
	    parent::__construct($registory);

	  $this->registry->set('ocutilities', new Ocutilities($this->registry));

    $this->ocutilities = $this->registry->get('ocutilities');

    $this->load->model('catalog/category');

    $this->load->model('customerpartner/partner');

    $this->__CpPartner  = $this->model_customerpartner_partner;

    $this->load->model('customerpartner/optionmapping');

    $this->__CpOptMap   = $this->model_customerpartner_optionmapping;

		$this->data         = $this->load->language('customerpartner/optmap');
  }

  /**
   * [index index of the controller will called by default]
   * @return [view] [description]
   */
  public function index() {

	  $this->document->setTitle($this->data['heading_title']);

    $filter = array_merge($this->stingTypeVar,$this->numericTypeVar);

    foreach ($filter as $getvar => $value) {
      $$getvar = $this->ocutilities->_setGetRequestVar($getvar,$value);
    }

		$url = '';

    foreach ($this->stingTypeVar as $key => $value) {
      	$url .= $this->ocutilities->_setStringURLs($key);
    }

    foreach ($this->numericTypeVar as $key => $value) {
        $url .= $this->ocutilities->_setNumericURLs($key);
    }

  	$this->__createBreadcrumbs();

		$this->data['insert'] = $this->url->link('customerpartner/optionmapping/add', 'user_token=' . $this->session->data['user_token'] . $url . '&mpcheck=1', true);

		$this->data['delete'] = $this->url->link('customerpartner/optionmapping/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

 		$this->_setuser_token();

 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} elseif(isset($this->session->data['warning']) && $this->session->data['warning']) {
			$this->data['error_warning'] = $this->session->data['warning'];
			unset($this->session->data['warning']);
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

		$url = '';

    foreach ($this->stingTypeVar as $key => $value) {
        $url .= $this->ocutilities->_setStringURLs($key);
    }

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$url = '';

    foreach ($this->stingTypeVar as $key => $value) {
        $url .= $this->ocutilities->_setStringURLs($key);
    }

		if (isset($this->request->get['sort'])) {
			 $url .= '&sort=' . $this->request->get['sort'];
		}

    foreach ($this->stingTypeVar as $key => $value) {
       $this->data[$key] = $$key;
    }

		$this->data['sort'] = $sort;

    $this->_loadCommonControllers();

		$this->response->setOutput($this->load->view('customerpartner/optmap_list',$this->data));
  }

  public function _loadData() {

    $json['categories'] = array();

    $json['success'] = 0;

    $filter = array_merge($this->stingTypeVar,$this->numericTypeVar);

    foreach ($filter as $getvar => $value) {
      $$getvar = $this->ocutilities->_setPostRequestVar($getvar,$value);
    }

    $filterData = array(
      'filter_option'	      => $filter_option,
      'filter_category'     => $filter_category,
      'filter_category_id'  => $filter_category_id,
      'filter_option_id'    => $filter_option_id,
      'sort'                => $sort,
      'order'               => $order,
      'start'               => $start,
      'limit'               => $limit,
      'page'                => $page,
    );

    $json['partners'] = $this->__CpPartner->getCustomers();

    $json['total']    = $categorymapping_total = $this->__CpOptMap->getTotalCategoryOptions($filterData);

    $results = $this->__CpOptMap->getCategoryOptions($filterData);
    if ($results) {
      $json['success'] = 1;
      foreach ($results as $result) {
          $option_name = '';
          foreach (explode(',',$result['option_id']) as $key => $value) {
            $option_name .= $this->__CpOptMap->getOptionName($value)['name'].', ';
          }
          $option_name = rtrim($option_name,', ');
          $json['categories'][] = array(
          'category_id' => $result['category_id'],
          'option_id' => $result['option_id'],
          'name'       => $result['name'],
          'option_name'       => $option_name,
          'selected'   => isset($this->request->post['selected']) && in_array($result['category_id'], $this->request->post['selected']),
          'action'	=> $this->url->link('customerpartner/optionmapping/add', 'user_token=' . $this->session->data['user_token'] . '&category_id=' . $result['category_id'] .'&option_id='.$result['option_id'] , true)
        );
      }
    }

   $this->response->setOutput(json_encode($json));
  }

  public function _loadCommonControllers() {
    $this->data['header']      = $this->load->controller('common/header');
    $this->data['column_left'] = $this->load->controller('common/column_left');
    $this->data['footer']      = $this->load->controller('common/footer');
  }

  public function _setuser_token() {
    $this->data['user_token'] = $this->session->data['user_token'];
  }

  public function _manageErrorWarning() {
    $this->data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
  }

  public function __createBreadcrumbs() {
    foreach ($this->breadcrumnbs as $language => $url) {
      $this->data['breadcrumbs'][] = array(
         		'text'      => $this->data[$language],
  			    'href'      => $this->url->link($url, 'user_token=' . $this->session->data['user_token'] . $this->storeurl, true),
     	);
    }
 }

 public function add() {

	 $this->document->setTitle($this->data['heading_title']);

	 if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {

			$this->__CpOptMap->_addCategoryOption($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('customerpartner/optionmapping', 'user_token=' . $this->session->data['user_token'], true));
	 }

   $this->_setuser_token();

   $this->_manageErrorWarning();

	 $url = '';

   $this->storeurl = isset($url) ? $url : '';

   $this->__createBreadcrumbs();

   if($this->request->server['REQUEST_METHOD'] != 'POST' && isset($this->request->get['category_id']) && $this->request->get['category_id']) {
   		$category_details = $this->model_catalog_category->getCategory($this->request->get['category_id']);
			$this->data['product_categories'][] = array(
				'category_id' => $this->request->get['category_id'],
				'name'		=> isset($category_details['name']) && $category_details['name'] ? $category_details['name'] : '',
			);

			if (isset($this->request->get['option_id'])) {

				$this->data['options'] = array();

				foreach (explode(',', $this->request->get['option_id']) as $key => $value) {

					$option_details = $this->__CpOptMap->getOptionName($value);
          if(!empty($option_details)){
						$this->data['options'][] = array(
							'option_id' => $value,
							'name'		  => isset($option_details['name']) && $option_details['name'] ? $option_details['name'] : 'All',
						);
					} else {
							$this->response->redirect($this->url->link('customerpartner/optionmapping', 'user_token=' . $this->session->data['user_token'], true));
					}
				}
			}
		}

    $this->data['cancel'] = $this->url->link('customerpartner/optionmapping', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$this->data['save'] = $this->url->link('customerpartner/optionmapping/add', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$this->_loadCommonControllers();

		$this->response->setOutput($this->load->view('customerpartner/optmap_form',$this->data));
	}

	public function delete() {

    $this->load->language('customerpartner/categorymapping');

    $this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('customerpartner/categorymapping');

		if ($this->validateDelete()) {
			$this->__CpOptMap->deleteCategoryOption(implode(',', $this->request->post['selected']));
			$this->session->data['success'] = $this->language->get('text_success_delete');
		}
    $this->index();
  }

  private function validateDelete() {
      $this->_userHasPermission();

      if (!isset($this->request->post['selected']) || !$this->request->post['selected']) {
         $this->session->data['warning'] = $this->language->get('error_delete');
  			 return false;
      }
  		return  !$this->error ? TRUE : FALSE;
  }

 public function _userHasPermission() {
   if (!$this->user->hasPermission('modify', 'customerpartner/optionmapping')) {
      $this->error['warning'] = $this->language->get('error_permission');
   }
 }

 private function validateForm(){
    $this->_userHasPermission();

    if (!$this->error && !isset($this->request->post['option_ids']) || !isset($this->request->post['product_category']) || !$this->request->post['option_ids'] || !$this->request->post['product_category']) {
      	$this->error['warning'] = $this->data['error_field'];
    }
    return  !$this->error ? TRUE : FALSE;
 }
 public function categoryOption() {
	 $json = array();

	 if (isset($this->request->get['category_id']) && $this->request->get['category_id']) {
		 $results = $this->__CpOptMap->getCategoryOption($this->request->get['category_id']);

		 if (!empty($results)) {
			 foreach ($results as $result) {
					 $this->load->language('catalog/option');

					 $this->load->model('catalog/option');

					 $this->load->model('tool/image');

           $explode_option_ids  = explode(',',$result['option_id']);

					 foreach ($explode_option_ids as $key => $_op_value) {
						 $option_details = $this->__CpOptMap->getOptionName($_op_value);

					   $filter_data = array(
						 'filter_name' => $option_details['name'],
						 'start'       => 0,
						 'limit'       => 1
						 );

						$options = $this->model_catalog_option->getOptions($filter_data);
	          $option = $options[0];

					 	$option_value_data = array();

						if ($option['type'] == 'select' || $option['type'] == 'radio' || $option['type'] == 'checkbox' || $option['type'] == 'image') {
								$option_values = $this->model_catalog_option->getOptionValues($option['option_id']);

								foreach ($option_values as $option_value) {
									if (is_file(DIR_IMAGE . $option_value['image'])) {
										$image = $this->model_tool_image->resize($option_value['image'], 50, 50);
									} else {
										$image = $this->model_tool_image->resize('no_image.png', 50, 50);
									}

									$option_value_data[] = array(
										'option_value_id' => $option_value['option_value_id'],
										'name'            => strip_tags(html_entity_decode($option_value['name'], ENT_QUOTES, 'UTF-8')),
										'image'           => $image
									);
								}

								$sort_order = array();

								foreach ($option_value_data as $key => $value) {
									$sort_order[$key] = $value['name'];
								}

								array_multisort($sort_order, SORT_ASC, $option_value_data);
							}

							$type = '';

							if ($option['type'] == 'select' || $option['type'] == 'radio' || $option['type'] == 'checkbox') {
								$type = $this->language->get('text_choose');
							}

							if ($option['type'] == 'text' || $option['type'] == 'textarea') {
								$type = $this->language->get('text_input');
							}

							if ($option['type'] == 'file') {
								$type = $this->language->get('text_file');
							}

							if ($option['type'] == 'date' || $option['type'] == 'datetime' || $option['type'] == 'time') {
								$type = $this->language->get('text_date');
							}

							$json[] = array(
								'option_id'    => $option['option_id'],
								'name'         => strip_tags(html_entity_decode($option['name'], ENT_QUOTES, 'UTF-8')),
								'category'     => $type,
								'category_id'     => $this->request->get['category_id'],
								'type'         => $option['type'],
								'option_value' => $option_value_data
							);
					 }
			 }

			 $sort_order = array();
			 foreach ($json as $key => $value) {
				 $sort_order[$key] = $value['name'];
			 }
			 array_multisort($sort_order, SORT_ASC, $json);
		 }
	  }
	 $this->response->setOutput(json_encode($json));
 }

}
?>
