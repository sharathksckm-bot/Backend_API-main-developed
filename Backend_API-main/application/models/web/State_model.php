<?php
Class State_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function getState($search_term)
    {
        $this->db->select('*');
        $this->db->from('state');
        if(!empty($search_term))
        {
            $this->db->where('statename',$search_term);
        }
        $this->db->limit(10);

        $query = $this->db->get();
        return $query->result();
        
    }
}