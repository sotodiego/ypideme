<?php 
/**
 * @version [Supported opencart version 3.x.x.x.]
 * @category Webkul
 * @package Opencart Marketplace Module Income controller
 * @author [Webkul] <[<http://webkul.com/>]>
 * @copyright Copyright (c) 2010-2019 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

class ControllerAccountCustomerpartnerIncome extends Controller {

    private $data = array();
    private $error = array();
    
    public function __construct($registry){
        parent::__construct($registry);

        $this->data = $this->load->language('account/customerpartner/income');

        $this->load->model('account/customerpartner/income');

        $this->income = $this->model_account_customerpartner_income;

        $this->document->addStyle('catalog/view/theme/default/stylesheet/MP/sell.css');

        if (!$this->customer->isLogged() || !$this->config->get('module_marketplace_status')) {
			$this->session->data['redirect'] = $this->url->link('account/customerpartner/income', '', true);
			$this->response->redirect($this->url->link('account/login', '', true));
        }
        
        $this->load->library('ocutilities');
    }

    public function index() {

        $this->load->model('account/customerpartner');

		$this->data['chkIsPartner'] = $this->model_account_customerpartner->chkIsPartner();

		if(!$this->data['chkIsPartner'] || (isset($this->session->data['marketplace_seller_mode']) && !$this->session->data['marketplace_seller_mode'])) {
            $this->response->redirect($this->url->link('account/account', '', true));
        }
        
        $this->document->setTitle($this->data['heading_title']);

        //pagination code starts here
        $url = $this->setRequestgetVar();


        $this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
        	'text'      => $this->data['text_home'],
			'href'      => $this->url->link('common/home', '', true),
        	'separator' => false
      	);

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->data['text_account'],
			'href'      => $this->url->link('account/account', '', true),
        	'separator' => $this->data['text_separator'],
      	);

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->data['heading_title'],
			'href'      => $this->url->link('account/customerpartner/income', $url, true),
        	'separator' => $this->data['text_separator'],
        );
          
        if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
        }

        $this->data['reset'] = $this->url->link('account/customerpartner/income', '', true);

        //get variable values form the query string code starts here
        $get_request_vars = array(
         'sort'                 => 'c2o.date_added',
         'order'                => 'DESC',
         'page'                 => 1,
         'filter_display_group' => 'month',
         'filter_display_type'  => 'product',
        );
                                    
        foreach ($get_request_vars as $key => $value) {
                                    
            $this->data[$key] = $this->ocutilities->_setGetRequestVar($key,$value);
            $$key = $this->data[$key];
        }
        //get variable values form the query string code ends here

        //make filter array  code starts here
        $filter_array = array(
         'filter_display_group' => $filter_display_group,
         'order'                => $order,
         'sort'                 => $sort,
         'start'                => ($page - 1) * $this->config->get('config_limit_admin'),
         'limit'                => $this->config->get('config_limit_admin'),
        );
        //make filter array code ends here

        $this->data['income_lists'] = array();
        $income_report = array();
        
        if($filter_display_type == 'order') {

            $income_result = $this->income->orderWiseEarning($this->customer->getId(),$filter_array);
            $total_income = $this->income->orderWiseEarning($this->customer->getId(),$filter_array,true);

            if($income_result) {
                foreach ($income_result as $key => $value) {
                    $this->data['income_lists'][] = array(
                     'date_start'    => $value['date_display'],
                     'order_total'   => $this->currency->format($this->currency->convert($value['order_total'], $this->config->get('config_currency'),$this->session->data['currency']),$this->session->data['currency']),
                     'admin_amount'  => $this->currency->format($this->currency->convert($value['admin_amount'], $this->config->get('config_currency'),$this->session->data['currency']),$this->session->data['currency']),
                     'seller_amount' => $this->currency->format($this->currency->convert($value['seller_amount'], $this->config->get('config_currency'),$this->session->data['currency']),$this->session->data['currency']),
                    );

                    $income_report[] = array(
                     'label'        => $value['date_display'],
                     'order_total'  => $value['order_total'],
                     'admin_amount' => $value['admin_amount'],
                     'seller_amount'=> $value['seller_amount'],
                    );
                }   
            }
            
        } else {

            $income_result = $this->income->getProductWiseEarnng($this->customer->getId(),$filter_array);
            $total_income = $this->income->getProductWiseEarnng($this->customer->getId(),$filter_array,true);

            if($income_result) {
                foreach ($income_result as $key => $value) {
                    $this->data['income_lists'][] = array(
                     'name'          => $value['name'],
                     'date_start'    => $value['date_display'],
                     'product_total' => $this->currency->format($this->currency->convert($value['product_total'], $this->config->get('config_currency'),$this->session->data['currency']),$this->session->data['currency']),
                     'admin_amount'  => $this->currency->format($this->currency->convert($value['admin_amount'], $this->config->get('config_currency'),$this->session->data['currency']),$this->session->data['currency']),
                     'seller_amount' => $this->currency->format($this->currency->convert($value['seller_amount'], $this->config->get('config_currency'),$this->session->data['currency']),$this->session->data['currency']),
                    );

                    $income_report[] = array(
                     'label'         => $value['date_display'],
                     'name'          => $value['name'],
                     'product_total' => $value['product_total'],
                     'admin_amount'  => $value['admin_amount'],
                     'seller_amount' => $value['seller_amount'],
                    );
                }
            }            
        }

        $this->data['income_report'] = json_encode($income_report);
        

        //pagination code starts here
        $url = $this->setRequestgetVar('page');

        $pagination = new Pagination();
        $pagination->total = $total_income;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link('account/customerpartner/income', '' . $url . '&page={page}', true);
        $this->data['pagination'] = $pagination->render();
        $this->data['results'] = sprintf($this->language->get('text_pagination'), ($total_income) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($total_income - $this->config->get('config_limit_admin'))) ? $total_income : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $total_income, ceil($total_income / $this->config->get('config_limit_admin')));


        //sort code starts here
        $url = $this->setRequestgetVar('sort');
        $this->data['sort_date'] = $this->url->link('account/customerpartner/income', 'sort=c2o.date_added' . $url, true);
        $this->data['sort_name'] = $this->url->link('account/customerpartner/income', 'sort=pd.name' . $url, true);
        $this->data['sort_product_total'] = $this->url->link('account/customerpartner/income', 'sort=product_total' . $url, true);
        $this->data['sort_order_total'] = $this->url->link('account/customerpartner/income', 'sort=order_total' . $url, true);
        $this->data['sort_admin_amount'] = $this->url->link('account/customerpartner/income', 'sort=admin_amount' . $url, true);
        $this->data['sort_seller_amount'] = $this->url->link('account/customerpartner/income', 'sort=seller_amount' . $url, true);


        $this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['content_top'] = $this->load->controller('common/content_top');
		$this->data['content_bottom'] = $this->load->controller('common/content_bottom');
		$this->data['footer'] = $this->load->controller('common/footer');
		$this->data['header'] = $this->load->controller('common/header');

        $this->data['separate_view'] = false;

        $this->data['separate_column_left'] = '';

        if ($this->config->get('marketplace_separate_view') && isset($this->session->data['marketplace_separate_view']) && $this->session->data['marketplace_separate_view'] == 'separate') {
            $this->data['separate_view'] = true;
            $this->data['column_left'] = '';
            $this->data['column_right'] = '';
            $this->data['content_top'] = '';
            $this->data['content_bottom'] = '';
            $this->data['separate_column_left'] = $this->load->controller('account/customerpartner/column_left');
            $this->data['footer'] = $this->load->controller('account/customerpartner/footer');
            $this->data['header'] = $this->load->controller('account/customerpartner/header');
        }


        $this->response->setOutput($this->load->view('account/customerpartner/income',$this->data));
    }



    /**
    * make Get filter URL string function
    */
    public function setRequestgetVar($type = '') {  

        $order = 'ASC';

        if(isset($this->request->get['order']) && $this->request->get['order'])
            $order = $this->request->get['order'];
     
        //setting get variable in URL code starts here
        $url = '';
     
        $uri_component = array(
         'filter_display_type',
         'filter_display_group',
         'sort',
         'page',
        );

        switch ($type) {
            case 'page':
                foreach ($uri_component as $key => $value) {
                    if($value != 'page')
                        $url .=  $this->ocutilities->_setStringURLs($value);
                }
                break;

            case 'sort':
                foreach ($uri_component as $key => $value) {
                    if($value != 'sort')
                    $url .=  $this->ocutilities->_setStringURLs($value);
                }
                if ($order == 'ASC') {
                    $order = 'DESC';
                } else {
                    $order = 'ASC';
                }
                break;
      
            default:
                foreach ($uri_component as $key => $value) {
                    $url .=  $this->ocutilities->_setStringURLs($value);
                }
                break;
        }

        $url .= '&order=' . $order;        
        
        //setting get variable in URL code ends here
        return $url;
    }

}
?>