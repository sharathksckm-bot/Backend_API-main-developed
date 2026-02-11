<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Methods: GET, POST');
/**
 * Facilities Controller
 *
 * @category   Controllers
 * @package    Admin
 * @subpackage Facilities
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    22 JAN 2024
 *
 * Class Facilities handles all the operations related to displaying list, creating Facilities, update, and delete.
 */

if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}
class Facilities extends CI_Controller
{
    /**
     * Constructor
     *
     * Loads necessary libraries, helpers, and models for the Facilities controller.
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
        $this->load->model("admin/Facilities_model", "", true);
		$this->load->library('Utility');

    }

	/**
     * get the list of Facilities.
     */
	public function getFacilitiesList()
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
					 0 => 'title',
					 1 => 'description',
					 2 => 'status',

				 );
		 
				 $limit = $data->length;
				 $start = ($data->draw - 1) * $limit;
				 $orderColumn = $columns[$data->order[0]->column];
				 $orderDir = $data->order[0]->dir;
				 $totalData = $this->Facilities_model->countAllFacilities();
				 $totalFiltered = $totalData;
		 
				 if (!empty($data->search->value)) {
					 $search = $data->search->value;
					 $totalFiltered = $this->Facilities_model->countFilteredFacilities($search);
					 $Facilities = $this->Facilities_model->getFilteredFacilities($search, $start, $limit, $orderColumn, $orderDir);

					} else {
					 $Facilities = $this->Facilities_model->getAllFacilities($start, $limit, $orderColumn, $orderDir);
				 }
		 
				 $datas = array();
				 foreach ($Facilities as $fac) {
					
					 $nestedData = array();
					 $nestedData['id'] = $fac->id;
					 $nestedData['title'] = $fac->title;  
					 $nestedData['description'] = $fac->description; 
					 $nestedData['icon'] = $fac->icon; 
					 $nestedData['status'] = $fac->status; 

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
     * insert the details of Facilities.
     */
	public function insertFacilities()
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

				$title = $data->title;
				$description = $data->description;
				$icon = $data->icon;
				$status = $data->status;
				$chkIsExtists = $this->Facilities_model->chkIsExtists($title);
				if($chkIsExtists > 0)
				{
					$response["response_code"] = 300;
                	$response["response_message"] = 'Facilities is already exists.Please try another one.';
				}
				else
				{
				$Arr = ['title'=>$title, 'description'=>$description, 'icon'=>$icon, 'status'=>$status];
				$result = $this->Facilities_model->insertFacilities($Arr);
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
			 echo json_encode($response);
				exit();
	}

	/**
     * update the details of Facilities .
     */
	public function updateFacilities()
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

				$id = $data->id;
				$title = $data->title;
				$description = $data->description;
				$icon = $data->icon;
				$status = $data->status;
				$chkIsExtists = $this->Facilities_model->chkWhileUpdate($id,$title);
				if($chkIsExtists > 0)
				{
					$response["response_code"] = 300;
                	$response["response_message"] = 'Facilities is already exists.Please try another one.';
				}
				else
				{
				$Arr = ['title'=>$title, 'description'=>$description, 'icon'=>$icon, 'status'=>$status];
				$result = $this->Facilities_model->updateFacilities($id,$Arr);
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
			 echo json_encode($response);
				exit();
	}

	/**
     * get the details of Facilities using Facilities id.
     */
    public function getFacilitiesDetailsById()
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
            $result = $this->Facilities_model->getFacilitiesDetailsById($Id);
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
     * delete the details of Facilities using Facilities id.
     */

	 public function deleteFacilities()
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
			 $result = $this->Facilities_model->deleteFacilities($Id);
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

	 public function getFacilities()
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

			$result = $this->Facilities_model->getFacilities();
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

}
