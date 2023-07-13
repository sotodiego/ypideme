<?php
class ControllerExtensionModuleBlog extends Controller {

	public function index($setting) {

		$this->load->language('extension/module/blog');
		$this->load->model('blog/article');
		

		if (isset($setting['module_description'][$this->config->get('config_language_id')])) {
			$data['heading_title'] = html_entity_decode($setting['module_description'][$this->config->get('config_language_id')]['title'], ENT_QUOTES, 'UTF-8');

		} else {			
			$data['heading_title'] = $this->language->get('heading_title');			
		}		

		if (isset($setting['limit'])) {
			$limit = $setting['limit'];
		} else {
			$limit = 10;
		}

		if (isset($setting['rows'])) {
			$rows = $setting['rows'];
		} else {
			$rows = 1;
		}

		if (isset($setting['items'])) {
			$items = $setting['items'];
		} else {
			$items = 4;
		}

		if (isset($setting['speed'])) {
			$speed = $setting['speed'];
		} else {
			$speed = 3000;
		}

		if (isset($setting['auto']) && $setting['auto']) {
			$auto = true;
		} else {
			$auto = false;
		}

		if (isset($setting['navigation']) && $setting['navigation']) {
			$navigation = true;
		} else {
			$navigation = false;
		}

		if (isset($setting['pagination']) && $setting['pagination']) {
			$pagination = true;
		} else {
			$pagination = false;
		}
		
		$date_format	= $setting['date_format'];	
		
		//print_r($setting);
		$filter_data = array(					
			'start'              => 0,
			'limit'              => $limit
		);			

		$results = $this->model_blog_article->getArticles($filter_data);

		//print_r($results);
		$this->load->model('tool/image');
		foreach ($results as $result) {
			$image_width = $this->config->get('module_blog_detail_width');
			$image_height = $this->config->get('module_blog_detail_height');			
			$time=strtotime($result['date_available']);
			$month=date("F",$time);
			$year=date("Y",$time);
			$day=date("d",$time);
			$total_comments=  $this->model_blog_article->updateComment($result['article_id']);
 			//$CategoryName =  $this->model_blog_article->getCategories($result['article_id']);
 			
			$data['articles'][] = array(
				'article_id'  		=> $result['article_id'],
				'article_author'  	=> $result['article_author'],
				'image'		  		=> $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height']),
				'thumb'		        =>$this->model_tool_image->resize($result['image'], $image_width, $image_height),
				'name'        		=> $result['name'],
				'preview'  			=> html_entity_decode($result['preview'], ENT_QUOTES, 'UTF-8'),
				'href'         		=> $this->url->link('blog/article', 'article_id=' . $result['article_id']),
				'date'   			=> ($date_format ? date($date_format, strtotime($result['date_available'])) : false),
				//'category_name' 	=> $CategoryName,
				'total_comments' 	=> number_format($total_comments),
				'day'   			=> $day,
				'year'   			=> $year,
				'month'   			=> 	$month
			);
		}

		$data['slide'] = array(
			'auto' 		 => $auto,
			'rows' 		 => $rows,
			'navigation' => $navigation,
			'pagination' => $pagination,
			'speed' 	 => $speed,
			'items' 	 => $items
		);


		$data['text_more'] = $this->language->get('text_more');
		$data['text_date_added'] = $this->language->get('text_date_added');
		$data['text_empty'] = $this->language->get('text_empty');

	    if (file_exists(DIR_TEMPLATE . $this->config->get('config_theme') . '/template/extension/module/blog.twig')) {
			return $this->load->view('extension/module/blog', $data);
		} else {
			return;
		}
	}
}