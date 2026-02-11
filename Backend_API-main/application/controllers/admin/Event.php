<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Methods: GET, POST');
/**
 * Event Controller
 *
 * @category   Controllers
 * @package    Admin
 * @subpackage Event
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    26 JAN 2024
 *
 * Class Event handles all the operations related to displaying list, creating Event, update, and delete.
 */
date_default_timezone_set('Asia/Kolkata');

if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}
class Event extends CI_Controller
{
    /**
     * Constructor
     *
     * Loads necessary libraries, helpers, and models for the Event controller.
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
        $this->load->model("admin/Event_model", "", true);
		$this->load->model("admin/Common_model", "", true);

		$this->load->library('Utility');

    }

	/*** Get list of Event */
	public function getEventList()
	{
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
			0 => 'event_id',
			1 => 'event_name',
			2 => 'event_package',
			3 => 'event_status',


		);
		$limit = $data->length;
		$start = ($data->draw - 1) * $limit;
		$orderColumn = $columns[$data->order[0]->column];
		$orderDir = $data->order[0]->dir;
		$totalData = $this->Event_model->countAllEvent($userId,$userType);
		$totalFiltered = $totalData;
		if (!empty($data->search->value)) {

			$search = $data->search->value;
			$totalFiltered = $this->Event_model->countFilteredEvent($search,$userId,$userType);
			$Event = $this->Event_model->getFilteredEvent($search, $start, $limit, $orderColumn, $orderDir,$userId,$userType);

		   } else {
			$Event = $this->Event_model->getAllEvent($start, $limit, $orderColumn, $orderDir,$userId,$userType);
		}

		$datas = array();
		foreach ($Event as $e) {
		   
			$nestedData = array();
			$nestedData['id'] = $e->event_id;
			$nestedData['name'] = $e->event_name;
			$nestedData['package_type'] = $e->event_package;
			$nestedData['status'] = $e->event_status;
			$nestedData['created_by'] = $e->created_by;
			$nestedData['created_date'] = $e->event_create_date;
			$nestedData['created_by_name'] = $e->created_by_name;
			$nestedData['updated_by'] = $e->updated_by;
			$nestedData['updated_date'] = $e->updated_date;
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

	/*** insert details of Event */
	public function insertEventDetails()
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
			$name = $data->name;
			$address = $data->address;
			$phone = $data->phone;
			$email = $data->email;
			$website = $data->website;
			$maplocation = $data->maplocation;
			$start_date = $data->start_date;
			$end_date = $data->end_date;
			$desc = $data->desc;
			$college_name = $data->collegeid;
			$package = $data->package;
			$status = $data->status;
			$str2=preg_replace('/[^A-Za-z0-9\. -]/', '', strtolower($name));
			$slug=preg_replace('/\s+/', '-', strtolower($str2));
			$Arr = array(
				'event_name' => $name,
				"event_url"=>$slug,
				'event_address' => $address,
				'event_phone' => $phone,
				'event_email' => $email,
				'event_website' => $website,
				'event_maplocation' => $maplocation,
				'event_start_date' => $start_date,
				'event_end_date' => $end_date,
				'event_desc' => $desc,
				'event_college_name' => $college_name,
				'event_package' => $package,
				'event_status' => $status,
				'created_by'=>$created_by
			  );
		$create_date = date('Y-m-d');
		$chkIfExists = $this->Event_model->chkIfExists($name);
		if($chkIfExists > 0)
		{
			$response["response_code"] = 300;
			$response["response_message"] = 'Event is already exists.Please try another one.';
		}
		else
		{
			$result = $this->Event_model->insertEventDetails($Arr);
			$checkTeamReport = $this->Common_model->checkTeamReport($created_by,$userType,$create_date);
			$TeamArr = ['userid'=>$created_by,'usertype'=>$userType,'no_of_colleges_added'=>0,'no_of_exams_added'=>0,'no_of_events_added'=>1,'no_of_articles_added'=>0,'updated_date'=>date('Y-m-d H:i:s')];
			// print_r($TeamArr);exit;

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

	/*** update details of Event */
	public function updateEventDetails()
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
			$name = $data->name;
			$address = $data->address;
			$phone = $data->phone;
			$email = $data->email;
			$website = $data->website;
			$maplocation = $data->maplocation;
			$start_date = $data->start_date;
			$end_date = $data->end_date;
			$desc = $data->desc;
			$college_name = $data->collegeid;
			$package = $data->package;
			$status = $data->status;
			$str2=preg_replace('/[^A-Za-z0-9\. -]/', '', strtolower($name));
			$slug=preg_replace('/\s+/', '-', strtolower($str2));
			$Arr = array(
				'event_name' => $name,
				"event_url"=>$slug,
				'event_address' => $address,
				'event_phone' => $phone,
				'event_email' => $email,
				'event_website' => $website,
				'event_maplocation' => $maplocation,
				'event_start_date' => $start_date,
				'event_end_date' => $end_date,
				'event_desc' => $desc,
				'event_college_name' => $college_name,
				'event_package' => $package,
				'event_status' => $status,
				'updated_by'=>$updated_by,
				'updated_date'=>date('Y-m-d H:i:s')
			  );
		$chkIfExists = $this->Event_model->chkWhileUpdate($name,$id);
		if($chkIfExists > 0)
		{
			$response["response_code"] = 300;
			$response["response_message"] = 'Event is already exists.Please try another one.';
		}
		else
		{
			$result = $this->Event_model->updateEventDetails($id,$Arr);
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
     * get the details of Event using Event id.
     */
    public function getEventDetailsById()
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
            $result = $this->Event_model->getEventDetailsById($Id);
			$result1 = $this->Event_model->getEventImgDetailsById($Id);
			// $result2 = $this->Event_model->getCatDetailsById($Id);
			foreach ($result1 as $key => $img) {
				$result1[$key]->imageName = $img->image;

				$result1[$key]->image = base_url().'/uploads/events/'.$img->image;
			}
			
            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["Event_data"] = $result;
				$response["image_data"] = $result1;
				// $response["category_data"] = $result2;


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
     * delete the details of Event using Event id.
     */

	 public function deleteEvent()
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
				$response["response_message"] = "Sorry, you do not have permission to modify another user's Events.";
				echo json_encode($response);
        		exit();
			}
			 $result = $this->Event_model->deleteEvent($Id);
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
     * upload documents of Event.
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
			
		$folder = 'uploads/events';
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
     * delete the details of Event using Event id.
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
			 $result = $this->Event_model->deleteDoc($Id);
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

	 public function updateCategory()
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
 
			 $Id = $data->eventId;
			 $catIds = $data->catIds;
			 $Arr = ['event_category'=>$catIds];
			 $result = $this->Event_model->updateCategory($Id,$Arr);
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
