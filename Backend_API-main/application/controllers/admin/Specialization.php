<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Methods: GET, POST');
/**
 * Specialization Controller
 *
 * @category   Controllers
 * @package    Admin
 * @subpackage Specialization
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    26 JAN 2024
 *
 * Class Specialization handles all the operations related to displaying list, creating Specialization, update, and delete.
 */

if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}

class Specialization extends CI_Controller
{
    /**
     * Constructor
     *
     * Loads necessary libraries, helpers, and models for the Specialization controller.
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
        // Load necessary models and library
        $this->load->model("admin/Common_model", "", true);
        $this->load->library('Utility');
    }
    
    /**
     * saveSpecialization function
     * 
     * This function handles the saving of specialization data.
     */
    public function saveSpecialization()
    {
        // Decode JSON data received from request
        $data = json_decode(file_get_contents('php://input'));
        
        // Check if the request method is OPTIONS
        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data['status'] = 'ok';
            echo json_encode($data);
            exit;
        }

        // Check if Authorization header is set
        if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $response["response_code"] = "401";
            $response["response_message"] = "Unauthorized";
            echo json_encode($response);
            exit();
        }

        // Validate user session using JWT token
        if ($data) {
            $headers = apache_request_headers();
            $token = str_replace("Bearer ", "", $headers['Authorization']);
            $kunci = $this->config->item('jwt_key');
            $userData = JWT::decode($token, $kunci);
            
            // Validate session
            Utility::validateSession($userData->iat, $userData->exp);
            $tokenSession = Utility::tokenSession($userData);

            // Extract necessary data from request
            $name = $data->name;
            $status = $data->status;
            $userid = $userData->data->userId;

            // Prepare data array for saving
            $Arr = ['name' => $name, 'status' => $status, 'created_by' => $userid];

            // Check if specialization already exists
            $chkIsExists = $this->Common_model->chkIsSpecExists($name);
            if ($chkIsExists > 0) {
                $response["response_code"] = "301";
                $response["response_message"] = "The Specialization already exists. Please try using different data.";
                echo json_encode($response);
                exit;
            } else {
                // Save specialization data
                $result = $this->Common_model->saveSpecialization($Arr);
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
            // Handle case when no data is received
			$response['status'] = 'false';
            $response['response_code'] = 500;
            $response['response_message'] = "data is null.";
        }
        
        // Send JSON response
        echo json_encode($response);
        exit;
    }

	/**
     * updateSpecializayion function
     * 
     * This function handles the updating of specialization data.
     */
    public function updateSpecialization()
    {
        // Decode JSON data received from request
        $data = json_decode(file_get_contents('php://input'));
        
        // Check if the request method is OPTIONS
        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data['status'] = 'ok';
            echo json_encode($data);
            exit;
        }

        // Check if Authorization header is set
        if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $response["response_code"] = "401";
            $response["response_message"] = "Unauthorized";
            echo json_encode($response);
            exit();
        }

        // Validate user session using JWT token
        if ($data) {
            $headers = apache_request_headers();
            $token = str_replace("Bearer ", "", $headers['Authorization']);
            $kunci = $this->config->item('jwt_key');
            $userData = JWT::decode($token, $kunci);
            
            // Validate session
            Utility::validateSession($userData->iat, $userData->exp);
            $tokenSession = Utility::tokenSession($userData);

            // Extract necessary data from request
			$id = $data->id;
            $name = $data->name;
            $status = $data->status;
            $userid = $userData->data->userId;
			$updated_date = date('Y-m-d H:i:s');

            // Prepare data array for saving
            $Arr = ['name' => $name, 'status' => $status, 'updated_by' => $userid,'updated_date'=>date('Y-m-d H:i:s')];

            // Check if specialization already exists
            $chkIsExists = $this->Common_model->chkIsSpecExistsWhileUpdate($id,$name);
            if ($chkIsExists > 0) {
                $response["response_code"] = "301";
                $response["response_message"] = "The Specialization already exists. Please try using different data.";
                echo json_encode($response);
                exit;
            } else {
                // Save specialization data
                $result = $this->Common_model->updateSpecialization($Arr,$id);
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
            // Handle case when no data is received
			$response['status'] = 'false';
            $response['response_code'] = 500;
            $response['response_message'] = "data is null.";
        }
        
        // Send JSON response
        echo json_encode($response);
        exit;
    }


	public function getSpecializationById()
    {
        // Decode JSON data received from request
        $data = json_decode(file_get_contents('php://input'));
        
        // Check if the request method is OPTIONS
        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data['status'] = 'ok';
            echo json_encode($data);
            exit;
        }

        // Check if Authorization header is set
        if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $response["response_code"] = "401";
            $response["response_message"] = "Unauthorized";
            echo json_encode($response);
            exit();
        }

        // Validate user session using JWT token
        if ($data) {
            $headers = apache_request_headers();
            $token = str_replace("Bearer ", "", $headers['Authorization']);
            $kunci = $this->config->item('jwt_key');
            $userData = JWT::decode($token, $kunci);
            
            // Validate session
            Utility::validateSession($userData->iat, $userData->exp);
            $tokenSession = Utility::tokenSession($userData);

            // Extract necessary data from request
			$id = $data->id;
           
                $result = $this->Common_model->getSpecializationById($id);
                if ($result) {
                    $response["response_code"] = "200";
                    $response["response_message"] = "Success";
                    $response["response_data"] = $result;
                } else {
                    $response["response_code"] = "400";
                    $response["response_message"] = "Failed";
                }
            
        } else {
            // Handle case when no data is received
			$response['status'] = 'false';
            $response['response_code'] = 500;
            $response['response_message'] = "data is null.";
        }
        
        // Send JSON response
        echo json_encode($response);
        exit;
    }

	public function deleteSpecialization()
    {
        // Decode JSON data received from request
        $data = json_decode(file_get_contents('php://input'));
        
        // Check if the request method is OPTIONS
        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data['status'] = 'ok';
            echo json_encode($data);
            exit;
        }

        // Check if Authorization header is set
        if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $response["response_code"] = "401";
            $response["response_message"] = "Unauthorized";
            echo json_encode($response);
            exit();
        }

        // Validate user session using JWT token
        if ($data) {
            $headers = apache_request_headers();
            $token = str_replace("Bearer ", "", $headers['Authorization']);
            $kunci = $this->config->item('jwt_key');
            $userData = JWT::decode($token, $kunci);
            
            // Validate session
            Utility::validateSession($userData->iat, $userData->exp);
            $tokenSession = Utility::tokenSession($userData);

            // Extract necessary data from request
			$id = $data->id;
           
                $result = $this->Common_model->deleteSpecialization($id);
                if ($result) {
                    $response["response_code"] = "200";
                    $response["response_message"] = "Success";
                    $response["response_data"] = $result;
                } else {
                    $response["response_code"] = "400";
                    $response["response_message"] = "Failed";
                }
            
        } else {
            // Handle case when no data is received
			$response['status'] = 'false';
            $response['response_code'] = 500;
            $response['response_message'] = "data is null.";
        }
        
        // Send JSON response
        echo json_encode($response);
        exit;
    }


	public function getList()
	{
		$data = json_decode(file_get_contents('php://input'));
		 
			 if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
				 $data->status = 'ok';
				 echo json_encode($data);
				 exit;
			 }
			 
			 if ($data) {
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
				// print_r($userData);exit;
				// $userId = $userData->data->userId;
				// $userType = $userData->data->user_type;
				 $columns = array(
					 0 =>'id',
					 1 => 'name',
					 2 => 'created_date'


				 );
		 
				 $limit = $data->length;
				 $start = ($data->draw - 1) * $limit;
				 $orderColumn = $columns[$data->order[0]->column];
				 $orderDir = $data->order[0]->dir;
				 $totalData = $this->Common_model->countAllSpec();
				 $totalFiltered = $totalData;
		 
				 if (!empty($data->search->value)) {
					 $search = $data->search->value;
					 $totalFiltered = $this->Common_model->countFilteredSpec($search);
					 $SpecList = $this->Common_model->getFilteredSpec($search, $start, $limit, $orderColumn, $orderDir);

					} else {
					 $SpecList = $this->Common_model->getAllSpec($start, $limit, $orderColumn, $orderDir);
				 }
		 
                //  print_r($SpecList);exit;
				 $datas = array();
				 foreach ($SpecList as $spec) {
					
					 $nestedData = array();
					 $nestedData['id'] = $spec->id;
					 $nestedData['name'] = $spec->name;  
					 $nestedData['status'] = $spec->status;
					 $nestedData['create_date'] = $spec->created_date;
					 $nestedData['created_by'] = $spec->created_by;
					 $nestedData['created_by_name'] = $spec->created_by_name;
					 $nestedData['updated_date'] = $spec->updated_date;
					 $nestedData['updated_by'] = $spec->updated_by;
					 $nestedData['updated_by_name'] = $spec->updated_by_name;


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
}
?>
