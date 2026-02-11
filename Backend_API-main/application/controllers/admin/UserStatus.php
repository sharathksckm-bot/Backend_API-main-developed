<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Methods: GET, POST');
/**
 * User Status Controller
 *
 * @category   Controllers
 * @package    Admin
 * @subpackage UserStatus
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    11 JAN 2024
 *
 * Class UserStatus handles all the operations related to displaying list, creating UserStatus, update, and delete.
 */

if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}

class UserStatus extends CI_Controller
{
    /**
     * Constructor
     *
     * Loads necessary libraries, helpers, and models for the User Status controller.
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('admin/Userstatus_model', '', TRUE);
		$this->load->library('Utility');

    }

	
		/**
     	* get list of user status.
     	*/
		 public function list()
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
				$columns = array(
					0 => 'id',
					1 => 'name',
				);
				$limit = $data->length;
				$start = ($data->draw - 1) * $limit;
				$orderColumn = $columns[$data->order[0]->column];
				$orderDir = $data->order[0]->dir;
				$totalData = $this->Userstatus_model->countAllStatus();
				$totalFiltered = $totalData;
		
				if (!empty($data->search->value)) {
					$search = $data->search->value;
					$totalFiltered = $this->Userstatus_model->countFilteredStatus($search);
					$status = $this->Userstatus_model->getFilteredStatus($search, $start, $limit, $orderColumn, $orderDir);
		
				   } else {
					$status = $this->Userstatus_model->getAllStatus($start, $limit, $orderColumn, $orderDir);
				}
		
				$datas = array();
				foreach ($status as $sts) {
				   
					$nestedData = array();
					$nestedData['id'] = $sts->id;
					$nestedData['name'] = $sts->name;  
					
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
     	* insert data of user status.
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

				$Status = $data->Status;
				$Arr = array("name"=>$Status);
				$chkUserType = $this->Userstatus_model->chkUserStatus($Status);
				if($chkUserType > 0)
				{
					$response["response_code"] = 300;
                	$response["response_message"] = 'User status is already exists.Please try another one.';
				}
				else
				{
					$result = $this->Userstatus_model->insert($Arr);
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
     	* update data of user status.
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

				$Id = $data->Id;
				$Status = $data->Status;
				$Arr = array("name"=>$Status);
				$checkstatus = $this->Userstatus_model->chkWhileUpdate($Id, $Status);
				if($checkstatus > 0)
				{
					$response["response_code"] = 300;
                	$response["response_message"] = 'User status is already exists.Please try another one.';
				}
				else
				{
				$result = $this->Userstatus_model->update($Id, $Arr);;
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
				$StatusId = $data->StatusId;
				 $result = $this->Userstatus_model->getDataById($StatusId);
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
				$StatusId = $data->StatusId;
				 $result = $this->Userstatus_model->delete($StatusId);
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
