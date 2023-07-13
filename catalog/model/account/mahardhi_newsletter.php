<?php
class ModelAccountmahardhinewsletter extends Model {
	
	public function checkalreadyemail($data) {  	   
	   $query=$this->db->query("SELECT * FROM " . DB_PREFIX . "subscribe where email='".$data['newsletter_usr_email']."'");	   
	   return $query->num_rows;	   
	}	
	
	public function getList() {	
		$query=$this->db->query("SELECT * FROM " . DB_PREFIX . "subscribe where email='".$data['newsletter_usr_email']."'");	    
		return $query->num_rows;		
	}
	
	public function subscribe($data) {	  	   
		$this->db->query(" INSERT INTO " . DB_PREFIX . "subscribe SET email='".$data['newsletter_usr_email']."' ,date= CURDATE() ");
	}
	
	public function unsubscribe($data) {		
		$this->db->query("DELETE FROM " . DB_PREFIX . "subscribe WHERE email='".$data['newsletter_usr_email']."'");
	}     
   
}
?>
