<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST");
defined('BASEPATH') or exit('No direct script access allowed');
class Review extends CI_Controller
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
        $this->load->model('apps/Review_model');
        $this->load->model('admin/common_model');
        $this->load->model("web/User_model", "", true);
        $this->load->model('apps/College_model');
        $this->load->model('apps/Common_model');
    }

    public function getReviewDetails()
    {
        $data = json_decode(file_get_contents('php://input'));
        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data['status'] = 'ok';
            echo json_encode($data);
            exit;
        }
        if ($data) {
            $collegeid = $data->collegeId;
            $Reviews = $this->Review_model->getReviewDetails($collegeid);

            if ($Reviews) {
                $response['response_code'] = '200';
                $response['response_message'] = 'Success';
                $response['data'] = $Reviews;
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

    public function voteReview()
    {
        $data = json_decode(file_get_contents('php://input'));
        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data['status'] = 'ok';
            echo json_encode($data);
            exit;
        }
        if ($data) {
            $userid = $data->user_id;
            $reviewid = $data->reviewid;
            $ishelpful = $data->ishelpful;
            $getRevById = $this->Review_model->getRevById($reviewid);
            
            if(!empty($getRevById)){
            $userArr = explode(",", $getRevById->voted_users);

            $userArr[] = $userid; // Add the latest user ID to the existing array
            $userArr = array_filter(array_unique($userArr));
            $voteReview = $this->Review_model->voteReview($reviewid, $userArr, $ishelpful);
            }
            $userArr[] = $userid;
            $voteReview = $this->Review_model->insertvoteReview($userArr, $ishelpful);
            if ($voteReview) {
                $response['response_code'] = '200';
                $response['response_message'] = 'Success';
                $response['data'] = $voteReview;
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

    public function addReview()
    {
        $data = json_decode(file_get_contents('php://input'));
        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data['status'] = 'ok';
            echo json_encode($data);
            exit;
        }
        if ($data) {
            $userid = $data->user_id;
            $collegeid = $data->collegeid;
            $courtype = $data->courtype;
            $courseid = $data->courseid;
            $title = $data->title;
            $getUserDetails = $this->User_model->getUserDetailsById($userid);

            $data = [
                'college_id' => $collegeid,
                'course_id' => $courseid,
                'course_type' => $courtype,
                'title' => $title,
                'placement_rate' => $data->placement_rate,
                'placement_desc' => $data->placement_description,
                'infrastructure_rate' => $data->infrastructure_rate,
                'infrastructure_desc' => $data->infrastructure_description,
                'faculty_rate' => $data->faculty_rate,
                'faculty_desc' => $data->faculty_description,
                'hostel_rate' => $data->hostel_rate,
                'hostel_desc' => $data->hostel_description,
                'campus_rate' => $data->campus_rate,
                'campus_desc' => $data->campus_description,
                'money_rate' => $data->money_rate,
                'money_desc' => $data->money_description,
            ];

            $voteReview = $this->Review_model->addReview($data);

            if ($voteReview) {
                $userid = $getUserDetails[0]->id;
                $email = $getUserDetails[0]->email;
                $fname = $getUserDetails[0]->f_name;
                $lname = $getUserDetails[0]->l_name;
                $name = $fname . " " . $lname;
                $clgDtl = $this->College_model->getCollegeDetailsByID($collegeid);
                $Arr = ['user_name' => $userid, 'email' => $email, 'college' => $clgDtl[0]['id'], 'location' => $clgDtl[0]['city'], 'latest_activity' => 'Review Added'];

                // $Arr = ['user_name'=>$name,'email'=>$email,'location'=>'','latest_activity'=>''.$clgDtl[0]['title'].','.$clgDtl[0]['city'].' Review Added.'];
                $addUserActivity = $this->Common_model->addUserActivity($Arr);
                $ClgRepArr = ['college' => $collegeid, 'no_of_articles_linked' => 0, 'no_of_brochures_download' => 0, 'no_of_application_submitted' => 0, 'no_of_que_asked' => 0, 'no_of_answeres' => 0, 'no_of_review' => 1];
                $checkcollegeReport = $this->common_model->checkcollegeReport($collegeid);
                if ($checkcollegeReport > 0) {
                    $updateClgReport = $this->common_model->updateClgReport($collegeid, $ClgRepArr);
                } else {
                    $saveClgReport = $this->common_model->saveClgReport($ClgRepArr);
                }
                $logArr = ['review_id' => $voteReview];
                $tableName = 'review_log ';
                $addLog = $this->Common_model->addLog($logArr, $tableName);
                $response['response_code'] = '200';
                $response['response_message'] = 'Success';
                $response['data'] = $voteReview;
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

    public function getPlacementRating()
    {
        $data = json_decode(file_get_contents('php://input'));
        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data['status'] = 'ok';
            echo json_encode($data);
            exit;
        }
        if ($data) {
            $collegeid = $data->collegeId;
            $Reviews = $this->Review_model->getPlacementRating($collegeid);

            if ($Reviews) {
                $response['response_code'] = '200';
                $response['response_message'] = 'Success';
                $response['data'] = $Reviews;
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

    public function getInfrastructureRating()
    {
		//echo "testing...";exit;
        $data = json_decode(file_get_contents('php://input'));
        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data['status'] = 'ok';
            echo json_encode($data);
            exit;
        }
        if ($data) {
            $collegeid = $data->collegeId;
            $Reviews = $this->Review_model->getInfrastructureRating($collegeid);

            if ($Reviews) {
                $response['response_code'] = '200';
                $response['response_message'] = 'Success';
                $response['data'] = $Reviews;
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


    public function getRatingList()
    {
        $data = json_decode(file_get_contents('php://input'));
        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data['status'] = 'ok';
            echo json_encode($data);
            exit;
        }
        // if($data)
        // {
        $Rating = $this->Review_model->getRatingList();

        if ($Rating) {
            $response['response_code'] = '200';
            $response['response_message'] = 'Success';
            $response['Rating'] = $Rating;
        } else {
            $response['response_code'] = '400';
            $response['response_message'] = 'Failed';
        }
        // }
        // else
        // {
        //     $response['response_code'] = '500';
        //     $response['response_message'] = 'Data is null.';
        // }

        echo json_encode($response);
    }
}
