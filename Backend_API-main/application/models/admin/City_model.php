<?php
/**
 * city Model
 *
 * @category   Models
 * @package    Admin
 * @subpackage city
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    24 JAN 2024
 * 
 * Class city_model handles all city-related operations.
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class City_model extends CI_Model {

    private $table = 'city';

    public function __construct() {
        parent::__construct();
    }
	/**
     * Count all City in the table.
     *
     * @return int The total number of City.
     */
    public function countAllCity()
    {
        return $this->db->count_all($this->table);
    }

	/**
     * Count filtered City based on the search term.
     *
     * @param string $search The search term.
     * @return int The number of filtered City.
     */
    public function countFilteredCity($search)
	{
    $this->db->like('c.city', $search);
    $this->db->or_like('cn.country', $search);
	$this->db->or_like('s.statename', $search);  
    $this->db->join("country cn", "cn.id = c.countryid", "left");
	$this->db->join("state s", "s.id = c.stateid", "left");

    return $this->db->get($this->table . " c")->num_rows(); 
	}


	/**
     * Get filtered City.
     *
     * @param string $search The search term.
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated City with additional information.
     */

	 public function getFilteredCity($search, $start, $limit, $order, $dir)
	 { 
		$this->db->select("c.*,cn.country,s.statename");
        $this->db->from($this->table . " c");
		$this->db->join("country cn", "cn.id = c.countryid", "left");
		$this->db->join("state s", "s.id = c.stateid", "left");
		 $this->db->group_start(); 
		 $this->db->like('c.city', $search);
		 $this->db->or_like('cn.country', $search);
		 $this->db->or_like('s.statename', $search);

		 $this->db->group_end(); 
 
		 $this->db->order_by($order, $dir);
		 $this->db->limit($limit, $start);
 
		 return $this->db->get()->result();
	 }

	  /**
     * Get all City with filtering, ordering, and pagination.
     *
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated City.
     */

	 public function getAllCity($start, $limit, $order, $dir)
	 {
		$this->db->select("c.*,cn.country,s.statename");
        $this->db->from($this->table . " c");
		$this->db->join("country cn", "cn.id = c.countryid", "left");
		$this->db->join("state s", "s.id = c.stateid", "left");
		 $this->db->order_by($order, $dir);
		 $this->db->limit($limit, $start);
 
		 return $this->db->get()->result();
	 }

	 /**
     * Get the details of a City by Country ID.
     *
     * @param string $id The ID to retrieve City details.
     * @return object The details of the City as an object.
     */
    public function getCityDetailsByCntId($id)
    {
        $this->db->where("stateid", $id);
        return $this->db->get($this->table)->result();
    }

	/**
     * Check if an city exists .
     *
     * @param string $stateid,$post_url and $countryId to check.
     * @return int The count of country .
     */
	public function chkIfExists($countryId,$stateid,$post_url)
    {
        $this->db->where("stateid", $stateid);
		$this->db->where("countryid", $countryId);
		$this->db->where("post_url", $post_url);
        $query = $this->db->get($this->table);
        return $query->num_rows();
    }

	/**
     * Insert details for City into the database.
     *
     * @param array $data The data to be inserted.
     * @return bool True if data insertion is successful, otherwise false.
     */
	public function insertCityDetails($data)
	{
		$query = $this->db->insert($this->table, $data);
		return $this->db->insert_id();
	}

	/**
     * Check if an City exists while updatte.
     *
     * @param string $data,$id The City  to check.
     * @return int The count of City .
     */
	function chkWhileUpdate($countryId,$stateid,$id,$post_url) {
		$this->db->where("stateid", $stateid);
		$this->db->where("countryid", $countryId);
		$this->db->where("post_url", $post_url);
		$this->db->where("id !=", $id);
		$query = $this->db->get($this->table);
		return $query->num_rows();
	}

	/**
     * Update the details of a City by ID.
     *
     * @param string $id   The ID of the City to be updated.
     * @param array  $data An associative array containing the data to be updated.
     * @return bool        True if the update operation is successful, otherwise false.
     */
    public function updateCityDetails($id, $data)
    {
        $this->db->where("id", $id);
        $query = $this->db->update($this->table, $data);
        return $query;
    }

	/**
     * Get the details of a City by ID.
     *
     * @param string $id The ID to retrieve City details.
     * @return object The details of the City as an object.
     */
    public function getCityDetailsById($id)
    {
        $this->db->where("id", $id);
        return $this->db->get($this->table)->row();
    }

	 /**
     * Delete the details of a City by ID.
     *
     * @param string $id   The ID of the City to be deleted.
     * @return bool        True if the delete operation is successful, otherwise false.
     */
    public function deleteCity($id)
    {
        $this->db->where("id", $id);
        $query = $this->db->delete($this->table);
        return $query;
    }


}
