<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Methods: GET, POST');
/**
 * RankCategories Controller
 *
 * @category   Controllers
 * @package    Admin
 * @subpackage RankCategories
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    22 JAN 2024
 *
 * Class RankCategories handles all the operations related to displaying list, creating rank, update, and delete.
 */

if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}
class RankCategories extends CI_Controller
{
    /**
     * Constructor
     *
     * Loads necessary libraries, helpers, and models for the Rank Categories controller.
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
        $this->load->model("admin/Rankcategory_model", "", true);
		$this->load->library('Utility');

    }

	public function getRankCategoryList()
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
					 1 => 'is_active',

				 );
		 
				 $limit = $data->length;
				 $start = ($data->draw - 1) * $limit;
				 $orderColumn = $columns[$data->order[0]->column];
				 $orderDir = $data->order[0]->dir;
				 $totalData = $this->Rankcategory_model->countAllRankCategory();
				 $totalFiltered = $totalData;
		 
				 if (!empty($data->search->value)) {
					 $search = $data->search->value;
					 $totalFiltered = $this->Rankcategory_model->countFilteredRankCategory($search);
					 $RankCategory = $this->Rankcategory_model->getFilteredRankCategory($search, $start, $limit, $orderColumn, $orderDir);

					} else {
					 $RankCategory = $this->Rankcategory_model->getAllRankCategory($start, $limit, $orderColumn, $orderDir);
				 }
		 
				 $datas = array();
				 foreach ($RankCategory as $rnkcat) {
					
					 $nestedData = array();
					 $nestedData['id'] = $rnkcat->category_id;
					 $nestedData['title'] = $rnkcat->title;  
					 $nestedData['image'] = base_url() .'/uploads/rankimage/'. $rnkcat->image; 
					 $nestedData['status'] = $rnkcat->is_active; 

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
     * insert the details of rank category.
     */
    public function insertRankCategory()
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
			$UserRole = $userData->data->type;
			$UserStatus = $userData->data->status;

            $title = $data->title;
            $image = $data->image;

            $Arr = [
                "title" => $title,
                "image" => $image,
            ];
            $checkIsExists = $this->Rankcategory_model->checkIsExists($title);
            if ($checkIsExists > 0) {
                $response["response_code"] = 300;
                $response["response_message"] =
                    "The Rank category you provided is already associated with an existing account.Use another rank category to register.";
            } else {
                $result = $this->Rankcategory_model->insertRankCategory($Arr);
                if ($result) {
                    $response["response_code"] = "200";
                    $response["response_message"] = "Success";
                    $response["response_data"] = $result;
                } else {
                    $response["response_code"] = "400";
                    $response["response_message"] = "Failed";
                }
            }
		
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }

        echo json_encode($response);
        exit();
    }

	/**
     * update the details of rank category.
     */
    public function updateRankCategory()
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
			$UserRole = $userData->data->type;
			$UserStatus = $userData->data->status;

			$id = $data->id;
            $title = $data->title;
            $image = $data->image;

            $Arr = [
                "title" => $title,
                "image" => $image,
            ];
            $checkIsExists = $this->Rankcategory_model->checkWhileUpdate($id,$title);
            if ($checkIsExists > 0) {
                $response["response_code"] = 300;
                $response["response_message"] =
                    "The Rank category you provided is already associated with an existing account.Use another rank category to register.";
            } else {
                $result = $this->Rankcategory_model->updateRankCategory($id,$Arr);
                if ($result) {
                    $response["response_code"] = "200";
                    $response["response_message"] = "Success";
                    $response["response_data"] = $result;
                } else {
                    $response["response_code"] = "400";
                    $response["response_message"] = "Failed";
                }
            }
		
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }

        echo json_encode($response);
        exit();
    }

	/**
     * get the details of rank category using rankid.
     */
    public function getRnkCatDetailsById()
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
            $id = $data->id;
            $result = $this->Rankcategory_model->getRnkCatDetailsById($id);
			// $result->image = base_url().'/uploads/rankimage/'.$result->image;

			$result->imagepath = base_url().'/uploads/rankimage/'.$result->image;

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
     * delete the details of rank category using rankId.
     */

	 public function deleteRnkCatDetails()
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
			 
			 $id = $data->id;
			 $result = $this->Rankcategory_model->deleteRnkCatDetails($id);
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
     * upload images of rank category.
     */
    public function uploadimages()
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
			
			$folder = 'uploads/rankimage';
			if(!is_dir($folder)) {
				mkdir($folder, 0777, TRUE);
				}
				if(isset($_FILES["file"]) && $_FILES["file"]["error"] == 0)
				{
					$allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "png" => "image/png", "JPG" => "image/jpeg","JPEG" => "image/jpeg", "PNG" => "image/png", "PDF" =>"application/pdf");
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

public function getRankcategories()
  {
    $data = json_decode(file_get_contents('php://input'));

    if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
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
    $result = $this->Rankcategory_model->get_rankcategories();
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
