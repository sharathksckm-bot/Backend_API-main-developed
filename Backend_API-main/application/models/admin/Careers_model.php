<?php
/**
 * Careers Model
 *
 * @category   Models
 * @package    Admin
 * @subpackage Careers
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    26 JAN 2024
 * 
 * Class Careers_model handles all Careers-related operations.
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Careers_model extends CI_Model {

    private $table = 'careers';
	private $imgtable = 'gallery';

    public function __construct() {
        parent::__construct();
    }
	/**
     * Count all Careers in the table.
     *
     * @return int The total number of Careers.
     */
    public function countAllCareers()
    {
        return $this->db->count_all($this->table);
    }

	/**
     * Count filtered Careers based on the search term.
     *
     * @param string $search The search term.
     * @return int The number of filtered Careers.  careers
     */
    public function countFilteredCareers($search)
    {
        $this->db->like('cr.title', $search);
        $this->db->or_like('c.catname', $search);
        $this->db->join("category c", "c.id = cr.categoryid", "left");
        return $this->db->get($this->table . " cr")->num_rows(); 
    }

	/**
     * Get filtered Careers.
     *
     * @param string $search The search term.
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated Careers with additional information.
     */

	 public function getFilteredCareers($search, $start, $limit, $order, $dir)
	 { 
		$this->db->select("cr.*, c.catname AS category,g.image");
        $this->db->from($this->table . " cr");
        $this->db->join("category c", "c.id = cr.categoryid", "left");
		$this->db->join("gallery g", "g.postid = cr.id", "left");
		 $this->db->group_start(); 
		 $this->db->like('cr.title', $search);
		 $this->db->or_like('c.catname', $search);
		 $this->db->group_end(); 
 
		 $this->db->order_by($order, $dir);
		 $this->db->limit($limit, $start);
 
		 return $this->db->get()->result();
	 }


	  /**
     * Get all Careers with filtering, ordering, and pagination.
     *
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated Careers.
     */

	 public function getAllCareers($start, $limit, $order, $dir)
	 {
		$this->db->select("cr.*, c.catname AS category,g.image");
        $this->db->from($this->table . " cr");
        $this->db->join("category c", "c.id = cr.categoryid", "left");
		$this->db->join("gallery g", "g.postid = cr.id", "left");
		 $this->db->order_by($order, $dir);
		 $this->db->limit($limit, $start);
 
		 return $this->db->get()->result();
	 }


	 /**
     * Insert details for Careers into the database.
     *
     * @param array $data The data to be inserted.
     * @return bool True if data insertion is successful, otherwise false.
     */
	public function insertCareersDetails($data)
	{
		$query = $this->db->insert($this->table, $data);
		$careerId['careerId'] = $this->db->insert_id();
		 return  $careerId;
	}

	/**
     * Check if an Careers exists .
     *
     * @param string $slug and $slug to check.
     * @return int The count of Careers .
     */
	public function chkIfExists($slug)
    {
        $this->db->where("slug", $slug);
        $query = $this->db->get($this->table);
        return $query->num_rows();
    }

	/**
     * Insert docs details for Careers into the database.
     *
     * @param array $data The data to be inserted.
     * @return bool True if data insertion is successful, otherwise false.
     */
	public function insertCareerDocsDetails($data)
	{
		$query = $this->db->insert($this->imgtable, $data);
		 $imageId['imageId'] = $this->db->insert_id();
		 return  $imageId;
	}

	public function updateCareerDocsDetails($id,$postid,$Arr)
	{
		$this->db->where("id", $id);
		$this->db->where("postid", $postid);
        $query = $this->db->update($this->imgtable, $Arr);
        return $query;
	}

	/**
     * Check if an Careers exists while updatte.
     *
     * @param string $data,$id The State  to check.
     * @return int The count of State .
     */
	function chkWhileUpdate($clgId,$slug) {
		$this->db->where('slug', $slug);
		$this->db->where('id !=', $clgId);
		$query = $this->db->get($this->table);
		return $query->num_rows();

	}

	/**
     * Update the details of a Careers by ID.
     *
     * @param string $id   The ID of the Careers to be updated.
     * @param array  $data An associative array containing the data to be updated.
     * @return bool        True if the update operation is successful, otherwise false.
     */
    public function updateCareersDetails($id, $data)
    {
        $this->db->where("id", $id);
        $query = $this->db->update($this->table, $data);
        return $query;
    }

	public function deleteDoc($Id)
	{
		$this->db->where('id', $Id);
		return $this->db->delete($this->imgtable);
	}
	
	public function deleteCareers($id)
    {
        $this->db->where("id", $id);
        $query = $this->db->delete($this->table);
        return $query;
    }


	/**
     * Get the details of a Careers by ID.
     *
     * @param string $id The ID to retrieve Careers details.
     * @return object The details of the Careers as an object.
     */
    public function getCareersDetailsById($id)
    {
        $this->db->where("id", $id);
		$this->db->where('status', '1');
        return $this->db->get($this->table)->row();
    }

	public function getCareersImageByClgId($Id)
	{
		$this->db->select('*');
		$this->db->from($this->imgtable);
		$this->db->where('postid', $Id);
		$this->db->where('type', 'careers');
		return $this->db->get()->result();
	}
}
