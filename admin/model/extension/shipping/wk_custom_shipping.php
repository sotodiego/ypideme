<?php

class ModelExtensionShippingWKCustomShipping extends Model {

    public function cerateTable() {
        $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX ."customerpartner_eventshipping` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `partner_id` int(11) NOT NULL,
            `shipping` text,
            `status` boolean NOT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1") ;

        $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX ."customerpartner_priorityshipping` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `partner_id` int(11) NOT NULL,
            `high` text,
            `medium` text,
            `low` text,
            `status` boolean NOT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1") ;

    }

    public function deleteTable() {
        $this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "customerpartner_eventshipping");
        $this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "customerpartner_priorityshipping");

    }

    public function isInstalled() {
        $table1 = $this->db->query("SELECT TABLE_NAME FROM information_schema.tables WHERE table_schema = '" . DB_DATABASE . "' AND table_name = '" . DB_PREFIX . "customerpartner_eventshipping' ")->row;
    
        $table2 = $this->db->query("SELECT TABLE_NAME FROM information_schema.tables WHERE table_schema = '" . DB_DATABASE . "' AND table_name = '" . DB_PREFIX . "customerpartner_priorityshipping' ")->row;
    
        if (!$table1 || !$table2)
          return false ;
        else 
          return true ;
      }
}
