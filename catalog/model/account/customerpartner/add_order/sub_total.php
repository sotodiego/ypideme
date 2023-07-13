<?php
class ModelAccountCustomerpartnerAddOrderSubTotal extends Model {
	public function getTotal($total,$true = false) {
		$this->load->language('extension/total/sub_total');

		if($true) {
			$this->load->model('account/customerpartner/add_order/cart');

			$this->add_order_cart = $this->model_account_customerpartner_add_order_cart;
	 
			 $sub_total = $this->model_account_customerpartner_add_order_cart->getSubTotal();
	 
			 if (!empty($this->session->data['add_order_customer']['vouchers'])) {
				 foreach ($this->session->data['add_order_customer']['vouchers'] as $voucher) {
					 $sub_total += $voucher['amount'];
				 }
			 }
		} else {
			$sub_total = $this->cart->getSubTotal();

			if (!empty($this->session->data['vouchers'])) {
				foreach ($this->session->data['vouchers'] as $voucher) {
					$sub_total += $voucher['amount'];
				}
			}
		}		

		$total['totals'][] = array(
			'code'       => 'sub_total',
			'title'      => $this->language->get('text_sub_total'),
			'value'      => $sub_total,
			'sort_order' => $this->config->get('sub_total_sort_order')
		);

		$total['total'] += $sub_total;
	}
}
