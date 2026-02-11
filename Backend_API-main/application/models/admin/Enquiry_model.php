<?php
/**
 * enquiry Model
 *
 * @category   Models
 * @package    Admin
 * @subpackage enquiry
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    22 JAN 2024
 * 
 * Class enquiry_model handles all enquiry-related operations.
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Enquiry_model extends CI_Model {

    private $table = 'inquiry';

    public function __construct() {
        parent::__construct();
    }
	/**
     * Count all enquiry in the table.
     *
     * @return int The total number of enquiry.
     */
    public function countAllEnquiry($fromdate='',$todate='')
    {
        if (!empty($fromdate) && !empty($todate)) {
			$this->db->where('create_date >=', $fromdate);
			$this->db->where('create_date <=', $todate);
		} elseif (!empty($fromdate)) {
			$this->db->where('create_date >=', $fromdate);
		} elseif (!empty($todate)) {
			$this->db->where('create_date <=', $todate);
		}
        return $this->db->count_all($this->table);
    }

	/**
     * Count filtered Enquiry based on the search term.
     *
     * @param string $search The search term.
     * @return int The number of filtered Enquiry.
     */
    public function countFilteredEnquiry($search)
	{
    $this->db->select('COUNT(*) as count');
    $this->db->from($this->table . ' i');
    $this->db->join('courses as cs', 'cs.id = i.course', 'left');
    $this->db->join('college as c', 'c.id = i.postid AND i.type = "college"', 'left');
    $this->db->group_start();
    $this->db->like('i.name', $search);
    $this->db->or_like('i.email', $search);
    $this->db->or_like('i.message', $search);
    $this->db->or_like('cs.name', $search);
    $this->db->or_like('c.title', $search);
    $this->db->group_end();
    return $this->db->get()->row()->count;
	}


	/**
     * Get filtered Enquiry.
     *
     * @param string $search The search term.
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated Enquiry with additional information.
     */

	 public function getFilteredEnquiry($search, $start, $limit, $order, $dir)
	{
		$this->db->select('i.*, cs.name as course, c.title as college, u1.email as attended_by_email , u1.f_name,ct.city as cityname,s.statename');
		$this->db->from($this->table . ' i');
		$this->db->join('courses as cs', 'cs.id = i.course', 'left');
		$this->db->join('college as c', 'c.id = i.postid AND i.type = "college"', 'left');
		$this->db->join('users as u', 'u.id = i.attended_by', 'left');
        $this->db->join('city as ct', 'ct.id = i.city', 'left');
		$this->db->join('state as s', 's.id = i.state', 'left');
		$this->db->group_start();
		$this->db->like('i.name', $search);
		$this->db->or_like('i.email', $search);
		$this->db->or_like('i.message', $search);
		$this->db->or_like('cs.name', $search);
		$this->db->or_like('c.title', $search);
		$this->db->group_end();

		$this->db->order_by($order, $dir);
		$this->db->limit($limit, $start);

		return $this->db->get()->result();
	}

	/**
     * Get all Enquiry with filtering, ordering, and pagination.
     *
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated Enquiry.
     */

	 public function getAllEnquiry($start, $limit, $order, $dir)
	 {
		$this->db->select('i.*, cs.name as course, c.title as college, u1.email as attended_by_email , u1.f_name,ct.city as cityname,s.statename');
		$this->db->from($this->table . ' i');
		$this->db->join('courses as cs', 'cs.id = i.course', 'left');
		$this->db->join('college as c', 'c.id = i.postid AND i.type = "college"', 'left');
		$this->db->join('users as u1', 'u1.id = i.attended_by', 'left');
		$this->db->join('city as ct', 'ct.id = i.city', 'left');
		$this->db->join('state as s', 's.id = i.state', 'left');
		$this->db->order_by($order, $dir);
		$this->db->limit($limit, $start);

		return $this->db->get()->result();
	 }

	 /**
     * Delete the details of a Enquiry by ID.
     *
     * @param string $id   The ID of the Enquiry to be deleted.
     * @return bool        True if the delete operation is successful, otherwise false.
     */
    public function deleteEnquiry($id)
    {
        $this->db->where("id", $id);
        $query = $this->db->delete($this->table);
        return $query;
    }

	public function updateStatus($Id,$arr,$type)
	{
		$this->db->where("id", $Id);
        $query = $this->db->update($type, $arr);
		
        return $query;
	}

    public function updateData($enquiryId,$Arr)
    {
        $this->db->where("id", $enquiryId);
        $query = $this->db->update($this->table, $Arr);
		
        return $query;
    }

}
