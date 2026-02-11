<?php
/**
 * certification Model
 *
 * @category   Models
 * @package    Admin
 * @subpackage certification
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    26 JAN 2024
 * 
 * Class certification_model handles all certification-related operations.
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Certification_model extends CI_Model {

    private $table = 'certification';

    public function __construct() {
        parent::__construct();
    }

    /**
     * Count all certification in the table.
     *
     * @return int The total number of certification.
     */
    public function countAllCertificate()
    {
        
            return $this->db->count_all($this->table);

        
    }

    /**
     * Count filtered certification based on the search term.
     *
     * @param string $search The search term.
     * @return int The number of filtered certification.
     */
    public function countFilteredCertificates($search)
    {
        $this->db->like('c.name', $search);
        $this->db->or_like('ct.catname', $search);
        $this->db->join("category ct", "ct.id = c.category", "left");
        return $this->db->get($this->table . " c")->num_rows(); 
    }

    /**
     * Get filtered certificate.
     *
     * @param string $search The search term.
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated certificate with additional information.
     */

	 public function getFilteredCertificate($search, $start, $limit, $order, $dir)
	 { 
        $this->db->select('c.*, ct.catname AS categoryName, CONCAT(u.f_name, " ", u.l_name) as created_by_name, CONCAT(u1.f_name, " ", u1.l_name) as updated_by_name');
        $this->db->from($this->table . " c");
		$this->db->join("category ct", "ct.id = c.category", "left");
        $this->db->join("users u", "u.id = c.created_by", "left");
		$this->db->join("users u1", "u1.id = c.updated_by", "left");
		 $this->db->group_start(); 
		 $this->db->like('c.name', $search);
		 $this->db->or_like('ct.catname', $search);
		 $this->db->group_end(); 
 
		 $this->db->order_by($order, $dir);
         $this->db->order_by('created_date', 'desc');
		 $this->db->limit($limit, $start);
 
		 return $this->db->get()->result();
	 }

      /**
     * Get all certificate with filtering, ordering, and pagination.
     *
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated certificate.
     */

	 public function getAllcertificate($start, $limit, $order, $dir)
     {
        $this->db->select('c.*, ct.catname AS categoryName, CONCAT(u.f_name, " ", u.l_name) as created_by_name, CONCAT(u1.f_name, " ", u1.l_name) as updated_by_name');
        $this->db->from($this->table . " c");
		$this->db->join("category ct", "ct.id = c.category", "left");
        $this->db->join("users u", "u.id = c.created_by", "left");
		$this->db->join("users u1", "u1.id = c.updated_by", "left");
         $this->db->order_by('created_date', 'desc');
         $this->db->order_by($order, $dir);
         $this->db->limit($limit, $start);
         return $this->db->get()->result();
     }

     public function chkIfExists($title)
     {
        $this->db->where("name", $title);
        $query = $this->db->get($this->table);
        return $query->num_rows();
     }

     public function insertcertifiateDetails($Arr)
     {
        $query = $this->db->insert($this->table, $Arr);
		return $this->db->insert_id();
     }

     public function chkWhileUpdate($title,$id)
     {
        $this->db->where("name", $title);
		$this->db->where("id !=", $id);
		$query = $this->db->get($this->table);
		return $query->num_rows();
     }

     public function updateCertificateDetails($id,$Arr)
     {
        $this->db->where("id", $id);
        $query = $this->db->update($this->table, $Arr);
        return $query;
     }

     public function getCertificateDetailsById($Id)
     {
        $this->db->select('c.*, GROUP_CONCAT(ce.title) as collegename'); 
        $this->db->where("c.id", $Id);
        $this->db->join('college ce', "FIND_IN_SET(ce.id, c.college)", 'left'); 
        return $this->db->get($this->table . " c")->row();
     }


     public function deleteCertificate($Id)
     {
        $this->db->where("id", $Id);
        $query = $this->db->delete($this->table);
        return $query;
     }
}
