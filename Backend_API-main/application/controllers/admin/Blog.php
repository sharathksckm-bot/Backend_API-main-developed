<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Methods: GET, POST');
/**
 * Blog Controller
 *
 * @category   Controllers
 * @package    Admin
 * @subpackage Blog
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    25 JAN 2024
 *
 * Class Blog handles all the operations related to displaying list, creating Blog, update, and delete.
 */

if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}
date_default_timezone_set('Asia/Kolkata');

class Blog extends CI_Controller
{
    /**
     * Constructor
     *
     * Loads necessary libraries, helpers, and models for the Blog controller.
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
        $this->load->model("admin/Blog_model", "", true);
		$this->load->model("admin/Common_model", "", true);
		$this->load->library('Utility');

    }

	/*** Get list of Blog */
	public function getBlogList()
	{
		//echo "test";exit;
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
			1 => 'title',
			2 => 'category'
		);
		$limit = $data->length;
		$start = ($data->draw - 1) * $limit;
		$orderColumn = $columns[$data->order[0]->column];
		$orderDir = $data->order[0]->dir;
		$totalData = $this->Blog_model->countAllBlog($userId,$userType);
		$totalFiltered = $totalData;
		if (!empty($data->search->value)) {

			$search = $data->search->value;
			$totalFiltered = $this->Blog_model->countFilteredBlogs($search,$userId,$userType);
			$Blog = $this->Blog_model->getFilteredBlog($search, $start, $limit, $orderColumn, $orderDir,$userId,$userType);

		   } else {
			$Blog = $this->Blog_model->getAllBlog($start, $limit, $orderColumn, $orderDir,$userId,$userType);
		}

		$datas = array();
		foreach ($Blog as $blg) {
		   
			$nestedData = array();
			$nestedData['id'] = $blg->id;
			$nestedData['title'] = $blg->title;
			$nestedData['subtitle'] = $blg->subtitle;
			$nestedData['category'] = $blg->category;
			$nestedData['image'] = base_url().'uploads/blogs/'.$blg->image;
			$nestedData['status'] = $blg->t_status;
			$nestedData['views'] = $blg->views;
			$nestedData['created_by'] = $blg->created_by;
			$nestedData['created_by_name'] = $blg->created_by_name;
			$nestedData['created_date'] = $blg->created_date;
			$nestedData['updated_by'] = $blg->created_by;
			$nestedData['updated_by_name'] = $blg->updated_by_name;
			$nestedData['updated_date'] = $blg->updated_date;

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

/*** insert details of Blog */
	public function insertBlogDetails()
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
		// print_r($userData);exit;
		$categoryid = $data->categoryid;
		$title = $data->title;
		$subTitle = $data->subTitle;
		$image = $data->image;
		$description = $data->description;
		$exam_id = $data->exam_id;
		$college_id = isset($data->college_id)?$data->college_id:'';
		$created_date = date('Y-m-d');
		$updated_date = date('Y-m-d H:i:s');

		$status = $data->status;
		$Arr = ['created_by'=>$created_by,'categoryid'=>$categoryid,'title'=>$title,'subtitle'=>$subTitle,'image'=>$image,'description'=>$description,'t_status'=>$status,'exam_id'=>$exam_id,'college_id'=>$college_id];
		$chkIfExists = $this->Blog_model->chkIfExists($title);
		if($chkIfExists > 0)
		{
			$response["response_code"] = 300;
			$response["response_message"] = 'This article already exists. Please try another one.';
		}
		else
		{
			$result = $this->Blog_model->insertBlogDetails($Arr);
			
			$checkTeamReport = $this->Common_model->checkTeamReport($created_by,$userType,$created_date);
		
			$TeamArr = ['userid'=>$created_by,'usertype'=>$userType,'no_of_colleges_added'=>0,'no_of_exams_added'=>0,'no_of_events_added'=>0,'no_of_articles_added'=>1,'updated_date'=>date('Y-m-d H:i:s')];
				
			if($checkTeamReport > 0)
			{
			 $updateTeamReport = $this->Common_model->updateTeamReport($created_by,$TeamArr,$created_date);

			}
			else
			{
			$saveTeamReport = $this->Common_model->saveTeamReport($TeamArr);
			}
            if(!empty($college_id)){
			$ClgRepArr = ['college'=>$college_id,'no_of_articles_linked'=>1,'no_of_brochures_download'=>0,'no_of_application_submitted'=>0,'no_of_que_asked'=>0,'no_of_answeres'=>0,'no_of_review'=>0];
			$checkcollegeReport = $this->Common_model->checkcollegeReport($college_id);
			 
			if($checkcollegeReport > 0)
			{
			 $updateClgReport = $this->Common_model->updateClgReport($college_id,$ClgRepArr);

			}
			else
			{

			$saveClgReport = $this->Common_model->saveClgReport($ClgRepArr);
			//print_r($saveClgReport);exit;
			}
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
		}
		else{
			$response["response_code"] = "500";
			$response["response_message"] = "Data is null";
			echo json_encode($response);
			exit();
		 }
		echo json_encode($response);exit;
	}

	/*** update details of Blog */
	public function updateBlogDetails()
	{
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
		$categoryid = $data->categoryid;
		$title = $data->title;
				$subTitle = $data->subTitle;

		$image = $data->image;
		$description = $data->description;
		$exam_id = isset($data->exam_id)?$data->exam_id:'';
		$college_id = $data->college_id;
		$status = $data->status;
		$updated_date = date('Y-m-d H:i:s');

		$Arr = ['updated_date'=>date('Y-m-d H:i:s'),'updated_by'=>$updated_by, 'categoryid'=>$categoryid,'title'=>$title,'subtitle'=>$subTitle,'image'=>$image,'description'=>$description,'t_status'=>$status,'exam_id'=>$exam_id,'college_id'=>$college_id];
		$chkIfExists = $this->Blog_model->chkWhileUpdate($title,$id);
		if($chkIfExists > 0)
		{
			$response["response_code"] = 300;
			$response["response_message"] = 'This article already exists. Please try another one.';
		}
		else
		{
			$result = $this->Blog_model->updateBlogDetails($id,$Arr);
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
     * get the details of Blog using Blog id.
     */
    public function getBlogDetailsById()
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
            $result = $this->Blog_model->getBlogDetailsById($Id);
			$result->imagepath = base_url().'/uploads/blogs/'.$result->image;

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
     * delete the details of Blog using Blog id.
     */

	 public function deleteBlog()
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
		$userType = $userData->data->user_type;
		$UserRole = $userData->data->type;
		if(strtoupper($UserRole)=='EMPLOYEE' && $Id != $userId)
		{
			$response["response_code"] = 300;
			$response["response_message"] = "Sorry, you do not have permission to modify the articles.";
			echo json_encode($response);
			exit();
		}
			 $result = $this->Blog_model->deleteBlog($Id);
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
     * upload documents of Blog.
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
			
		$folder = 'uploads/blogs';
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
				$response['response_message'] = "Please upload the image";
			}
		
			echo json_encode($response);exit;
		}
}
