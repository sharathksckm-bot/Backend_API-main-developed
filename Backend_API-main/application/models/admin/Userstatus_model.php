<?php
/**
 * userstatus_model Model
 *
 * @category   Models
 * @package    Admin
 * @subpackage userstatus_model
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    11 JAN 2024
 *
 * Class userstatus_model handles all user status -related operations.
 */

defined("BASEPATH") or exit("No direct script access allowed");

class Userstatus_model extends CI_Model
{
    private $table = "user_status";

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get the list of all users types.
     *
     * @return array The list of all users types.
     */
	public function list(){
		return $this->db->get($this->table)->result_array();
	}

	function chkUserStatus($Status) {
		$this->db->where('name', $Status);
		$query = $this->db->get($this->table);
		return $query->num_rows();
	}

	function insert($data) {
		$this->db->insert($this->table, $data);
		return $this->db->insert_id();
	}

	function update($id, $data) {
		$this->db->where('id', $id);
		$query = $this->db->update($this->table, $data);
		return $query; 
	}

	function chkWhileUpdate($id, $data) {
		$this->db->where('name', $data);
		$this->db->where("id !=", $id);
		$query = $this->db->get($this->table);
		return $query->num_rows();
	}

	public function getDataById($StatusId)
	{
		$this->db->where('id', $StatusId);
		return $this->db->get($this->table)->row();
	}

	function delete($id) {
		$this->db->where('id', $id);
		$query = $this->db->delete($this->table);
		return $query;
	}

	/**
     * Count all Status in the table.
     *
     * @return int The total number of Status.
     */
    public function countAllStatus()
    {
        return $this->db->count_all($this->table);
    }

	/**
     * Count filtered Status based on the search term.
     *
     * @param string $search The search term.
     * @return int The number of filtered Status.
     */
    public function countFilteredStatus($search)
    {
        $this->db->like('name', $search);
        return $this->db->get($this->table)->num_rows();
    }

	/**
     * Get filtered Status.
     *
     * @param string $search The search term.
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated Status with additional information.
     */

	 public function getFilteredStatus($search, $start, $limit, $order, $dir)
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
     * Get all Status with filtering, ordering, and pagination.
     *
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated Status.
     */

	 public function getAllStatus($start, $limit, $order, $dir)
	 {
		 $this->db->select("*");
		 $this->db->from($this->table);
 
		 $this->db->order_by($order, $dir);
		 $this->db->limit($limit, $start);
 
		 return $this->db->get()->result();
	 }
}
