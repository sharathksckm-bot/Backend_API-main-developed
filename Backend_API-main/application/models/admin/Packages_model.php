<?php
/**
 * packages Model
 *
 * @category   Models
 * @package    Admin
 * @subpackage packages
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    25 JAN 2024
 * 
 * Class packages_model handles all packages-related operations.
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Packages_model extends CI_Model {

    private $table = 'packages';

    public function __construct() {
        parent::__construct();
    }
	/**
     * Count all packages in the table.
     *
     * @return int The total number of packages.
     */
    public function countAllPackages()
    {
        return $this->db->count_all($this->table);
    }
	
	/**
     * Count filtered Packages based on the search term.
     *
     * @param string $search The search term.
     * @return int The number of filtered Packages.
     */
    public function countFilteredPackages($search)
	{
    $this->db->like('name', $search);
    $this->db->or_like('price', $search);
    return $this->db->get($this->table)->num_rows(); 
	}

	/**
     * Get filtered Packages.
     *
     * @param string $search The search term.
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated Packages with additional information.
     */

	 public function getFilteredPackages($search, $start, $limit, $order, $dir)
	 { 
		$this->db->select("*");
        $this->db->from($this->table);
		 $this->db->group_start(); 
		 $this->db->like('name', $search);
		 $this->db->or_like('price', $search);
		 $this->db->group_end(); 
 
		 $this->db->order_by($order, $dir);
		 $this->db->limit($limit, $start);
 
		 return $this->db->get()->result();
	 }

	 /**
     * Get all Packages with filtering, ordering, and pagination.
     *
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated Packages.
     */

	 public function getAllPackages($start, $limit, $order, $dir)
	 {
		$this->db->select("*");
        $this->db->from($this->table);
		 $this->db->order_by($order, $dir);
		 $this->db->limit($limit, $start);
 
		 return $this->db->get()->result();
	 }

	 /**
     * Check if an packages exists .
     *
     * @param string $name to check.
     * @return int The count of packages .
     */
	public function chkIfExists($name)
    {
        $this->db->where("name", $name);
        $query = $this->db->get($this->table);
        return $query->num_rows();
    }

	/**
     * Insert details for Packages into the database.
     *
     * @param array $data The data to be inserted.
     * @return bool True if data insertion is successful, otherwise false.
     */
	public function insertPackagesDetails($data)
	{
		$query = $this->db->insert($this->table, $data);
		return $this->db->insert_id();
	}

	/**
     * Check if an Packages exists while updatte.
     *
     * @param string $data,$id The Packages  to check.
     * @return int The count of Packages .
     */
	function chkWhileUpdate($id,$name) {
		$this->db->where("name", $name);
		$this->db->where("id !=", $id);
		$query = $this->db->get($this->table);
		return $query->num_rows();
	}

	/**
     * Update the details of a Packages by ID.
     *
     * @param string $id   The ID of the Packages to be updated.
     * @param array  $data An associative array containing the data to be updated.
     * @return bool        True if the update operation is successful, otherwise false.
     */
    public function updatePackagesDetails($id, $data)
    {
        $this->db->where("id", $id);
        $query = $this->db->update($this->table, $data);
        return $query;
    }

	/**
     * Get the details of a Packages by ID.
     *
     * @param string $id The ID to retrieve Packages details.
     * @return object The details of the Packages as an object.
     */
    public function getPackagesDetailsById($id)
    {
        $this->db->where("id", $id);
        return $this->db->get($this->table)->row();
    }

	/**
     * Delete the details of a packages by ID.
     *
     * @param string $id   The ID of the packages to be deleted.
     * @return bool        True if the delete operation is successful, otherwise false.
     */
    public function deletePackages($id)
    {
        $this->db->where("id", $id);
        $query = $this->db->delete($this->table);
        return $query;
    }
	
}
