<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Methods: GET, POST');
/**
 * Certificate Controller
 *
 * @category   Controllers
 * @package    Admin
 * @subpackage Certificate
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    25 JAN 2024
 *
 * Class Certificate handles all the operations related to displaying list, creating Certificate, update, and delete.
 */

if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}
date_default_timezone_set('Asia/Kolkata');

class Certification extends CI_Controller
{
    /**
     * Constructor
     *
     * Loads necessary libraries, helpers, and models for the Certificate controller.
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
        $this->load->model("admin/Certification_model", "", true);
		$this->load->model("admin/Common_model", "", true);
		$this->load->library('Utility');

    }

    	/*** Get list of Certificate */
	public function getCertificateList()
	{
		//echo "test";exit;
		$data = json_decode(file_get_contents('php://input'));
		 
		if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
			$data->status = 'ok';
			echo json_encode($data);
			exit;
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
			1 => 'name',
			2 => 'category'
		);
		$limit = $data->length;
		$start = ($data->draw - 1) * $limit;
		$orderColumn = $columns[$data->order[0]->column];
		$orderDir = $data->order[0]->dir;
		$totalData = $this->Certification_model->countAllCertificate();
		$totalFiltered = $totalData;
		if (!empty($data->search->value)) {

			$search = $data->search->value;
			$totalFiltered = $this->Certification_model->countFilteredCertificates($search);
			$Certificate = $this->Certification_model->getFilteredCertificate($search, $start, $limit, $orderColumn, $orderDir);

		   } else {
			$Certificate = $this->Certification_model->getAllCertificate($start, $limit, $orderColumn, $orderDir);
		}

		$datas = array();
		foreach ($Certificate as $blg) {
		   
			$nestedData = array();
			$nestedData['id'] = $blg->id;
			$nestedData['name'] = $blg->name;
			$nestedData['category'] = $blg->categoryName;
			$nestedData['image'] = base_url().'uploads/certificate/'.$blg->image;
			$nestedData['status'] = $blg->status;
			// $nestedData['descritpion'] = $blg->descritpion;
			$nestedData['created_by'] = $blg->created_by;
			$nestedData['created_by_name'] = $blg->created_by_name;
			$nestedData['created_date'] = $blg->created_date;
			$nestedData['updated_by'] = $blg->created_by;
			$nestedData['updated_by_name'] = $blg->updated_by_name;
			$nestedData['updated_date'] = $blg->updated_date;
            $nestedData['categoryid']  = $blg->category;
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
     * upload documents of Certificate.
     */
    public function uploadDocs()
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
			
		$folder = 'uploads/certificate';
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
					
				);
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
                	$response["response_message"] = "Success";
				}
				}
			}
			else
			{
				$response['status'] = 'false';
				$response['response_code'] = 3;
				$response['response_message'] = "Please upload the image";
			}
		
			echo json_encode($response);exit;
		}

        /*** insert details of Certificate */
	public function insertcertifiateDetails()
	{
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
		if($data)
		{
		$created_by = $userData->data->userId;
		$userType = $userData->data->user_type;

		$categoryid = $data->categoryid;
		$title = $data->name;
		$image = $data->image;
		$description = $data->description;
		$college_id = $data->college_id;
		$status = $data->status;
		$Arr = ['created_by'=>$created_by,'created_date'=>date('Y-m-d H:i:s'),'category'=>$categoryid,'name'=>$title,'image'=>$image,'descritpion'=>$description,'status'=>$status,'college'=>$college_id];
		$chkIfExists = $this->Certification_model->chkIfExists($title);
		if($chkIfExists > 0)
		{
			$response["response_code"] = 300;
			$response["response_message"] = 'Certification course already exists. Please try another one.';
		}
		else
		{
			$result = $this->Certification_model->insertcertifiateDetails($Arr);

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

    /*** update details of Certificate */
	public function updateCertificateDetails()
	{
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
		if($data)
		{
		$updated_by = $userData->data->userId;
		$id = $data->id;
		$categoryid = $data->categoryid;
		$title = $data->name;
		$image = $data->image;
		$description = $data->description;
		$college_id = $data->college_id;
		$status = $data->status;
		$updated_date = date('Y-m-d H:i:s');

		$Arr = ['updated_date'=>date('Y-m-d H:i:s'),'updated_by'=>$updated_by, 'category'=>$categoryid,'name'=>$title,'image'=>$image,'descritpion'=>$description,'status'=>$status,'college'=>$college_id];
		$chkIfExists = $this->Certification_model->chkWhileUpdate($title,$id);
		if($chkIfExists > 0)
		{
			$response["response_code"] = 300;
			$response["response_message"] = 'Certification course already exists. Please try another one.';
		}
		else
		{
			$result = $this->Certification_model->updateCertificateDetails($id,$Arr);
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
     * get the details of Certificate using Certificate id.
     */
    public function getCertificateDetailsById()
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
            $Id = $data->id;
            $result = $this->Certification_model->getCertificateDetailsById($Id);
			$result->imagepath = base_url().'/uploads/certificate/'.$result->image;

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
     * delete the details of Certificate using Certificate id.
     */

	 public function deleteCertificate()
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
 
			 $Id = $data->id;
			 $userId = $userData->data->userId;
		    $userType = $userData->data->user_type;
		    $UserRole = $userData->data->type;
		if(strtoupper($UserRole)=='EMPLOYEE' && $Id != $userId)
		{
			$response["response_code"] = 300;
			$response["response_message"] = "Sorry, you do not have permission to modify the Certificates";
			echo json_encode($response);
			exit();
		}
			 $result = $this->Certification_model->deleteCertificate($Id);
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
