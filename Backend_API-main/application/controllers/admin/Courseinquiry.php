<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Methods: GET, POST');
date_default_timezone_set("Asia/Kolkata");

/**
 * Course inquiry Controller
 *
 * @category   Controllers
 * @package    Admin
 * @subpackage Course inquiry
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    22 JAN 2024
 *
 * Class Course inquiry handles all the operations related to displaying list, creating Course inquiry, update, and delete.
 */

if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}

/**
* Note: This file may contain artifacts of previous malicious infection.
* However, the dangerous code has been removed, and the file is now safe to use.
*/


class Courseinquiry extends CI_Controller
{
    /**
     * Constructor
     *
     * Loads necessary libraries, helpers, and models for the Course inquiry controller.
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
    $this->load->model('admin/Courseinquiry_model');
    $this->load->library('Utility');
  }

  public function getcourseInquiry()
  {
    $data = json_decode(file_get_contents('php://input'));

    if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
      $data->status = 'ok';
      echo json_encode($data);
      exit;
    }
    if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
      $response["response_code"] = "401";
      $response["response_message"] = "Unauthorized";
      echo json_encode($response);
      exit();
    }
		$headers = apache_request_headers();
		$token = str_replace("Bearer ", "", $headers['Authorization']);
		$kunci = $this->config->item('jwt_key');
		$userData = JWT::decode($token, $kunci);
		Utility::validateSession($userData->iat, $userData->exp);
		$tokenSession = Utility::tokenSession($userData);

    if ($data) {

      $columns = array(
        0 => 'id',
        1 => 'create_date',
        2 => 'email',
        3 => 'phone',
        4 => 'category',
        5 => 'coursename',
        6 => 'interested',
        7 => 'is_read',
        8 => 'name'
      );

      $limit = $data->length;
      $start = ($data->draw - 1) * $limit;
      $orderColumn = $columns[$data->order[0]->column];
      $orderDir = $data->order[0]->dir;
      $totalData = $this->Courseinquiry_model->countAllCourseinquiry();
      $totalFiltered = $totalData;


      if (!empty($data->search->value)) {
        $search = $data->search->value;
        $totalFiltered = $this->Courseinquiry_model->countFilteredCourseinquiry($search);
        $Course = $this->Courseinquiry_model->getFilteredCourseinquiry($search, $start, $limit, $orderColumn, $orderDir);
      } else {
        $Course = $this->Courseinquiry_model->getAllCourseinquiry($start, $limit, $orderColumn, $orderDir);
      }

      $datas = array();
      foreach ($Course as $e) {

        $nestedData = array();
        $nestedData['id'] = $e->id;
        $nestedData['name'] = $e->name;
        $nestedData['email'] = $e->email;
        $nestedData['phone'] = $e->phone;
        $nestedData['categoryName'] = $e->category;
        $nestedData['course'] = $e->coursename;
        $nestedData['interested'] = $e->interested;
        $nestedData['is_read'] = $e->is_read;
        $nestedData['create_date'] = $e->create_date;
        $nestedData['attended_by'] = $e->attended_by;
        $nestedData['attended_date'] = $e->attended_date;
        $nestedData['attended_by_name'] = $e->attended_by_name;
        $nestedData['category'] = $e->categoryName;
        $nestedData['coursename'] = $e->course;
				$nestedData['is_attended_value'] = ($e->is_attended == 1) ? 'Attended' : 'Pending';
				$nestedData['is_attended'] = $e->is_attended;
        $nestedData['cityname'] = $e->cityname;
				$nestedData['statename'] = $e->statename;
        $datas[] = $nestedData;
      }

      $json_data = array(
        'draw' => intval($data->draw),
        'recordsTotal' => intval($totalData),
        'recordsFiltered' => intval($totalFiltered),
        'data' => $datas
      );

      echo json_encode($json_data);
    } else {
      $response["response_code"] = "500";
      $response["response_message"] = "Data is null";
      echo json_encode($response);
      exit();
    }
  }




  public function course_inquiryDelete()
  {
    $data = json_decode(file_get_contents('php://input'));

    if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
      $data["status"] = "ok";
      echo json_encode($data);
      exit;
    }
    if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
      $response["response_code"] = "401";
      $response["response_message"] = "Unauthorized";
      echo json_encode($response);
      exit();
    }
    if ($data) {

      $headers = apache_request_headers();
      $token = str_replace("Bearer ", "", $headers['Authorization']);
      $kunci = $this->config->item('jwt_key');
      $userData = JWT::decode($token, $kunci);
      Utility::validateSession($userData->iat, $userData->exp);
      $tokenSession = Utility::tokenSession($userData);

      $id = $data->id;
      $UserRole = $userData->data->type;
      $userId = $userData->data->userId;
      if($UserRole=='Employee')
      {
        $response["response_code"] = 300;
        $response["response_message"] = "Sorry, you do not have permission to modify courses enquiry.";
        echo json_encode($response);
            exit();
      }
      $result = $this->Courseinquiry_model->dataDelete($id);

      if ($result) {
        $response['response_code'] = "200";
        $response['response_message'] = "Success";
        $response['response_data'] = $result;
      } else {
        $response['response_code'] = "400";
        $response['response_message'] = "Failed";
      }
    } else {
      $response["response_code"] = "500";
      $response["response_message"] = "Data is null";
    }
    echo json_encode($response);
    exit;
  }

  function SendCourseEnquiryResponse()
  {

  $data = json_decode(file_get_contents('php://input'));
  if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
    $data['status'] = 'ok';
    echo json_encode($data);
    exit;
  }
  if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $response["response_code"] = "401";
    $response["response_message"] = "Unauthorized";
    echo json_encode($response);
    exit();
  }
  if ($data) {
    $headers = apache_request_headers();
    $token = str_replace("Bearer ", "", $headers['Authorization']);
    $kunci = $this->config->item('jwt_key');
    $userData = JWT::decode($token, $kunci);
    Utility::validateSession($userData->iat, $userData->exp);
    $tokenSession = Utility::tokenSession($userData);
      $senderName = "OhCampus Team";
      $bccArray = "";
      $from = 'enquiry@ohcampus.com';
      $to = $data->email;
      $name = $data->name;
      $course_cat = $data->course_cat;
      $course = $data->course;
      $intrested = $data->intrested;
      $response = $data->response;
      $emailMessage = '<!DOCTYPE html>
      <html lang="en">
      <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Email Template</title>
      </head>
      <body style="font-family: Arial, sans-serif; background-color: #f5f5f5; padding: 20px;">
      
      <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
        <div style="text-align: center; margin-bottom: 20px;">
          <img src="https://ohcampus.com/assets/images/logo/logo.png" alt="Company Logo" style="max-width: 200px;">
        </div>
        <div style="padding: 20px; background-color: #f0f0f0; border-radius: 5px;">
          <h2 style="color: #2196f3; margin-bottom: 10px;">Response to Your Course Enquiry</h2>
          <p style="color: #666666; font-size: 16px; line-height: 1.6;">Dear '.$name.',</p>
          <p style="color: #666666; font-size: 16px; line-height: 1.6;">Thank you for contacting us regarding your enquiry. Below is the response to your enquiry:</p>
          <div style="background-color: #ffffff; border-radius: 5px; padding: 15px; margin-bottom: 20px;">
            <p style="color: #666666; font-size: 16px; line-height: 1.6;"><strong>Your Enquiry Details:</strong></p>
            <p style="color: #666666; font-size: 16px; line-height: 1.6;">Name : '.$name.'</p>
            <p style="color: #666666; font-size: 16px; line-height: 1.6;">Course Category : '.$course_cat.'</p>
            <p style="color: #666666; font-size: 16px; line-height: 1.6;">Course : '.$course.'</p>
            <p style="color: #666666; font-size: 16px; line-height: 1.6;">Intrested In : '.$intrested.'</p>

          </div>
          <div style="background-color: #ffffff; border-radius: 5px; padding: 15px;">
            <p style="color: #666666; font-size: 16px; line-height: 1.6;"><strong>Our Response:</strong></p>
            <p style="color: #666666; font-size: 16px; line-height: 1.6;">'.$response.' </p>
          </div>
          <p style="color: #666666; font-size: 16px; line-height: 1.6;">If you have any further questions or need immediate assistance, please feel free to contact us.</p>
          <p style="color: #666666; font-size: 16px; line-height: 1.6;">Best regards,<br>OhCampus Team<br>OhCampus.com<br><img src="https://ohcampus.com/assets/images/logo/logo.png" alt="Company Logo" style="max-width: 100px;">
          </p>
        </div>
      </div>
      
      </body>
      </html>
      ';
      $subject = 'Response to your Course Enquiry - OhCampus';
      $url = "https://api.sendinblue.com/v3/smtp/email";

      $headers = [
        "api-key: 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",
        "Content-Type: application/json",
      ];
      $custJsonData = [
        "sender" => ["name" => $senderName, "email" => $from],
        "to" => [["name" =>  $name, "email" => $to]],
        "subject" => $subject,
        "htmlContent" => $emailMessage,
      ];
      $curl = curl_init();

      curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($custJsonData),
        CURLOPT_HTTPHEADER => $headers,
      ]);
      $response = curl_exec($curl);
      curl_close($curl);
      $res = json_decode($response);
      if($res)
      {
        $attended_by = $userData->data->userId;
        $enquiryId = $data->enquiryId;
        $Arr = ['is_attended'=>1,'attended_by'=>$attended_by];
        $updateData = $this->Courseinquiry_model->updateData($enquiryId,$Arr);
        $response1["response_code"] = "200";
        $response1["response_message"] = "Response sent successfully.";
      }
      else
      {
        $response1["response_code"] = "400";
        $response1["response_message"] = "Having problem sending response. Please try again.";

      }
    } else {
      $response1["response_code"] = "500";
      $response1["response_message"] = "Data is null";
    }
    echo json_encode($response1);exit;
  }
}
