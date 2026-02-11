<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Methods: GET, POST');
date_default_timezone_set("Asia/Kolkata");
/**
 * PredictAdmission Controller
 *
 * @category   Controllers
 * @package    Admin
 * @subpackage PredictAdmission
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    25 JAN 2024
 *
 * Class PredictAdmission handles all the operations related to displaying list, creating PredictAdmission, update, and delete.
 */

if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}
class PredictAdmission extends CI_Controller
{
    /**
     * Constructor
     *
     * Loads necessary libraries, helpers, and models for the PredictAdmission controller.
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

        $this->load->model("admin/PredictAdmission_model", "", true);
		    $this->load->library('Utility');

    }

	public function getPredictedadmission()
  {
    //echo "tttt";exit;
    $data = json_decode(file_get_contents('php://input'));

    if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
      $data->status = 'ok';
      echo json_encode($data);
      exit;
    }

	  $headers = apache_request_headers();	
		$token = str_replace("Bearer ", "", $headers['Authorization']);
		$kunci = $this->config->item('jwt_key');
		$userData = JWT::decode($token, $kunci);
		Utility::validateSession($userData->iat,$userData->exp);
		$tokenSession = Utility::tokenSession($userData);
    if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
      $response["response_code"] = "401";
      $response["response_message"] = "Unauthorized";
      echo json_encode($response);
      exit();
    }
    if ($data) {

      $columns = array(
        0 => 'id',
        1 => 'CreatedDate',
        2 => 'mobile_no',
        3 => 'email',
        4 => 'category',
        5 => 'college',
        6 => 'course',
        7 => 'entrance_exam',
        8 => 'rank',
        9 => 'score',
        10 => 'student_name'
      );

      $limit = $data->length;
      $start = ($data->draw - 1) * $limit;
      $orderColumn = $columns[$data->order[0]->column];
      $orderDir = $data->order[0]->dir;
      $totalData = $this->PredictAdmission_model->countAllPredictAdmission();
      $totalFiltered = $totalData;


      if (!empty($data->search->value)) {

        $search = $data->search->value;
        $totalFiltered = $this->PredictAdmission_model->countFilteredPredictAdmission($search);
        $Predict = $this->PredictAdmission_model->getFilteredPredictAdmission($search, $start, $limit, $orderColumn, $orderDir);
      } else {
        $Predict = $this->PredictAdmission_model->getAllPredictAdmission($start, $limit, $orderColumn, $orderDir);
      }
      $datas = array();
      foreach ($Predict as $e) {
				// print_r($e['Id']);exit;
        $nestedData = array();
        $nestedData['id'] = $e['Id'];
        $nestedData['student_name'] = $e['student_name'];
        $nestedData['mobile_no'] = $e['mobile_no'];
        $nestedData['email'] = $e['email'];
        $nestedData['category'] = $e['catname'];
        $nestedData['college'] = $e['collegename'];
        $nestedData['courseid'] = $e['course'];
        $nestedData['entrance_examid'] = $e['entrance_exam'];
        $nestedData['rank'] = $e['rank'];
        $nestedData['score'] = $e['score'];
        $nestedData['attended_date'] = $e['attended_date'];
        $nestedData['attended_by'] = $e['attended_by'];
        $nestedData['attended_by_name'] = $e['attended_by_name'];
        $nestedData['course'] = $e['coursename'];
        $nestedData['entrance_exam'] = $e['examname'];
                $nestedData['collegeid'] = $e['college'];

        $nestedData['is_attended_value'] = ($e['is_attended'] == 1) ? 'Attended' : 'Pending';
				$nestedData['is_attended'] =$e['is_attended'];
        $nestedData['CreatedDate'] = $e['CreatedDate'];

        $datas[] = $nestedData;
      }

      $json_data = array(
        'draw' => intval($data->draw),
        'recordsTotal' => intval($totalData),
        'recordsFiltered' => intval($totalFiltered),
        'data' => $datas
      );

      echo json_encode($json_data);
    } else {
      $response["response_code"] = "500";
      $response["response_message"] = "Data is null";
      echo json_encode($response);
      exit();
    }
    exit;
  }
  public function deleteAdmission()
  {
    $data = json_decode(file_get_contents('php://input'));
    if($this->input->server('REQUEST_METHOD')=='OPTIONS')
    {
      $data['status']='ok';
      echo json_encode($data);exit;
    }
    if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
      $response["response_code"] = "401";
      $response["response_message"] = "Unauthorized";
      echo json_encode($response);
      exit();
    }
     $headers = apache_request_headers();
     $token = str_replace("Bearer ", "", $headers['Authorization']);
     $kunci = $this->config->item('jwt_key');
     $userData = JWT::decode($token, $kunci);
     Utility::validateSession($userData->iat,$userData->exp);
     $tokenSession = Utility::tokenSession($userData);
    if($data)
    {
      $Id = $data->id;
      $Arr = ['is_deleted' => 1 ];
      $UserRole = $userData->data->type;
            $userId = $userData->data->userId;
            if($UserRole=='Employee')
            {
              $response["response_code"] = 300;
              $response["response_message"] = "Sorry, you do not have permission to modify predict admission entries.";
              echo json_encode($response);
                  exit();
            }
      $result = $this->PredictAdmission_model->deleteAdmission($Id,$Arr);
      if ($result) {
        $response["response_code"] = "200";
        $response["response_message"] = "Success";
        $response["response_data"] = $result;
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
  }
  
}
