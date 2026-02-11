<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Methods: GET, POST');
/**
 * User Controller
 *
 * @category   Controllers
 * @package    Admin
 * @subpackage User
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    10 JAN 2024
 *
 * Class User handles all the operations related to displaying list, creating user, update, and delete.
 */

if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}

class User extends CI_Controller
{
    /**
     * Constructor
     *
     * Loads necessary libraries, helpers, and models for the User controller.
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
        $this->load->model("admin/Users_model", "", true);
		$this->load->library('Utility');

    }

    /**
     * Get the list of users.
     */
    public function getUserList()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
	
		$headers = apache_request_headers();
		$token = str_replace("Bearer ", "", $headers['Authorization']);
		$kunci = $this->config->item('jwt_key');
		$userData = JWT::decode($token, $kunci);
		Utility::validateSession($userData->iat,$userData->exp);
        $tokenSession = Utility::tokenSession($userData);


        $result = $this->Users_model->getUserList();

        if ($result) {
            $response["response_code"] = 200;
            $response["response_message"] = "Success";
            $response["response_data"] = $result;
        } else {
            $response["response_code"] = 400;
            $response["response_message"] = "Failed";
        }

        echo json_encode($response);
        exit();
    }

    /**
     * insert the details of users.
     */
    public function insertUserDetails()
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
			$UserRole = $userData->data->type;
			$UserStatus = $userData->data->status;
			if($UserRole=='Employee')
			{
				$response["response_code"] = 300;
				$response["response_message"] = "This user does not have access to modify the user.";
				echo json_encode($response);
        		exit();
			}
            $Fname = $data->FirstName;
            $Lname = $data->LastName;
            $PhoneNo = $data->PhoneNumber;
            $Email = $data->Email;
            $UserType = $data->UserType;
            $UserStatus = $data->UserStatus;
            $Password = md5($data->Password);
            $ConfirmPassword = md5($data->ConfirmPassword);
            $ImageName = $data->ImageName;

            $Arr = [
                "f_name" => $Fname,
                "l_name" => $Lname,
                "email" => $Email,
                "password" => $Password,
                "phone" => $PhoneNo,
                "user_type" => $UserType,
                "user_status" => $UserStatus,
                "image" => $ImageName,
            ];
            $checkEmailExits = $this->Users_model->checkEmailExists($Email);
            if ($checkEmailExits > 0) {
                $response["response_code"] = 300;
                $response["response_message"] =
                    "The email address you provided is already associated with an existing account.Use another email address to register.";
            } else {
                $result = $this->Users_model->insertUserDetails($Arr);
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
     * get the details of users using userId.
     */
    public function getUserDetailsById()
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
            $UserId = $data->UserId;
            $result = $this->Users_model->getUserDetailsById($UserId);
            // print_r($result);exit;
            			$result->image = base_url().'uploads/userImage/'.$result->image;

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
     * update the details of user
     * */

    public function updateUserDetails()
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
			$UserRole = $userData->data->type;
			if($UserRole=='Employee')
			{
				$response["response_code"] = 300;
				$response["response_message"] = "This user does not have access to modify the user.";
				echo json_encode($response);
        		exit();
			}
            $UserId = $data->UserId;
            $Fname = $data->FirstName;
            $Lname = $data->LastName;
            $PhoneNo = $data->PhoneNumber;
            $Email = $data->Email;
            $UserType = $data->UserType;
            $UserStatus = $data->UserStatus;
            // $Password = md5($data->Password);
            // $ConfirmPassword = md5($data->ConfirmPassword);
            $ImageName = $data->ImageName;

            $Arr = [
                "f_name" => $Fname,
                "l_name" => $Lname,
                "email" => $Email,
                // "password" => $Password,
                "phone" => $PhoneNo,
                "user_type" => $UserType,
                "user_status" => $UserStatus,
                "image" => $ImageName,
            ];
            $result = $this->Users_model->updateUserDetails($UserId, $Arr);
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
     * delete the details of users using userId.
     */

    public function deleteUserDetails()
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
			$UserRole = $userData->data->type;
			if($UserRole=='Employee')
			{
				$response["response_code"] = 300;
				$response["response_message"] = "Sorry, you do not have permission to modify users.";
				echo json_encode($response);
        		exit();
			}

            $UserId = $data->UserId;
            $result = $this->Users_model->deleteUserDetails($UserId);
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
     * upload documents of users.
     */
    public function uploadUserDocs()
    {
        $data = json_decode(file_get_contents('php://input'));
		if ($this->input->server('REQUEST_METHOD') == 'OPTIONS')
		{
		$data["status"] = "ok";
		echo json_encode($data);
		exit;
		}
			$headers = apache_request_headers();
			$token = str_replace("Bearer ", "", $headers['Authorization']);
			$kunci = $this->config->item('jwt_key');
			$userData = JWT::decode($token, $kunci);
			Utility::validateSession($userData->iat,$userData->exp);
        	$tokenSession = Utility::tokenSession($userData);
			
			

		$folder = 'uploads/userImage';
		if(!is_dir($folder)) {
			mkdir($folder, 0777, TRUE);
			}
			if(isset($_FILES["file"]) && $_FILES["file"]["error"] == 0)
			{
				$allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "png" => "image/png", "JPG" => "image/jpeg","JPEG" => "image/jpeg", "PNG" => "image/png", "PDF" =>"application/pdf");
				$filename = $_FILES["file"]["name"];
				$filesize = $_FILES["file"]["size"];
				$file_ext = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
				$ext = pathinfo($filename, PATHINFO_EXTENSION);
				$maxsize = 6 * 1024 * 1024;
				if(!array_key_exists($file_ext, $allowed))
				{
				$response['status'] = 'false';
				$response['response_code'] = 1;
				$response['response_message'] = " Please select a valid file format.";
				}
				else if($filesize > $maxsize)
				{
				$response['status'] = 'false';
				$response['response_code'] = 2;
				$response['response_message'] = "File size is larger than the allowed limit";
				}
				else
				{
				$fileNameFinal = time()."_".$filename."";
				$finalFile = $folder."/". $fileNameFinal;
				$upload = move_uploaded_file($_FILES["file"]["tmp_name"], $finalFile);
				if($upload)
				{	
					$response['File'] = $fileNameFinal;
					$response['FileDir'] = base_url().$finalFile;
					$response["response_code"] = "200";
                	$response["response_message"] = "success";
				}
				}
			}
			else
			{
				$response['status'] = 'false';
				$response['response_code'] = 3;
				$response['response_message'] = "please Upload the image";
			}
		
			echo json_encode($response);exit;
		}

		/**
     	* get list of user type.
     	*/
		 public function getUserTypeList()
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

				 $result = $this->Users_model->getUserTypeList();
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
     	* get server side datatable data of user.
     	*/
		 public function getUsers()
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
					 0 => 'user_status',
					 1 => 'f_name',
					 2 => 'l_name',
					 3 => 'email',
					 4 => 'phone',
					 5 => 'user_type',
					 6 => 'id'
				 );
		 
				 $limit = $data->length;
				 $start = ($data->draw - 1) * $limit;
				 $orderColumn = $columns[$data->order[0]->column];
				 $orderDir = $data->order[0]->dir;
				 $totalData = $this->Users_model->countAllUsers();
				 $totalFiltered = $totalData;
		 
				 if (!empty($data->search->value)) {
					 $search = $data->search->value;
					 $totalFiltered = $this->Users_model->countFilteredUsers($search);
					 $users = $this->Users_model->getFilteredUsers($search, $start, $limit, $orderColumn, $orderDir);

					} else {
					 $users = $this->Users_model->getAllUsers($start, $limit, $orderColumn, $orderDir);
				 }
		 
				 $datas = array();
				 foreach ($users as $user) {
					
					 $nestedData = array();
					 $nestedData['id'] = $user->id;
					 $nestedData['f_name'] = $user->f_name;  
					 $nestedData['l_name'] = $user->l_name; 
					 $nestedData['email'] = $user->email;
					 $nestedData['phone'] = $user->phone;
					 $nestedData['type'] = $user->type;
					 $nestedData['status'] = $user->status;
					 $nestedData['create_date'] = $user->create_date;

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
