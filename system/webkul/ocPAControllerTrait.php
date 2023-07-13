<?php
/**
 * Webkul Software.
 * @category  Webkul
 * @author    Webkul
 * @copyright Copyright (c) 2010-2016 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

trait OcPAControllerTrait {

  private function validatePermissions($path = '') {
    if (!$this->user->hasPermission('modify', $path)) {
      $this->error['warning'] = $this->language->get('error_permission');
    }
  }

  private function genrateCBreadcrumb($breadcrumbs = array()) {
    $this->data['breadcrumbs'] = array();
     foreach ($breadcrumbs as $key => $value) {
       $this->data['breadcrumbs'][] = array(
         'text'          => $key,
         'href'          => $this->url->link($value, 'user_token=' . $this->session->data['user_token'], true),
       );
     }
  }

  private function _loadCommonControllers() {
    $this->data['footer'] = $this->load->controller('common/footer');
    $this->data['header'] = $this->load->controller('common/header');
  }

  private function _loadCustomerCommonControllers() {
    $this->data['column_left'] = $this->load->controller('common/column_left');
    $this->data['column_right'] = $this->load->controller('common/column_right');
    $this->data['content_top'] = $this->load->controller('common/content_top');
    $this->data['content_bottom'] = $this->load->controller('common/content_bottom');
  }

  public function _getSeprateViewCommonController() {
    $this->data['column_left']    = '';
    $this->data['column_right']   = '';
    $this->data['content_top']    = '';
    $this->data['content_bottom'] = '';
    $this->data['separate_column_left'] = $this->load->controller('account/customerpartner/column_left');
    $this->data['footer'] = $this->load->controller('account/customerpartner/footer');
    $this->data['header'] = $this->load->controller('account/customerpartner/header');
  }

  private function initBreadCrumbs(){
      $this->breadcrumbs = array(
         $this->language->get('text_home') => 'common/home',
         $this->language->get('heading_title') => $this->file_path,
      );
  }

  private function initAddPageBreadCrumbs(){
      $this->breadcrumbs = array(
         $this->language->get('text_home') => 'common/home',
         $this->language->get('heading_title') => $this->file_path,
         $this->language->get('heading_title_add') => $this->file_path.'/add',
      );
  }

}
