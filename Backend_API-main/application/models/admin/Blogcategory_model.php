<?php
/**
 * blogcategory Model
 *
 * @category   Models
 * @package    Admin
 * @subpackage blogcategory
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    26 JAN 2024
 * 
 * Class blogcategory_model handles all blogcategory-related operations.
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Blogcategory_model extends CI_Model {

    private $table = 'blog_category';

    public function __construct() {
        parent::__construct();
    }
	/**
     * Count all blogcategory in the table.
     *
     * @return int The total number of blogcategory.
     */
    public function countAllBlogcategory()
    {
        return $this->db->count_all($this->table);
    }

	/**
     * Count filtered blogcategory based on the search term.
     *
     * @param string $search The search term.
     * @return int The number of filtered blogcategory.
     */
    public function countFilteredBlogcategory($search)
	{
    $this->db->like('name', $search);
    return $this->db->get($this->table)->num_rows(); 
	}

	/**
     * Get filtered blogcategory.
     *
     * @param string $search The search term.
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated blogcategory with additional information.
     */

	 public function getFilteredBlogcategory($search, $start, $limit, $order, $dir)
	 { 
		$this->db->select("*");
        $this->db->from($this->table);
		 $this->db->group_start(); 
		 $this->db->like('name', $search);
		 $this->db->group_end(); 
 
		 $this->db->order_by($order, $dir);
		 $this->db->limit($limit, $start);
 
		 return $this->db->get()->result();
	 }

	 /**
     * Get all blogcategory with filtering, ordering, and pagination.
     *
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated blogcategory.
     */

	 public function getAllBlogcategory($start, $limit, $order, $dir)
	 {
		$this->db->select("*");
        $this->db->from($this->table);
		 $this->db->order_by($order, $dir);
		 $this->db->limit($limit, $start);
 
		 return $this->db->get()->result();
	 }

	 /**
     * Check if an blogcategory exists .
     *
     * @param string $name to check.
     * @return int The count of blogcategory .
     */
	public function chkIfExists($name)
    {
        $this->db->where("name", $name);
        $query = $this->db->get($this->table);
        return $query->num_rows();
    }

	/**
     * Insert details for blogcategory into the database.
     *
     * @param array $data The data to be inserted.
     * @return bool True if data insertion is successful, otherwise false.
     */
	public function insertBlogcategoryDetails($data)
	{
		$query = $this->db->insert($this->table, $data);
		return $this->db->insert_id();
	}

	/**
     * Check if an blogcategory exists while updatte.
     *
     * @param string $data,$id The blogcategory  to check.
     * @return int The count of blogcategory .
     */
	function chkWhileUpdate($id,$name) {
		$this->db->where("name", $name);
		$this->db->where("id !=", $id);
		$query = $this->db->get($this->table);
		return $query->num_rows();
	}

	/**
     * Update the details of a blogcategory by ID.
     *
     * @param string $id   The ID of the blogcategory to be updated.
     * @param array  $data An associative array containing the data to be updated.
     * @return bool        True if the update operation is successful, otherwise false.
     */
    public function updateBlogcategoryDetails($id, $data)
    {
        $this->db->where("id", $id);
        $query = $this->db->update($this->table, $data);
        return $query;
    }
	/**
     * Get the details of a blogcategory by ID.
     *
     * @param string $id The ID to retrieve blogcategory details.
     * @return object The details of the blogcategory as an object.
     */
    public function getblogcategoryDetailsById($id)
    {
        $this->db->where("id", $id);
        return $this->db->get($this->table)->row();
    }

	/**
     * Delete the details of a blogcategory by ID.
     *
     * @param string $id   The ID of the blogcategory to be deleted.
     * @return bool        True if the delete operation is successful, otherwise false.
     */
    public function deleteBlogcategory($id)
    {
        $this->db->where("id", $id);
        $query = $this->db->delete($this->table);
        return $query;
    }

	public function getBlogCategory()
	{
	   $this->db->select("*");
	   $this->db->from($this->table);
	    return $this->db->get()->result();
	}

}
	