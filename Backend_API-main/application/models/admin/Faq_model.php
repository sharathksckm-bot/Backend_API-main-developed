<?php

/**
 * faq Model
 *
 * @category   Models
 * @package    Admin
 * @subpackage faq
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    26 JAN 2024
 * 
 * Class faq_model handles all faq-related operations.
 */

defined('BASEPATH') or exit('No direct script access allowed');

class Faq_model extends CI_Model
{

    private $table = 'faq';

    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Count all faq in the table.
     *
     * @return int The total number of faq.
     */
    public function countAllFaq()
    {
        return $this->db->count_all($this->table);
    }

    /**
     * Count filtered faq based on the search term.
     *
     * @param string $search The search term.
     * @return int The number of filtered faq.
     */
    public function countFilteredFaq($search)
    {
        $this->db->like('f.heading', $search);
        $this->db->or_like('c.catname', $search);
        $this->db->join("category c", "c.id = f.categoryid", "left");
        return $this->db->get($this->table . " f")->num_rows();
    }

    /**
     * Get filtered faq.
     *
     * @param string $search The search term.
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated faq with additional information.
     */

    public function getFilteredfaq($search, $start, $limit, $order, $dir)
    {
        $this->db->select("f.*,c.catname as category");
        $this->db->from($this->table . " f");
        $this->db->join("category c", "c.id = f.categoryid", "left");
        $this->db->group_start();
        $this->db->like('f.heading', $search);
        $this->db->or_like('c.catname', $search);
        $this->db->group_end();

        $this->db->order_by($order, $dir);
        $this->db->limit($limit, $start);

        return $this->db->get()->result();
    }

    /**
     * Get all faq with filtering, ordering, and pagination.
     *
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated faq.
     */

    public function getAllfaq($start, $limit, $order, $dir)
    {
        $this->db->select("f.*,c.catname as category");
        $this->db->from($this->table . " f");
        $this->db->join("category c", "c.id = f.categoryid", "left");
        $this->db->order_by($order, $dir);
        $this->db->limit($limit, $start);

        return $this->db->get()->result();
    }

    /**
     * Check if an faq exists .
     *
     * @param string $name to check.
     * @return int The count of faq .
     */
    public function chkIfExists($heading)
    {
        $this->db->where("heading", $heading);
        $query = $this->db->get($this->table);
        return $query->num_rows();
    }

    /**
     * Insert details for faq into the database.
     *
     * @param array $data The data to be inserted.
     * @return bool True if data insertion is successful, otherwise false.
     */
    public function insertfaqDetails($data)
    {
        $query = $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    /**
     * Check if an faq exists while updatte.
     *
     * @param string $data,$id The faq  to check.
     * @return int The count of faq .
     */
    function chkWhileUpdate($id, $name)
    {
        $this->db->where("heading", $name);
        $this->db->where("id !=", $id);
        $query = $this->db->get($this->table);
        return $query->num_rows();
    }

    /**
     * Update the details of a faq by ID.
     *
     * @param string $id   The ID of the faq to be updated.
     * @param array  $data An associative array containing the data to be updated.
     * @return bool        True if the update operation is successful, otherwise false.
     */
    public function updatefaqDetails($id, $data)
    {
        $this->db->where("id", $id);
        $query = $this->db->update($this->table, $data);
        return $query;
    }
    /**
     * Get the details of a faq by ID.
     *
     * @param string $id The ID to retrieve faq details.
     * @return object The details of the faq as an object.
     */
    public function getfaqDetailsById($id)
    {
        $this->db->where("id", $id);
        return $this->db->get($this->table)->row();
    }

    /**
     * Delete the details of a faq by ID.
     *
     * @param string $id   The ID of the faq to be deleted.
     * @return bool        True if the delete operation is successful, otherwise false.
     */
    public function deletefaq($id)
    {
        $this->db->where("id", $id);
        $query = $this->db->delete($this->table);
        return $query;
    }
    
    public function deleteCollegeFaq($id)
    {
        $this->db->where("id", $id);
        $query = $this->db->delete('college_faq');
        return $query;
    }

    /**
     * Get faq by catagory id.
     *
     * @param string $collegeId The collegeid to retrieve faq list.
     * @return object The details of the faq as an object.
     */
    public function allFaqByCatagory($Id)
    {
        $this->db->select("*");
        $this->db->from($this->table . " f");
        $this->db->where('categoryid', $Id);
        $query = $this->db->get()->result();
        return $query;
    }

    /**
     * Update the faq ID in college table.
     *
     * @param string $id   The ID of the faq to be deleted.
     * @return bool        True if the delete operation is successful, otherwise false.
     */
    public function updateFaq($data,$categoryId)
    {
        // $this->db->where("id", $collegeId);
        // $data = array(
		// 	'collegeid' => $collegeId,
        //     'faq_ids' => $faqId,
		// 	'faq_type' => $faq_type
        // );
        $this->db->where("faq_type", $categoryId);
        $query = $this->db->update('college_faq', $data);
        return $query;
        
    }

    public function saveFaq($details)
    {
        $query = $this->db->insert('college_faq', $details);
        return $this->db->insert_id();

    }

    public function hasDataForCategory($collegeId,$categoryId)
    {
        $this->db->select("*");
        $this->db->from("college_faq");
        $this->db->where('collegeid', $collegeId);
        $this->db->where('faq_type', $categoryId);
        $query = $this->db->get()->num_rows();
        return $query;
    }
}
