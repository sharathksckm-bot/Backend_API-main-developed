<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Methods: GET, POST');
/**
 * Exam Controller
 *
 * @category   Controllers
 * @package    Admin
 * @subpackage Exam
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    26 JAN 2024
 *
 * Class Exam handles all the operations related to displaying list, creating Exam, update, and delete.
 */
date_default_timezone_set('Asia/Kolkata');

if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}
class Exam extends CI_Controller
{
    /**
     * Constructor
     *
     * Loads necessary libraries, helpers, and models for the Exam controller.
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
        $this->load->model("admin/Exam_model", "", true);
		$this->load->model("admin/Common_model", "", true);
		$this->load->library('Utility');

    }

	/*** Get list of Exam */
	public function getExamList()
	{
	    // echo "ttt";exit;
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
		$columns = array(
			0 => 'title',
			1 => 'id',
			2 => 'category',
			3 => 'status',


		);
		$limit = $data->length;
		$start = ($data->draw - 1) * $limit;
		$orderColumn = $columns[$data->order[0]->column];
		$orderDir = $data->order[0]->dir;
		$totalData = $this->Exam_model->countAllExam($userId,$userType);
		$totalFiltered = $totalData;
		if (!empty($data->search->value)) {

			$search = $data->search->value;
			$totalFiltered = $this->Exam_model->countFilteredExam($search,$userId,$userType);
			$Exam = $this->Exam_model->getFilteredExam($search, $start, $limit, $orderColumn, $orderDir,$userId,$userType);

		   } else {
			$Exam = $this->Exam_model->getAllExam($start, $limit, $orderColumn, $orderDir,$userId,$userType);
		}

		$datas = array();
		foreach ($Exam as $e) {
		   
			$nestedData = array();
			$nestedData['id'] = $e->id;
			$nestedData['title'] = $e->title;
			$nestedData['category'] = $e->category;
			$nestedData['status'] = $e->status;
			$nestedData['created_by'] = $e->created_by;
			$nestedData['created_date'] = $e->create_date;
			$nestedData['updated_by'] = $e->updated_by;
			$nestedData['updated_date'] = $e->updated_date;
			$nestedData['created_by_name'] = $e->created_by_name;
			$nestedData['updated_by_name'] = $e->updated_by_name;

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

	/*** insert details of Exam */
	public function insertExamDetails()
	{
	   // echo "tttt";exit;
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
		$create_date = date('Y-m-d H:i:s');
		$name = $data->name;
		$category = $data->category;
		$status = $data->status;
		$view_in_menu = $data->view_in_menu;
		$description = $data->description;
		$criteria = $data->criteria;
		$process = $data->process;
		$pattern = $data->pattern;
		$notification = $data->notification;
	$state = $data->state;

		$exam = $data->exam_level;
		$course = $data->course_level;
		$formData = $data->formData;
		$toDate = $data->toDate;
		
	
		$Arr = ['created_by'=>$created_by,'create_date'=>$create_date,'notification'=>$notification,'title'=>$name,'categoryid'=>$category,'status'=>$status,'view_in_menu'=>$view_in_menu,'description'=>$description,'criteria'=>$criteria,'process'=>$process,'pattern'=>$pattern,'state_id'=>$state,'exam_level'=>$exam,'course_level'=>$course,'start_date'=>$formData,'end_date'=>$toDate];
		//	print_r($Arr);exit;
		$chkIfExists = $this->Exam_model->chkIfExists($name);
		if($chkIfExists > 0)
		{
			$response["response_code"] = 300;
			$response["response_message"] = 'Exam already exists. Please try another one.';
		}
		else
		{
		   // print_r($Arr);exit;
			$result = $this->Exam_model->insertExamDetails($Arr);
			$checkTeamReport = $this->Common_model->checkTeamReport($created_by,$userType,$create_date);
			$TeamArr = ['userid'=>$created_by,'usertype'=>$userType,'no_of_colleges_added'=>0,'no_of_exams_added'=>1,'no_of_events_added'=>0,'no_of_articles_added'=>0,'updated_date'=>date('Y-m-d H:i:s')];
			if($checkTeamReport > 0)
			{
			 $updateTeamReport = $this->Common_model->updateTeamReport($created_by,$TeamArr,$create_date);

			}
			else
			{
			$saveTeamReport = $this->Common_model->saveTeamReport($TeamArr);
			}
			$id['id'] = $result ; 
			if ($result) {
				$response["response_code"] = "200";
				$response["response_message"] = "Success";
				$response["response_data"] = $id;
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

	/*** update details of Exam */
	public function updateExamDetails()
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
		$updated_date = date('Y-m-d H:i:s');
		$id = $data->id;
		$name = $data->name;
		$category = $data->category;
		$status = $data->status;
		$view_in_menu = $data->view_in_menu;
		$description = $data->description;
		$criteria = $data->criteria;
		$process = $data->process;
		$pattern = $data->pattern;
		$notification = $data->notification;
		$state = $data->state;
		$exam = $data->examLevel;
		$course = $data->course;
		$formData = $data->formData;
		$toDate = $data->toDate;
		$Arr = ['updated_by'=>$updated_by,'updated_date'=>$updated_date,'notification'=>$notification,'title'=>$name,'categoryid'=>$category,'status'=>$status,'view_in_menu'=>$view_in_menu,'description'=>$description,'criteria'=>$criteria,'process'=>$process,'pattern'=>$pattern,'state_id'=>$state,'exam_level'=>$exam,'course_level'=>$course,'start_date'=>$formData,'end_date'=>$toDate];
	//	print_r($Arr);exit;
		$chkIfExists = $this->Exam_model->chkWhileUpdate($name,$id);
		if($chkIfExists > 0)
		{
			$response["response_code"] = 300;
			$response["response_message"] = 'Exax already exists. Please try another one.';
		}
		else
		{
			$result = $this->Exam_model->updateExamDetails($id,$Arr);
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
     * get the details of Exam using Exam id.
     */
    public function getExamDetailsById()
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
            
            $result = $this->Exam_model->getExamDetailsById($Id);
           // print_r($result);exit;
			$result1 = $this->Exam_model->getExamImgDetailsById($Id);
			foreach ($result1 as $key => $img) {
				$result1[$key]->imageName = $img->image;

				$result1[$key]->image = base_url().'/uploads/exams/'.$img->image;

			}
			//print_r($result->questionpaper);exit;
			
		/*	if(!empty($result->questionpaperPath)){
			$result->questionpaperPath = base_url().'/uploads/questionpaper/'.$result->questionpaper;}
			else{$result->questionpaperPath ='';}
			if(!empty($result->preparationPath)){
			$result->preparationPath = base_url().'/uploads/preparation/'.$result->preparation;}
			else{$result->preparationPath ='';}
			if(!empty($result->syllabusPath)){
			$result->syllabusPath = base_url().'/uploads/syllabus/'.$result->syllabus;}
			else{$result->syllabusPath ='';}*/
			
	if (!empty($result->questionpaper)) {
    $questionPapers = explode(',', $result->questionpaper);
    $questionpaperPaths = [];

    foreach ($questionPapers as $index => $paper) {
        $fileName = trim($paper);
        $questionpaperPaths[] = [
            'file_name' => $fileName,
            'pdfpath' => base_url() . '/uploads/questionpaper/' . $fileName
        ];
    }

    $result->questionpaperPaths = $questionpaperPaths;
} else {
    $result->questionpaperPaths = [];
}

			if(!empty($result->preparation)){
			$result->preparationPath = base_url().'/uploads/preparation/'.$result->preparation;}
			else{$result->preparationPath ='';}
			if(!empty($result->syllabus)){
			$result->syllabusPath = base_url().'/uploads/syllabus/'.$result->syllabus;}
			else{$result->syllabusPath ='';}
			
            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["exam_data"] = $result;
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

	/**
     * delete the details of Exam using Exam id.
     */

	 public function deleteExam()
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
			 $UserRole = $userData->data->type;
			 $userId = $userData->data->userId;
			if($UserRole=='Employee' && $Id != $userId)
			{
				$response["response_code"] = 300;
				$response["response_message"] = "Sorry, you do not have permission to modify another user's Exams.";
				echo json_encode($response);
        		exit();
			}
			 $result = $this->Exam_model->deleteExam($Id);
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
     * upload documents of Exam.
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
		 //print_r($_POST['type']);exit;
			$headers = apache_request_headers();
			$token = str_replace("Bearer ", "", $headers['Authorization']);
			$kunci = $this->config->item('jwt_key');
			$userData = JWT::decode($token, $kunci);
			Utility::validateSession($userData->iat,$userData->exp);
        	$tokenSession = Utility::tokenSession($userData);
		// $type = $data->type;
		$folder = ''; // Initialize $folder variable

		$allowed = []; // Initialize $allowed array
		$type = $_POST['type'];
		switch ($type) {
			case 'IMAGE':
				$folder = 'uploads/exams';
				$allowed = [
					"jpg" => "image/jpeg",
					"jpeg" => "image/jpeg",
					"png" => "image/png"
				];
				break;
			case 'QUESTIONPAPER':
				$folder = 'uploads/questionpaper';
				$allowed = [
					"pdf" => "application/pdf"
				];
				break;
			case 'SYLLABUS':
				$folder = 'uploads/syllabus';
				$allowed = [
					"pdf" => "application/pdf"
				];
				break;
			case 'PREPARATION':
				$folder = 'uploads/preparation';
				$allowed = [
					"pdf" => "application/pdf"
				];
				break;
			default:
				// Handle invalid $type
				break;
		}

		if(!is_dir($folder)) {
			mkdir($folder, 0777, TRUE);
			}
			if(isset($_FILES["file"]) && $_FILES["file"]["error"] == 0)
			{
			
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
				$response['response_message'] = "Please upload the image";
			}
		
			echo json_encode($response);exit;
		}

		/**
     * delete the details of Exam using Exam id.
     */



	  /**
     * delete the details of event Doc using image id.
     */

	 public function deleteDoc()
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
 
			 $Id = $data->imageId;
			 $result = $this->Exam_model->deleteDoc($Id);
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

	 public function getExams()
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

			 $searchExam = isset($data->searchExam) ? $data->searchExam : "";
             $subcat = isset($data->subcat) ? $data->subcat : "";
             $getCatId = $this->Exam_model->getCatId($subcat);
             //print_r($getCatId[0]->parent_category);exit;
			 $result = $this->Exam_model->getExams($searchExam,isset($getCatId[0]->parent_category)?$getCatId[0]->parent_category:'');
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


	public function updateExamsDocs()
	{
	    //echo "tt";exit;
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
		 	if($data)
			{
			$examId = $data->examId;
			
			$questionpaper = $data->questionpaper;
			
			
	 // Extract docName values and join them with commas
        $questionpdf = array_map(function($quespaper) {
            return $quespaper->docName;
        }, $questionpaper);
        $questionpdfStr = implode(',', $questionpdf); 
			
			
			$preparation = $data->preparation;
			
			$syllabus = $data->syllabus;
			
			$Arr = ['questionpaper'=> $questionpdfStr,'preparation'=>$preparation,'syllabus'=>$syllabus];
			
			 $result = $this->Exam_model->updateExamsDocs($examId,$Arr);
			// print_r($result);exit;
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
	
	public function saveExamDocs()
{
	//echo "tttt";exit;
    $data = json_decode(file_get_contents("php://input"));

    if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
        $data["status"] = "ok";
        echo json_encode($data);
        exit();
    }

    if ($data) {
        $examId = $data->examId;
        $docType = $data->docName;
        $title = $data->title;
        $docs = $data->docs;

		$docs_data = $this->Exam_model->getDocsData($examId,$title,$docType);
		//print_r();exit;
		foreach($docs_data as $data){
			if($data->docs_title == $title && $data->docs_type == $docType){
				$response["status"] = "false";
				$response["res_code"] = 2;
				$response["res_status"] = "Failed";
				$response["res_massage"] = "This document is already saved with this title or type.";
				
				echo json_encode($response);
				exit;
			}
		}
		
        $lastDocumentName = []; 

        foreach ($docs as $docsname) {
            $lastDocumentName[] = $docsname->docName;
        }
 $documentString = implode(',', $lastDocumentName);

 $Arr = ['examId'=>$examId,'docs_type'=>$docType,'docs_title'=>$title,'documents'=>$documentString];

        $result = $this->Exam_model->insertExamDocs($Arr);

        if ($result) {
            $response["status"] = "true";
            $response["res_code"] = 1;
            $response["res_status"] = "Success";
        } else {
            $response["status"] = "false";
            $response["res_code"] = 2;
            $response["res_status"] = "Failed";
        }
    } else {
        $response["response_code"] = "500";
        $response["response_message"] = "Data is null";
    }

    echo json_encode($response);exit;
}

  public function getExamDataById()
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
            $Id = $data->examId;
            
            $result = $this->Exam_model->getExamDataById($Id);
           // print_r($result);exit;
			$result1 = $this->Exam_model->getExamImgDetailsById($Id);
			foreach ($result1 as $key => $img) {
				$result1[$key]->imageName = $img->image;

				$result1[$key]->image = base_url().'/uploads/exams/'.$img->image;

			}
			//print_r($result->questionpaper);exit;
			
		/*	if(!empty($result->questionpaperPath)){
			$result->questionpaperPath = base_url().'/uploads/questionpaper/'.$result->questionpaper;}
			else{$result->questionpaperPath ='';}
			if(!empty($result->preparationPath)){
			$result->preparationPath = base_url().'/uploads/preparation/'.$result->preparation;}
			else{$result->preparationPath ='';}
			if(!empty($result->syllabusPath)){
			$result->syllabusPath = base_url().'/uploads/syllabus/'.$result->syllabus;}
			else{$result->syllabusPath ='';}*/
			
	if (!empty($result->questionpaper)) {
    $questionPapers = explode(',', $result->questionpaper);
    $questionpaperPaths = [];

    foreach ($questionPapers as $index => $paper) {
        $fileName = trim($paper);
        $questionpaperPaths[] = [
            'file_name' => $fileName,
            'pdfpath' => base_url() . '/uploads/questionpaper/' . $fileName
        ];
    }

    $result->questionpaperPaths = $questionpaperPaths;
} else {
    $result->questionpaperPaths = [];
}

			if(!empty($result->preparation)){
			$result->preparationPath = base_url().'/uploads/preparation/'.$result->preparation;}
			else{$result->preparationPath ='';}
			if(!empty($result->syllabus)){
			$result->syllabusPath = base_url().'/uploads/syllabus/'.$result->syllabus;}
			else{$result->syllabusPath ='';}
			
            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["exam_data"] = $result;
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


	public function getexamdocs()
	{
	    // echo "ttt";exit;
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
		$columns = array(
			0 => 'docs_title',
			1 => 'docs_type'
		);
		$limit = $data->length;
		$start = ($data->draw - 1) * $limit;
		$orderColumn = $columns[$data->order[0]->column];
		$orderDir = $data->order[0]->dir;
		$examId = isset($data->examId) ? $data->examId : '';
		$totalData = $this->Exam_model->countAllExamdocs($userType);
		//print_r($totalData);exit;
		$totalFiltered = $totalData;
		if (!empty($data->search->value)) {

			$search = $data->search->value;
			$totalFiltered = $this->Exam_model->countFilteredExamdocs($search,$userId,$userType);
		//	print_r($totalFiltered);exit;
			$Exam = $this->Exam_model->getFilteredExamdocs($search, $start, $limit, $orderColumn, $orderDir,$userId,$userType);
            //print_r($Exam);exit;
		   } else {
			$Exam = $this->Exam_model->getAllExamdocs($start, $limit, $orderColumn, $orderDir,$examId);
			//print_r($Exam);exit;
		}
//print_r($Exam);exit;
$datas = array();

foreach ($Exam as $e) {
    $nestedData = array();
    $nestedData['id'] = $e->id;
    $nestedData['docs_title'] = $e->docs_title;
    $nestedData['docs_type'] = $e->docs_type;

    $documentList = !empty($e->documents) ? explode(',', $e->documents) : [];

   
  //  $nestedData['document_name'] = !empty($documentList) ? $documentList[0] : ''; // First document name or empty

    $documentUrls = [];

    foreach ($documentList as $doc) {
        if (!empty($doc)) {
            $documentData = [
                'document_name' => $doc, 
                'document' => ''
            ];

			//print_r($e->docs_type);exit;

            if ($e->docs_type == 'Preparation') {
                $documentData['document'] = base_url().'uploads/preparation/'.$doc;
            } elseif ($e->docs_type == 'Syllabus') {
                $documentData['document'] = base_url().'uploads/syllabus/'.$doc;
            } else {
                $documentData['document'] = base_url().'uploads/questionpaper/'.$doc;
            }

            $documentUrls[] = $documentData;
        }
    }

    $nestedData['documents'] = $documentUrls;

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

	public function getexamdocsById()
    {
		//echo "tttt";exit;
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
            $Id = $data->docsId;
            
          //  $result = $this->Exam_model->getExamDetailsById($Id);
           // print_r($result);exit;
			$Exam = $this->Exam_model->getexamdocsById($Id);
			$datas = array();
			foreach ($Exam as $e) {
			  // print_r($e);exit;
				$nestedData = array();
				$nestedData['id'] = $e->id;
				$nestedData['docs_title'] = $e->docs_title;
				$nestedData['docs_type'] = $e->docs_type;
	
				if($e->docs_type == 'preparation'){
					$nestedData['documents'] = base_url().'uploads/preparation/'.$e->documents;
				}elseif($e->docs_type == 'Syllabus'){
					$nestedData['documents'] = base_url().'uploads/syllabus/'.$e->documents;
				}else{
					$nestedData['documents'] = base_url().'uploads/questionpaper/'.$e->documents;
				}
				$datas[] = $nestedData;
			}

		
            if ($datas) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["exam_docs_data"] = $datas;

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

	public function deleteexamdocsById()
    {
		//echo "tttt";exit;
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
            $Id = $data->docsId;
            
          //  $result = $this->Exam_model->getExamDetailsById($Id);
           // print_r($result);exit;

			$Exam = $this->Exam_model->deleteexamdocsById($Id);
            if ($Exam) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";

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

	public function updateExamDocs()
{
	//echo "tttt";exit;
    $data = json_decode(file_get_contents("php://input"));

    if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
        $data["status"] = "ok";
        echo json_encode($data);
        exit();
    }

    if ($data) {
		$id = $data->docs_id;
        $examId = $data->examId;
        $docType = $data->docName;
        $title = $data->title;
        $docs = $data->docs;
//print_r($docs);exit;
		// $docs_data = $this->Exam_model->getDocsData($examId,$title,$docType);
		// //print_r();exit;
		// foreach($docs_data as $data){
		// 	if($data->docs_title == $title && $data->docs_type == $docType){
		// 		$response["status"] = "false";
		// 		$response["res_code"] = 2;
		// 		$response["res_status"] = "Failed";
		// 		$response["res_massage"] = "This document is already saved with this title or type.";
				
		// 		echo json_encode($response);
		// 		exit;
		// 	}
		// }
		
        $lastDocumentName = []; 

        foreach ($docs as $docsname) {
			//print_r($docsname);exit;
            $lastDocumentName[] = $docsname;
        }
 $documentString = implode(',', $lastDocumentName);
 //print_r($documentString);exit;

 $Arr = ['examId'=>$examId,'docs_type'=>$docType,'docs_title'=>$title,'documents'=>$documentString];

        $result = $this->Exam_model->updateExamDocs($id,$Arr);

        if ($result) {
            $response["status"] = "true";
            $response["res_code"] = 1;
            $response["res_status"] = "Success";
        } else {
            $response["status"] = "false";
            $response["res_code"] = 2;
            $response["res_status"] = "Failed";
        }
    } else {
        $response["response_code"] = "500";
        $response["response_message"] = "Data is null";
    }

    echo json_encode($response);exit;
}
}
