<?php
if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}
class exam extends CI_Controller
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
		$this->load->model("apps/Exam_model", "", true);
        $this->load->library('Utility');
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

    public function getCareerByCategory()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $courseCatId = $data->courseCatId;
            $catName = $this->Campus_app_model->getCatName($courseCatId);
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
            $courseCatId = $data->courseCatId;
            $catName = $this->Campus_app_model->getCatName($courseCatId);
			if($catName =='Science'){$catName = "Arts & science";}
            $examId = $this->Campus_app_model->getExamCatId($catName);
            //echo $catName;exit;
            $result = $this->Exam_model->getExamsByCategory($examId);
			foreach($result as &$exam){
				$exam['questionpaper'] = base_url() . "/uploads/questionpaper/" . $exam['questionpaper'];
                $exam['preparation'] = base_url() . "/uploads/preparation/" . $exam['preparation'];
                $exam['syllabus'] = base_url() . "/uploads/syllabus/" . $exam['syllabus'];
			}
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

    public function getExamNotificationForClg()
    {
        $data = json_decode(file_get_contents('php://input'));

        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data["status"] = "ok";
            echo json_encode($data);
            exit;
        }
        if ($data) {
            $collegeid = $data->collegeId;
            $result = $this->Exam_model->getExamNotificationForClg($collegeid);
            foreach ($result as $key => $img) {
                $result[$key]->imageName = $img->image;

                $result[$key]->image = base_url() . '/uploads/blogs/' . $img->image;
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
	
	public function getExamAccepted()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if($data){
        $collegeId = $data->collegeId;
        $SubCategory = $this->Exam_model->getExamAccepted($collegeId);

        if ($SubCategory) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["SubCategory"] = $SubCategory;
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed";
        }
    }
    else
    {
        $response["response_code"] = "500";
         $response["response_message"] = "Data is null";
    }
        echo json_encode($response);
        exit;
    }
}
