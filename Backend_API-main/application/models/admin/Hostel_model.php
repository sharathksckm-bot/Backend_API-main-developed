<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Hostel_model extends CI_Model {

    private $table = 'hostelpg';

    public function __construct() {
        parent::__construct();
    }

    /**
     * Count all entries in the hostel table.
     *
     * @param int $userId User ID.
     * @param int $userType User Type.
     * @return int The total number of entries.
     */
    public function countAll($userId, $userType) {
       // echo "ttt";exit;
        if ($userType == 13) {
            $this->db->where('created_by', $userId);
        }
        return $this->db->count_all_results($this->table);
    }

    /**
     * Count filtered entries based on the search term.
     */
    public function countFiltered($search, $userId, $userType) {
        if ($userType == 13 || $userType == 14) {
            $this->db->where('created_by', $userId);
        }
        $this->db->like('title', $search);
        return $this->db->count_all_results($this->table);
    }

    /**
     * Get filtered entries with pagination.
     */
    public function getFiltered($search, $start, $limit, $order, $dir, $userId, $userType) {
        $this->db->from($this->table);
        if ($userType == 13 || $userType == 14) {
            $this->db->where('created_by', $userId);
        }
        $this->db->like('title', $search);
        $this->db->order_by($order, $dir);
        $this->db->limit($limit, $start);
        return $this->db->get()->result();
    }
    
    /* public function getAll($start, $limit, $order, $dir, $userId, $userType) {
        $this->db->from($this->table);
        if ($userType == 13 || $userType == 14) {
            $this->db->where('created_by', $userId);
        }
      //  $this->db->like('title', $search);
        $this->db->order_by($order, $dir);
        $this->db->limit($limit, $start);
        return $this->db->get()->result();
    }*/
    
public function getAll($start, $limit, $order, $dir, $userId, $userType) {
   // echo "ttt";exit;
    $this->db->select('hp.*, c.title as college_name');
    $this->db->from('hostelpg hp');
       $this->db->join('college c', 'hp.collegeid = c.id');
    if ($userType == 13 || $userType == 14) {
        $this->db->where('hp.created_by', $userId);
    }
   //  echo $this->db->last_query();exit(); 
    $this->db->order_by($order, $dir);
    $this->db->limit($limit, $start);
    return $this->db->get()->result();
}

    /**
     * Insert hostel details into the database.
     */
    public function insertHostelDetails($data) {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    /**
     * Update the details of a hostel by ID.
     */
    public function updateHostelDetails($id, $data) {
        $this->db->where("id", $id);
        return $this->db->update($this->table, $data);
    }

    /**
     * Check if a hostel exists with the same name during update.
     */
    public function chkWhileUpdate($id, $name) {
        $this->db->where("name", $name);
        $this->db->where("id !=", $id);
        return $this->db->count_all_results($this->table) > 0;
    }

    /**
     * Get the details of a hostel by ID.
     */
   /* public function getHostelDetailsById($id) {
        return $this->db->where("id", $id)->get($this->table)->row();
    }*/
    
    public function getHostelDetailsById($id) {
    $this->db->select('hp.*, c.title as college_name');
    $this->db->from('hostelpg hp');
    $this->db->join('college c', 'hp.collegeid = c.id');
    $this->db->where('hp.id', $id); 
    $query = $this->db->get(); 
    return $query->row(); 
}

    /**
     * Delete the details of a hostel by ID.
     */
    public function deleteHostel($id) {
        $this->db->where("id", $id);
        return $this->db->delete($this->table);
    }

    /**
     * Check if a hostel exists by name.
     */
    public function chkIfExists($name) {
        $this->db->where('name', $name);
        return $this->db->count_all_results($this->table) > 0;
    }
    
    public function getHostelData($id){
         $this->db->select('hp.*,c.title as college_name');
    $this->db->from('hostelpg hp');
    $this->db->join('college c', 'hp.collegeid = c.id');
    $this->db->where('hp.id', $id); 
    $query = $this->db->get(); 
    return $query->row();
    }
}
