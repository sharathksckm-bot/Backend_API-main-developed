<?php

defined('BASEPATH') or exit('No direct script access allowed');


class Common_model extends CI_Model
{
  public function __construct()
  {
    parent::__construct();
    $this->load->database();
  }

  public function get_Blogs($searchCategory = '')
  {
    $this->db->select('*');
    $this->db->from('blog');
    $this->db->where('image IS NOT NULL');
    $this->db->where('image IS NOT NULL');
    $this->db->where('title IS NOT NULL');
    if (!empty($searchCategory)) {
      $this->db->where('categoryid', $searchCategory);
    }
    $this->db->order_by('id', 'desc');
    //$this->db->limit(5);
    $query = $this->db->get();
    if ($query->num_rows() > 0) {
      return $query->result();
    } else {
      return false;
    }
  }

  public function getLatestBlogs()
  {
    $this->db->select('*');
    $this->db->from('blog');
    $this->db->where('t_status', '1');
    $this->db->order_by('created_date', 'DESC');
    $this->db->order_by('id', 'DESC');
    $this->db->limit(10);
    $query = $this->db->get();
    $result = $query->result();
    return $result;
  }


  public function getPopularBlogs()
  {
    $this->db->select('*');
    $this->db->from('blog');
    $this->db->where('t_status', '1');
    $this->db->where('views >', 500);
    $this->db->order_by('views', 'DESC');
    $this->db->order_by('created_date', 'DESC');
    $this->db->order_by('id', 'DESC');
    $this->db->limit(10);
    $query = $this->db->get();
    $result = $query->result();
    return $result;
  }

  public function getBlogsDetails($blogId)
  {
    $this->db->select('b.id as blog_id,b.categoryid,bc.name as category_name,b.title,b.post_url, b.image,b.description,b.post_rate_date,MONTH( post_rate_date) as month,DAY( post_rate_date) as day,YEAR( post_rate_date) as year,SUBSTRING(`description`,1,90) as short_desc');
    $this->db->join('blog_category bc', 'bc.id = b.categoryid', 'left');
    $this->db->where('b.id', $blogId);
    return $this->db->get('blog b')->result();
  }

  public function increment_view($blogId = '')
  {
    if (!empty($blogId)) {
      $this->db->where('id', $blogId);
      $this->db->set('views', 'views+1', FALSE);
      $this->db->set('post_rate_date', date('Y-m-d H:i:s'));
      $this->db->update('blog');
    }
  }
  public function relatedBlogs($id, $blog_id)
  {

    $list = array();
    $Array = explode(',', $id);
    foreach ($Array as $categoryid) {
      if (count($list) < 5) {
        $this->db->select('id,title,post_url,categoryid,image,post_rate_date');
        $this->db->where("FIND_IN_SET('$categoryid',categoryid) !=", 0);
        $this->db->where('id!= ', $blog_id);
        $this->db->order_by('id', 'DESC');
        $this->db->where('t_status', '1');
        $this->db->limit(5);
        $data = $this->db->get('blog')->result();
        if (!empty($data)) {
          foreach ($data as $value) {
            if (!in_array($value, $list, true)) {
              $list[] = $value;
            }
          }
        }
      }
    }
    return $list;
  }

  public function getBlogCategory()
  {
    $this->db->select('id,name,post_url');
    $this->db->where('status', '1');
    return $this->db->get('blog_category')->result_array();
  }

  public function getSubCategoryList($collegeId)
  {
    $this->db->select('sc.id ,sc.name, COUNT(c.sub_category) as totalCount');
    $this->db->from('college_course cc');
    $this->db->join('courses c', 'c.id = cc.courseid', 'left');
    $this->db->join('sub_category sc', 'sc.id = c.sub_category', 'left');
    $this->db->where('cc.collegeid', $collegeId);
    $this->db->where('c.sub_category IS NOT NULL', null, false);
    $this->db->group_by('c.sub_category');
    $query = $this->db->get();
    $result = $query->result();
    return $result;
  }
  public function getFAQ()
  {
    $this->db->select('*');
    $this->db->from('faq c');
    $this->db->limit(5);
    $query = $this->db->get();
    $result = $query->result_array();
    //echo $this->db->last_query();      exit;
    return $result;
  }


  public function getAcademicCategory($collegeId)
  {
    $this->db->select('ac.category_id as id, ac.name, COUNT(c.academic_category) as totalCount');
    $this->db->from('college_course cc');
    $this->db->join('courses c', 'c.id = cc.courseid', 'left');
    $this->db->join('academic_categories ac', 'ac.category_id = c.academic_category', 'left');
    $this->db->where('cc.collegeid', $collegeId);
    $this->db->where('c.academic_category IS NOT NULL', null, false);
    $this->db->group_by('c.academic_category');
    $query = $this->db->get();
    $result = $query->result();
    return $result;
  }


  public function getExamAccepted($collegeId)
  {
    $this->db->select('e.title, e.id, COUNT(cc.entrance_exams) as totalCount');
    $this->db->from('exams e');
    $this->db->join('college_course cc', 'FIND_IN_SET(e.id, cc.entrance_exams) > 0', 'left');
    $this->db->where('cc.collegeid', $collegeId);
    $this->db->group_by('e.id');

    $query = $this->db->get();
    $result = $query->result();

    return $result;
  }

  public function getQAofCollege($collegeId, $limit, $start)
  {
    $this->db->select('q.question_id, q.question, q.date,q.views, q.course_type, ac.name AS course_typeName, q.course_id, c.name AS courseName, q.user_id, CONCAT(u.f_name, " ", u.l_name) AS fullname,u.image, q.college_id');
    $this->db->from('question q');
    $this->db->join('academic_categories ac', 'ac.category_id = q.course_type', 'left');
    $this->db->join('courses c', 'c.id = q.course_id', 'left');
    $this->db->join('users u', 'u.id = q.user_id', 'left');
    $this->db->where('q.college_id', $collegeId);
    $this->db->where('EXISTS (SELECT 1 FROM answer a WHERE a.question_id = q.question_id)', NULL, FALSE);
    $this->db->set('q.views', 'q.views+1', FALSE);
    $this->db->order_by('q.date', 'DESC');
    $this->db->limit($limit, $start);
    $query = $this->db->get();
    return $query->result_array();
  }

  public function getQADataByQueId($QueId)
  {
    $this->db->select('q.question_id, q.question, q.date,q.views, q.course_type, ac.name AS course_typeName, q.course_id, c.name AS courseName, q.user_id, CONCAT(u.f_name, " ", u.l_name) AS fullname,u.image, q.college_id');
    $this->db->from('question q');
    $this->db->join('academic_categories ac', 'ac.category_id = q.course_type', 'left');
    $this->db->join('courses c', 'c.id = q.course_id', 'left');
    $this->db->join('users u', 'u.id = q.user_id', 'left');
    //$this->db->where('q.college_id', $collegeId);
    $this->db->where('q.question_id', $QueId);
    $this->db->where('EXISTS (SELECT 1 FROM answer a WHERE a.question_id = q.question_id)', NULL, FALSE);
    $this->db->set('q.views', 'q.views+1', FALSE);
    $this->db->order_by('q.date', 'DESC');
    // $this->db->limit($limit, $start);
    $query = $this->db->get();
    return $query->result_array();
  }

  public function relatedQue($QueId, $collegeId)
  {
    $this->db->select('q.question_id, q.question, q.date,q.views,count(a.answer) as answerCount');
    $this->db->from('question q');
    $this->db->where('q.question_id !=', $QueId);
    $this->db->where('q.college_id', $collegeId);
    $this->db->join('answer a', 'a.question_id = q.question_id', 'left');
    $query = $this->db->get();
    return $query->result_array();
  }

  public function getQAFollowCount($questionId)
  {
    $this->db->select('COUNT(question_id) as QuestionFollowCount');
    $this->db->where('question_id', 7);
    $query = $this->db->get('question_follow');
    $result = $query->row();
    $questionFollowCount = $result->QuestionFollowCount;
    return $result;
  }

  public function getAnsweres($questionId)
  {
    $this->db->select('a.*, CONCAT(u.f_name, " ", u.l_name) as answerby,u.image');
    $this->db->from('answer a');
    $this->db->join('users u', 'u.id = a.user_id', 'left');
    $this->db->where('a.question_id', $questionId);
    $query = $this->db->get();
    $result = $query->result_array();
    return $result;
  }

  public function getAnsComments($answersid)
  {
    $this->db->select('ac.*, CONCAT(u.f_name, " ", u.l_name) as commentby,u.image');
    $this->db->from('answer_comment ac');
    $this->db->join('users u', 'u.id = ac.user_id', 'left');
    $this->db->where('ac.answer_id', $answersid);
    $query = $this->db->get();
    $result = $query->result_array();
    return $result;
  }

  public function getUnAnsweredQueofCollege($collegeId, $limit, $start)
  {
    $this->db->select('q.question_id, q.question, q.date, q.views, q.course_type, ac.name AS course_typeName, q.course_id, c.name AS courseName, q.user_id, CONCAT(u.f_name, " ", u.l_name) AS fullname,u.image, q.college_id');
    $this->db->from('question q');
    $this->db->join('academic_categories ac', 'ac.category_id = q.course_type', 'left');
    $this->db->join('courses c', 'c.id = q.course_id', 'left');
    $this->db->join('users u', 'u.id = q.user_id', 'left');
    $this->db->where('q.college_id', 2829);
    $this->db->where('NOT EXISTS (SELECT 1 FROM answer a WHERE a.question_id = q.question_id)', NULL, FALSE);
    $this->db->order_by('q.date', 'DESC');
    $this->db->limit(10);

    $query = $this->db->get();

    //echo $this->db->last_query();exit;
    return $query->result_array();
  }


  public function postQuestion($data)
  {
    $this->db->insert('question', $data);
    return $this->db->insert_id();
  }

  public function postAnswere($data)
  {
    $this->db->insert('answer', $data);
    return $this->db->insert_id();
  }

  public function updateQuestionRepStatus($questionId)
  {
    $this->db->where('question_id', $questionId);
    $this->db->set('replied', 'replied+1', FALSE);
    $this->db->update('question');
  }

  public function postAnsComment($data)
  {
    $this->db->insert('answer_comment', $data);
    return $this->db->insert_id();
  }

  public function followQuestion($data)
  {
    $this->db->insert('question_follow', $data);
    return $this->db->insert_id();
  }
  public function UnfollowQuestion($question_id, $user_id)
  {
    $this->db->where('user_id', $user_id);
    $this->db->where('question_id', $question_id);
    $this->db->delete('question_follow');
  }

  public function getAns($answer_id)
  {
    $this->db->select('*');
    $this->db->from('answer');
    $this->db->where('answer_id', $answer_id);
    $query = $this->db->get();
    return $query->result_array();
  }

  public function voteAnswer($data, $answer_id)
  {
    $this->db->where('answer_id', $answer_id);
    $result = $this->db->update('answer', $data);
    return $result;
  }

  public function saveEnquiry($arr)
  {
    $this->db->insert('inquiry', $arr);
    return $this->db->insert_id();
  }

  public function addLog($logArr, $tableName)
  {
    $this->db->insert($tableName, $logArr);
    return $this->db->insert_id();
  }
  public function getQueAnsAboutAdmissions($collegeId)
  {
    $this->db->select('q.question_id, q.question, q.date,q.views, q.course_type, ac.name AS course_typeName, q.course_id, c.name AS courseName, q.user_id, CONCAT(u.f_name, " ", u.l_name) AS question_asked_by,u.image, q.college_id');
    $this->db->from('question q');
    $this->db->join('academic_categories ac', 'ac.category_id = q.course_type', 'left');
    $this->db->join('courses c', 'c.id = q.course_id', 'left');
    $this->db->join('users u', 'u.id = q.user_id', 'left');
    $this->db->where('q.college_id', $collegeId);
    $this->db->where('EXISTS (SELECT 1 FROM answer a WHERE a.question_id = q.question_id)', NULL, FALSE);
    $this->db->group_start();
    $this->db->like('q.question', 'admission');
    $this->db->or_like('q.question', 'apply');
    $this->db->or_like('q.question', 'application');
    $this->db->or_like('q.question', 'placement');
    $this->db->or_like('q.question', 'join');
    $this->db->or_like('q.question', 'admission process');
    $this->db->group_end();
    $this->db->set('q.views', 'q.views+1', FALSE);
    $this->db->order_by('q.date', 'DESC');
    $this->db->order_by('RAND()');
    $this->db->limit(5);
    $query2 = $this->db->get();
    $result2 = $query2->result_array();
    return $result2;
  }

  public function getTotalQuestionForCollege($collegeId)
  {
    $this->db->select('(SELECT COUNT(*)  FROM question WHERE college_id = ' . $collegeId . ') AS TOTALQUESTION', FALSE);
    $query1 = $this->db->get();
    $result1 = $query1->row();
    return $result1;
  }

  function getBrochure($collegeid)
  {
    $this->db->where('collegeid', $collegeid);
    return $this->db->get('brochures')->result_array();
  }

  public function addUserActivity($Arr)
  {
    $this->db->insert('user_activity', $Arr);
    return $this->db->insert_id();
  }

  public function  getClgIdByQues($Ques)
  {
    $this->db->select('*');
    $this->db->from('question');
    $this->db->where('question_id', $Ques);
    $query = $this->db->get();
    return $query->result_array();
  }

  //for time beign 

  public function updateClgReport($college_id, $ClgRepArr)
  {
    if (isset($ClgRepArr['no_of_articles_linked'])) {
      $this->db->set('no_of_articles_linked', 'no_of_articles_linked + ' . $ClgRepArr['no_of_articles_linked'], FALSE);
    }
    if (isset($ClgRepArr['no_of_brochures_download'])) {
      $this->db->set('no_of_brochures_download', 'no_of_brochures_download + ' . $ClgRepArr['no_of_brochures_download'], FALSE);
    }
    if (isset($ClgRepArr['no_of_application_submitted'])) {
      $this->db->set('no_of_application_submitted', 'no_of_application_submitted + ' . $ClgRepArr['no_of_application_submitted'], FALSE);
    }
    if (isset($ClgRepArr['no_of_que_asked'])) {
      $this->db->set('no_of_que_asked', 'no_of_que_asked + ' . $ClgRepArr['no_of_que_asked'], FALSE);
    }
    if (isset($ClgRepArr['no_of_answeres'])) {
      $this->db->set('no_of_answeres', 'no_of_answeres + ' . $ClgRepArr['no_of_answeres'], FALSE);
    }
    if (isset($ClgRepArr['no_of_review'])) {

      $this->db->set('no_of_review', 'no_of_review + ' . $ClgRepArr['no_of_review'], FALSE);
    }
    $this->db->where('college', $college_id);
    $query = $this->db->update('college_report');
    return $query;
  }

  public function saveClgReport($ClgRepArr)
  {
    $this->db->set('no_of_articles_linked', 'no_of_articles_linked + ' . $ClgRepArr['no_of_articles_linked'], FALSE);
    $this->db->set('no_of_brochures_download', 'no_of_brochures_download + ' . $ClgRepArr['no_of_brochures_download'], FALSE);
    $this->db->set('no_of_application_submitted', 'no_of_application_submitted + ' . $ClgRepArr['no_of_application_submitted'], FALSE);
    $this->db->set('no_of_que_asked', 'no_of_que_asked + ' . $ClgRepArr['no_of_que_asked'], FALSE);
    $this->db->set('no_of_answeres', 'no_of_answeres + ' . $ClgRepArr['no_of_answeres'], FALSE);
    $this->db->set('no_of_review', 'no_of_review + ' . $ClgRepArr['no_of_review'], FALSE);

    $this->db->set('college', $ClgRepArr['college'], FALSE);
    $this->db->insert('college_report');
    $collegeRep['college_report_id'] = $this->db->insert_id();
    return $collegeRep;
  }

  public function checkcollegeReport($college_id)
  {
    $this->db->select("*");
    $this->db->from("college_report");
    $this->db->where("college", $college_id);
    $query = $this->db->get();
    // echo $this->db->last_query();exit;
    $result = $query->num_rows();
    return $result;
  }

  public function getCollegeReport()
  {
    $this->db->select("cr.*,c.title as collegename,ci.city,s.statename");
    $this->db->from("college_report cr");
    $this->db->join('college c', 'c.id = cr.college', 'left');
    $this->db->join('city ci', 'ci.id = c.cityid', 'left');
    $this->db->join('state s', 's.id = c.stateid', 'left');

    $query = $this->db->get();
    $result = $query->result();
    return $result;
  }

  public function saveCourseApplication($arr)
  {
    $this->db->insert('Course_Application', $arr);
    return $this->db->insert_id();
  }
  public function savPredictAdmission($arr)
  {
    $this->db->insert('predict_admission', $arr);
    return $this->db->insert_id();
  }

  public function getTrendingSpecilization()
  {
    $this->db->select('keyword');
    $this->db->from('courses');
    $this->db->where('keyword IS NOT NULL');
    $this->db->where('keyword !=', '');
    $this->db->group_by('keyword');
    $this->db->order_by('keyword', 'ASC');
    $query = $this->db->get();
    $result = $query->result();
    return $result;
  }
	public function getQueAnsAboutScholarships($collegeId)
    {
        $this->db->select('q.question_id, q.question, q.date,q.views, q.course_type, ac.name AS course_typeName, q.course_id, c.name AS courseName, q.user_id, CONCAT(u.f_name, " ", u.l_name) AS question_asked_by,u.image, q.college_id');
        $this->db->from('question q');
        $this->db->join('academic_categories ac', 'ac.category_id = q.course_type', 'left');
        $this->db->join('courses c', 'c.id = q.course_id', 'left');
        $this->db->join('users u', 'u.id = q.user_id', 'left');
        $this->db->where('q.college_id', $collegeId);
        // $this->db->where('q.course_id',$courseid);
        $this->db->where('EXISTS (SELECT 1 FROM answer a WHERE a.question_id = q.question_id)', NULL, FALSE);
        $this->db->group_start();
        $this->db->like('q.question', 'scholarship');
        $this->db->or_like('q.question', 'Eligibility');
        $this->db->or_like('q.question', 'application');
        $this->db->or_like('q.question', 'Deadline');
        $this->db->or_like('q.question', 'Financial');
        $this->db->or_like('q.question', 'Qualifications');
        $this->db->or_like('q.question', 'Criteria');
        $this->db->or_like('q.question', 'Renewal');
        $this->db->or_like('q.question', 'Form');

        $this->db->group_end();
        $this->db->set('q.views', 'q.views+1', FALSE);
        $this->db->order_by('q.date','DESC');
        $this->db->order_by('RAND()');
        $this->db->limit(5);
        $query2 = $this->db->get();
        $result2 = $query2->result_array();
        return $result2;

    }
}
