<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST");
if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}
class Course extends CI_Controller
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
        $this->load->model("apps/Courses_model", "", true);
        $this->load->model("apps/College_model", "", true);

        $this->load->library('Utility');
    }

    /**
     *  To get list of Course
     */
    public function getCourse()
    {
        $data = json_decode(file_get_contents('php://input'));

        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data["status"] = "ok";
            echo json_encode($data);
            exit;
        }

        $result = $this->Campus_app_model->get_Course();

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

    /**
     * To get Course list by academic category and course catagory
     */
    public function getCoursesByAcat_CCat()
    {
        $data = json_decode(file_get_contents('php://input'));
        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data['status'] = 'ok';
            echo json_encode($data);
            exit;
        }
        if ($data) {
            $CouCat = $data->CouCat;
            $AcaCat = $data->AcaCat;

            $courses = $this->Campus_app_model->getCoursesByAcat_CCat($CouCat, $AcaCat);
            //print_r($courses);exit;

            if ($courses) {
                $response['response_code'] = '200';
                $response['response_message'] = 'Success';
                $response['data'] = $courses;
            } else {
                $response['response_code'] = '400';
                $response['response_message'] = 'Failed';
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }

        echo json_encode($response);
    }

    public function getCourseLevel()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $collegeId = $data->collegeId;
            $SubCategory = $this->Courses_model->getAcademicCategory($collegeId);

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


    /* public function getListOfSpecificCourses()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $ccID = $data->courseCatId;
            $acID = $data->academicCatId;

            $result = $this->Campus_app_model->getListBySpecification($ccID, $acID);

            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["collegelist"] = $result;
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
    }*/




    public function getListOfSpecificCourses()
    {
        //  echo "testing...";exit;
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }

        if ($data) {
            $ccID = $data->courseCatId;

            $acID = $data->academicCatId;
            $subID = $data->subcategory;

            $result = $this->Campus_app_model->getListBySpecification($ccID, $acID, $subID);
            //print_r($result[0]['id']);exit;
            foreach ($result as &$r) {
                //print_r($r['id']);exit;
                $getCollegecount = $this->Campus_app_model->getCollege_Count($r['id']);
                //print_r($getCollegecount);exit;
                $r["CollegeCount"] = $getCollegecount;
            }
            //$result['CollegeCount']=$getCollegecount;
            //print_r($getCoursecount);exit;

            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["collegelist"] = $result;
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




    public function getCourseCategory()
    {
        //echo "testing..";exit;
        $data = json_decode(file_get_contents('php://input'));
        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data['status'] = 'ok';
            echo json_encode($data);
            exit;
        }
        $courses = $this->Courses_model->getCourseCategory();
        if ($courses) {
            $response['response_code'] = '200';
            $response['response_message'] = 'Success';
            $response['data'] = $courses;
        } else {
            $response['response_code'] = '400';
            $response['response_message'] = 'Failed';
        }
        echo json_encode($response);
    }

    public function coursesOfferedInSameGroup()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $collegeId = isset($data->collegeId) ? $data->collegeId : '';
            $result = $this->Courses_model->coursesOfferedInSameGroup($collegeId);


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
        //echo "tttt";exit;
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $collegeId = isset($data->collegeId) ? $data->collegeId : '';
            $subcategory = isset($data->subcategory) ? $data->subcategory : '';
            $courselevel = isset($data->courselevel) ? $data->courselevel : '';
            $total_fees = isset($data->total_fees) ? $data->total_fees : '';
            $exam_accepted = isset($data->exam_accepted) ? $data->exam_accepted : '';
            $CourseName = isset($data->course_name) ? $data->course_name : '';
            $result = $this->Courses_model->getCoursesOfCollege($collegeId, $subcategory, $courselevel, $total_fees, $exam_accepted, $CourseName);
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
        if ($data) {
            $collegeId = isset($data->collegeId) ? $data->collegeId : '';
            $subcategory = isset($data->subcategory) ? $data->subcategory : '';
            $categoryId = isset($data->categoryId) ? $data->categoryId : "";
            $collegeTypeId = isset($data->collegeTypeId) ? $data->collegeTypeId : "";

            $result = $this->Courses_model->getCoursesBySubcategory($collegeId, $subcategory, $categoryId, $collegeTypeId);
            foreach ($result as &$course) {
                // Support both `result()` (objects) and `result_array()` (arrays)
                $examNames = is_array($course) ? ($course['examNames'] ?? null) : ($course->examNames ?? null);
                if (!empty($examNames)) {
                    // Add space before and after comma
                    $examNames = preg_replace('/\s*,\s*/', ', ', $examNames);
                    if (is_array($course)) {
                        $course['examNames'] = $examNames;
                    } else {
                        $course->examNames = $examNames;
                    }
                }
            }
            //print_r($result);exit;
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

    /*public function getOtherCollegesOfferingSameCourseInSameCity()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $cityId = isset($data->cityId) ? $data->cityId : '';
            $collegeId = isset($data->collegeId) ? $data->collegeId : '';

            $result = $this->Courses_model->getOtherCollegesOfferingSameCourseInSameCity($cityId, $collegeId);
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
    }*/

    public function getOtherCollegesOfferingSameCourseInSameCity()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
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
        if ($data) {
            $cityId = isset($data->cityId) ? $data->cityId : "";
            $subcat = isset($data->subcat) ? $data->subcat : "";
            $collegeId = isset($data->collegeId) ? $data->collegeId : "";
            $getSubCategoryForClg = $this->Courses_model->getSubCategoryForClg($collegeId);
            $categoryIds = array_map(function ($item) {
                return $item->categoryid;
            }, $getSubCategoryForClg);

            // Convert the array to a comma-separated string
            $categoryIdsString = implode(',', $categoryIds);

            //print_r($categoryIdsString); // Outputs: 1,2,14
            //exit;
            $result = $this->Courses_model->getOtherCollegesOfferingSameCourseInSameCity(
                $cityId,
                $collegeId,
                $subcat
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

    public function getCoursesList()
    {
        $data = json_decode(file_get_contents('php://input'));
        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data['status'] = 'ok';
            echo json_encode($data);
            exit;
        }
        if ($data) {
            $search_term = $data->search_term;

            $courses = $this->Courses_model->getCoursesList($search_term);

            if ($courses) {
                $response['response_code'] = '200';
                $response['response_message'] = 'Success';
                $response['data'] = $courses;
            } else {
                $response['response_code'] = '400';
                $response['response_message'] = 'Failed';
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }

        echo json_encode($response);
    }

    public function getCourseByCategory()
    {
        $data = json_decode(file_get_contents('php://input'));
        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data['status'] = 'ok';
            echo json_encode($data);
            exit;
        }
        if ($data) {
            $categoryId = $data->categoryId;
            $search = $data->search;
            $courses = $this->Courses_model->getCourseByCategory($categoryId, $search);
            if ($courses) {
                $response['response_code'] = '200';
                $response['response_message'] = 'Success';
                $response['data'] = $courses;
            } else {
                $response['response_code'] = '400';
                $response['response_message'] = 'Failed';
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }
        echo json_encode($response);
    }

    public function saveCourseInquiry()
    {
        $data = json_decode(file_get_contents('php://input'));
        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data['status'] = 'ok';
            echo json_encode($data);
            exit;
        }

        // if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
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
            $name = $firstName . ' ' . $lastName;
            $email = $data->email;
            $phone = $data->phone;
            $courseCategory = $data->courseCategory;
            $course = $data->course;
            $intrestedIn = $data->intrestedIn;

            $Arr = ['name' => $name, 'email' => $email, 'phone' => $phone, 'category' => $courseCategory, 'coursename' => $course, 'interested' => $intrestedIn];
            //print_r($Arr);exit;
            $result = $this->Courses_model->saveCourseInquiry($Arr);
            if ($result) {
                $logArr = ['crs_enquiry_id' => $result];
                //print_r($logArr);exit;
                $tableName = 'courseenquiry_log';
                $addLog = $this->Courses_model->addLog($logArr, $tableName);
                $response['response_code'] = '200';
                $response['response_message'] = 'Your inquiry has been submitted successfully. We will get back to you soon!';
                $response['inquiryId'] = $result;
            } else {
                $response['response_code'] = '400';
                $response['response_message'] = 'Failed';
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }
        echo json_encode($response);
    }

    public function getFeesDataOfCollege()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $collegeId = isset($data->collegeId) ? $data->collegeId : '';

            $result = $this->Courses_model->getFeesDataOfCollege($collegeId);
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
        $data = json_decode(file_get_contents('php://input'));
        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data['status'] = 'ok';
            echo json_encode($data);
            exit;
        }
        //print_r($data);exit;
        if ($data) {
            $categoryId = $data->categoryId;
            //  $collegeId = $data->collegeId;
            $collegeId = isset($data->collegeId) ? $data->collegeId : '';
            $courses = $this->Courses_model->getCourseByCategoryClg($categoryId, $collegeId);
            //	print_r($courses);exit;
            if ($courses) {
                $response['response_code'] = '200';
                $response['response_message'] = 'Success';
                $response['data'] = $courses;
            } else {
                $response['response_code'] = '400';
                $response['response_message'] = 'Failed';
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }
        echo json_encode($response);
    }

    public function getCourseList()
    {
        //  echo "tttt";exit;
        $data = json_decode(file_get_contents('php://input'));

        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data->status = 'ok';
            echo json_encode($data);
            exit;
        }

        if ($data) {

            //$search = $data->value;

            // $headers = apache_request_headers();

            // $token = str_replace("Bearer ", "", $headers['Authorization']);
            // $kunci = $this->config->item('jwt_key');
            // $userData = JWT::decode($token, $kunci);
            // Utility::validateSession($userData->iat, $userData->exp);
            // $tokenSession = Utility::tokenSession($userData);

            $columns = array(
                0 => 'id',
                1 => 'name',
                2 => 'type',
            );

            //  $limit = $data->length;
            // $start = ($data->draw - 1) * $limit;
            //  $orderColumn = $columns[$data->order[0]->column];
            //  $orderDir = $data->order[0]->dir;
            $totalData = $this->Courses_model->countAllCourse();


            $totalFiltered = $totalData;

            if (!empty($data->value) or !empty($data->category)) {
                //echo"5";exit;
                $search = $data->value;
                $cat = isset($data->subcategory) ? $data->subcategory : '';
                $totalFiltered = $this->Courses_model->countFilteredCourse($search);

                $courseList = $this->Courses_model->getFilteredCourse($search, $cat);
                //print_r($courseList);exit;
            } else {
                $courseList = $this->Courses_model->getAllCourse($start, $limit, $orderColumn, $orderDir);
            }

            $datas = array();
            foreach ($courseList as $crs) {

                $nestedData = array();
                $nestedData['id'] = $crs->id;
                $nestedData['name'] = $crs->name;
                $nestedData['category'] = $crs->category;
                $nestedData['subcategory'] = $crs->subcategory;
                $nestedData['type'] = $crs->type;
                $nestedData['duration'] = $crs->duration . 'years';

                $nestedData['status'] = $crs->status;
                if ($crs->image == 'NULL' || $crs->image == '') {
                    $nestedData['image'] = "";
                } else {
                    $nestedData['image'] = base_url() . 'uploads/courses/' . $crs->image;
                }
                $datas[] = $nestedData;
            }

            $json_data = array(
                // 'draw' => intval($data->draw),
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
    }

    // public function getCoursesInfo()
    // {
    //     $data = json_decode(file_get_contents('php://input'));
    //     if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
    //         $data['status'] = 'ok';
    //         echo json_encode($data);
    //         exit;
    //     }
    //     if ($data) {
    //         $courseid = $data->courseid;
    //         $collegeId = $data->collegeId;
    //         $courses = $this->Courses_model->getCoursesInfo($collegeId, $courseid);
    //       // print_r($courses[0]->eligibility);exit;

    //       $abc =  unserialize($courses[0]->eligibility);
    //       print_r($abc);exit;
    //         $coursefee = $this->Courses_model->getCoursesfeeStructure($collegeId, $courseid);

    //         if ($courses || $coursefee) {
    //             $response['response_code'] = '200';
    //             $response['response_message'] = 'Success';
    //             $response['courseinfo'] = $courses;
    //             $response['coursefees'] = $coursefee;
    //         } else {
    //             $response['response_code'] = '400';
    //             $response['response_message'] = 'Failed';
    //         }
    //     } else {
    //         $response["response_code"] = "500";
    //         $response["response_message"] = "Data is null";
    //     }
    //     echo json_encode($response);
    // }

    function safe_unserialize($value)
    {
        if ($value === null)
            return [];
        $value = trim($value);

        if ($value === '' || strtolower($value) === 'null')
            return [];

        // If it looks like JSON, decode JSON instead
        if ($value[0] === '{' || $value[0] === '[') {
            $json = json_decode($value, true);
            return (json_last_error() === JSON_ERROR_NONE) ? $json : [];
        }

        // Try unserialize safely
        $data = @unserialize($value);
        if ($data !== false || $value === 'b:0;') {
            return $data;
        }

        // As a last fallback, handle CSV "1,2,3"
        if (strpos($value, ',') !== false) {
            return array_map('trim', explode(',', $value));
        }

        return [];
    }

    public function getCoursesInfo()
    {
        $data = json_decode(file_get_contents('php://input'));

        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $response = ['status' => 'ok'];
            echo json_encode($response);
            exit;
        }

        if ($data) {
            $courseid = $data->courseid;
            $collegeId = $data->collegeId;
            $ClgList = $this->College_model->getCollegeDetailsByID($collegeId);
            $getCoursesById = $this->Courses_model->getCoursesById($courseid);

            // print_r($getCoursesById[0]->sub_category);exit;
            $courses = $this->Courses_model->getCoursesInfo($collegeId, $courseid, $ClgList[0]['college_typeid'], $getCoursesById[0]->sub_category);

            // print_r($courses);exit;
            if ($courses && isset($courses[0]->eligibility)) {
                $eligibility = $this->safe_unserialize($courses[0]->eligibility);
                $eligibility = [
                    'Qualification' => isset($eligibility['qualification']) ? implode(', ', $eligibility['qualification']) : null,
                    'Cut_off' => isset($eligibility['cut_off']) ? $eligibility['cut_off'] : null,
                    'Other_eligibility' => isset($eligibility['other_eligibility']) ? $eligibility['other_eligibility'] : null
                ];

                $courses[0]->eligibility = $eligibility;
            } else {
                if ($courses) {
                    $courses[0]->eligibility = 'No eligibility data available';
                }
            }

            $coursefee = $this->Courses_model->getCoursesfeeStructure($collegeId, $courseid);

            if ($courses || $coursefee) {
                $response['response_code'] = '200';
                $response['response_message'] = 'Success';
                $response['courseinfo'] = $courses;
                $response['coursefees'] = $coursefee;
            } else {
                $response['response_code'] = '400';
                $response['response_message'] = 'Failed';
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }


        echo json_encode($response);
    }


    public function getCoursesFeeStructure()
    {
        $data = json_decode(file_get_contents('php://input'));
        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data['status'] = 'ok';
            echo json_encode($data);
            exit;
        }
        if ($data) {
            $courseid = $data->courseid;
            $collegeId = $data->collegeId;
            $coursefee = $this->Courses_model->getCoursesFeeStructure($collegeId, $courseid);

            if ($coursefee) {
                $response['response_code'] = '200';
                $response['response_message'] = 'Success';
                $response['coursefees'] = $coursefee;
            } else {
                $response['response_code'] = '400';
                $response['response_message'] = 'Failed';
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }
        echo json_encode($response);
    }

    public function getCoursesAdmissionProcess()
    {
        $data = json_decode(file_get_contents('php://input'));
        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data['status'] = 'ok';
            echo json_encode($data);
            exit;
        }
        if ($data) {
            $courseid = isset($data->courseid) ? $data->courseid : '';
            $collegeId = $data->collegeId;
            $result = $this->Courses_model->getCoursesAdmissionProcess($collegeId, $courseid);
            $EXAMs = array();
            foreach ($result as $key => $value) {
                $result[$key]->eligibility = json_decode($result[$key]->eligibility);
                $result[$key]->entrance_exams = explode(',', $result[$key]->entrance_exams);
                $acceptingExams = explode(',', $result[$key]->Accepting_Exams);
                $combinedExams = array();
                foreach ($result[$key]->entrance_exams as $index => $examId) {
                    $combinedExams[] = array(
                        'id' => $examId,
                        'value' => isset($acceptingExams[$index]) ? $acceptingExams[$index] : ''
                    );
                }
                $result[$key]->acceptingExams = $combinedExams;
                for ($i = 0; $i < count($result[$key]->entrance_exams); $i++) {
                    $exams = $this->College_model->getExamsNotification($result[$key]->entrance_exams[$i]);
                    if (!empty($exams)) {
                        $EXAMs[] = array(
                            'Examnotification_or_ImportantDates' => $exams[0]['notification']
                        );
                        $result[$key]->Examnotification_or_ImportantDates = $exams[0]['notification'];
                    }
                }
            }
            $result2 = $this->getCommonalyAskedQ($collegeId, $type = 'COURSES');

            if ($result) {
                $response['response_code'] = '200';
                $response['response_message'] = 'Success';
                $response['coursefees'] = $result;
                $response["Commonaly_Asked_Questions"] = $result2;
            } else {
                $response['response_code'] = '400';
                $response['response_message'] = 'Failed';
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }
        echo json_encode($response);
    }

    public function getEntranceExamsForCourse()
    {
        $data = json_decode(file_get_contents('php://input'));
        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data['status'] = 'ok';
            echo json_encode($data);
            exit;
        }
        if ($data) {
            $courseid = isset($data->courseid) ? $data->courseid : '';
            // print_r($courseid);exit;
            $collegeId = $data->collegeId;
            $EntranceExams = $this->Courses_model->getEntranceExamsForCourse($collegeId, $courseid);
            //  print_r($EntranceExams);exit;

            if ($EntranceExams) {
                $response['response_code'] = '200';
                $response['response_message'] = 'Success';
                $response['EntranceExams'] = $EntranceExams;
            } else {
                $response['response_code'] = '400';
                $response['response_message'] = 'Failed';
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }
        echo json_encode($response);
    }

    public function getCommonalyAskedQ($collegeId, $type)
    {

        $getType = $this->College_model->getFaqType($type);
        $type = $getType[0]->id;
        $result = $this->College_model->getCommonalyAskedQ($collegeId, $type);
        //print_r($result);exit;
        $FAQs = array();
        foreach ($result as $item) {
            $faq_ids = explode(',', $item['faq_id']);
            $questions = explode(',', $item['question']);

            for ($i = 0; $i < count($faq_ids); $i++) {
                $description = $this->College_model->getDescriptionForFAQ($faq_ids[$i]);
                if (!empty($description)) {
                    $FAQs[] = array(
                        'faq_id' => $faq_ids[$i],
                        'question' => $questions[$i],
                        'answere' => $description[0]['answere']
                    );
                }
            }
        }

        return $FAQs;
    }


    public function getTrendingCoursesList()
    {
        $data = json_decode(file_get_contents('php://input'));
        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data['status'] = 'ok';
            echo json_encode($data);
            exit;
        }
        if ($data) {
            $categoryId = $data->categoryId;
            $ac_id = isset($data->academic_categories) ? $data->academic_categories : '';
            // $collegeId = $data->collegeId;
            $courses = $this->Courses_model->getTrendingCoursesList($categoryId, $ac_id);
            //print_r($courses);exit;
            foreach ($courses as $crs) {
                $courseData = [
                    "id" => $crs->id,
                    "name" => $crs->name,
                    "image" => $crs->image ? base_url() . 'uploads/courses/' . $crs->image : '',
                    "type" => $crs->type,
                    "category" => $crs->category
                ];
                $response['response_code'] = '200';
                $response['response_message'] = 'Success';
                $response['data'][] = $courseData;
            }
            if ($courses) {
            } else {
                $response['response_code'] = '400';
                $response['response_message'] = 'Failed';
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }
        echo json_encode($response);
    }


    public function getTrendingCoursesDetailsById()
    {
        $data = json_decode(file_get_contents('php://input'));
        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data['status'] = 'ok';
            echo json_encode($data);
            exit;
        }
        if ($data) {
            $courseId = $data->courseId;
            // $collegeId = $data->collegeId;
            $courses = $this->Courses_model->getTrendingCoursesDetailsById($courseId);
            //print_r($courses);exit;
            $courseData = [];
            foreach ($courses as $crs) {
                $courseArray = (array) $crs;
                if ($crs->image == 'NULL' || $crs->image == '') {
                    $courseArray['image'] = "";
                } else {
                    $courseArray['image'] = base_url() . 'uploads/courses/' . $crs->image;
                }
                $courseData = $courseArray;
            }
            if ($courseData) {
                $response['response_code'] = '200';
                $response['response_message'] = 'Success';
                $response['data'][] = $courseData;
            } else {
                $response['response_code'] = '400';
                $response['response_message'] = 'Failed';
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }
        echo json_encode($response);
    }
}
