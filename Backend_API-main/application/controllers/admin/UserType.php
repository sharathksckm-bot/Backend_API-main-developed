<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Methods: GET, POST');
/**
 * User Type Controller
 *
 * @category   Controllers
 * @package    Admin
 * @subpackage UserType
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    11 JAN 2024
 *
 * Class UserType handles all the operations related to displaying list, creating UserType, update, and delete.
 */

if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}

class UserType extends CI_Controller
{
    /**
     * Constructor
     *
     * Loads necessary libraries, helpers, and models for the User type controller.
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('admin/Usertype_model', '', TRUE);
		$this->load->library('Utility');

    }

	
		/**
     	* get list of user type.
     	*/
		 public function list()
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
				if($data)
				{
					$columns = array(
						0 => 'id',
						1 => 'name',
					);
					$limit = $data->length;
					$start = ($data->draw - 1) * $limit;
					$orderColumn = $columns[$data->order[0]->column];
					$orderDir = $data->order[0]->dir;
					$totalData = $this->Usertype_model->countAllType();
					$totalFiltered = $totalData;
			
					if (!empty($data->search->value)) {
						$search = $data->search->value;
						$totalFiltered = $this->Usertype_model->countFilteredType($search);
						$Type = $this->Usertype_model->getFilteredType($search, $start, $limit, $orderColumn, $orderDir);
			
					   } else {
						$Type = $this->Usertype_model->getAllType($start, $limit, $orderColumn, $orderDir);
					}
			
					$datas = array();
					foreach ($Type as $typ) {
					   
						$nestedData = array();
						$nestedData['id'] = $typ->id;
						$nestedData['name'] = $typ->name;  
						
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
     	* insert data of user type.
     	*/
		 public function insert()
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
				$UserType = $data->UserType;
				$Arr = array("name"=>$UserType);
				$chkUserType = $this->Usertype_model->chkUserType($UserType);
				if($chkUserType > 0)
				{
					$response["response_code"] = 300;
                	$response["response_message"] = 'User Role is already exists.Please try another one.';
				}
				else
				{
					$result = $this->Usertype_model->insert($Arr);
					if($result)
					{
						$response["response_code"] = "200";
						$response["response_message"] = "Success";
						$response["response_data"] = $result;
					} else {
						$response["response_code"] = "400";
						$response["response_message"] = "Failed";
					}
				}
			 }
			 else
			 {
				$response["response_code"] = "500";
				$response["response_message"] = "Data is null";
			 }
			 echo json_encode($response);exit;
		}

		 /**
     	* update data of user type.
     	*/
		 public function update()
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
				$TypeId = $data->TypeId;
				$UserType = $data->UserType;
				$Arr = array("name"=>$UserType);
				$type = $this->Usertype_model->chkWhileUpdate($TypeId, $UserType);
				if($type > 0)
				{
					$response["response_code"] = 300;
                	$response["response_message"] = 'User Role is already exists.Please try another one.';
				}
				else
				{
				$result = $this->Usertype_model->update($TypeId, $Arr);;
				if($result)
				{
					$response["response_code"] = "200";
					$response["response_message"] = "Success";
					$response["response_data"] = $result;
				} else {
					$response["response_code"] = "400";
					$response["response_message"] = "Failed";
				}
			}
				
			 }
			 else
			 {
				$response["response_code"] = "500";
				$response["response_message"] = "Data is null";
			 }
			 echo json_encode($response);exit;
		}
 		/**
     	* get data of user type by id.
     	*/
		 public function getDataById()
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
				$TypeId = $data->TypeId;
				 $result = $this->Usertype_model->getDataById($TypeId);
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

		 /**
     	* delete data of user type by id.
     	*/
		 public function delete()
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
				$TypeId = $data->TypeId;
				 $result = $this->Usertype_model->delete($TypeId);
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

}
