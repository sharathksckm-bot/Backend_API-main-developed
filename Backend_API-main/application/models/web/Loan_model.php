<?php

class Loan_model extends CI_Model
{    private $table = 'loans';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function getLoans($search_term = null)
    {
        $this->db->select("*");
        $this->db->from($this->table);

        if ($search_term) {
            $this->db->like("name", $search_term);
        }
        $query = $this->db->get()->result_array();

        return $query;
    }
}