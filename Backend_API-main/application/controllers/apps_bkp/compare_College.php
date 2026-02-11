<?php
defined('BASEPATH') or exit('No direct script access allowed');
class compare_College extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
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
}
