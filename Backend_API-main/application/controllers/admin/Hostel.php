<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Methods: GET, POST');
/**
 * Hostel Controller
 *
 * @category   Controllers
 * @package    Admin
 * @subpackage Hostel
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    23 JAN 2025
 *
 * Class Hostel handles all the operations related to displaying list, creating Hostel, update, and delete.
 */

if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}
date_default_timezone_set('Asia/Kolkata');

class Hostel extends CI_Controller
{
    /**
     * Constructor
     *
     * Loads necessary libraries, helpers, and models for the Hostel controller.
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
        $this->load->model("admin/Hostel_model", "", true);
		$this->load->model("admin/Common_model", "", true);
		$this->load->library('Utility');

    }
    
    /*** Get list of Hostel */
	public function getHostelList()
	{
		// echo "test";exit;
		$data = json_decode(file_get_contents('php://input'));
		 
		if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
			$data->status = 'ok';
			echo json_encode($data);
			exit;
		}
		if (empty($_SERVER['HTTP_AUTHORIZATION'])) {
			$response["response_code"] = "401";
			$response["response_message"] = "Unauthorized";
			echo json_encode($response);
			exit();
		}
		if($data)
		{
		$headers = apache_request_headers();	
		$token = str_replace("Bearer ", "", $headers['Authorization']);
		$kunci = $this->config->item('jwt_key');
		$userData = JWT::decode($token, $kunci);
		Utility::validateSession($userData->iat,$userData->exp);
		$tokenSession = Utility::tokenSession($userData);
		$userId = $userData->data->userId;
		$userType = $userData->data->user_type;
		$UserRole = $userData->data->type;
		
		$columns = array(
			0 => 'id',
			1 => 'name'
		);
		$limit = $data->length;
	//	print_r($limit);exit;
		$start = ($data->draw - 1) * $limit;
		$orderColumn = $columns[$data->order[0]->column];
		$orderDir = $data->order[0]->dir;
	//	$collegeId = $data->collegeId;
		$totalData = $this->Hostel_model->countAll($userId,$userType);
		$totalFiltered = $totalData;
		if (!empty($data->search->value)) {

			$search = $data->search->value;
			$totalFiltered = $this->Hostel_model->countFiltered($search,$userId,$userType);
			$Hostel = $this->Hostel_model->getFiltered($search, $start, $limit, $orderColumn, $orderDir,$userId,$userType);

		   } else {
			$Hostel = $this->Hostel_model->getAll($start, $limit, $orderColumn, $orderDir,$userId,$userType);
			  
			 
		//	print_r($Hostel);exit;
		}

	   $datas = [];
		foreach ($Hostel as $hstl) {

		
			$nestedData = array();
			$nestedData['id'] = $hstl->id;
			$nestedData['title'] = $hstl->name;
			$nestedData['image'] = base_url().'uploads/Hostels/'.$hstl->image;
			$nestedData['status'] = $hstl->status;
			$nestedData['college_name'] = $hstl->college_name;
			$nestedData['created_by'] = $hstl->created_by;
			$nestedData['college_id'] = $hstl->collegeid;
			$nestedData['created_date'] = $hstl->created_date;
			$nestedData['updated_by'] = $hstl->created_by;
		//	$nestedData['updated_by_name'] = $hstl->updated_by_name;
			$nestedData['updated_date'] = $hstl->updated_date;

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
   
	}
	
	/*  public function getHostelList()
    {
        $data = json_decode(file_get_contents('php://input'));
		if($this->input->server('REQUEST_METHOD')=='OPTIONS')
		{
			$data['status']='ok';
			echo json_encode($data);exit;
		}
		if (empty($_SERVER['HTTP_AUTHORIZATION'])) {
			$response["response_code"] = "401";
			$response["response_message"] = "Unauthorized";
			echo json_encode($response);
			exit();
		}
		if($data)
		{
			$headers = apache_request_headers();
			$token = str_replace("Bearer ", "", $headers['Authorization']);
			$kunci = $this->config->item('jwt_key');
			$userData = JWT::decode($token, $kunci);
			Utility::validateSession($userData->iat,$userData->exp);
        	$tokenSession = Utility::tokenSession($userData);
            $collegeId = $data->collegeId;
               
            $result = $this->Hostel_model->getAll($collegeId);
            //print_r($result[0]);exit;
            
         // print_r($result);exit;
				$result->imagepath = base_url().'/uploads/Hostels/'.$result->image;

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
    }*/
	
	/*** insert details of Hostel */
	public function insertHostelDetails()
	{
		$data = json_decode(file_get_contents('php://input'));
		 
		if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
			$data->status = 'ok';
			echo json_encode($data);
			exit;
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
		$created_by = $userData->data->userId;
		$userType = $userData->data->user_type;
		$title = $data->title;
		$image = $data->image;
		$description = $data->description;
		$college_id = isset($data->college_id)?$data->college_id:'';
	//	print_r($college_id);exit;
		$created_date = date('Y-m-d');
		$updated_date = date('Y-m-d H:i:s');

		$status = $data->status;
		$Arr = ['created_by'=>$created_by,'name'=>$title,'image'=>$image,'description'=>$description,'status'=>$status,'collegeid'=>$college_id];
		$chkIfExists = $this->Hostel_model->chkIfExists($title);
		if($chkIfExists > 0)
		{
			$response["response_code"] = 300;
			$response["response_message"] = 'Hostels is already exists.Please try another one.';
		}
		else
		{
			$result = $this->Hostel_model->insertHostelDetails($Arr);
			

			if ($result) {
				$response["response_code"] = "200";
				$response["response_message"] = "Success";
				$response["response_data"] = $result;
			} else {
				$response["response_code"] = "400";
				$response["response_message"] = "Failed";
			}
		}
		}
		else{
			$response["response_code"] = "500";
			$response["response_message"] = "Data is null";
			echo json_encode($response);
			exit();
		 }
		echo json_encode($response);exit;
	}

	/*** update details of Hostel */
	public function updateHostelDetails()
	{
	    // echo "tttt";exit;
		$data = json_decode(file_get_contents('php://input'));
		 
		if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
			$data->status = 'ok';
			echo json_encode($data);
			exit;
		}
		if (empty($_SERVER['HTTP_AUTHORIZATION'])) {
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
		$updated_by = $userData->data->userId;
		$id = $data->id;
		$title = $data->title;
		$image = $data->image;
		$description = $data->description;
		$college_id = $data->college_id;
		$status = $data->status;
		$updated_date = date('Y-m-d H:i:s');

		$Arr = ['updated_date'=>date('Y-m-d H:i:s'),'updated_by'=>$updated_by,'name'=>$title,'image'=>$image,'description'=>$description,'status'=>$status,'collegeid'=>$college_id];
		$chkIfExists = $this->Hostel_model->chkWhileUpdate($title,$id);
		if($chkIfExists > 0)
		{
			$response["response_code"] = 300;
			$response["response_message"] = 'Hostel is already exists.Please try another one.';
		}
		else
		{
			$result = $this->Hostel_model->updateHostelDetails($id,$Arr);
			if ($result) {
				$response["response_code"] = "200";
				$response["response_message"] = "Success";
				$response["response_data"] = $result;
			} else {
				$response["response_code"] = "400";
				$response["response_message"] = "Failed";
			}
		}
		}
		else{
			$response["response_code"] = "500";
			$response["response_message"] = "Data is null";
			echo json_encode($response);
			exit();
		 }
		echo json_encode($response);exit;
	}
	/**
     * get the details of Hostel using Hostel id.
     */
    public function getHostelDetailsById()
    {
        $data = json_decode(file_get_contents('php://input'));
		if($this->input->server('REQUEST_METHOD')=='OPTIONS')
		{
			$data['status']='ok';
			echo json_encode($data);exit;
		}
		if (empty($_SERVER['HTTP_AUTHORIZATION'])) {
			$response["response_code"] = "401";
			$response["response_message"] = "Unauthorized";
			echo json_encode($response);
			exit();
		}
		if($data)
		{
			$headers = apache_request_headers();
			$token = str_replace("Bearer ", "", $headers['Authorization']);
			$kunci = $this->config->item('jwt_key');
			$userData = JWT::decode($token, $kunci);
			Utility::validateSession($userData->iat,$userData->exp);
        	$tokenSession = Utility::tokenSession($userData);
            $Id = $data->id;
               
            $result = $this->Hostel_model->getHostelDetailsById($Id);
            
         // print_r($result);exit;
			$result->imagepath = base_url().'/uploads/Hostels/'.$result->image;

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
     * delete the details of Hostel using Hostel id.
     */

	 public function deleteHostel()
	 {
		 $data = json_decode(file_get_contents('php://input'));
		 if($this->input->server('REQUEST_METHOD')=='OPTIONS')
		 {
			 $data['status']='ok';
			 echo json_encode($data);exit;
		 }
		 if (empty($_SERVER['HTTP_AUTHORIZATION'])) {
			$response["response_code"] = "401";
			$response["response_message"] = "Unauthorized";
			echo json_encode($response);
			exit();
		}
		 if($data)
		 {
			 $headers = apache_request_headers();
			 $token = str_replace("Bearer ", "", $headers['Authorization']);
			 $kunci = $this->config->item('jwt_key');
			 $userData = JWT::decode($token, $kunci);
			 Utility::validateSession($userData->iat,$userData->exp);
			 $tokenSession = Utility::tokenSession($userData);
 
			 $Id = $data->id;
			 $userId = $userData->data->userId;
	//	$userType = $userData->data->user_type;
		$UserRole = $userData->data->type;
		if(strtoupper($UserRole)=='EMPLOYEE' && $Id != $userId)
		{
			$response["response_code"] = 300;
			$response["response_message"] = "Sorry, you do not have permission to modify the Hostels";
			echo json_encode($response);
			exit();
		}
			 $result = $this->Hostel_model->deleteHostel($Id);
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
     * upload documents of Hostel.
     */
    public function uploadDocs()
    {
        // echo "tttt";exit;
        $data = json_decode(file_get_contents('php://input'));
		if ($this->input->server('REQUEST_METHOD') == 'OPTIONS')
		{
		$data["status"] = "ok";
		echo json_encode($data);
		exit;
		}
		if (empty($_SERVER['HTTP_AUTHORIZATION'])) {
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
			
		$folder = 'uploads/Hostels';
		if(!is_dir($folder)) {
			mkdir($folder, 0777, TRUE);
			}
			if(isset($_FILES["file"]) && $_FILES["file"]["error"] == 0)
			{
				$allowed = array(
					"jpg" => "image/jpg",
					"jpeg" => "image/jpeg",
					"png" => "image/png",
					"JPG" => "image/jpeg",
					"JPEG" => "image/jpeg",
					"PNG" => "image/png",
					"PDF" => "application/pdf",
					"doc" => "application/msword", // DOC
					"csv" => "text/csv",           // CSV
					"xls" => "application/vnd.ms-excel", // XLS
					"xlsx" => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" // XLSX
				);
				$filename = $_FILES["file"]["name"];
			//	print_r($_FILES["file"]);exit;
				$filesize = $_FILES["file"]["size"];
				$file_ext = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
				$ext = pathinfo($filename, PATHINFO_EXTENSION);
				$maxsize = 1100 * 500;
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
		
		 public function uploadDescriptionDocs()
    {
        // echo "tttt";exit;
        $data = json_decode(file_get_contents('php://input'));
		if ($this->input->server('REQUEST_METHOD') == 'OPTIONS')
		{
		$data["status"] = "ok";
		echo json_encode($data);
		exit;
		}
		if (empty($_SERVER['HTTP_AUTHORIZATION'])) {
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
			
		$folder = 'uploads/Hostels';
		if(!is_dir($folder)) {
			mkdir($folder, 0777, TRUE);
			}
			if(isset($_FILES["file"]) && $_FILES["file"]["error"] == 0)
			{
				$allowed = array(
					"jpg" => "image/jpg",
					"jpeg" => "image/jpeg",
					"png" => "image/png",
					"JPG" => "image/jpeg",
					"JPEG" => "image/jpeg",
					"PNG" => "image/png",
					"PDF" => "application/pdf",
					"doc" => "application/msword", // DOC
					"csv" => "text/csv",           // CSV
					"xls" => "application/vnd.ms-excel", // XLS
					"xlsx" => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" // XLSX
				);
				$filename = $_FILES["file"]["name"];
			//	print_r($_FILES["file"]);exit;
				$filesize = $_FILES["file"]["size"];
				$file_ext = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
				$ext = pathinfo($filename, PATHINFO_EXTENSION);
				$maxsize = 1100 * 500;
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
		
		
		 public function getHostelData()
	 {
		 $data = json_decode(file_get_contents('php://input'));
		 if($this->input->server('REQUEST_METHOD')=='OPTIONS')
		 {
			 $data['status']='ok';
			 echo json_encode($data);exit;
		 }
		 if (empty($_SERVER['HTTP_AUTHORIZATION'])) {
			$response["response_code"] = "401";
			$response["response_message"] = "Unauthorized";
			echo json_encode($response);
			exit();
		}
		 if($data)
		 {
			 $headers = apache_request_headers();
			 $token = str_replace("Bearer ", "", $headers['Authorization']);
			 $kunci = $this->config->item('jwt_key');
			 $userData = JWT::decode($token, $kunci);
			 Utility::validateSession($userData->iat,$userData->exp);
			 $tokenSession = Utility::tokenSession($userData);
 
			 $Id = $data->id;
		//	 $userId = $userData->data->userId;
	//	$userType = $userData->data->user_type;
		$UserRole = $userData->data->type;
		if(strtoupper($UserRole)=='EMPLOYEE' && $Id != $userId)
		{
			$response["response_code"] = 300;
			$response["response_message"] = "Sorry, you do not have permission to modify the Hostels";
			echo json_encode($response);
			exit();
		}
		
		  $result = $this->Hostel_model->getHostelData($Id);
            
         // print_r($result);exit;
			$result->imagepath = base_url().'/uploads/Hostels/'.$result->image;
			
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


