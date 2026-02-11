<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST");
if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}
class College extends CI_Controller
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
        $this->load->model("apps/Review_model", "", true);
        $this->load->model("apps/College_model", "", true);
        //$this->load->model("apps/Exam_model", "", true);
        $this->load->library('Utility');

        $this->load->library('mpdf_lib');
        $config = [
            'tempDir' => APPPATH . 'tmp' // Or use FCPATH.'tmp' if you want outside of application folder
        ];
        $this->mpdf = new \Mpdf\Mpdf($config);
        //$this->load->library('m_pdf');
        //$this->load->library('mpdf_lib');

    }
    /**
     * To get College list and course catagory ID
     */
    public function getCollegeListByCoursetest()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $course = $data->course;
            $Courseid = $this->Campus_app_model->getcoursesId($course);
            //	print_r($Courseid);exit;
            $subCatName = $this->Campus_app_model->getSubCatByCoursesId($course);
            //print_r($Courseid);exit;
            $result = array();
            $course = array();
            $college = array();
            foreach ($Courseid as &$ci) {
                $CourseId = $ci['id'];
                $course[] = $CourseId;
                //echo ' --'.$CourseId.'-- ';
                $collegeList = $this->Campus_app_model->getCollegeListByCourse($CourseId);
                $result = array_merge($result, $collegeList);
            }
            //print_r($course);
            foreach ($result as &$clg) {
                $clgID = $clg['collegeid'];
                $college[] = $clgID;
                $clg['logo'] = base_url() . "/uploads/college/" . $clg['logo'];
                $clg['file'] = base_url() . "/uploads/brochures/" . $clg['file'];

                // Initialize course count for the current college
                $clg['courseCount'] = 0;

                foreach ($course as $courseId) {
                    $courseCount = $this->Campus_app_model->countCoursesByCourseId($courseId, $clgID);

                    // Increment course count for the current college
                    $clg['courseCount'] += $courseCount;
                }
            }
            //print_r($result);
            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["collegelist"] = $result;
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
        exit;
    }

    public function getCollegeListByCourse()
    {
        // echo "ttt";exit;
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $course = isset($data->courseId) ? $data->courseId : $data->course->courseId;
            $statename = isset($data->statename) ? $data->statename : '';
            $startLimit = isset($data->startLimit) ? $data->startLimit : 0;
            $endLimit = isset($data->endLimit) ? $data->endLimit : 5;
            $subCatName = $this->Campus_app_model->getSubCatByCoursesId($course);

            $collegeList = $this->Campus_app_model->getCollegeListByCourse123($course, $statename, $startLimit, $endLimit);
            foreach ($collegeList as  &$clg) {
                $clgID = $clg['collegeid'];
                $courseCount = $this->Campus_app_model->countCoursesByCourseId($course, $clgID);
                $clg['CourseCount'] = $courseCount;
                $clg['logo'] = base_url() . "/uploads/college/" . $clg['logo'];
            }
            //print_r($clg);exit;
            //   $total_fees = $this->Campus_app_model->getTotalFeesForCollege($clg['college_typeid'], $clg['categoryid'], $course,$clg['entrance_exams']);
            //   print_r($total_fees);exit;
            //   $collegeList[$key]['total_fees'] = $total_fees[$key]->total_fees;

            //   if (empty($collegeList[$key]['total_fees']) && empty($total_fees)) {
            //      $collegeList[$key]['total_fees'] = 'N/A';
            //    }

            //  $clgID = $clg['collegeid'];

            //  $collegeList[$key]['file'] = base_url() . "/uploads/brochures/" . $clg['file'];
            //  $TotalRate = $this->Review_model->getCollegeTotalRate($clgID);
            //   $RateCount = $TotalRate['totalRateCount'];
            //  $collegeList[$key]['rating'] = $RateCount;
            //   $clgID = $clg['collegeid'];
            //   $courseCount = $this->Campus_app_model->countCoursesByCourseId($course, $clgID);
            //  $collegeList[$key]['CourseCount'] = $courseCount;
            // }
            //print_r($collegeList);exit;*/

            $result = json_encode($collegeList);



            foreach ($collegeList as $colgs) {

                // print_r($colgs['logo']);exit;
                $colgs['logo'] = base_url() . "/uploads/college/" . $clg['logo'];
            }

            if ($collegeList) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["collegelist"] = $collegeList;
                // $response["subCatName"] = $subCatName;
                //$response["CatId"] = $Courseid[0]['parent_category'];
                //$response["subCatId"] = $Courseid[0]['name'];
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
        exit;
    }

    public function getCollegeListByCourse_cop()
    {
        $data = json_decode(file_get_contents("php://input"));

        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }

        if ($data) {
            $course = isset($data->courseId) ? $data->courseId : $data->subcategory->courseId;
            $startLimit = isset($data->startLimit) ? $data->startLimit : 0;
            $endLimit = isset($data->endLimit) ? $data->endLimit : 5;

            // Fetch college list
            $collegeList = $this->Campus_app_model->getCollegeListByCourse123($course, $startLimit, $endLimit);

            if (!empty($collegeList)) {
                // Loop to batch fetch total fees and ratings individually
                $feesLookup = [];
                $ratingsLookup = [];

                foreach ($collegeList as $clg) {
                    $clgID = $clg['collegeid'];
                    $categoryID = $clg['categoryid'];
                    $collegeTypeID = $clg['college_typeid'];

                    // Fetch total fees for each college
                    $totalFees = $this->Campus_app_model->getTotalFeesForCollege($collegeTypeID, $categoryID, $course);
                    if (!empty($totalFees) && isset($totalFees[0]->fees)) {
                        $feesLookup[$clgID] = $totalFees[0]->fees;
                    }

                    // Fetch ratings for each college
                    $TotalRate = $this->Review_model->getCollegeTotalRate($clgID);
                    $ratingsLookup[$clgID] = $TotalRate['totalRateCount'] ?? 0;
                }

                // Fetch the sub-category name
                $subCatName = $this->Campus_app_model->getSubCatByCoursesId($course);

                // Update the college list
                foreach ($collegeList as &$clg) {
                    $clgID = $clg['collegeid'];

                    // Assign total fees if not already set
                    if (empty($clg['total_fees']) && isset($feesLookup[$clgID])) {
                        $clg['total_fees'] = $feesLookup[$clgID];
                    }

                    $clg['logo'] = base_url() . "/uploads/college/" . $clg['logo'];
                    $clg['file'] = base_url() . "/uploads/brochures/" . $clg['file'];
                    $clg['rating'] = $ratingsLookup[$clgID] ?? 0;

                    // Fetch the course count once per college
                    $courseCount = $this->Campus_app_model->countCoursesByCourseId($course, $clgID);
                    $clg['CourseCount'] = $courseCount;
                    $clg['subCatName'] = $subCatName;
                }

                $response = [
                    "response_code" => "200",
                    "response_message" => "Success",
                    "collegelist" => $collegeList,
                ];
            } else {
                $response = [
                    "response_code" => "400",
                    "response_message" => "Failed",
                ];
            }
        } else {
            $response = [
                "response_code" => "500",
                "response_message" => "Data is null",
            ];
        }

        echo json_encode($response);
        exit;
    }


    public function getCollegeListByCatId()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $courseCatId = $data->courseCatId;
            $collegeList = $this->Campus_app_model->getCollegeListByCourse($courseCatId);

            foreach ($collegeList as &$clg) {
                $clgID = $clg['collegeid'];
                $clg['logo'] = base_url() . "/uploads/college/" . $clg['logo'];
                $clg['file'] = base_url() . "/uploads/brochures/" . $clg['file'];
                $TotalRate = $this->Review_model->getCollegeTotalRate($clgID);
                $RateCount = $TotalRate['totalRateCount'];
                $clg['rating'] = $RateCount;
            }
            $result = json_encode($collegeList);

            if ($collegeList) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["collegelist"] = $collegeList;
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
        exit;
    }


    /**
     * To get college list by \Fees
     */
    public function getClgListByFees()
    {
        // echo "tttt";exit;
        $data = json_decode(file_get_contents('php://input'));

        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data->status = 'ok';
            echo json_encode($data);
            exit;
        }

        if ($data) {
            $min_fees = $data->min_fees;

            $max_fees = $data->max_fees;

            $course = isset($data->courseId) ? $data->courseId : '';
            $startlimit = isset($data->startlimit) ? $data->startlimit : 0;
            $endlimit = isset($data->endlimit) ? $data->endlimit : 5;
            //$Courseid = $this->Campus_app_model->getcourseParentId($course);
            //   print_r($Courseid);exit;
            $stateName = $data->statename;

            //$subCatName = $this->Campus_app_model->getSubCatByCoursesId($course);
            // print_r($stateName);exit;

            $ClgList = $this->Campus_app_model->getClgbyFees($min_fees, $max_fees, $stateName, $course, $startlimit, $endlimit);
            // print_r($ClgList);exit;
            if (!empty($ClgList)) {
                foreach ($ClgList as &$ci) {
                    $courseCount = $this->Campus_app_model->countCourseByClgID($ci['collegeid']);
                    $TotalRate = $this->Review_model->getCollegeTotalRate($ci['collegeid']);
                    $RateCount = $TotalRate['totalRateCount'];
                    $ci['coursesCount'] = $courseCount;
                    $ci['rating'] = $RateCount;
                    $ci['logo'] = base_url() . "/uploads/college/" . $ci['logo'];
                    $ci['file'] = base_url() . "/uploads/brochures/" . $ci['file'];
                    //$ci["subCatName"] = $subCatName;
                    //$ci["CatId"] = $Courseid[0]['parent_category'];
                }
                if ($ClgList) {
                    $response["response_code"] = "200";
                    $response["response_message"] = "Success";
                    $response["Colleges"] = $ClgList;
                }
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
     * To get list of popular colleges of engineering
     */
    public function getPopColleges()
    {
        //echo "tttt";exit;
        $data = json_decode(file_get_contents('php://input'));

        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data["status"] = "ok";
            echo json_encode($data);
            exit;
        }
        if ($data) {
            $courseId = $data->courseId;
            $categoryid = isset($data->categoryid) ? $data->categoryid : '';
            $getCollegeType = $this->Campus_app_model->getCollegeType($courseId);

            $collegeTypeArray = array_column($getCollegeType, 'college_type');

            $finalResult = [];

            // foreach ($collegeTypeArray as $collegeType) {
            $result = $this->Campus_app_model->getPopCollegesNew($courseId, $categoryid);

            /* foreach ($result as $key => $val) {
                    
                    if (empty($val['total_fees'])) {
                        $oldData = $this->Campus_app_model->getPopCollegesOld($val['collegeid']);
                        if (!empty($oldData)) {
                            $result[$key] = $oldData; // Replace only if old data exists
                        }
                    }
                }
*/
            /*if (!empty($result)) {
                    $finalResult = array_merge($finalResult, $result);
                }*/


            // print_r($result);exit;
            foreach ($result as &$college) {
                $TotalRate = $this->Review_model->getCollegeTotalRate($college['collegeid']);
                $college['logo'] = base_url() . "/uploads/college/" . $college['logo'];
                $RateCount = $TotalRate['totalRateCount'];
                $college['ratings'] = $RateCount;
            }
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
     *  To get list of engineering colleges by Ranking
     */
    public function getCollegeListByRank()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $courseId = $data->courseId;

            $result = $this->Campus_app_model->getCollegesListByRank($courseId);

            foreach ($result as &$clg) {
                $TotalRate = $this->Review_model->getCollegeTotalRate($clg->collegeid);
                $RateCount = $TotalRate['totalRateCount'];
                $clg->logo = base_url() . "/uploads/college/" . $clg->logo;
                $clg->file = base_url() . "/uploads/brochures/" . $clg->file;
                $clg->ratings = $RateCount;
            }
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
    /**
     * To get college list by location
     */

    ///----------
    public function getClgListByLoc()
    {
        $data = json_decode(file_get_contents('php://input'));

        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data->status = 'ok';
            echo json_encode($data);
            exit;
        }

        if ($data) {
            $loc = isset($data->loc) ? $data->loc : '';
            $course = isset($data->course) ? $data->course : '';
            $Courseid = $this->Campus_app_model->getcoursesId($course);

            foreach ($Courseid as &$ci) {
                $CourseId = $ci['id'];
                //echo $CourseId;exit;
                $ClgList = $this->Campus_app_model->getClgListByLoc($loc, $CourseId);
                //$ci['ratings'] = $RateCount;
            }
            //print_r($ClgList);exit;
            //$ClgList = $this->Campus_app_model->getClgListByLoc($loc, $Courseid);
            foreach ($ClgList as &$clg) {
                $TotalRate = $this->Review_model->getCollegeTotalRate($clg['id']);

                $courseCount = $this->Campus_app_model->countCourseByClgID($clg['id']);
                // print_r($courseCount);exit;
                $clg['coursesCount'] = $courseCount;
                $RateCount = $TotalRate['totalRateCount'];
                $clg['ratings'] = $RateCount;
                $clg['logo'] = base_url() . "/uploads/college/" . $clg['logo'];
                $clg['file'] = base_url() . "/uploads/brochures/" . $clg['file'];
                $clg['image'] = base_url() . '/uploads/college/' . $clg['image'];
            }

            if ($ClgList) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["Colleges"] = $ClgList;
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

    public function collegesOffereingSameCourseAtSameCity()
    {
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
        if ($data) {
            $courseid = $data->courseid;
            $cityid = $data->cityid;
            $collegeId = isset($data->collegeId) ? $data->collegeId : "";

            $result = $this->College_model->collegesOffereingSameCourseAtSameCity(
                $courseid,
                $cityid,
                $collegeId
            );
            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["colleges_Offereing_SameCourse"] = $result;
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
    public function getCollegeListByLoc()
    {
        $data = json_decode(file_get_contents('php://input'));

        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data->status = 'ok';
            echo json_encode($data);
            exit;
        }

        if ($data) {
            $locId = isset($data->locId) ? $data->locId : '';
            //echo $locId;exit;
            $loc = array();
            $result = array();
            $loc = explode(',', $locId);
            $limit = (count($loc) <= 2) ? 10 : 3;
            //print_r($loc);exit;
            $courseId = isset($data->courseId) ? $data->courseId : '';
            $parentCatid = $this->Campus_app_model->getcourseParentId($courseId);
            //print_r($parentCatid);exit;
            foreach ($loc as $locID) {
                //echo $locID. $parentCatid[0]['parent_category']. $limit;exit;
                $ClgList = $this->Campus_app_model->getCollegeListByLoc($locID, $parentCatid[0]['parent_category'], $limit);
                $result = array_merge($result, $ClgList);
            }
            //print_r($result);exit;
            foreach ($result as &$college) {
                $TotalRate = $this->Review_model->getCollegeTotalRate($college['id']);
                $RateCount = $TotalRate['totalRateCount'];
                $college['ratings'] = $RateCount;
                $college['image'] = base_url() . '/uploads/college/' . $college['image'];
                $college['logo'] = base_url() . "/uploads/college/" . $college['logo'];
                $college['file'] = base_url() . "/uploads/brochures/" . $college['file'];
            }
            $subCatName = $this->Campus_app_model->getSubCatByCoursesId($courseId);
            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["Colleges"] = $result;
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

    public function getClgListByLoc_copy()
    {
        $data = json_decode(file_get_contents('php://input'));

        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data->status = 'ok';
            echo json_encode($data);
            exit;
        }

        if ($data) {
            $loc = isset($data->locId) ? $data->locId : '';
            $subcategory = isset($data->subcategory) ? $data->subcategory : '';
            $parentCatid = $this->Campus_app_model->getcourseParentId__($subcategory);
            $startlimit = isset($data->startlimit) ? $data->startlimit : '0';
            $endlimit = isset($data->endlimit) ? $data->endlimit : '10';

            $ClgList = $this->Campus_app_model->getClgListByLoc($loc, $subcategory, $startlimit, $endlimit);
            foreach ($ClgList as &$clg) {
                $TotalRate = $this->Review_model->getCollegeTotalRate($clg['id']);
                $courseCount = $this->Campus_app_model->countCourseByClgID($clg['id']);
                $clg['coursesCount'] = $courseCount;
                $RateCount = $TotalRate['totalRateCount'];
                $clg['ratings'] = $RateCount;
                $clg['image'] = base_url() . '/uploads/college/' . $clg['image'];
                $clg['logo'] = base_url() . "/uploads/college/" . $clg['logo'];
                $clg['file'] = base_url() . "/uploads/brochures/" . $clg['file'];
            }

            if ($ClgList) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["Colleges"] = $ClgList;
                $response["TotalColleges"] = count($ClgList);
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

            $result = $this->Campus_app_model->getFeesDataOfCollege($collegeId);
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
    //-------------------College Details---------------------------//
    /**
     * To get college Details by college id
     * 
     * 
     */

    public function getTableOfConent()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $collegeId = isset($data->collegeId) ? $data->collegeId : '';

            $tableOfContent = $this->College_model->getTableOfContent($collegeId);

            // print_r($tableOfContent);exit;

            if ($tableOfContent) {

                //       $titles = array_map(function($item) {
                //     return $item['title']; // Extract title from each array item
                // }, $tableOfContent);

                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["response_data"] =   $tableOfContent;
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
    public function getCollegeDetailsByID()
    {
        // echo "ttt";exit;
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $id = $data->collegeId;
            //     print_r($id);exit;
            $courselevel = isset($data->courselevel) ? $data->courselevel : '';
            $subcategory = isset($data->subcategory) ? $data->subcategory : '';



            $courseList = $this->College_model->getCourses($id, $courselevel, $subcategory);
            // print_r($courseList);exit;

            //   print_r($id);exit;


            $ClgList = $this->College_model->getCollegeDetailsByID($id);
            // $ClgHighlight = $this->Comparecollege_model->getCollegeHighlightByID($id);
            //  print_r($ClgList);exit;
            //  $clgCourses = $this->College_model->getCollegeCoursesByID($id);
            $clgCourses = $this->College_model->getCollegeCoursesCountByID($id);
            //$tableOfContent = $this->College_model->getTableOfContent($collegeId);
            //  getCollegeCoursesCountByID
            // print_r($clgCourses);exit;
            $clgReviewRate = $this->College_model->getCollegeTotalRate($id);
            $clgAcademic_data = $this->College_model->getAcademicDataByClgId($id);
            if ($courseList) {
                $coursesandfees = $this->College_model->getCoursesAndFeesOfClg($id, $courseList[0]['level'], $subcategory);
            } else {
                $coursesandfees = [];
            }
            $adminssionprocess = $this->College_model->getCollegeAdmissionProcess($id, $subcategory);

            $facilities = $this->College_model->getCollegeFacilities($id);
            //print_r($facilities[0]->facilities);exit;


            // $facilityArray = explode(',', $facilities[0]->facilities);
            // $iconArray = explode(',', $facilities[0]->icons);

            // $facilitiesWithIcons = [];

            // foreach ($facilityArray as $index => $facility) {
            //     $facilitiesWithIcons[] = [
            //         'name' => $facility,
            //         'icon' => isset($iconArray[$index]) ? $iconArray[$index] : null
            //     ];
            // }


            $facilityData = $facilities[0]->facilities ?? '';
            $iconData = $facilities[0]->icons ?? '';

            if (!empty($facilityData)) {
                $facilityArray = explode(',', $facilityData);
                $iconArray = explode(',', $iconData);

                $facilitiesWithIcons = [];

                foreach ($facilityArray as $index => $facility) {
                    $facilitiesWithIcons[] = [
                        'name' => $facility,
                        'icon' => $iconArray[$index] ?? null
                    ];
                }
            } else {
                $facilitiesWithIcons = null;
            }



            // Output the result
            // print_r($facilitiesWithIcons);exit;
            $result = [];
            foreach ($ClgList as $clg) {
                // print_r($clg);exit;
                $rnkList = $this->College_model->getRankListByClgId($clg['id']);
                $RankList = [];
                foreach ($rnkList as $rn) {
                    $rankData = [
                        "title" => $rn->title,
                        "rank" => $rn->rank,
                        "year" => $rn->year,
                    ];
                    $RankList[] = $rankData;
                }
                //  print_r($clg);exit;
                $nestedData["id"] = $clg['id'];
                $nestedData["what_new"] = $clg['what_new'];
                $nestedData["title"] = $clg['title'];
                $nestedData["description"] = $clg['description'];
                $nestedData["logo"] =  base_url() . "/uploads/college/" . $clg['logo'];
                $nestedData["image"] = base_url() . "/uploads/college/" . $clg['image'];
                $nestedData["city"] = $clg['city'];
                $nestedData["state"] = isset($clg['statename']) ? $clg['statename'] : '';
                $nestedData["country"] = $clg['country'];
                $nestedData["estd"] = $clg['estd'];
                $nestedData["accreditation"] = $clg['accreditation'];
                $nestedData["package_type"] = $clg['package_type'];
                $nestedData["category"] = $clg['catname'];
                $nestedData["categoryid"] = $clg['categoryid'];
                $nestedData["cityid"] = $clg['cityid'];
                $nestedData["Collage_category"] = $clg['name'];
                $nestedData["coursesandfees"] = $coursesandfees;
                $nestedData["Courses_Count"] = $clgCourses;
                $nestedData["Courses_list"] = $courseList;
                $nestedData["Rank"] = $RankList;
                $nestedData["ReviewRating"] = $clgReviewRate;
                $nestedData["Academic_Date"] = $clgAcademic_data;
                $nestedData["Adminssionprocess"] = $adminssionprocess;
                //print_r($facilitiesWithIcons);exit;
                $nestedData["facilities"] = $facilitiesWithIcons;


                $result[] = $nestedData;
            }
            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["college_detail"] = $result;
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
        exit;
    }

    /*public function getCollegeDetailsByID()
    {
        //print_r("testing...");exit;
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $collegeId = $data->collegeId;
            $ClgList = $this->College_model->getCollegeDetailsByID($collegeId);
			$increment_view = $this->College_model->increment_view($collegeId);
            //print_r($ClgList);exit;
            $ClgHighlight = $this->College_model->getCollegeHighlightByID($collegeId);
            $clgCourses = $this->College_model->getCollegeCoursesByID($collegeId);
            $tableOfContent = $this->College_model->getTableOfContent($collegeId);
			//print_r($tableOfContent);exit;
            $clgImages = $this->College_model->getCollegeImagesByID($collegeId);
            $TotalRate = $this->Review_model->getCollegeTotalRate($collegeId);
            // $popularProgrammes = $this->College_model->getCollegeProgrammesByID($id);
            foreach ($clgImages as $key => $img) {
                $clgImages[$key]->imageName = $img->image;
                $clgImages[$key]->image = base_url() . '/uploads/college/' . $img->image;
            }

            $result = [];
            //print_r($ClgList);exit;
            foreach ($ClgList as $clg) {
                //echo $clg['id'];exit;
                $rnkList = $this->Campus_app_model->getRankListByClgId($clg['id']);
                $RankList = [];
                foreach ($rnkList as $rn) {
                    $rankData = [
                        "title" => $rn->title,
                        "rank" => $rn->rank,
                        "year" => $rn->year,
                    ];
                    $RankList[] = $rankData;
                }
                $nestedData["id"] = $clg['id'];
                $nestedData["title"] = $clg['title'];
                $nestedData["description"] = $clg['description'];
                $nestedData["application_link"] = $clg["application_link"];
                $nestedData["logo"] =  base_url() . "/uploads/college/" . $clg['logo'];
                $nestedData["image"] = base_url() . "/uploads/college/" . $clg['image'];
                $nestedData["city"] = $clg['city'];
                $nestedData["cityid"] = $clg['cityid'];
                
                $nestedData["Courses_Count"] = $clgCourses;
                
                $nestedData["country"] = $clg['country'];
                $nestedData["estd"] = $clg['estd'];
                $nestedData["accreditation"] = $clg['accreditation'];
                $nestedData["package_type"] = $clg['package_type'];
                $nestedData["categoryId"] = $clg['catID'];
                $nestedData["category"] = $clg['catname'];
                $nestedData["Collage_category"] = $clg['name'];
                $nestedData["what_new"] = $clg['what_new'];
                $nestedData["CollegeHighlight"] = $ClgHighlight;
                $nestedData["Courses"] = $clgCourses;
                $nestedData["Rank"] = $RankList;
                $nestedData["Rating"] = $TotalRate;
                // $tableOfContent = [];
                $nestedData["facilities"] = $facilitiesWithIcons;


                $result[] = $nestedData;
            }
            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["college_detail"] = $result;
                
                $response["table_of_content"] = $tableOfContent;
                $response["college_images"] = $clgImages;
                // $response["popular_programmes"] = $popularProgrammes;
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
        exit;
    }*/

    public function getCollegeHighlightByID()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $id = $data->collegeId;

            $result = $this->College_model->getCollegeHighlightByID($id);
            $result2 = $this->getCommonalyAskedQ($id, $type = 'HIGHLIGHTS');

            if ($result || $result2) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["CollegeHighlight"] = $result;
                $response["Commonaly_Asked_Questions"] = $result2;
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
        exit;
    }

    public function getCollegeTotalRate()
    {
        $data = json_decode(file_get_contents('php://input'));
        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data['status'] = 'ok';
            echo json_encode($data);
            exit;
        }
        if ($data) {
            $collegeid = $data->collegeId;
            $TotalRate = $this->Review_model->getCollegeTotalRate($collegeid);

            if ($TotalRate) {
                $response['response_code'] = '200';
                $response['response_message'] = 'Success';
                $response['data'] = $TotalRate;
            } else {
                $response['response_code'] = '400';
                $response['response_message'] = 'Failed';
            }
        } else {
            $response['response_code'] = '500';
            $response['response_message'] = 'Data is null.';
        }

        echo json_encode($response);
    }

    /* public function getPlacementDataOfClg()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $searchYear = isset($data->searchYear) ? $data->searchYear : date('Y') - 1;
            $searchCategory = isset($data->searchCategory) ? $data->searchCategory : '2';
            $collegeId = isset($data->collegeId) ? $data->collegeId : '';
            // print_r($searchYear);exit;
            $result = $this->College_model->getPlacementDataOfClg($searchCategory, $searchYear, $collegeId);
            print_r($result);exit;
            $result2 = $this->getCommonalyAskedQ($collegeId, $type = 'PLACEMENT');

            if ($result || $result2) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["placementlist"] = $result;
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
    }*/

    public function getPlacementDataOfClg()
    {
        //echo "tttt";exit;
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
            $searchYear = isset($data->searchYear)
                ? $data->searchYear
                : date("Y") - 1;
            $searchCategory = isset($data->searchCategory)
                ? $data->searchCategory
                : "2";
            $collegeId = isset($data->collegeId) ? $data->collegeId : "";
            // print_r($searchYear);exit;
            $result = $this->College_model->getPlacementDataOfClg(
                $searchCategory,
                $searchYear,
                $collegeId
            );
            $result2 = $this->getCommonalyAskedQ(
                $collegeId,
                $type = "PLACEMENT"
            );

            if ($result || $result2) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["placementlist"] = $result;
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


    public function getRanktDataOfClg()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $collegeId = isset($data->collegeId) ? $data->collegeId : '';
            $result = $this->College_model->getRanktDataOfClg($collegeId);
            $result2 = $this->getCommonalyAskedQ($collegeId, $type = 'RANKING');
            foreach ($result as $key => $value) {
                if (isset($result[$key]->image)) {
                    $result[$key]->image = base_url('uploads/rankimage/') . $result[$key]->image;
                }
            }
            if ($result || $result2) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["rankList"] = $result;
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
    //--------------Commenly asked questions---------------------//
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


    public function getCoursesAndFeesByClg()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $collegeId = isset($data->collegeId) ? $data->collegeId : "";

            $result = $this->College_model->getCoursesAndFeesByClg($collegeId);
            // print_r($result);exit;

            // foreach ($result as &$clg) {
            //  //   print_r($clg['entrance_exams']);exit;
            //     $total_fees = $this->College_model->getTotalFeesForCollege($clg['college_typeid'], $clg['categoryid'], $clg['sub_category'], $clg['entrance_exams']);

            //     foreach ($total_fees as $totalfees) {
            //      //   print_r($clg['total_fees']);exit;
            //         if (empty($clg['total_fees'])) {
            //             $clg['total_fees'] = $totalfees['fees'];
            //         }
            //     }
            //     if (empty($clg['total_fees']) && empty($total_fees)) {
            //         $clg['total_fees'] = 'N/A';
            //     }

            //     foreach ($clg as $key => $value) {
            //         if (empty($value)) {
            //             $clg[$key] = 'N/A';
            //         }
            //     }

            // }

            //print_r($result);
            foreach ($result as &$clg) {

                $total_fees = $this->College_model->getTotalFeesForCollege($clg['college_typeid'], $clg['categoryid'], $clg['sub_category'], $clg['entrance_exams']);
                // print_r($clg['total_fees'] .'---');
                if ($clg['total_fees'] == '' || $clg['total_fees'] == NULL) {
                    //  print_r($clg['total_fees']);exit;
                    foreach ($total_fees as $totalfees) {
                        $clg['total_fees'] = $totalfees['fees'];
                    }
                }
                //  print_r($clg['total_fees'].'---');
                if (empty($clg['total_fees']) && empty($total_fees)) {
                    $clg['total_fees'] = 'N/A';
                }

                foreach ($clg as $key => $value) {
                    if (empty($value)) {
                        $clg[$key] = 'N/A';
                    }
                }
            }
            //  print_r($result);exit;
            foreach ($result as $key => $value) {
                // print_r($value['eligibility']);exit;
                $result[$key]['eligibility'] = json_decode(
                    $result[$key]['eligibility']
                );
            }
            $result2 = $this->getCommonalyAskedQ($collegeId, $type = "Fees");

            if ($result || $result2) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["courselist"] = $result;
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


    public function getCoursesAndFeesOfClg()
    {
        // echo "tttt";exit;
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $collegeId = isset($data->collegeId) ? $data->collegeId : '';
            //  $result = $this->College_model->getCoursesAndFeesOfClg($collegeId);

            $result = $this->College_model->getCoursesAndFeesOfClg($collegeId);
            // print_r($result);exit;
            foreach ($result as &$clg) {
                $total_fees = $this->Campus_app_model->getTotalFeesForCollege($clg['college_typeid'], $clg['categoryid'], $clg['sub_category'], $clg['entrance_exams']);

                /*  if(empty($clg['total_fees']))
                {
                $clg['total_fees'] = $total_fees[0]->fees;
               
                }*/

                if (empty($clg['total_fees'])) {
                    if (!empty($total_fees) && isset($total_fees[0]->fees)) {
                        $clg['total_fees'] = $total_fees[0]->fees;
                    }
                }

                if (empty($clg['total_fees']) && empty($total_fees)) {
                    $clg['total_fees'] = 'N/A';
                }

                foreach ($clg as $key => $value) {
                    if (empty($value)) {
                        $clg[$key] = 'N/A';
                    }
                }
            }
            foreach ($result as $key => $value) {
                $result[$key]['eligibility'] = json_decode($value['eligibility']);
            }
            // print_r("tttt");exit;
            $result2 = $this->getCommonalyAskedQ($collegeId, $type = 'Fees');


            if ($result || $result2) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["courselist"] = $result;
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

    /* public function getCollegeProgrammesByID()
    {
       //echo "ttt";exit;
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $collegeId = isset($data->collegeId) ? $data->collegeId : '';
            $subcategory = isset($data->subcategory) ? $data->subcategory : '';
            $result = $this->College_model->getCollegeProgrammesByID($collegeId,$subcategory);
                $fees = $this->College_model->getfees($collegeId,$subcategory);
          //  print_r($fees);exit;
            $result2 = $this->getCommonalyAskedQ($collegeId, $type = 'COURSES');

            if ($result || $result2) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["popular_programmes"] = $result;
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
    }*/

    public function getCollegeProgrammesByID()
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
            $collgeTypeid = isset($data->collegeTypeId) ? $data->collegeTypeId : '';

            $result = $this->College_model->getCollegeProgrammesByID($collegeId, $subcategory);
            $fees = $this->College_model->getfees($collegeId, $subcategory, $collgeTypeid);
            $entranceExams = $this->College_model->getexams($collegeId, $subcategory);
            $result2 = $this->getCommonalyAskedQ($collegeId, $type = 'COURSES');

            // Prepare fees map
            $feesMap = [];
            if (!empty($fees)) {
                foreach ($fees as $fee) {
                    if (!empty($fee['total_fees'])) {
                        $feesMap[$fee['courseid']] = $fee['total_fees'];
                    }
                }
            }

            // Prepare exams map
            $examsMap = [];
            if (!empty($entranceExams)) {
                foreach ($entranceExams as $exam) {
                    $examsMap[$exam['courseid']] = $exam['entrance_exam_names'];
                }
            }

            // Merge fees and exams into result
            foreach ($result as &$course) {
                $courseId = $course['courseid'];

                // Merge total_fees
                $currentFee = isset($course['total_fees']) ? trim($course['total_fees']) : '';
                if ($currentFee === '' || is_null($currentFee)) {
                    $course['total_fees'] = isset($feesMap[$courseId]) ? $feesMap[$courseId] : '';
                }

                // Merge entrance_exam_names

                $course['entrance_exam_names'] = isset($examsMap[$courseId])
                    ? preg_replace('/\s*,\s*/', ',    ', trim($examsMap[$courseId]))
                    : null;


                // $course['entrance_exam_names'] = str_replace(',', ' , ', $course['entrance_exam_names']);

            }
            unset($course); // best practice

            if ($result || $result2) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["popular_programmes"] = $result;
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



    public function getCollegeOtherProgrammesByID()
    {
        // echo "ttt";exit;
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $collegeId = isset($data->collegeId) ? $data->collegeId : '';
            $subcategory = isset($data->subcategory) ? $data->subcategory : '';
            $result = $this->College_model->getCollegeOtherProgrammesByID($collegeId, $subcategory);
            $result2 = $this->getCommonalyAskedQ($collegeId, $type = 'COURSES');

            if ($result || $result2) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["popular_programmes"] = $result;
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

    public function getCollegeContactDetails()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $collegeId = isset($data->collegeId) ? $data->collegeId : '';
            $result = $this->College_model->getCollegeContactDetails($collegeId);

            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["ContactDetails"] = $result;
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

    public function getcollegeByLocation()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $collegeId = isset($data->collegeId) ? $data->collegeId : '';
            $cityid = isset($data->cityid) ? $data->cityid : '';
            $result = $this->College_model->collegeByLocation($cityid, $collegeId);

            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["collegeByLocation"] = $result;
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

    public function getFAQsOfClg()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $collegeId = isset($data->collegeId) ? $data->collegeId : '';
            $result = $this->College_model->getFAQsOfClg($collegeId);
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

            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["FAQs"] = $FAQs;
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

    //    public function getCollegeAdmissionProcess()
    // {
    //     // echo "tttt";exit;
    //     $data = json_decode(file_get_contents("php://input"));

    //     // Handle OPTIONS request
    //     if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
    //         $data["status"] = "ok";
    //         echo json_encode($data);
    //         exit();
    //     }

    //     if ($data) {
    //         $collegeId = isset($data->collegeId) ? $data->collegeId : '';
    //         $result = $this->College_model->getCollegeAdmissionProcess($collegeId);
    //        // print_r($result);exit;
    //         $EXAMs = array();

    //         foreach ($result as $key => $value) {
    //           //  $result[$key]->eligibility = json_decode($result[$key]->eligibility);
    //            // print_r($result[$key]->entrance_exams);exit;
    //              // print_r($result[$key]->eligibility);exit;

    //             // Split exams and accepting exams
    //             $entranceExams = explode(',', $result[$key]->entrance_exams ?? '');
    //             $acceptingExams = explode(',', $result[$key]->Accepting_Exams);

    //             // Remove duplicate exams from both arrays
    //             $entranceExams = array_unique($entranceExams);
    //             $acceptingExams = array_unique($acceptingExams);

    //             // Ensure both arrays have the same length before combining
    //             $combinedExams = array();
    //             foreach ($entranceExams as $index => $examId) {
    //                 $combinedExams[] = array(
    //                     'id' => $examId,
    //                     'value' => isset($acceptingExams[$index]) ? $acceptingExams[$index] : ''
    //                 );
    //             }

    //             // Set combined exams to result
    //             $result[$key]->acceptingExams = $combinedExams;

    //             // Handle exam notifications
    //             foreach ($entranceExams as $examId) {
    //                 $exams = $this->College_model->getExamsNotification($examId);
    //                 if (!empty($exams)) {
    //                     $EXAMs[] = array(
    //                         'Examnotification_or_ImportantDates' => $exams[0]['notification']
    //                     );
    //                     $result[$key]->Examnotification_or_ImportantDates = $exams[0]['notification'];
    //                 }
    //             }
    //         }

    //         // Fetch commonly asked questions related to admissions
    //         $result2 = $this->getCommonalyAskedQ($collegeId, $type = 'Admissions');
    //         /*$eligibility = $result[0]->eligibility; // Assuming this is an object or array

    //         // Convert the arrays to strings using implode
    //         $qualification = implode(", ", $eligibility['qualification']);
    //         $cut_off = implode(", ", $eligibility['cut_off']);
    //         $other_eligibility = $eligibility['other_eligibility'];

    //         // Create the single line string
    //         $single_line = "Qualification: " . $qualification . ", Cut Off: " . $cut_off . 
    //                ($other_eligibility ? "\nOther Eligibility: " . $other_eligibility : "");*/
    //         // Prepare response
    //         if ($result || $result2) {
    //             //$result[0]->eligibility = $single_line;
    //             $response["response_code"] = "200";
    //             $response["response_message"] = "Success";
    //             $response["AdmissionProcess"] = $result;
    //             $response["Commonaly_Asked_Questions"] = $result2;
    //         } else {
    //             $response["response_code"] = "400";
    //             $response["response_message"] = "Failed";
    //         }
    //     } else {
    //         $response["response_code"] = "500";
    //         $response["response_message"] = "Data is null";
    //     }

    //     echo json_encode($response);
    //     exit();
    // }
    public function getCollegeAdmissionProcess()
    {
        $data = json_decode(file_get_contents("php://input"));

        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }

        if ($data) {

            $collegeId = isset($data->collegeId) ? $data->collegeId : '';
            $result = $this->College_model->getCollegeAdmissionProcess($collegeId);

            $EXAMs = [];

            foreach ($result as $key => $value) {

                /* -----------------------------
               SAFE explode() FIX (PHP 8+)
            ------------------------------ */
                $entranceExams = !empty($result[$key]->entrance_exams)
                    ? explode(",", $result[$key]->entrance_exams)
                    : [];

                $acceptingExams = !empty($result[$key]->Accepting_Exams)
                    ? explode(",", $result[$key]->Accepting_Exams)
                    : [];

                // Remove duplicates
                $entranceExams = array_unique($entranceExams);
                $acceptingExams = array_unique($acceptingExams);

                // Combine safely
                $combinedExams = [];
                foreach ($entranceExams as $index => $examId) {
                    $combinedExams[] = [
                        "id"    => $examId,
                        "value" => $acceptingExams[$index] ?? ""
                    ];
                }

                $result[$key]->acceptingExams = $combinedExams;

                /* -----------------------------
               Exam notifications
            ------------------------------ */
                foreach ($entranceExams as $examId) {
                    if (!$examId) continue; // skip empty values

                    $exams = $this->College_model->getExamsNotification($examId);

                    if (!empty($exams)) {
                        $EXAMs[] = [
                            "Examnotification_or_ImportantDates" => $exams[0]["notification"]
                        ];
                        $result[$key]->Examnotification_or_ImportantDates = $exams[0]["notification"];
                    }
                }
            }

            // Fetch common questions
            $result2 = $this->getCommonalyAskedQ($collegeId, 'Admissions');

            if ($result || $result2) {
                $response = [
                    "response_code" => "200",
                    "response_message" => "Success",
                    "AdmissionProcess" => $result,
                    "Commonaly_Asked_Questions" => $result2
                ];
            } else {
                $response = [
                    "response_code" => "400",
                    "response_message" => "Failed"
                ];
            }
        } else {
            $response = [
                "response_code" => "500",
                "response_message" => "Data is null"
            ];
        }

        echo json_encode($response);
        exit();
    }




    public function getScholarShipOfClg()
    {
        //echo "testing...";exit;
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $collegeId = isset($data->collegeId) ? $data->collegeId : '';
            $result = $this->College_model->getScholarShipOfClg($collegeId);
            // print_r($result);exit;
            $result2 = $this->getCommonalyAskedQ($collegeId, $type = 'SCHOLARSHIP');

            if ($result || $result2) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["scholarship_data"] = $result;
                $response["Commonaly_Asked_Questions"] = $result2;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }
        echo json_encode($response, JSON_UNESCAPED_SLASHES);
        exit();
    }



    public function getPopularClgByLocation()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $cityid = isset($data->cityid) ? $data->cityid : '';
            $result = $this->College_model->getPopularClgByLocation($cityid);

            foreach ($result as $key => $value) {
                $collegeId = $result[$key]->collegeid;
                $TotalRate = $this->Review_model->getCollegeTotalRate($collegeId);
                $courseCount = $this->Campus_app_model->countCourseByClgID($collegeId);
                if (isset($result[$key]->image)) {
                    $result[$key]->image = base_url('uploads/college/') . $result[$key]->image;
                    $result[$key]->logo = base_url('uploads/college/') . $result[$key]->logo;
                }
                $result[$key]->rating = $TotalRate;
                $result[$key]->courseCount = $courseCount;
            }
            /*$chunkedColleges = array_chunk($result, 3);
            $groupedPopColleges = [];
            foreach ($chunkedColleges as $chunk) {
                $groupedPopColleges[] = ['popularColleges' => $chunk];
            }*/
            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["popularColleges"] = $result;
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

    public function getCollegesAccordingCategory()
    {
        // echo "tttt";exit;
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $collegeId = $data->collegeId;
            $categories = $data->categories;
            $result = $this->College_model->getCollegesAccordingCategory($collegeId, $categories);
            // print_r($result);exit;
            //print_r($result);exit;
            foreach ($result as $key => $value) {
                //print_r($result);exit;
                $collegeId = $result[$key]['id'];
                $TotalRate = $this->Review_model->getCollegeTotalRate($collegeId);
                $courseCount = $this->Campus_app_model->countCourseByClgID($collegeId);
                if (isset($result[$key]['image'])) {
                    $result[$key]['image'] = base_url('uploads/college/') . $result[$key]['image'];
                    $result[$key]['logo'] = base_url('uploads/college/') . $result[$key]['logo'];
                }
                $result[$key]['rating'] = $TotalRate;
                $result[$key]['courseCount'] = $courseCount;
            }
            /*$chunkedColleges = array_chunk($result, 3);
            $groupedPopColleges = [];
            foreach ($chunkedColleges as $chunk) {
                $groupedPopColleges[] = ['bestColleges' => $chunk];
            }*/
            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["bestSuitedColleges"] = $result;
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

    public function getLastThreeYearsPlacementData()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $CurrentYear = date('Y');
            $collegeId = isset($data->collegeId) ? $data->collegeId : '';
            //print_r($CurrentYear);exit;
            $result = $this->College_model->getLastThreeYearsPlacementData($CurrentYear, $collegeId);
            $result2 = $this->getCommonalyAskedQ($collegeId, $type = 'PLACEMENT');

            if ($result || $result2) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["placementlist"] = $result;
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


    public function getCollegesearch()
    {
        //echo "testing...";exit;


        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }

        if ($data) {

            $searchterm = $data->searchcollege;

            $colleges = $this->College_model->get_colleges($searchterm);

            if ($colleges) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["colleges"] = $colleges;
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

    public function getCollegelistBytype()
    {
        // echo "testing...";exit;
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }

        if ($data) {

            $collgeType = $data->collgetype;

            //print_r($collgeType);exit;

            $colleges = $this->College_model->get_colleges($searchterm);

            if ($colleges) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["colleges"] = $colleges;
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


    public function getPopularCollegeListForCompare()
    {
        //	echo "testing...";exit;
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        /* if (empty($_SERVER["HTTP_AUTHORIZATION"])) {
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
            $categoryid = isset($data->categoryid) ? $data->categoryid : "";

            $result = $this->College_model->getPopularCollegeListForCompare(
                $categoryid
            );

            foreach ($result as $key => $value) {
                if (!empty($result[$key]['image'])) {
                    $result[$key]['image'] = base_url("uploads/college/") . $result[$key]['image'];
                }
                $TotalRate = $this->Review_model->getCollegeTotalRate($result[$key]['id']);

                $result[$key]['total_rate'] = $TotalRate;
            }


            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["CollegeListForCompare"] = $result;
                // $response['rating'] = $TotalRate;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }
        echo json_encode($response, JSON_UNESCAPED_SLASHES);
        exit();
    }



    /*	public function getCollegeProgrammesByID11()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $collegeId = isset($data->collegeId)?$data->collegeId:'';
            $result = $this->College_model->getCollegeProgrammesByID($collegeId);
            $result2 = $this->getCommonalyAskedQ($collegeId,$type = 'COURSES');

            if ($result || $result2) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["popular_programmes"] = $result;
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
    }*/


    public function getCollgefacilities()
    {
        //echo "testing...";exit;
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            // $collegeId = isset($data->collegeId)?$data->collegeId:'';
            $collegeId = $data->collegeId;
            //  print_r($collegeId);exit;
            $result = $this->College_model->getCollgeFacilities($collegeId);
            $faciArr = explode(",", $result[0]->facilities);
            $Facilities = [];
            foreach ($faciArr as $f) {
                $Facilities[] = $this->College_model->GetFacilities($f);
            }
            //print_r($Facilities);exit;


            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["facilities"] = $Facilities;
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

    //-------------------------------

    public function getCollegePlacement()
    {
        // echo "testing...";exit;
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }

        $categoryId = $data->categoryId;
        $limit = isset($data->limit) ? $data->limit : 10;

        // print_r($collegeId);exit;

        $result = $this->College_model->getCollegePlacement($categoryId, $limit);
        //print_r($result);exit;
        //$collgeId = $result[0]->collegeid;
        //print_r($collgeId);exit;
        //	print_r($result);exit;
        //-------convert to , format to array-----------
        // $faciArr = explode(",", $result[0]->facilities);
        // print_r($faciArr);exit;
        //$Facilities = [];

        //print_r($result);exit;

        foreach ($result as $r) {
            //print_r($f);exit;
            $collgeId = $r->collegeid;
            //print_r($collgeId.' ');
            //$Facilities[] = $this->College_model->GetFacilities($f);
            //$getPlacementCollege[] = $this->College_model->getCollegeDetailsBy_ID($r->collegeid);		
            //$total_fees = $r->total_fees ;

            $TotalRate = $this->Review_model->getCollegeTotalRate($r->collegeid);
            //print_r($TotalRate);exit;
            $images = $this->College_model->getImageById($r->collegeid);
            //print_r($images);exit;
            //$r->image = $images;
            //$image = [];
            foreach ($images as &$item) {
                $item['image'] = base_url('uploads/college/') . $item['image'];
            }
            //print_r($image);exit;
            //   $total_rate = $TotalRate['totalRateCount'];

            //   $courseCount = $this->Campus_app_model->countCourseByClgID($r->collegeid);
            //print_r($courseCount);exit;
            $r->image = $item['image'];
            //   $r->totalRateCount = $TotalRate['totalRateCount'];
            //  $r->CourseCount = $courseCount;

            //$result[$key]['courseCount'] = $courseCount;

            if (isset($r->logo)) {
                $r->logo = base_url('uploads/college/') . $r->logo;
            }





            //print_r($r->total_fees);exit;
            // $RateCount = $TotalRate['totalRateCount'];
        }
        //print_r($Facilities);exit;


        if ($result) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["PlacementCollege"] = $result;
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed";
        }
        echo json_encode($response);
        exit();
    }
    ///--------------------------

    public function getRatingColleges()
    {
        // echo "testing...";exit;
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }


        /*if($data){
			
//print_r($data);exit;
			
		$courseId = $data->courseId;
	    $maxRate = $data->maxRate; //4
		$minRate = $data->minRate; //5
			
		 $colleges = $this->College_model->get_collegesId($courseId);
			//print_r($colleges);exit;
        // $collegeId = $colleges->id;
			//print_r($collegeId);exit;
			
$filteredColleges = [];
	    
			foreach ($colleges as &$clg) {
				
                //$filteredColleges = [];
				

                $clgID = $clg['collegeid'];
                $clg['logo'] = base_url() . "/uploads/college/" . $clg['logo'];
                $clg['file'] = base_url() . "/uploads/brochures/" . $clg['file'];
                $TotalRate = $this->Review_model->getCollegeTotalRate($clgID);
                $RateCount = $TotalRate['totalRateCount'];
                //print_r($RateCount);exit;
                //$courseCount = $this->Campus_app_model->countCoursesByCourseId($Courseid, $clgID);
               // $clg['CourseCount'] = $courseCount;
                $clg['rating'] = $RateCount;
				
				 if ($RateCount >= $minRate && $RateCount <= $maxRate) {
                    $filteredColleges[] = $clg;
					 
					// print_r($clg);exit;
                }
            }
			
																	 // print_r($clg);exit;
		print_r($filteredColleges);exit;*/
        if ($data) {
            // Retrieve parameters from the input data
            $courseId = $data->courseId;
            // $maxRate = $data->maxRate; // Expected to be 5
            // $minRate = $data->minRate; // Expected to be 4

            $minRate = isset($data->minRate) ? $data->minRate : 4;
            $maxRate = isset($data->maxRate) ? $data->maxRate : 5;


            // Get the list of colleges based on the course ID
            $colleges = $this->College_model->get_collegesId($courseId);

            // Initialize an array to hold the filtered colleges
            $filteredColleges = [];

            // Loop through each college to process and filter based on rating
            foreach ($colleges as &$clg) {
                // Get the college ID
                $clgID = $clg['collegeid'];

                // Add the base URL to the logo and file paths
                $clg['logo'] = base_url() . "/uploads/college/" . $clg['logo'];
                $clg['file'] = base_url() . "/uploads/brochures/" . $clg['file'];

                // Get the total rating count for the college
                $TotalRate = $this->Review_model->getCollegeTotalRate($clgID);
                $RateCount = $TotalRate['totalRateCount'];

                // Add the rating count to the college data
                $clg['rating'] = $RateCount;
                //print_r($RateCount);exit;
                // Check if the rating is within the specified range
                if ($RateCount >= $minRate && $RateCount <= $maxRate) {
                    // Add the college to the filtered list
                    $filteredColleges[] = $clg;
                }
            }

            // Print the filtered colleges array
            // print_r($filteredColleges);exit;
            if ($colleges) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["filteredColleges"] = $filteredColleges;
                //$response["c"] = $filteredColleges;
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
    public function getCollegeDetailsBy()
    {
        //echo "testing...";exit;
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }


        if ($data) {
            $collegeId = $data->collegeId;

            $ClgList = $this->College_model->getCollegeDetailsByID($collegeId);

            $ClgHighlight = $this->College_model->getCollegeHighlightByID($collegeId);
            $clgCourses = $this->College_model->getCollegeCoursesByID($collegeId);
            $tableOfContent = $this->College_model->getTableOfContent($collegeId);
            $clgImages = $this->College_model->getCollegeImagesByID($collegeId);

            // $TotalRate = $this->Review_model->getCollegeTotalRate($collegeId);

            $TotalRate = $this->College_model->getCollegeTotalRate($collegeId);
            // print_r($TotalRate);
            // exit;
            // $popularProgrammes = $this->College_model->getCollegeProgrammesByID($id);
            foreach ($clgImages as $key => $img) {
                $clgImages[$key]->imageName = $img->image;
                $clgImages[$key]->image = base_url() . '/uploads/college/' . $img->image;
            }

            $result = [];
            //print_r($ClgList);exit;
            foreach ($ClgList as $clg) {
                //echo $clg['id'];exit;
                $rnkList = $this->Campus_app_model->getRankListByClgId($clg['id']);
                $RankList = [];
                foreach ($rnkList as $rn) {
                    $rankData = [
                        "title" => $rn->title,
                        "rank" => $rn->rank,
                        "year" => $rn->year,
                    ];
                    $RankList[] = $rankData;
                }
                $nestedData["id"] = $clg['id'];
                $nestedData["title"] = $clg['title'];
                $nestedData["description"] = $clg['description'];
                $nestedData["logo"] =  base_url() . "/uploads/college/" . $clg['logo'];
                $nestedData["image"] = base_url() . "/uploads/college/" . $clg['image'];
                $nestedData["city"] = $clg['city'];
                $nestedData["cityid"] = $clg['cityid'];

                $nestedData["country"] = $clg['country'];
                $nestedData["estd"] = $clg['estd'];
                $nestedData["accreditation"] = $clg['accreditation'];
                $nestedData["package_type"] = $clg['package_type'];
                $nestedData["categoryId"] = $clg['catID'];
                $nestedData["category"] = $clg['catname'];
                $nestedData["Collage_category"] = $clg['name'];
                $nestedData["what_new"] = $clg['what_new'];
                $nestedData["CollegeHighlight"] = $ClgHighlight;
                $nestedData["Courses"] = $clgCourses;
                $nestedData["Rank"] = $RankList;
                $nestedData["Rating"] = $TotalRate;
                // $tableOfContent = [];


                $result[] = $nestedData;
            }
            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["college_detail"] = $result;
                $response["table_of_content"] = $tableOfContent;
                $response["college_images"] = $clgImages;
                // $response["popular_programmes"] = $popularProgrammes;
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
        exit;
    }

    //----	
    public function getCollegeList()
    {
        // echo "ttttt";exit;
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }

        // print_r($_SERVER['HTTP_AUTHORIZATION']);exit;
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
        if ($data) {
            $columns = [
                0 => "id",
                1 => "title",
                2 => "package_type",
                3 => "status",
            ];

            // $limit = $data->length;
            // $start = ($data->draw - 1) * $limit;
            // $orderColumn = $columns[$data->order[0]->column];
            // $orderDir = $data->order[0]->dir;
            $totalData = $this->College_model->countAllClg();
            $totalFiltered = $totalData;
            $loc = isset($data->loc) ? $data->loc : "";
            $clgname = isset($data->clgname)
                ? $data->clgname
                : "";
            $courseid = isset($data->courseid)
                ? $data->courseid
                : "";
            $ownerShip = isset($data->ownerShip)
                ? $data->ownerShip
                : "";
            $rankCategory = isset($data->rankCategory)
                ? $data->rankCategory
                : "";
            $categoryid = isset($data->categoryid)
                ? $data->categoryid
                : "";
            $value = isset($data->value) ? $data->value : "";

            if (
                !empty($clgname) ||
                !empty($loc) ||
                !empty($ownerShip) ||
                !empty($rankCategory ||
                    !empty($courseid) ||
                    !empty($value) ||
                    !empty($categoryid))
            ) {
                //  $search = $data->search->value;
                $totalFiltered = $this->College_model->countFilteredClg(
                    $clgname,
                    $loc,
                    $ownerShip,
                    $rankCategory,
                    $courseid,
                    $value,
                    $categoryid
                );
                $ClgList = $this->College_model->getFilteredClg(
                    $clgname,
                    // $start,
                    //  $limit,
                    //  $orderColumn,
                    //  $orderDir,
                    $loc,
                    $ownerShip,
                    $rankCategory,
                    $courseid,
                    $value,
                    $categoryid
                );
            } else {

                $ClgList = $this->College_model->getAllClg(
                    $start,
                    $limit,
                    $orderColumn,
                    $orderDir
                );
            }
            //  print_r($ClgList);exit;

            $datas = [];
            foreach ($ClgList as $clg) {
                $rnkList = $this->College_model->getRankListByClgId($clg->id);
                $RankList = [];
                $courseCount = $this->Campus_app_model->countCoursesByCourseId($courseid, $clg->id);
                $TotalRate = $this->Review_model->getCollegeTotalRate($clg->id);

                foreach ($rnkList as $rn) {
                    $rankData = [
                        "title" => $rn->title,
                        "rank" => $rn->rank,
                        "year" => $rn->year,
                    ];
                    $RankList[] = $rankData;
                }

                // print_r($clg);exit;
                $nestedData = [];
                $nestedData["id"] = $clg->id;
                $nestedData["title"] = $clg->title;
                $nestedData["logo"] =
                    base_url() . "/uploads/college/" . $clg->logo;
                $nestedData["image"] =
                    base_url() . "/uploads/college/" . $clg->gallery_image;
                $nestedData["banner"] =
                    base_url() . "/uploads/college/" . $clg->banner;
                $nestedData["city"] = $clg->city;
                $nestedData["estd"] = $clg->estd;
                $nestedData["package_type"] = $clg->package_type;
                $nestedData["Rank"] = $RankList;
                $nestedData["is_accept_entrance"] = $clg->is_accept_entrance;
                $nestedData["application_link"] = $clg->application_link;
                $nestedData["ratings"] = $TotalRate;
                $nestedData["courseCount"] = $courseCount;
                $nestedData["statename"] = $clg->statename ?? NULL;
                $datas[] = $nestedData;
            }

            $json_data = [
                // "draw" => intval($data->draw),
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

    public function addUpdateSpecificList()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $userId = $data->userId;
            $collegeId = $data->collegeId;
            $active = $data->active;
            $event = $data->event;
            if ($event === 'insert') {
                $checkData = $this->College_model->checkSpecificCollege($userId, $collegeId);
                //print_r($checkData);exit;
                if ($checkData) {
                    $response["response_code"] = "100";
                    $response["response_message"] = "Already added";
                    echo json_encode($response);
                    exit;
                } else {

                    $insertData =  array(
                        "userId" => $userId,
                        "collegeId" => $collegeId,
                        "active" => $active,
                        "create_date" => date('Y-m-d h:i:s'),
                        "update_date" => date('Y-m-d h:i:s')
                    );
                    $result = $this->College_model->insertSpecificCollege($insertData);
                }
            } else if ($event === 'update') {
                $updateData =  array(
                    "active" => $active,
                    "update_date" => date('Y-m-d h:i:s')
                );
                $result = $this->College_model->updateSpecificCollege($updateData, $userId, $collegeId);
            } else {

                $specific_Data = $this->College_model->getSpecificCollegeList($userId, $collegeId);
                $result = array();

                foreach ($specific_Data as $sD) {
                    $collegeId = $sD->collegeId;
                    $collegeDetails = $this->College_model->getCollegeListById($collegeId);

                    if (!isset($result[$collegeId])) {
                        $result[$collegeId] = array(
                            'collegeid' => $collegeDetails[0]['collegeid'],
                            'accreditation' => $collegeDetails[0]['accreditation'],
                            'title' => $collegeDetails[0]['title'],
                            'logo' => $collegeDetails[0]['logo'],
                            'address' => $collegeDetails[0]['address'],
                            'total_fees' => $collegeDetails[0]['total_fees'],
                            'eligibility' => $collegeDetails[0]['eligibility'],
                            'rank' => $collegeDetails[0]['rank'],
                            'year' => $collegeDetails[0]['year'],
                            'category' => $collegeDetails[0]['category'],
                            'file' => base_url() . "/uploads/brochures/" . $collegeDetails[0]['file']
                        );
                    }
                }
                //print_r($result);exit;
            }

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
            echo json_encode($response);
            exit();
        }

        echo json_encode($response);
        exit;
    }


    public function getListOfShortListedColleges()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $userId = $data->userId;
            // $collegeId = $data->collegeId;

            $collegeData = $this->College_model->getListOfShortListedColleges($userId);
            //print_r($collegeData);exit;
            $unique_colleges = [];
            $result = [];
            foreach ($collegeData as $res) {
                if (!isset($unique_colleges[$res->collegeid])) {
                    $unique_colleges[$res->collegeid] = true;
                    $result[] = $res;
                }
            }
            //print_r($filtered_data);exit;
            foreach ($result as $r) {
                $clgID = $r->collegeid;
                $TotalRate = $this->Review_model->getCollegeTotalRate($clgID);
                $r->Rating = $TotalRate;
                $r->logo =  base_url() . "/uploads/college/" . $r->logo;
                $r->file = base_url() . "/uploads/brochures/" . $r->file;
            }

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
            echo json_encode($response);
            exit();
        }

        echo json_encode($response);
        exit;
    }

    public function getGallaryOfTrendColleges()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {

            $collegeData = $this->College_model->getTrendColleges();
            //print_r($collegeData);exit;
            $gallary = [];
            foreach ($collegeData as $res) {
                //echo $res->id;exit;
                $gallary[] = $this->College_model->getGallaryOfTrendColleges($res->id);
                //print_r($gallary);exit;
                $res->gallary = $gallary;
            }

            //print_r($gallary);exit;
            if ($collegeData) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["response_data"] = $collegeData;
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
        exit;
    }

    public function getFeaturedColleges()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {/*(empty($_SERVER["HTTP_AUTHORIZATION"])) {
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
        } else{
            $headers = apache_request_headers();
            $token = str_replace("Bearer ", "", $headers["Authorization"]);
            $kunci = $this->config->item("jwt_key");
            $userData = JWT::decode($token, $kunci);
            Utility::validateSession($userData->iat, $userData->exp);
            $tokenSession = Utility::tokenSession($userData);
        } */
            $categoryid = $data->categoryId;
            $result = $this->College_model->getFeaturedColleges($categoryid);
            //print_r($result);exit;
            foreach ($result as $key => $value) {
                $getTotalCourses = $this->College_model->getTotalCourses(
                    $result[$key]->id
                );
                $result[$key]->totalCourseCount = $getTotalCourses;
                if (!empty($result[$key]->image)) {
                    $result[$key]->image =
                        base_url("uploads/college/") . $result[$key]->image;
                    $result[$key]->logo =  base_url() . "/uploads/college/" . $result[$key]->logo;
                    $result[$key]->file = base_url() . "/uploads/brochures/" . $result[$key]->file;
                }
            }

            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["data"] = $result;
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

    /*************CREATE Admission Process and Important Dates PDF *******************/

    public function AdmissionProcessImportantDatesPDF()
    {
        $data = json_decode(file_get_contents("php://input"));

        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }

        if ($data) {

            $collegeId = isset($data->collegeId) ? $data->collegeId : "";
            $category = isset($data->sub_category) ? $data->sub_category : "";

            $result = $this->College_model->getCollegeAdmissionProcess($collegeId);

            $EXAMs = [];

            foreach ($result as $key => $value) {

                /* -------------------------
               SAFE JSON DECODE
            ----------------------------*/
                if (isset($result[$key]->eligibility) && is_string($result[$key]->eligibility)) {
                    $result[$key]->eligibility = json_decode($result[$key]->eligibility);
                }

                /* -------------------------
               SAFE explode() - PHP 8 fix
            ----------------------------*/
                $result[$key]->entrance_exams = !empty($result[$key]->entrance_exams)
                    ? explode(",", $result[$key]->entrance_exams)
                    : [];

                $acceptingExams = !empty($result[$key]->Accepting_Exams)
                    ? explode(",", $result[$key]->Accepting_Exams)
                    : [];

                /* -------------------------
               Combine exams properly
            ----------------------------*/
                $combinedExams = [];
                foreach ($result[$key]->entrance_exams as $index => $examId) {
                    $combinedExams[] = [
                        "id" => $examId,
                        "value" => isset($acceptingExams[$index]) ? $acceptingExams[$index] : "",
                    ];
                }
                $result[$key]->acceptingExams = $combinedExams;

                /* -------------------------
               Exam Notification Fetch
            ----------------------------*/
                foreach ($result[$key]->entrance_exams as $examId) {
                    $exams = $this->College_model->getExamsNotification($examId);

                    if (!empty($exams)) {
                        $EXAMs[] = ["Examnotification_or_ImportantDates" => $exams[0]["notification"]];
                        $result[$key]->Examnotification_or_ImportantDates = $exams[0]["notification"];
                    }
                }
            }

            /* -------------------------
           HTML Content Start
        ----------------------------*/

            $content = '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>OhCampus</title>
            <style>
                body{font-size: 14px!important;font-family: Arial!important;padding: 5px!important;margin: 5px!important;}
                table{ width: 100%;border-collapse: collapse;}
                table th, table td{padding: 7px!important; border: 1px solid #e7e7e7;}
                .noborder th, .noborder td{border: 0px!important;}
                table th{text-align: left;}
                .textcenter{text-align: center;}
                .bgcolor{background: aliceblue;}
                .layout{max-width:700px; margin: auto; border: double; border-color: #e7e7e7;}
            </style>
        </head>
        <body>
        <header class="textcenter">
            <img src="https://campusapi.ohcampus.com/uploads/Untitled.jpeg" alt="Campus Logo" style="width:350px;">
        </header>

        <section class="margintopcss">
            <div class="layout">
                <header>
                    <table class="noborder">
                        <tbody>
                            <tr>
                                <td style="text-align:center;font-weight:bold;color:#88d834;">
                                    Admission Process & Important Dates
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </header>';

            $this->mpdf->SetHTMLFooter('
            <div style="text-align: center; font-size: 14px; color: #333;font-weight:bold;color:#88d834">
                <p>OhCampus.com, Comet Career India (R), 2nd Floor, SMG Plaza, MG Road, Chikkamagaluru, Karnataka.</p>
            </div>
        ');

            /* -------------------------
           Loop Final Data
        ----------------------------*/
            foreach ($result as $object) {

                if ($object->sub_category == $category) {

                    $courseName = $object->subCatName;
                    $duration = $object->duration;

                    $importantDates = isset($object->Examnotification_or_ImportantDates)
                        ? $object->Examnotification_or_ImportantDates
                        : "Important dates not available for this course.";

                    $content .= '
                    <table class="margintopcss">
                        <tr class="bgcolor">
                            <th colspan="2">' . $courseName . '<br>
                                <span style="font-weight:normal;">Duration : ' . $duration . ' years</span>
                            </th>
                        </tr>
                        <tr>
                            <th colspan="2">' . $importantDates . '</th>
                        </tr>
                    </table>';
                }
            }

            $content .= '</div></section></body></html>';

            /* -------------------------
           PDF Save
        ----------------------------*/

            $outputDirectory = FCPATH . "AllPdf/AdmissionProcessImportantDatesPDFDocs/";

            if (!is_dir($outputDirectory)) {
                mkdir($outputDirectory, 0777, true);
            }

            $filename = time() . "_Admission_impdates_.pdf";

            if (ob_get_length()) {
                ob_end_clean();
            }
            $this->mpdf->WriteHTML($content);
            $this->mpdf->Output($outputDirectory . $filename, "F");

            $response = [
                "response_code" => "200",
                "response_message" => "Success",
                "PDF" => base_url("AllPdf/AdmissionProcessImportantDatesPDFDocs/") . $filename,
            ];

            echo json_encode($response);
            exit();
        } else {
            echo json_encode([
                "response_code" => "500",
                "response_message" => "Data is null."
            ]);
        }
    }



    public function getCollegeFacilitiesByID()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        /* if (empty($_SERVER["HTTP_AUTHORIZATION"])) {
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
            $id = $data->collegeId;

            $result = $this->College_model->getCollegeFacilitiesByID($id);
            //print_r($result);exit;
            //  $result2 = $this->getCommonalyAskedQ($id, $type = "FACILITIES");

            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["CollegeFac"] = $result;
                //$response["Commonaly_Asked_Questions"] = $result2;
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
}
