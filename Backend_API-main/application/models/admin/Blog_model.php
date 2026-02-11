<?php
/**
 * blog Model
 *
 * @category   Models
 * @package    Admin
 * @subpackage blog
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    26 JAN 2024
 * 
 * Class blog_model handles all blog-related operations.
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Blog_model extends CI_Model {

    private $table = 'blog';

    public function __construct() {
        parent::__construct();
    }
	/**
     * Count all blog in the table.
     *
     * @return int The total number of blog.
     */
    public function countAllblog($userId,$userType)
    {
        if($userType == 13)
        {   $this->db->where('created_by',$userId);
            return $this->db->count_all_results($this->table);
        }
        else
        {
            return $this->db->count_all($this->table);

        }
    }

	/**
     * Count filtered blogs based on the search term.
     *
     * @param string $search The search term.
     * @return int The number of filtered blogs.
     */
    public function countFilteredBlogs($search,$userId,$userType)
    {
        if($userType == 13 || $userType == 14)
        {
               $this->db->where('created_by',$userId);
        }
        $this->db->like('b.title', $search);
        $this->db->or_like('bc.name', $search);
        $this->db->join("blog_category bc", "bc.id = b.categoryid", "left");
        return $this->db->get($this->table . " b")->num_rows(); 
    }


	/**
     * Get filtered blog.
     *
     * @param string $search The search term.
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated blog with additional information.
     */

	 public function getFilteredblog($search, $start, $limit, $order, $dir,$userId,$userType)
	 { 
        $this->db->select('b.*, bc.name AS category, CONCAT(u.f_name, " ", u.l_name) as created_by_name, CONCAT(u1.f_name, " ", u1.l_name) as updated_by_name');
        $this->db->from($this->table . " b");
		$this->db->join("blog_category bc", "bc.id = b.categoryid", "left");
        $this->db->join("users u", "u.id = b.created_by", "left");
		$this->db->join("users u1", "u1.id = b.updated_by", "left");
        if($userType == 13 || $userType == 14)
        {
               $this->db->where('created_by',$userId);
        }
		 $this->db->group_start(); 
		 $this->db->like('b.title', $search);
		 $this->db->or_like('bc.name', $search);
		 $this->db->group_end(); 
 
		 $this->db->order_by($order, $dir);
         $this->db->order_by('created_date', 'desc');
		 $this->db->limit($limit, $start);
 
		 return $this->db->get()->result();
	 }

	 /**
     * Get all blog with filtering, ordering, and pagination.
     *
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated blog.
     */

	 public function getAllBlog($start, $limit, $order, $dir,$userId,$userType)
        {
            $this->db->select('b.*, bc.name AS category, CONCAT(u.f_name, " ", u.l_name) as created_by_name, CONCAT(u1.f_name, " ", u1.l_name) as updated_by_name');
            $this->db->from($this->table . " AS b");
            $this->db->join("blog_category AS bc", "bc.id = b.categoryid", "left");
            $this->db->join("users AS u", "u.id = b.created_by", "left");
            $this->db->join("users AS u1", "u1.id = b.updated_by", "left");
            if($userType == 13 || $userType == 14)
            {
               $this->db->where('created_by',$userId);
            }
            $this->db->order_by('created_date', 'desc');
            $this->db->order_by($order, $dir);
            $this->db->limit($limit, $start);
            return $this->db->get()->result();
        }

	 /**
     * Check if an blog exists .
     *
     * @param string $name to check.
     * @return int The count of blog .
     */
	public function chkIfExists($title)
    {
        $this->db->where("title", $title);
        $query = $this->db->get($this->table);
        return $query->num_rows();
    }

	/**
     * Insert details for blog into the database.
     *
     * @param array $data The data to be inserted.
     * @return bool True if data insertion is successful, otherwise false.
     */
/*	public function insertblogDetails($data)
	{
	    //print_r($data);exit;
		$query = $this->db->insert($this->table, $data);
		// echo $this->db->last_query();exit;
		return $this->db->insert_id();
	}*/
	
		public function insertblogDetails($data)
	{
	    //print_r($data);exit;
		$query = $this->db->insert('blog', $data);
		// echo $this->db->last_query();exit;
		return $this->db->insert_id();
	}

	/**
     * Check if an blog exists while updatte.
     *
     * @param string $data,$id The blog  to check.
     * @return int The count of blog .
     */
	function chkWhileUpdate($id,$name) {
		$this->db->where("title", $name);
		$this->db->where("id !=", $id);
		$query = $this->db->get($this->table);
		return $query->num_rows();
	}

	/**
     * Update the details of a blog by ID.
     *
     * @param string $id   The ID of the blog to be updated.
     * @param array  $data An associative array containing the data to be updated.
     * @return bool        True if the update operation is successful, otherwise false.
     */
    public function updateblogDetails($id, $data)
    {
        $this->db->where("id", $id);
        $query = $this->db->update($this->table, $data);
       // echo $this->db->last_query();exit;
        return $query;
    }
	/**
     * Get the details of a blog by ID.
     *
     * @param string $id The ID to retrieve blog details.
     * @return object The details of the blog as an object.
     */
    public function getblogDetailsById($id)
    {
        $this->db->select('b.*, GROUP_CONCAT(DISTINCT c.title) as collegename,GROUP_CONCAT(DISTINCT c.id) as college_id,e.title as exam_name'); 
        $this->db->where("b.id", $id);
        $this->db->join('college c', "FIND_IN_SET(c.id, b.college_id)", 'left'); 
        $this->db->join("exams AS e", "e.id = b.exam_id", "left");

        return $this->db->get($this->table . " b")->row();

    }

	/**
     * Delete the details of a blog by ID.
     *
     * @param string $id   The ID of the blog to be deleted.
     * @return bool        True if the delete operation is successful, otherwise false.
     */
    public function deleteblog($id)
    {
        $this->db->where("id", $id);
        $query = $this->db->delete($this->table);
        return $query;
    }

}
	