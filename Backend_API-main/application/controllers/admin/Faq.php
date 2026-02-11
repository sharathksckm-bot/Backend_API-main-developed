<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Methods: GET, POST');
/**
 * Faq Controller
 *
 * @category   Controllers
 * @package    Admin
 * @subpackage Faq
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    25 JAN 2024
 *
 * Class Faq handles all the operations related to displaying list, creating Faq, update, and delete.
 */

if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}
class Faq extends CI_Controller
{
    /**
     * Constructor
     *
     * Loads necessary libraries, helpers, and models for the Faq controller.
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
        $this->load->model("admin/Faq_model", "", true);
        $this->load->library('Utility');
    }

    /*** Get list of Faq */
    public function getFaqList()
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
            Utility::validateSession($userData->iat, $userData->exp);
            $tokenSession = Utility::tokenSession($userData);

            $columns = array(
                0 => 'id',
                1 => 'heading',
                2 => 'category'
            );
            $limit = $data->length;
            $start = ($data->draw - 1) * $limit;
            $orderColumn = $columns[$data->order[0]->column];
            $orderDir = $data->order[0]->dir;
            $totalData = $this->Faq_model->countAllFaq();
            $totalFiltered = $totalData;
            if (!empty($data->search->value)) {

                $search = $data->search->value;
                $totalFiltered = $this->Faq_model->countFilteredFaq($search);
                $Faq = $this->Faq_model->getFilteredFaq($search, $start, $limit, $orderColumn, $orderDir);
            } else {
                $Faq = $this->Faq_model->getAllFaq($start, $limit, $orderColumn, $orderDir);
            }

            $datas = array();
            foreach ($Faq as $fq) {

                $nestedData = array();
                $nestedData['id'] = $fq->id;
                $nestedData['heading'] = $fq->heading;
                $nestedData['category'] = $fq->category;
                $nestedData['description'] = $fq->description;
                $nestedData['status'] = $fq->status;


                $datas[] = $nestedData;
            }

            $json_data = array(
                'draw' => intval($data->draw),
                'recordsTotal' => intval($totalData),
                'recordsFiltered' => intval($totalFiltered),
                'data' => $datas
            );

            echo json_encode($json_data);
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
            echo json_encode($response);
            exit();
        }
        exit;
    }

    /*** insert details of Faq */
    public function insertFaqDetails()
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
        Utility::validateSession($userData->iat, $userData->exp);
        $tokenSession = Utility::tokenSession($userData);
        if ($data) {
            $categoryid = $data->categoryid;
            $heading = $data->heading;
            $description = $data->description;
            $status = $data->status;
            $Arr = ['categoryid' => $categoryid, 'heading' => $heading, 'description' => $description, 'status' => $status];
            $chkIfExists = $this->Faq_model->chkIfExists($heading);
            if ($chkIfExists > 0) {
                $response["response_code"] = 300;
                $response["response_message"] = 'Faqs is already exists.Please try another one.';
            } else {
                $result = $this->Faq_model->insertFaqDetails($Arr);
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
            echo json_encode($response);
            exit();
        }
        echo json_encode($response);
        exit;
    }

    /*** update details of Faq */
    public function updateFaqDetails()
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
        Utility::validateSession($userData->iat, $userData->exp);
        $tokenSession = Utility::tokenSession($userData);
        if ($data) {
            $id = $data->id;
            $categoryid = $data->categoryid;
            $heading = $data->heading;
            $description = $data->description;
            $status = $data->status;
            $Arr = ['categoryid' => $categoryid, 'heading' => $heading, 'description' => $description, 'status' => $status];

            $chkIfExists = $this->Faq_model->chkWhileUpdate($heading, $id);
            if ($chkIfExists > 0) {
                $response["response_code"] = 300;
                $response["response_message"] = 'Faq is already exists.Please try another one.';
            } else {
                $result = $this->Faq_model->updateFaqDetails($id, $Arr);
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
            echo json_encode($response);
            exit();
        }
        echo json_encode($response);
        exit;
    }
    /**
     * get the details of Faq using Faq id.
     */
    public function getFaqDetailsById()
    {
        $data = json_decode(file_get_contents('php://input'));
        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data['status'] = 'ok';
            echo json_encode($data);
            exit;
        }
        if ($data) {
            $headers = apache_request_headers();
            $token = str_replace("Bearer ", "", $headers['Authorization']);
            $kunci = $this->config->item('jwt_key');
            $userData = JWT::decode($token, $kunci);
            Utility::validateSession($userData->iat, $userData->exp);
            $tokenSession = Utility::tokenSession($userData);
            $Id = $data->id;
            $result = $this->Faq_model->getFaqDetailsById($Id);
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
     * delete the details of Faq using Faq id.
     */

    public function deleteFaq()
    {
        $data = json_decode(file_get_contents('php://input'));
        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data['status'] = 'ok';
            echo json_encode($data);
            exit;
        }
        if ($data) {
            $headers = apache_request_headers();
            $token = str_replace("Bearer ", "", $headers['Authorization']);
            $kunci = $this->config->item('jwt_key');
            $userData = JWT::decode($token, $kunci);
            Utility::validateSession($userData->iat, $userData->exp);
            $tokenSession = Utility::tokenSession($userData);

            $Id = $data->id;
            $result = $this->Faq_model->deleteFaq($Id);
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
     * get the Faq using category id.
     */

    public function allFaqByCatagory()
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
            Utility::validateSession($userData->iat, $userData->exp);
            $tokenSession = Utility::tokenSession($userData);

            $Id =  isset($data->id) ? ($data->id) : '143';
            //echo $collegeId;exit;
            $result = $this->Faq_model->allFaqByCatagory($Id);
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["response_data"] = $result;
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }
        echo json_encode($response);
        exit();
    }
	/**
     * Update the faq ID in college table.
     */
    public function updateFaqForCollege()
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
            Utility::validateSession($userData->iat, $userData->exp);
            $tokenSession = Utility::tokenSession($userData);

            // $faqId = $data->id;
            $faqsData = $data->faqsData;

            $collegeId = $data->college;
           

            $groupedFaqIds = [];

            foreach ($faqsData as $data) {
                if (isset($data->faqs) && is_array($data->faqs)) {
                foreach ($data->faqs as $faq) {
                    $categoryId = $faq->categoryid;
                    $faqId = $faq->id;
                    
                    // Check if the category ID exists in the grouped array
                    if (isset($groupedFaqIds[$categoryId])) {
                        // Append the FAQ ID to the existing category ID
                        $groupedFaqIds[$categoryId][] = $faqId;
                    } else {
                        // Initialize a new entry for the category ID and FAQ ID
                        $groupedFaqIds[$categoryId] = [$faqId];
                    }
                   // echo "567890";exit;
                }}
            }
            
            // Now, $groupedFaqIds contains FAQ IDs grouped by category ID
            // Iterate over $groupedFaqIds and save/update each group
            foreach ($groupedFaqIds as $categoryId => $faqIds) {
                // If there are multiple FAQ IDs for a category, implode them
                $faqIdsString = implode(",", $faqIds);
            
                // Prepare details array for updating FAQ
                $details = [
                    'collegeid' => $collegeId,
                    'faq_ids' => $faqIdsString,
                    'faq_type' => $categoryId,
                ];
                
               // Check if there is any previous data for this category
               $hasDataForCategory = $this->Faq_model->hasDataForCategory($collegeId,$categoryId);
            //    print_r($hasDataForCategory);exit;
                if ($hasDataForCategory > 0 ) {
                    // Update the FAQ using the model
                    $result = $this->Faq_model->updateFaq($details, $categoryId);
                } else {
                    
                    // Save the data for the category
                    $result1 = $this->Faq_model->saveFaq($details);
                }
            }
            // print_r($hasDataForCategory);exit;
            if(true)
            {
			// $faq_type = $data->faq_type;
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            // $response["response_ data"] = $result;
            }
            else
            {
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
    
    public function deleteCollegeFaq()
    {
        $data = json_decode(file_get_contents('php://input'));
        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data['status'] = 'ok';
            echo json_encode($data);
            exit;
        }
        if ($data) {
            $headers = apache_request_headers();
            $token = str_replace("Bearer ", "", $headers['Authorization']);
            $kunci = $this->config->item('jwt_key');
            $userData = JWT::decode($token, $kunci);
            Utility::validateSession($userData->iat, $userData->exp);
            $tokenSession = Utility::tokenSession($userData);

            $Id = $data->id;
            $result = $this->Faq_model->deleteCollegeFaq($Id);
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
