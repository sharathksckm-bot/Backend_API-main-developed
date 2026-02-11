<?php
if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}
class campus_app extends CI_Controller
{
    /**
     * Constructor
     *
     * Loads necessary libraries, helpers, and models for the app controller.
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model("web/Campus_app_model", "", true);
        $this->load->library('Utility');
    }
    public function register()
    {
        $data = json_decode(file_get_contents('php://input'));

        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data->status = 'ok';
            echo json_encode($data);
            exit;
        }
        if ($data) {
            $name = $data->name;
            $email = $data->email;
            $pass = $data->password;

            $result = $this->Campus_app_model->register($name, $email, $pass);

            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["Data"] = $result;
                //$response["Fields"] = $fields;
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
     * To get the navlist data
     */
    public function getNavList()
    {
        $data = json_decode(file_get_contents('php://input'));

        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data->status = 'ok';
            echo json_encode($data);
            exit;
        }
        if ($data) {
            $cat = $data->cat;
            // get category name
            $CatName = $this->Campus_app_model->getCatName($cat);
            //print_r($CatName);exit;
            //get Courses by category id and by this getting exams
            $examCat = $this->Campus_app_model->getExamCat($CatName);
            $exam = $this->Campus_app_model->getExam($examCat);
            //colleges data
            //$popClgInIndia = $this->Campus_app_model->getPopCollege($cat);
            //$topRankClg = $this->Campus_app_model->getCollegeListByRank($cat);
            //$specification = $this->Campus_app_model->getClgSpecification();
            //$all = 'All about ' . $CatName;
            //$colleges = [['subFieldName' => 'Popular Colleges in India', 'subChild' => $popClgInIndia], ['subFieldName' => 'Top Rank Colleges', 'subChild' => $topRankClg], ['subFieldName' => 'Find College by Specification', 'subChild' => $specification], ['subFieldName' => 'All about ' . $CatName, 'subChild' => $all]];
            $colleges = [['subFieldName' => 'Popular Colleges in India', 'path' => 'populerclg'], ['subFieldName' => 'Top Rank Colleges', 'path' => 'toprankclg'], ['subFieldName' => 'Find College by Specification', 'path' => 'specialiclg'], ['subFieldName' => 'All about ' . $CatName, 'path' => 'allabout']];
            //$exams = [['subFieldName' => 'All Courses Exams', 'subChild' => $exam]];
            $exams = [['subFieldName' => 'All Courses Exams']];
            $blog = $this->Campus_app_model->getBlog();
            $faQ = $this->Campus_app_model->getFaQ();
            //$resources = [['subFieldName' => 'All Articles', 'subChild' => $blog], ['subFieldName' => 'Questions and Discussions', 'subChild' => $faQ]];
            $resources = [['subFieldName' => 'All Articles'], ['subFieldName' => 'Questions and Discussions']];
            $about = [['subFieldName' => 'About_Us'], ['subFieldName' => 'Feedback'], ['subFieldName' => 'Privacy']];
            $result = [['fieldName' => $CatName . ' colleges', 'Child' => $colleges], ['fieldName' => 'Exams', 'Child' => $exams], ['fieldName' => $CatName . ' Resources', 'Child' => $resources], ['fieldName' => 'About Oh Campus', 'Child' => $about]];
            $fields = [$CatName . ' colleges' => $CatName . '_colleges', 'Exams' => 'Exams', $CatName . ' Resources' => $CatName . '_Resources', 'About Oh Campus' => 'About_Oh_Campus'];
            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["Data"] = $result;
                $response["Fields"] = $fields;
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
     * To get college data by search the text
     */
    public function getDataBySearch()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $text = $data->text;   //print_r('hello');exit;
            $Colleges = $this->Campus_app_model->getClgBySearch($text);
            //print_r($Colleges);exit;

            $result = [];
            foreach ($Colleges as $clg) {
                $nestedData["id"] = $clg->id;
                $nestedData["title"] = $clg->title;
                $nestedData["address"] = $clg->address;
                $nestedData["description"] = $clg->description;
                $nestedData["college_type"] = $clg->college_type;
                $nestedData["accreditation"] = $clg->accreditation;
                $nestedData["logo"] =  base_url() . "/uploads/college/" . $clg->logo;
                $nestedData["image"] = base_url() . "/uploads/college/" . $clg->image;
                $nestedData["package_type"] = $clg->package_type;
                $nestedData["city"] = $clg->city;
                $nestedData["country"] = $clg->country;
                $nestedData["phone"] = $clg->phone;
                //$nestedData["phone"] = $clg->phone;
                $result[] = $nestedData;
            }
            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["Colleges"] = $result;
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
                $courseCount = $this->Campus_app_model->countCoursesByCourseId($Courseid, $clgID);
                $clg['CourseCount'] = $courseCount; // Add the count to the individual college array
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
     *  To get list of city by Ranking the selected course
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
            $course = $data->course;

            $city = $this->Campus_app_model->get_City();
            foreach ($city as &$c) {
                $cityId = $c->id;
                //echo $cityId;exit;
                $Courseid = $this->Campus_app_model->getcourseParentId($course);
                //print_r($Courseid); exit;
                //$courseid = $Courseid['parent_category'];
				$subCatName = $this->Campus_app_model->getSubCatByCoursesId($course);
                //foreach ($Courseid as &$ci) {
                //    $CourseId = $ci['id'];
                    //echo $CourseId;exit;
                //    $collegeCount = $this->Campus_app_model->getCollegeCount($cityId, $CourseId);
                //}
				//print_r($subCatName); exit;
                //echo $subCatName;exit;
                $collegeCount = $this->Campus_app_model->getCollegeCount($cityId, $Courseid);
                $c->collegeCount = $collegeCount;
            }
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
            //print_r($Courseid);exit;
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
                $ci['coursesCount'] = $courseCount;
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
            $id = $data->id;
            $ClgList = $this->Campus_app_model->getCollegeDetailsByID($id);
            $ClgHighlight = $this->Campus_app_model->getCollegeHighlightByID($id);
            $clgCourses = $this->Campus_app_model->getCollegeCoursesByID($id);
            $tableOfContent = $this->Campus_app_model->getTableOfContent($id);
            $clgImages = $this->Campus_app_model->getCollegeImagesByID($id);
            // $popularProgrammes = $this->College_model->getCollegeProgrammesByID($id);
            foreach ($clgImages as $key => $img) {
                $clgImages[$key]->imageName = $img->image;
                $clgImages[$key]->image = base_url() . '/uploads/college/' . $img->image;
            }

            $result = [];
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
                $nestedData["category"] = $clg['catname'];
                $nestedData["Collage_category"] = $clg['name'];
                $nestedData["what_new"] = $clg['what_new'];
                $nestedData["CollegeHighlight"] = $ClgHighlight;
                $nestedData["Courses"] = $clgCourses;
                $nestedData["Rank"] = $RankList;
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
            $id = $data->id;

            $result = $this->Campus_app_model->getPopColleges($id);

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
            $id = $data->id;

            $result = $this->Campus_app_model->getCollegesListByRank($id);

            foreach ($result as $clg) {
                $clg->logo = base_url() . "/uploads/college/" . $clg->logo;
                $clg->file = base_url() . "/uploads/brochures/" . $clg->file;
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

    public function getListBySpecification()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $ccID = $data->ccId;
            $acID = $data->acId;

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
    }

    /**
     *  To get list of Exam
     */
    public function getExamList()
    {
        // echo "testing...";exit;
        $data = json_decode(file_get_contents('php://input'));

        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data["status"] = "ok";
            echo json_encode($data);
            exit;
        }

        $result = $this->Campus_app_model->get_ExamList();

        //print_r($result);exit;

        foreach ($result as $pdf) { {
                $pdf->questionpaper = base_url() . "/uploads/questionpaper/" . $pdf->questionpaper;
                $pdf->preparation = base_url() . "/uploads/preparation/" . $pdf->preparation;
                $pdf->syllabus = base_url() . "/uploads/syllabus/" . $pdf->syllabus;
                // $pdf->syllabus = base_url() . "/uploads/syllabus/" . $pdf->syllabus;
            }
        }

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
	
	public function getCareerByCategory()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $catId = $data->catId;
			$catName = $this->Campus_app_model->getCatName($catId);
			$careerId = $this->Campus_app_model->getCareerCatId($catName);
			//echo $careerId;exit;
            $result = $this->Campus_app_model->getCareerByCategory($careerId);

            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["careerslist"] = $result;
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
	
	public function getExamsByCategory()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $catId = $data->catId;
			$catName = $this->Campus_app_model->getCatName($catId);
			$examId = $this->Campus_app_model->getExamCatId($catName);
			//echo $examId;exit;
            $result = $this->Campus_app_model->getExamsByCategory($examId);

            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["examslist"] = $result;
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
