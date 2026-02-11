<?php
/**
 * Blog Model
 *
 * @category   Models
 * @package    Admin
 * @subpackage Blog
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    09 JAN 2024
 * 
 * Class Blogs_model handles all blog-related operations.
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Blogs_model extends CI_Model {

    private $table = 'blog';

    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Get the count of all blogs.
     *
     * @return int The count of blogs.
     */
    public function getBlogCount() {
        $this->db->select('COUNT(*) as count');
        return $this->db->get($this->table)->row()->count;
    }
}
