<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Methods: GET, POST');
/**
 * User Controller
 *
 * @category   Controllers
 * @package    Admin
 * @subpackage CollegeType
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    18 JAN 2024
 *
 * Class college type handles all the operations related to displaying list, creating user, update, and delete.
 */

if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}
class CollegeType extends CI_Controller
{
    /**
     * Constructor
     *
     * Loads necessary libraries, helpers, and models for the College Type controller.
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
        $this->load->model("admin/CollegeType_model", "", true);
		$this->load->library('Utility');

    }

	 /**
     	* get server side datatable data of user.
     	*/
		 public function getCollegeTypes()
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
					 0 => 'name',
					 1 => 'status',
				 );
		 
				 $limit = $data->length;
				 $start = ($data->draw - 1) * $limit;
				 $orderColumn = $columns[$data->order[0]->column];
				 $orderDir = $data->order[0]->dir;
				 $totalData = $this->CollegeType_model->countAllClgTypes();
				 $totalFiltered = $totalData;
		 
				 if (!empty($data->search->value)) {
					 $search = $data->search->value;
					 $totalFiltered = $this->CollegeType_model->countFilteredClgTypes($search);
					 $clgTypes = $this->CollegeType_model->getFilteredClgTypes($search, $start, $limit, $orderColumn, $orderDir);

					} else {
					 $clgTypes = $this->CollegeType_model->getAllClgTypes($start, $limit, $orderColumn, $orderDir);
				 }
		 
				 $datas = array();
				 foreach ($clgTypes as $clgTypes) {
					
					 $nestedData = array();
					 $nestedData['id'] = $clgTypes->id;
					 $nestedData['name'] = $clgTypes->name;  
					 $nestedData['status'] = $clgTypes->status; 

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

		 /**
     * insert the details of college types.
     */
    public function insertClgTypeDetails()
    {
        $data = json_decode(file_get_contents('php://input'));
		if($this->input->server('REQUEST_METHOD')=='OPTIONS')
		{
			$data['status']='ok';
			echo json_encode($data);exit;
		}
		if($data)
		{
			$headers = apache_request_headers();
			$token = str_replace("Bearer ", "", $headers['Authorization']);
			$kunci = $this->config->item('jwt_key');
			$userData = JWT::decode($token, $kunci);
			Utility::validateSession($userData->iat,$userData->exp);
        	$tokenSession = Utility::tokenSession($userData);

            $type = $data->type;
            $status = $data->status;

            $Arr = [
                "name" => $type,
                "status" => $status,
            ];
            $checkClgTypeExits = $this->CollegeType_model->checkClgTypeExits($type);
            if ($checkClgTypeExits > 0) {
                $response["response_code"] = 300;
                $response["response_message"] =
                    "The college type you provided is already associated with an existing account.Use another type  to register.";
            } else {
                $result = $this->CollegeType_model->insertClgTypeDetails($Arr);
                if ($result) {
                    $response["response_code"] = "200";
                    $response["response_message"] = "Success";
                    $response["response_data"] = $result;
                } else {
                    $response["response_code"] = "400";
                    $response["response_message"] = "Failed";
                }
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }

        echo json_encode($response);
        exit();
    }

		 /**
     * update the details of college types.
     */
    public function updatetClgTypeDetails()
    {
        $data = json_decode(file_get_contents('php://input'));
		if($this->input->server('REQUEST_METHOD')=='OPTIONS')
		{
			$data['status']='ok';
			echo json_encode($data);exit;
		}
		if($data)
		{
			$headers = apache_request_headers();
			$token = str_replace("Bearer ", "", $headers['Authorization']);
			$kunci = $this->config->item('jwt_key');
			$userData = JWT::decode($token, $kunci);
			Utility::validateSession($userData->iat,$userData->exp);
        	$tokenSession = Utility::tokenSession($userData);

			$id = $data->id;
            $type = $data->type;
            $status = $data->status;

            $Arr = [
                "name" => $type,
                "status" => $status,
            ];
            $checkClgTypeExits = $this->CollegeType_model->checkClgTypeWhileupdate($id,$type);
            if ($checkClgTypeExits > 0) {
                $response["response_code"] = 300;
                $response["response_message"] =
                    "The college type you provided is already associated with an existing account.Use another type  to register.";
            } else {
                $result = $this->CollegeType_model->updateClgTypeDetails($id,$Arr);
                if ($result) {
                    $response["response_code"] = "200";
                    $response["response_message"] = "Success";
                    $response["response_data"] = $result;
                } else {
                    $response["response_code"] = "400";
                    $response["response_message"] = "Failed";
                }
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }

        echo json_encode($response);
        exit();
    }

	/**
     * get the details of college using id.
     */
    public function getClgTypeDetailsById()
    {
        $data = json_decode(file_get_contents('php://input'));
		if($this->input->server('REQUEST_METHOD')=='OPTIONS')
		{
			$data['status']='ok';
			echo json_encode($data);exit;
		}
		if($data)
		{
			$headers = apache_request_headers();
			$token = str_replace("Bearer ", "", $headers['Authorization']);
			$kunci = $this->config->item('jwt_key');
			$userData = JWT::decode($token, $kunci);
			Utility::validateSession($userData->iat,$userData->exp);
        	$tokenSession = Utility::tokenSession($userData);
            $typeId = $data->typeId;
            $result = $this->CollegeType_model->getClgTypeDetailsById($typeId);
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

	 /**
     * delete the details of college type using userId.
     */

	 public function deleteclgTypeDetails()
	 {
		 $data = json_decode(file_get_contents('php://input'));
		 if($this->input->server('REQUEST_METHOD')=='OPTIONS')
		 {
			 $data['status']='ok';
			 echo json_encode($data);exit;
		 }
		 if($data)
		 {
			 $headers = apache_request_headers();
			 $token = str_replace("Bearer ", "", $headers['Authorization']);
			 $kunci = $this->config->item('jwt_key');
			 $userData = JWT::decode($token, $kunci);
			 Utility::validateSession($userData->iat,$userData->exp);
			 $tokenSession = Utility::tokenSession($userData);
 
			 $typeId = $data->typeId;
			 $result = $this->CollegeType_model->deleteclgTypeDetails($typeId);
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

	 public function getCollegeType()
	 {
		$data = json_decode(file_get_contents('php://input'));
		 if($this->input->server('REQUEST_METHOD')=='OPTIONS')
		 {
			 $data['status']='ok';
			 echo json_encode($data);exit;
		 }
			 $headers = apache_request_headers();
			 $token = str_replace("Bearer ", "", $headers['Authorization']);
			 $kunci = $this->config->item('jwt_key');
			 $userData = JWT::decode($token, $kunci);
			 Utility::validateSession($userData->iat,$userData->exp);
			 $tokenSession = Utility::tokenSession($userData);
 
			 $result = $this->CollegeType_model->getCollegeType();
			 if ($result) {
				 $response["response_code"] = "200";
				 $response["response_message"] = "Success";
				 $response["response_data"] = $result;
			 } else {
				 $response["response_code"] = "400";
				 $response["response_message"] = "Failed";
			 }
		 
		 echo json_encode($response);
		 exit();
	 }

	 public function getClgTypes() 
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
			 $search_clgtype = isset($data->search_clgtype)?$data->search_clgtype:'';
			 $result = $this->CollegeType_model->getClgTypes($search_clgtype);
			 if ($result) {
				 $response["response_code"] = "200";
				 $response["response_message"] = "Success";
				 $response["coursedata"] = $result;
			 } else {
				 $response["response_code"] = "400";
				 $response["response_message"] = "Failed";
			 }
		 
		 echo json_encode($response);
		 exit();
	 }
}
