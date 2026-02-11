<?php

defined('BASEPATH') or exit('No direct script access allowed');
class create_html_Model extends CI_Model
{
  public function __construct()
  {
    parent::__construct();
    $this->load->database();
  }
public function articles($id)
  {
    $this->db->select('title, description, image');
    $this->db->from('blog e');
    $this->db->where('id', $id);
    $result = $this->db->get()->result_array();
    return $result;
  }
	public function exam($id)
  {
    $this->db->select('title, description, image');
    $this->db->from('exams e');
    $this->db->join('gallery g', 'g.postid = e.id', 'left');
    $this->db->where('e.id', $id);
    $this->db->where('g.type', 'exams');
    $result = $this->db->get()->result_array();
    return $result;
  }
	public function event($id)
  {
    $this->db->select('e.event_name AS title,event_desc as description, g.image');
    $this->db->from('events e');
    $this->db->join('gallery g', 'g.postid = e.event_id', 'left');
    $this->db->where('e.event_id', $id);
    $this->db->where('g.type', 'events');
    $result = $this->db->get()->result_array();
    return $result;
  }
	
  public function college($id)
  {
    $this->db->select('title,description, g.image ');
    $this->db->from('college e');
    $this->db->join('gallery g', 'g.postid = e.id', 'left');
    $this->db->where('e.id', $id);
    $this->db->where('g.type', 'college');
    $result = $this->db->get()->result_array();
    return $result;
  }
}
