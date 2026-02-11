<?php
/**
 * Rank category Model
 *
 * @category   Models
 * @package    Admin
 * @subpackage Rank category
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    22 JAN 2024
 * 
 * Class rankcategory_model handles all rank-related operations.
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Rankcategory_model extends CI_Model {

    private $table = 'rank_categories';

    public function __construct() {
        parent::__construct();
    }

	/**
     * Count all Rank category in the table.
     *
     * @return int The total number of Rank category.
     */
    public function countAllRankCategory()
    {
        return $this->db->count_all($this->table);
    }

	/**
	 * Count filtered Rank category based on the search term.
	 *
	 * @param string $search The search term.
	 * @return int The number of filtered Rank categories.
	 */
	public function countFilteredRankCategory($search)
	{
		$this->db->select('rc.*');
		$this->db->like('rc.title', $search);
		return $this->db->get($this->table . " rc")->num_rows(); 
	}

	/**
     * Get filtered Rank category.
     *
     * @param string $search The search term.
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated Rank category with additional information.
     */

	 public function getFilteredRankCategory($search, $start, $limit, $order, $dir)
		{
			$this->db->select('rc.*');
			$this->db->from($this->table . " rc");

			$this->db->group_start();
			$this->db->like('rc.title', $search);
			$this->db->group_end();

			$this->db->order_by($order, $dir);
			$this->db->limit($limit, $start);

			return $this->db->get()->result();
		}

		/**
     * Get all Rank category with filtering, ordering, and pagination.
     *
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated facilities.
     */

	 public function getAllRankCategory($start, $limit, $order, $dir)
	 {
		$this->db->select('rc.*');
		$this->db->from($this->table . " rc");
		 $this->db->order_by($order, $dir);
		 $this->db->limit($limit, $start);
 
		 return $this->db->get()->result();
	 }

	  /**
     * Check if Rank category exists for any particular.
     *
     * @param string $title The Rank category to check.
     * @return int The count of Rank category with the specified title.
     */
    public function checkIsExists($title)
    {
        $this->db->where("title", $title);
        $query = $this->db->get($this->table);
        return $query->num_rows();
    }

	/**
     * Insert details for all Rank category into the database.
     *	
     * @param array $data The data to be inserted.
     * @return bool True if data insertion is successful, otherwise false.
     */
    public function insertRankCategory($data)
    {
        $query = $this->db->insert($this->table, $data);
		return $this->db->insert_id();
    }

	/**
     * Check if an Rank category exists while updatte.
     *
     * @param string $data,$id The Rank category  to check.
     * @return int The count of Rank category .
     */
	function checkWhileUpdate($id, $data) {
		$this->db->where('title', $data);
		$this->db->where("category_id !=", $id);
		$query = $this->db->get($this->table);
		return $query->num_rows();
	}
	/**
     * Update the details of a Rank category by ID.
     *
     * @param string $id   The ID of the Rank category to be updated.
     * @param array  $data An associative array containing the data to be updated.
     * @return bool        True if the update operation is successful, otherwise false.
     */
    public function updateRankCategory($id, $data)
    {
        $this->db->where("category_id", $id);
        $query = $this->db->update($this->table, $data);
        return $query;
    }

	/**
     * Get the details of a Rank category by ID.
     *
     * @param string $id The ID to retrieve Rank category details.
     * @return object The details of the Rank category as an object.
     */
    public function getRnkCatDetailsById($id)
    {
        $this->db->where("category_id", $id);
        return $this->db->get($this->table)->row();
    }

	/**
     * Delete the details of a Rank category by ID.
     *
     * @param string $id   The ID of the Rank category to be deleted.
     * @return bool        True if the delete operation is successful, otherwise false.
     */
    public function deleteRnkCatDetails($id)
    {
        $this->db->where("category_id", $id);
        $query = $this->db->delete($this->table);
        return $query;
    }

	public function get_rankcategories()
  {
    $query = $this->db->get('rank_categories');

    if ($query->num_rows() > 0) {
      return $query->result();
    } else {
      return false;
    }
  }

}
