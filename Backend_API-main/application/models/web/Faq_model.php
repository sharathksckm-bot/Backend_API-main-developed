<?php

class Faq_model extends CI_Model
{    private $table = 'faq';
        private $category = 'category';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function getFaqCategory()
    {
        $this->db->select("*");
        $this->db->from($this->category);
        $this->db->where("type", 'faq');
        $this->db->where("status", 1);

        $query = $this->db->get()->result_array();

        return $query;
    }

    public function getFaqs($search,$category)
    {
        $this->db->select("*");
        $this->db->from($this->table);
        if (!empty($search)) {
            $this->db->like("heading", $search);
        }
        $this->db->where("categoryid", $category);
        $this->db->where("status", '1');

        $query = $this->db->get()->result_array();

        return $query;
    }
}