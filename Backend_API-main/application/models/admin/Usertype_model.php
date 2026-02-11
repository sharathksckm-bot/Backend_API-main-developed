<?php
/**
 * usertype_model Model
 *
 * @category   Models
 * @package    Admin
 * @subpackage usertype_model
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    11 JAN 2024
 *
 * Class usertype_model handles all usertype -related operations.
 */

defined("BASEPATH") or exit("No direct script access allowed");

class Usertype_model extends CI_Model
{
    private $table = "user_type";

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

	/**
     * check if an users types exists or not.
     *     
	 * @param string $type The name to retrieve user type.
     * @return array The list of all users types.
     */
	public function chkUserType($type) {
		$this->db->where('name', $type);
		$query = $this->db->get($this->table);
		return $query->num_rows();
	}
	/**
     * Insert details for user type into the database.
     *
     * @param array $data The data to be inserted.
     * @return bool True if data insertion is successful, otherwise false.
     */
	public function insert($data)
	{
		$query = $this->db->insert($this->table, $data);
		return $this->db->insert_id();
	}

	public function getDataById($TypeId)
	{
		$this->db->where('id', $TypeId);
		return $this->db->get($this->table)->row();
	}

	public function delete($id) {
		$this->db->where('id', $id);
		$query = $this->db->delete($this->table);
		return $query;
	}

	public function update($id, $data) {
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

	/**
     * Count all Type in the table.
     *
     * @return int The total number of Type.
     */
    public function countAllType()
    {
        return $this->db->count_all($this->table);
    }

	/**
     * Count filtered Type based on the search term.
     *
     * @param string $search The search term.
     * @return int The number of filtered Type.
     */
    public function countFilteredType($search)
    {
        $this->db->like('name', $search);
        return $this->db->get($this->table)->num_rows();
    }

	/**
     * Get filtered Type.
     *
     * @param string $search The search term.
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated Type with additional information.
     */

	 public function getFilteredType($search, $start, $limit, $order, $dir)
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
     * Get all Type with filtering, ordering, and pagination.
     *
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated Type.
     */

	 public function getAllType($start, $limit, $order, $dir)
	 {
		 $this->db->select("*");
		 $this->db->from($this->table);
 
		 $this->db->order_by($order, $dir);
		 $this->db->limit($limit, $start);
 
		 return $this->db->get()->result();
	 }
}
