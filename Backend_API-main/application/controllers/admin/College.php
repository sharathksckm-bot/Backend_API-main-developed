<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Methods: GET, POST');
/**
 * College Controller
 *
 * @category   Controllers
 * @package    Admin
 * @subpackage College
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    24 JAN 2024
 *
 * Class College handles all the operations related to displaying list, creating college, update, and delete.
 */

if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}
date_default_timezone_set('Asia/Kolkata');
class College extends CI_Controller
{
    /**
     * Constructor
     *
     * Loads necessary libraries, helpers, and models for the college controller.
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
        $this->load->model("admin/College_model", "", true);
		$this->load->model("admin/Common_model", "", true);
		$this->load->library('Utility');

    }

	public function getClgList()
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
				$userId = $userData->data->userId;
				$userType = $userData->data->user_type;
				 $columns = array(
					 0 =>'id',
					 1 => 'title',
					 2 => 'email',
					 3 => 'package_type',
					 4 => 'status',


				 );
		 
				 $limit = $data->length;
				 $start = ($data->draw - 1) * $limit;
				 $orderColumn = $columns[$data->order[0]->column];
				 $orderDir = $data->order[0]->dir;
				 $totalData = $this->College_model->countAllClg($userId,$userType);
				 $totalFiltered = $totalData;
		 
				 if (!empty($data->search->value)) {
					 $search = $data->search->value;
					 $totalFiltered = $this->College_model->countFilteredClg($search,$userId,$userType,$start, $limit);
					 $ClgList = $this->College_model->getFilteredClg($search, $start, $limit, $orderColumn, $orderDir,$userId,$userType);

					} else {
					 $ClgList = $this->College_model->getAllClg($start, $limit, $orderColumn, $orderDir,$userId,$userType);
				 }
		 
                //print_r($ClgList);exit;
				 $datas = array();
				 foreach ($ClgList as $clg) {
					
					 $nestedData = array();
					 $nestedData['id'] = $clg->id;
					 $nestedData['title'] = $clg->title;  
					 $nestedData['registraion_type'] = $clg->package_type; 
					 $nestedData['map_location'] = $clg->map_location; 
					 $nestedData['views'] = $clg->views;
					 $nestedData['status'] = $clg->status;
                     $nestedData['cityname'] = $clg->cityname;
					 $nestedData['statename'] = $clg->statename;
					 $nestedData['create_date'] = $clg->create_date;
					 $nestedData['created_by'] = $clg->created_by;
					 $nestedData['created_by_name'] = $clg->created_by_name;
					 $nestedData['updated_date'] = $clg->updated_date;
					 $nestedData['updated_by'] = $clg->updated_by;
					 $nestedData['updated_by_name'] = $clg->updated_by_name;


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

	/*** insert details of college */
public function insertCollegeDetails()
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
		// print_r($userData->data);exit;
		$userType = $userData->data->user_type;
		$created_by = $userData->data->userId;
		$collegeName = $data->collegeName;
		$countryid = $data->countryId;
		$stateid = $data->stateid;
		$cityid = $data->cityid;
		$estyear = $data->estyear;
		$address = $data->address;
		$is_trending = $data->is_trending;
		$phones = array();
		foreach ($data->phone as $item) {
			$number = $item->mobileNumber;
    		$phones[] = $number;
		}
		$phone = implode(",", $phones);
		//print_r($phone);exit;
		$accreditation  = $data->accreditation ;
		$email = $data->email;
		$website = $data->website;
		$map_location = $data->map_location;
		$collegeTypeId = $data->collegeTypeId;
		$description = $data->description;
		$cet = $data->cet;
		$pgcet = $data->pgcet;
		$package_type = $data->package_type;
		$terms = $data->terms;
		$status = $data->status;
		$view_in_menu = $data->view_in_menu;
		$is_accept_entrance = $data->is_accept_entrance;
		$what_new = $data->what_new;
		$notification = isset($data->notification) ? $data->notification :'';
		$notificationlink = isset($data->notification_link) ? $data->notification_link : '';
		$applicationlink = isset($data->application_link) ? $data->application_link : '';

		$words = explode(' ', $collegeName);
		$tag = '';
		foreach ($words as $word) {
			if ($word !== "of" || $word !== "and" ) {
				$tag .= ucfirst(substr($word, 0, 1));
			}
		}
		
		$str = preg_replace('/[^A-Za-z0-9\. -]/', ' ', $collegeName);
		$slug = preg_replace('/\s+/', '-', strtolower($str));
		$Arr = [
		'title'=>$collegeName,'countryid'=>$countryid,
		'stateid'=>$stateid,'slug'=>$slug,'phone'=>$phone,
		'accreditation'=>$accreditation,'map_location'=>$map_location,
		'cityid'=>$cityid,'estd'=>$estyear,'address'=>$address,
		'email'=>$email,'web'=>$website,
		'college_typeid'=>$collegeTypeId,'description'=>$description,
		'CET'=>$cet,'PGCET'=>$pgcet,'package_type'=>$package_type,
		'terms'=>$terms,'status'=>$status,'view_in_menu'=>$view_in_menu,
		'is_accept_entrance'=>$is_accept_entrance,
		'tag'=>$tag,
		'what_new'=>$what_new,
		'is_trending' => $is_trending,

		'notification'=>$notification,'notification_link'=>$notificationlink,'created_by'=>$created_by,'create_date'=>date('Y-m-d H:i:s'),'application_link'=>$applicationlink
		];
		$create_date = date('Y-m-d');
		$chkIfExists = $this->College_model->chkIfExists($slug);
		if($chkIfExists > 0)
		{
			$response["response_code"] = 300;
			$response["response_message"] = 'college is already exists.Please try another one.';
		}
		else
		{
			$result = $this->College_model->insertCollegeDetails($Arr);
			$checkTeamReport = $this->Common_model->checkTeamReport($created_by,$userType,$create_date);
			$TeamArr = ['userid'=>$created_by,'usertype'=>$userType,'no_of_colleges_added'=>1,'no_of_exams_added'=>0,'no_of_events_added'=>0,'no_of_articles_added'=>0,'updated_date'=>date('Y-m-d H:i:s')];
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

	/*** update details of college */
	public function updateCollegeDetails()
	{
		$data = json_decode(file_get_contents('php://input'));
		 
		if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
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
		if($data)
		{
		
		$updated_by = $userData->data->userId;
		$clgId = $data->clgId;
		$collegeName = isset($data->collegeName)?$data->collegeName:'';
		$countryid = isset($data->countryId)?$data->countryId:'';
		$stateid = isset($data->stateid)?$data->stateid:'';
		$cityid = isset($data->cityid)?$data->cityid:'';
		$estyear = isset($data->estyear)?$data->estyear:'';
		$address = isset($data->address)?$data->address:'';
		//$phone = $data->phone;
		$is_trending = isset($data->is_trending)?$data->is_trending:'';

		$phones = array();
		foreach ($data->phone as $item) {
			
    		$phones[] = $item->mobileNumber; // Add each mobile number to the $phones array
		}
		$phone = implode(",", $phones);
		//print_r($phone);exit;
		$accreditation  = $data->accreditation ;
		$email = $data->email;
		$website = $data->website;
		$map_location = $data->map_location;
		$collegeTypeId = $data->collegeTypeId;
		$description = $data->description;
		$cet = $data->cet;
		$pgcet = $data->pgcet;
		$package_type = $data->package_type;
		$terms = $data->terms;
		$status = $data->status;
		$view_in_menu = $data->view_in_menu;
		$what_new = isset($data->what_new) ? $data->what_new:'';
		$notification = isset($data->notification) ? $data->notification :'';
		$notificationlink = isset($data->notification_link) ? $data->notification_link : '';
		$is_accept_entrance = $data->is_accept_entrance;
		$words = explode(' ', $collegeName);
		$applicationlink = isset($data->application_link) ? $data->application_link : '';

		$tag = '';
		foreach ($words as $word) {
			if ($word !== "of" || $word !== "and" ) {
				$tag .= ucfirst(substr($word, 0, 1));
			}
		}
		$str = preg_replace('/[^A-Za-z0-9\. -]/', ' ', $collegeName);
		$slug = preg_replace('/\s+/', '-', strtolower($str));
		$Arr = [
		'title'=>$collegeName,'countryid'=>$countryid,
		'stateid'=>$stateid,'slug'=>$slug,'phone'=>$phone,
		'accreditation'=>$accreditation,'map_location'=>$map_location,
		'cityid'=>$cityid,'estd'=>$estyear,'address'=>$address,
		'email'=>$email,'web'=>$website,'what_new'=>$what_new,
		'college_typeid'=>$collegeTypeId,'description'=>$description,
		'CET'=>$cet,'PGCET'=>$pgcet,'package_type'=>$package_type,
		'terms'=>$terms,'status'=>$status,'view_in_menu'=>$view_in_menu,
		'is_accept_entrance'=>$is_accept_entrance,'is_trending'=>$is_trending,
		'tag'=>$tag,'notification'=>$notification,'notification_link'=>$notificationlink,'updated_by'=>$updated_by,'updated_date'=>date('Y-m-d H:i:s'),'application_link'=>$applicationlink

		];
		
		/*$chkIfExists = $this->College_model->chkWhileUpdate($clgId,$slug);
		if($chkIfExists > 0)
		{
			$response["response_code"] = 300;
			$response["response_message"] = 'college is already exists.Please try another one.';
		}
		else
		{*/
			
			$result = $this->College_model->updateCollegeDetails($clgId,$Arr);
			if ($result) {
				$response["response_code"] = "200";
				$response["response_message"] = "Success";
				$response["response_data"] = $result;
			} else {
				$response["response_code"] = "400";
				$response["response_message"] = "Failed";
			}
		//}
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
     * get the details of college using id.
     */
    public function getCollegeDetailsById()
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
            $Id = $data->id;
            $result = $this->College_model->getCollegeDetailsById($Id);
			if($result)
			{
				$result->logoName =  $result->logo;
				$result->bannerName = $result->banner;
				//echo $result->phone;exit;
				$phones = array($result->phone);
				//print_r($phones);exit;
				$formattedNumbers = array();

				// Iterate through each element in the original array
				foreach ($phones as $numbers) {
					// Explode the comma-separated string into an array of phone numbers
					$individualNumbers = explode(",", $numbers);

					// Add each phone number to the formatted array
					foreach ($individualNumbers as $number) {
						$formattedNumbers[] = array('mobileNumber' => $number);;
					}
				}
			$result->phone = $formattedNumbers;
			$result->logo = base_url().'/uploads/college/'.$result->logo;
			$result->banner = base_url().'/uploads/college/'.$result->banner;
			}
			$result1 = $this->College_model->getClgImageByClgId($Id);
			//print_r($result1);exit;
			if (!empty($result1)) {

			foreach ($result1 as $key => $img) {
				$result1[$key]->imageName = $img->image;
				$result1[$key]->image = base_url().'/uploads/college/'.$img->image;
			}
			}
			 else {
				$result1 =  [];
			}
			$result2 = $this->College_model->getClgCoursesByClgId($Id);
			$result3 = $this->College_model->getClgHighlightsByClgId($Id);
			$result4 = $this->College_model->getClgFeeStructureByClgId($Id);
		//	print_r($result4);exit;
			$result5 = $this->College_model->getClgBrochuresByClgId($Id);
			$result6 = $this->College_model->getClgPlacementsByClgId($Id);
			$result7 = $this->College_model->getClgRankByClgId($Id);
			$result9 = $this->College_model->getFaqByClgId($Id);
			$result10 = $this->College_model->getExamsForCat($Id);

			foreach($result9 as $key => $value) {
				//Explode the faq_ids field into an array
				$result9[$key]['faq_ids'] = explode(',', $value['faq_ids']);
			}
			$criteria = ['type' =>'college','college_id'=>$Id];
			$result8 = $this->Common_model->getTblOfContentById($criteria);

			if($result5)
			{
			foreach ($result5 as $key => $img) {
				$result5[$key]->imageName = $img->file;
				$result5[$key]->image = base_url().'/uploads/brochures/'.$img->file;
				
			}
		}

            if ($result || $result2 || $result3 || $result4 || $result5 || $result6 || $result7) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["response_data"] = $result;
				$response["image_data"] = $result1;
				$response["course_data"] = $result2;
				$response["highlights_data"] = $result3;
				$response["Fee_structure"] = $result4;
				$response["brochures"] = $result5;
				$response["placements"] = $result6;
				$response["Rank"] = $result7;
				$response["faq"] = $result9;
				$response["exams"] = $result10;

				$response["tableofcontent"] = $result8;



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
     * delete the details of college using state id.
     */

	 public function deleteCollege()
	 {
		 $data = json_decode(file_get_contents('php://input'));
		 if($this->input->server('REQUEST_METHOD')=='OPTIONS')
		 {
			 $data['status']='ok';
			 echo json_encode($data);exit;
		 }
		 if($data)
		 {
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
 
			 $Id = $data->id;
			 $UserRole = $userData->data->type;
			 $userId = $userData->data->userId;
			if($UserRole=='Employee' && $Id != $userId)
			{
				$response["response_code"] = 300;
				$response["response_message"] = "This user does not have access to modify the user.";
				echo json_encode($response);
        		exit();
			}
			 $Arr = ['is_deleted' => 1 ];
			 $result = $this->College_model->deleteCollege($Id,$Arr);
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
			
		$folder = 'uploads/college';
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
					// "PDF" => "application/pdf",
					// "doc" => "application/msword", // DOC
					// "csv" => "text/csv",           // CSV
					// "xls" => "application/vnd.ms-excel", // XLS
					// "xlsx" => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" // XLSX
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
				/*else if($filesize > $maxsize)
				{
				$response['status'] = 'false';
				$response['response_code'] = 2;
				$response['response_message'] = "File size is larger than the allowed limit";
				}*/
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
     * save docs...
     */

	 public function saveDoc()
	 {
		 $data = json_decode(file_get_contents('php://input'));
		 if($this->input->server('REQUEST_METHOD')=='OPTIONS')
		 {
			 $data['status']='ok';
			 echo json_encode($data);exit;
		 }
		 if($data)
		 {
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
 
			 $postId = $data->clgId;
			 $logoImage = $data->logoImage;
			 $bannerImage = $data->bannerImage;
			 $type = $data->type;
			 $images = $data->image;
			
			 $imageArr = ['logo'=>$logoImage,'banner'=>$bannerImage];
			 $UpdateClgLogo =  $this->College_model->updateCollegeDetails($postId,$imageArr);
			//  $Arr = ['postid'=>$postId, 'type'=>$type, 'image'=>$image];
			//  $result = $this->College_model->insertClgDocsDetails($Arr);
			 $resultArray = array();
			 foreach ($images as $image) {
				 if (!empty($image->id)) {
					 $updateArr = ['postid' => $postId, 'type' => $type, 'image' => $image->imageName];
					 $this->College_model->updateClgDocsDetails($image->id,$postId, $updateArr);
					 $resultArray['imageId'] = $image->id;
				 } else {
					 $insertArr = ['postid' => $postId, 'type' => $type, 'image' => $image->imageName];
					 $result = $this->College_model->insertClgDocsDetails($insertArr);
					 if ($result) {
						 $resultArray['imageId'] = $result;
					 }
				 }
			 }
			 if ($resultArray || $UpdateClgLogo) {
				 $response["response_code"] = "200";
				 $response["response_message"] = "Success";
				 $response["response_data"] = $resultArray;
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

	 public function updateCategoryForClg()
	 {
		$data = json_decode(file_get_contents('php://input'));
		if($this->input->server('REQUEST_METHOD')=='OPTIONS')
		{
			$data['status']='ok';
			echo json_encode($data);exit;
		}
		if($data)
		{
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
			
			$catIds = $data->catIds;
			$clgId = $data->clgId;
			$Arr = ['categoryid'=>$catIds];
			$result = $this->College_model->updateCollegeDetails($clgId,$Arr);
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

	 public function updateCourseForClg()
	 {
		$data = json_decode(file_get_contents('php://input'));
		if($this->input->server('REQUEST_METHOD')=='OPTIONS')
		{
			$data['status']='ok';
			echo json_encode($data);exit;
		}
		if($data)
		{
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
			$courses = $data->courses;
			$clgId = $data->clgId;
			$Arr = [];
// 			$deleteCourse = $this->College_model->deleteCourse($clgId);

			foreach ($courses as $key => $crs) {
				$course = []; 
				$course['collegeid'] = $clgId;
				$course['courseid'] = $crs->id;
				$course['level'] = $crs->type;
				$course['duration'] = $crs->duration;
				$course['categoryid'] = $crs->sub_category;

				$Arr[] = $course; 
				$chkIfExists = $this->College_model->chkIfExistsCrs($clgId, $course['courseid']);
				if ($chkIfExists > 0) {
					
					$result = $this->College_model->updateCourseForClg($clgId,$course['courseid'], $course);
				}else{
					
					$result = $this->College_model->insertCourseForClg($course);
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
		
		} else {
			$response["response_code"] = "500";
			$response["response_message"] = "Data is null";
		}
		echo json_encode($response);
		exit();
	 }


	 public function updateHighlightsForClg()
	 {
		$data = json_decode(file_get_contents('php://input'));
		if($this->input->server('REQUEST_METHOD')=='OPTIONS')
		{
			$data['status']='ok';
			echo json_encode($data);exit;
		}
		if($data)
		{
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
			$highlights = $data->highlights;
			$clgId = $data->clgId;
			$Arr = [];
			foreach ($highlights as $key => $crs) { 
				$HigLt = [
				   'id' => $crs->id,
                    'collegeid' => $clgId,
                    'text' => $crs->text
                ];
				$chkIfExists = $this->College_model->chkIfExistsHighlights($clgId,$HigLt['id'], $HigLt['text']);
				//print_r($chkIfExists);exit;
				if ($chkIfExists > 0) {
				    //echo"342";exit;
					$result = $this->College_model->updateHighlightsForClg($clgId, $HigLt);
				}else{
					$result = $this->College_model->insertHighlightsForClg($HigLt);
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
		
		} else {
			$response["response_code"] = "500";
			$response["response_message"] = "Data is null";
		}
		echo json_encode($response);
		exit();
	 }

	 public function insertUpdateFeeStructure()
	 {
		$data = json_decode(file_get_contents('php://input'));
		if($this->input->server('REQUEST_METHOD')=='OPTIONS')
		{
			$data['status']='ok';
			echo json_encode($data);exit;
		}
		if($data)
		{
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
			$feeStructure = $data->feeStructure;
		
			$clgId = $data->clgId;
			$Arr = [];
			foreach ($feeStructure as $key => $fee) {
				$feeData  = [
                    'college_id' => $clgId,
                    'details' => $fee->details,
					'amount'=>$fee->amount,
					'course_id' => $fee->course_id
                ];
				$chkIfExists = $this->College_model->chkIfExistsFeeStructure($clgId, $feeData ['course_id'],$feeData ['details']);
				//print_r($chkIfExists);exit;
				if ($chkIfExists > 0) {
				//	print_r($fee->id);exit;
					$result = $this->College_model->updateFeeStructureForClg($fee->id,$clgId,$feeData ['course_id'], $feeData );
				}else{
					//	echo "ttt";exit;
					$result = $this->College_model->insertFeeStructureForClg($feeData );
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
		
		} else {
			$response["response_code"] = "500";
			$response["response_message"] = "Data is null";
		}
		echo json_encode($response);
		exit();
	 }
	 
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
 
			 $Id = $data->imageId;
			 $result = $this->College_model->deleteDoc($Id);
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

	 public function updateFacilityForClg()
	 {
		 $data = json_decode(file_get_contents('php://input'));
		 if($this->input->server('REQUEST_METHOD')=='OPTIONS')
		 {
			 $data['status']='ok';
			 echo json_encode($data);exit;
		 }
		 if($data)
		 {
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
 
			 $clgId = $data->clgId;
			 $facilities = $data->facilities;
			 $result = $this->College_model->updateFacilityForClg($clgId,$facilities);
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
     * upload documents of Brochures.
     */
    public function uploadBrochuresDocs()
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
			
		$folder = 'uploads/brochures';
		if(!is_dir($folder)) {
			mkdir($folder, 0777, TRUE);
			}
			if(isset($_FILES["file"]) && $_FILES["file"]["error"] == 0)
			{

				$allowed = array(
					// "jpg" => "image/jpg",
					// "jpeg" => "image/jpeg",
					// "png" => "image/png",
					// "JPG" => "image/jpeg",
					// "JPEG" => "image/jpeg",
					// "PNG" => "image/png",
					"PDF" => "application/pdf",
					"pdf" => "application/pdf",
					//"doc" => "application/msword", // DOC
					//"csv" => "text/csv",           // CSV
					//"xls" => "application/vnd.ms-excel", // XLS
					//"xlsx" => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" // XLSX
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


		/**
     * save docs...
     */

	 public function saveBrochuresDoc()
	 {
		 $data = json_decode(file_get_contents('php://input'));
		 if($this->input->server('REQUEST_METHOD')=='OPTIONS')
		 {
			 $data['status']='ok';
			 echo json_encode($data);exit;
		 }
		 if($data)
		 {
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
 
			 $postId = $data->clgId;
			 $images = $data->image;
			
			//  $imageArr = ['collegeid'=>$postId,'file'=>$images,'title'=>$title];
			 $resultArray = array();
			 foreach ($images as $image) {
				 if (!empty($image->id)) {
					 $updateArr = ['collegeid' => $postId, 'file' => $image->file, 'title' => $image->title];
					 $this->College_model->updateClgBrochuresDocsDetails($image->id,$postId, $updateArr);
					 $resultArray['imageId'] = $image->id;
				 } else {
					$insertArr = ['collegeid' => $postId, 'file' => $image->file, 'title' => $image->title];
					$result = $this->College_model->insertClgBrochuresDocsDetails($insertArr);
					 if ($result) {
						 $resultArray['imageId'] = $result;
					 }
				 }
			 }
			 if ($resultArray || $UpdateClgLogo) {
				 $response["response_code"] = "200";
				 $response["response_message"] = "Success";
				 $response["response_data"] = $resultArray;
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


	 public function insertupdateAcademicYearForClg()
	 {
		 $data = json_decode(file_get_contents('php://input'));
		 if($this->input->server('REQUEST_METHOD')=='OPTIONS')
		 {
			 $data['status']='ok';
			 echo json_encode($data);exit;
		 }
		 if($data)
		 {
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
 
			 $clgId = $data->clgId;
			 $placementStats = $data->placementStats;

				foreach ($placementStats as $placement) {
					$clgId = $clgId;
					$year = $placement->year;
					$compVisited = $placement->compVisited;
					$studentPlaced = $placement->studentPlaced;
					$medianSalary = $placement->medianSalary;
					$higherEduStudentPlaced = $placement->higherEduStudentPlaced;
					$id = $placement->id;

					$courseCategory = $placement->courseCategory; 

					$Arr = [
						'collegeid' => $clgId,
						'year' => $year,
						'no_of_companies_visited' => $compVisited,
						'no_of_students_placed' => $studentPlaced,
						'median_salary' => $medianSalary,
						'no_of_student_selected' => $higherEduStudentPlaced,
						'course_category' => $courseCategory
					];
                    if(!empty($id))
                    {
                       $chekWhileUpdate = $this->College_model->chkPlacementIfExistsWhileUpdate($clgId, $year,$courseCategory,$id);
                       if($chekWhileUpdate > 0)
                       {
                            $response["response_code"] = "400";
				            $response["response_message"] = "Failed. The data you attempted to enter already exists. Please try again with new data.";
				             echo json_encode($response);
		                    exit();
                       }
                       else
                       {
                           $result = $this->College_model->updateAcademicYearForClgById($Arr, $id);
                       }
                    }
                    else
                    {
                        
					$chkIfExists = $this->College_model->chkPlacementIfExists($clgId, $year,$courseCategory);
					if ($chkIfExists > 0) {
						$result = $this->College_model->updateAcademicYearForClg($Arr, $clgId,$year,$courseCategory);
					} else {
						$result = $this->College_model->insertAcademicYearForClg($Arr);
					}
                    }
				}
			 if (true) {
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

	 public function getColleges()
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
            $search_college = $data->search_college;
			 $result = $this->College_model->getColleges($search_college);
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

	 public function insertupdateRankForClg()
	 {
		 $data = json_decode(file_get_contents('php://input'));
		 if($this->input->server('REQUEST_METHOD')=='OPTIONS')
		 {
			 $data['status']='ok';
			 echo json_encode($data);exit;
		 }
		 if($data)
		 {
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
 
			 $clgId = $data->clgId;
			 $rank = $data->rank;

				foreach ($rank as $rnk) {
					$clgId = $clgId;
					$category_id = $rnk->category_id;
					$rank = $rnk->rank;
					$year = $rnk->year;
					$description = $rnk->description;
					$display_order = $rnk->display_order;
		            $id = $rnk->id;
					$Arr = [
						'college_id' => $clgId,
						'category_id' => $category_id,
						'rank' => $rank,
						'year'=>$year,
						'description' => $description,
						'display_order' => $display_order,
						
					];
                    if(!empty($id))
                    {
                        $result = $this->College_model->updateRankForClg($Arr, $id);
                    }
                    else
                    {
					$chkIfExists = $this->College_model->chkRankIfExists($clgId, $category_id,$year,$rank);
					if ($chkIfExists > 0) {
						$result = $this->College_model->updateRankForClg($Arr, $id);
					} else {
						$result = $this->College_model->insertRankForClg($Arr);
					}
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
		 } else {
			 $response["response_code"] = "500";
			 $response["response_message"] = "Data is null";
		 }
		 echo json_encode($response);
		 exit();
	 }

	 public function deleteRankForCollege()
	 {
		 $data = json_decode(file_get_contents('php://input'));
		 if($this->input->server('REQUEST_METHOD')=='OPTIONS')
		 {
			 $data['status']='ok';
			 echo json_encode($data);exit;
		 }
		 if($data)
		 {
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
 
			 $rankId = $data->rankId;
			 $result = $this->College_model->deleteRankForCollege($rankId);
			 if ($result) {
				 $response["response_code"] = "200";
				 $response["response_message"] = "Success";
				 $response["response_data"] = $result;
			 } else {
				 $response["response_code"] = "400";
				 $response["response_message"] = "Failed";
			 }
			}
			else
			{
				$response["response_code"] = "500";
			 $response["response_message"] = "Data is null";
			}
		 echo json_encode($response);
		 exit();
	 }

	 public function deleteAcadmicPlacements()
	 {
		 $data = json_decode(file_get_contents('php://input'));
		 if($this->input->server('REQUEST_METHOD')=='OPTIONS')
		 {
			 $data['status']='ok';
			 echo json_encode($data);exit;
		 }
		 if($data)
		 {

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
 
			 $placementId = $data->placementId;
			 $result = $this->College_model->deleteAcadmicPlacements($placementId);
			 if ($result) {
				 $response["response_code"] = "200";
				 $response["response_message"] = "Success";
				 $response["response_data"] = $result;
			 } else {
				 $response["response_code"] = "400";
				 $response["response_message"] = "Failed";
			 }
			}
			else
			{
				$response["response_code"] = "500";
			 $response["response_message"] = "Data is null";
			}
		 echo json_encode($response);
		 exit();
	 }

	 public function updateScholarshipsForClg()
	{
		$data = json_decode(file_get_contents('php://input'));
		if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
		$data['status'] = 'ok';
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

		$clgId = $data->clgId;
		
		$scholarships = $data->scholarships;

		$result = $this->College_model->update_ScholarshipsForClg($clgId, $scholarships);
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

  public function deleteHighlightsOfCollege()
	 {
		 $data = json_decode(file_get_contents('php://input'));
		 if($this->input->server('REQUEST_METHOD')=='OPTIONS')
		 {
			 $data['status']='ok';
			 echo json_encode($data);exit;
		 }
		 if($data)
		 {
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
 
			 $highlightId = $data->highlightId;
			 $result = $this->College_model->deleteHighlightsOfCollege($highlightId);
			 if ($result) {
				 $response["response_code"] = "200";
				 $response["response_message"] = "Success";
				 $response["response_data"] = $result;
			 } else {
				 $response["response_code"] = "400";
				 $response["response_message"] = "Failed";
			 }
			}
			else
			{
				$response["response_code"] = "500";
			 $response["response_message"] = "Data is null";
			}
		 echo json_encode($response);
		 exit();
	 }

	 public function deleteBrochureOfCollege()
	 {
		 $data = json_decode(file_get_contents('php://input'));
		 if($this->input->server('REQUEST_METHOD')=='OPTIONS')
		 {
			 $data['status']='ok';
			 echo json_encode($data);exit;
		 }
		 if($data)
		 {
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
 
			 $brochureId = $data->brochureId;
			 $result = $this->College_model->deleteBrochureOfCollege($brochureId);
			 if ($result) {
				 $response["response_code"] = "200";
				 $response["response_message"] = "Success";
				 $response["response_data"] = $result;
			 } else {
				 $response["response_code"] = "400";
				 $response["response_message"] = "Failed";
			 }
			}
			else
			{
				$response["response_code"] = "500";
			 $response["response_message"] = "Data is null";
			}
		 echo json_encode($response);
		 exit();
	 }


	 public function deleteFeeStructOfCollege()
	 {
		 $data = json_decode(file_get_contents('php://input'));
		 if($this->input->server('REQUEST_METHOD')=='OPTIONS')
		 {
			 $data['status']='ok';
			 echo json_encode($data);exit;
		 }
		 if($data)
		 {
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
 
			 $feeId = $data->feeId;
			 $result = $this->College_model->deleteFeeStructOfCollege($feeId);
			 if ($result) {
				 $response["response_code"] = "200";
				 $response["response_message"] = "Success";
				 $response["response_data"] = $result;
			 } else {
				 $response["response_code"] = "400";
				 $response["response_message"] = "Failed";
			 }
			}
			else
			{
				$response["response_code"] = "500";
			 $response["response_message"] = "Data is null";
			}
		 echo json_encode($response);
		 exit();
	 }


	 public function getPlacementCategory()
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

			 $result = $this->College_model->getPlacementCategory();
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


	 public function insertTableOfContent()
	 {
		$data = json_decode(file_get_contents('php://input'));
		if($this->input->server('REQUEST_METHOD')=='OPTIONS')
		{
			$data['status']='ok';
			echo json_encode($data);exit;
		}
		if($data)
		{
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
			$content = $data->content;
			$clgId = $data->clgId;
			$Arr = [];

			//save data to the table of content table....
			foreach ($content as $key => $cnt) { 
				$details = [
					'collegeid'=>$clgId,
                    'title' => $cnt->title,
					'categoryid' => $cnt->categoryid,
					'format_id'=>$cnt->formatid,
                ];
				$newData = [
					'text'=>$cnt->description,
					'collegeid'=>$clgId
				];
				$chkIfExists = $this->College_model->chkIfExistsContent($clgId, $details['title'],$details['categoryid']);
				if ($chkIfExists > 0) {
					$result = $this->College_model->updateTableOfContent($clgId, $details);
				}else{
					$result = $this->College_model->insertTableOfContent($details);
				}

				// If the format is for highlights, then save the data to the highlight table...
				if(strtoupper($cnt->formatname =='HIGHLIGHTS'))
				{
					$result = $this->College_model->insertHighlightsForClg($HigLt);

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
		
		} else {
			$response["response_code"] = "500";
			$response["response_message"] = "Data is null";
		}
		echo json_encode($response);
		exit();
	 }
	 public function deleteTableOfContents() {
		$data = json_decode(file_get_contents('php://input'));
		if($this->input->server('REQUEST_METHOD')=='OPTIONS')
		{
			$data['status']='ok';
			echo json_encode($data);exit;
		}
		if($data)
		{
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
			$clgId = $data->clgId;
			$contentId = $data->contentId;
		}
		else
		{
			$response["response_code"] = "500";
			$response["response_message"] = "Data is null";
		}
		echo json_encode($response);
		exit();
	 }

	 public function getSampleFormat() {
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
			$result = $this->College_model->getSampleFormat();
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

	 public function getFormatDataUsingId() {
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
			$sampleId = $data->sampleId;
			$result = $this->College_model->getFormatDataUsingId($sampleId);
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

	 public function getCourseUsingClgId() {
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
			$clgId = $data->clgId;
			$searchCrs = isset($data->searchCrs)?$data->searchCrs:'';

			$result = $this->College_model->getCourseUsingClgId($clgId,$searchCrs);
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
	 public function authorize_google()
	 {
		 // Get your Google Analytics tracking ID
		 $trackingId = 'UA-154134972-1'; // Replace with your actual tracking ID
	 
		 // Retrieve website traffic data using Measurement Protocol
		 $url = "https://www.google-analytics.com/collect";
		 $data = array(
			 'v' => '1', // API version
			 'tid' => $trackingId, // Tracking ID
			 'cid' => '172018522583-h1rrkpa83b9eptls6d626lhedmrhcd95.apps.googleusercontent.com', // Client ID
			 't' => 'pageview', // Hit type
			 'dh' => $_SERVER['HTTP_HOST'], // Document host name
			 'dp' => $_SERVER['REQUEST_URI'], // Document path
			 'uip' => $_SERVER['REMOTE_ADDR'] // IP address of the user
			 // Add more parameters as needed
		 );
	 
		 // Send data to Google Analytics
		 $ch = curl_init($url);
		 curl_setopt($ch, CURLOPT_POST, true);
		 curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		 curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		 curl_exec($ch);
		 curl_close($ch);
	 
		 // Display website traffic data in admin panel view
		 $this->load->library('Googleanalytics');
		 $this->googleanalytics->initialize(); // Initialize Google Analytics API
		 $this->googleanalytics->setAccountId($trackingId); // Set the Google Analytics tracking ID
		 $results = $this->googleanalytics->query('ga:pageviews', array(
			 'dimensions' => 'ga:date',
			 'start-date' => '30daysAgo',
			 'end-date' => 'today',
			 'sort' => '-ga:date'
		 ));
	 
		 // Display data in admin panel view
		 $data['traffic_data'] = $results->rows;
		 print_r($data);exit;
	 }

	 public function getCollegeList()
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
			$searchClg = isset($data->searchClg)?$data->searchClg:'';
			$result = $this->College_model->getCollegeList($searchClg);
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

	 public function getTrendingColleges()
	 {
		 $data = json_decode(file_get_contents("php://input"));
		 if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
			 $data["status"] = "ok";
			 echo json_encode($data);
			 exit();
		 }
		 //print_r($_SERVER['HTTP_AUTHORIZATION']);exit;
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

				$fromdate = isset($data->fromdate) ? $data->fromdate :'';
				$todate = isset($data->todate) ? $data->todate :'';

			 	$result = $this->College_model->getTrendingColleges($fromdate,$todate);
 
			 if ($result) {
				 $response["response_code"] = "200";
				 $response["response_message"] = "Success";
				 $response["TrendingColleges"] = $result;
 
			 } else {
				 $response["response_code"] = "400";
				 $response["response_message"] = "Failed";
			 }
			}
			else
			{
				$response["response_code"] = "500";
				 $response["response_message"] = "Data is null !";
			}
		 
		 echo json_encode($response);
		 exit();
	 }

	 // getting list of categories which assigned to college....
	 public function getCollegeCategory() {
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

			$college_id = $data->college_id;
			$result = $this->College_model->getCollegeCategory($college_id);
			$catArr = [];
			foreach ($result as $key => $value) {
				$category_id = explode(',', $value->categoryid);
				$category_name = explode(',', $value->categoryname);
				
				foreach ($category_id as $index => $id) {
					$catArr[] = array('categoryid' => $id, 'catname' => $category_name[$index]);
				}
				}

			if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["response_data"] = $catArr;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }

			echo json_encode($response);exit;
	 }


	 // getting list of courses which assigned to college....
	 public function getCollegeCourses() {
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

			$college_id = $data->college_id;
			$category_id = $data->category_id;

			$result = $this->College_model->getCollegeCourses($college_id,$category_id);
	
			if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["response_data"] = $result;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }

			echo json_encode($response);exit;
	 }
}
