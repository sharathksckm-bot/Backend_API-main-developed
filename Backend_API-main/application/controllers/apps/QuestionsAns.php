<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST");
if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}
class QuestionsAns extends CI_Controller
{
    /**
     * Constructor
     *
     * Loads necessary libraries, helpers, and models for the QuestionAnswere controller.
     */
    public function __construct()
    {
        parent::__construct();
        $data = json_decode(file_get_contents('php://input'));
		if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
			$data['status'] = 'ok';
			echo json_encode($data);
			exit;
		}
        $this->load->model("apps/College_model", "", true);
        $this->load->model("apps/Exam_model", "", true);
        $this->load->model("web/User_model","", true);

        $this->load->model("web/Courses_model", "", true);
        $this->load->model("apps/Common_model", "");
        $this->load->model("admin/Common_model", "admin_Common_model" );

        // // $this->load->model('admin/Common_model','legacy_blog_model');
        // $this->load->model('web/Common_model','main_blog_model');
        // $this->load->model('admin/Common_model','legacy_blog_model');
        $this->load->library("Utility");
        // var_dump($this->legacy_blog_model);
    }
	

    public function getQAofCollege()
    {
		//echo"testing...";exit;
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if($data){
        $collegeId = $data->collegeId;
        $limit = $data->length;
        $start = ($data->draw - 1) * $limit;
        $getQAofCollege = $this->Common_model->getQAofCollege($collegeId,$limit, $start);

        foreach ($getQAofCollege as &$question) {
            $question['question_asked'] = $this->getDateDifference($question['date']);
            $question['que_askby_image'] = base_url().'/uploads/userImage/'. $question['image'];
            $questionId = $question['question_id'];
        
            // Get question follow count
            // $question['QuestionFollowCount'] = $this->Common_model->getQAFollowCount($questionId);

            $QuestionFollowCount = $this->Common_model->getQAFollowCount($questionId);
            $question['QuestionFollowCount'] = $QuestionFollowCount->QuestionFollowCount;
            // Get answers for this question
            $answers = $this->Common_model->getAnsweres($questionId);
        
            foreach ($answers as &$answer) {
                $answer['answered_question'] = $this->getDateDifference($answer['date']);
                // $answer['ans_by_image'] = base_url().'/uploads/userImage'. $answer['image'];
                // Get comments for this answer
                $ansComments = $this->Common_model->getAnsComments($answer['answer_id']);
                foreach ($ansComments as &$comment) {
                    $comment['commented_on_answere'] = $this->getDateDifference($comment['date']);
                    // $answer['comment_by_image'] = base_url().'/uploads/userImage'. $answer['image'];

                }
                $answer['answere_comments'] = $ansComments;
            }
        
            $question['Answeres'] = $answers;
        }
        // Now $getQAofCollege contains questions along with their follow counts, answers, and comments.
        

        if ($getQAofCollege) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["response_data"] = $getQAofCollege;
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed";
        }
    }
    else
    {
        $response["response_code"] = "500";
         $response["response_message"] = "Data is null";
    }
        echo json_encode($response);
        exit;
    }

	
    function getDateDifference($storedDate) {
        $storedDate = new DateTime($storedDate);
        $currentDate = new DateTime();
        $interval = $currentDate->diff($storedDate);
        if ($interval->y > 0) {
            return $interval->y . " years ago";
        } elseif ($interval->m > 0) {
            return $interval->m . " months ago";
        } elseif ($interval->d > 0) {
            return $interval->d . " days ago";
        } elseif ($interval->h > 0) {
            return $interval->h . " hours ago";
        } elseif ($interval->i > 0) {
            return $interval->i . " minutes ago";
        } else {
            return "Just now";
        }
    }


    public function getUnAnsweredQueofCollege()
    {
		//echo "testing..";exit;
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if($data){
        $collegeId = $data->collegeId;
        $limit = $data->length;
        $start = ($data->draw - 1) * $limit;
        $getUnAnweredQueofCollege = $this->Common_model->getUnAnsweredQueofCollege($collegeId,$limit, $start);
        foreach ($getUnAnweredQueofCollege as &$question) {
            $question['question_asked'] = $this->getDateDifference($question['date']);
        
            $questionId = $question['question_id'];
        
            // Get question follow count
            $QuestionFollowCount = $this->Common_model->getQAFollowCount($questionId);
            $question['QuestionFollowCount'] = $QuestionFollowCount->QuestionFollowCount;

        }


        if ($getUnAnweredQueofCollege) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["response_data"] = $getUnAnweredQueofCollege;
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed";
            $response["response_data"] = "THERE ARE NO UNANSWERED QUESTIONS";

        }
    }
    else
    {
        $response["response_code"] = "500";
         $response["response_message"] = "Data is null";
    }
        echo json_encode($response);
        exit;
    }


    public function postQuestion()
    {
       // echo "tttt";exit;
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if($data){
        $collegeId = $data->collegeid;
        $courselevel = isset($data->courselevel)?$data->courselevel:0;
        $course = isset($data->course)?$data->course:0;
        $user_id = $data->user_id;
        $date = date("Y-m-d H:i:s");
        $questionInput = $data->questionInput;
        $data = [
            'question' => $questionInput,
            'user_id' => $user_id,
            'college_id' => $collegeId,
			'description' => '',
            'course_id' => $course,
            'course_type' => $courselevel,
            'date' => date('Y-m-d H:i:s')
        ];
		
        $result = $this->Common_model->postQuestion($data);
        $clgDtl = $this->College_model->getCollegeDetailsByID($collegeId);
        $getUserDetails = $this->User_model->getUserDetailsById($user_id);
        //print_r($clgDtl);exit;
        if($getUserDetails)
        {$email = $getUserDetails[0]->email;}
		else
		{$response['response_message']='Invalid user';$response['response_code']=300;}
        
        $Arr = ['user_name'=>$user_id,'email'=>$email,'college'=>$clgDtl[0]['id'],'location'=>$clgDtl[0]['city'],'latest_activity'=>'Question Submmited','question'=>$result];

        // $Arr = ['user_name'=>$name,'email'=>$email,'location'=>'','latest_activity'=>''.$clgDtl[0]['title'].','.$clgDtl[0]['city'].' Question Submmited'];
        $addUserActivity = $this->Common_model->addUserActivity($Arr);
        $ClgRepArr = ['college'=>$collegeId,'no_of_articles_linked'=>0,'no_of_brochures_download'=>0,'no_of_application_submitted'=>0,'no_of_que_asked'=>1,'no_of_answeres'=>0,'no_of_review'=>0];
        $checkcollegeReport = $this->Common_model->checkcollegeReport($collegeId);
        if($checkcollegeReport > 0)
        {
         $updateClgReport = $this->Common_model->updateClgReport($collegeId,$ClgRepArr);

        }
        else
        {

        $saveClgReport = $this->Common_model->saveClgReport($ClgRepArr);
        }
        if ($result) {
            $logArr = ['question_id'=>$result];
            $tableName = 'question_log ';
            $addLog = $this->Common_model->addLog($logArr,$tableName);
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["response_data"] = $result;
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed";
        }
    }
    else
    {
        $response["response_code"] = "500";
         $response["response_message"] = "Data is null";
    }
        echo json_encode($response);
        exit;
    }


    public function postAnswere()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if($data){
        $answer = $data->answer;
        $user_id = $data->user_id;
        $questionId = $data->questionId;
        $date = date("Y-m-d H:i:s");
        $data = [
            'answer' => $answer,
            'user_id' => $user_id,
            'question_id' => $questionId,
            'date' => date('Y-m-d H:i:s')
        ];
        $result = $this->Common_model->postAnswere($data);
        $getClgIdByQues = $this->Common_model->getClgIdByQues($questionId);
        $collegeId = $getClgIdByQues[0]['college_id'];
        $clgDtl = $this->College_model->getCollegeDetailsByID($collegeId);
        $getUserDetails = $this->User_model->getUserDetailsById($user_id);
        if($getUserDetails)
        {$email = $getUserDetails[0]->email;}else{$response['response_message']='Invalid user';$response['response_code']=300;}
        
        $Arr = ['user_name'=>$user_id,'email'=>$email,'college'=>$clgDtl[0]['id'],'location'=>$clgDtl[0]['city'],'latest_activity'=>'Answere Submmited','answere'=>$result];

        // $Arr = ['user_name'=>$name,'email'=>$email,'location'=>'','latest_activity'=>''.$clgDtl[0]['title'].','.$clgDtl[0]['city'].' Answere Submmited'];
        $addUserActivity = $this->Common_model->addUserActivity($Arr);

       
        $ClgRepArr = ['college'=>$collegeId,'no_of_articles_linked'=>0,'no_of_brochures_download'=>0,'no_of_application_submitted'=>0,'no_of_que_asked'=>0,'no_of_answeres'=>1];
        $checkcollegeReport = $this->admin_Common_model->checkcollegeReport($collegeId);
        if($checkcollegeReport > 0)
        {
         $updateClgReport = $this->admin_Common_model->updateClgReport($collegeId,$ClgRepArr);

        }
        else
        {

        $saveClgReport = $this->admin_Common_model->saveClgReport($ClgRepArr);
        }
        if ($result) {
            $updateQuestionRepStatus = $this->Common_model->updateQuestionRepStatus($questionId);
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["response_data"] = $result;
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed";
        }
    }
    else
    {
        $response["response_code"] = "500";
         $response["response_message"] = "Data is null";
    }
        echo json_encode($response);
        exit;
    }

    public function postAnsComment()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if($data){
        $comment = $data->comment;
        $user_id = $data->user_id;
        $answer_id = $data->answer_id;
        $date = date("Y-m-d H:i:s");
        $data = [
            'comment' => $comment,
            'user_id' => $user_id,
            'answer_id' => $answer_id,
            'date' => date('Y-m-d H:i:s')
        ];
        $result = $this->Common_model->postAnsComment($data);

        if ($result) {
            // $updateQuestionRepStatus = $this->Common_model->updateQuestionRepStatus($questionId);
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["response_data"] = $result;
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed";
        }
    }
    else
    {
        $response["response_code"] = "500";
         $response["response_message"] = "Data is null";
    }
        echo json_encode($response);
        exit;
    }


    public function voteAnswere()
    {
       // echo "tttt";exit;
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if($data){
        $action = $data->action;
        $answer_id = $data->answer_id;
        $user_id = $data->user_id;
        $date = date("Y-m-d H:i:s");
        $getAns = $this->Common_model->getAns($answer_id);
        $userArr[] = $data->user_id;
        if(strtoupper($action)=='LIKE')
        { 
        $userArr = explode(",", $getAns[0]['like_users']);
        $like_users = array_unique($userArr);
        $like_users[] = $data->user_id;
        $like = count($like_users);
        $data = [
            'like' => $like,
            'like_users' => implode(",", $like_users),
            'date' => date('Y-m-d H:i:s')
        ];
        }
        if(strtoupper($action)=='DISLIKE')
        {
            $userArr = explode(",", $getAns[0]['dislike_users']);
            $dislike_users = array_unique($userArr);
            $dislike_users[] = $data->user_id;

            $dis_like = count($dislike_users);
            $data = [
                'dis_like' => $dis_like,
                'dislike_users' => implode(",", $dislike_users),
                'date' => date('Y-m-d H:i:s')
            ];
        }
        $result = $this->Common_model->voteAnswer($data,$answer_id);

        if ($result) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["response_data"] = $result;
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed";
        }
    }
    else
    {
        $response["response_code"] = "500";
         $response["response_message"] = "Data is null";
    }
        echo json_encode($response);
        exit;
    }

    public function getQADataByQueId()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if($data){
        $QueId = $data->QueId;
        $collegeId = $data->collegeId;
        $getQADataByQueId = $this->Common_model->getQADataByQueId($QueId);

        foreach ($getQADataByQueId as &$question) {
            $question['question_asked'] = $this->getDateDifference($question['date']);
            $question['que_askby_image'] = base_url().'/uploads/userImage/'. $question['image'];
            $questionId = $question['question_id'];
        
            // Get question follow count
            // $question['QuestionFollowCount'] = $this->Common_model->getQAFollowCount($questionId);

            $QuestionFollowCount = $this->Common_model->getQAFollowCount($questionId);
            $question['QuestionFollowCount'] = $QuestionFollowCount->QuestionFollowCount;
            // Get answers for this question
            $answers = $this->Common_model->getAnsweres($questionId);
        
            foreach ($answers as &$answer) {
                $answer['answered_question'] = $this->getDateDifference($answer['date']);
                // $answer['ans_by_image'] = base_url().'/uploads/userImage'. $answer['image'];
                // Get comments for this answer
                $ansComments = $this->Common_model->getAnsComments($answer['answer_id']);
                foreach ($ansComments as &$comment) {
                    $comment['commented_on_answere'] = $this->getDateDifference($comment['date']);
                    // $answer['comment_by_image'] = base_url().'/uploads/userImage'. $answer['image'];

                }
                $answer['answere_comments'] = $ansComments;
            }
        
            $question['Answeres'] = $answers;
        }
        $relatedQue = $this->Common_model->relatedQue($QueId,$collegeId);
        if ($getQADataByQueId) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["response_data"] = $getQADataByQueId;
            $response["related_question"] = $relatedQue;

        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed";
        }
    }
    else
    {
        $response["response_code"] = "500";
         $response["response_message"] = "Data is null";
    }
        echo json_encode($response);
        exit;
    }


    public function followQuestion()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if($data){
        $action = $data->action;
        $user_id = $data->user_id;
        $date = date("Y-m-d H:i:s");
        $question_id = isset($data->question_id)?$data->question_id:'';
        $data = [
            'question_id' => $question_id,
            'user_id' => $user_id,
            'date' => date('Y-m-d H:i:s')
        ];
        if(strtoupper($action) == 'FOLLOW')
        {
        $result = $this->Common_model->followQuestion($data);
        }
        else
        {
        $result = $this->Common_model->UnfollowQuestion($question_id,$user_id);

        }
        if ($result) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["response_data"] = $result;
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed";
        }
    }
    else
    {
        $response["response_code"] = "500";
         $response["response_message"] = "Data is null";
    }
        echo json_encode($response);
        exit;
    }

    public function getQueAnsAboutAdmissions() {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if($data){
        $collegeId = $data->collegeId;
        $getQueAnsAboutAdmissions = $this->Common_model->getQueAnsAboutAdmissions($collegeId);
        foreach ($getQueAnsAboutAdmissions as &$question) {
            $question['question_asked'] = $this->getDateDifference($question['date']);
            $question['que_askby_image'] = base_url().'/uploads/userImage/'. $question['image'];
            $questionId = $question['question_id'];

            $QuestionFollowCount = $this->Common_model->getQAFollowCount($questionId);
            $question['QuestionFollowCount'] = $QuestionFollowCount->QuestionFollowCount;
            $answers = $this->Common_model->getAnsweres($questionId);
            foreach ($answers as &$answer) {
                $answer['answered_question'] = $this->getDateDifference($answer['date']);
            }
            $question['Answeres'] = $answers;

        }
        $totalQuestion = $this->Common_model->getTotalQuestionForCollege($collegeId);
        if ($getQueAnsAboutAdmissions) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["QueAnsAboutAdmissions"] = $getQueAnsAboutAdmissions;
            $response["totalQuestion"] = $totalQuestion;

        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed";
            $response["response_data"] = "THERE ARE NO QUESTIONS RELATED TO ADMISSION .";

        }
    }
    else
    {
        $response["response_code"] = "500";
         $response["response_message"] = "Data is null";
    }
        echo json_encode($response);
        exit;
    }


    public function getTotalQuestionForCollege()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if($data){
        $collegeId = $data->collegeid;
       
        $totalQuestion = $this->Common_model->getTotalQuestionForCollege($collegeId);
        if ($totalQuestion) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["totalQuestion"] = $totalQuestion;

        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed";

        }
    }
    else
    {
        $response["response_code"] = "500";
         $response["response_message"] = "Data is null";
    }
        echo json_encode($response);
        exit;
    }
	public function getQueAnsAboutScholarships() {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if($data){
        $collegeId = $data->collegeId;
        $getQueAnsAboutScholarships = $this->Common_model->getQueAnsAboutScholarships($collegeId);
        foreach ($getQueAnsAboutScholarships as &$question) {
            $question['question_asked'] = $this->getDateDifference($question['date']);
            $question['que_askby_image'] = base_url().'/uploads/userImage/'. $question['image'];
            $questionId = $question['question_id'];

            $QuestionFollowCount = $this->Common_model->getQAFollowCount($questionId);
            $question['QuestionFollowCount'] = $QuestionFollowCount->QuestionFollowCount;
            $answers = $this->Common_model->getAnsweres($questionId);
            foreach ($answers as &$answer) {
                $answer['answered_question'] = $this->getDateDifference($answer['date']);
            }
            $question['Answeres'] = $answers;

        }
        $totalQuestion = $this->Common_model->getTotalQuestionForCollege($collegeId);
        if ($getQueAnsAboutScholarships) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["QueAnsAboutScholarship"] = $getQueAnsAboutScholarships;
            // $response["totalQuestion"] = $totalQuestion;

        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed";
            $response["response_data"] = "THERE ARE NO QUESTIONS RELATED TO SCHOLARSHIP .";

        }
    }
    else
    {
        $response["response_code"] = "500";
         $response["response_message"] = "Data is null";
    }
        echo json_encode($response);
        exit;
    }
	
	 public function getQueAnsAboutCourses() {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if($data){
        $collegeId = $data->collegeId;
        $courseid = $data->courseId;
        $getQueAnsAboutCourses = $this->Common_model->getQueAnsAboutCourses($collegeId,$courseid);
        foreach ($getQueAnsAboutCourses as &$question) {
            $question['question_asked'] = $this->getDateDifference($question['date']);
            $question['que_askby_image'] = base_url().'/uploads/userImage/'. $question['image'];
            $questionId = $question['question_id'];

            $QuestionFollowCount = $this->Common_model->getQAFollowCount($questionId);
            $question['QuestionFollowCount'] = $QuestionFollowCount->QuestionFollowCount;
            $answers = $this->Common_model->getAnsweres($questionId);
            foreach ($answers as &$answer) {
                $answer['answered_question'] = $this->getDateDifference($answer['date']);
            }
            $question['Answeres'] = $answers;

        }
        $totalQuestion = $this->Common_model->getTotalQuestionForCollege($collegeId);
        if ($getQueAnsAboutCourses) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["QueAnsAboutCourses"] = $getQueAnsAboutCourses;
            // $response["totalQuestion"] = $totalQuestion;

        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed";
            $response["response_data"] = "THERE ARE NO QUESTIONS FOR THIS COURSE .";

        }
    }
    else
    {
        $response["response_code"] = "500";
         $response["response_message"] = "Data is null";
    }
        echo json_encode($response);
        exit;
    }
	
	
	public function getQuestionAns(){
		 $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
		
		//print_r($data);exit;
        if($data){
          $getCourseId = $data->course_categoryId;
        $getQueAns = $this->Common_model->getQue($getCourseId);
        foreach ($getQueAns as &$question) {
            $question['question_asked'] = $this->getDateDifference($question['date']);
           // print_r($question['question_asked']);exit;
            //$question['que_askby_image'] = base_url().'/uploads/userImage/'. $question['image'];
            $questionId = $question['question_id'];

            $QuestionFollowCount = $this->Common_model->getQAFollowCount($questionId);
            //$question['QuestionFollowCount'] = $QuestionFollowCount->QuestionFollowCount;
            $answers = $this->Common_model->getAnsweres($questionId);
            foreach ($answers as &$answer) {
                $answer['answered_question'] = $this->getDateDifference($answer['date']);
            }
            $question['Answeres'] = $answers;

        }
  
        if ($getQueAns) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["QueAnsAboutCourses"] = $getQueAns;
            // $response["totalQuestion"] = $totalQuestion;

        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed";
            $response["response_data"] = "THERE ARE NO QUESTIONS FOR THIS COURSE .";

        }
    }
    else
    {
        $response["response_code"] = "500";
         $response["response_message"] = "Data is null";
    }
        echo json_encode($response);
        exit;
		
	}
	
	
	
	
	
	
/*	 public function getQAofCollege()
    {
		// echo "testing...";exit;
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if (empty($_SERVER["HTTP_AUTHORIZATION"])) {
            if (
                !is_object($data) ||
                !property_exists($data, "defaultToken") ||
                empty($data->defaultToken)
            ) {
                $response["response_code"] = "401";
                $response["response_message"] =
                    "UNAUTHORIZED: Please provide an access token to continue accessing the API";
                echo json_encode($response);
                exit();
            }
            if ($data->defaultToken !== $this->config->item("defaultToken")) {
                $response["response_code"] = "402";
                $response["response_message"] =
                    "UNAUTHORIZED: Please provide a valid access token to continue accessing the API";
                echo json_encode($response);
                exit();
            }
        } else {
            $headers = apache_request_headers();
            $token = str_replace("Bearer ", "", $headers["Authorization"]);
            $kunci = $this->config->item("jwt_key");
            $userData = JWT::decode($token, $kunci);
            Utility::validateSession($userData->iat, $userData->exp);
            $tokenSession = Utility::tokenSession($userData);
        }
		//print_r($data);exit;
		
        if ($data) {
            $collegeId = $data->collegeid;
            $limit = $data->length;
            $start = ($data->draw - 1) * $limit;
			
			//print_r($start);exit;
			
            $getQAofCollege = $this->Common_model->getQAofCollege(
                $collegeId,
                $limit,
                $start
            );
			
			//print_r($getQAofCollege);exit;

            foreach ($getQAofCollege as &$question) {
                $question["question_asked"] = $this->getDateDifference(
                    $question["date"]
                );
				
				//echo "1";exit;
				
				//print_r($question["question_asked"]);exit;
                $question["que_askby_image"] =
                    base_url() . "/uploads/userImage/" . $question["image"];
                $questionId = $question["question_id"];

                // Get question follow count
                // $question['QuestionFollowCount'] = $this->Common_model->getQAFollowCount($questionId);

                $QuestionFollowCount = $this->Common_model->getQAFollowCount(
                    $questionId
                );
                $question["QuestionFollowCount"] =
                    $QuestionFollowCount->QuestionFollowCount;
                // Get answers for this question
                $answers = $this->Common_model->getAnsweres($questionId);

                foreach ($answers as &$answer) {
                    $answer["answered_question"] = $this->getDateDifference(
                        $answer["date"]
                    );
                    // $answer['ans_by_image'] = base_url().'/uploads/userImage'. $answer['image'];
                    // Get comments for this answer
                    $ansComments = $this->Common_model->getAnsComments(
                        $answer["answer_id"]
                    );
                    foreach ($ansComments as &$comment) {
                        $comment[
                            "commented_on_answere"
                        ] = $this->getDateDifference($comment["date"]);
                        // $answer['comment_by_image'] = base_url().'/uploads/userImage'. $answer['image'];
                    }
                    $answer["answere_comments"] = $ansComments;
                }

                $question["Answeres"] = $answers;
            }
            // Now $getQAofCollege contains questions along with their follow counts, answers, and comments.

            if ($getQAofCollege) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["response_data"] = $getQAofCollege;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }
        echo json_encode($response);
        exit();
    }*/
	
	
	
	/*   public function getUnAnsweredQueofCollege()
    {
		   echo "testing...";exit;
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
       if (empty($_SERVER["HTTP_AUTHORIZATION"])) {
            if (
                !is_object($data) ||
                !property_exists($data, "defaultToken") ||
                empty($data->defaultToken)
            ) {
                $response["response_code"] = "401";
                $response["response_message"] =
                    "UNAUTHORIZED: Please provide an access token to continue accessing the API";
                echo json_encode($response);
                exit();
            }
            if ($data->defaultToken !== $this->config->item("defaultToken")) {
                $response["response_code"] = "402";
                $response["response_message"] =
                    "UNAUTHORIZED: Please provide a valid access token to continue accessing the API";
                echo json_encode($response);
                exit();
            }
        } else {
            $headers = apache_request_headers();
            $token = str_replace("Bearer ", "", $headers["Authorization"]);
            $kunci = $this->config->item("jwt_key");
            $userData = JWT::decode($token, $kunci);
            Utility::validateSession($userData->iat, $userData->exp);
            $tokenSession = Utility::tokenSession($userData);
        }
		
		
		
        if ($data) {
            $collegeId = $data->collegeid;
            $limit = $data->length;
            $start = ($data->draw - 1) * $limit;
            $getUnAnweredQueofCollege = $this->Common_model->getUnAnsweredQueofCollege(
                $collegeId,
                $limit,
                $start
            );
            foreach ($getUnAnweredQueofCollege as &$question) {
                $question["question_asked"] = $this->getDateDifference(
                    $question["date"]
                );

                $questionId = $question["question_id"];

                // Get question follow count
                $QuestionFollowCount = $this->Common_model->getQAFollowCount(
                    $questionId
                );
                $question["QuestionFollowCount"] =
                    $QuestionFollowCount->QuestionFollowCount;
            }

            if ($getUnAnweredQueofCollege) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["response_data"] = $getUnAnweredQueofCollege;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
                $response["response_data"] =
                    "THERE ARE NO UNANSWERED QUESTIONS";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }
        echo json_encode($response);
        exit();
    }*/


}