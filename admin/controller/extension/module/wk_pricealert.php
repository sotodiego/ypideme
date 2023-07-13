<?php
/**
 * Webkul Software.
 * @category  Webkul
 * @author    Webkul
 * @copyright Copyright (c) 2010-2016 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
require_once DIR_SYSTEM . 'webkul/ocPAControllerTrait.php';

class ControllerExtensionModuleWkpricealert extends Controller {
    // use controller traits for the common fucntionality
    use ocPAControllerTrait;
    private $error = array();
    // private module code variable
    private $module_code = '';
    //set module paths
    private $module_path = 'extension/module/wk_pricealert';
    // private data variable
    private $data = array();
    // __constructer
    public function __construct($registory) {
      parent::__construct($registory);
      $this->data = $this->load->language($this->module_path);
      //load module model for the creating/doping table and managing sql
      $this->load->model($this->module_path);
      $this->helper_pricealert = $this->model_extension_module_wk_pricealert;
      //load event model
      $this->load->model('setting/event');
      $this->helper_event = $this->model_setting_event;
      //load setting model
      $this->load->model('setting/setting');
      $this->helper_setting = $this->model_setting_setting;
      //get all the key used ion the config into single array
      $this->initModulesKeys();
      // get module status variable
      $this->initModuleCode();
      // aGet All the set config values for the module
      $this->getModuleConfig();
    }
    /**
     * [initModuleKeyCode get all the common key code saved in the config for the module]
     * @return [type] [description]
     */
    public function initModulesKeys() {
      $this->data['module_keys'] = array(
        'status',
        'allow_guest',
        'custom_css',
        'limit_seller',
        'allow_seller',
        'notify_admin',
        'email_subject',
        'email_text',
        'email_notification',
        'email_notification_subject',
        'email_accept_subject',
        'email_accept_text',
        'custom_css_email_accept',
        'email_reject_subject',
        'email_reject_text',
        'custom_css_email_reject',
        'registered_request',
        'unregistered_request',
        'coupon_name',
        'coupon_validity'
      );
    }
    /**
     * [seniziteEditorForScriptValue to remove the script from the editor]
     * @param  array  $_array_editor [post key set of the post array]
     * @return [type]                [array]
     */
    public function seniziteEditorForScriptValue($_array_editor = array()) {
  		foreach ($_array_editor as $_post_key) {
        $_post_key = 'wk_pricealert_'.$_post_key;
  			if(isset($this->request->post[$_post_key]) && is_array($this->request->post[$_post_key])){
  				foreach ($this->request->post[$_post_key] as $key => $value) {
  					if(isset($this->request->post[$_post_key][$key])){
  						$this->request->post[$_post_key][$key] = preg_replace("/script.*?\/script/ius", " ", trim($this->request->post[$_post_key][$key]));
  					}
  				}
  			} else if(isset($this->request->post[$_post_key]) && is_string($this->request->post[$_post_key])) {
            $this->request->post[$_post_key] = preg_replace("/script.*?\/script/ius", " ", trim($this->request->post[$_post_key]));
        }
  		}
    }
    /**
     * [initModuleStatusCode stroe module code]
     * @return [type] [description]
     */
    public function initModuleCode() {
      $this->module_code = 'wk_pricealert';
    }
    /**
     * [install insallation will used to create table and requiered sql or other opration which is requiered to used module fundtinality]
     * @return [type] [description]
     */
    public function install() {
      $this->load->model($this->module_path);

      $this->helper_pricealert->createTables();

      $this->__registerEvents();
    }
    /**
     * [uninstall destroy all the data]
     * @return [type] [description]
     */
    public function uninstall() {
       $this->helper_pricealert->deleteTables();
       $this->__deleteEvents();
    }
    /**
     * [__registerEvents register event for the module]
     * @return [type] [description]
     */
    public function __registerEvents() {
      $code    = "wk_pa_add_column_menu";
      $trigger = "admin/view/common/column_left/before";
      $action  = "wk_pricealert/event/addCommonMenu";
      $this->helper_event->addEvent($code, $trigger, $action);

      //add event to add the menu in the admin end code ends here
      $code     = "wk_pa_addJs_product";
      $trigger  = "admin/view/catalog/product_form/before";
      $action   = "wk_pricealert/event/addProductPageJs";
      $this->helper_event->addEvent($code, $trigger, $action);

      //add event to add the menu in the admin end code ends here
      $code     = "wk_pa_product_model_add";
      $trigger  = "admin/model/catalog/product/addProduct/after";
      $action   = "wk_pricealert/event/addAlertProduct";
      $this->helper_event->addEvent($code, $trigger, $action);

      $code     = "wk_pa_product_model_update";
      $trigger  = "admin/model/catalog/product/editProduct/before";
      $action   = "wk_pricealert/event/updateAlertProduct";
      $this->helper_event->addEvent($code, $trigger, $action);

      //add event to add the menu in the admin end code ends here
      $code     = "wk_pricealert_addJs_edit";
      $trigger  = "admin/controller/catalog/product/add/before";
      $action   = "wk_pricealert/event/addProductPageJs";
      $this->helper_event->addEvent($code, $trigger, $action);
      //add event to add the menu in the admin end code ends here
      $code     = "wk_pricealert_addJs_add";
      $trigger  = "admin/controller/catalog/product/edit/before";
      $action   = "wk_pricealert/event/addProductPageJs";
      $this->helper_event->addEvent($code, $trigger, $action);

      $code     = "wk_pricealert_header";
      $trigger  = "catalog/view/common/header/before";
      $action   = "extension/module/wk_pricealert/addJsFile";
      $this->model_setting_event->addEvent($code, $trigger, $action);

      $code     = "wk_pricealert_account_view";
      $trigger  = "catalog/controller/account/account/before";
      $action   = "extension/module/wk_pricealert/addAccountPageJsFile";
      $this->model_setting_event->addEvent($code, $trigger, $action);

      $code     = "wk_pa_product_model_delete";
      $trigger  = "admin/model/catalog/product/deleteProduct/before";
      $action   = "wk_pricealert/event/deleteAlertProduct";
      $this->helper_event->addEvent($code, $trigger, $action);
    }

    /**
     * [__deleteEvents unregister remove the event while uninstall the module]
     * @return [type] [description]
     */
    public function __deleteEvents() {
      $_deleteEventCodes_ = array(
        'wk_pa_add_column_menu',
        'wk_pa_addJs_product',
        'wk_pa_product_model_delete',
        'wk_pricealert_account_view',
        'wk_pricealert_header',
        'wk_pricealert_addJs_add',
        'wk_pricealert_addJs_edit',
        'wk_pa_product_model_update',
        'wk_pa_product_model_add',
      );

      foreach ($_deleteEventCodes_ as $_DE_code) {
        $this->helper_event->deleteEventByCode($_DE_code);
      }
    }

    /**
     * [getModuleConfig get all the config key value in the single array]
     * @return [type] [description]
     */
   public function getModuleConfig() {
      $this->data['config_value'] =  array_merge($this->helper_setting->getSetting($this->module_code),$this->helper_setting->getSetting('module_'.$this->module_code));
   }

   public function save() {
      if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

          $post_keys =  array(
            'custom_css',
            'email_subject',
            'email_text',
            'email_notification',
            'email_notification_subject',
            'email_accept_subject',
            'email_accept_text',
            'custom_css_email_accept',
            'email_reject_subject',
            'email_reject_text',
            'custom_css_email_reject',
            'coupon_name',
            'coupon_validity'
          );

          $this->seniziteEditorForScriptValue($post_keys);

          $this->model_setting_setting->editSetting($this->module_code, $this->request->post);
          $this->model_setting_setting->editSetting('module_'.$this->module_code, $this->request->post);

          $this->session->data['success'] = $this->language->get('text_success');

          $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
      }
      $this->index();
    }

    public function index() {

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }
        $array = array(
          'wk_pricealert_registered_request',
          'wk_pricealert_unregistered_request',
          'wk_pricealert_coupon_validity'
        );

        foreach ($array as $key => $value) {
          if (isset($this->error['error_'.$value])) {
             $this->data['error_'.$value] = $this->error['error_'.$value];
          } else {
            $this->data['error_'.$value] = '';
          }
        }

        if (isset($this->error['error_wk_pricealert_coupon_name'])) {
    			$this->data['error_wk_pricealert_coupon_name'] = $this->error['error_wk_pricealert_coupon_name'];
          $this->data['tab_cp'] ='error';
    		} else {
    			$this->data['error_wk_pricealert_coupon_name'] = array();
          $this->data['tab_cp'] = '';
    		}

        $this->data['breadcrumbs'] = array();

        $this->breadcrumbs = array(
          $this->language->get('text_home') => 'common/dashboard',
          $this->language->get('text_extension') => 'marketplace/extension&type=module',
          $this->language->get('heading_title') => $this->module_path,
        );
        // loop the breaccrumb
        $this->genrateCBreadcrumb($this->breadcrumbs);
        //load language for the language wise mail template and other purpose
        $this->load->model('localisation/language');
        //get all the current laguage details
    		$this->data['languages'] = $this->model_localisation_language->getLanguages();
        //get all the config key and value stored in the this $this->data['config_value']
        $this->getModuleConfig();

        foreach ($this->data['module_keys'] as $alert_value) {
    			if (isset($this->request->post[$this->module_code.'_'. $alert_value])) {
    				$this->data[$this->module_code .'_'. $alert_value] = $this->request->post[$this->module_code .'_'. $alert_value];
    			} else {
    				$this->data[$this->module_code .'_'. $alert_value] = isset($this->data['config_value'][$this->module_code .'_'. $alert_value]) ? $this->data['config_value'][$this->module_code .'_'. $alert_value] : '';
    			}
    		}

        if (isset($this->request->post['module_'.$this->module_code.'_status'])) {
          $this->data[$this->module_code .'_status'] = $this->request->post['module_'.$this->module_code.'_status'];
        } else {
          $this->data[$this->module_code .'_status'] = isset($this->data['config_value']['module_'.$this->module_code.'_status']) ? $this->data['config_value']['module_'.$this->module_code.'_status'] : 0;
        }

        $this->data['action'] = $this->url->link($this->module_path.'/save', 'user_token=' . $this->session->data['user_token'], true);

        $this->data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        $this->_loadCommonControllers();

        $this->response->setOutput($this->load->view($this->module_path, $this->data));
   }

   public function _loadCommonControllers() {
    $this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['footer'] = $this->load->controller('common/footer');
   }

   public function __senatizationPost() {
     //sentization part
     $post_keys =  array(
       'custom_css',
       'email_subject',
       'email_text',
       'email_notification',
       'email_notification_subject',
       'email_accept_subject',
       'email_accept_text',
       'custom_css_email_accept',
       'email_reject_subject',
       'email_reject_text',
       'custom_css_email_reject',
       'coupon_name',
       'coupon_validity'
     );

     foreach ($post_keys as $key => $post) {
       if(isset($this->request->post[$post]) && $this->request->post[$post]) {
          $this->sentizeScript($post);
       }
     }
   }

   public function validate() {

     if (!$this->user->hasPermission('modify', $this->module_path)) {
       $this->error['warning'] = $this->language->get('error_permission');
     }

      //__senatizationPost for the senitizer purpose
      $this->__senatizationPost();
      $array = array(
        'wk_pricealert_registered_request',
        'wk_pricealert_unregistered_request',
      );
      foreach ($array as $key => $value) {
        if (isset($this->request->post[$value])) {
         if($this->request->post[$value] > 999 || $this->request->post[$value] <= 0) {
            $this->error['error_'.$value] = $this->language->get('error_range');
            $this->error['warning'] = $this->language->get('error_warning');
         }
        }
      }

      if (isset($this->request->post['wk_pricealert_coupon_validity'])) {
       if($this->request->post['wk_pricealert_coupon_validity'] > 100 || $this->request->post['wk_pricealert_coupon_validity'] <= 0) {
          $this->error['error_wk_pricealert_coupon_validity'] = $this->language->get('error_crange');
          $this->error['warning'] = $this->language->get('error_warning');
       }
      }

     if(isset($this->request->post['wk_pricealert_coupon_name'])) {
       foreach ($this->request->post['wk_pricealert_coupon_name'] as $language_id => $value) {
         if ((utf8_strlen(trim($value)) < 1) || (utf8_strlen(trim($value)) > 64)) {
           $this->error['error_wk_pricealert_coupon_name'][$language_id] = $this->language->get('error_coupan');
           $this->error['warning'] = $this->language->get('error_warning');
         }
       }
     }

      $this->validatePermissions($this->module_path);

      return !$this->error;
    }

    public function sentizeScript($_p_key) {
        $this->request->post[$_p_key] = preg_replace("/script.*?\/script/ius", " ", trim($this->request->post[$_p_key]));
    }

}
