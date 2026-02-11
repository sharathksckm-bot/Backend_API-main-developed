<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Scholarship extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('apps/Scholarship_model');
        $this->load->library('Utility');

    }

    public function getScholarships()
    {
        $data = json_decode(file_get_contents('php://input'));
        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data['status'] = 'ok';
            echo json_encode($data);
            exit;
        }
        if ($data) {
            $search = isset($data->search)?$data->search:'';

            $getScholarships = $this->Scholarship_model->getScholarships($search);

            if ($getScholarships) {
                $response['response_code'] = '200';
                $response['response_message'] = 'Success';
                $response['data'] = $getScholarships;
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
            $result = $this->Scholarship_model->getScholarShipOfClg($collegeId);
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
}