<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Methods: GET, POST');
/**
 * Careers Controller
 *
 * @category   Controllers
 * @package    Admin
 * @subpackage Careers
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    25 JAN 2024
 *
 * Class Careers handles all the operations related to displaying list, creating Careers, update, and delete.
 */

if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}
class Careers extends CI_Controller
{
    /**
     * Constructor
     *
     * Loads necessary libraries, helpers, and models for the Careers controller.
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
        $this->load->model("admin/Careers_model", "", true);
		$this->load->library('Utility');

    }

	/*** Get list of Careers */
	public function getCareersList()
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
		if($data)
		{
			

		$headers = apache_request_headers();	
		$token = str_replace("Bearer ", "", $headers['Authorization']);
		$kunci = $this->config->item('jwt_key');
		$userData = JWT::decode($token, $kunci);
		Utility::validateSession($userData->iat,$userData->exp);
		$tokenSession = Utility::tokenSession($userData);

		$columns = array(
			0 => 'id',
			1 => 'create_date'
		);
		$limit = $data->length;
		$start = ($data->draw - 1) * $limit;
		$orderColumn = $columns[$data->order[0]->column];
		$orderDir = $data->order[0]->dir;
		$totalData = $this->Careers_model->countAllCareers();
		$totalFiltered = $totalData;
		if (!empty($data->search->value)) {

			$search = $data->search->value;
			$totalFiltered = $this->Careers_model->countFilteredCareers($search);
			$Careers = $this->Careers_model->getFilteredCareers($search, $start, $limit, $orderColumn, $orderDir);

		   } else {
			$Careers = $this->Careers_model->getAllCareers($start, $limit, $orderColumn, $orderDir);
		}

		$datas = array();
		foreach ($Careers as $blg) {
		   
			$nestedData = array();
			$nestedData['id'] = $blg->id;
			$nestedData['title'] = $blg->title;
			$nestedData['category'] = $blg->category;
			$nestedData['description'] = $blg->description;
			$nestedData['image'] = base_url().'uploads/careers/'.$blg->image;
			$nestedData['status'] = $blg->status;


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


	/*** insert details of Careers */
	public function insertCareersDetails()
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
		$careerName = $data->careerName;
		$description = $data->description;
		$images = $data->image;
		$categoryid = $data->categoryid;
		$status = $data->status;
		$str = preg_replace('/[^A-Za-z0-9\. -]/', ' ', $careerName);
		$slug = preg_replace('/\s+/', '-', strtolower($str));
		$Arr = ['title'=>$careerName,'slug'=>$slug,'description'=>$description,'categoryid'=>$categoryid,'status'=>$status];
		$chkIfExists = $this->Careers_model->chkIfExists($slug);
		if($chkIfExists > 0)
		{
			$response["response_code"] = 300;
			$response["response_message"] = 'Career already exists. Please try another one.';
		}
		else
		{
			$result = $this->Careers_model->insertCareersDetails($Arr);
			if ($result) {

				foreach ($images as $image) {
					if (!empty($image->id)) {
						$updateArr = ['postid' => $result['careerId'], 'type' => 'careers', 'image' => $image->imageName];
						$this->Careers_model->updateCareerDocsDetails($image->id,$result['careerId'], $updateArr);
						$resultArray['imageId'] = $image->id;
					} else {
						$insertArr = ['postid' => $result['careerId'], 'type' => 'careers', 'image' => $image->imageName];
						$result1 = $this->Careers_model->insertCareerDocsDetails($insertArr);
						if ($result1) {
							$resultArray['imageId'] = $result;
						}
					}
				}
			}
				if($resultArray && $result)
				{
				$response["response_code"] = "200";
				$response["response_message"] = "Success";
				$response["response_data"] = $result;
				$response["image_data"] = $resultArray;

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
     * upload documents of careers.
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
			
		$folder = 'uploads/careers';
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
				$filesize = $_FILES["file"]["size"];
				$file_ext = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
				$ext = pathinfo($filename, PATHINFO_EXTENSION);
				$maxsize = 6 * 1024 * 1024; // 6 megabytes in bytes
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


		/*** update details of Careers */
	public function updateCareersDetails()
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
		$id  = $data->id;
		$careerName = $data->careerName;
		$description = $data->description;
		$images = $data->image;
		$categoryid = $data->categoryid;
		$status = $data->status;
		$str = preg_replace('/[^A-Za-z0-9\. -]/', ' ', $careerName);
		$slug = preg_replace('/\s+/', '-', strtolower($str));
		$Arr = ['title'=>$careerName,'slug'=>$slug,'description'=>$description,'categoryid'=>$categoryid,'status'=>$status];
		$chkIfExists = $this->Careers_model->chkWhileUpdate($id,$slug);
		if($chkIfExists > 0)
		{ 	
			$response["response_code"] = 300;
			$response["response_message"] = 'Career already exists. Please try another one.';
		}
		else
		{
			$result = $this->Careers_model->updateCareersDetails($id,$Arr);
			
			if ($result) {

				foreach ($images as $image) {
					if (!empty($image->id)) {
						$updateArr = ['postid' => $id, 'type' => 'careers', 'image' => $image->imageName];
						$this->Careers_model->updateCareerDocsDetails($image->id,$id, $updateArr);
						$resultArray['imageId'] = $image->id;
					} else {
						$insertArr = ['postid' => $id, 'type' => 'careers', 'image' => $image->imageName];
						$result1 = $this->Careers_model->insertCareerDocsDetails($insertArr);
						if ($result1) {
							$resultArray['imageId'] = $result;
						}
					}
				}

			}
			if($resultArray && $result)
			{
			$response["response_code"] = "200";
			$response["response_message"] = "Success";
			$response["response_data"] = $result;
			$response["image_data"] = $resultArray;

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

	public function deleteDoc()
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
		if($data)
		{
			$headers = apache_request_headers();
			$token = str_replace("Bearer ", "", $headers['Authorization']);
			$kunci = $this->config->item('jwt_key');
			$userData = JWT::decode($token, $kunci);
			Utility::validateSession($userData->iat,$userData->exp);
			$tokenSession = Utility::tokenSession($userData);

			$Id = $data->imageId;
			$result = $this->Careers_model->deleteDoc($Id);
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
     * delete the details of Careers using state id.
     */

	 public function deleteCareers()
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
		 if($data)
		 {
			 $headers = apache_request_headers();
			 $token = str_replace("Bearer ", "", $headers['Authorization']);
			 $kunci = $this->config->item('jwt_key');
			 $userData = JWT::decode($token, $kunci);
			 Utility::validateSession($userData->iat,$userData->exp);
			 $tokenSession = Utility::tokenSession($userData);
 
			 $Id = $data->id;
			 $result = $this->Careers_model->deleteCareers($Id);
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
     * get the details of Careers using id.
     */
    public function getCareersDetailsById()
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
		if($data)
		{
			$headers = apache_request_headers();
			$token = str_replace("Bearer ", "", $headers['Authorization']);
			$kunci = $this->config->item('jwt_key');
			$userData = JWT::decode($token, $kunci);
			Utility::validateSession($userData->iat,$userData->exp);
        	$tokenSession = Utility::tokenSession($userData);
            $Id = $data->id;
            $result = $this->Careers_model->getCareersDetailsById($Id);
			$result1 = $this->Careers_model->getCareersImageByClgId($Id);

			foreach ($result1 as $key => $img) {
				$result1[$key]->imageName = $img->image;
				$result1[$key]->image = base_url().'/uploads/careers/'.$img->image;

				
			}
			

            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["response_data"] = $result;
				$response["image_data"] = $result1;


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
