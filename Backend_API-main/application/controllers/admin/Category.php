<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Methods: GET, POST');
/**
 * Category Controller
 *
 * @category   Controllers
 * @package    Admin
 * @subpackage Category
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    18 JAN 2024
 *
 * Class Category handles all the operations related to displaying list, creating Category, update, and delete.
 */

if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}
class Category extends CI_Controller
{
	 /**
     * Constructor
     *
     * Loads necessary libraries, helpers, and models for the Category controller.
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
        $this->load->model("admin/Category_model", "", true);
		$this->load->library('Utility');

    }

	 /**
     	* get server side datatable data of categories.
     	*/
		 public function getCategoryList()
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
					 0 => 'id',
					 1 => 'catname',
					 2 => 'post_url',
					 3 => 'type',	
					 4 => 'topmenu',
					 5 => 'menuorder',
					 6 => 'status'
				 );
				 $type = $data->type;
				 $limit = $data->length;
				 $start = ($data->draw - 1) * $limit;
				 $orderColumn = $columns[$data->order[0]->column];
				 $orderDir = $data->order[0]->dir;
				 $totalData = $this->Category_model->countAllCategory($type);
				 
				 $totalFiltered = $totalData;
		 
				 if (!empty($data->search->value)) {
					 $search = $data->search->value;
					 $totalFiltered = $this->Category_model->countFilteredCategory($search,$type);
					 $category = $this->Category_model->getFilteredCategory($search, $start, $limit, $orderColumn, $orderDir,$type);

					} else {
					 $category = $this->Category_model->getAllCategory($start, $limit, $orderColumn, $orderDir,$type);
				 }
		 
				 $datas = array();
				 foreach ($category as $categories) {
					
					 $nestedData = array();
					 $nestedData['id'] = $categories->id;
					 $nestedData['catname'] = $categories->catname;  
					 $nestedData['post_url'] = $categories->post_url; 
					 $nestedData['type'] = $categories->type;
					 $nestedData['topmenu'] = $categories->topmenu;
					 $nestedData['menuorder'] = $categories->menuorder;
					 $nestedData['status'] = $categories->status;

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
     * insert the details of Categories.
     */
    public function insertCategoryDetails()
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

            $CatName = $data->CatName;
            $TopMenu = $data->TopMenu;
            $MenuOrder = $data->MenuOrder;
            $Status = $data->Status;
			$Type = $data->type;

            $Arr = [
                "catname" => $CatName,
                "topmenu" => $TopMenu,
                "menuorder" => $MenuOrder,
                "status" => $Status,
				"type" => $Type,

            ];
            $chkIsExtists = $this->Category_model->chkIsExtists($CatName,$Type,$MenuOrder);
            if ($chkIsExtists > 0) {
                $response["response_code"] = 300;
                $response["response_message"] =
                    "The category details you provided is already associated with an existing account.Use another category to register.";
            } else {
                $result = $this->Category_model->insertCategoryDetails($Arr);
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
     * get the details of category using catId.
     */
    public function getCategoryDetailsById()
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
            $CatId = $data->CatId;
			$Type = $data->type;

            $result = $this->Category_model->getCategoryDetailsById($CatId,$Type);
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
     * update the details of Categories.
     */
    public function updateCategoryDetails()
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
			$Id = $data->CatId;
            $CatName = $data->CatName;
            $TopMenu = $data->TopMenu;
            $MenuOrder = $data->MenuOrder;
            $Status = $data->Status;
			$Type = $data->type;
            $Arr = [
                "catname" => $CatName,
                "topmenu" => $TopMenu,
                "menuorder" => $MenuOrder,
                "status" => $Status,
				"type" => $Type,
              
            ];
            $chkIsExtists = $this->Category_model->chkWhileUpdate($Id,$CatName,$Type,$MenuOrder);
            if ($chkIsExtists > 0) {
                $response["response_code"] = 300;
                $response["response_message"] =
                    "The category you provided is already associated with an existing account.Use another category to register.";
            } else {
                $result = $this->Category_model->updateCategoryDetails($Id,$Arr);
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
     * delete the details of category using CatId.
     */

	 public function deleteCategoryDetails()
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
 
			 $CatId = $data->CatId;
			 $result = $this->Category_model->deleteCategoryDetails($CatId);
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
     * get the details of category using type.
     */
    public function getCategoryListByType()
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
			$Type = $data->type;

            $result = $this->Category_model->getCategoryListByType($Type);
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
	public function getCategoryForCourse()
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

			$result = $this->Category_model->getCategoryForCourse();
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

	public function getAcCategoryForCourse()
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

			$result = $this->Category_model->getAcCategoryForCourse();
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


	public function getSubCategoryByCatId()
    {
        $data = json_decode(file_get_contents('php://input'));
		if($this->input->server('REQUEST_METHOD')=='OPTIONS')
		{
			$data['status']='ok';
			echo json_encode($data);exit;
		}
		if($data){
			$headers = apache_request_headers();
			$token = str_replace("Bearer ", "", $headers['Authorization']);
			$kunci = $this->config->item('jwt_key');
			$userData = JWT::decode($token, $kunci);
			Utility::validateSession($userData->iat,$userData->exp);
        	$tokenSession = Utility::tokenSession($userData);

			$catId = $data->catId;
			$acCatId = $data->acCatId;
			$result = $this->Category_model->getSubCategoryByCatId($catId,$acCatId);
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
			echo json_encode($response);exit;
		
	}


	   public function getCategories() 
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
			if($data){
				$headers = apache_request_headers();
				$token = str_replace("Bearer ", "", $headers['Authorization']);
				$kunci = $this->config->item('jwt_key');
				$userData = JWT::decode($token, $kunci);
				Utility::validateSession($userData->iat,$userData->exp);
				$tokenSession = Utility::tokenSession($userData);
				$search_category = isset($data->search_category)?$data->search_category:'';
				$result = $this->Category_model->getCategories($search_category);
				if ($result) {
					$response["response_code"] = "200";
					$response["response_message"] = "Success";
					$response["coursedata"] = $result;
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


		public function getSubCategory() 
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
			if($data)
			{
				$headers = apache_request_headers();
				$token = str_replace("Bearer ", "", $headers['Authorization']);
				$kunci = $this->config->item('jwt_key');
				$userData = JWT::decode($token, $kunci);
				Utility::validateSession($userData->iat,$userData->exp);
				$tokenSession = Utility::tokenSession($userData);
				$search_category = isset($data->search_category)?$data->search_category:'';
				$categoryId = isset($data->categoryId)?$data->categoryId:'';
				$result = $this->Category_model->getSubCategory($search_category,$categoryId);
				if ($result) {
					$response["response_code"] = "200";
					$response["response_message"] = "Success";
					$response["coursedata"] = $result;
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
     	* get server side datatable data of sub categories.
     	*/
		 public function getSubCategoryList()
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
					 0 => 'id',
					 1 => 'name',
					 2 => 'eligibility',
					 3 => 'catname',	
					 4 => 'level',
					
				 );
				 $limit = $data->length;
				 $start = ($data->draw - 1) * $limit;
				 $orderColumn = $columns[$data->order[0]->column];
				 $orderDir = $data->order[0]->dir;
				 $totalData = $this->Category_model->countAllSubCategory();
				 
				 $totalFiltered = $totalData;
		 
				 if (!empty($data->search->value) || !empty($data->search->level) || !empty($data->search->cat) ) {
					 $search = $data->search->value;
					 $level = isset($data->search->level)?$data->search->level:'';
					 $cat = isset($data->search->cat)?$data->search->cat:'';

					 $totalFiltered = $this->Category_model->countFilteredSubCategory($search,$level,$cat);
					 $category = $this->Category_model->getFilteredSubCategory($search,$level,$cat, $start, $limit, $orderColumn, $orderDir);

					} else {
					 $category = $this->Category_model->getAllSubCategory($start, $limit, $orderColumn, $orderDir);
				 }
		 
				 $datas = array();
				 foreach ($category as $categories) {
					
					 $nestedData = array();
					 $nestedData['id'] = $categories->id;
					 $nestedData['categoyrname'] = $categories->catname;  
					 $nestedData['name'] = $categories->subcat; 
					 $nestedData['eligibility'] = $categories->eligibility;
					 $nestedData['level'] = $categories->level;
				// 	 $nestedData['menuorder'] = $categories->menuorder;
				// 	 $nestedData['status'] = $categories->status;

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
     * insert the details of SUB Categories.
     */
    public function insertSubCategoryDetails()
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

            $catid = $data->catid;
            $level = $data->level;
            $eligibility = $data->eligibility;
            $name = $data->name;

            $Arr = [
                "parent_category" => $catid,
                "academic_category" => $level,
                "eligibility" => $eligibility,
                "name" => $name

            ];
            $chkIsExtists = $this->Category_model->chkIsSubCatExtists($catid,$level,$name);
            if ($chkIsExtists > 0) {
                $response["response_code"] = 300;
                $response["response_message"] =
                    "The sub category details you provided is already associated with an existing account.Use another category to register.";
            } else {
                $result = $this->Category_model->insertSubCategoryDetails($Arr);
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
     * update the details of Sub Categories.
     */
    public function updateSubCategoryDetails()
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
            $catid = $data->catid;
            $level = $data->level;
            $eligibility = $data->eligibility;
            $name = $data->name;

            $Arr = [
                "parent_category" => $catid,
                "academic_category" => $level,
                "eligibility" => $eligibility,
                "name" => $name

            ];
            $chkIsExtists = $this->Category_model->chkSubCatWhileUpdate($id,$catid,$level,$name);
            if ($chkIsExtists > 0) {
                $response["response_code"] = 300;
                $response["response_message"] =
                    "The sub category you provided is already associated with an existing account.Use another category to register.";
            } else {
                $result = $this->Category_model->updateSubCategoryDetails($id,$Arr);
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
     * get the details of Sub category using catId.
     */
    public function getSubCategoryDetailsById()
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
			

            $result = $this->Category_model->getSubCategoryDetailsById($id);
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
     * delete the details of sub category using id.
     */

	 public function deleteSubCategoryDetails()
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
			 $result = $this->Category_model->deleteSubCategoryDetails($id);
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
