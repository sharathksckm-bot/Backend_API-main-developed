<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Methods: GET, POST');
defined('BASEPATH') or exit('No direct script access allowed');
class CompareCollege extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('web/Comparecollege_model');
       $this->load->model('web/Review_model');
    }

    public function getStateList()
    {
        $data = json_decode(file_get_contents('php://input'));


        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data["status"] = "ok";
            echo json_encode($data);
            exit;
        }


        $states = $this->Comparecollege_model->getState();
        if ($states) {
            $response['response_code'] = '1';
            $response['response_message'] = 'Success';
            $response['data'] = $states;
        } else {
            $response['response_code'] = '2';
            $response['response_message'] = 'Failed';
        }

        echo json_encode($response);
    }

    public function getCityList()
    {

        $data = $this->input->post();

        $data = json_decode(file_get_contents('php://input'));
        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data['status'] = 'ok';
            echo json_encode($data);
            exit;
        }


        if (isset($data->stateId)) {
            $stateId = $data->stateId;

            $cities = $this->Comparecollege_model->getCityByState($stateId);

            if ($cities) {
                $response['response_code'] = '1';
                $response['response_message'] = 'Success';
                $response['data'] = $cities;
            } else {
                $response['response_code'] = '2';
                $response['response_message'] = 'Failed';
            }

            echo json_encode($response);
        }
    }

    public function getccList()
    {

        $data = json_decode(file_get_contents('php://input'));
        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data['status'] = 'ok';
            echo json_encode($data);
            exit;
        }


        $categories = $this->Comparecollege_model->getcc();

        if ($categories) {
            $response['response_code'] = '1';
            $response['response_message'] = 'Success';
            $response['data'] = $categories;
        } else {
            $response['response_code'] = '2';
            $response['response_message'] = 'Failed';
        }

        echo json_encode($response);
    }


    public function getCourseList()
    {

        $data = json_decode(file_get_contents('php://input'));
        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data['status'] = 'ok';
            echo json_encode($data);
            exit;
        }

        $cat = $data->catid;
        $courses = $this->Comparecollege_model->getcourse($cat);

        if ($courses) {
            $response['response_code'] = '1';
            $response['response_message'] = 'Success';
            $response['data'] = $courses;
        } else {
            $response['response_code'] = '2';
            $response['response_message'] = 'Failed';
        }

        echo json_encode($response);
    }

    /*****GET COLLEGE LIST BY SEARCH THE COLLEGE NAME ******/
    public function getCollegeList()
    {
        $data = json_decode(file_get_contents('php://input'));

        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data["status"] = "ok";
            echo json_encode($data);
            exit;
        }
        if ($data) {
            $searchTerm =  $data->searchTerm;
            $start = $data->start;
            $limit = $data->limit;
            $colleges = $this->Comparecollege_model->getAllClg($searchTerm, $start, $limit);

            if ($colleges) {
                $response['response_code'] = '1';
                $response['response_message'] = 'Success';
                $response['data'] = $colleges;
            } else {
                $response['response_code'] = '2';
                $response['response_message'] = 'Failed';
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
            echo json_encode($response);
            exit();
        }

        echo json_encode($response);
    }

    /*****GET LEVEL BY COLLEGE ID ******/
    public function getLevelById()
    {
        $data = json_decode(file_get_contents('php://input'));

        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data["status"] = "ok";
            echo json_encode($data);
            exit;
        }
        if ($data) {
            $id = $data->Id;
            $level = $this->Comparecollege_model->getlevel($id);

            $levels = array();
            foreach ($level as $i) {
                $levels[] = $i['level'];
            }

            if ($levels) {
                $response['response_code'] = '1';
                $response['response_message'] = 'Success';
                $response['data'] = $levels;
            } else {
                $response['response_code'] = '2';
                $response['response_message'] = 'Failed';
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
            echo json_encode($response);
            exit();
        }

        echo json_encode($response);
    }

    /*****GET COURSES BY COLLEGE ID ******/
    public function getCoursesById()
    {
        $data = json_decode(file_get_contents('php://input'));

        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data["status"] = "ok";
            echo json_encode($data);
            exit;
        }
        if ($data) {
            $level = $data->level;
            $id = $data->Id;
            if ($level == 'PG')
                $Courses = $this->Comparecollege_model->getPGcourses($id);
            else if ($level == 'UG')
                $Courses = $this->Comparecollege_model->getUGcourses($id);
            else $Courses = 'Please select the Course';
            $Course = array();
            foreach ($Courses as $i) {
                $Course[] = $i['name'];
            }
            if ($Courses) {
                $response['response_code'] = '1';
                $response['response_message'] = 'Success';
                $response['data'] = $Courses;
            } else {
                $response['response_code'] = '2';
                $response['response_message'] = 'Failed';
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
            echo json_encode($response);
            exit();
        }

        echo json_encode($response);
    }
    /************GET FEATURED COLLEGES LIST ********************/
    /*public function getFeaturedColleges(){
		$data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
			$result = $this->Comparecollege_model->getFeaturedColleges();
			foreach ($result as $key => $value) {
				if (isset($result[$key]->image)) {
					$result[$key]->image = base_url('uploads/college/') . $result[$key]->image;
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

        echo json_encode($response);exit;
	}


    */ public function getFeaturedColleges()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }

        $result = $this->Comparecollege_model->getFeaturedColleges();
        $CompResult = [];
        $compCounter = 1;

        foreach ($result as $key => $value) {
            //$nameKey = 'Comp' . $compCounter;
            //$CompResult[$nameKey][] = $value;
            $CompResult[$compCounter][] = $value;
            if ($compCounter == 3) {
                $compCounter = 1;
            } else {
                $compCounter++;
            }
        }

        foreach ($CompResult as $compKey => $colleges) {
            foreach ($colleges as $key => $value) {
                if (isset($CompResult[$compKey][$key]->image)) {
                    $CompResult[$compKey][$key]->image = base_url('uploads/college/') . $CompResult[$compKey][$key]->image;
                }
            }
        }

        if ($CompResult) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["data"] = $CompResult;
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed";
        }

        echo json_encode($response);
        exit;
    }

    /*****GET COMPARISATION OF COLLEGES ******/
    public function getComparisation()
    {
        $data = json_decode(file_get_contents('php://input'));

        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data["status"] = "ok";
            echo json_encode($data);
            exit;
        }
        if ($data) {
            $id = $data->Id;
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
            echo json_encode($response);
            exit();
        }

        echo json_encode($response);
    }
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
            $courselevel = isset($data->courselevel)?$data->courselevel:'';
            $subcategory = isset($data->subcategory)?$data->subcategory:'';
            
               $courseList = $this->Comparecollege_model->getCourses($id,$courselevel,$subcategory);

            
       // print_r($courseList);exit;
            $ClgList = $this->Comparecollege_model->getCollegeDetailsByID($id);
            // $ClgHighlight = $this->Comparecollege_model->getCollegeHighlightByID($id);
            //print_r($ClgList);exit;
            $clgCourses = $this->Comparecollege_model->getCollegeCoursesByID($id);
            $clgReviewRate = $this->Review_model->getCollegeTotalRate($id);
            $clgAcademic_data = $this->Comparecollege_model->getAcademicDataByClgId($id);
            if($courseList)
            {
            $coursesandfees = $this->Comparecollege_model->getCoursesAndFeesOfClg($id,$courseList[0]['level'],$subcategory);
            }
            else
            {
                $coursesandfees =[];
            }
            $adminssionprocess = $this->Comparecollege_model->getCollegeAdmissionProcess($id,$subcategory);
            $facilities = $this->Comparecollege_model->getCollegeFacilities($id);
                $facilityArray = explode(',', $facilities[0]->facilities);
                $iconArray = explode(',', $facilities[0]->icons);
                
                // Initialize an empty array to store facilities with their icons
                $facilitiesWithIcons = [];
                
                // Combine facilities with their respective icons
                foreach ($facilityArray as $index => $facility) {
                    $facilitiesWithIcons[] = [
                        'name' => $facility,
                        'icon' => isset($iconArray[$index]) ? $iconArray[$index] : null
                    ];
                }
                
                // Output the result
                // print_r($facilitiesWithIcons);exit;
            $result = [];
            foreach ($ClgList as $clg) {
                $rnkList = $this->Comparecollege_model->getRankListByClgId($clg['id']);
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
                $nestedData["country"] = $clg['country'];
                $nestedData["estd"] = $clg['estd'];
                $nestedData["accreditation"] = $clg['accreditation'];
                $nestedData["package_type"] = $clg['package_type'];
                $nestedData["category"] = $clg['catname'];
                $nestedData["categoryid"] = $clg['categoryid'];
                $nestedData["Collage_category"] = $clg['name'];
                $nestedData["coursesandfees"] = $coursesandfees;
                $nestedData["Courses_Count"] = $clgCourses;
                $nestedData["Courses_list"] = $courseList;
                $nestedData["Rank"] = $RankList;
                $nestedData["ReviewRating"] = $clgReviewRate;
                $nestedData["Academic_Date"] = $clgAcademic_data;
                $nestedData["Adminssionprocess"] = $adminssionprocess;
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
}
