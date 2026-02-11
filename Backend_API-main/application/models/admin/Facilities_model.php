<?php
/**
 * facilities Model
 *
 * @category   Models
 * @package    Admin
 * @subpackage facilities
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    22 JAN 2024
 * 
 * Class facilities_model handles all facilities-related operations.
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Facilities_model extends CI_Model {

    private $table = 'facilities';

    public function __construct() {
        parent::__construct();
    }

	/**
     * Count all facilities in the table.
     *
     * @return int The total number of facilities.
     */
    public function countAllFacilities()
    {
        return $this->db->count_all($this->table);
    }

	/**
     * Count filtered facilities based on the search term.
     *
     * @param string $search The search term.
     * @return int The number of filtered facilities.
     */
    public function countFilteredFacilities($search)
    {
        $this->db->like('title', $search);
        $this->db->or_like('description', $search);
        return $this->db->get($this->table)->num_rows();
    }

	/**
     * Get filtered facilities.
     *
     * @param string $search The search term.
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated facilities with additional information.
     */

	 public function getFilteredFacilities($search, $start, $limit, $order, $dir)
	 {
		 $this->db->select("*");
		 $this->db->from($this->table);
 
		 $this->db->group_start(); 
		 $this->db->like('title', $search);
		 $this->db->or_like('description', $search);
		 $this->db->group_end(); 
 
		 $this->db->order_by($order, $dir);
		 $this->db->limit($limit, $start);
 
		 return $this->db->get()->result();
	 }

	 /**
     * Get all facilities with filtering, ordering, and pagination.
     *
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated facilities.
     */

	 public function getAllFacilities($start, $limit, $order, $dir)
	 {
		 $this->db->select("*");
		 $this->db->from($this->table);
 
		 $this->db->order_by($order, $dir);
		 $this->db->limit($limit, $start);
 
		 return $this->db->get()->result();
	 }

	 /**
     * Insert details for all facilities into the database.
     *	
     * @param array $data The data to be inserted.
     * @return bool True if data insertion is successful, otherwise false.
     */
    public function insertFacilities($data)
    {
        $query = $this->db->insert($this->table, $data);
		return $this->db->insert_id();
    }

	 /**
     * Check if an facilities exists .
     *
     * @param string $data The facilities  to check.
     * @return int The count of facilities .
     */
	public function chkIsExtists($data)
    {
        $this->db->where("title", $data);
        $query = $this->db->get($this->table);
        return $query->num_rows();
    }

 	/**
     * Check if an facilities exists while updatte.
     *
     * @param string $data,$id The facilities  to check.
     * @return int The count of facilities .
     */
	function chkWhileUpdate($id, $data) {
		$this->db->where('title', $data);
		$this->db->where("id !=", $id);
		$query = $this->db->get($this->table);
		return $query->num_rows();
	}

	/**
     * Update the details of a facilities by ID.
     *
     * @param string $id   The ID of the facilities to be updated.
     * @param array  $data An associative array containing the data to be updated.
     * @return bool        True if the update operation is successful, otherwise false.
     */
    public function updateFacilities($id, $data)
    {
        $this->db->where("id", $id);
        $query = $this->db->update($this->table, $data);
        return $query;
    }

	/**
     * Get the details of a facilities by ID.
     *
     * @param string $id The ID to retrieve facilities details.
     * @return object The details of the facilities as an object.
     */
    public function getFacilitiesDetailsById($id)
    {
        $this->db->where("id", $id);
        return $this->db->get($this->table)->row();
    }

	 /**
     * Delete the details of a facilities by ID.
     *
     * @param string $id   The ID of the facilities to be deleted.
     * @return bool        True if the delete operation is successful, otherwise false.
     */
    public function deleteFacilities($id)
    {
        $this->db->where("id", $id);
        $query = $this->db->delete($this->table);
        return $query;
    }

	public function getFacilities()
	{
		$this->db->select("*");
		$this->db->from($this->table);
		return $this->db->get()->result();
	}

}
