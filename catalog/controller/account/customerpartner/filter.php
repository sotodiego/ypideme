<?php
class ControllerAccountCustomerpartnerFilter extends Controller {
	public function index() {

	}

  public function addHtml()  {
    $json = array();
    $json = $this->load->language('account/customerpartner/filter');
    $json['marketplace_status']	= $this->config->get('marketplace_status');
    $json['tilte'] = $this->language->get('heading_title');;
    $json['placeholder'] = $this->language->get('text_placeholder');
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
  }
  public function ajaxFilter()  {
    $json = array();
    $json = $this->load->language('account/customerpartner/filter');
    $json['marketplace_status']	= $this->config->get('marketplace_status');
    $json['tilte'] = $this->language->get('heading_title');;
    $json['placeholder'] = $this->language->get('text_placeholder');
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
  }

  public function ajaxAutocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			// $this->load->model('customerpartner/filter');
			if (isset($this->request->get['filter_name'])) {
				$filter_name = $this->request->get['filter_name'];
			} else {
				$filter_name = '';
			}
			$data = array(
				'filter_name'         => $filter_name,
				'limit'               => 20
			);
			$results = $this->getSeller($data);
  
			foreach ($results as $result) {

				$option_data = array();

				$json[] = array(
					'id' 		 => $result['customer_id'],
					'name'       => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
					'email'      => $result['email'],
				);
			}
		}
		$this->response->setOutput(json_encode($json));
	}

  public function getSeller($data = array()) {

		$add = ' c2c.is_partner = 1 AND';

		$sql = "SELECT *,c.status, CONCAT(c.firstname, ' ', c.lastname) AS name,c.customer_id AS customer_id, c2c.is_partner,cgd.name AS customer_group FROM " . DB_PREFIX . "customer c LEFT JOIN " . DB_PREFIX . "customer_group_description cgd ON (c.customer_group_id = cgd.customer_group_id) LEFT JOIN ". DB_PREFIX . "customerpartner_to_customer c2c ON (c2c.customer_id = c.customer_id) WHERE ". $add ." cgd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		$implode = array();

		if (!empty($data['filter_name'])) {
			$implode[] = "LCASE(CONCAT(c.firstname, ' ', c.lastname)) LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "%'";
		}

		if ($implode) {
			$sql .= " AND " . implode(" AND ", $implode);
		}


		$sql .= " ORDER BY c.customer_id";
		$sql .= " ASC ";
		$sql .= " LIMIT 0,20";

		$query = $this->db->query($sql);
		return $query->rows;
	}


}
