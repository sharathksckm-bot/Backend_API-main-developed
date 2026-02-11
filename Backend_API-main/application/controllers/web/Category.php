<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST");
/**
 * Category Controller
 *
 * @category   Controllers
 * @package    Web
 * @subpackage Category
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    26 JAN 2024
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
        $this->load->model("web/Category_model", "", true);
        $this->load->model("web/City_model", "", true);

        $this->load->library("Utility");
        // $this->checkAuthorization();
    }
    /*** Get list of Category */
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

    public function getCoursesForCategory()
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
        $categoryid = $data->categoryid;
        $categories = $this->Category_model->getCourses($categoryid);
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

    public function getExamForCategory()
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
        $categoryid = $data->categoryid;

        $exams = $this->Category_model->getExams($categoryid);

        if ($exams) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["response_data"] = $exams;
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed";
        }
        echo json_encode($response);
        exit();
    }
    public function getCategoryForMenuNav()
    {
        $data = json_decode(file_get_contents("php://input"));

        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data->status = "ok";
            echo json_encode($data);
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
      $categories = $this->Category_model->getCategoryForMenus();
$cities = $this->City_model->getCities();

foreach ($categories as &$category) {
    $category->type = "collapsable";
    
    // Fetch courses and exams for the category
    $courses = $this->Category_model->getCourse($category->id);
    foreach ($courses as $course) {
        $course->type = "basic";
        $course->link = "/allCollegeList/bycategory/course/" . $course->id;

    }
    $exams = $this->Category_model->getExams($category->id);
    foreach ($exams as $exam) {
        $exam->type = "basic";
        $exam->link = "examsdetails/".$exam->id;
    }

    // Create the cities list for "Top Ranked Colleges" with correct links
    $topRankedCollegesInCities = array_map(function($city) use ($category) {
        return [
            "title" => 'Top Ranked Colleges in ' . $city['city'],
            "type" => "basic",
            "link" => "/allCollegeList/menu/" . $category->id . "/" . $city['id'],
        ];
    }, $cities);

    // Create the children array for the category
    $category->children = [
        [
            "title" => "Top Ranked Colleges",
            "type" => "collapsable",
            "children" => $topRankedCollegesInCities,
        ],
        [
            "title" => "Popular Courses",
            "type" => "collapsable",
            "children" => $courses,
            
        ],
        [
            "title" => "Exams",
            "type" => "collapsable",
            "children" => $exams,
        ],
        [
            "title" => "Colleges by Location",
            "type" => "collapsable",
            "children" => array_map(function($city) use ($category) {
                return [
                    "title" => $category->title . ' Colleges in ' . $city['city'],
                    "type" => "basic",
                    "link" => "/allCollegeList/menu/" . $category->id . "/" . $city['id'],
                ];
            }, $cities),
        ],
    ];
}

// Additional code for adding Home and More categories, and handling the categories array
$home = [
    "id" => "",
    "title" => "Home",
    "menuorder" => "1",
    "type" => "basic",
    "link" => "/home",
];
$more = [
    "id" => "",
    "title" => "More",
    "menuorder" => "11",
    "type" => "collapsable",
    "link" => "/home",
    "children" => []
];

array_unshift($categories, $home);

if (count($categories) > 10) {
    // Get the categories after the first 9 (because index 0 is Home)
    $more["children"] = array_slice($categories, 10);

    // Get the first 9 elements (excluding Home) and add "More" as the last element
    $subArray = array_merge(array_slice($categories, 0, 10), [$more]);
} else {
    // If there are 10 or fewer categories, simply add "More" with no children
    $subArray = array_merge($categories, [$more]);
}

echo json_encode(["horizontal" => $subArray]);

    }

    public function getCategory()
    {
        $data = json_decode(file_get_contents("php://input"));

        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data->status = "ok";
            echo json_encode($data);
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
        $categories = $this->Category_model->getCategory();

        foreach ($categories as &$category) {
            $courseCount = $this->Category_model->getCourseCount($category->id);
            $category->Total_Courses = $courseCount;
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
        exit();
    }
    public function getAcadamicCategory()
    {
        $data = json_decode(file_get_contents("php://input"));

        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data->status = "ok";
            echo json_encode($data);
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
        $categories = $this->Category_model->getAcadamicCategory();
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

    public function getPlacementCategory()
    {
        $data = json_decode(file_get_contents("php://input"));

        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data->status = "ok";
            echo json_encode($data);
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
        $categories = $this->Category_model->getPlacementCategory();
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
}
