<?php

class Scholarship_model extends CI_Model
{    private $table = 'scholarships';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function getScholarships($search_term = null)
    {
        $this->db->select("*");
        $this->db->from($this->table);

        if ($search_term) {
            $this->db->like("provider_name", $search_term);
        }
        $query = $this->db->get()->result_array();

        return $query;
    }
 
}