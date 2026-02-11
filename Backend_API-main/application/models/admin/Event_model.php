<?php
/**
 * event Model
 *
 * @category   Models
 * @package    Admin
 * @subpackage event
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    26 JAN 2024
 * 
 * Class event_model handles all event-related operations.
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Event_model extends CI_Model {

    private $table = 'events';
	private $imgtable= 'gallery';

    public function __construct() {
        parent::__construct();
    }
	/**
     * Count all Event in the table.
     *
     * @return int The total number of Event.
     */
    public function countAllEvent($userId,$userType)
    {
        if($userType == 14)
		{
			$this->db->where('created_by', $userId);
            return $this->db->count_all_results($this->table);


		}
        else
        {
        return $this->db->count_all($this->table);
        }
    }

	/**
     * Count filtered Event based on the search term.
     *
     * @param string $search The search term.
     * @return int The number of filtered Event.
     */
    public function countFilteredEvent($search,$userId,$userType)
	{
        if($userType == 14)
		{
			$this->db->where('e.created_by', $userId);

		}
    $this->db->like('e.event_name', $search);
	$this->db->or_like('e.event_package', $search);
	// $this->db->join("gallery g", "g.postid = e.event_id", "left");
    return $this->db->get($this->table. " e")->num_rows(); 
	}

	/**
     * Get filtered Event.
     *
     * @param string $search The search term.
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated Event with additional information.
     */

	 public function getFilteredEvent($search, $start, $limit, $order, $dir,$userId,$userType)
	 { 
		 $this->db->select('e.*,g.image,CONCAT(u.f_name, " ", u.l_name) as created_by_name, CONCAT(u1.f_name, " ", u1.l_name) as updated_by_name');
         $this->db->from($this->table. " e");
		 $this->db->join("gallery g", "g.postid = e.event_id", "left");
         $this->db->join("users u", "u.id = e.created_by", "left");
         $this->db->join("users u1", "u1.id = e.updated_by", "left");
         if($userType == 14)
		{
			$this->db->where('e.created_by', $userId);

		}

		 $this->db->group_start(); 
		 $this->db->like('e.event_name', $search);
		 $this->db->or_like('e.event_package', $search);
		 $this->db->group_end(); 
     $this->db->group_by('e.event_id');

		 $this->db->order_by($order, $dir);
		 $this->db->limit($limit, $start);
 
		 return $this->db->get()->result();
	 }

	 /**
     * Get all Event with filtering, ordering, and pagination.
     *
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated Event.
     */

	 public function getAllEvent($start, $limit, $order, $dir,$userId,$userType)
	 {
        $this->db->select('e.*,g.image,CONCAT(u.f_name, " ", u.l_name) as created_by_name, CONCAT(u1.f_name, " ", u1.l_name) as updated_by_name');
		$this->db->from($this->table. " e");
        $this->db->join("gallery g", "g.postid = e.event_id", "left");
        $this->db->join("users u", "u.id = e.created_by", "left");
		$this->db->join("users u1", "u1.id = e.updated_by", "left");
        if($userType == 14)
		{
			$this->db->where('e.created_by', $userId);

		}
        $this->db->group_by('e.event_id');

		 $this->db->order_by($order, $dir);
		 $this->db->limit($limit, $start);
		 return $this->db->get()->result();
	 }

	 /**
     * Check if an Event exists .
     *
     * @param string $name to check.
     * @return int The count of Event .
     */
	public function chkIfExists($event_name)
    {
        $this->db->where("event_name", $event_name);
        $query = $this->db->get($this->table);
        return $query->num_rows();
    }

	/**
     * Insert details for Event into the database.
     *
     * @param array $data The data to be inserted.
     * @return bool True if data insertion is successful, otherwise false.
     */
	public function insertEventDetails($data)
	{
		$query = $this->db->insert($this->table, $data);
		return $this->db->insert_id();
	}

	/**
     * Check if an Event exists while updatte.
     *
     * @param string $data,$id The Event  to check.
     * @return int The count of Event .
     */
	function chkWhileUpdate($id,$name) {
		$this->db->where("event_name", $name);
		$this->db->where("event_id !=", $id);
		$query = $this->db->get($this->table);
		return $query->num_rows();
	}

	/**
     * Update the details of a Event by ID.
     *
     * @param string $id   The ID of the Event to be updated.
     * @param array  $data An associative array containing the data to be updated.
     * @return bool        True if the update operation is successful, otherwise false.
     */
    public function updateEventDetails($id, $data)
    {
        $this->db->where("event_id", $id);
        $query = $this->db->update($this->table, $data);
        return $query;
    }
	/**
     * Get the details of a Event by ID.
     *
     * @param string $id The ID to retrieve Event details.
     * @return object The details of the Event as an object.
     */
    public function getEventDetailsById($id)
	{
    $this->db->select('e.*,e.event_college_name as collegeid,c.title as collegename');
    $this->db->from($this->table . ' e');
    $this->db->join('college c', 'c.id = e.event_college_name', 'left');
    // $this->db->join('category cat', 'cat.id = e.categoryid', 'left');
    $this->db->where('e.event_id', $id);
	// $this->db->where('g.type', 'Events');
    $this->db->group_by('e.event_id');
    $this->db->order_by('e.event_id');
    return $this->db->get()->row();
	}

	public function getEventImgDetailsById($id)
	{
		$this->db->select('*');
		$this->db->from('gallery');
		$this->db->where('postid', $id);
		$this->db->where('type', 'events');
		return $this->db->get()->result();
	}

	/**
     * Delete the details of a Event by ID.
     *
     * @param string $id   The ID of the Event to be deleted.
     * @return bool        True if the delete operation is successful, otherwise false.
     */
    public function deleteEvent($id)
    {
        $this->db->where("event_id", $id);
        $query = $this->db->delete($this->table);
        return $query;
    }

	/**
     * Insert docs details for Event into the database.
     *
     * @param array $data The data to be inserted.
     * @return bool True if data insertion is successful, otherwise false.
     */
	public function insertEventDocsDetails($data)
	{
		$query = $this->db->insert($this->imgtable, $data);
		$imageId['imageId'] = $this->db->insert_id();
		 return  $imageId;
	}
	public function updateEventDocsDetails($id,$postid,$Arr)
	{
		$this->db->where("id", $id);
		$this->db->where("postid", $postid);
        $query = $this->db->update($this->imgtable, $Arr);
        return $query;
	}
	public function deleteDoc($Id)
	{
		$this->db->where('id', $Id);
		return $this->db->delete($this->imgtable);
	}

	public function updateCategory($Id,$Arr)
	{
		$this->db->where("event_id", $Id);
        $query = $this->db->update($this->table,$Arr);
        return $query;
	}

// 	public function getCatDetailsById($Id)
// {

// }
}
	