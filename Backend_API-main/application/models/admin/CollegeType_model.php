<?php 
/**
 * collegeType Model
 *
 * @category   Models
 * @package    Admin
 * @subpackage collegeType
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    09 JAN 2024
 *
 * Class collegeType_model handles all college type-related operations.
 */
defined("BASEPATH") or exit("No direct script access allowed");

class CollegeType_model extends CI_Model
{
    private $table = "college_type";

    public function __construct()
    {
        parent::__construct();
    }

	/**
     * Count all college type in the table.
     *
     * @return int The total number of college type.
     */
    public function countAllClgTypes()
    {
        return $this->db->count_all($this->table);
    }

	/**
     * Count filtered college type based on the search term.
     *
     * @param string $search The search term.
     * @return int The number of filtered users.
     */
    public function countFilteredClgTypes($search)
    {
        $this->db->like('name', $search);
        $this->db->or_like('status', $search);
        return $this->db->get($this->table)->num_rows();
    }

	 /**
 * Get filtered college type.
 *
 * @param string $search The search term.
 * @param int    $start  The starting index for pagination.
 * @param int    $limit  The number of records to retrieve.
 * @param string $order  The column to order by.
 * @param string $dir    The direction of sorting.
 * @return array The list of filtered and paginated users with additional information.
 */
public function getFilteredClgTypes($search, $start, $limit, $order, $dir)
{
    $this->db->select("c.*");
    $this->db->from($this->table . " c");
    $this->db->where('c.name LIKE', '%' . $search . '%');
    $this->db->order_by($order, $dir);
    $this->db->limit($limit, $start);

    return $this->db->get()->result();
}


	  /**
     * Get all college type with filtering, ordering, and pagination.
     *
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated college type.
     */

	 public function getAllClgTypes($start, $limit, $order, $dir)
	 {
		 $this->db->select("c.*");
		 $this->db->from($this->table . " c");
		 $this->db->order_by($order, $dir);
		 $this->db->limit($limit, $start);
 
		 return $this->db->get()->result();
	 }

	 /**
     * Check if an college type exists.
     *
     * @param string $type The type  to check.
     * @return int The count of college type with the specified type.
     */
    public function checkClgTypeExits($type)
    {
        $this->db->where("name", $type);
        $query = $this->db->get($this->table);
        return $query->num_rows();
    }

	/**
     * Insert details for college type into the database.
     *	
     * @param array $data The data to be inserted.
     * @return bool True if data insertion is successful, otherwise false.
     */
    public function insertClgTypeDetails($data)
    {
        $query = $this->db->insert($this->table, $data);
		return $this->db->insert_id();
    }

	public function checkClgTypeWhileupdate($id,$type)
	{
		$this->db->where('name', $type);
		$this->db->where("id !=", $id);
		$query = $this->db->get($this->table);
		return $query->num_rows();
	}

	 /**
     * Update the details of a college type by ID.
     *
     * @param string $id   The ID of the college type to be updated.
     * @param array  $data An associative array containing the data to be updated.
     * @return bool        True if the update operation is successful, otherwise false.
     */
    public function updateClgTypeDetails($id, $data)
    {
        $this->db->where("id", $id);
        $query = $this->db->update($this->table, $data);
        return $query;
    }

	/**
     * Get the details of a college type by ID.
     *
     * @param string $id The ID to retrieve college type details.
     * @return object The details of the college type as an object.
     */
    public function getClgTypeDetailsById($id)
    {
        $this->db->where("id", $id);
        return $this->db->get($this->table)->row();
    }

	/**
     * Delete the details of a college type by ID.
     *
     * @param string $id   The ID of the college type to be deleted.
     * @return bool        True if the delete operation is successful, otherwise false.
     */
    public function deleteclgTypeDetails($id)
    {
        $this->db->where("id", $id);
        $query = $this->db->delete($this->table);
        return $query;
    }

	public function getCollegeType()
	{
		$this->db->select("c.*");
		$this->db->from($this->table . " c");
		return $this->db->get()->result();
	}

    public function getClgTypes($search_clgtype)
    {
        $this->db->select("*");
		$this->db->from($this->table); 
		if ($search_clgtype !== NULL) {
			$this->db->like('name', $search_clgtype);
		}
		$this->db->limit(10);
		return $this->db->get()->result();
    }
}
