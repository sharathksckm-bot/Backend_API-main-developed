<?php
/**
 * loan Model
 *
 * @category   Models
 * @package    Admin
 * @subpackage loan
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    25 JAN 2024
 * 
 * Class loan_model handles all loan-related operations.
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Loan_model extends CI_Model {

    private $table = 'loans';
	private $imgtable = 'galler';

    public function __construct() {
        parent::__construct();
    }
	/**
     * Count all Loan in the table.
     *
     * @return int The total number of Loan.
     */
    public function countAllLoan()
    {
        return $this->db->count_all($this->table);
    }

	/**
     * Count filtered Loan based on the search term.
     *
     * @param string $search The search term.
     * @return int The number of filtered Loan.
     */
    public function countFilteredLoan($search)
	{
    $this->db->like('name', $search);
	$this->db->or_like('type', $search);

    return $this->db->get($this->table)->num_rows(); 
	}

	/**
     * Get filtered Loan.
     *
     * @param string $search The search term.
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated Loan with additional information.
     */

	 public function getFilteredLoan($search, $start, $limit, $order, $dir)
	 { 
		$this->db->select("*");
        $this->db->from($this->table);
		 $this->db->group_start(); 
		 $this->db->like('name', $search);
		 $this->db->or_like('type', $search);
		 $this->db->group_end(); 
 
		 $this->db->order_by($order, $dir);
		 $this->db->limit($limit, $start);
 
		 return $this->db->get()->result();
	 }

	 /**
     * Get all Loan with filtering, ordering, and pagination.
     *
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated Loan.
     */

	 public function getAllLoan($start, $limit, $order, $dir)
	 {
		$this->db->select("*");
        $this->db->from($this->table);
		 $this->db->order_by($order, $dir);
		 $this->db->limit($limit, $start);
 
		 return $this->db->get()->result();
	 }

	 /**
     * Check if an Loan exists .
     *
     * @param string $name to check.
     * @return int The count of Loan .
     */
	public function chkIfExists($provider_name)
    {
        $this->db->where("name", $provider_name);
        $query = $this->db->get($this->table);
        return $query->num_rows();
    }

	/**
     * Insert details for Loan into the database.
     *
     * @param array $data The data to be inserted.
     * @return bool True if data insertion is successful, otherwise false.
     */
	public function insertLoanDetails($data)
	{
		$query = $this->db->insert($this->table, $data);
		return $query;
	}

	/**
     * Check if an Loan exists while updatte.
     *
     * @param string $data,$id The Loan  to check.
     * @return int The count of Loan .
     */
	function chkWhileUpdate($id,$name) {
		$this->db->where("name", $name);
		$this->db->where("id !=", $id);
		$query = $this->db->get($this->table);
		return $query->num_rows();
	}

	/**
     * Update the details of a Loan by ID.
     *
     * @param string $id   The ID of the Loan to be updated.
     * @param array  $data An associative array containing the data to be updated.
     * @return bool        True if the update operation is successful, otherwise false.
     */
    public function updateLoanDetails($id, $data)
    {
        $this->db->where("id", $id);
        $query = $this->db->update($this->table, $data);
        return $query;
    }
	/**
     * Get the details of a Loan by ID.
     *
     * @param string $id The ID to retrieve Loan details.
     * @return object The details of the Loan as an object.
     */
    public function getLoanDetailsById($id)
    {
        $this->db->where("id", $id);
        return $this->db->get($this->table)->row();
    }

	/**
     * Delete the details of a Loan by ID.
     *
     * @param string $id   The ID of the Loan to be deleted.
     * @return bool        True if the delete operation is successful, otherwise false.
     */
    public function deleteLoan($id)
    {
        $this->db->where("id", $id);
        $query = $this->db->delete($this->table);
        return $query;
    }

	public function getLoanImageById($Id)
	{
		$this->db->select('*');
		$this->db->from($this->imgtable);
		$this->db->where('postid', $Id);
		$this->db->where('type', 'loan');
		return $this->db->get()->result();
	}
}
	