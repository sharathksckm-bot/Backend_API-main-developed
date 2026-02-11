<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST");
/**
 * Cutoff Controller
 *
 * @category   Controllers
 * @package    web
 * @subpackage Cutoff
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    22 APRIL 2024
 *
 * Class Cutoff handles all the operations related to displaying list, creating Cutoff, update, and delete of exames like (KCET,COMEDK,JEE )
 */

if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}
class Cutoff extends CI_Controller
{
    /**
     * Constructor
     *
     * Loads necessary libraries, helpers, and models for the Cutoff controller.
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
        $this->load->model("apps/Cutoff_model", "", true);
        $this->load->model("apps/College_model", "", true);

		$this->load->library('Utility');

    }

    public function getKCETCutOff()
    {
		//echo "testing...";exit;
        $data = json_decode(file_get_contents('php://input'));
		if($this->input->server('REQUEST_METHOD')=='OPTIONS')
		{
			$data['status']='ok';
			echo json_encode($data);exit;
		}
		/*if (empty($_SERVER["HTTP_AUTHORIZATION"])) {
            if (
                !is_object($data) ||
                !property_exists($data, "defaultToken") ||
                empty($data->defaultToken)
            ) {
                $response["response_code"] = "401";
                $response["response_message"] =
                    "UNAUTHORIZED: Please provide an access token to continue accessing the API";
                echo json_encode($response);
                exit();
            }
            if ($data->defaultToken !== $this->config->item("defaultToken")) {
                $response["response_code"] = "402";
                $response["response_message"] =
                    "UNAUTHORIZED: Please provide a valid access token to continue accessing the API";
                echo json_encode($response);
                exit();
            }
        } else {
            $headers = apache_request_headers();
            $token = str_replace("Bearer ", "", $headers["Authorization"]);
            $kunci = $this->config->item("jwt_key");
            $userData = JWT::decode($token, $kunci);
            Utility::validateSession($userData->iat, $userData->exp);
            $tokenSession = Utility::tokenSession($userData);
        }*/

			$college_id = $data->college_id;
            $round = $data->round;
            $category = $data->category;
			$result = $this->Cutoff_model->getKCETCutOff($college_id,$round,$category);
            $result2 = $this->getCommonalyAskedQ($college_id, $type = "CutOff");

			if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["response_data"] = $result;
                $response["Commonaly_Asked_Questions"] = $result2;

            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }

			echo json_encode($response);exit;
    }


    public function getKCETCutoffCat() {
		//echo "testing...";exit;
        $data = json_decode(file_get_contents('php://input'));
		if($this->input->server('REQUEST_METHOD')=='OPTIONS')
		{
			$data['status']='ok';
			echo json_encode($data);exit;
		}
		/*if (empty($_SERVER["HTTP_AUTHORIZATION"])) {
            if (
                !is_object($data) ||
                !property_exists($data, "defaultToken") ||
                empty($data->defaultToken)
            ) {
                $response["response_code"] = "401";
                $response["response_message"] =
                    "UNAUTHORIZED: Please provide an access token to continue accessing the API";
                echo json_encode($response);
                exit();
            }
            if ($data->defaultToken !== $this->config->item("defaultToken")) {
                $response["response_code"] = "402";
                $response["response_message"] =
                    "UNAUTHORIZED: Please provide a valid access token to continue accessing the API";
                echo json_encode($response);
                exit();
            }
        } else {
            $headers = apache_request_headers();
            $token = str_replace("Bearer ", "", $headers["Authorization"]);
            $kunci = $this->config->item("jwt_key");
            $userData = JWT::decode($token, $kunci);
            Utility::validateSession($userData->iat, $userData->exp);
            $tokenSession = Utility::tokenSession($userData);
        }*/
			$searchval = isset($data->searchval)?$data->searchval:'';
          
			$result = $this->Cutoff_model->getKCETCutoffCat($searchval);
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
    public function getCommonalyAskedQ($collegeId, $type)
    {
        $getType = $this->College_model->getFaqType($type);
        $type = $getType[0]->id;
        $result = $this->College_model->getCommonalyAskedQ($collegeId, $type);
        $FAQs = [];
        foreach ($result as $item) {
            $faq_ids = explode(",", $item["faq_id"]);
            $questions = explode(",", $item["question"]);

            for ($i = 0; $i < count($faq_ids); $i++) {
                $description = $this->College_model->getDescriptionForFAQ(
                    $faq_ids[$i]
                );
                if (!empty($description)) {
                    $FAQs[] = [
                        "faq_id" => $faq_ids[$i],
                        "question" => $questions[$i],
                        "answere" => $description[0]["answere"],
                    ];
                }
            }
        }

        return $FAQs;
    }
    
    public function getCounsellingFees(){
		$data = json_decode(file_get_contents('php://input'));
		if($this->input->server('REQUEST_METHOD')=='OPTIONS')
		{
			$data['status']='ok';
			echo json_encode($data);exit;
		}
		if (empty($_SERVER["HTTP_AUTHORIZATION"])) {
            if (
                !is_object($data) ||
                !property_exists($data, "defaultToken") ||
                empty($data->defaultToken)
            ) {
                $response["response_code"] = "401";
                $response["response_message"] =
                    "UNAUTHORIZED: Please provide an access token to continue accessing the API";
                echo json_encode($response);
                exit();
            }
            if ($data->defaultToken !== $this->config->item("defaultToken")) {
                $response["response_code"] = "402";
                $response["response_message"] =
                    "UNAUTHORIZED: Please provide a valid access token to continue accessing the API";
                echo json_encode($response);
                exit();
            }
        } else {
            $headers = apache_request_headers();
            $token = str_replace("Bearer ", "", $headers["Authorization"]);
            $kunci = $this->config->item("jwt_key");
            $userData = JWT::decode($token, $kunci);
            Utility::validateSession($userData->iat, $userData->exp);
            $tokenSession = Utility::tokenSession($userData);
        }
			$college_id = isset($data->college_id)?$data->college_id:'';
          
			$result = $this->College_model->getCollegeDetailsByID($college_id);
			$result1 = $this->College_model->getExamsForClg($college_id);
			if(!empty($result))
			{
			$typeid = $result[0]['college_typeid'];
			$category = $result[0]['categoryid'];
			$exam = $result1[0]['entrance_exams'];
			$getCounselling  = $this->Cutoff_model->getCounsellingFees($typeid,$category,$exam);
            }
			
			// print_r($getCounselling);exit;
			if ($result1) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["counsellingfee"] = $getCounselling;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }

			echo json_encode($response);exit;
	}
	
	///////////////////////////////////////////////////////////////////////////
	
	public function getCutOffRoundWise(){
	   // echo "tttt";exit;
		$data = json_decode(file_get_contents('php://input'));
		if($this->input->server('REQUEST_METHOD')=='OPTIONS')
		{
			$data['status']='ok';
			echo json_encode($data);exit;
		}
		/*if (empty($_SERVER["HTTP_AUTHORIZATION"])) {
            if (
                !is_object($data) ||
                !property_exists($data, "defaultToken") ||
                empty($data->defaultToken)
            ) {
                $response["response_code"] = "401";
                $response["response_message"] =
                    "UNAUTHORIZED: Please provide an access token to continue accessing the API";
                echo json_encode($response);
                exit();
            }
            if ($data->defaultToken !== $this->config->item("defaultToken")) {
                $response["response_code"] = "402";
                $response["response_message"] =
                    "UNAUTHORIZED: Please provide a valid access token to continue accessing the API";
                echo json_encode($response);
                exit();
            }
        } else {
            $headers = apache_request_headers();
            $token = str_replace("Bearer ", "", $headers["Authorization"]);
            $kunci = $this->config->item("jwt_key");
            $userData = JWT::decode($token, $kunci);
            Utility::validateSession($userData->iat, $userData->exp);
            $tokenSession = Utility::tokenSession($userData);
        }*/

            $college_id = $data->college_id;
			$result = $this->Cutoff_model->getCoutOffRoundWise($college_id);
// 			print_r($result);exit;
			$rounds = array();

// Iterate through the results
// foreach ($result as $row) {
//     // Get the round number for the current row
//     $round_number = $row->round;

//     // Check if an array for this round already exists, if not, create it
//     if (!isset($rounds[$round_number])) {
//         $rounds['round'. $round_number] = array();
//     }

//     // Add the current row to the array corresponding to its round
//     $rounds['round'. $round_number][] = $row;
// }
			if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["CoutOffRoundWise"] = $result;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }

			echo json_encode($response);exit;
	}
	 public function getCOMDEKCutOff()
    {
        $data = json_decode(file_get_contents('php://input'));
		if($this->input->server('REQUEST_METHOD')=='OPTIONS')
		{
			$data['status']='ok';
			echo json_encode($data);exit;
		}
		/*if (empty($_SERVER["HTTP_AUTHORIZATION"])) {
            if (
                !is_object($data) ||
                !property_exists($data, "defaultToken") ||
                empty($data->defaultToken)
            ) {
                $response["response_code"] = "401";
                $response["response_message"] =
                    "UNAUTHORIZED: Please provide an access token to continue accessing the API";
                echo json_encode($response);
                exit();
            }
            if ($data->defaultToken !== $this->config->item("defaultToken")) {
                $response["response_code"] = "402";
                $response["response_message"] =
                    "UNAUTHORIZED: Please provide a valid access token to continue accessing the API";
                echo json_encode($response);
                exit();
            }
        } else {
            $headers = apache_request_headers();
            $token = str_replace("Bearer ", "", $headers["Authorization"]);
            $kunci = $this->config->item("jwt_key");
            $userData = JWT::decode($token, $kunci);
            Utility::validateSession($userData->iat, $userData->exp);
            $tokenSession = Utility::tokenSession($userData);
        }*/

			$college_id = $data->college_id;
            $round = isset($data->round)?$data->round:'';
            $category = isset($data->category)?$data->category:'';
			$result = $this->Cutoff_model->getCOMDEKCutOff($college_id,$round,$category);
            $result2 = $this->getCommonalyAskedQ($college_id, $type = "CutOff");

			if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["response_data"] = $result;
                $response["Commonaly_Asked_Questions"] = $result2;

            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }

			echo json_encode($response);exit;
    }
    
    public function getCOMEDKCutOffRoundWise(){
		$data = json_decode(file_get_contents('php://input'));
		if($this->input->server('REQUEST_METHOD')=='OPTIONS')
		{
			$data['status']='ok';
			echo json_encode($data);exit;
		}
	/*	if (empty($_SERVER["HTTP_AUTHORIZATION"])) {
            if (
                !is_object($data) ||
                !property_exists($data, "defaultToken") ||
                empty($data->defaultToken)
            ) {
                $response["response_code"] = "401";
                $response["response_message"] =
                    "UNAUTHORIZED: Please provide an access token to continue accessing the API";
                echo json_encode($response);
                exit();
            }
            if ($data->defaultToken !== $this->config->item("defaultToken")) {
                $response["response_code"] = "402";
                $response["response_message"] =
                    "UNAUTHORIZED: Please provide a valid access token to continue accessing the API";
                echo json_encode($response);
                exit();
            }
        } else {
            $headers = apache_request_headers();
            $token = str_replace("Bearer ", "", $headers["Authorization"]);
            $kunci = $this->config->item("jwt_key");
            $userData = JWT::decode($token, $kunci);
            Utility::validateSession($userData->iat, $userData->exp);
            $tokenSession = Utility::tokenSession($userData);
        }*/

            $college_id = $data->college_id;
			$result = $this->Cutoff_model->getCOMEDKCutOffRoundWise($college_id);
// 			print_r($result);exit;
			$rounds = array();

			if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["CoutOffRoundWise"] = $result;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }

			echo json_encode($response);exit;
	}
}
