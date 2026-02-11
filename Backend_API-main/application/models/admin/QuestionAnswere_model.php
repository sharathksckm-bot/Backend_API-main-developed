<?php
/**
 * QuestionAnswere Model
 *
 * @category   Models
 * @package    Admin
 * @subpackage QuestionAnswere
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    24 APRIL 2024
 * 
 * Class QuestionAnswere_model handles all Question-Answere related operations.
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class QuestionAnswere_model extends CI_Model {

    private $table = 'question';

    public function __construct() {
        parent::__construct();
    }

    public function countAllQuestion()
    {
        $query = $this->db->get($this->table);
    	return $query->num_rows();
    }

    public function countFilteredQuestion($search){
        $this->db->from('question q');
        $this->db->join('college c', 'c.id = q.college_id', 'left');
        $this->db->join('course cc', 'cc.id = q.course_id', 'left');
        $this->db->where('is_deleted', 0);
        $this->db->group_start(); 
        $this->db->like('q.question', $search);
        $this->db->or_like('c.title', $search);
        $this->db->or_like('cc.name', $search);
        $this->db->group_end(); 		
        return $this->db->get()->num_rows();
    }

    public function getFilteredQuestion($search, $start, $limit, $orderColumn, $orderDir)
    {
        $this->db->select('c.id as college_id,c.title as collegename,q.question_id,q.question,CONCAT(u1.f_name," ", u1.l_name) question_asked_by,q.replied,q.views as question_view,ac.name as course_type,cs.name as coursename');
        $this->db->from('question q');
        $this->db->join('college c', 'c.id = q.college_id', 'left');
        $this->db->join('academic_categories ac', 'ac.category_id = q.course_type', 'left');
        $this->db->join('courses cs', 'cs.id = q.course_id', 'left');
        $this->db->join('users u1', 'u1.id = q.user_id', 'left');
        $this->db->where('q.is_deleted', 0);

        $this->db->group_start(); 
        $this->db->like('c.title', $search);
        $this->db->or_like('ac.name', $search);
        $this->db->or_like('cs.name', $search);
        $this->db->group_end(); 
        $this->db->order_by('q.question_id', $orderDir);
		$this->db->limit($limit, $start);
		return $this->db->get()->result();
    }

    public function getAllKQuestion($start, $limit, $orderColumn, $orderDir)
    {
        $this->db->select('c.id as college_id,c.title as collegename,q.question_id,q.question,CONCAT(u1.f_name," ", u1.l_name) question_asked_by,q.replied,q.views as question_view,ac.name as course_type,cs.name as coursename');
        $this->db->from('question q');
        $this->db->join('college c', 'c.id = q.college_id', 'left');
        $this->db->join('academic_categories ac', 'ac.category_id = q.course_type', 'left');
        $this->db->join('courses cs', 'cs.id = q.course_id', 'left');
        $this->db->join('users u1', 'u1.id = q.user_id', 'left');
        $this->db->where('q.is_deleted', 0);
        $this->db->order_by('q.question_id', $orderDir);
		 $this->db->limit($limit, $start);
		 return $this->db->get()->result();
    }

    public function deleteQuestion($questionId)
    {
        $data = ['is_deleted'=>1];
         $this->db->where("question_id", $questionId);
        $query = $this->db->update('question', $data);
        return $query;
    }


//     SELECT c.title as collegename,q.question_id,q.question,CONCAT(u1.f_name,' ', u1.l_name) question_asked_by,q.replied,q.views as question_view,a.answer_id,CONCAT(u.f_name,' ', u.l_name) answere_by,a.answer,ac.name as course_type,cs.name as coursename
// FROM `question` q
// LEFT JOIN answer a ON a.question_id = q.question_id
// LEFT JOIN college c ON c.id = q.college_id
// LEFT JOIN academic_categories ac ON ac.category_id = q.course_type
// LEFT JOIN courses cs ON cs.id = q.course_id
// LEFT JOIN users u ON u.id = a.user_id
// LEFT JOIN users u1 ON u1.id = q.user_id

// WHERE q.is_deleted = 0
}