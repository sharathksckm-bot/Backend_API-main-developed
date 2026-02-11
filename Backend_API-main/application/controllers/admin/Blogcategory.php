<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Methods: GET, POST');
/**
 * Blogcategory Controller
 *
 * @category   Controllers
 * @package    Admin
 * @subpackage Blogcategory
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    25 JAN 2024
 *
 * Class Blogcategory handles all the operations related to displaying list, creating Blogcategory, update, and delete.
 */

if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}
class Blogcategory extends CI_Controller
{
    /**
     * Constructor
     *
     * Loads necessary libraries, helpers, and models for the Blogcategory controller.
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
        $this->load->model("admin/Blogcategory_model", "", true);
		$this->load->library('Utility');

    }

	/*** Get list of Blogcategory */
	public function getBlogcategoryList()
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
			0 => 'id',
			1 => 'name',
		);
		$limit = $data->length;
		$start = ($data->draw - 1) * $limit;
		$orderColumn = $columns[$data->order[0]->column];
		$orderDir = $data->order[0]->dir;
		$totalData = $this->Blogcategory_model->countAllBlogcategory();
		$totalFiltered = $totalData;
		if (!empty($data->search->value)) {

			$search = $data->search->value;
			$totalFiltered = $this->Blogcategory_model->countFilteredBlogcategory($search);
			$Blogcategory = $this->Blogcategory_model->getFilteredBlogcategory($search, $start, $limit, $orderColumn, $orderDir);

		   } else {
			$Blogcategory = $this->Blogcategory_model->getAllBlogcategory($start, $limit, $orderColumn, $orderDir);
		}

		$datas = array();
		foreach ($Blogcategory as $bc) {
		   
			$nestedData = array();
			$nestedData['id'] = $bc->id;
			$nestedData['name'] = $bc->name;
			$nestedData['status'] = $bc->status;


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

	/*** insert details of Blogcategory */
	public function insertBlogcategoryDetails()
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
		$name = $data->name;
		$status = $data->status;
		$str = preg_replace('/[^A-Za-z0-9\. -]/', ' ', $name);
		$post_url = preg_replace('/\s+/', '-', strtolower($str));
		$Arr = ['name'=>$name,'status'=>$status,'post_url'=>$post_url];
		$chkIfExists = $this->Blogcategory_model->chkIfExists($name);
		if($chkIfExists > 0)
		{
			$response["response_code"] = 300;
			$response["response_message"] = 'Blogcategory is already exists.Please try another one.';
		}
		else
		{
			$result = $this->Blogcategory_model->insertBlogcategoryDetails($Arr);
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

	/*** update details of Blogcategory */
	public function updateBlogcategoryDetails()
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
		$name = $data->name;
		$status = $data->status;
		$str = preg_replace('/[^A-Za-z0-9\. -]/', ' ', $name);
		$post_url = preg_replace('/\s+/', '-', strtolower($str));
		$Arr = ['name'=>$name,'status'=>$status,'post_url'=>$post_url];
		$chkIfExists = $this->Blogcategory_model->chkWhileUpdate($name,$id);
		if($chkIfExists > 0)
		{
			$response["response_code"] = 300;
			$response["response_message"] = 'Blogcategory is already exists.Please try another one.';
		}
		else
		{
			$result = $this->Blogcategory_model->updateBlogcategoryDetails($id,$Arr);
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
     * get the details of Blogcategory using Blogcategory id.
     */
    public function getBlogcategoryDetailsById()
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
            $result = $this->Blogcategory_model->getBlogcategoryDetailsById($Id);
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
     * delete the details of Blogcategory using Blogcategory id.
     */

	 public function deleteBlogcategory()
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
			 $result = $this->Blogcategory_model->deleteBlogcategory($Id);
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

	 public function getBlogCategory()
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
 
			 $result = $this->Blogcategory_model->getBlogCategory();
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
