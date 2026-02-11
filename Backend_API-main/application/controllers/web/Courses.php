<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST");
defined("BASEPATH") or exit("No direct script access allowed");
class Courses extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $data = json_decode(file_get_contents('php://input'));
        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data['status'] = 'ok';
            echo json_encode($data);
            exit;
        }
        $this->load->model("web/Courses_model");
        $this->load->model("web/College_model", "", true);
        $this->load->library("Utility");
    }

    public function getCoursesList()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
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
        if ($data) {
            $search_term = $data->search_term;

            $courses = $this->Courses_model->getCoursesList($search_term);

            if ($courses) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["data"] = $courses;
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

    public function getCoursesByCatId()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
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
        if ($data) {
            $CatId = $data->CatId;

            $courses = $this->Courses_model->getCoursesByCatId($CatId);
            foreach ($courses as $key => $value) {
                $courses[$key]->imagepath =
                    base_url() . "/uploads/courses/" . $courses[$key]->image;
            }
            if ($courses) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["data"] = $courses;
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

    public function getCoursesByAcat_CCat()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
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
        if ($data) {
            $CouCat = $data->CouCat;
            $AcaCat = $data->AcaCat;

            $courses = $this->Courses_model->getCoursesByAcat_CCat(
                $CouCat,
                $AcaCat
            );
            if ($courses) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["data"] = $courses;
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

    public function getCourseCategory()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
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
        $courses = $this->Courses_model->getCourseCategory();
        if ($courses) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["data"] = $courses;
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed";
        }
        echo json_encode($response);
        exit;
    }

    public function getCourseCategoryById()
    {
        //  echo "ttt";exit;
        $data = json_decode(file_get_contents("php://input"));

        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $response["status"] = "ok";
            echo json_encode($response);
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

        $collegeId = isset($data->collegeId) ? $data->collegeId : null;

        if (!$collegeId) {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed: College ID is required.";
            echo json_encode($response);
            exit();
        }

        $courses = $this->Courses_model->getCourseCategory();
        $courseCategoryIds = array_column($courses, 'category_id');

        $courseData = $this->Courses_model->checkCourse($courseCategoryIds, $collegeId);

        if (!empty($courseData)) {
            $validCategoryIds = array_column($courseData, 'course_category');

            $filteredCourses = array_filter($courses, function ($course) use ($validCategoryIds) {
                return in_array($course->category_id, $validCategoryIds);
            });

            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["response_data"] = array_values($filteredCourses);
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "No data available for the selected categories.";
        }

        echo json_encode($response);
        exit();
    }

    public function getCourseByCategory()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
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
        if ($data) {
            $categoryId = $data->categoryId;
            $search = $data->search;
            $courses = $this->Courses_model->getCourseByCategory(
                $categoryId,
                $search
            );
            if ($courses) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["data"] = $courses;
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

    public function saveCourseInquiry()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
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
        // if (empty($_SERVER['HTTP_AUTHORIZATION'])) {
        //     if (!is_object($data) || !property_exists($data, 'defaultToken') || empty($data->defaultToken)) {
        //         $response["response_code"] = "401";
        //         $response["response_message"] = "UNAUTHORIZED: Please provide an access token to continue accessing the API";
        //         echo json_encode($response);
        //         exit();
        //     }
        //     if ($data->defaultToken !== $this->config->item('defaultToken')) {
        //         $response["response_code"] = "402";
        //         $response["response_message"] = "UNAUTHORIZED: Please provide a valid access token to continue accessing the API";
        //         echo json_encode($response);
        //         exit();
        //     }
        // }
        // else
        // {
        // $headers = apache_request_headers();
        // $token = str_replace("Bearer ", "", $headers['Authorization']);
        // $kunci = $this->config->item('jwt_key');
        // $userData = JWT::decode($token, $kunci);
        // Utility::validateSession($userData->iat,$userData->exp);
        // $tokenSession = Utility::tokenSession($userData);
        // }
        if ($data) {
            $firstName = $data->firstName;
            $lastName = $data->lastName;
            $name = $firstName . " " . $lastName;
            $email = $data->email;
            $phone = $data->phone;
            $courseCategory = $data->courseCategory;
            $course = $data->course;
            $intrestedIn = $data->intrestedIn;
            $city = $data->city;
            $state = $data->state;

            $Arr = [
                "name" => $name,
                "email" => $email,
                "phone" => $phone,
                "category" => $courseCategory,
                "coursename" => $course,
                "interested" => $intrestedIn,
                "city" => $city,
                "state" => $state,
            ];
            //print_r($Arr);exit;
            $result = $this->Courses_model->saveCourseInquiry($Arr);
            if ($result) {
                $logArr = ["crs_enquiry_id" => $result];
                //print_r($logArr);exit;
                $tableName = "courseenquiry_log";
                $addLog = $this->Courses_model->addLog($logArr, $tableName);
                $response["response_code"] = "200";
                $response["response_message"] =
                    "Your inquiry has been submitted successfully. We will get back to you soon!";
                $response["inquiryId"] = $result;
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

    public function coursesOfferedInSameGroup()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
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
        if ($data) {
            $collegeId = isset($data->collegeId) ? $data->collegeId : "";
            $result = $this->Courses_model->coursesOfferedInSameGroup(
                $collegeId
            );

            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["coursesOfferedInSameGroup"] = $result;
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

    public function getCoursesOfCollege()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
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
        if ($data) {
            $collegeId = isset($data->collegeId) ? $data->collegeId : "";
            $subcategory = isset($data->course) ? $data->course : "";
            $courselevel = isset($data->courselevel) ? $data->courselevel : "";
            $total_fees = isset($data->total_fees) ? $data->total_fees : "";
            // $subcategory = isset($subcategory[0]->id) ? $subcategory[0]->id : '';
            $subcategory = [];

            if (!empty($data->course) && is_array($data->course)) {
                foreach ($data->course as $course) {
                    if (!empty($course->id)) {
                        $subcategory[] = $course->id;
                    }
                }
            }

            $exam_accepted = isset($data->exam_accepted)
                ? $data->exam_accepted
                : "";
            $CourseName = isset($data->course_name) ? $data->course_name : "";
            $categoryId = isset($data->categoryId) ? $data->categoryId : "";
            $collegeTypeId = isset($data->collegeTypeId) ? $data->collegeTypeId : "";
            $result = $this->Courses_model->getCoursesOfCollege(
                $collegeId,
                $subcategory,
                $courselevel,
                $total_fees,
                $exam_accepted,
                $CourseName,
                $categoryId,
                $collegeTypeId
            );

            if ($result) {
                foreach ($result as &$course) {
                    if (!empty($course->examNames)) {
                        // Add space before and after comma
                        $course->examNames = preg_replace('/\s*,\s*/', ', ', $course->examNames);
                    }
                }
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["courses_list"] = $result;
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


    public function getCoursesOfCollegefilteredtotal_fees()
    {
        // echo "ttttt";exit;
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
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
        if ($data) {
            $collegeId = isset($data->collegeId) ? $data->collegeId : "";
            $subcategory = isset($data->course) ? $data->course : "";
            $courselevel = isset($data->courselevel) ? $data->courselevel : "";
            $total_fees = isset($data->feesArr) ? $data->feesArr : "";



            $exam_accepted = isset($data->exam_accepted)
                ? $data->exam_accepted
                : "";
            $CourseName = isset($data->course_name) ? $data->course_name : "";
            $categoryId = isset($data->categoryId) ? $data->categoryId : "";
            $collegeTypeId = isset($data->collegeTypeId) ? $data->collegeTypeId : "";
            $result = $this->Courses_model->getCoursesOfCollegefilteredtotal_fees(
                $collegeId,
                $subcategory,
                $courselevel,
                $total_fees,
                $exam_accepted,
                $CourseName,
                $categoryId,
                $collegeTypeId
            );
            // print_r($result);exit;
            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["courses_list"] = $result;
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

    public function getCoursesBySubcategory()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
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
        if ($data) {
            $collegeId = isset($data->collegeId) ? $data->collegeId : "";
            $subcategory = isset($data->subcategory) ? $data->subcategory : "";
            $categoryId = isset($data->categoryId) ? $data->categoryId : "";
            $collegeTypeId = isset($data->collegeTypeId) ? $data->collegeTypeId : "";

            $result = $this->Courses_model->getCoursesBySubcategory(
                $collegeId,
                $subcategory,
                $categoryId,
                $collegeTypeId
            );
            // print_r($result);exit;
            foreach ($result as &$course) {
                // print_r($course['examNames']);exit;
                if (!empty($course['examNames'])) {
                    // print_r($course['examNames']);exit;
                    // Add space before and after comma
                    $course['examNames'] = preg_replace('/\s*,\s*/', ', ', $course['examNames']);
                }
            }
            if ($result) {

                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["courses_list"] = $result;
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

    public function getOtherCollegesOfferingSameCourseInSameCity()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
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
        if ($data) {
            $cityId = isset($data->cityId) ? $data->cityId : "";
            $collegeId = isset($data->collegeId) ? $data->collegeId : "";
            $subcatid     = isset($data->subcatid) ? $data->subcatid     : "";

            $result = $this->Courses_model->getOtherCollegesOfferingSameCourseInSameCity(
                $cityId,
                $collegeId,
                $subcatid
            );
            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["courses_list"] = $result;
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

    public function getFeesDataOfCollege()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
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
        if ($data) {
            $collegeId = isset($data->collegeId) ? $data->collegeId : "";

            $result = $this->Courses_model->getFeesDataOfCollege($collegeId);
            // print_r($result);exit;
            // foreach($result as $key => $value) {
            //     $result[$key]['total_fees'] = explode(" - ", $value['total_fees']);
            // }
            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["fees_list"] = $result;
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

    public function getCourseByCategoryClg()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
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
        if ($data) {
            $categoryId = $data->categoryId;
            $collegeId = $data->collegeId;
            $courses = $this->Courses_model->getCourseByCategoryClg(
                $categoryId,
                $collegeId
            );
            if ($courses) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["data"] = $courses;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }
        echo json_encode($response);
    }

    public function getCourseList()
    {
        // echo "ttttt";exit;
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
        if ($data) {
            // $headers = apache_request_headers();

            // $token = str_replace("Bearer ", "", $headers['Authorization']);
            // $kunci = $this->config->item('jwt_key');
            // $userData = JWT::decode($token, $kunci);
            // Utility::validateSession($userData->iat, $userData->exp);
            // $tokenSession = Utility::tokenSession($userData);

            $columns = [
                0 => "id",
                1 => "name",
                2 => "type",
            ];

            $limit = $data->length;
            $start = ($data->draw - 1) * $limit;
            $orderColumn = $columns[$data->order[0]->column];
            $orderDir = $data->order[0]->dir;
            $totalData = $this->Courses_model->countAllCourse();
            $totalFiltered = $totalData;

            if (
                !empty($data->search->value) or !empty($data->search->category)
            ) {
                $search = $data->search->value;
                $cat = isset($data->search->cat) ? $data->search->cat : "";
                $totalFiltered = $this->Courses_model->countFilteredCourse(
                    $search,
                    $cat
                );
                $courseList = $this->Courses_model->getFilteredCourse(
                    $search,
                    $cat,
                    $start,
                    $limit,
                    $orderColumn,
                    $orderDir
                );
            } else {
                $cat = isset($data->search->cat) ? $data->search->cat : "";
                $courseList = $this->Courses_model->getAllCourse(
                    $start,
                    $limit,
                    $orderColumn,
                    $orderDir,
                    $cat
                );
                // print_r($courseList);exit;
            }

            $datas = [];
            foreach ($courseList as $crs) {
                $nestedData = [];
                $nestedData["id"] = $crs->id;
                $nestedData["name"] = $crs->name;
                $nestedData["category"] = $crs->category;
                $nestedData["type"] = $crs->type;
                $nestedData["duration"] = $crs->duration . "years";

                $nestedData["status"] = $crs->status;
                if ($crs->image == "NULL" || $crs->image == "") {
                    $nestedData["image"] = "";
                } else {
                    $nestedData["image"] =
                        base_url() . "uploads/courses/" . $crs->image;
                }
                $datas[] = $nestedData;
            }

            $json_data = [
                "draw" => intval($data->draw),
                "recordsTotal" => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data" => $datas,
            ];

            echo json_encode($json_data);
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
            echo json_encode($response);
            exit();
        }
    }

    public function getCoursesInfo()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
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
        if ($data) {
            $courseid = $data->courseid;
            $collegeId = $data->collegeId;
            $college_typeid = $data->college_typeid;
            $categoryid = $data->categoryid;
            $courses = $this->Courses_model->getCoursesInfo(
                $collegeId,
                $courseid,
                $college_typeid,
                $categoryid
            );
            //print_r($courses);exit;
            $coursefee = $this->Courses_model->getCoursesfeeStructure(
                $collegeId,
                $courseid
            );
            $increment_view = $this->Courses_model->increment_view($courseid);

            if ($courses || $coursefee) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["courseinfo"] = $courses;
                $response["coursefees"] = $coursefee;
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

    public function getCoursesFeeStructure()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
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
        if ($data) {
            $courseid = $data->courseid;
            $collegeId = $data->collegeId;
            $coursefee = $this->Courses_model->getCoursesFeeStructure(
                $collegeId,
                $courseid
            );

            if ($coursefee) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["coursefees"] = $coursefee;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }
        echo json_encode($response);
    }

    public function getCoursesAdmissionProcess()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
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
        if ($data) {
            $courseid = $data->courseid;
            $collegeId = $data->collegeId;
            $result = $this->Courses_model->getCoursesAdmissionProcess(
                $collegeId,
                $courseid
            );
            $EXAMs = [];

            foreach ($result as $key => $value) {

                // $result[$key]->eligibility = strip_tags($result[$key]->eligibility);
                if(!empty($result[$key]->entrance_exams)){
                $result[$key]->entrance_exams = explode(
                    ",",
                    $result[$key]->entrance_exams
                );
                $acceptingExams = explode(",", $result[$key]->Accepting_Exams);

                // Combine exams
                $combinedExams = [];
                foreach ($result[$key]->entrance_exams as $index => $examId) {
                    $combinedExams[] = [
                        "id" => $examId,
                        "value" => isset($acceptingExams[$index])
                            ? $acceptingExams[$index]
                            : "",
                    ];
                }
                $result[$key]->acceptingExams = $combinedExams;
                //  $arrayData = unserialize($result[$key]->eligibility);
            
                if (is_string($result[$key]->eligibility) && @unserialize($result[$key]->eligibility) !== false) {
                    $arrayData = unserialize($result[$key]->eligibility);
                } else {
                    $arrayData = []; // or handle error accordingly
                }
                //print_r($arrayData);exit;
                $result[$key]->eligibility = $arrayData;
                // Initialize an array to hold notifications for this result item
                $result[$key]->Examnotification_or_ImportantDates = [];

                // Fetch and process exam notifications
                for ($i = 0; $i < count($result[$key]->entrance_exams); $i++) {
                    $exams = $this->College_model->getExamsNotification(
                        $result[$key]->entrance_exams[$i]
                    );
                    if (!empty($exams)) {
                        foreach ($exams as $exam) {
                            // Append each exam notification to the result item's notifications array
                            $result[$key]->Examnotification_or_ImportantDates[] =
                                $exam["notification"];

                            // Add to the EXAMs array
                            $EXAMs[] = [
                                "Examnotification_or_ImportantDates" =>
                                $exam["notification"],
                            ];
                        }
                    }
                }
            }
            }
            $result2 = $this->getCommonalyAskedQ($collegeId, $type = "COURSES");

            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["coursefees"] = $result;
                $response["Commonaly_Asked_Questions"] = $result2;
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

    public function getEntranceExamsForCourse()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
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
        if ($data) {
            $courseid = $data->courseid;
            $collegeId = $data->collegeId;
            $EntranceExams = $this->Courses_model->getEntranceExamsForCourse(
                $collegeId,
                $courseid
            );

            if ($EntranceExams) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["EntranceExams"] = $EntranceExams;
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

    public function getCommonalyAskedQ($collegeId, $type)
    {
        $getType = $this->College_model->getFaqType($type);
        $type = $getType[0]->id;
        $result = $this->College_model->getCommonalyAskedQ($collegeId, $type);
        //print_r($result);exit;
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
}
