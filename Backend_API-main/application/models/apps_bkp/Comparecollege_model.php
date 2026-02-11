<?php

class Comparecollege_model extends CI_Model
{

    public function __construct()
    {

        parent::__construct(); {
            $this->load->database();
        }
    }
    public function getAllClg($searchTerm, $start, $limit)
    {
        $this->db->select('id, title, logo');
        $this->db->from('college c');
        $this->db->like('c.title ', $searchTerm);
        $this->db->where('c.is_deleted', '0');
        $this->db->where('c.status', '1');
        $this->db->order_by('id', 'asc');
        $this->db->limit($limit, $start);
        $query = $this->db->get()->result_array();
        //echo $this->db->last_query();exit;
        return $query;
    }
	
	public function getDegreeByCollegeId($ClgId)
    {
        $this->db->select('sc.id, sc.name');
        $this->db->from('college_course cc');
		$this->db->join('courses co', 'co.id = cc.courseid', 'left');
		$this->db->join('sub_category sc', 'sc.id = co.sub_category', 'left');
        $this->db->where('cc.collegeid ', $ClgId);
		$this->db->where('sc.name !=', '');
		$this->db->group_by('sc.name');
		$this->db->order_by('sc.name');
        $query = $this->db->get()->result_array();
        //echo $this->db->last_query();exit;
        return $query;
    }
	
	public function getCoursesByCollegeId($ClgId, $degId)
    {
        $this->db->select('co.id, co.name');
        $this->db->from('college_course cc');
		$this->db->join('courses co', 'co.id = cc.courseid', 'left');
        $this->db->where('cc.collegeid', $ClgId);
		$this->db->where('co.sub_category', $degId);
        $query = $this->db->get()->result_array();
        //echo $this->db->last_query();exit;
        return $query;
    }
	
	public function countCollegeReviews($ci)
    {
        $this->db->select('*');
        $this->db->where("college_id", $ci);
        $query =  $this->db->get('review')->num_rows();
        echo $this->db->last_query();exit;
        return $query;
    }
	
	public function getPopularCompOfBTech()
    {
        $this->db->select('c.id, c.title, c.address, c.web, c.estd, g.image, c.logo');
        $this->db->from('college c');
		$this->db->join('college_course cc', 'cc.id = c.id', 'left');
        $this->db->join('gallery g', 'g.postid = c.id', 'left');
        $this->db->where('c.package_type', 'featured_listing');
		$this->db->like('c.categoryid ', '91');
        $this->db->where('c.status', '1');
        $this->db->where('c.is_deleted', '0');
        $this->db->where('g.type', 'college');
        $this->db->group_by('c.id');
        $this->db->limit(4);
        $query = $this->db->get()->result_array();
        //echo $this->db->last_query();exit;
        return $query;
    }
	public function getPopularCompOfMBA()
    {
        $this->db->select('c.id, c.title, c.address, c.web, c.estd, g.image, c.logo');
        $this->db->from('college c');
		$this->db->join('college_course cc', 'cc.id = c.id', 'left');
        $this->db->join('gallery g', 'g.postid = c.id', 'left');
        $this->db->where('c.package_type', 'featured_listing');
		$this->db->like('c.categoryid ', '162');
        $this->db->where('c.status', '1');
        $this->db->where('c.is_deleted', '0');
        $this->db->where('g.type', 'college');
        $this->db->group_by('c.id');
        $this->db->limit(4);
        $query = $this->db->get()->result_array();
        //echo $this->db->last_query();exit;
        return $query;
    }
}
