<?php

class Common_model extends CI_Model
{
    private $table = "college";

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }
    public function getFAQ()
    {
        $this->db->select("*");
        $this->db->from("faq c");
        $this->db->limit(5);
        $query = $this->db->get();
        $result = $query->result_array();
        //echo $this->db->last_query();      exit;
        return $result;
    }

    public function getSubCategoryList($collegeId)
    {
        // echo "tttt";exit;
        $this->db->select(
            "sc.id ,sc.name, COUNT(c.sub_category) as totalCount,sc.parent_category as categoryid"
        );
        $this->db->from("college_course cc");
        $this->db->join("courses c", "c.id = cc.courseid", "left");
        $this->db->join("sub_category sc", "sc.id = c.sub_category", "left");
        $this->db->where("cc.collegeid", $collegeId);
        $this->db->where("c.sub_category IS NOT NULL", null, false);
        $this->db->group_by("c.sub_category");
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }

    public function getAcademicCategory($collegeId)
    {
        $this->db->select(
            "ac.category_id as id, ac.name, COUNT(c.academic_category) as totalCount"
        );
        $this->db->from("college_course cc");
        $this->db->join("courses c", "c.id = cc.courseid", "left");
        $this->db->join(
            "academic_categories ac",
            "ac.category_id = c.academic_category",
            "left"
        );
        $this->db->where("cc.collegeid", $collegeId);
        $this->db->where("c.academic_category IS NOT NULL", null, false);
        $this->db->group_by("c.academic_category");
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }

    public function getExamAccepted($collegeId)
    {
        $this->db->select(
            "e.title, e.id, COUNT(cc.entrance_exams) as totalCount"
        );
        $this->db->from("exams e");
        $this->db->join(
            "college_course cc",
            "FIND_IN_SET(e.id, cc.entrance_exams) > 0",
            "left"
        );
        $this->db->where("cc.collegeid", $collegeId);
        $this->db->group_by("e.id");

        $query = $this->db->get();
        $result = $query->result();

        return $result;
    }

    public function getQAofCollege($collegeId, $limit, $start)
    {
        $this->db->select(
            'q.question_id, q.question, q.date,q.views, q.course_type, ac.name AS course_typeName, q.course_id, c.name AS courseName, q.user_id, CONCAT(u.f_name, " ", u.l_name) AS fullname,u.image, q.college_id'
        );
        $this->db->from("question q");
        $this->db->join(
            "academic_categories ac",
            "ac.category_id = q.course_type",
            "left"
        );
        $this->db->join("courses c", "c.id = q.course_id", "left");
        $this->db->join("users u", "u.id = q.user_id", "left");
        $this->db->where("q.college_id", $collegeId);
        $this->db->where(
            "EXISTS (SELECT 1 FROM answer a WHERE a.question_id = q.question_id)",
            null,
            false
        );
        $this->db->set("q.views", "q.views+1", false);
        $this->db->order_by("q.date", "DESC");
        $this->db->limit($limit, $start);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getQADataByQueId($QueId)
    {
        $this->db->select(
            'q.question_id, q.question, q.date,q.views, q.course_type, ac.name AS course_typeName, q.course_id, c.name AS courseName, q.user_id, CONCAT(u.f_name, " ", u.l_name) AS fullname,u.image, q.college_id'
        );
        $this->db->from("question q");
        $this->db->join(
            "academic_categories ac",
            "ac.category_id = q.course_type",
            "left"
        );
        $this->db->join("courses c", "c.id = q.course_id", "left");
        $this->db->join("users u", "u.id = q.user_id", "left");
        //$this->db->where('q.college_id', $collegeId);
        $this->db->where("q.question_id", $QueId);
         $this->db->where("is_deleted", 0);
        // $this->db->where(
        //     "EXISTS (SELECT 1 FROM answer a WHERE a.question_id = q.question_id)",
        //     null,
        //     false
        // );
        $this->db->set("q.views", "q.views+1", false);
        $this->db->order_by("q.date", "DESC");
        // $this->db->limit($limit, $start);
        $query = $this->db->get();
        // echo $this->db->last_query();exit;
        return $query->result_array();
    }

    public function relatedQue($QueId, $collegeId)
    {
        $this->db->select(
            "q.question_id, q.question, q.date,q.views,count(a.answer) as answerCount"
        );
        $this->db->from("question q");
        $this->db->where("q.question_id !=", $QueId);
        $this->db->where("q.college_id", $collegeId);
        $this->db->join("answer a", "a.question_id = q.question_id", "left");
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getQAFollowCount($questionId)
    {
        $this->db->select("COUNT(question_id) as QuestionFollowCount");
        $this->db->where("question_id", 7);
        $query = $this->db->get("question_follow");
        $result = $query->row();
        $questionFollowCount = $result->QuestionFollowCount;
        return $result;
    }

    public function getAnsweres($questionId)
    {
        $this->db->select(
            'a.*, CONCAT(u.f_name, " ", u.l_name) as answerby,u.image'
        );
        $this->db->from("answer a");
        $this->db->join("users u", "u.id = a.user_id", "left");
        $this->db->where("a.question_id", $questionId);
        $query = $this->db->get();
        $result = $query->result_array();
        return $result;
    }

    public function getAnsComments($answersid)
    {
        $this->db->select(
            'ac.*, CONCAT(u.f_name, " ", u.l_name) as commentby,u.image'
        );
        $this->db->from("answer_comment ac");
        $this->db->join("users u", "u.id = ac.user_id", "left");
        $this->db->where("ac.answer_id", $answersid);
        $query = $this->db->get();
        $result = $query->result_array();
        return $result;
    }

    public function getUnAnsweredQueofCollege($collegeId, $limit, $start)
    {
        $this->db->select(
            'q.question_id, q.question, q.date, q.views, q.course_type, ac.name AS course_typeName, q.course_id, c.name AS courseName, q.user_id, CONCAT(u.f_name, " ", u.l_name) AS fullname,u.image, q.college_id'
        );
        $this->db->from("question q");
        $this->db->join(
            "academic_categories ac",
            "ac.category_id = q.course_type",
            "left"
        );
        $this->db->join("courses c", "c.id = q.course_id", "left");
        $this->db->join("users u", "u.id = q.user_id", "left");
        $this->db->where("q.college_id", $collegeId);
        $this->db->where("q.is_deleted", 0);
        $this->db->where(
            "NOT EXISTS (SELECT 1 FROM answer a WHERE a.question_id = q.question_id)",
            null,
            false
        );
        $this->db->order_by("q.date", "DESC");
        $this->db->limit(10);

        $query = $this->db->get();

        //echo $this->db->last_query();exit;
        return $query->result_array();
    }

    public function postQuestion($data)
    {
        $this->db->insert("question", $data);
        return $this->db->insert_id();
    }

    public function postAnswere($data)
    {
        $this->db->insert("answer", $data);
        return $this->db->insert_id();
    }

    public function updateQuestionRepStatus($questionId)
    {
        $this->db->where("question_id", $questionId);
        $this->db->set("replied", "replied+1", false);
        $this->db->update("question");
    }

    public function postAnsComment($data)
    {
        $this->db->insert("answer_comment", $data);
        return $this->db->insert_id();
    }

    public function followQuestion($data)
    {
        $this->db->insert("question_follow", $data);
        return $this->db->insert_id();
    }
    public function UnfollowQuestion($question_id, $user_id)
    {
        $this->db->where("user_id", $user_id);
        $this->db->where("question_id", $question_id);
        $this->db->delete("question_follow");
    }

    public function getAns($answer_id)
    {
        $this->db->select("*");
        $this->db->from("answer");
        $this->db->where("answer_id", $answer_id);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function voteAnswer($data, $answer_id)
    {
        $this->db->where("answer_id", $answer_id);
        $result = $this->db->update("answer", $data);
        return $result;
    }

    public function saveEnquiry($arr)
    {
        $this->db->insert("inquiry", $arr);
        return $this->db->insert_id();
    }

    public function addLog($logArr, $tableName)
    {
        $this->db->insert($tableName, $logArr);
        return $this->db->insert_id();
    }
    public function getQueAnsAboutAdmissions($collegeId)
    {
        $this->db->select(
            'q.question_id, q.question, q.date,q.views, q.course_type, ac.name AS course_typeName, q.course_id, c.name AS courseName, q.user_id, CONCAT(u.f_name, " ", u.l_name) AS question_asked_by,u.image, q.college_id'
        );
        $this->db->from("question q");
        $this->db->join(
            "academic_categories ac",
            "ac.category_id = q.course_type",
            "left"
        );
        $this->db->join("courses c", "c.id = q.course_id", "left");
        $this->db->join("users u", "u.id = q.user_id", "left");
        $this->db->where("q.college_id", $collegeId);
        $this->db->where(
            "EXISTS (SELECT 1 FROM answer a WHERE a.question_id = q.question_id)",
            null,
            false
        );
        $this->db->group_start();
        $this->db->like("q.question", "admission");
        $this->db->or_like("q.question", "apply");
        $this->db->or_like("q.question", "application");
        $this->db->or_like("q.question", "placement");
        $this->db->or_like("q.question", "join");
        $this->db->or_like("q.question", "admission process");
        $this->db->group_end();
        $this->db->set("q.views", "q.views+1", false);
        $this->db->order_by("q.date", "DESC");
        $this->db->order_by("RAND()");
        $this->db->limit(5);
        $query2 = $this->db->get();
        $result2 = $query2->result_array();
        return $result2;
    }

    public function getQueAnsAboutCourses($collegeId, $courseid)
    {
        $this->db->select(
            'q.question_id, q.question, q.date,q.views, q.course_type, ac.name AS course_typeName, q.course_id, c.name AS courseName, q.user_id, CONCAT(u.f_name, " ", u.l_name) AS question_asked_by,u.image, q.college_id'
        );
        $this->db->from("question q");
        $this->db->join(
            "academic_categories ac",
            "ac.category_id = q.course_type",
            "left"
        );
        $this->db->join("courses c", "c.id = q.course_id", "left");
        $this->db->join("users u", "u.id = q.user_id", "left");
        $this->db->where("q.college_id", $collegeId);
        $this->db->where("q.course_id", $courseid);
        $this->db->where(
            "EXISTS (SELECT 1 FROM answer a WHERE a.question_id = q.question_id)",
            null,
            false
        );
        $this->db->group_start();
        $this->db->like("q.question", "University");
        $this->db->or_like("q.question", "Course");
        $this->db->or_like("q.question", "application");
        $this->db->or_like("q.question", "Curriculum");
        $this->db->or_like("q.question", "Education");
        $this->db->or_like("q.question", "Learning");
        $this->db->or_like("q.question", "Study");
        $this->db->or_like("q.question", "Academic");
        $this->db->or_like("q.question", "Degree");
        $this->db->or_like("q.question", "Program");
        $this->db->or_like("q.question", "Syllabus");
        $this->db->or_like("q.question", "Training");

        $this->db->group_end();
        $this->db->set("q.views", "q.views+1", false);
        $this->db->order_by("q.date", "DESC");
        $this->db->order_by("RAND()");
        $this->db->limit(5);
        $query2 = $this->db->get();
        $result2 = $query2->result_array();
        return $result2;
    }

    public function getTotalQuestionForCollege($collegeId)
    {
        $this->db->select(
            "(SELECT COUNT(*)  FROM question WHERE college_id = " .
                $collegeId .
                ") AS TOTALQUESTION",
            false
        );
        $query1 = $this->db->get();
        $result1 = $query1->row();
        return $result1;
    }

    function getBrochure($collegeid)
    {
        $this->db->where("collegeid", $collegeid);
        return $this->db->get("brochures")->result_array();
    }

    public function addUserActivity($Arr)
    {
        $this->db->insert("user_activity", $Arr);
        return $this->db->insert_id();
    }

    public function getClgIdByQues($Ques)
    {
        $this->db->select("*");
        $this->db->from("question");
        $this->db->where("question_id", $Ques);
        $query = $this->db->get();
        return $query->result_array();
    }

    //for time beign

    public function updateClgReport($college_id, $ClgRepArr)
    {
        if (isset($ClgRepArr["no_of_articles_linked"])) {
            $this->db->set(
                "no_of_articles_linked",
                "no_of_articles_linked + " .
                    $ClgRepArr["no_of_articles_linked"],
                false
            );
        }
        if (isset($ClgRepArr["no_of_brochures_download"])) {
            $this->db->set(
                "no_of_brochures_download",
                "no_of_brochures_download + " .
                    $ClgRepArr["no_of_brochures_download"],
                false
            );
        }
        if (isset($ClgRepArr["no_of_application_submitted"])) {
            $this->db->set(
                "no_of_application_submitted",
                "no_of_application_submitted + " .
                    $ClgRepArr["no_of_application_submitted"],
                false
            );
        }
        if (isset($ClgRepArr["no_of_que_asked"])) {
            $this->db->set(
                "no_of_que_asked",
                "no_of_que_asked + " . $ClgRepArr["no_of_que_asked"],
                false
            );
        }
        if (isset($ClgRepArr["no_of_answeres"])) {
            $this->db->set(
                "no_of_answeres",
                "no_of_answeres + " . $ClgRepArr["no_of_answeres"],
                false
            );
        }
        if (isset($ClgRepArr["no_of_review"])) {
            $this->db->set(
                "no_of_review",
                "no_of_review + " . $ClgRepArr["no_of_review"],
                false
            );
        }
        $this->db->where("college", $college_id);
        $query = $this->db->update("college_report");
        return $query;
    }

    public function saveClgReport($ClgRepArr)
    {

        $this->db->set(
            "no_of_articles_linked",
            "no_of_articles_linked + " . $ClgRepArr["no_of_articles_linked"],
            false
        );
        $this->db->set(
            "no_of_brochures_download",
            "no_of_brochures_download + " .
                $ClgRepArr["no_of_brochures_download"],
            false
        );
        $this->db->set(
            "no_of_application_submitted",
            "no_of_application_submitted + " .
                $ClgRepArr["no_of_application_submitted"],
            false
        );
        $this->db->set(
            "no_of_que_asked",
            "no_of_que_asked + " . $ClgRepArr["no_of_que_asked"],
            false
        );
        $this->db->set(
            "no_of_answeres",
            "no_of_answeres + " . $ClgRepArr["no_of_answeres"],
            false
        );
        if (!empty($ClgRepArr["no_of_review"])) {
            $this->db->set(
                "no_of_review",
                "no_of_review + " . $ClgRepArr["no_of_review"],
                false
            );
        }


        $this->db->set("college", $ClgRepArr["college"], false);
        $this->db->insert("college_report");
        $collegeRep["college_report_id"] = $this->db->insert_id();
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
        $this->db->join("college c", "c.id = cr.college", "left");
        $this->db->join("city ci", "ci.id = c.cityid", "left");
        $this->db->join("state s", "s.id = c.stateid", "left");

        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }

    public function saveCourseApplication($arr)
    {
        $this->db->insert("course_application", $arr);
        return $this->db->insert_id();
    }
    public function savPredictAdmission($arr)
    {
        $this->db->insert("predict_admission", $arr);
        return $this->db->insert_id();
    }

    public function getTrendingSpecilization()
    {
        $this->db->select("s.name as keyword");
        $this->db->from("specialization s");
        // $this->db->where('keyword IS NOT NULL');
        // $this->db->where('keyword !=', '');
        // $this->db->group_by('keyword');
        // $this->db->order_by('keyword', 'ASC');
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }

    public function getQueAnsAboutScholarships($collegeId)
    {
        $this->db->select(
            'q.question_id, q.question, q.date,q.views, q.course_type, ac.name AS course_typeName, q.course_id, c.name AS courseName, q.user_id, CONCAT(u.f_name, " ", u.l_name) AS question_asked_by,u.image, q.college_id'
        );
        $this->db->from("question q");
        $this->db->join(
            "academic_categories ac",
            "ac.category_id = q.course_type",
            "left"
        );
        $this->db->join("courses c", "c.id = q.course_id", "left");
        $this->db->join("users u", "u.id = q.user_id", "left");
        $this->db->where("q.college_id", $collegeId);
        // $this->db->where('q.course_id',$courseid);
        $this->db->where(
            "EXISTS (SELECT 1 FROM answer a WHERE a.question_id = q.question_id)",
            null,
            false
        );
        $this->db->group_start();
        $this->db->like("q.question", "scholarship");
        $this->db->or_like("q.question", "Eligibility");
        $this->db->or_like("q.question", "application");
        $this->db->or_like("q.question", "Deadline");
        $this->db->or_like("q.question", "Financial");
        $this->db->or_like("q.question", "Qualifications");
        $this->db->or_like("q.question", "Criteria");
        $this->db->or_like("q.question", "Renewal");
        $this->db->or_like("q.question", "Form");

        $this->db->group_end();
        $this->db->set("q.views", "q.views+1", false);
        $this->db->order_by("q.date", "DESC");
        $this->db->order_by("RAND()");
        $this->db->limit(5);
        $query2 = $this->db->get();
        $result2 = $query2->result_array();
        return $result2;
    }

    function generateLink($type, $id)
    {

        if ($type == 'article') {
            $this->db->select('title, description, image');
            $this->db->from('blog');
            $this->db->where('id', $id);
        } elseif ($type == 'exam') {
            $this->db->select('e.title, e.description, g.image');
            $this->db->from('exams e');
            $this->db->join('gallery g', 'g.postid = e.id', 'left');
            $this->db->where('e.id', $id);
            $this->db->where('g.type', 'exams');
        } elseif ($type == 'event') {
            $this->db->select('e.event_name AS title, e.event_desc AS description, g.image');
            $this->db->from('events e');
            $this->db->join('gallery g', 'g.postid = e.event_id', 'left');
            $this->db->where('e.event_id', $id);
            $this->db->where('g.type', 'events');
        } elseif ($type == 'college') {
            // echo "ttt";exit;
            $this->db->select('e.title, e.description, g.image,e.web');
            $this->db->from('college e');
            $this->db->join('gallery g', 'g.postid = e.id', 'left');
            $this->db->where('e.id', $id);
            $this->db->where('g.type', 'college');
        }
        $query = $this->db->get();
        $result = $query->row();
        return $result;
    }

    function getfooterNotification()
    {
         $result = $this->db
        ->where('is_enabled', 1)
        ->order_by('id', 'DESC')
        ->get('footer_notification')
        ->result_array();

        return $result;
    }

    public function saveStudyAbroad($data)
	{
		return $this->db->insert('study_abroad_enquiries', $data);
	}
}
