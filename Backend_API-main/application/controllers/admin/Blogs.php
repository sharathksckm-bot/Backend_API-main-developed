<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Methods: GET, POST');
if (!defined("BASEPATH")) {
  exit("No direct script access allowed");
}
date_default_timezone_set('Asia/Kolkata');

class Blogs extends CI_Controller
{
  /**
   * Constructor
   *
   * Loads necessary libraries, helpers, and models for the Blogs controller.
   */
  public function __construct()
  {
    parent::__construct();
    $data = json_decode(file_get_contents('php://input'));
		if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
			$data['status'] = 'ok';
			echo json_encode($data);
			exit;
		}
    // $this->load->model("web/Blog_model", "", true);
    $this->load->model("Blog_model");
  }

  public function getBlogs()
  {

    // echo "shubham";
    // exit;

    $data = json_decode(file_get_contents('php://input'));

    if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
      $data["status"] = "ok";
      echo json_encode($data);
      exit;
    }

    $result = $this->Blog_model->get_Blogs();

    // foreach ($result as $key => $value) {
    //   if (isset($result[$key]->image)) {
    //     $result[$key]->image = base_url('uploads/blogs/') . $result[$key]->image;
    //   }
    // }

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
}
