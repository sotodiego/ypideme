<?php
/**
 * Webkul Software.
 * @category  Webkul
 * @author    Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

trait OcMpTrait {

 private function checkMpModuleStatus() {
   if (!$this->config->get('module_marketplace_status')) {
     $this->session->data['redirect'] = $this->url->link('account/customerpartner/crosssell', '', true);
     $this->response->redirect($this->url->link('account/account', '', true));
   }
 }

 private function checkMpProModuleStatus($config_key, $redirect) {
   if (!$this->config->get($config_key)) {
     $this->session->data['redirect'] = $this->url->link($redirect, '', true);
     $this->response->redirect($this->url->link('account/account', '', true));
   }
 }


}
