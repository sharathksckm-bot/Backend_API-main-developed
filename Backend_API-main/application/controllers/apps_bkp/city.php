<?php
if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}
class city extends CI_Controller
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
    /**
     *  To get list of all city by searching the city name
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
            $text = isset($data->search_term) ? $data->search_term : '';
            $city = $this->Campus_app_model->getCity($text);
            if ($city) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["response_data"] = $city;
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
     *  To get list of city by Ranking the selected course
     */
    public function getCityByCourse()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $course = $data->course;

            $city = $this->Campus_app_model->get_City($course);
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
}
