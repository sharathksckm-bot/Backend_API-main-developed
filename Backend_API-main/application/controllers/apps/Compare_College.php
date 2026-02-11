<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST");
defined('BASEPATH') or exit('No direct script access allowed');
class Compare_College extends CI_Controller
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
        $this->load->model('apps/Comparecollege_model');
		$this->load->model('apps/Review_model');
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
	
	/*****GET COURSES BY COLLEGE ID ******/
    public function getDegreeByCollegeId()
    {
        $data = json_decode(file_get_contents('php://input'));

        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data["status"] = "ok";
            echo json_encode($data);
            exit;
        }
        if ($data) {
            $ClgId = $data->collegeId;
            $Courses = $this->Comparecollege_model->getDegreeByCollegeId($ClgId);
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
	
	/*****GET COURSES BY COLLEGE ID ******/
    public function getCoursesByCollegeId()
    {
        $data = json_decode(file_get_contents('php://input'));

        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data["status"] = "ok";
            echo json_encode($data);
            exit;
        }
        if ($data) {
            $ClgId = $data->collegeId;
			$degId = $data->degreeId;
            $Courses = $this->Comparecollege_model->getCoursesByCollegeId($ClgId,$degId);
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
	
	public function getPopularCompOfBTech()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }

        $result = $this->Comparecollege_model->getPopularCompOfBTech();
        $CompResult = [];
        $compCounter = 1;
		foreach ($result as &$ci) {
			$ci["image"] = base_url() . "/uploads/college/" . $ci['image'];
			$ci["logo"] = base_url() . "/uploads/college/" . $ci['logo'];
			$review = $this->Review_model->countCollegeReviews($ci['id']);
			$ci['reviews'] = $review;
			$TotalRate = $this->Review_model->getCollegeTotalRate($ci['id']);
			$RateCount = $TotalRate['totalRateCount'];
			$ci['rating'] = $RateCount;
			$ci['branch'] = 'B.Tech in Computer Science and Engineering';
        }
		foreach ($result as $key1 => $value1) {
    		foreach (array_slice($result, $key1 + 1) as $key2 => $value2) {
        	$CompResult[] = array($value1, $value2);
    		}
		}
		
        /*foreach ($result as $key => $value) {
            $CompResult[$compCounter][] = $value;
            if ($compCounter == 5) {
                $compCounter = 1;
            } else {
                $compCounter++;
            }
        }*/
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
	
	public function getPopularCompOfMBA()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }

        $result = $this->Comparecollege_model->getPopularCompOfMBA();
        $CompResult = [];
        $compCounter = 1;
		foreach ($result as &$ci) {
			$ci["image"] = base_url() . "/uploads/college/" . $ci['image'];
			$ci["logo"] = base_url() . "/uploads/college/" . $ci['logo'];
			$TotalRate = $this->Review_model->getCollegeTotalRate($ci['id']);
			$RateCount = $TotalRate['totalRateCount'];
			$ci['rating'] = $RateCount;
			$ci['branch'] = 'Masters in Business Administration (MBA)';
        }
		foreach ($result as $key1 => $value1) {
    		foreach (array_slice($result, $key1 + 1) as $key2 => $value2) {
        	$CompResult[] = array($value1, $value2);
    		}
		}
        /*foreach ($result as $key => $value) {
            $CompResult[$compCounter][] = $value;
            if ($compCounter == 5) {
                $compCounter = 1;
            } else {
                $compCounter++;
            }
        }*/
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
	
	
	
	    public function getCollegeDetailsByID1()
    {
		//	echo "testing...";exit;
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
			
			//print_r($data);exit;
        if ($data) {
          //  $id = $data->id;
			$id = $data->collegeId;
			//$courselevel = $data->courselevel;
			//$subcategory = $data->subcategory;
            $ClgList = $this->Comparecollege_model->getCollegeDetailsByID($id);
          //  print_r($ClgList);exit;
            $ClgHighlight = $this->Comparecollege_model->getCollegeHighlightByID($id);
            $clgCourses = $this->Comparecollege_model->getCollegeCoursesByID($id);
            $clgReviewRate = $this->Comparecollege_model->getReviewRatingByClgId($id);
            $clgAcademic_data = $this->Comparecollege_model->getAcademicDataByClgId($id);
          //  print_r($ClgList);exit;
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
                $nestedData["Collage_category"] = $clg['name'];
                $nestedData["CollegeHighlight"] = $ClgHighlight;
                $nestedData["Courses_Count"] = $clgCourses;
                $nestedData["Rank"] = $RankList;
                $nestedData["ReviewRating"] = $clgReviewRate;
                $nestedData["Academic_Date"] = $clgAcademic_data;

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
	
	/*	    public function getCollegeDetailsByID()
    {
			//echo "testing...";exit;
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
			
			//print_r($data);exit;
        if ($data) {
          //  $id = $data->id;
			$id = $data->collegeId;
            $ClgList = $this->Comparecollege_model->getCollegeDetailsByID($id);
            $ClgHighlight = $this->Comparecollege_model->getCollegeHighlightByID($id);
            $clgCourses = $this->Comparecollege_model->getCollegeCoursesByID($id);
            $clgReviewRate = $this->Comparecollege_model->getReviewRatingByClgId($id);
            $clgAcademic_data = $this->Comparecollege_model->getAcademicDataByClgId($id);
          //  print_r($ClgList);exit;
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
                $nestedData["Collage_category"] = $clg['name'];
                $nestedData["CollegeHighlight"] = $ClgHighlight;
                $nestedData["Courses_Count"] = $clgCourses;
                $nestedData["Rank"] = $RankList;
                $nestedData["ReviewRating"] = $clgReviewRate;
                $nestedData["Academic_Date"] = $clgAcademic_data;

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
    }*/
	
	/*public function getCollegeDetailsByID()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $id = $data->collegeId;
			$courselevel = $data->courselevel;
			$subcategory = $data->subcategory;
            $ClgList = $this->Comparecollege_model->getCollegeDetailsByID($id);
            $ClgHighlight = $this->Comparecollege_model->getCollegeHighlightByID($id);
            $clgCourses = $this->Comparecollege_model->getCollegeCoursesByID($id);
            $clgReviewRate = $this->Comparecollege_model->getReviewRatingByClgId($id);
            $clgAcademic_data = $this->Comparecollege_model->getAcademicDataByClgId($id);
            //print_r($ClgList);exit;
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
                $nestedData["Collage_category"] = $clg['name'];
                $nestedData["CollegeHighlight"] = $ClgHighlight;
                $nestedData["Courses_Count"] = $clgCourses;
                $nestedData["Rank"] = $RankList;
                $nestedData["ReviewRating"] = $clgReviewRate;
                $nestedData["Academic_Date"] = $clgAcademic_data;

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
    }*/
	
	 public function getCollegeDetailsByID()
    {
		// echo "testing...";exit;
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
		//print_r($data);exit;
		 
        if ($data) {
            $id = $data->collegeId;
            	
            $courselevel = isset($data->courselevel)?$data->courselevel:'';
            
            $subcategory = isset($data->subcategory)?$data->subcategory:'';
          
            $courseList = $this->Comparecollege_model->getCourses($id,$courselevel,$subcategory);
       
            $ClgList = $this->Comparecollege_model->getCollegeDetails_ByID($id);
           // print_r($ClgList);exit;
            // $ClgHighlight = $this->Comparecollege_model->getCollegeHighlightByID($id);
            $clgCourses = $this->Comparecollege_model->getCollegeCoursesBy_ID($id);
            $clgReviewRate = $this->Review_model->getCollegeTotalRate($id);
			
            $clgAcademic_data = $this->Comparecollege_model->getAcademicDataByClg_Id($id);
            // print_r($coursesandfees);exit; 
            $coursesandfees = $this->Comparecollege_model->getCoursesAndFeesOfClg($id,$courseList[0]['level'],$subcategory);
		
           $adminssionprocess = $this->Comparecollege_model->getCollegeAdmissionProcess($id,$subcategory);
			//print_r($adminssionprocess);exit;
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
                $rnkList = $this->Comparecollege_model->getRankListByClg_Id($clg['id']);
				//print_r($rnkList);exit;
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
    
    
     public function getCoursesById()
    {
        // echo "ttt";exit;
        $data = json_decode(file_get_contents('php://input'));

        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data["status"] = "ok";
            echo json_encode($data);
            exit;
        }
        if ($data) {
            $level = $data->level;
            $id = $data->Id;
            $subcategory = $data->subcategory;
            if ($level == 'PG')
                $Courses = $this->Comparecollege_model->getPGcourses($id,$subcategory);
            else if ($level == 'UG')
                $Courses = $this->Comparecollege_model->getUGcourses($id,$subcategory);
            else $Courses = 'Please select the Course';
         //   $Course = array();
           // print_r($Course);exit;
           /* foreach ($Courses as $i) {
                $Course[] = $i['name'];
            }*/

            $Course = []; 

if (!empty($Courses) && is_array($Courses)) {
    foreach ($Courses as $i) {
        $Course[] = $i['name'];
    }
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
}


