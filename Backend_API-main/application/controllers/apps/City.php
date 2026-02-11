<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST");
if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}
class City extends CI_Controller
{
    /**
     * Constructor
     *
     * Loads necessary libraries, helpers, and models for the app controller.
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
       // $this->load->model("web/Campus_app_model", "", true);
        $this->load->model("apps/Campus_app_model", "", true);
        $this->load->library('Utility');
    }
    /**
     *  To get list of all city by searching the city name
     */
    public function getCity()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $text = isset($data->search_term) ? $data->search_term : '';
            $city = $this->Campus_app_model->getCity($text);
            if ($city) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["response_data"] = $city;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
            echo json_encode($response);
            exit();
        }
        echo json_encode($response);
        exit();
    }
    /**
     *  To get list of city by Ranking the selected course
     */
    public function getCityByCourse()
    {
       // echo "ttt";exit;
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $course = $data->subcategory;
            $statename = $data->statename;
            $startlimit = $data->startlimit;
            $endlimit = $data->endlimit;

           // $city = $this->Campus_app_model->get_City($course);
             $city = $this->Campus_app_model->getByCity($course,$statename,$startlimit,$endlimit);
           
           
            foreach ($city as &$c) {
               
               if (!empty($c->image)) {
                $c->imageName = $c->image;
                $c->image = base_url("uploads/City/" . $c->image);
            } else {
                $c->image = base_url("uploads/City/"); 
            }
                $cityId = $c->id;
                //echo $cityId;exit;
                $Courseid = $this->Campus_app_model->getcourseParentId($course);
               // print_r($Courseid); exit;
                //$courseid = $Courseid['parent_category'];
                $subCatName = $this->Campus_app_model->getSubCatByCoursesId($course);
                
                //foreach ($Courseid as &$ci) {
                //    $CourseId = $ci['id'];
                //echo $CourseId;exit;
                //    $collegeCount = $this->Campus_app_model->getCollegeCount($cityId, $CourseId);
                //}
                //print_r($subCatName); exit;
                //echo $subCatName;exit;
                //$collegeCount = $this->Campus_app_model->getCollegeCount($cityId, $Courseid);
                //$c->collegeCount = $collegeCount;
            }
           // print_r($city[0]->city);exit;
            if ($city) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["response_data"] = $city;
                $response["subCatName"] = $subCatName;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
            echo json_encode($response);
            exit();
        }
        echo json_encode($response);
        exit();
    }
	
	   public function getCityList()
    {
		   //print_r("testing...");exit;
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
		   
      /*  if (empty($_SERVER["HTTP_AUTHORIZATION"])) {
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
		
		
		
        $search_term = isset($data->search_term) ? $data->search_term : "";
        $cities = $this->City_model->getCity($search_term);
        if (!empty($cities)) {
            foreach ($cities as &$city) {
                $totalColleges = $this->College_model->countFilteredClg(
                    "",
                    $city["id"],
                    "",
                    "",
                    "",
                    ""
                );
                $city["Total_Colleges"] = $totalColleges;
            }
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["data"] = $cities;
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed";
        }
        echo json_encode($response);
    }
    
}
