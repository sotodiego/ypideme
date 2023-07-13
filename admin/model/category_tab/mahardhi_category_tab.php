<?php
class ModelCategoryTabMahardhiCategoryTab extends Model {

	public function install() {		
		$hasModelChoiceField = FALSE;
		$result = $this->db->query( "DESCRIBE `".DB_PREFIX."category`;" );
		foreach ($result->rows as $row) {
		 if ($row['Field'] == 'catimg') {
		    $hasModelChoiceField = TRUE;
		    break;
		 }
		}
		if (!$hasModelChoiceField) {
			$sql = "ALTER TABLE `".DB_PREFIX."category` ADD `catimg` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `image`;";
			$this->db->query( $sql );
		}
	}

	public function uninstall() {		
		$sql = "ALTER TABLE `".DB_PREFIX."category` DROP `catimg`";
	    $this->db->query( $sql );
	}
   	
}
?>

