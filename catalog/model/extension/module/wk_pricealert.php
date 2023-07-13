<?php
class ModelAccountWkPricealert extends Model {
  private $error = array();
  public function index() {
    $data = array();
    $data = array_merge($data, $this->load->language('language'));
  }
}
?>
