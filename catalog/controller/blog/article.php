<?php
class ControllerBlogArticle extends Controller {
	private $error = array();
	
	public function index() {

		$this->load->language('blog/article');

		$data['breadcrumbs'] = array();		

		//$this->load->model('blog/blog');
		$this->load->model('blog/article');
		$this->load->model('setting/setting');
		

		if (isset($this->request->get['article_id'])) {
			$article_id = (int)$this->request->get['article_id'];
		} else {
			$article_id = 0;
		}		

		$article_info = $this->model_blog_article->getArticle($article_id);		

		//print_r($article_info);

		if ($article_info) {

			$url = '';
			
			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . $this->request->get['tag'];
			}

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/home')
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_blog'),
				'href' => $this->url->link('blog/blog')
			);

			$data['breadcrumbs'][] = array(
				'text' => $article_info['name'],
				'href' => $this->url->link('blog/article', '&article_id=' . $article_id)
			);
			
			$this->document->setTitle($article_info['meta_title']);
			$this->document->setDescription($article_info['meta_description']);
			$this->document->setKeywords($article_info['meta_keyword']);
			
			$this->document->addLink($this->url->link('blog/article', 'article_id=' . $article_id), 'canonical');	
			$data['date_time'] = array();		
			$data['article_id'] = $article_id;
			$data['heading_title'] = $article_info['name'];
			$data['blog_author'] = $article_info['article_author'];
			$data['description'] = html_entity_decode($article_info['description'], ENT_QUOTES, 'UTF-8');
			$data['intro_text'] = html_entity_decode($article_info['preview'], ENT_QUOTES, 'UTF-8');
			$date_format = $this->config->get('module_blog_date_format');      
      		$data['blog_date'] = ($date_format ? date($date_format, strtotime($article_info['date_available'])) : false);
			$this->load->model('tool/image');	
			//get settings width & height		
			$image_width = $this->config->get('module_blog_detail_width');
			$image_height = $this->config->get('module_blog_detail_height');			
			$data['image'] = $this->model_tool_image->resize($article_info['image'], $image_width, $image_height);

      		$data['href'] = $this->url->link('blog/article', 'article_id=' . $article_id);

      		//blog tags
			$data['tags'] = array();

			if ($article_info['tag']) {
				$tags = explode(',', $article_info['tag']);

				foreach ($tags as $tag) {
					$data['tags'][] = array(
						'name'  => trim($tag),
						//'href' => $this->url->link('product/search', 'tag=' . trim($tag))
					);
				}
			}
			
			$data['comment_count'] =  $this->model_blog_article->updateComment($article_id);
			$data['visitor_comment'] = $this->model_blog_article->getPostComments($article_id);
			$data['blog_category'] = $this->model_blog_article->getCategories($article_id);

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('blog/article', $data));
		} else {
			$url = '';			

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . $this->request->get['tag'];
			}

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/home')
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('blog/article', $url . '&article_id=' . $article_id)
			);

			$this->document->setTitle($this->language->get('text_error'));

			$data['heading_title'] = $this->language->get('text_error');

			$data['text_error'] = $this->language->get('text_error');

			$data['button_continue'] = $this->language->get('button_continue');

			$data['continue'] = $this->url->link('common/home');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}

	public function write() {

		$this->load->language('blog/article');

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if ((utf8_strlen($this->request->post['author']) < 3) || (utf8_strlen($this->request->post['author']) > 25)) {
                $json['error'][] = $this->language->get('text_error_author');

            }
            if ((utf8_strlen($this->request->post['email']) > 96) || !preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $this->request->post['email'])) {
                    $json['error'][] = $this->language->get('text_error_email');
            }
            if ((utf8_strlen($this->request->post['comment_text']) < 25) || (utf8_strlen($this->request->post['comment_text']) > 1000)) {
                $json['error'][] = $this->language->get('text_error_comment_text');
            }

			if (!isset($json['error'])) {
                $this->load->model('blog/article');                
                $json['success'] = $this->language->get('text_success');
                $this->model_blog_article->addReview($this->request->post);

				}
			}

			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
	}	
}
