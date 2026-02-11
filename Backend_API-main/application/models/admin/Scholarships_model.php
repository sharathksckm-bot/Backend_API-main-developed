<?php
/**
 * scholarships Model
 *
 * @category   Models
 * @package    Admin
 * @subpackage scholarships
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    25 JAN 2024
 * 
 * Class scholarships_model handles all scholarships-related operations.
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Scholarships_model extends CI_Model {

    private $table = 'scholarships';

    public function __construct() {
        parent::__construct();
    }
	/**
     * Count all scholarships in the table.
     *
     * @return int The total number of scholarships.
     */
    public function countAllScholarships()
    {
        return $this->db->count_all($this->table);
    }

	/**
     * Count filtered Scholarships based on the search term.
     *
     * @param string $search The search term.
     * @return int The number of filtered Scholarships.
     */
    public function countFilteredScholarships($search)
	{
    $this->db->like('name', $search);
    $this->db->or_like('provider_name', $search);
	$this->db->or_like('type', $search);

    return $this->db->get($this->table)->num_rows(); 
	}

	/**
     * Get filtered Scholarships.
     *
     * @param string $search The search term.
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated Scholarships with additional information.
     */

	 public function getFilteredScholarships($search, $start, $limit, $order, $dir)
	 { 
		$this->db->select("*");
        $this->db->from($this->table);
		 $this->db->group_start(); 
		 $this->db->like('name', $search);
		 $this->db->or_like('provider_name', $search);
		 $this->db->or_like('type', $search);
		 $this->db->group_end(); 
 
		 $this->db->order_by($order, $dir);
		 $this->db->limit($limit, $start);
 
		 return $this->db->get()->result();
	 }

	 /**
     * Get all Scholarships with filtering, ordering, and pagination.
     *
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated Scholarships.
     */

	 public function getAllScholarships($start, $limit, $order, $dir)
	 {
		$this->db->select("*");
        $this->db->from($this->table);
		 $this->db->order_by($order, $dir);
		 $this->db->limit($limit, $start);
 
		 return $this->db->get()->result();
	 }

	 /**
     * Check if an Scholarships exists .
     *
     * @param string $name to check.
     * @return int The count of Scholarships .
     */
	public function chkIfExists($provider_name)
    {
        $this->db->where("provider_name", $provider_name);
        $query = $this->db->get($this->table);
        return $query->num_rows();
    }

	/**
     * Insert details for Scholarships into the database.
     *
     * @param array $data The data to be inserted.
     * @return bool True if data insertion is successful, otherwise false.
     */
	public function insertScholarshipsDetails($data)
	{
		$query = $this->db->insert($this->table, $data);
		return $this->db->insert_id();
	}

	/**
     * Check if an Scholarships exists while updatte.
     *
     * @param string $data,$id The Scholarships  to check.
     * @return int The count of Scholarships .
     */
	function chkWhileUpdate($id,$name) {
		$this->db->where("provider_name", $name);
		$this->db->where("id !=", $id);
		$query = $this->db->get($this->table);
		return $query->num_rows();
	}

	/**
     * Update the details of a Scholarships by ID.
     *
     * @param string $id   The ID of the Scholarships to be updated.
     * @param array  $data An associative array containing the data to be updated.
     * @return bool        True if the update operation is successful, otherwise false.
     */
    public function updateScholarshipsDetails($id, $data)
    {
        $this->db->where("id", $id);
        $query = $this->db->update($this->table, $data);
        return $query;
    }
	/**
     * Get the details of a Scholarships by ID.
     *
     * @param string $id The ID to retrieve Scholarships details.
     * @return object The details of the Scholarships as an object.
     */
    public function getScholarshipsDetailsById($id)
    {
        $this->db->where("id", $id);
        return $this->db->get($this->table)->row();
    }

	/**
     * Delete the details of a Scholarships by ID.
     *
     * @param string $id   The ID of the Scholarships to be deleted.
     * @return bool        True if the delete operation is successful, otherwise false.
     */
    public function deleteScholarships($id)
    {
        $this->db->where("id", $id);
        $query = $this->db->delete($this->table);
        return $query;
    }

	public function getScholarships()
	{
		$this->db->select("*");
        $this->db->from($this->table); 
		 return $this->db->get()->result();
	}
}
	