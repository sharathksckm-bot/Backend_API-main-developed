<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Methods: GET, POST');
/**
 * QuestionAnswere Controller
 *
 * @category   Controllers
 * @package    Admin
 * @subpackage QuestionAnswere
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    22 APRIL 2024
 *
 * Class QuestionAnswere handles all the operations related to displaying list, creating QuestionAnswere, update, and delete of exames like (KCET,COMEDK,JEE )
 */

if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}
class QuestionAnswere extends CI_Controller
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
        $this->load->model("admin/QuestionAnswere_model", "", true);
		$this->load->library('Utility');

    }

    public function getQuestionList()
    {
        $data = json_decode(file_get_contents('php://input'));
    
        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data->status = 'ok';
            echo json_encode($data);
            exit;
        }
    
        if ($data) {
       
           $headers = apache_request_headers();
               
           $token = str_replace("Bearer ", "", $headers['Authorization']);
           $kunci = $this->config->item('jwt_key');
           $userData = JWT::decode($token, $kunci);
           Utility::validateSession($userData->iat,$userData->exp);
           $tokenSession = Utility::tokenSession($userData);
           
            $columns = array(
                0 => 'id',
                1 => 'round',
              
            );
            $limit = $data->length;
            $start = ($data->draw - 1) * $limit;
            $orderColumn = $columns[$data->order[0]->column];
            $orderDir = $data->order[0]->dir;
            $totalData = $this->QuestionAnswere_model->countAllQuestion();
            
            $totalFiltered = $totalData;
    
            if (!empty($data->search->value)) {
                $search = $data->search->value;
                $totalFiltered = $this->QuestionAnswere_model->countFilteredQuestion($search);
                $QuestionAns = $this->QuestionAnswere_model->getFilteredQuestion($search, $start, $limit, $orderColumn, $orderDir);

               } else {
                $QuestionAns = $this->QuestionAnswere_model->getAllKQuestion($start, $limit, $orderColumn, $orderDir);
            }
    
            $datas = array();
            foreach ($QuestionAns as $queans) {
               
                $nestedData = array();
                $nestedData['questionid'] = $queans->question_id;
                $nestedData['question'] = $queans->question;  
                $nestedData['question_asked_by'] = $queans->question_asked_by; 
                $nestedData['course_type'] = $queans->course_type;
                $nestedData['coursename'] = $queans->coursename;
                $nestedData['collegename'] = $queans->collegename;
                $nestedData['question_view'] = $queans->question_view;
                $nestedData['replied'] = $queans->replied;
                $nestedData['college_id'] = $queans->college_id;

                $datas[] = $nestedData;
            }
    
            $json_data = array(
                'draw' => intval($data->draw),
                'recordsTotal' => intval($totalData),
                'recordsFiltered' => intval($totalFiltered),
                'data' => $datas
            );
    
            echo json_encode($json_data);
        }
        else{
           $response["response_code"] = "500";
           $response["response_message"] = "Data is null";
           echo json_encode($response);
           exit();
        }
       exit;
    }
    
    
        public function deleteQuestion()
    {
        $data = json_decode(file_get_contents('php://input'));
    
        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data->status = 'ok';
            echo json_encode($data);
            exit;
        }
    
        if ($data) {
       
           $headers = apache_request_headers();
               
           $token = str_replace("Bearer ", "", $headers['Authorization']);
           $kunci = $this->config->item('jwt_key');
           $userData = JWT::decode($token, $kunci);
           Utility::validateSession($userData->iat,$userData->exp);
           $tokenSession = Utility::tokenSession($userData);
           
            $questionId = $data->questionId;
            $result = $this->QuestionAnswere_model->deleteQuestion($questionId);
            if($result)
            {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["response_data"] = "Question deleted.";
            }
            else
            {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }
            
        }
        else{
           $response["response_code"] = "500";
           $response["response_message"] = "Data is null";
          
        }
         echo json_encode($response);
           exit();
       
    }
}
