<?php
/**
 * PredictAdmission Model
 *
 * @category   Models
 * @package    Admin
 * @subpackage PredictAdmission
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    26 JAN 2024
 * 
 * Class PredictAdmission_model handles all PredictAdmission-related operations.
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class PredictAdmission_model extends CI_Model {

    private $table = 'predict_admission';

    public function __construct() {
        parent::__construct();
    }

	public function countAllPredictAdmission()
  {
    $this->db->select('id,student_name,mobile_no,email,category,college,course,entrance_exam,rank,score,CreatedDate');
    $this->db->from('predict_admission');

    $query = $this->db->get();

    if ($query->num_rows() > 0) {
      return $query->result();
    } else {
      return array();
    }
  }

  public function countFilteredPredictAdmission($search)
  {
    $this->db->like('id', $search);
    $this->db->like('student_name', $search);
    $this->db->like('mobile_no', $search);
    $this->db->like('email', $search);
    $this->db->like('category', $search);
    $this->db->like('college', $search);
    $this->db->like('course', $search);
    $this->db->like('entrance_exam', $search);
    $this->db->like('rank', $search);
    $this->db->like('score', $search);
    $this->db->like('CreatedDate', $search);

    $query = $this->db->get('predict_admission');
    return $query->num_rows();
  }


  public function getFilteredPredictAdmission($search, $start, $limit, $orderColumn, $orderDir)
  {
    $this->db->like('id', $search);
    $this->db->like('student_name', $search);
    $this->db->like('mobile_no', $search);
    $this->db->like('email', $search);
    $this->db->like('category', $search);
    $this->db->like('college', $search);
    $this->db->like('course', $search);
    $this->db->like('entrance_exam', $search);
    $this->db->like('rank', $search);
    $this->db->like('score', $search);
    $this->db->like('CreatedDate', $search);

    $this->db->limit($limit, $start);
    $this->db->order_by($orderColumn, $orderDir);

    $query = $this->db->get('predict_admission');
    return $query->result();
  }
}
