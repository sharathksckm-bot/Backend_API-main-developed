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
    

    public function getlistofCertificate($courseCatId)
    {
        $this->db->select('id,name,image');
        $this->db->from($this->table);
		$this->db->where('category',$courseCatId);
        $this->db->limit(10);
        $this->db->order_by('created_date', 'DESC');

        return $this->db->get()->result();

    }

    public function getCertificationDatabyId($certificateId)
    {
        $this->db->select('c.*, GROUP_CONCAT(ce.title) as collegename'); 
        $this->db->where("c.id", $certificateId);
        $this->db->join('college ce', "FIND_IN_SET(ce.id, c.college)", 'left'); 
        return $this->db->get($this->table . " c")->row();
    }
}