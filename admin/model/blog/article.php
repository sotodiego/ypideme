<?php
class ModelBlogArticle extends Model {
	public function install() {
			// Add tables in databse if not exists.
			$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."newsblog_article` (
			`article_id` INT(11) NOT NULL AUTO_INCREMENT,
			`image` VARCHAR(255) DEFAULT NULL,
			`date_available` DATETIME NOT NULL,
			`sort_order` INT(11) NOT NULL DEFAULT '0',
			`status` TINYINT(1) NOT NULL DEFAULT '0',
			`viewed` INT(5) NOT NULL DEFAULT '0',
			`date_added` DATETIME NOT NULL,
			`date_modified` DATETIME NOT NULL,
			PRIMARY KEY (`article_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");

			// $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."newsblog_article_attribute` (
			// `article_id` int(11) NOT NULL,
			// `attribute_id` int(11) NOT NULL,
			// `language_id` int(11) NOT NULL,
			// `text` text NOT NULL,
			// PRIMARY KEY (`article_id`,`attribute_id`,`language_id`)
			// ) ENGINE=MyISAM DEFAULT CHARSET=utf8");

			$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."newsblog_article_description` (
			`article_id` INT(11) NOT NULL,
			`language_id` INT(11) NOT NULL,
			`name` VARCHAR(255) NOT NULL,
			`preview` TEXT NOT NULL,
			`description` TEXT NOT NULL,
			`tag` TEXT NOT NULL,
			`meta_title` VARCHAR(255) NOT NULL,			
			`meta_description` VARCHAR(255) NOT NULL,
			`meta_keyword` VARCHAR(255) NOT NULL,
			`article_author` VARCHAR(255) NOT NULL,
			PRIMARY KEY (`article_id`,`language_id`),
			KEY `name` (`name`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8");

			$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."newsblog_article_image` (
			`product_image_id` INT(11) NOT NULL AUTO_INCREMENT,
			`article_id` INT(11) NOT NULL,
			`image` VARCHAR(255) DEFAULT NULL,
			`sort_order` INT(3) NOT NULL DEFAULT '0',
			PRIMARY KEY (`product_image_id`),
			KEY `article_id` (`article_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");

			// $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."newsblog_article_related` (
			// `article_id` int(11) NOT NULL,
			// `related_id` int(11) NOT NULL,
			// `type` int(1) NOT NULL,
			// PRIMARY KEY (`article_id`,`related_id`)
			// ) ENGINE=MyISAM DEFAULT CHARSET=utf8");

			$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."newsblog_article_to_category` (
			`article_id` INT(11) NOT NULL,
			`category_id` INT(11) NOT NULL,
			`main_category` TINYINT(1) NOT NULL DEFAULT '0',
			PRIMARY KEY (`article_id`,`category_id`),
			KEY `category_id` (`category_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8");

			// $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."newsblog_article_to_layout` (
			// `article_id` int(11) NOT NULL,
			// `store_id` int(11) NOT NULL,
			// `layout_id` int(11) NOT NULL,
			// PRIMARY KEY (`article_id`,`store_id`)
			// ) ENGINE=MyISAM DEFAULT CHARSET=utf8");

			$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."newsblog_article_to_store` (
			`article_id` INT(11) NOT NULL,
			`store_id` INT(11) NOT NULL DEFAULT '0',
			PRIMARY KEY (`article_id`,`store_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8");

			// $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."newsblog_category` (
			// `category_id` INT(11) NOT NULL AUTO_INCREMENT,
			// `name` VARCHAR(255) DEFAULT NULL,
			// `image` VARCHAR(255) DEFAULT NULL,
			// `parent_id` INT(11) NOT NULL DEFAULT '0',
			// `sort_order` INT(3) NOT NULL DEFAULT '0',
			// `status` TINYINT(1) NOT NULL,
			// `date_added` DATETIME NOT NULL,
			// `date_modified` DATETIME NOT NULL,
			// `settings` TEXT NOT NULL,
			// PRIMARY KEY (`category_id`),
			// INDEX `name` (`name`)
			// ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");

			$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."newsblog_category` (
			`category_id` INT(11) NOT NULL AUTO_INCREMENT,
			`name` VARCHAR(255) DEFAULT NULL,
			`sort_order` INT(3) NOT NULL DEFAULT '0',
			`status` TINYINT(1) NOT NULL,			
			PRIMARY KEY (`category_id`),
			INDEX `name` (`name`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");


			// $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."newsblog_category_list` (
			// `category_id` INT(11) NOT NULL,
			// `article_id` INT(11) NOT NULL
			// ) ENGINE=MyISAM DEFAULT CHARSET=utf8");

			// $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."newsblog_category_description` (
			// `category_id` INT(11) NOT NULL,
			// `language_id` INT(11) NOT NULL,
			// `name` VARCHAR(255) NOT NULL,
			// `description` TEXT NOT NULL,
			// `meta_title` VARCHAR(255) NOT NULL,
			// `meta_h1` VARCHAR(255) NOT NULL,
			// `meta_description` VARCHAR(255) NOT NULL,
			// `meta_keyword` VARCHAR(255) NOT NULL,
			// PRIMARY KEY (`category_id`,`language_id`),
			// KEY `name` (`name`)
			// ) ENGINE=MyISAM DEFAULT CHARSET=utf8");

			// $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."newsblog_category_path` (
			// `category_id` INT(11) NOT NULL,
			// `path_id` INT(11) NOT NULL,
			// `level` INT(11) NOT NULL,
			// PRIMARY KEY (`category_id`,`path_id`)
			// ) ENGINE=MyISAM DEFAULT CHARSET=utf8");

			// $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."newsblog_category_to_layout` (
			// `category_id` int(11) NOT NULL,
			// `store_id` int(11) NOT NULL,
			// `layout_id` int(11) NOT NULL,
			// PRIMARY KEY (`category_id`,`store_id`)
			// ) ENGINE=MyISAM DEFAULT CHARSET=utf8");

			// $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."newsblog_category_to_store` (
			// `category_id` INT(11) NOT NULL,
			// `store_id` INT(11) NOT NULL,
			// PRIMARY KEY (`category_id`,`store_id`)
			// ) ENGINE=MyISAM DEFAULT CHARSET=utf8");

			$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."url_alias` (
				`url_alias_id` INT(11) NOT NULL,
				`query` VARCHAR(255) NOT NULL,
				`keyword` VARCHAR(255) NOT NULL
			) ENGINE=MyISAM DEFAULT CHARSET=utf8");

			$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."newsblog_article_comment` (
			`comment_id` INT(11) NOT NULL AUTO_INCREMENT,
			`article_id` INT(11) NOT NULL,
			`author` VARCHAR(255) NOT NULL,
			`email` VARCHAR(255) NOT NULL,
			`comment_text` TEXT NOT NULL,
			`status` TINYINT(1) NOT NULL DEFAULT '0',
			`sort_order` TINYINT(1) NOT NULL DEFAULT '0',
			`date_added` DATETIME NOT NULL,
			`date_modified` DATETIME NOT NULL,
			 PRIMARY KEY (`comment_id`),
			 KEY `article_id` (`article_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");

	}


	public function uninstall() {

		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "newsblog_article`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "newsblog_article_attribute`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "newsblog_article_description`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "newsblog_article_image`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "newsblog_article_related`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "newsblog_article_to_category`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "newsblog_article_to_layout`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "newsblog_article_to_store`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "newsblog_category`");
		//$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "newsblog_category_description`");
		//$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "newsblog_category_path`");
		//$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "newsblog_category_to_layout`");
	//	$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "newsblog_category_to_store`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "url_alias`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "newsblog_article_comment`");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `code` = 'module_blog'");
		//$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "newsblog_category_list`");

	}
	public function addArticle($data) {

		$this->db->query("INSERT INTO " . DB_PREFIX . "newsblog_article SET date_available = '" . $this->db->escape(date('Y-m-d H:i:s')) . "', status = '" . (int)$data['status'] . "', sort_order = '" . (int)$data['sort_order'] . "', date_added = NOW()");

		$article_id = $this->db->getLastId();

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "newsblog_article SET image = '" . $this->db->escape($data['image']) . "' WHERE article_id = '" . (int)$article_id . "'");
		}

		foreach ($data['article_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "newsblog_article_description SET article_id = '" . (int)$article_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', preview = '" . $this->db->escape($value['intro_text']) . "', description = '" . $this->db->escape($value['description']) . "', tag = '" . $this->db->escape($value['tag']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']). "', article_author = '" . $this->db->escape($value['article_author']). "'");
		}

		if (isset($data['article_store'])) {
			foreach ($data['article_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "newsblog_article_to_store SET article_id = '" . (int)$article_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		if (isset($data['article_attribute'])) {
			foreach ($data['article_attribute'] as $article_attribute) {
				if ($article_attribute['attribute_id']) {
					foreach ($article_attribute['article_attribute_description'] as $language_id => $article_attribute_description) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "newsblog_article_attribute SET article_id = '" . (int)$article_id . "', attribute_id = '" . (int)$article_attribute['attribute_id'] . "', language_id = '" . (int)$language_id . "', text = '" .  $this->db->escape($article_attribute_description['text']) . "'");
					}
				}
			}
		}

		if (isset($data['article_image'])) {
			foreach ($data['article_image'] as $article_image) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "newsblog_article_image SET article_id = '" . (int)$article_id . "', image = '" . $this->db->escape($article_image['image']) . "', sort_order = '" . (int)$article_image['sort_order'] . "'");
			}
		}

		if (isset($data['article_category'])) {
			foreach ($data['article_category'] as $category_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "newsblog_article_to_category SET article_id = '" . (int)$article_id . "', category_id = '" . (int)$category_id . "'");
			}
		}

		if(isset($data['main_category_id']) && $data['main_category_id'] > 0) {
			$this->db->query("DELETE FROM " . DB_PREFIX . "newsblog_article_to_category WHERE article_id = '" . (int)$article_id . "' AND category_id = '" . (int)$data['main_category_id'] . "'");
			$this->db->query("INSERT INTO " . DB_PREFIX . "newsblog_article_to_category SET article_id = '" . (int)$article_id . "', category_id = '" . (int)$data['main_category_id'] . "', main_category = 1");
		} elseif(isset($data['article_category'][0])) {
			$this->db->query("UPDATE " . DB_PREFIX . "newsblog_article_to_category SET main_category = 1 WHERE article_id = '" . (int)$article_id . "' AND category_id = '" . (int)$data['article_category'][0] . "'");
		}

		// if (isset($data['article_related'])) {
		// 	foreach ($data['article_related'] as $related_id) {
		// 		$this->db->query("DELETE FROM " . DB_PREFIX . "newsblog_article_related WHERE article_id = '" . (int)$article_id . "' AND related_id = '" . (int)$related_id . "' AND type=1");
		// 		$this->db->query("INSERT INTO " . DB_PREFIX . "newsblog_article_related SET article_id = '" . (int)$article_id . "', related_id = '" . (int)$related_id . "', type=1");
		// 		$this->db->query("DELETE FROM " . DB_PREFIX . "newsblog_article_related WHERE article_id = '" . (int)$related_id . "' AND related_id = '" . (int)$article_id . "' AND type=1");
		// 		$this->db->query("INSERT INTO " . DB_PREFIX . "newsblog_article_related SET article_id = '" . (int)$related_id . "', related_id = '" . (int)$article_id . "', type=1");
		// 	}
		// }

		// if (isset($data['article_related_products'])) {
		// 	foreach ($data['article_related_products'] as $related_id) {
		// 		$this->db->query("DELETE FROM " . DB_PREFIX . "newsblog_article_related WHERE article_id = '" . (int)$article_id . "' AND related_id = '" . (int)$related_id . "' AND type=2");
		// 		$this->db->query("INSERT INTO " . DB_PREFIX . "newsblog_article_related SET article_id = '" . (int)$article_id . "', related_id = '" . (int)$related_id . "', type=2");
		// 		$this->db->query("DELETE FROM " . DB_PREFIX . "newsblog_article_related WHERE article_id = '" . (int)$related_id . "' AND related_id = '" . (int)$article_id . "' AND type=2");
		// 		$this->db->query("INSERT INTO " . DB_PREFIX . "newsblog_article_related SET article_id = '" . (int)$related_id . "', related_id = '" . (int)$article_id . "', type=2");
		// 	}
		// }

		// if (isset($data['article_layout'])) {
		// 	foreach ($data['article_layout'] as $store_id => $layout_id) {
		// 		$this->db->query("INSERT INTO " . DB_PREFIX . "newsblog_article_to_layout SET article_id = '" . (int)$article_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
		// 	}
		// }

		if (isset($data['keyword'])) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'newsblog_article_id=" . (int)$article_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
		}

		$this->cache->delete('article');

		return $article_id;

	}

	public function editArticle($article_id, $data) {

		$this->db->query("UPDATE " . DB_PREFIX . "newsblog_article SET status = '" . (int)$data['status'] . "', sort_order = '" . (int)$data['sort_order'] . "', date_modified = NOW() WHERE article_id = '" . (int)$article_id . "'");

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "newsblog_article SET image = '" . $this->db->escape($data['image']) . "' WHERE article_id = '" . (int)$article_id . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "newsblog_article_description WHERE article_id = '" . (int)$article_id . "'");

		foreach ($data['article_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "newsblog_article_description SET article_id = '" . (int)$article_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', preview = '" . $this->db->escape($value['intro_text']) . "', description = '" . $this->db->escape($value['description']) . "', tag = '" . $this->db->escape($value['tag']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']). "', article_author = '" . $this->db->escape($value['article_author']) . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "newsblog_article_to_store WHERE article_id = '" . (int)$article_id . "'");

		if (isset($data['article_store'])) {
			foreach ($data['article_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "newsblog_article_to_store SET article_id = '" . (int)$article_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		//$this->db->query("DELETE FROM " . DB_PREFIX . "newsblog_article_attribute WHERE article_id = '" . (int)$article_id . "'");

		// if (!empty($data['article_attribute'])) {
		// 	foreach ($data['article_attribute'] as $article_attribute) {
		// 		if ($article_attribute['attribute_id']) {
		// 			foreach ($article_attribute['article_attribute_description'] as $language_id => $article_attribute_description) {
		// 				$this->db->query("INSERT INTO " . DB_PREFIX . "newsblog_article_attribute SET article_id = '" . (int)$article_id . "', attribute_id = '" . (int)$article_attribute['attribute_id'] . "', language_id = '" . (int)$language_id . "', text = '" .  $this->db->escape($article_attribute_description['text']) . "'");
		// 			}
		// 		}
		// 	}
		// }

		$this->db->query("DELETE FROM " . DB_PREFIX . "newsblog_article_image WHERE article_id = '" . (int)$article_id . "'");

		if (isset($data['article_image'])) {
			foreach ($data['article_image'] as $article_image) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "newsblog_article_image SET article_id = '" . (int)$article_id . "', image = '" . $this->db->escape($article_image['image']) . "', sort_order = '" . (int)$article_image['sort_order'] . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "newsblog_article_to_category WHERE article_id = '" . (int)$article_id . "'");

		if (isset($data['article_category'])) {
			foreach ($data['article_category'] as $category_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "newsblog_article_to_category SET article_id = '" . (int)$article_id . "', category_id = '" . (int)$category_id . "'");
			}
		}

		if(isset($data['main_category_id']) && $data['main_category_id'] > 0) {
			$this->db->query("DELETE FROM " . DB_PREFIX . "newsblog_article_to_category WHERE article_id = '" . (int)$article_id . "' AND category_id = '" . (int)$data['main_category_id'] . "'");
			$this->db->query("INSERT INTO " . DB_PREFIX . "newsblog_article_to_category SET article_id = '" . (int)$article_id . "', category_id = '" . (int)$data['main_category_id'] . "', main_category = 1");
		} elseif(isset($data['article_category'][0])) {
			$this->db->query("UPDATE " . DB_PREFIX . "newsblog_article_to_category SET main_category = 1 WHERE article_id = '" . (int)$article_id . "' AND category_id = '" . (int)$data['article_category'][0] . "'");
		}

		// $this->db->query("DELETE FROM " . DB_PREFIX . "newsblog_article_related WHERE article_id = '" . (int)$article_id . "'");
		// $this->db->query("DELETE FROM " . DB_PREFIX . "newsblog_article_related WHERE related_id = '" . (int)$article_id . "'");

		// if (isset($data['article_related'])) {
		// 	foreach ($data['article_related'] as $related_id) {
		// 		$this->db->query("DELETE FROM " . DB_PREFIX . "newsblog_article_related WHERE article_id = '" . (int)$article_id . "' AND related_id = '" . (int)$related_id . "' AND type=1");
		// 		$this->db->query("INSERT INTO " . DB_PREFIX . "newsblog_article_related SET article_id = '" . (int)$article_id . "', related_id = '" . (int)$related_id . "', type=1");
		// 		$this->db->query("DELETE FROM " . DB_PREFIX . "newsblog_article_related WHERE article_id = '" . (int)$related_id . "' AND related_id = '" . (int)$article_id . "' AND type=1");
		// 		$this->db->query("INSERT INTO " . DB_PREFIX . "newsblog_article_related SET article_id = '" . (int)$related_id . "', related_id = '" . (int)$article_id . "', type=1");
		// 	}
		// }

		// if (isset($data['article_related_products'])) {
		// 	foreach ($data['article_related_products'] as $related_id) {
		// 		$this->db->query("DELETE FROM " . DB_PREFIX . "newsblog_article_related WHERE article_id = '" . (int)$article_id . "' AND related_id = '" . (int)$related_id . "' AND type=2");
		// 		$this->db->query("INSERT INTO " . DB_PREFIX . "newsblog_article_related SET article_id = '" . (int)$article_id . "', related_id = '" . (int)$related_id . "', type=2");
		// 		$this->db->query("DELETE FROM " . DB_PREFIX . "newsblog_article_related WHERE article_id = '" . (int)$related_id . "' AND related_id = '" . (int)$article_id . "' AND type=2");
		// 		$this->db->query("INSERT INTO " . DB_PREFIX . "newsblog_article_related SET article_id = '" . (int)$related_id . "', related_id = '" . (int)$article_id . "', type=2");
		// 	}
		// }

		// $this->db->query("DELETE FROM " . DB_PREFIX . "newsblog_article_to_layout WHERE article_id = '" . (int)$article_id . "'");

		// if (isset($data['article_layout'])) {
		// 	foreach ($data['article_layout'] as $store_id => $layout_id) {
		// 		$this->db->query("INSERT INTO " . DB_PREFIX . "newsblog_article_to_layout SET article_id = '" . (int)$article_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
		// 	}
		// }

		$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'newsblog_article_id=" . (int)$article_id . "'");

		if ($data['keyword']) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'newsblog_article_id=" . (int)$article_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
		}

		$this->cache->delete('article');

	}

	public function deletearticle($article_id) {

		$this->db->query("DELETE FROM " . DB_PREFIX . "newsblog_article WHERE article_id = '" . (int)$article_id . "'");
		//$this->db->query("DELETE FROM " . DB_PREFIX . "newsblog_article_attribute WHERE article_id = '" . (int)$article_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "newsblog_article_description WHERE article_id = '" . (int)$article_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "newsblog_article_image WHERE article_id = '" . (int)$article_id . "'");
		//$this->db->query("DELETE FROM " . DB_PREFIX . "newsblog_article_related WHERE article_id = '" . (int)$article_id . "'");
		//$this->db->query("DELETE FROM " . DB_PREFIX . "newsblog_article_related WHERE related_id = '" . (int)$article_id . "' and type=1");
		$this->db->query("DELETE FROM " . DB_PREFIX . "newsblog_article_to_category WHERE article_id = '" . (int)$article_id . "'");
		//$this->db->query("DELETE FROM " . DB_PREFIX . "newsblog_article_to_layout WHERE article_id = '" . (int)$article_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "newsblog_article_to_store WHERE article_id = '" . (int)$article_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'newsblog_article_id=" . (int)$article_id . "'");

		$this->cache->delete('article');

	}

	public function getArticle($article_id) {
		$query = $this->db->query("SELECT DISTINCT *,
		(SELECT keyword FROM " . DB_PREFIX . "url_alias WHERE query = 'newsblog_article_id=" . (int)$article_id . "') AS keyword

		FROM " . DB_PREFIX . "newsblog_article p
		LEFT JOIN " . DB_PREFIX . "newsblog_article_description pd ON (p.article_id = pd.article_id)

		WHERE p.article_id = '" . (int)$article_id . "' AND
		pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getArticles($data = array()) {

		$sql = "SELECT * FROM " . DB_PREFIX . "newsblog_article p LEFT JOIN " . DB_PREFIX . "newsblog_article_description pd ON (p.article_id = pd.article_id)";

		if (!empty($data['filter_category'])) {
        	$sql .= " LEFT JOIN " . DB_PREFIX . "newsblog_article_to_category p2c ON (p.article_id = p2c.article_id)";
		}

			$sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";


		if (!empty($data['filter_name'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
		}


    if (!empty($data['filter_category'])) {
			if (!empty($data['filter_sub_category'])) {
				$implode_data = array();

				$implode_data[] = "category_id = '" . (int)$data['filter_category'] . "'";

				$this->load->model('catalog/category');

				$categories = $this->model_catalog_category->getCategories($data['filter_category']);

				foreach ($categories as $category) {
					$implode_data[] = "p2c.category_id = '" . (int)$category['category_id'] . "'";
				}

				$sql .= " AND (" . implode(' OR ', $implode_data) . ")";
			} else {
				$sql .= " AND p2c.category_id = '" . (int)$data['filter_category'] . "'";
			}
		}

		$sql .= " GROUP BY p.article_id";

		$sort_data = array(
			'pd.name',
			'p.status',
			'p.date_available',
			'p.date_modified',
			'p.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY pd.name";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}
		//echo $sql;

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getArticleDescriptions($article_id) {
		$article_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "newsblog_article_description WHERE article_id = '" . (int)$article_id . "'");

		foreach ($query->rows as $result) {
			$article_description_data[$result['language_id']] = array(
				'name'             => $result['name'],
				'intro_text'       => $result['preview'],
				'description'      => $result['description'],
				'meta_title'       => $result['meta_title'],				
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword'],
				'article_author'   => $result['article_author'],
				'tag'              => $result['tag']
			);
		}

		return $article_description_data;
	}

	public function getArticleCategories($article_id) {
		$article_category_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "newsblog_article_to_category WHERE article_id = '" . (int)$article_id . "'");

		foreach ($query->rows as $result) {
			$article_category_data[] = $result['category_id'];
		}

		return $article_category_data;
	}

	public function getArticleAttributes($article_id) {
		$article_attribute_data = array();

		$article_attribute_query = $this->db->query("SELECT attribute_id FROM " . DB_PREFIX . "newsblog_article_attribute WHERE article_id = '" . (int)$article_id . "' GROUP BY attribute_id");

		foreach ($article_attribute_query->rows as $article_attribute) {
			$article_attribute_description_data = array();

			$article_attribute_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "newsblog_article_attribute WHERE article_id = '" . (int)$article_id . "' AND attribute_id = '" . (int)$article_attribute['attribute_id'] . "'");

			foreach ($article_attribute_description_query->rows as $article_attribute_description) {
				$article_attribute_description_data[$article_attribute_description['language_id']] = array('text' => $article_attribute_description['text']);
			}

			$article_attribute_data[] = array(
				'attribute_id'                  => $article_attribute['attribute_id'],
				'article_attribute_description' => $article_attribute_description_data
			);
		}

		return $article_attribute_data;
	}

	public function getArticleImages($article_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "newsblog_article_image WHERE article_id = '" . (int)$article_id . "' ORDER BY sort_order ASC");

		return $query->rows;
	}

	public function getArticleStores($article_id) {
		$article_store_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "newsblog_article_to_store WHERE article_id = '" . (int)$article_id . "'");

		foreach ($query->rows as $result) {
			$article_store_data[] = $result['store_id'];
		}

		return $article_store_data;
	}

	public function getArticleLayouts($article_id) {
		$article_layout_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "newsblog_article_to_layout WHERE article_id = '" . (int)$article_id . "'");

		foreach ($query->rows as $result) {
			$article_layout_data[$result['store_id']] = $result['layout_id'];
		}

		return $article_layout_data;
	}

	public function getArticleMainCategoryId($article_id) {
		$query = $this->db->query("SELECT category_id FROM " . DB_PREFIX . "newsblog_article_to_category WHERE article_id = '" . (int)$article_id . "' AND main_category = '1' LIMIT 1");

		return ($query->num_rows ? (int)$query->row['category_id'] : 0);
	}

	public function getArticleRelated($article_id) {
		$article_related_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "newsblog_article_related WHERE article_id = '" . (int)$article_id . "' and type = 1");

		foreach ($query->rows as $result) {
			$article_related_data[] = $result['related_id'];
		}

		return $article_related_data;
	}

	public function getArticleRelatedProducts($article_id) {
		$article_related_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "newsblog_article_related WHERE article_id = '" . (int)$article_id . "' and type = 2");

		foreach ($query->rows as $result) {
			$article_related_data[] = $result['related_id'];
		}

		return $article_related_data;
	}

	public function getTotalArticles($data = array()) {
	    $sql = "SELECT COUNT(DISTINCT p.article_id) AS total FROM " . DB_PREFIX . "newsblog_article p LEFT JOIN " . DB_PREFIX . "newsblog_article_description pd ON (p.article_id = pd.article_id) LEFT JOIN " . DB_PREFIX . "newsblog_article_to_category p2c ON (p.article_id = p2c.article_id)";

		$sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (isset($data['filter_category']) && !is_null($data['filter_category'])) {
			$sql .= " AND p2c.category_id = '" . (int)$data['filter_category'] . "'";
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

}
