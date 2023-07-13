<?php
class ControllerExtensionModuleSuperbar extends Controller {
	public function index() {
		if ($this->config->get('module_marketplace_status')) {

			$this->load->language('extension/module/superbar');
			$data['heading_title'] = $this->language->get('heading_title');
			$data['text_superbar'] = $this->language->get('text_superbar');
			$this->document->addScript('catalog/view/javascript/promo/countdown.js');
			$this->document->addScript('catalog/view/javascript/jquery-ui/jquery-ui.min.js');
			$this->document->addScript('catalog/view/javascript/promo/time_manager.js');

			$language_id = $this->config->get('config_language_id');

			$data['superbar_status'] = $this->config->get('module_superbar_status');
			$data['superbar_width'] = $this->config->get('superbar_width');
			$data['superbar_border_color'] = $this->config->get('superbar_border_color');
			$data['superbar_border_style'] = $this->config->get('superbar_border_style');
			$data['superbar_button_color'] = $this->config->get('superbar_button_color');
			$data['superbar_color'] = $this->config->get('superbar_color');
			$data['superbar_design_layout_icon'] = $this->config->get('superbar_design_layout_icon');
			$data['superbar_upsell_tooltip'] = $this->config->get('superbar_upsell_tooltip')[$language_id];
			$data['superbar_crosssell_tooltip'] = $this->config->get('superbar_crosssell_tooltip')[$language_id];
			$data['superbar_gift_tooltip'] = $this->config->get('superbar_gift_tooltip')[$language_id];
			$data['superbar_custom_tooltip'] = $this->config->get('superbar_custom_tooltip');
			$data['superbar_upsell_icon'] = $this->config->get('superbar_upsell_icon');
			$data['superbar_crosssell_icon'] = $this->config->get('superbar_crosssell_icon');
			$data['superbar_gift_icon'] = $this->config->get('superbar_gift_icon');
			$data['superbar_custom_icon'] = $this->config->get('superbar_custom_icon');
			$data['upsell_title'] = $this->config->get('wk_widget_title')[$language_id];
			$data['crosssell_title'] = $this->config->get('wk_cwidget_title')[$language_id];
			$data['gift_title'] = $this->config->get('wk_gwidget_title')[$language_id];
			$data['upsell_status'] = $this->config->get('module_wk_upsell_upsell_status');
			$data['crosssell_status'] = $this->config->get('module_wk_crosssell_crosssell_status');
			$data['gift_status'] = $this->config->get('wk_gift_gift_status');
			$data['custom_status'] = $this->config->get('wk_custom_custom_status');
			$data['bwidth'] = $iwidth = $this->config->get('superbar_icon_width');
			$data['bheight'] = $iheight = $this->config->get('superbar_icon_height');
			$data['hposition'] = $this->config->get('superbar_position_hori') ? 'left' : 'right';
			$data['hper'] = $this->config->get('superbar_hori');
			$data['vposition'] = $this->config->get('superbar_position_verti') ? 'top' : 'bottom';
			$data['vper'] = $this->config->get('superbar_verti');
			$data['refresh_time'] = $this->config->get('superbar_content_time') ? $this->config->get('superbar_content_time') : 0;
			$data['struck_time'] = $this->config->get('superbar_struck_time') ? $this->config->get('superbar_struck_time') : 10;
			$data['superbar_time'] = $this->config->get('superbar_time') ? $this->config->get('superbar_time') : 0;
			$data['uheader1'] = addslashes(html_entity_decode($this->config->get('wk_widget_upselling_details')[$language_id]));
			$data['uheader2'] = addslashes(html_entity_decode($this->config->get('wk_widget_upselling_details2')[$language_id]));
			$data['cheader1'] = addslashes(html_entity_decode($this->config->get('wk_cwidget_crossselling_details')[$language_id]));
			$data['cheader2'] = addslashes(html_entity_decode($this->config->get('wk_cwidget_crossselling_details2')[$language_id]));
			$data['gheader1'] = addslashes(html_entity_decode($this->config->get('wk_gwidget_gifting_details')[$language_id]));
			$data['gheader2'] = addslashes(html_entity_decode($this->config->get('wk_gwidget_gifting_details2')[$language_id]));

			$this->load->model('account/promotional');

			if (isset($this->request->get['product_id']) && $this->request->get['product_id']) {
				$product_id = $this->request->get['product_id'];
				$upsell = $this->model_account_promotional->getUpsells($product_id);

				if (!$upsell) {
					$data['upsell_status'] = false;
				}

				$crosssell = $this->model_account_promotional->getCrosssells($product_id);

				if (!$crosssell) {
					$data['crosssell_status'] = false;
				}

				if (!$data['upsell_status'] && !$data['crosssell_status']) {
					$data['superbar_status'] = false;
				}

			}

			$this->load->model('tool/image');

			if ($data['superbar_design_layout_icon']) {
				$data['superbar_design_layout_icon'] = $this->model_tool_image->resize($data['superbar_design_layout_icon'], $data['superbar_width'], $iheight + 10);
			} else {
				$data['superbar_design_layout_icon'] = '';
			}

			if ($data['superbar_upsell_icon']) {
				$data['superbar_upsell_icon'] = $this->model_tool_image->resize($data['superbar_upsell_icon'], $iwidth, $iheight);
			} else {
				$data['superbar_upsell_icon'] = $this->model_tool_image->resize('placeholder.png', $iwidth, $iheight);
			}

			if ($data['superbar_crosssell_icon']) {
				$data['superbar_crosssell_icon'] = $this->model_tool_image->resize($data['superbar_crosssell_icon'], $iwidth, $iheight);
			} else {
				$data['superbar_crosssell_icon'] = $this->model_tool_image->resize('placeholder.png', $iwidth, $iheight);
			}

			if ($data['superbar_gift_icon']) {
				$data['superbar_gift_icon'] = $this->model_tool_image->resize($data['superbar_gift_icon'], $iwidth, $iheight);
			} else {
				$data['superbar_gift_icon'] = $this->model_tool_image->resize('placeholder.png', $iwidth, $iheight);
			}

			if ($data['superbar_custom_icon']) {
				$data['superbar_custom_icon'] = $this->model_tool_image->resize($data['superbar_custom_icon'], $iwidth, $iheight);
			} else {
				$data['superbar_custom_icon'] = $this->model_tool_image->resize('placeholder.png', $iwidth, $iheight);
			}

			return $this->load->view('extension/module/superbar', $data);
		} // if Marketplace Is enabled then only refrlect
	}

	public function ajax() {
		$json = array(
				'superbar_status'	=> true,
				'upsell_status'		=> true,
				'crosssell_status'	=> true,
			);

		$this->load->model('account/promotional');
    if ($this->config->get('module_marketplace_status')) {
			if (isset($this->request->get['product_id']) && $this->request->get['product_id']) {
				$product_id = $this->request->get['product_id'];
				$upsell = $this->model_account_promotional->getUpsells($product_id);

				if (!$upsell) {
					$json['upsell_status'] = false;
				}

				$crosssell = $this->model_account_promotional->getCrosssells($product_id);

				if (!$crosssell) {
					$json['crosssell_status'] = false;
				}

				if (!$json['upsell_status'] && !$json['crosssell_status']) {
					$json['superbar_status'] = false;
				}
	    }
		} else {
			$json['superbar_status'] = false;
			$json['crosssell_status'] = false;
			$json['upsell_status'] = false;
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
