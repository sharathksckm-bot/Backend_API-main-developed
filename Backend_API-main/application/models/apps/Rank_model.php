<?php

Class Rank_model extends CI_Model{
     
    public function __construct()
	{

		parent::__construct();
        {
            $this->load->database();
        }
    }
	
	public function getRankList() {
		 
	    $this->db->select('*');
		$this->db->from('rank_categories');
		$query = $this->db->get()->result_array();
		return $query;
	}
}
