<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Courseinquiry_model extends CI_Model
{

  public function __construct()
  {
    parent::__construct();
    $this->load->database();
  }


  public function dataDelete($id)
  {
    // echo $id;
    // exit;

    $this->db->where('id', $id);
    $this->db->delete('course_inquiry');

    return $this->db->affected_rows() > 0;
  }



  public function countAllCourseinquiry()
  {
    return $this->db->count_all('course_inquiry');

    
  }

  public function getAllCourseinquiry($start, $limit, $orderColumn, $orderDir)
{
        $this->db->select('ci.id, ci.name, ci.email, ci.phone,ct.city as cityname,s.statename, ci.category,ac.name as categoryName,c.name as course, ci.coursename, ci.interested, ci.is_read, ci.create_date, ci.attended_date,ci.is_attended, ci.attended_by, CONCAT(u.f_name, " ", u.l_name) as attended_by_name');

    $this->db->from('course_inquiry ci');
    $this->db->join('users as u', 'u.id = ci.attended_by', 'left');
    $this->db->join('academic_categories as ac', 'ac.category_id = ci.category', 'left');
    $this->db->join('courses as c', 'c.id = ci.coursename', 'left');
    $this->db->join('city as ct', 'ct.id = ci.city', 'left');
		$this->db->join('state as s', 's.id = ci.state', 'left');
    $this->db->limit($limit, $start);
    $this->db->order_by($orderColumn, $orderDir);

    $query = $this->db->get();
    return $query->result();
}


  public function countFilteredCourseinquiry($search)
  {
    // $this->db->like('id', $search);
    $this->db->like('name', $search);
    $this->db->or_like('email', $search);
    $this->db->or_like('phone', $search);
    $this->db->or_like('category', $search);
    $this->db->or_like('coursename', $search);
    $this->db->or_like('interested', $search);
    $this->db->or_like('is_read', $search);
    $this->db->or_like('create_date', $search);

    $query = $this->db->get('course_inquiry');
    return $query->num_rows();
  }


  public function getFilteredCourseinquiry($search, $start, $limit, $orderColumn, $orderDir)
  {
       $this->db->select('ci.id, ci.name, ci.email, ci.phone,ct.city as cityname,s.statename, ci.category,ac.name as categoryName,c.name as course, ci.coursename, ci.interested, ci.is_read, ci.create_date, ci.attended_date,ci.is_attended, ci.attended_by, CONCAT(u.f_name, " ", u.l_name) as attended_by_name');

    $this->db->join('users as u', 'u.id = ci.attended_by', 'left');
      $this->db->join('academic_categories as ac', 'ac.category_id = ci.category', 'left');
    $this->db->join('courses as c', 'c.id = ci.coursename', 'left');
    $this->db->join('city as ct', 'ct.id = ci.city', 'left');
		$this->db->join('state as s', 's.id = ci.state', 'left');
    // $this->db->like('id', $search);
    $this->db->like('ci.name', $search);
    $this->db->or_like('ci.email', $search);
    $this->db->or_like('ci.phone', $search);
    $this->db->or_like('ci.category', $search);
    $this->db->or_like('ci.coursename', $search);
    $this->db->or_like('ci.interested', $search);

    $this->db->limit($limit, $start);
    $this->db->order_by($orderColumn, $orderDir);

    $query = $this->db->get('course_inquiry ci');
    return $query->result();
  }
  public function updateData($enquiryId,$Arr)
  {
    $this->db->where("id", $enquiryId);
    $query = $this->db->update('course_inquiry', $Arr);
    return $query;
  }
}
