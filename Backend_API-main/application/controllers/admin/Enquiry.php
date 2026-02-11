<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Methods: GET, POST');
date_default_timezone_set("Asia/Kolkata");
/**
 * Enquiry Controller
 *
 * @category   Controllers
 * @package    Admin
 * @subpackage Enquiry
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    07 FEB 2024
 *
 * Class Enquiry handles all the operations related to displaying list, creating Enquiry, update, and delete.
 */

if (!defined("BASEPATH")) {
	exit("No direct script access allowed");
}

/**
 * Note: This file may contain artifacts of previous malicious infection.
 * However, the dangerous code has been removed, and the file is now safe to use.
 */

class Enquiry extends CI_Controller
{
	/**
	 * Constructor
	 *
	 * Loads necessary libraries, helpers, and models for the Enquiry controller.
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
		$this->load->model("admin/Enquiry_model", "", true);
		$this->load->library('Utility');
		// $this->load->library('m_pdf');

		$this->load->library('mpdf_lib');
		$config = [
			'tempDir' => APPPATH . 'tmp' // Or use FCPATH.'tmp' if you want outside of application folder
		];
		$this->mpdf = new \Mpdf\Mpdf($config);
	}
	/*** Get list of Enquiry */
	public function getEnquiryList()
	{
		//print_r("testing...");exit;
		$data = json_decode(file_get_contents('php://input'));

		if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
			$data->status = 'ok';
			echo json_encode($data);
			exit;
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
				1 => 'create_date',

			);
			$limit = $data->length;
			$start = ($data->draw - 1) * $limit;
			$orderColumn = $columns[$data->order[0]->column];
			$orderDir = $data->order[0]->dir;
			$totalData = $this->Enquiry_model->countAllEnquiry();
			$totalFiltered = $totalData;
			if (!empty($data->search->value)) {

				$search = $data->search->value;
				$totalFiltered = $this->Enquiry_model->countFilteredEnquiry($search);
				$Enquiry = $this->Enquiry_model->getFilteredEnquiry($search, $start, $limit, $orderColumn, $orderDir);
			} else {
				$Enquiry = $this->Enquiry_model->getAllEnquiry($start, $limit, $orderColumn, $orderDir);
			}
			$datas = array();
			foreach ($Enquiry as $ln) {

				$nestedData = array();
				$nestedData['id'] = $ln->id;
				$nestedData['name'] = $ln->name;
				$nestedData['email'] = $ln->email;
				$nestedData['phone'] = $ln->phone;
				$nestedData['message'] = $ln->message;
				$nestedData['college'] = $ln->college;
				$nestedData['course'] = $ln->course;
				$nestedData['inquiry_type'] = $ln->type;
				$nestedData['form_type'] = $ln->form_type;
				$nestedData['create_date'] = $ln->create_date;
				$nestedData['is_attended'] = $ln->is_attended;
				$nestedData['is_attended_value'] = ($ln->is_attended == 1) ? 'Attended' : 'Pending';
				$nestedData['attended_date'] = $ln->attended_date;
				$nestedData['attended_by'] = $ln->f_name;
				$nestedData['email'] = $ln->email;
				$nestedData['cityname'] = $ln->cityname;
				$nestedData['statename'] = $ln->statename;
				$datas[] = $nestedData;
			}


			$pdf = $this->enquiryDetailsPdf($datas, $start);
			//echo $pdf;exit;
			if ($pdf) {
				$json_data = array(
					'draw' => intval($data->draw),
					'recordsTotal' => intval($totalData),
					'pdf' => base_url() . '/AllPdf/EnquiryPdfDoc/' . $pdf,
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

	public function deleteEnquiry()
	{
		$data = json_decode(file_get_contents('php://input'));
		if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
			$data['status'] = 'ok';
			echo json_encode($data);
			exit;
		}
		if ($data) {
			$headers = apache_request_headers();
			$token = str_replace("Bearer ", "", $headers['Authorization']);
			$kunci = $this->config->item('jwt_key');
			$userData = JWT::decode($token, $kunci);
			Utility::validateSession($userData->iat, $userData->exp);
			$tokenSession = Utility::tokenSession($userData);

			$Id = $data->id;
			$UserRole = $userData->data->type;
			$userId = $userData->data->userId;
			if ($UserRole == 'Employee') {
				$response["response_code"] = 300;
				$response["response_message"] = "Sorry, you do not have permission to modify enquires.";
				echo json_encode($response);
				exit();
			}
			$result = $this->Enquiry_model->deleteEnquiry($Id);
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
		}
		echo json_encode($response);
		exit();
	}

	public function updateStatus()
	{
		$data = json_decode(file_get_contents('php://input'));
		if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
			$data['status'] = 'ok';
			echo json_encode($data);
			exit;
		}
		if ($data) {
			$headers = apache_request_headers();
			$token = str_replace("Bearer ", "", $headers['Authorization']);
			$kunci = $this->config->item('jwt_key');
			$userData = JWT::decode($token, $kunci);
			Utility::validateSession($userData->iat, $userData->exp);
			$tokenSession = Utility::tokenSession($userData);
			// print_r($userData->data->userId);exit;
			$type = $data->type;
			$Id = $data->id;
			$is_attended = $data->is_attended;
			$attended_by = $userData->data->userId;
			$attended_date = date('Y-m-d H:i:s');

			$arr = ['is_attended' => $is_attended, 'attended_by' => $attended_by, 'attended_date' => $attended_date];
			$result = $this->Enquiry_model->updateStatus($Id, $arr, $type);
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
		}
		echo json_encode($response);
		exit();
	}

	public function enquiryDetailsPdf($datas, $start)
	{
		//print_r($start);exit;

		$content = '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <style>table, th, td {
  border: 1px solid black;
  border-collapse: collapse;
}

h2 {
    text-align: center;
}

</style>
</head>
<body>
<h2>Enquiry Details</h2>
<table "border-collapse: collapse; margin: 25px 0; font-size: 0.9em; min-width: 400px; text-align: center;">
 <thead style="font-weight: bold;">
  <tr>
      <th style="padding: 12px 15px;">Sr.No</th>
      <th style="padding: 12px 15px; ">Email</th>
      <th style="padding: 12px 15px; ">Phone</th>
	  <th style="padding: 12px 15px; ">Message</th>
	  <th style="padding: 12px 15px; ">Enquiry Type</th>
	  <th style="padding: 12px 15px; ">College</th>
	  <th style="padding: 12px 15px; ">Course</th>
	  <th style="padding: 12px 15px; ">Attended By</th>
	  <th style="padding: 12px 15px; ">Attended Date</th>
	  <th style="padding: 12px 15px; ">Status</th>
  </tr>
  </thead>';
		$id = $start + 1;
		foreach ($datas as $row) {
			$content .= '  <tbody style="border-bottom: 1px solid #dddddd; ">
			<tr>
        <td style="padding: 10px; text-align: center">' . $id++ . '</td>
        <td style="padding: 10px; text-align: center">' . $row['email'] . '</td>
        <td style="padding: 10px; text-align: center">' . $row['phone'] . '</td>
        <td style="padding: 10px; text-align: center">' . $row['message'] . '</td>
		<td style="padding: 10px; text-align: center">' . $row['inquiry_type'] . '</td>
        <td style="padding: 10px; text-align: center">' . $row['college'] . '</td>
        <td style="padding: 10px; text-align: center">' . $row['course'] . '</td>
         <td style="padding: 10px; text-align: center">' . $row['attended_by'] . '</td>
        <td style="padding: 10px; text-align: center">' . $row['attended_date'] . '</td>
		<td style="padding: 10px; text-align: center">' . $row['is_attended_value'] . '</td>
    </tr>
	</tbody>';
		}

		$content .= '</table>
</body>
</html>';

		//print_r($content);exit;

		$this->mpdf->SetHTMLHeader('
                 <div>
                   <img src="https://ohcampus.com/assets/images/logo/logo.png" alt="#" width="150px">
                 </div>
			');
		$this->mpdf->SetHTMLFooter('
                 <p style="font-size: 14px;">OhCampus.com, Comet Career India (R), 2nd Floor, SMG Plaza, MG Road, Chikkamagaluru, Karnataka.</p>
			');

		//$this->m_pdf->pdf->AddPage("", "", "", "", "", 18, 18, 20, 20, 5, 1);


		//echo "testing...";exit;

		//$this->m_pdf->pdf->SetWatermarkImage('img/D-Logo.jpg', 0.2, 'P', array(0, 40));
		//$this->m_pdf->pdf->watermarkImgBehind = true;
		//$this->m_pdf->pdf->showWatermarkImage = true;

		//$path = dirname(dirname(dirname(__DIR__))) . "/EnquiryPdfDoc/";
		$outputDirectory = FCPATH . 'AllPdf/EnquiryPdfDoc/';
		if (!is_dir($outputDirectory)) {
			mkdir($outputDirectory, 0777, true);
		}
		//$outputFilePath = $outputDirectory . '1709558012_EnquiryDetails.pdf';
		//echo $path;exit;
		$filename = time() . "_EnquiryDetails" . ".pdf";
		$this->mpdf->WriteHTML($content);
		//$this->m_pdf->pdf->AddPage();
		//ob_end_clean();
		if (ob_get_level() > 0) {
			ob_end_clean();
		}
		$this->mpdf->Output($outputDirectory . $filename, "F");
		return $filename;
	}



	function SendEnquiryResponse()
	{

		$data = json_decode(file_get_contents('php://input'));
		if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
			$data['status'] = 'ok';
			echo json_encode($data);
			exit;
		}
		if ($data) {
			//print_r($data->email);exit;
			$headers = apache_request_headers();
			$token = str_replace("Bearer ", "", $headers['Authorization']);
			$kunci = $this->config->item('jwt_key');
			$userData = JWT::decode($token, $kunci);
			Utility::validateSession($userData->iat, $userData->exp);
			$tokenSession = Utility::tokenSession($userData);
			$senderName = "OhCampus Team";
			$bccArray = "";
			$toName = 'User';
			$from = 'enquiry@ohcampus.com';
			$to = $data->email;
			$enquiry = $data->enquiry;
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
						<h2 style="color: #2196f3; margin-bottom: 10px;">Response to your Enquiry</h2>
						<p style="color: #666666; font-size: 16px; line-height: 1.6;">Dear User,</p>
						<p style="color: #666666; font-size: 16px; line-height: 1.6;">Thank you for contacting us regarding your enquiry. Below is the response to your enquiry:</p>
						<div style="background-color: #ffffff; border-radius: 5px; padding: 15px; margin-bottom: 20px;">
							<p style="color: #666666; font-size: 16px; line-height: 1.6;"><strong>Your Enquiry:</strong></p>
							<p style="color: #666666; font-size: 16px; line-height: 1.6;">' . $enquiry . '</p>
						</div>
						<div style="background-color: #ffffff; border-radius: 5px; padding: 15px;">
							<p style="color: #666666; font-size: 16px; line-height: 1.6;"><strong>Our Response:</strong></p>
							<p style="color: #666666; font-size: 16px; line-height: 1.6;">' . $response . '</p>
						</div>
						<p style="color: #666666; font-size: 16px; line-height: 1.6;">If you have any further questions or need immediate assistance, please feel free to contact us.</p>
						<p style="color: #666666; font-size: 16px; line-height: 1.6;">Best regards,<br>OhCampus IT Team<br>OhCampus.com<br><img src="https://ohcampus.com/assets/images/logo/logo.png" alt="Company Logo" style="max-width: 100px;">
						</p>
					</div>
				</div>
				
				</body>
				</html>
				';
			$subject = 'Response to your Enquiry - OhCampus';
			$url = "https://api.sendinblue.com/v3/smtp/email";

			$headers = [
				"api-key: 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",
				"Content-Type: application/json",
			];
			$custJsonData = [
				"sender" => ["name" => $senderName, "email" => $from],
				"to" => [["name" =>  $toName, "email" => $to]],
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
			if ($res) {
				$attended_by = $userData->data->userId;
				$enquiryId = $data->enquiryId;
				$Arr = ['is_attended' => 1, 'attended_by' => $attended_by];
				$updateData = $this->Enquiry_model->updateData($enquiryId, $Arr);
				$response1["response_code"] = "200";
				$response1["response_message"] = "Response sent successfully.";
			} else {
				$response1["response_code"] = "400";
				$response1["response_message"] = "Having problem sending response. Please try again.";
			}
		} else {
			$response1["response_code"] = "500";
			$response1["response_message"] = "Data is null";
		}
		echo json_encode($response1);
		exit;
	}
}
