<?php
/**
 * @version [1.0.0.0] [Supported opencart version 3.x.x.x]
 * @category Webkul
 * @package Opencart-Webkul
 * @author [Webkul] <[<http://webkul.com/>]>
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

class Ocvalidator {
	private $customer_id;
	private $firstname;
	private $lastname;

	public function __construct($registry) {
		$this->config 	= $registry->get('config');
		$this->db 		  = $registry->get('db');
		$this->request 	= $registry->get('request');
		$this->session 	= $registry->get('session');
	}



}
