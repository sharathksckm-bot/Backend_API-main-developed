<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Methods: GET, POST');
/**
 * City Controller
 *
 * @category   Controllers
 * @package    Admin
 * @subpackage City
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    25 JAN 2024
 *
 * Class city handles all the operations related to displaying list, creating city, update, and delete.
 */

if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}
class City extends CI_Controller
{
    /**
     * Constructor
     *
     * Loads necessary libraries, helpers, and models for the city controller.
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
        $this->load->model("admin/City_model", "", true);
		$this->load->library('Utility');

    }

	/*** Get list of city */
	public function getCityList()
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

		$columns = array(
			0 => 'country',
			1 => 'id',
		);
		$limit = $data->length;
		$start = ($data->draw - 1) * $limit;
		$orderColumn = $columns[$data->order[0]->column];
		$orderDir = $data->order[0]->dir;
		$totalData = $this->City_model->countAllCity();
		$totalFiltered = $totalData;
		if (!empty($data->search->value)) {

			$search = $data->search->value;
			$totalFiltered = $this->City_model->countFilteredCity($search);
			$city = $this->City_model->getFilteredCity($search, $start, $limit, $orderColumn, $orderDir);

		   } else {
			$city = $this->City_model->getAllCity($start, $limit, $orderColumn, $orderDir);
			//print_r($city);exit;
			  
		}

		$datas = array();
		foreach ($city as $ct) {
		   $ct->image = base_url("uploads/City/" . $ct->image);
			$nestedData = array();
			$nestedData['id'] = $ct->id;
			$nestedData['city'] = $ct->city;
			$nestedData['country'] = $ct->country;
			$nestedData['state'] = $ct->statename;
			$nestedData['image'] = $ct->image;

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
     * get the details of city using country id.
     */
    public function getCityDetailsByStateId()
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
            $stateId = $data->stateId;
            $result = $this->City_model->getCityDetailsByCntId($stateId);
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

	/*** insert details of city */
	public function insertCityDetails()
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
		$city = $data->city;
		$countryid = $data->countryId;
		$stateid = $data->stateid;
		$view_in_menu = $data->view_in_menu;
		$image = $data->image;
		$str = preg_replace('/[^A-Za-z0-9\. -]/', ' ', $city);
		$post_url = preg_replace('/\s+/', '-', strtolower($str));
		$Arr = ['city'=>$city,'countryid'=>$countryid,'view_in_menu'=>$view_in_menu,'stateid'=>$stateid,'post_url'=>$post_url,'image'=>$image];
		$chkIfExists = $this->City_model->chkIfExists($countryid,$stateid,$post_url);
		if($chkIfExists > 0)
		{
			$response["response_code"] = 300;
			$response["response_message"] = 'city is already exists.Please try another one.';
		}
		else
		{
			$result = $this->City_model->insertCityDetails($Arr);
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

	/*** update details of city */
	public function updateCityDetails()
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
		$id = $data->id;
		$city = $data->city;
		$countryid = $data->countryId;
		$stateid = $data->stateid;
		$view_in_menu = $data->view_in_menu;
		$image = $data->image;
		$str = preg_replace('/[^A-Za-z0-9\. -]/', ' ', $city);
		$post_url = preg_replace('/\s+/', '-', strtolower($str));
		$Arr = ['city'=>$city,'countryid'=>$countryid,'view_in_menu'=>$view_in_menu,'stateid'=>$stateid,'post_url'=>$post_url,'image'=>$image];
		$chkIfExists = $this->City_model->chkWhileUpdate($countryid,$stateid,$id,$post_url);
		if($chkIfExists > 0)
		{
			$response["response_code"] = 300;
			$response["response_message"] = 'city is already exists.Please try another one.';
		}
		else
		{
			$result = $this->City_model->updateCityDetails($id,$Arr);
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
     * get the details of city using city id.
     */
    public function getCityDetailsById()
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
            $result = $this->City_model->getCityDetailsById($Id);
          //  print_r($result->image);exit;
              // Assign the full image path
             $result->image = base_url("uploads/City/" . $result->image);
            
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
     * delete the details of City using City id.
     */

	 public function deleteCity()
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
			 $result = $this->City_model->deleteCity($Id);
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

  public function uploadCityDocs()
    {
       // echo "ttt";exit;
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
	/*	$type = $_POST['type'];
		switch ($type) {
			case 'IMAGE':
				$folder = 'uploads/City';
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
		}*/
	$folder = 'uploads/City';
				$allowed = [
					"jpg" => "image/jpeg",
					"jpeg" => "image/jpeg",
					"png" => "image/png"
				];
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
				$response['response_message'] = "please Upload the image";
			}
		
			echo json_encode($response);exit;
		}

}
