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
            $totalFiltered = $this->Campus_app_model->countFilteredClgByLoc($loc, $course);
            $ClgList = $this->Campus_app_model->getFilteredClgByLoc($loc, $course);

            if ($ClgList) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["Colleges"] = $ClgList;
                $response["Total_Colleges"] = $totalFiltered;
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
            $totalColleges = $this->Campus_app_model->countFilteredClgByfees($min_fees, $max_fees);
            $ClgList = $this->Campus_app_model->getClgbyFees($min_fees, $max_fees);
            if ($ClgList) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["Colleges"] = $ClgList;
                $response["Total_Colleges"] = $totalColleges;
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

    public function getCollegeListByCourseId()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $course = $data->course;

            $result = $this->Campus_app_model->getCollegeListByCourseId($course);

            foreach ($result as $clg) {

                $clgID = $clg->collegeid;
                //print_r($clg->collegeid);exit;
                $courseCount = $this->Campus_app_model->countClgListByCourseId($course, $clgID);
                $clg->logo = base_url() . "/uploads/college/" . $clg->logo;
                $clg->file = base_url() . "/uploads/brochures/" . $clg->file;
                $clg->courseCount = $courseCount;
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
            echo json_encode($response);exit();
        }

        echo json_encode($response);exit;
    }
}
