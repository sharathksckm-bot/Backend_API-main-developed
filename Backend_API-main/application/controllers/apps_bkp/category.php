<?php
if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}
class category extends CI_Controller
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
		$this->load->model("apps/Common_model", "", true);
        $this->load->library('Utility');
    }
    public function getCategory()
    {
        $data = json_decode(file_get_contents('php://input'));

        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data->status = 'ok';
            echo json_encode($data);
            exit;
        }

        $categories = $this->Campus_app_model->getCategory();

        foreach ($categories as &$category) {
            $courseCount = $this->Campus_app_model->getCourseCount($category->id);
            $category->Total_Courses = $courseCount;
        }
        if ($categories) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["response_data"] = $categories;
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed";
        }

        echo json_encode($response);
        exit;
    }
    public function getAcadamicCategory()
    {
        $data = json_decode(file_get_contents('php://input'));

        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data->status = 'ok';
            echo json_encode($data);
            exit;
        }
        $categories = $this->Campus_app_model->getAcadamicCategory();
        if ($categories) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["response_data"] = $categories;
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed";
        }

        echo json_encode($response);
        exit;
    }


    public function getPlacementCategory()
    {
        $data = json_decode(file_get_contents('php://input'));

        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data->status = 'ok';
            echo json_encode($data);
            exit;
        }
        $categories = $this->Campus_app_model->getPlacementCategory();
        if ($categories) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["response_data"] = $categories;
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed";
        }

        echo json_encode($response);
        exit;
    }

    public function getSubCategoryList()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $collegeId = $data->collegeId;
            $SubCategory = $this->Common_model->getSubCategoryList($collegeId);

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
}
