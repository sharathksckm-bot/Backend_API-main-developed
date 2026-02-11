<?php
Class City_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function getCity($search_term = null)
    {
        $this->db->select('id, city, stateid, countryid');
        $this->db->from('city');

        if ($search_term) {
            $this->db->like('city', $search_term);
        }
        $this->db->limit(10);
        return $this->db->get()->result_array();
    }

	public function getCities()
	{
		$this->db->select('id, city, stateid, countryid');
        $this->db->from('city');
		$this->db->where('view_in_menu','1');
		return $this->db->get()->result_array();

	}
	public function getCityByState($search,$stateid)
    {
        $this->db->select('id,city');
        $this->db->from('city');
        if(!empty($search))
        {
            $this->db->where('city',$search);

        }
        //$this->db->limit(10);

		$this->db->where('stateid',$stateid);
		return $this->db->get()->result_array();
    }
}
?>
