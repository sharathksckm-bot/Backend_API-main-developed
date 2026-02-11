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
				$this->db->from('predict_admission');
				return $this->db->count_all_results();
		}

  public function countFilteredPredictAdmission($search)
{
    $this->db->join('users u', 'u.id = pa.attended_by', 'left');
    $this->db->join('courses c', 'c.id = pa.course', 'left');
    $this->db->join('exams e', 'e.id = pa.entrance_exam', 'left');
    $this->db->group_start();
    $this->db->like('pa.student_name', $search);
    $this->db->or_like('pa.mobile_no', $search);
    $this->db->or_like('pa.email', $search);
    $this->db->or_like('pa.category', $search);
    $this->db->or_like('pa.college', $search);
    $this->db->or_like('pa.course', $search);
    $this->db->or_like('pa.entrance_exam', $search);
    $this->db->or_like('pa.rank', $search);
    $this->db->or_like('pa.score', $search);
    $this->db->group_end();
    
    $this->db->or_like('rank', $search);
    $this->db->or_like('score', $search);

    $query = $this->db->get('predict_admission pa');
    return $query->num_rows();
}



public function getFilteredPredictAdmission($search, $start, $limit, $orderColumn, $orderDir)
{
    $this->db->select('pa.*, CONCAT(u.f_name, " ", u.l_name) as attended_by_name,c.name as coursename,e.title as examname,ce.title as collegename,ct.name as catname');
    $this->db->join('users u', 'u.id = pa.attended_by', 'left');
    $this->db->join('courses c', 'c.id = pa.course', 'left');
    $this->db->join('exams e', 'e.id = pa.entrance_exam', 'left');
        $this->db->join('college ce', 'ce.id = pa.college', 'left');
        $this->db->join('academic_categories ct', 'ct.category_id = pa.category', 'left');

    $this->db->group_start();
    $this->db->like('pa.student_name', $search);
    $this->db->or_like('pa.mobile_no', $search);
    $this->db->or_like('pa.email', $search);
    $this->db->or_like('pa.category', $search);
    $this->db->or_like('pa.college', $search);
    $this->db->or_like('pa.course', $search);
    $this->db->or_like('pa.entrance_exam', $search);
    $this->db->or_like('pa.rank', $search);
    $this->db->or_like('pa.score', $search);
    $this->db->group_end();

    $this->db->limit($limit, $start);
    $this->db->order_by($orderColumn, $orderDir);

    $query = $this->db->get('predict_admission pa');
    return $query->result_array();
}


public function getAllPredictAdmission($start = 0, $limit = null, $orderColumn = null, $orderDir = 'asc')
{
    $this->db->select('pa.*, CONCAT(u.f_name, " ", u.l_name) as attended_by_name,c.name as coursename,e.title as examname,ce.title as collegename,ct.name as catname');
    if ($limit !== null) {
        $this->db->limit($limit, $start);
    }
    if ($orderColumn !== null && $orderDir !== null) {
        $orderDir = ($orderDir === 'asc' || $orderDir === 'desc') ? $orderDir : 'asc';
        $this->db->order_by($orderColumn, $orderDir);
    }
    $this->db->join('users u', 'u.id = pa.attended_by', 'left');
    $this->db->join('courses c', 'c.id = pa.course', 'left');
    $this->db->join('exams e', 'e.id = pa.entrance_exam', 'left');
    $this->db->join('college ce', 'ce.id = pa.college', 'left');
        $this->db->join('academic_categories ct', 'ct.category_id = pa.category', 'left');

    $query = $this->db->get('predict_admission pa');
    return $query->result_array();
}

public function updateData($enquiryId,$Arr)
{
    $this->db->where("id", $enquiryId);
    $query = $this->db->update('predict_admission', $Arr);
    return $query;
}
public function deleteAdmission($Id,$Arr)
{
    $this->db->where("id", $Id);
    $query = $this->db->update('predict_admission', $Arr);
    return $query;
}

}
