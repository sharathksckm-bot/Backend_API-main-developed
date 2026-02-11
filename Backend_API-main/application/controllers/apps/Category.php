<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST");
if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}
class Category extends CI_Controller
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
        $this->load->model("apps/Campus_app_model", "", true);
		$this->load->model("apps/Common_model", "", true);
		$this->load->model("apps/College_model", "", true);
        $this->load->library('Utility');
    }
    public function getCategory()
    {
        $data = json_decode(file_get_contents('php://input'));

        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data->status = 'ok';
            echo json_encode($data);
            exit;
        }

        $categories = $this->Campus_app_model->getCategory();

        foreach ($categories as &$category) {
            $courseCount = $this->Campus_app_model->getCourseCount($category->id);
            $category->Total_Courses = $courseCount;
            
                if ($category->catname == "Arts (Liberal / Fine / Visual / Performing)") {
            $category->catname = "Arts (Liberal,Fine,Visual,Performing)";
        }
        }
        if ($categories) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["response_data"] = $categories;
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed";
        }

        echo json_encode($response);
        exit;
    }
    public function getAcadamicCategory()
    {
        $data = json_decode(file_get_contents('php://input'));

        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data->status = 'ok';
            echo json_encode($data);
            exit;
        }
        $categories = $this->Campus_app_model->getAcadamicCategory();
        if ($categories) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["response_data"] = $categories;
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed";
        }

        echo json_encode($response);
        exit;
    }


    public function getPlacementCategory()
    {
        $data = json_decode(file_get_contents('php://input'));

        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data->status = 'ok';
            echo json_encode($data);
            exit;
        }

        $collegeId = isset($data->collegeId) ? $data->collegeId : null;

        if (!$collegeId) {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed: College ID is required.";
            echo json_encode($response);
            exit();
        }

        $categories = $this->Campus_app_model->getPlacementCategory();
//print_r($categories);exit;
        $searchCategoryId = array_column($categories, 'id'); 
       
        $courseData = $this->College_model->checkdata($searchCategoryId, $collegeId);
         
        if (!empty($courseData)) {

            $validCategoryIds = array_column($courseData, 'course_category');
            
            
            $filteredCourses = array_filter($categories, function ($course) use ($validCategoryIds) {
                //print_r($course);exit;
                return in_array($course->id, $validCategoryIds);
            });

            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["response_data"] = array_values($filteredCourses); 
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed";
        }

        echo json_encode($response);
        exit;
    }

    public function getSubCategoryList()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $collegeId = $data->collegeId;
            $SubCategory = $this->Common_model->getSubCategoryList($collegeId);

            if ($SubCategory) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["SubCategory"] = $SubCategory;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }
        echo json_encode($response);
        exit;
    }
	
	
	public function getCategoryForMenu()
    {
        $data = json_decode(file_get_contents("php://input"));

        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
           // $data->status = "ok";
            echo json_encode("ok");
            exit();
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
        $categories = $this->Category_model->getCategoryForMenu();
        
        // foreach ($categories as &$category) {
        //     $courseCount = $this->Category_model->getCourseCount($category->id);
        //     $category->Total_Courses = $courseCount;
        //     $courses = $this->Category_model->getCourses($category->id);
        //     $category->Courses = $courses;
        //     $exams = $this->Category_model->getExams($category->id);
        //     $category->Exams = $exams;
        // }
         
        if ($categories) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["response_data"] = $categories;
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed";
        }
        echo json_encode($response);
        exit();
    }
	//===============
	
	public function getRecommendedList()
    {
		//echo "testing...";exit;
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $categoryId = $data->categoryId;
			
			//print_r($categoryId);exit;
			
            $getCollegesData = $this->Common_model->get_College($categoryId);
			
			$getCourses = $this->Common_model->countCoursesBycategoryId();
			
			//print_r($getCourses);exit;

            if ($getCollegesData) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["resgetCollegesData"] = $getCollegesData;
				$response["resgetCourses"] = $getCourses;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }
        echo json_encode($response);
        exit;
    }
    
     public function getYearByCategory()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $collegeId = $data->collegeId;
            $categoryId = $data->categoryId;
            $years = $this->College_model->getYearByCategory($collegeId,$categoryId);

           // print_r($years);exit;

            if ($years) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["year"] = $years;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }
        echo json_encode($response);
        exit;
    }

}
