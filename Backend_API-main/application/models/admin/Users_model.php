<?php 
/**
 * User Model
 *
 * @category   Models
 * @package    Admin
 * @subpackage User
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    09 JAN 2024
 *
 * Class Users_model handles all user-related operations.
 */
defined("BASEPATH") or exit("No direct script access allowed");

class Users_model extends CI_Model
{
    private $table = "users";

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get the count of all users.
     *
     * @return int The count of users.
     */
    public function getUsersCount()
    {
        $this->db->select("COUNT(*) as count");
        return $this->db->get($this->table)->row()->count;
    }

    /**
     * Get the list of all users with user type and user status.
     *
     * @return array The list of all users with additional information.
     */
    public function getUserList()
    {
        $this->db->select("u.*, s.name as status, t.name as type");
        $this->db->from($this->table . " u");
        $this->db->join("user_type t", "t.id = u.user_type", "left");
        $this->db->join("user_status s", "s.id = u.user_status", "left");
        return $this->db->get()->result_array();
    }
    


    /**
     * Insert details for all users into the database.
     *	
     * @param array $data The data to be inserted.
     * @return bool True if data insertion is successful, otherwise false.
     */
    public function insertUserDetails($data)
    {
        $query = $this->db->insert($this->table, $data);
        return $query;
    }

    /**
     * Check if an email address exists for any particular user.
     *
     * @param string $email The email address to check.
     * @return int The count of users with the specified email address.
     */
    public function checkEmailExists($email)
    {
        $this->db->where("email", $email);
        $query = $this->db->get($this->table);
        return $query->num_rows();
    }

    /**
     * Get the details of a user by ID.
     *
     * @param string $id The ID to retrieve user details.
     * @return object The details of the user as an object.
     */
    public function getUserDetailsById($id)
    {
        $this->db->where("id", $id);
        return $this->db->get($this->table)->row();
    }

    /**
     * Update the details of a user by ID.
     *
     * @param string $id   The ID of the user to be updated.
     * @param array  $data An associative array containing the data to be updated.
     * @return bool        True if the update operation is successful, otherwise false.
     */
    public function updateUserDetails($id, $data)
    {
        $this->db->where("id", $id);
        $query = $this->db->update($this->table, $data);
        return $query;
    }

    /**
     * Delete the details of a user by ID.
     *
     * @param string $id   The ID of the user to be deleted.
     * @return bool        True if the delete operation is successful, otherwise false.
     */
    public function deleteUserDetails($id)
    {
        $this->db->where("id", $id);
        $query = $this->db->delete($this->table);
        return $query;
    }

    /**
     * Count all users in the table.
     *
     * @return int The total number of users.
     */
    public function countAllUsers()
    {
        return $this->db->count_all($this->table);
    }

    /**
     * Count filtered users based on the search term.
     *
     * @param string $search The search term.
     * @return int The number of filtered users.
     */
    public function countFilteredUsers($search)
    {
        $this->db->like('f_name', $search);
        $this->db->or_like('l_name', $search);
        $this->db->or_like('email', $search);
        // Add more conditions as needed
        return $this->db->get($this->table)->num_rows();
    }

    /**
     * Get all users with filtering, ordering, and pagination.
     *
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated users.
     */

    public function getAllUsers($start, $limit, $order, $dir)
    {
        $this->db->select("u.*, s.name as status, t.name as type");
        $this->db->from($this->table . " u");
        $this->db->join("user_type t", "t.id = u.user_type", "left");
        $this->db->join("user_status s", "s.id = u.user_status", "left");

        $this->db->order_by($order, $dir);
        $this->db->limit($limit, $start);

        return $this->db->get()->result();
    }

    /**
     * Get filtered users with user type and user status.
     *
     * @param string $search The search term.
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated users with additional information.
     */

    public function getFilteredUsers($search, $start, $limit, $order, $dir)
    {
        $this->db->select("u.*, s.name as status, t.name as type");
        $this->db->from($this->table . " u");
        $this->db->join("user_type t", "t.id = u.user_type", "left");
        $this->db->join("user_status s", "s.id = u.user_status", "left");

        $this->db->group_start(); 
        $this->db->like('f_name', $search);
        $this->db->or_like('l_name', $search);
        $this->db->or_like('email', $search);
        $this->db->group_end(); 

        $this->db->order_by($order, $dir);
        $this->db->limit($limit, $start);

        return $this->db->get()->result();
    }
}
