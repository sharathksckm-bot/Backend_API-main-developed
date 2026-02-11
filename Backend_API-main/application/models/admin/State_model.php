<?php
/**
 * State Model
 *
 * @category   Models
 * @package    Admin
 * @subpackage State
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    24 JAN 2024
 * 
 * Class state_model handles all State-related operations.
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class State_model extends CI_Model {

    private $table = 'state';

    public function __construct() {
        parent::__construct();
    }

	/**
     * Get the list of all State.
     *
     * @return array The list of all State.
     */
	public function getStateList()
	{
		$this->db->select("*");
		$this->db->from($this->table);
		return $this->db->get()->result();

	}
	/**
     * Insert details for State into the database.
     *
     * @param array $data The data to be inserted.
     * @return bool True if data insertion is successful, otherwise false.
     */
	public function insertStateDetails($data)
	{
		$query = $this->db->insert($this->table, $data);
		return $this->db->insert_id();
	}

	/**
     * check if State exists or not.
     *     
	 * @param string $name The name to retrieve State.
     * @return array The list of all State.
     */
	public function chkIfExists($name) {
		$this->db->where('statename', $name);
		$query = $this->db->get($this->table);
		return $query->num_rows();
	}

	/**
     * Update the details of a State by ID.
     *
     * @param string $id   The ID of the State to be updated.
     * @param array  $data An associative array containing the data to be updated.
     * @return bool        True if the update operation is successful, otherwise false.
     */
    public function updateStateDetails($id, $data)
    {
        $this->db->where("id", $id);
        $query = $this->db->update($this->table, $data);
        return $query;
    }

	/**
     * Get the details of a State by ID.
     *
     * @param string $id The ID to retrieve State details.
     * @return object The details of the State as an object.
     */
    public function getStateDetailsById($id)
    {
        $this->db->where("id", $id);
        return $this->db->get($this->table)->row();
    }

	 /**
     * Delete the details of a State by ID.
     *
     * @param string $id   The ID of the State to be deleted.
     * @return bool        True if the delete operation is successful, otherwise false.
     */
    public function deleteState($id)
    {
        $this->db->where("id", $id);
        $query = $this->db->delete($this->table);
        return $query;
    }

	/**
     * Count all State in the table.
     *
     * @return int The total number of State.
     */
    public function countAllState()
    {
        return $this->db->count_all($this->table);
    }

	/**
     * Count filtered State based on the search term.
     *
     * @param string $search The search term.
     * @return int The number of filtered State.
     */
    public function countFilteredState($search)
{
    $this->db->like('s.statename', $search);
    $this->db->or_like('c.country', $search); 
    $this->db->join("country c", "c.id = s.countryid", "left");
	
    return $this->db->get($this->table . " s")->num_rows(); 
}


	/**
     * Get filtered State.
     *
     * @param string $search The search term.
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated State with additional information.
     */

	 public function getFilteredState($search, $start, $limit, $order, $dir)
	 { 
		$this->db->select("s.*,c.country");
        $this->db->from($this->table . " s");
		$this->db->join("country c", "c.id = s.countryid", "left");
		 $this->db->group_start(); 
		 $this->db->like('s.statename', $search);
		 $this->db->or_like('c.country', $search);

		 $this->db->group_end(); 
 
		 $this->db->order_by($order, $dir);
		 $this->db->limit($limit, $start);
 
		 return $this->db->get()->result();
	 }

	  /**
     * Get all State with filtering, ordering, and pagination.
     *
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated State.
     */

	 public function getAllState($start, $limit, $order, $dir)
	 {
		$this->db->select("s.*,c.country");
        $this->db->from($this->table . " s");
		$this->db->join("country c", "c.id = s.countryid", "left");
		 $this->db->order_by($order, $dir);
		 $this->db->limit($limit, $start);
 
		 return $this->db->get()->result();
	 }

	 /**
     * Get the details of a State by Country ID.
     *
     * @param string $id The ID to retrieve State details.
     * @return object The details of the State as an object.
     */
    public function getStateDetailsByCntId($id)
    {
        $this->db->where("countryid", $id);
        return $this->db->get($this->table)->result();
    }

	/**
     * Check if an State exists .
     *
     * @param string $statename and $countryId to check.
     * @return int The count of State .
     */
	public function chkIsExtists($statename,$countryId)
    {
        $this->db->where("statename", $statename);
		$this->db->where("countryid", $countryId);
        $query = $this->db->get($this->table);
        return $query->num_rows();
    }

	/**
     * Check if an State exists while updatte.
     *
     * @param string $data,$id The State  to check.
     * @return int The count of State .
     */
	function chkWhileUpdate($id,$stateName,$countryid) {
		$this->db->where("statename", $stateName);
		$this->db->where("countryid", $countryid);
		$this->db->where("id !=", $id);
		$query = $this->db->get($this->table);
		return $query->num_rows();
	}
	 

    function getStateIDByName($stateName) {
        $this->db->where("statename", $stateName);
        return $this->db->get($this->table)->result();
	}

}
