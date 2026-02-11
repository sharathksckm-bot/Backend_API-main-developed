<?php
if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}
class college extends CI_Controller
{
    /**
     * Constructor
     *
     * Loads necessary libraries, helpers, and models for the app controller.
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model("apps/Campus_app_model", "", true);
        $this->load->model("apps/Review_model", "", true);
        $this->load->model("apps/College_model", "", true);
        //$this->load->model("apps/Exam_model", "", true);
        $this->load->library('Utility');
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
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $course = $data->courseId;
            $Courseid = $this->Campus_app_model->getcourseParentId($course);
            //echo $Courseid;exit;
            $subCatName = $this->Campus_app_model->getSubCatByCoursesId($course);
            $collegeList = $this->Campus_app_model->getCollegeListByCourse($Courseid);

            //print_r($collegeList);exit;
            foreach ($collegeList as &$clg) {
                $clgID = $clg['collegeid'];
                $clg['logo'] = base_url() . "/uploads/college/" . $clg['logo'];
                $clg['file'] = base_url() . "/uploads/brochures/" . $clg['file'];
                $TotalRate = $this->Review_model->getCollegeTotalRate($clgID);
                $RateCount = $TotalRate['totalRateCount'];
                //print_r($RateCount);exit;
                $courseCount = $this->Campus_app_model->countCoursesByCourseId($Courseid, $clgID);
                $clg['CourseCount'] = $courseCount;
                $clg['rating'] = $RateCount;
            }
            $result = json_encode($collegeList);

            if ($collegeList) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["collegelist"] = $collegeList;
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


    /**
     * To get college list by \Fees
     */
    public function getClgListByFees()
    {
        $data = json_decode(file_get_contents('php://input'));

        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data->status = 'ok';
            echo json_encode($data);
            exit;
        }

        if ($data) {
            $min_fees = $data->min_fees;
            $max_fees = $data->max_fees;
            $course = isset($data->course) ? $data->course : '';
            //$Courseid = $this->Campus_app_model->getcoursesId($course);
            //foreach ($Courseid as &$ci) {
            // $CourseId = $ci['id'];
            //$totalColleges = $this->Campus_app_model->countFilteredClgByfees($min_fees, $max_fees);
            $ClgList = $this->Campus_app_model->getClgbyFees($min_fees, $max_fees);
            //print_r($ClgList);exit;
            foreach ($ClgList as &$ci) {
                $courseCount = $this->Campus_app_model->countCourseByClgID($ci['collegeid']);
                $TotalRate = $this->Review_model->getCollegeTotalRate($ci['collegeid']);
                $RateCount = $TotalRate['totalRateCount'];
                $ci['coursesCount'] = $courseCount;
                $ci['rating'] = $RateCount;
            }
            if ($ClgList) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["Colleges"] = $ClgList;
                //$response["Total_Colleges"] = $totalColleges;
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
        $data = json_decode(file_get_contents('php://input'));

        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data["status"] = "ok";
            echo json_encode($data);
            exit;
        }
        if ($data) {
            $courseId = $data->courseId;

            $result = $this->Campus_app_model->getPopColleges($courseId);
            //print_r($result);exit;
            foreach ($result as &$college) {
                $TotalRate = $this->Review_model->getCollegeTotalRate($college['collegeid']);
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
            }
            //print_r($ClgList);exit;
            //$ClgList = $this->Campus_app_model->getClgListByLoc($loc, $Courseid);

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
            foreach ($loc as $locID) {
                $ClgList = $this->Campus_app_model->getCollegeListByLoc($locID, $parentCatid, $limit);
                $result = array_merge($result, $ClgList);
            }
            //print_r($result);exit;
            foreach ($result as &$college) {
                $TotalRate = $this->Review_model->getCollegeTotalRate($college['id']);
                $RateCount = $TotalRate['totalRateCount'];
                $college['ratings'] = $RateCount;
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
     */
    public function getCollegeDetailsByID()
    {
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
            $result2 = $this->getCommonalyAskedQ($id,$type = 'HIGHLIGHTS');

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

    public function getPlacementDataOfClg()
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
	
	public function getRanktDataOfClg()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $collegeId = isset($data->collegeId)?$data->collegeId:'';
            $result = $this->College_model->getRanktDataOfClg($collegeId);
            $result2 = $this->getCommonalyAskedQ($collegeId,$type = 'RANKING');
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
	
	public function getCoursesAndFeesOfClg() {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $collegeId = isset($data->collegeId)?$data->collegeId:'';
            $result = $this->College_model->getCoursesAndFeesOfClg($collegeId);
            foreach($result as $key => $value) {
                $result[$key]->eligibility = json_decode($result[$key]->eligibility);

            }
            $result2 = $this->getCommonalyAskedQ($collegeId,$type = 'Fees');

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
	
	public function getCollegeProgrammesByID()
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
            $collegeId = isset($data->collegeId)?$data->collegeId:'';
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
	
	public function getcollegeByLocation() {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $collegeId = isset($data->collegeId)?$data->collegeId:'';
            $cityid = isset($data->cityid)?$data->cityid:'';
            $result = $this->College_model->collegeByLocation($cityid,$collegeId);
            
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
            $collegeId = isset($data->collegeId)?$data->collegeId:'';
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
	
	public function getCollegeAdmissionProcess() 
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $collegeId = isset($data->collegeId)?$data->collegeId:'';
            $result = $this->College_model->getCollegeAdmissionProcess($collegeId);
            $EXAMs = array();
            foreach($result as $key => $value) {
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
                for($i=0; $i<count($result[$key]->entrance_exams); $i++)
                {
                    $exams = $this->College_model->getExamsNotification($result[$key]->entrance_exams[$i]);
                    if (!empty($exams)) {
                        $EXAMs[] = array(
                            'Examnotification_or_ImportantDates' => $exams[0]['notification'] 
                        );
                        $result[$key]->Examnotification_or_ImportantDates = $exams[0]['notification'];

                    }

                }

            }
            $result2 = $this->getCommonalyAskedQ($collegeId,$type = 'Admissions');

            if ($result || $result2) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["AdmissionProcess"] = $result;
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
	
	public function getScholarShipOfClg()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            
            $collegeId = isset($data->collegeId)?$data->collegeId:'';
            $result = $this->College_model->getScholarShipOfClg($collegeId);
            //print_r($result[0]['scholarship'] );exit;
            $result2 = $this->getCommonalyAskedQ($collegeId,$type = 'SCHOLARSHIP');

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
            $cityid = isset($data->cityid)?$data->cityid:'';
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
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $collegeId = $data->collegeId;
            $categories = $data->categories;
            $result = $this->College_model->getCollegesAccordingCategory($collegeId,$categories);
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
            $CurrentYear = date('Y') ;
            $collegeId = isset($data->collegeId)?$data->collegeId:'';
            //print_r($CurrentYear);exit;
            $result = $this->College_model->getLastThreeYearsPlacementData($CurrentYear,$collegeId);
            $result2 = $this->getCommonalyAskedQ($collegeId,$type = 'PLACEMENT');

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
}
