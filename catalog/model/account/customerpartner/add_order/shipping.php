<?php
class ModelAccountCustomerpartnerAddOrderShipping extends Model {
	public function getTotal($total,$true = false) {

		if($true) {
			$this->load->model('account/customerpartner/add_order/cart');

			$this->add_order_cart = $this->model_account_customerpartner_add_order_cart;

			if ($this->add_order_cart->hasShipping() && isset($this->session->data['add_order_customer']['shipping_method'])) {
			$total['totals'][] = array(
				'code'       => 'shipping',
				'title'      => $this->session->data['add_order_customer']['shipping_method']['title'],
				'value'      => $this->session->data['add_order_customer']['shipping_method']['cost'],
				'sort_order' => $this->config->get('total_shipping_sort_order')
			);

			if ($this->session->data['add_order_customer']['shipping_method']['tax_class_id']) {
				$tax_rates = $this->tax->getRates($this->session->data['add_order_customer']['shipping_method']['cost'], $this->session->data['add_order_customer']['shipping_method']['tax_class_id']);

				foreach ($tax_rates as $tax_rate) {
					if (!isset($total['taxes'][$tax_rate['tax_rate_id']])) {
						$total['taxes'][$tax_rate['tax_rate_id']] = $tax_rate['amount'];
					} else {
						$total['taxes'][$tax_rate['tax_rate_id']] += $tax_rate['amount'];
					}
				}
			}

			$total['total'] += $this->session->data['add_order_customer']['shipping_method']['cost'];
		}

		} else {
		if ($this->cart->hasShipping() && isset($this->session->data['shipping_method'])) {
			$total['totals'][] = array(
				'code'       => 'shipping',
				'title'      => $this->session->data['shipping_method']['title'],
				'value'      => $this->session->data['shipping_method']['cost'],
				'sort_order' => $this->config->get('total_shipping_sort_order')
			);

			if ($this->session->data['shipping_method']['tax_class_id']) {
				$tax_rates = $this->tax->getRates($this->session->data['shipping_method']['cost'], $this->session->data['shipping_method']['tax_class_id']);

				foreach ($tax_rates as $tax_rate) {
					if (!isset($total['taxes'][$tax_rate['tax_rate_id']])) {
						$total['taxes'][$tax_rate['tax_rate_id']] = $tax_rate['amount'];
					} else {
						$total['taxes'][$tax_rate['tax_rate_id']] += $tax_rate['amount'];
					}
				}
			}

			$total['total'] += $this->session->data['shipping_method']['cost'];
		}
		}
	}
}