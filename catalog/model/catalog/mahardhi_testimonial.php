<?php
class ModelCatalogMahardhiTestimonial extends Model {
    public function getTestimonial($testimonial_id) {
        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "testimonial t LEFT JOIN " . DB_PREFIX . "testimonial_description td ON (t.testimonial_id = td.testimonial_id) WHERE t.testimonial_id = '" . (int)$testimonial_id . "' AND td.language_id = '" . (int)$this->config->get('config_language_id') . "'  AND t.status = '1'");
        
        return $query->row;
    }

    public function getTestimonials($start = 0, $limit = 10) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX ."testimonial_description td LEFT JOIN " . DB_PREFIX . "testimonial t ON (t.testimonial_id = td.testimonial_id) WHERE t.status = '1' AND td.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY t.sort_order ASC LIMIT " . (int)$start . "," . (int)$limit);
        return $query->rows;
    }
    public function getRandomTestimonial(){
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX ."testimonial t LEFT JOIN " . DB_PREFIX . "testimonial_description td ON (t.testimonial_id = td.testimonial_id) WHERE  t.status = '1' AND td.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY RAND() LIMIT 1");
        return $query->row;
    }

    public function getTotalTestimonials() {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "testimonial AS t WHERE t.status = '1'");
        return $query->row['total'];
    }  

}
?>