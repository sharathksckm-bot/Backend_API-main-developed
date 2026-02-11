<?php
/**
 * Auth Model
 *
 * @category   Models
 * @package    Admin
 * @subpackage Authentication
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    08 JAN 2024
 * 
 * Class Auth_model handles secure login methods for administrators.
 */
class Auth_model extends CI_Model
{

    /**
     * Database table name for users.
     * 
     * @var string
     */
    private $table = 'users';
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Verify User
     * 
     * Verifies the user credentials against the database.
     *
     * @param string $email    User's email address.
     * @param string $password User's password.
     * @return bool            Returns true if the user is verified, false otherwise.
     */
    public function verifyUser($email, $password) {
		if (empty($email) || empty($password)) {
			return null; 
		}
	
		$this->db->select('u.id as userId,u.f_name as firstname,u.l_name as lastname,u.user_type,u.email as email,u.create_date,u.image, s.name as status, t.name as type');
		$this->db->from('users u');
		$this->db->join('user_type t', 't.id = u.user_type', 'left');
		$this->db->join('user_status s', 's.id = u.user_status', 'left');
		$this->db->where('u.email', $email);
		$this->db->where('u.password', $password); 
		$query = $this->db->get();
		return $query->row_array();
	}
	
}
