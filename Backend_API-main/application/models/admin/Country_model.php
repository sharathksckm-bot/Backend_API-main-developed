<?php
/**
 * Country Model
 *
 * @category   Models
 * @package    Admin
 * @subpackage Country
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    22 JAN 2024
 * 
 * Class country_model handles all country-related operations.
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Country_model extends CI_Model {

    private $table = 'country';

    public function __construct() {
        parent::__construct();
    }

	/**
     * Get the list of all country.
     *
     * @return array The list of all country.
     */
	public function getCountryList()
	{
		$this->db->select("*");
		$this->db->from($this->table);
		return $this->db->get()->result();

	}
	/**
     * Insert details for country into the database.
     *
     * @param array $data The data to be inserted.
     * @return bool True if data insertion is successful, otherwise false.
     */
	public function insertCountryDetails($data)
	{
		$query = $this->db->insert($this->table, $data);
		return $this->db->insert_id();
	}

	/**
     * check if country exists or not.
     *     
	 * @param string $name The name to retrieve country.
     * @return array The list of all country.
     */
	public function chkIfExists($name) {
		$this->db->where('country', $name);
		$query = $this->db->get($this->table);
		return $query->num_rows();
	}

	/**
     * Check if an country exists while updatte.
     *
     * @param string $data,$id The country  to check.
     * @return int The count of country .
     */
	function chkwhileUpdate($id, $data) {
		$this->db->where('country', $data);
		$this->db->where("id !=", $id);
		$query = $this->db->get($this->table);
		return $query->num_rows();
	}

	/**
     * Update the details of a country by ID.
     *
     * @param string $id   The ID of the country to be updated.
     * @param array  $data An associative array containing the data to be updated.
     * @return bool        True if the update operation is successful, otherwise false.
     */
    public function updateCountryDetails($id, $data)
    {
        $this->db->where("id", $id);
        $query = $this->db->update($this->table, $data);
        return $query;
    }

	/**
     * Get the details of a country by ID.
     *
     * @param string $id The ID to retrieve country details.
     * @return object The details of the country as an object.
     */
    public function getCountryDetailsById($id)
    {
        $this->db->where("id", $id);
        return $this->db->get($this->table)->row();
    }

	 /**
     * Delete the details of a country by ID.
     *
     * @param string $id   The ID of the country to be deleted.
     * @return bool        True if the delete operation is successful, otherwise false.
     */
    public function deleteCountry($id)
    {
        $this->db->where("id", $id);
        $query = $this->db->delete($this->table);
        return $query;
    }

	/**
     * Count all country in the table.
     *
     * @return int The total number of country.
     */
    public function countAllCountry()
    {
        return $this->db->count_all($this->table);
    }

	/**
     * Count filtered country based on the search term.
     *
     * @param string $search The search term.
     * @return int The number of filtered country.
     */
    public function countFilteredCountry($search)
    {
        $this->db->like('country', $search);
        return $this->db->get($this->table)->num_rows();
    }

	/**
     * Get filtered country.
     *
     * @param string $search The search term.
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated country with additional information.
     */

	 public function getFilteredCountry($search, $start, $limit, $order, $dir)
	 {
		 $this->db->select("*");
		 $this->db->from($this->table);
 
		 $this->db->group_start(); 
		 $this->db->like('country', $search);
		 $this->db->group_end(); 
 
		 $this->db->order_by($order, $dir);
		 $this->db->limit($limit, $start);
 
		 return $this->db->get()->result();
	 }

	  /**
     * Get all country with filtering, ordering, and pagination.
     *
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated country.
     */

	 public function getAllCountry($start, $limit, $order, $dir)
	 {
		 $this->db->select("*");
		 $this->db->from($this->table);
 
		 $this->db->order_by($order, $dir);
		 $this->db->limit($limit, $start);
 
		 return $this->db->get()->result();
	 }

	 public function getCountry()
	 {
		 $this->db->select("*");
		 $this->db->from($this->table);
		 return $this->db->get()->result();
	 }

}
