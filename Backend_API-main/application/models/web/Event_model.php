<?php
/**
 * Events Model
 *
 * @category   Models
 * @package    Web
 * @subpackage Events
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    24 JAN 2024
 * 
 * Class Event_model handles all event-related operations.
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Event_model extends CI_Model {

    private $table = 'events';
	private $imgtable= 'gallery';

    public function __construct() {
        parent::__construct();
    }
	
	public function getEventList($value) 
	{
	// $this->db->select('e.event_id, e.event_name, e.event_desc, e.event_website, e.event_address, e.event_start_date, e.event_end_date, g.image');
	// $this->db->from('events e');
	// $this->db->join('gallery g', 'g.postid = e.event_id', 'left');
	// $this->db->where('e.event_status', 1);
	// $this->db->where('g.image IS NOT NULL');
	// $this->db->group_by('e.event_id');
	// $this->db->order_by('e.event_id', 'DESC');
	$this->db->select('g.image, e.event_id, e.event_name, e.event_desc, e.event_website, e.event_address, e.event_start_date, e.event_end_date');
	$this->db->from('events e');
	$this->db->join('gallery g', 'g.postid = e.event_id', 'left');
	$this->db->where('e.event_status', '1');
	$this->db->where('g.type', 'events');
	if(!empty($value))
	{
		$this->db->like('e.event_name', $value);

	}
	$this->db->group_by('e.event_id');
	$this->db->order_by('e.event_id', 'DESC');
	
	$query = $this->db->get();
	return $query->result();
	//$this->db->limit(5);
	$query = $this->db->get();
	// echo $this->db->last_query();exit;
	$result = $query->result();
	return $result;
	}


	public function getEventDetails($eventid)
	{
		$this->db->select('e.event_id, e.event_name,e.event_desc,g.image, e.event_address, e.event_phone, e.event_email, e.event_website, e.event_maplocation, e.event_package, e.event_start_date, e.event_end_date, e.event_category, c.title, c.id AS college_id, SUBSTRING(`event_desc`, 1, 100) AS short_event_desc, e.event_url,ce.catname as event_catname');
		$this->db->from('events e');
		$this->db->join('college c', 'c.id = e.event_college_name', 'left');
		$this->db->join('gallery g', 'g.postid = e.event_id', 'left');
		$this->db->join('category ce','ce.id = e.event_category','left');
		$this->db->where('e.event_id', $eventid);
		$this->db->where('g.type', 'events');
		$this->db->where('g.postid', $eventid);
		

		$this->db->set('views', 'views+1', FALSE);

		$data = $this->db->get()->result();
	// echo $this->db->last_query();exit;
		return $data;

	}
	
	public function getEventCatname($event_categories){
	    $this->db->select('catname');
		$this->db->from('category c');
		$this->db->where_in('id', $event_categories);

		$data = $this->db->get()->result();
	// echo $this->db->last_query();exit;
		return $data;
	}

	function getUpcomingEvents($collegeid){
		$current_date=date('Y-m-d');
		$this->db->select('e.event_id,e.event_name,e.event_desc,e.event_address,e.event_phone,e.event_email,e.event_website,e.event_maplocation,e.event_package,e.event_start_date,e.event_end_date,e.event_category,e.event_url,SUBSTRING(`event_desc`, 1,100 ) as short_event_desc');
		$this->db->where('event_college_name', $collegeid);
		$this->db->where('event_status', '1');
		$this->db->where('event_start_date >=', $current_date);
		$this->db->or_where('event_end_date >=', $current_date);
		$this->db->order_by('event_start_date','DESC');
		$this->db->limit(5);  
		return $this->db->get('events e')->result_array();
	}
	
}
