<?php
class ControllerBlogBlog extends Controller {
	public function index() {
		$this->load->language('blog/blog');

		//$this->load->model('blog/blog');
		$this->load->model('blog/article');		

		

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'sort_order';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['limit'])) {
			$limit = $this->request->get['limit'];
		} else {
			$limit = $this->config->get('module_blog_limit');
		}
		
		$this->document->setTitle($this->config->get('module_blog_meta_title'));
		$this->document->setDescription($this->config->get('meta_description'));
		$this->document->setKeywords($this->config->get('module_blog_meta_keyword'));

		$data['heading_title'] = $this->config->get('module_blog_meta_title');


		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_blog'),
			'href' => $this->url->link('blog/blog')
		);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}

		$data['articles'] = array();

		$filter_data = array(			
			'sort'               => $sort,
			'order'              => $order,
			'start'              => ($page - 1) * $limit,
			'limit'              => $limit
		);

		$article_total = $this->model_blog_article->getTotalArticles($filter_data);
		
		$results = $this->model_blog_article->getArticles($filter_data);

	//	print_r($results);

		$image_width = (int)$this->config->get('module_blog_width');
		$image_height = (int)$this->config->get('module_blog_height');
		$date_format = $this->config->get('module_blog_date_format');
		$this->load->model('tool/image');
		foreach($results as $result){
			$image_width = $this->config->get('module_blog_detail_width');
			$image_height = $this->config->get('module_blog_detail_height');
			$total_comments=  $this->model_blog_article->updateComment($result['article_id']);
			$time=strtotime($result['date_available']);
			$month=date("M",$time);
			$year=date("Y",$time);
			$day=date("d",$time);

			if($image_width && $image_height) {
				$image = $this->model_tool_image->resize($result['image'], $image_width, $image_height);
			} else {
				$image = $this->model_tool_image->resize($result['image'], 300, 300);
			}

			$data['articles'][] = array(
				'article_id'  		=> $result['article_id'],
				'name'        		=> $result['name'],
				'author_name'	  	=> $result['article_author'],
				'image'		  		=> $image,
				'thumb'		        =>$this->model_tool_image->resize($result['image'], $image_width, $image_height),			
				'intro_text' 		=> mb_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, 250) . '..',
				'date'		  		=> ($date_format ? date($date_format, strtotime($result['date_available'])) : false),
				'href'        		=> $this->url->link('blog/article', 'article_id=' . $result['article_id'] . $url),
				'day'   			=> $day,
				'month'   			=> 	$month,
				'total_comments' 	=> number_format($total_comments)
			);
		}

		$url = '';

		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$data['limits'] = array();

		$limits = array_unique(array($this->config->get('module_blog_limit'), 25, 50, 100));

		sort($limits);

		foreach($limits as $value) {
			$data['limits'][] = array(
				'text'  => $value,
				'value' => $value,
				'href'  => $this->url->link('blog/blog', $url . '&limit=' . $value)
			);
		}

		$url = '';

		
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}

		$pagination = new Pagination();
		$pagination->total = $article_total;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->url = $this->url->link('blog/blog', $url . '&page={page}');

		$data['pagination'] = $pagination->render();
		$data['text_empty'] = $this->language->get('text_empty');
		$data['results'] = sprintf($this->language->get('text_pagination'), ($article_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($article_total - $limit)) ? $article_total : ((($page - 1) * $limit) + $limit), $article_total, ceil($article_total / $limit));

		$data['sort'] = $sort;
		$data['order'] = $order;
		$data['limit'] = $limit;

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('blog/blog', $data));			
	} 
	
}
