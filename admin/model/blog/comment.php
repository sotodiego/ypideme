<?php
class ModelBlogComment extends Model {
	public function addComment($data) {
		//print_r($data['article']);
		$this->db->query("INSERT INTO " . DB_PREFIX . "newsblog_article_comment SET author = '" . $this->db->escape($data['author']) . "', article_id = '" . (int)$data['article'] . "', comment_text = '" . $this->db->escape(strip_tags($data['comment_text'])) . "', email = '" . $this->db->escape($data['email']) . "', status = '" . (int)$data['status'] . "', sort_order = '" . $this->db->escape($data['sort_order']) . "', date_added = NOW() ");

	

		$comment_id = $this->db->getLastId();

		return $comment_id;
	}

	public function editComment($comment_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "newsblog_article_comment SET author = '" . $this->db->escape($data['author']) . "', article_id = '" . (int)$data['article'] . "', comment_text = '" . $this->db->escape(strip_tags($data['comment_text'])) . "', email = '" . $this->db->escape($data['email']) . "', status = '" . (int)$data['status'] . "', sort_order = '" . $this->db->escape($data['sort_order']) . "', date_modified = NOW() WHERE comment_id = '" . (int)$comment_id . "'");		
	}

	public function deleteComment($comment_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "newsblog_article_comment WHERE comment_id = '" . (int)$comment_id . "'");
	}

	public function getComment($comment_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "newsblog_article_comment  WHERE comment_id = '" . (int)$comment_id . "'");

		return $query->row;
	}

	public function getComments($comment_id,$data = array()) {
		$sql = "SELECT r.comment_id,r.article_id, pd.name, r.author, r.email,r.comment_text, r.status, r.sort_order, r.date_added FROM " . DB_PREFIX . "newsblog_article_comment r LEFT JOIN " . DB_PREFIX . "newsblog_article_description pd ON (r.article_id = pd.article_id)";

		//$sql = "SELECT * FROM " . DB_PREFIX . "newsblog_article_comment";

		$sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		
		//print_r($data);

		$sort_data = array(
		  //'pd.name',
		  'article_id',
		  'author',
		  'email',
		  'comment_text',
		  'status',
		  'sort_order',		  
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
		  $sql .= " ORDER BY " . $data['sort'];
		} else {
		  $sql .= " ORDER BY author";
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

	public function getTotalComments() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "newsblog_article_comment ");

		if ($query->row) {
			return $query->row['total'];
		} else {
			return false;
		}

	}

	public function getTotalCommentsAwaitingApproval() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "newsblog_article_comment WHERE status = '0'");

		return $query->row['total'];
	}
}
