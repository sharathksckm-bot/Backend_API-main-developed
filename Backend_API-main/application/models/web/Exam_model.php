<?php

class Exam_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function countAllExam()
    {
        $this->db->select("count(*) as exam_count");
        $this->db->from("exams e");
        $this->db->where("e.status", "1");

        $result = $this->db->get()->result_array();

        if (!empty($result)) {
            return $result[0]["exam_count"];
        } else {
            return 0;
        }
    }

    public function getExamNotificationForClg($collegeid){
        $this->db->select('e.id as examId, e.title as examName, b.title as notification, b.image, b.datesubmit');
        $this->db->from('blog b');
        // $this->db->join('college_course cc', 'FIND_IN_SET(e.id, cc.entrance_exams) > 0', 'left');
        $this->db->join('exams e', 'e.id = b.exam_id', 'left');
        $this->db->where('b.college_id', $collegeid);
        $this->db->where('b.categoryid', 4);
        $this->db->group_by('e.id');
        $query = $this->db->get();
        // echo $this->db->last_query();exit;
        $result = $query->result();
        return $result;

    }

  public function getExamList($value)
  {
        $this->db->select("e.id,e.title,e.slug,g.image,e.questionpaper,e.preparation,e.syllabus,e.notification");
        $this->db->from("exams e");
        $this->db->join('gallery g', 'g.postid = e.id', 'left');
        if(!empty($value))
        {
            $this->db->like('e.title', $value);

        }
        $this->db->where('g.type', 'exams');
        $this->db->where("e.status", "1");
        $result = $this->db->get()->result();
        return $result;

  }


  public function getExamDetails($examId)
  {
    $this->db->select('g.image,e.questionpaper,e.id as eid,e.categoryid, e.title,e.description,e.criteria,e.process,SUBSTRING(`description`, 1,10) as short_exam_desc,e.pattern,e.slug,GROUP_CONCAT(c.catname) AS catname');
    $this->db->where('e.id', $examId);
    //$this->db->where('c.type','exams');
   // $this->db->where('g.type', 'exams');
    $this->db->where("e.status", "1");
    $this->db->join('category AS c', 'FIND_IN_SET(c.id, e.categoryid)', 'left');

    // $this->db->join('category c', 'c.id = e.categoryid','left');
    $this->db->join('gallery g', 'g.postid = e.id', 'left');

    //return $data=$this->db->get('exams e')->result();
    $query = $this->db->get('exams e');
        ///echo $this->db->last_query();exit;
        $result = $query->result();
        return $result;
  }


 public function get_question_papers($examId)
  {
    $this->db->select('e.questionpaper');
    $this->db->where('e.id', $examId);
    //$this->db->where('c.type','exams');
   // $this->db->where('g.type', 'exams');
    $this->db->where("e.status", "1");
  

    //return $data=$this->db->get('exams e')->result();
    $query = $this->db->get('exams e');
        ///echo $this->db->last_query();exit;
        $result = $query->result();
        return $result;
  }

  public function increment_view($examId = '') {
    if (!empty($examId)) {
        $this->db->where('id', $examId);
        $this->db->set('views', 'views+1', FALSE);
        $this->db->update('exams');
    }
}
public function listgallary($id)
{
    $this->db->select('image');
    $this->db->where('postid', $id);
    $this->db->where('type','exams');
    return $this->db->get('gallery')->row();
    
}

function relatedExams($categoryid = '')
	{
		$this->db->select('e.title,e.id as eid,e.slug,e.description,c.catname,SUBSTRING(`description`, 1,100 ) as short_exam_desc,g.image');
		if (!empty($categoryid)) {
			$this->db->where('e.categoryid', $categoryid);
		}
		$this->db->where('e.status', '1');
		$this->db->where('g.type', 'exams');
		//$this->db->group_by('e.categoryid');
		$this->db->join('category c', 'c.id = e.categoryid','left');
		$this->db->join('gallery g', 'g.postid = e.id','left');
		$this->db->order_by('e.id','DESC');
		if (!empty($categoryid)) {
			$this->db->limit(10);	
		} else {
			$this->db->limit(5);	
		}
		//echo $this->db->last_query(); 
		return $this->db->get('exams e')->result();
		 

	}
	
public function getDocsData($examId){
    $this->db->select('*');
    $this->db->from('documents');
    $this->db->where('examId',$examId);
$this->db->where('is_deleted',0);
    $query = $this->db->get();
    ///echo $this->db->last_query();exit;
    $result = $query->result_array();
    return $result;
}

public function getAllDocsData(){
        $this->db->select('*');
    $this->db->from('documents');
$this->db->where('is_deleted',0);
    $query = $this->db->get();
   // echo $this->db->last_query();exit;
    $result = $query->result_array();
    return $result;
}


}


?>
