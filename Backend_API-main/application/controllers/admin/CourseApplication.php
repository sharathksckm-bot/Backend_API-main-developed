<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Methods: GET, POST');
date_default_timezone_set("Asia/Kolkata");

/**
 * CourseApplication Controller
 *
 * @category   Controllers
 * @package    Admin
 * @subpackage CourseApplication
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    07 FEB 2024
 *
 * Class CourseApplication handles all the operations related to displaying list, creating CourseApplication, update, and delete.
 */

if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}

/**
* Note: This file may contain artifacts of previous malicious infection.
* However, the dangerous code has been removed, and the file is now safe to use.
*/

class CourseApplication extends CI_Controller
{
    /**
     * Constructor
     *
     * Loads necessary libraries, helpers, and models for the CourseApplication controller.
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
        $this->load->model("admin/CourseApplication_model","", true);
        $this->load->model("admin/PredictAdmission_model","", true);
        $this->load->library('Utility');
         $this->load->library('mpdf_lib');
        $config = [
            'tempDir' => APPPATH . 'tmp' // Or use FCPATH.'tmp' if you want outside of application folder
        ];
        $this->mpdf = new \Mpdf\Mpdf($config);
        //$this->load->library('m_pdf');
    }

    /*** Get list of CourseApplication */
    public function getCourseApplicationList()
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
        if ($data) {
            $headers = apache_request_headers();
            $token = str_replace("Bearer ", "", $headers['Authorization']);
            $kunci = $this->config->item('jwt_key');
            $userData = JWT::decode($token, $kunci);
            Utility::validateSession($userData->iat, $userData->exp);
            $tokenSession = Utility::tokenSession($userData);

            $columns = array(
                0 => 'id',
                1 => 'CreatedDate',
            );
            $limit = $data->length;
            $start = ($data->draw - 1) * $limit;
            $orderColumn = $columns[$data->order[0]->column];
            $orderDir = $data->order[0]->dir;
            $totalData = $this->CourseApplication_model->countAllCourseApplication();
            $totalFiltered = $totalData;

            if (!empty($data->search->value)) {
                $search = $data->search->value;
                $totalFiltered = $this->CourseApplication_model->countFilteredCourseApplication($search);
                $CourseApplication = $this->CourseApplication_model->getFilteredCourseApplication($search, $start, $limit, $orderColumn, $orderDir);
            } else {
                $CourseApplication = $this->CourseApplication_model->getAllCourseApplication($start, $limit, $orderColumn, $orderDir);
            }
			//print_r($CourseApplication);exit;
            $datas = array();
            foreach ($CourseApplication as $cnt) {

                $nestedData = array();
                $nestedData['id'] = $cnt->Id;
                $nestedData['mobile_no'] = $cnt->mobile_no;
                $nestedData['email'] = $cnt->email;
                $nestedData['category'] = $cnt->category;
                $nestedData['student_name'] = $cnt->student_name;
				$nestedData['college'] = $cnt->title;
                $nestedData['course'] = $cnt->courses;
                $nestedData['entrance_exam'] = $cnt->exam;
                $nestedData['rank'] = $cnt->rank;
                $nestedData['score'] = $cnt->score;
                $nestedData['CreatedDate'] = $cnt->CreatedDate;
                $nestedData['attended_date'] = $cnt->attended_date;
                $nestedData['attended_by'] = $cnt->attended_by;
                $nestedData['attended_by_name'] = $cnt->attended_by_name;
				$nestedData['is_attended_value'] = ($cnt->is_attended == 1) ? 'Attended' : 'Pending';
				$nestedData['is_attended'] = $cnt->is_attended;

                $datas[] = $nestedData;
            }

            $pdf = $this->getCourseApplicationPdf($datas, $start);

            if ($pdf) {
                $json_data = array(
                    'draw' => intval($data->draw),
                    'recordsTotal' => intval($totalData),
                    'pdf' => base_url().'/AllPdf/CourseApplicationPdfDocs/'. $pdf,
                    'recordsFiltered' => intval($totalFiltered),
                    'data' => $datas
                );

                echo json_encode($json_data);
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
            echo json_encode($response);
            exit();
        }
    }



    public function getCourseApplicationPdf($datas, $start)
    {
        //print_r($start);exit;

        $content = '<!DOCTYPE html><html lang="en">
		<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Document</title>
		<style>
		table, th, td {
		  border: 1px solid black;
		  border-collapse: collapse;
		}
		
		h2{
		 text-align: center;
		}
		
		</style>
		</head>
		<body>
		<h2>Course Aplication</h2>
		<table "border-collapse: collapse; margin: 25px 0; font-size: 0.9em; min-width: 400px; text-align: center;">
		<thead style="font-weight: bold;">
		<tr>
			<th style="padding: 12px 15px; ">Sr.No</th>
			<th style="padding: 12px 15px; ">Email</th>
			<th style="padding: 12px 15px; ">Phone</th>
			<th style="padding: 12px 15px; ">Entrance Exam</th>
			<th style="padding: 12px 15px; ">College</th>
			<th style="padding: 12px 15px; ">Course</th>
			<th style="padding: 12px 15px; ">Rank</th>
			<th style="padding: 12px 15px; ">Score</th>
		</tr>
		</thead>';
        $id = $start + 1;
        foreach ($datas as $row) {
            $content .= '  <tbody style="border-bottom: 1px solid #dddddd; ">
			<tr>
				<td style="padding: 10px; text-align: center;">' . $id++ . '</td>
				<td style="padding: 10px; text-align: center;">' . $row['email'] . '</td>
				<td style="padding: 10px; text-align: center;">' . $row['mobile_no'] . '</td>
				<td style="padding: 10px; text-align: center;">' . $row['entrance_exam'] . '</td>
				<td style="padding: 10px; text-align: center;">' . $row['college'] . '</td>
				<td style="padding: 10px; text-align: center;">' . $row['course'] . '</td>
				<td style="padding: 10px; text-align: center;">' . $row['rank'] . '</td>
				<td style="padding: 10px; text-align: center;">' . $row['score'] . '</td>
			</tr>
			</tbody>';
        }

        $content .= '</table>
		</body>
		</html>';

        //print_r($content);exit;

        	$this->mpdf->SetHTMLHeader('
				<div class="head-img"><img src="https://ohcampus.com/assets/images/logo/logo.png" alt="#" width="auto" height="45"></div>
		');
        	$this->mpdf->SetHTMLFooter('
                 <p style="font-size: 14px;">OhCampus.com, Comet Career India (R), 2nd Floor, SMG Plaza, MG Road, Chikkamagaluru, Karnataka.</p>
			');

        //$this->m_pdf->pdf->AddPage("", "", "", "", "", 18, 18, 20, 20, 5, 1);


        //echo "testing...";exit;

        //$this->m_pdf->pdf->SetWatermarkImage('uploads/OhCampusLogo.jpg', 0.2, 'P', array(0, 40));
       // $this->m_pdf->pdf->watermarkImgBehind = true;
       // $this->m_pdf->pdf->showWatermarkImage = true;

        //$path = dirname(dirname(dirname(__DIR__))) . "/EnquiryPdfDoc/";
        $outputDirectory = FCPATH . 'AllPdf/CourseApplicationPdfDocs/';
        if (!is_dir($outputDirectory)) {
            mkdir($outputDirectory, 0777, true);
        }
        //$outputFilePath = $outputDirectory . '1709558012_EnquiryDetails.pdf';
        //echo $path;exit;
        $filename = time() . "_CourseApplicationDetails" . ".pdf";
        $this->mpdf->WriteHTML($content);
        //$this->m_pdf->pdf->AddPage();
       // ob_end_clean();
        if( ob_get_level() > 0){
          ob_end_clean();
        }
        $this->mpdf->Output($outputDirectory . $filename, "F");
        return $filename;
    }

    /*** Get list of CourseApplication */
    public function deleteCourseApplication()
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
              $response["response_message"] = "Sorry, you do not have permission to modify applications.";
              echo json_encode($response);
                  exit();
            }
            $this->CourseApplication_model->deleteApplication($id);
            //echo $result;exit;
            if (true) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["response_data"] = "Course application deleted";
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }
        echo json_encode($response);
        exit();
    }

    function SendCourseApplicationResponse()
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
      if(empty($name)){
         $student_name = $this->CourseApplication_model->getName($to);
         $name = $student_name->student_name;
      }else{
        $name = $data->name;
      };
      $college = $data->college;
      $course = $data->course;
      $exams = $data->exams;
      $rank = $data->rank;
      $score = $data->score;
      $response = $data->response;
      $type = isset($data->type) ? $data->type :'';
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
          <h2 style="color: #2196f3; margin-bottom: 10px;">Response to your Course Application Enquiry</h2>
          <p style="color: #666666; font-size: 16px; line-height: 1.6;">Dear '.$name.',</p>
          <p style="color: #666666; font-size: 16px; line-height: 1.6;">Thank you for contacting us regarding your enquiry. Below is the response to your enquiry:</p>
          <div style="background-color: #ffffff; border-radius: 5px; padding: 15px; margin-bottom: 20px;">
            <p style="color: #666666; font-size: 16px; line-height: 1.6;"><strong>Application Details:</strong></p>
            <p style="color: #666666; font-size: 16px; line-height: 1.6;">Name: '.$name.'</p>
            <p style="color: #666666; font-size: 16px; line-height: 1.6;">College Name: '.$college.'</p>
            <p style="color: #666666; font-size: 16px; line-height: 1.6;">Course Name: '.$course.'</p>
            <p style="color: #666666; font-size: 16px; line-height: 1.6;">Exams Attempted: '.$exams.'</p>
            <p style="color: #666666; font-size: 16px; line-height: 1.6;">Rank: '.$rank.'</p>
            <p style="color: #666666; font-size: 16px; line-height: 1.6;">Score: '.$score.'</p>
      
          </div>
          <div style="background-color: #ffffff; border-radius: 5px; padding: 15px;">
            <p style="color: #666666; font-size: 16px; line-height: 1.6;"><strong>Our Response:</strong></p>
            <p style="color: #666666; font-size: 16px; line-height: 1.6;">'.$response.'</p>
          </div>
          <p style="color: #666666; font-size: 16px; line-height: 1.6;">If you have any further questions or need immediate assistance, please feel free to contact us.</p>
          <p style="color: #666666; font-size: 16px; line-height: 1.6;">Best regards,<br>OhCampus Team<br>OhCampus.com<br><img src="https://ohcampus.com/assets/images/logo/logo.png" alt="Company Logo" style="max-width: 100px;">
          </p>
        </div>
      </div>
      </body>
      </html>
      ';
      $subject = 'Response to your Application Enquiry - OhCampus';
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
      //print_r($response);exit;
      curl_close($curl);
      $res = json_decode($response);
      if($res)
      {
        
        $attended_by = $userData->data->userId;
        $enquiryId = $data->enquiryId;
        $Arr = ['is_attended'=>1,'attended_by'=>$attended_by];
        if(strtoupper($type) ==  'APPLICATION')
        {
            $updateData = $this->CourseApplication_model->updateData($enquiryId,$Arr);
        }
        else
        {
            $updateData = $this->PredictAdmission_model->updateData($enquiryId,$Arr);

        }
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

