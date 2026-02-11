<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Methods: GET, POST');

/**
 * Common Controller
 *
 * @category   Controllers
 * @package    Web
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    30 JAN 2024
 *
 * Class Common handles all the operations related to displaying list, creating Common, update, and delete.
 */

if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}

/**
 * Note: This file may contain artifacts of previous malicious infection.
 * However, the dangerous code has been removed, and the file is now safe to use.
 */

class Common extends CI_Controller
{
    /**
     * Constructor
     *
     * Loads necessary libraries, helpers, and models for the Common controller.
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
        $this->load->model("web/College_model", "", true);
        $this->load->model("web/Exam_model", "", true);
        $this->load->model("web/Blog_model", "", true);
        $this->load->model("web/Event_model", "", true);
        $this->load->model("web/Courses_model", "", true);
        $this->load->model("web/Common_model", "", true);
        $this->load->model("admin/common_model", "", true);
        $this->load->model("web/User_model", "", true);
        $this->load->library("Utility");
        $this->load->library('mpdf_lib');
        $config = [
            'tempDir' => APPPATH . 'tmp' // Or use FCPATH.'tmp' if you want outside of application folder
        ];
        $this->mpdf = new \Mpdf\Mpdf($config);
        //$this->load->library("m_pdf");
    }

    public function getTotalCount()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if (empty($_SERVER["HTTP_AUTHORIZATION"])) {
            if (
                !is_object($data) ||
                !property_exists($data, "defaultToken") ||
                empty($data->defaultToken)
            ) {
                $response["response_code"] = "401";
                $response["response_message"] =
                    "UNAUTHORIZED: Please provide an access token to continue accessing the API";
                echo json_encode($response);
                exit();
            }
            if ($data->defaultToken !== $this->config->item("defaultToken")) {
                $response["response_code"] = "402";
                $response["response_message"] =
                    "UNAUTHORIZED: Please provide a valid access token to continue accessing the API";
                echo json_encode($response);
                exit();
            }
        } else {
            $headers = apache_request_headers();
            $token = str_replace("Bearer ", "", $headers["Authorization"]);
            $kunci = $this->config->item("jwt_key");
            $userData = JWT::decode($token, $kunci);
            Utility::validateSession($userData->iat, $userData->exp);
            $tokenSession = Utility::tokenSession($userData);
        }
        $clg = $this->College_model->countAllClg();
        $exam = $this->Exam_model->countAllExam();
        $courses = $this->Courses_model->countAllcourses();

        if ($clg || $exam || $courses) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["Clgcount"] = $clg;
            $response["Examcount"] = $exam;
            $response["Coursescount"] = $courses;
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed";
        }
        echo json_encode($response);
        exit();
    }

    public function getFAQ()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        $Que = $this->Common_model->getFAQ();

        if ($Que) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["Question"] = $Que;
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed";
        }
        echo json_encode($response);
        exit();
    }

    public function getSubCategoryList()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if (empty($_SERVER["HTTP_AUTHORIZATION"])) {
            if (
                !is_object($data) ||
                !property_exists($data, "defaultToken") ||
                empty($data->defaultToken)
            ) {
                $response["response_code"] = "401";
                $response["response_message"] =
                    "UNAUTHORIZED: Please provide an access token to continue accessing the API";
                echo json_encode($response);
                exit();
            }
            if ($data->defaultToken !== $this->config->item("defaultToken")) {
                $response["response_code"] = "402";
                $response["response_message"] =
                    "UNAUTHORIZED: Please provide a valid access token to continue accessing the API";
                echo json_encode($response);
                exit();
            }
        } else {
            $headers = apache_request_headers();
            $token = str_replace("Bearer ", "", $headers["Authorization"]);
            $kunci = $this->config->item("jwt_key");
            $userData = JWT::decode($token, $kunci);
            Utility::validateSession($userData->iat, $userData->exp);
            $tokenSession = Utility::tokenSession($userData);
        }
        if ($data) {
            $collegeId = $data->collegeid;
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
        exit();
    }

    public function getCourseLevel()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if (empty($_SERVER["HTTP_AUTHORIZATION"])) {
            if (
                !is_object($data) ||
                !property_exists($data, "defaultToken") ||
                empty($data->defaultToken)
            ) {
                $response["response_code"] = "401";
                $response["response_message"] =
                    "UNAUTHORIZED: Please provide an access token to continue accessing the API";
                echo json_encode($response);
                exit();
            }
            if ($data->defaultToken !== $this->config->item("defaultToken")) {
                $response["response_code"] = "402";
                $response["response_message"] =
                    "UNAUTHORIZED: Please provide a valid access token to continue accessing the API";
                echo json_encode($response);
                exit();
            }
        } else {
            $headers = apache_request_headers();
            $token = str_replace("Bearer ", "", $headers["Authorization"]);
            $kunci = $this->config->item("jwt_key");
            $userData = JWT::decode($token, $kunci);
            Utility::validateSession($userData->iat, $userData->exp);
            $tokenSession = Utility::tokenSession($userData);
        }
        if ($data) {
            $collegeId = $data->collegeid;
            $SubCategory = $this->Common_model->getAcademicCategory($collegeId);

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
        exit();
    }

    public function getExamAccepted()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if (empty($_SERVER["HTTP_AUTHORIZATION"])) {
            if (
                !is_object($data) ||
                !property_exists($data, "defaultToken") ||
                empty($data->defaultToken)
            ) {
                $response["response_code"] = "401";
                $response["response_message"] =
                    "UNAUTHORIZED: Please provide an access token to continue accessing the API";
                echo json_encode($response);
                exit();
            }
            if ($data->defaultToken !== $this->config->item("defaultToken")) {
                $response["response_code"] = "402";
                $response["response_message"] =
                    "UNAUTHORIZED: Please provide a valid access token to continue accessing the API";
                echo json_encode($response);
                exit();
            }
        } else {
            $headers = apache_request_headers();
            $token = str_replace("Bearer ", "", $headers["Authorization"]);
            $kunci = $this->config->item("jwt_key");
            $userData = JWT::decode($token, $kunci);
            Utility::validateSession($userData->iat, $userData->exp);
            $tokenSession = Utility::tokenSession($userData);
        }
        if ($data) {
            $collegeId = $data->collegeid;
            $SubCategory = $this->Common_model->getExamAccepted($collegeId);

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
        exit();
    }

    public function saveEnquiry()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if (empty($_SERVER["HTTP_AUTHORIZATION"])) {
            if (
                !is_object($data) ||
                !property_exists($data, "defaultToken") ||
                empty($data->defaultToken)
            ) {
                $response["response_code"] = "401";
                $response["response_message"] =
                    "UNAUTHORIZED: Please provide an access token to continue accessing the API";
                echo json_encode($response);
                exit();
            }
            if ($data->defaultToken !== $this->config->item("defaultToken")) {
                $response["response_code"] = "402";
                $response["response_message"] =
                    "UNAUTHORIZED: Please provide a valid access token to continue accessing the API";
                echo json_encode($response);
                exit();
            }
        } else {
            $headers = apache_request_headers();
            $token = str_replace("Bearer ", "", $headers["Authorization"]);
            $kunci = $this->config->item("jwt_key");
            $userData = JWT::decode($token, $kunci);
            Utility::validateSession($userData->iat, $userData->exp);
            $tokenSession = Utility::tokenSession($userData);
        }
        if ($data) {
            $name = $data->name;
            $email = $data->email;
            $phone = $data->phone;
            $message = $data->message;
            $postid = $data->postid;
            $type = $data->type;
            $city = $data->city;
            $state = $data->state;
            $arr = [
                "name" => $name,
                "email" => $email,
                "phone" => $phone,
                "message" => $message,
                "postid" => $postid,
                "type" => $type,
                "city" => $city,
                "state" => $state
            ];
            $saveEnquiry = $this->Common_model->saveEnquiry($arr);

            if ($saveEnquiry) {
                $sendMailToCustomer = $this->sendMailToCustomer($name, $email);
                $logArr = ["enquiry_id" => $saveEnquiry];
                $tableName = "enquiry_log";
                $addLog = $this->Common_model->addLog($logArr, $tableName);
                $response["response_code"] = "200";
                $response["response_message"] =
                    "Your inquiry has been submitted successfully. We will get back to you soon!";
                $response["response_data"] = $saveEnquiry;
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

    public function sendMailToCustomer($name, $email)
    {
        $senderName = "OhCampus Team";
        $bccArray = "";
        $toName = $name;
        $from = "enquiry@ohcampus.comm";
        $to = $email;
        $emailMessage =
            '<!DOCTYPE html>
        <html lang="en">
        <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Email Template</title>
        </head>
        <body style="font-family: Arial, sans-serif; background-color: #f5f5f5; padding: 20px;">
        
        <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
            <div style="text-align: center; margin-bottom: 20px;">
                <img src="https://win.k2key.in/ohcampus/uploads/OhCampusLogo.png" alt="Company Logo" style="max-width: 200px;">
            </div>
            <div style="padding: 20px; background-color: #f0f0f0; border-radius: 5px;">
                <h2 style="color: #333333; margin-bottom: 10px;">Thank You for Your Enquiry!</h2>
                <p style="color: #666666; font-size: 16px; line-height: 1.6;">Dear ' .
            $toName .
            ',</p>
                <p style="color: #666666; font-size: 16px; line-height: 1.6;">Thank you for contacting us regarding your enquiry. We have received your message and will get back to you as soon as possible with the information you requested.</p>
                <p style="color: #666666; font-size: 16px; line-height: 1.6;">If you have any further questions or need immediate assistance, please feel free to contact us.</p>
                <p style="color: #666666; font-size: 16px; line-height: 1.6;">Best regards,<br>OhCampus IT Team<br>OhCampus<br><img src="https://win.k2key.in/ohcampus/uploads/OhCampusLogo.png" alt="Company Logo" style="max-width: 100px;">
                </div>
        </div>
        
        </body>
        </html>
        ';
        $subject = "Response to Your Enquiry - OhCampus";
        $url = "https://api.sendinblue.com/v3/smtp/email";

        $headers = [
            "api-key: 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",
            "Content-Type: application/json",
        ];
        $custJsonData = [
            "sender" => ["name" => $senderName, "email" => $from],
            "to" => [["name" => $toName, "email" => $to]],
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
    }

    public function downloadBrochure()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if (empty($_SERVER["HTTP_AUTHORIZATION"])) {
            if (
                !is_object($data) ||
                !property_exists($data, "defaultToken") ||
                empty($data->defaultToken)
            ) {
                $response["response_code"] = "401";
                $response["response_message"] =
                    "UNAUTHORIZED: Please provide an access token to continue accessing the API";
                echo json_encode($response);
                exit();
            }
            if ($data->defaultToken !== $this->config->item("defaultToken")) {
                $response["response_code"] = "402";
                $response["response_message"] =
                    "UNAUTHORIZED: Please provide a valid access token to continue accessing the API";
                echo json_encode($response);
                exit();
            }
        } else {
            $headers = apache_request_headers();
            $token = str_replace("Bearer ", "", $headers["Authorization"]);
            $kunci = $this->config->item("jwt_key");
            $userData = JWT::decode($token, $kunci);
            // Utility::validateSession($userData->iat, $userData->exp);
            // $tokenSession = Utility::tokenSession($userData);
        }
        if ($data) {
            // print_r($userData->id);exit;
            $collegeId = $data->collegeId;
            $userId = $data->userId;
            if (empty($userId)) {
                $userId =  $userData->id;
            }
            $brochures = $this->Common_model->getBrochure($collegeId);

            $clgDtl = $this->College_model->getCollegeDetailsByID($collegeId);

            $getUserDetails = $this->User_model->getUserDetailsById($userId);
            // print_r($brochures);exit;
            if (!empty($brochures[0])) {
                $brochure = $brochures[0]["file"];
                $brochureName = $brochures[0]["title"];
                $fname = $getUserDetails[0]->f_name;
                $lname = $getUserDetails[0]->l_name;
                $name = $fname . " " . $lname;
                $email = $getUserDetails[0]->email;
                $senderName = "OhCampus Team";
                $bccArray = "";
                $toName = $name;
                $from = "enquiry@ohcampus.com";
                // $to = $getUserDetails[0]["email"];
                $to = $getUserDetails[0]->email;
                $emailMessage =
                    '<!DOCTYPE html>
                <html lang="en">
                <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Email Template</title>
                </head>
                <body style="font-family: Arial, sans-serif; background-color: #f5f5f5; padding: 20px;">
                
                <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
                  <div style="text-align: center; margin-bottom: 20px;">
                    <img src="https://win.k2key.in/ohcampus/uploads/OhCampusLogo.png" alt="Company Logo" style="max-width: 200px;">
                  </div>
                  <div style="padding: 20px; background-color: #f0f0f0; border-radius: 5px;">
                    <p style="color: #666666; font-size: 16px; line-height: 1.6;">Hi ' .
                    $name .
                    ',</p>
                    <p style="color: #666666; font-size: 16px; line-height: 1.6;">We are thrilled to share with you the latest brochure for ' .
                    $clgDtl[0]["title"] .
                    '! This comprehensive guide contains all the information you need to know about our offerings, programs, and facilities.</p>
                    <p style="color: #666666; font-size: 16px; line-height: 1.6;">Please feel free to explore it at your convenience, and dont hesitate to reach out if you have any questions or need further assistance.</p>
                    <p style="color: #666666; font-size: 16px; line-height: 1.6;">Thank you for your interest in ' .
                    $clgDtl[0]["title"] .
                    ' and for choosing OhCampus. We are excited to embark on this journey with you!</p>
                    <p style="color: #666666; font-size: 16px; line-height: 1.6;">Warm regards,<br>The OhCampus Team</p>
                  </div>
                </div>
                
                </body>
                </html>
                ';
                $subject =
                    "E-Brochure of " . $clgDtl[0]["title"] . " - OhCampus";

                $url = "https://api.sendinblue.com/v3/smtp/email";



                /*  $headers = [

                    "api-key: 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",

                    "Content-Type: application/json",

                ];*/

                $headers = [

                    "api-key: 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",

                    "Content-Type: application/json",

                ];

                // $attachmentData = base64_encode(
                //     file_get_contents("uploads/brochures/" . $brochure)
                // );
                $filePath = FCPATH . "uploads/brochures/" . $brochure;

                if (!file_exists($filePath)) {
                    die("PDF file not found: " . $filePath);
                }

                $fileContent = file_get_contents($filePath);

                if ($fileContent === false) {
                    die("Unable to read PDF file");
                }

                $attachmentData = base64_encode($fileContent);

                $custJsonData = [
                    "sender" => ["name" => $senderName, "email" => $from],
                    "to" => [["name" => $toName, "email" => $to]],
                    "subject" => $subject,
                    "htmlContent" => $emailMessage,
                    "attachment" => [
                        [
                            "content" => $attachmentData,
                            "name" =>
                            "e_brochure" . $clgDtl[0]["title"] . ".pdf",
                            "type" => "application/pdf", // Adjust MIME type according to your attachment
                        ],
                    ],
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

                if ($res) {
                    $Arr = [
                        "user_name" => $getUserDetails[0]->id,
                        "email" => $email,
                        "college" => $clgDtl[0]["id"],
                        "location" => $clgDtl[0]["city"],
                        "latest_activity" => "Brochure Downloaded",
                    ];
                    $addUserActivity = $this->Common_model->addUserActivity(
                        $Arr
                    );
                    $ClgRepArr = [
                        "college" => $collegeId,
                        "no_of_articles_linked" => 0,
                        "no_of_brochures_download" => 1,
                        "no_of_application_submitted" => 0,
                        "no_of_que_asked" => 0,
                        "no_of_answeres" => 0,
                    ];
                    // $this->load->model("admin/common_model", "", true);
                    $checkcollegeReport = $this->common_model->checkcollegeReport(
                        $collegeId
                    );
                    if ($checkcollegeReport > 0) {
                        $updateClgReport = $this->common_model->updateClgReport(
                            $collegeId,
                            $ClgRepArr
                        );
                    } else {
                        $saveClgReport = $this->common_model->saveClgReport(
                            $ClgRepArr
                        );
                    }
                    $response1["response_code"] = "200";
                    $response1["response_message"] =
                        "brochure sent sucessfully by mail";
                }
            } else {
                $collegeDetails = $this->College_model->getCollegeDetailsByID($collegeId);
                $clgDtl = $this->College_model->getCollegeDetailsByID(
                    $collegeId
                );

                //print_r($clgDtl);exit;
                $courseDetails = $this->College_model->getCoursesAndFeesOfClg(
                    $collegeId
                );
                $HighlightsDetails = $this->College_model->getCollegeHighlightByID(
                    $collegeId
                );
                $template_name = "template/brochure.html";
                $content = file_get_contents($template_name);
                // $PDFheader = SetHtmlHeader();
                // $PDFfooter = SetHtmlFooter();
                $this->mpdf->AddPage(
                    "",
                    "",
                    "",
                    "",
                    "",
                    20, // margin_left
                    20, // margin right
                    10, // margin top
                    15, // margin bottom
                    5, // margin header
                    5
                ); // margin footer
                $content = $this->generatePDF(
                    $collegeDetails,
                    $courseDetails,
                    $HighlightsDetails
                );
                // print_r($content);
                // exit;
                //----------
                $path = dirname(dirname(__DIR__)) . "/uploads/brochures/";

                $path = str_replace("\\application", "", $path);

                $path = str_replace("/", "\\", $path);

                // print_r($path);exit;

                $title = str_replace(['(', ')'], '', $collegeDetails[0]["title"]);
                $title = str_replace(' ', '_', $title);

                $filename = "e_brochure_" . $title . ".pdf";

                // $filename = 'e_brochure_new_xx.pdf';
                $path = FCPATH . "uploads/brochures/";

                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }
                $this->mpdf->WriteHTML($content);

                $this->mpdf->debug = true;

                $this->mpdf->Output($path . $filename, "F");
                $pdf = base_url() . "/uploads/brochures/" . $filename;
                // print_r($pdf);
                // exit;
                $brochuresData = [
                    "collegeid" => $collegeId,
                    "title" => "brochure pdf",
                    "file" => $filename,
                ];
                $saveBrochures = $this->College_model->saveBrochures(
                    $brochuresData
                );
                sleep(3);
                $brochures = $this->Common_model->getBrochure($collegeId);

                $brochure = $brochures[0]["file"];
                $brochureName = $brochures[0]["title"];
                $fname = $getUserDetails[0]->f_name;
                $lname = $getUserDetails[0]->l_name;
                $name = $fname . " " . $lname;
                $email = $getUserDetails[0]->email;
                $senderName = "OhCampus Team";
                $bccArray = "";
                $toName = $name;
                $from = "enquiry@ohcampus.comm";
                // $to = $getUserDetails[0]["email"];
                $to = $getUserDetails[0]->email;
                $emailMessage =
                    '<!DOCTYPE html>
                <html lang="en">
                <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Email Template</title>
                </head>
                <body style="font-family: Arial, sans-serif; background-color: #f5f5f5; padding: 20px;">
                
                <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
                  <div style="text-align: center; margin-bottom: 20px;">
                    <img src="https://win.k2key.in/ohcampus/uploads/OhCampusLogo.png" alt="Company Logo" style="max-width: 200px;">
                  </div>
                  <div style="padding: 20px; background-color: #f0f0f0; border-radius: 5px;">
                    <p style="color: #666666; font-size: 16px; line-height: 1.6;">Hi ' .
                    $name .
                    ',</p>
                    <p style="color: #666666; font-size: 16px; line-height: 1.6;">We are thrilled to share with you the latest brochure for ' .
                    $clgDtl[0]["title"] .
                    '! This comprehensive guide contains all the information you need to know about our offerings, programs, and facilities.</p>
                    <p style="color: #666666; font-size: 16px; line-height: 1.6;">Please feel free to explore it at your convenience, and dont hesitate to reach out if you have any questions or need further assistance.</p>
                    <p style="color: #666666; font-size: 16px; line-height: 1.6;">Thank you for your interest in ' .
                    $clgDtl[0]["title"] .
                    ' and for choosing OhCampus. We are excited to embark on this journey with you!</p>
                    <p style="color: #666666; font-size: 16px; line-height: 1.6;">Warm regards,<br>The OhCampus Team</p>
                  </div>
                </div>
                
                </body>
                </html>
                ';
                $subject =
                    "E-Brochure of " . $clgDtl[0]["title"] . " - OhCampus";
                $url = "https://api.sendinblue.com/v3/smtp/email";

                $headers = [
                    "api-key: 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",
                    "Content-Type: application/json",
                ];
                $attachmentData = base64_encode(
                    file_get_contents("uploads/brochures/" . $brochure)
                );

                $custJsonData = [
                    "sender" => ["name" => $senderName, "email" => $from],
                    "to" => [["name" => $toName, "email" => $to]],
                    "subject" => $subject,
                    "htmlContent" => $emailMessage,
                    "attachment" => [
                        [
                            "content" => $attachmentData,
                            "name" =>
                            "e_brochure" . $clgDtl[0]["title"] . ".pdf",
                            "type" => "application/pdf", // Adjust MIME type according to your attachment
                        ],
                    ],
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
                    $Arr = [
                        "user_name" => $getUserDetails[0]->id,
                        "email" => $email,
                        "college" => $clgDtl[0]["id"],
                        "location" => $clgDtl[0]["city"],
                        "latest_activity" => "Brochure Downloaded",
                    ];
                    $addUserActivity = $this->Common_model->addUserActivity(
                        $Arr
                    );
                    $ClgRepArr = [
                        "college" => $collegeId,
                        "no_of_articles_linked" => 0,
                        "no_of_brochures_download" => 1,
                        "no_of_application_submitted" => 0,
                        "no_of_que_asked" => 0,
                        "no_of_answeres" => 0,
                    ];
                    // $this->load->model("admin/common_model", "", true);
                    $checkcollegeReport = $this->common_model->checkcollegeReport(
                        $collegeId
                    );
                    if ($checkcollegeReport > 0) {
                        $updateClgReport = $this->common_model->updateClgReport(
                            $collegeId,
                            $ClgRepArr
                        );
                    } else {
                        $saveClgReport = $this->common_model->saveClgReport(
                            $ClgRepArr
                        );
                    }

                    $response1["response_code"] = "200";
                    $response1["response_message"] =
                        "brochure sent sucessfully by mail";
                }
            }
        } else {
            $response1["response_code"] = "500";
            $response1["response_message"] = "Data is null";
        }
        echo json_encode($response1);
        exit();
    }

    public function generatePDF(

        $collegeDetails,

        $courseDetails,

        $HighlightsDetails

    ) {

        // print_r($collegeDetails);exit;

        $collegeContent =

            '<h2>College Information</h2>

        <ul>

          <li style="font-weight:bold;">' .
            $collegeDetails[0]["title"] . ', ' .
            $collegeDetails[0]["city"] .
            '</li>

    <li style="margin:10px 0;">
        <img 
            src="' . base_url('uploads/college/' . $collegeDetails[0]["banner"]) . '" 
            alt="College Banner"
            style="
                width:100%;
                height:180px;
                display:block;
                object-fit:cover;
                border-radius:6px;
            ">
    </li>

    <li>' .
            $collegeDetails[0]["description"] .
            '</li>

        </ul>';

        $coursesContent = ' <table>

        <thead>

            <tr>

                <th>Course Name</th>

                <th>Duration</th>

                <th>category</th>

                <th>academic category</th>

                <th>sub category</th>



            </tr>

        </thead>

        <tbody>';

        foreach ($courseDetails as $key => $value) {

            // Convert object to array

            $value = (array) $value;

            if (!empty($value["name"])) {

                $coursesContent .=

                    '<tr>

                <td>' .

                    $value["name"] .

                    '</td>

                <td>' .

                    $value["duration"] .

                    ' years</td>

                <td>' .

                    $value["courseCategoryName"] .

                    '</td>

                <td>' .

                    $value["academicCategoryName"] .

                    '</td>

                <td>' .

                    $value["subCategoryName"] .

                    '</td>

            </tr>';
            }
        }



        $coursesContent .= '</tbody>

        </table><pagebreak />';

        /*  foreach ($HighlightsDetails as $key => $value) {

            $value = (array) $value;

            if (count($HighlightsDetails) > 1) {

                // If there are multiple highlights, wrap each one in <li> tags

                $highlightContent = "<li>" . $value["text"] . "</li>";

            } else {

                // If there's only one highlight, don't use <ul> or <li> tags

                $highlightContent .= $value["text"];

            }

        }*/


        $highlightContent = "";

        if (count($HighlightsDetails) > 1) {
            $highlightContent .= "<ul>";
            foreach ($HighlightsDetails as $value) {
                $value = (array) $value;
                $highlightContent .= "<li>" . $value["text"] . "</li>";
            }
            $highlightContent .= "</ul>";
        } else {
            $highlightContent = $HighlightsDetails[0]["text"];
        }

        $address = $collegeDetails[0]["address"];

        $template_name = "template/brochure.html";

        $content = file_get_contents($template_name);



        // Replace placeholders with actual content

        $content = str_replace(

            ["VAR_COLLEGE_DATA", "VAR_COURSE_DATA", "VAR_HIGHLIGHT_DATA", "ADD"],

            [$collegeContent, $coursesContent, $highlightContent, $address],

            $content

        );

        return $content;
    }

    public function generatePDFbkp(
        $collegeDetails,
        $courseDetails,
        $HighlightsDetails
    ) {
        /* ---------------- LIST STYLE FIX (IMPORTANT) ---------------- */
        $style = '
        <style>
            ul {
                list-style-type: disc;
                list-style-position: outside;
                padding-left: 20px;
            }
            ol {
                list-style-type: decimal;
                list-style-position: outside;
                padding-left: 20px;
            }
            table {
                width:100%;
                border-collapse: collapse;
            }
            th, td {
                border:1px solid #ccc;
                padding:8px;
                font-size:12px;
            }
            th {
                background:#f5f5f5;
                font-weight:bold;
            }
        </style>
    ';

        /* ---------------- COLLEGE CONTENT ---------------- */
        $collegeContent = '
    <h2>College Information</h2>
    <ul>
        <li><strong>' . $collegeDetails[0]["title"] . ', ' . $collegeDetails[0]["city"] . '</strong></li>

        <li style="margin:10px 0; list-style:none;">
            <img src="' . base_url('uploads/college/' . $collegeDetails[0]["banner"]) . '"
                 style="width:100%; height:180px; object-fit:cover; border-radius:6px;">
        </li>

        <li>' . $collegeDetails[0]["description"] . '</li>
    </ul>';

        /* ---------------- COURSES TABLE ---------------- */
        $coursesContent = '
    <table>
        <thead>
            <tr>
                <th>Course Name</th>
                <th>Duration</th>
                <th>Category</th>
                <th>Academic Category</th>
                <th>Sub Category</th>
            </tr>
        </thead>
        <tbody>';

        foreach ($courseDetails as $value) {
            $value = (array) $value;

            if (!empty($value["name"])) {
                $coursesContent .= '
            <tr>
                <td>' . $value["name"] . '</td>
                <td>' . $value["duration"] . ' years</td>
                <td>' . $value["courseCategoryName"] . '</td>
                <td>' . $value["academicCategoryName"] . '</td>
                <td>' . $value["subCategoryName"] . '</td>
            </tr>';
            }
        }

        $coursesContent .= '</tbody></table>';

        /* ---------------- HIGHLIGHTS (FIXED) ---------------- */
        $highlightContent = '';

        if (!empty($HighlightsDetails)) {
            $highlightContent .= '<ul>';

            foreach ($HighlightsDetails as $value) {
                $value = (array) $value;
                if (!empty($value["text"])) {
                    $highlightContent .= '<li>' . $value["text"] . '</li>';
                }
            }

            $highlightContent .= '</ul>';
        }

        /* ---------------- TEMPLATE ---------------- */
        $address = $collegeDetails[0]["address"];

        $template_name = FCPATH . "template/brochure.html";
        $content = file_get_contents($template_name);

        $content = str_replace(
            ["VAR_COLLEGE_DATA", "VAR_COURSE_DATA", "VAR_HIGHLIGHT_DATA", "ADD"],
            [$style . $collegeContent, $coursesContent, $highlightContent, $address],
            $content
        );

        return $content;
    }

    public function savCourseApplication()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if (empty($_SERVER["HTTP_AUTHORIZATION"])) {
            if (
                !is_object($data) ||
                !property_exists($data, "defaultToken") ||
                empty($data->defaultToken)
            ) {
                $response["response_code"] = "401";
                $response["response_message"] =
                    "UNAUTHORIZED: Please provide an access token to continue accessing the API";
                echo json_encode($response);
                exit();
            }
            if ($data->defaultToken !== $this->config->item("defaultToken")) {
                $response["response_code"] = "402";
                $response["response_message"] =
                    "UNAUTHORIZED: Please provide a valid access token to continue accessing the API";
                echo json_encode($response);
                exit();
            }
        } else {
            $headers = apache_request_headers();
            $token = str_replace("Bearer ", "", $headers["Authorization"]);
            $kunci = $this->config->item("jwt_key");
            $userData = JWT::decode($token, $kunci);
            Utility::validateSession($userData->iat, $userData->exp);
            $tokenSession = Utility::tokenSession($userData);
        }
        if ($data) {
            $student_name = $data->student_name;
            $email = $data->email;
            $mobile_no = $data->mobile_no;
            $category = $data->category;
            $college = $data->college;
            $course = $data->course;
            $entrance_exam = $data->entrance_exam;
            $rank = $data->rank;
            $score = $data->score;
            $userId = 1;
            $arr = [
                "student_name" => $student_name,
                "email" => $email,
                "mobile_no" => $mobile_no,
                "category" => $category,
                "college" => $college,
                "course" => $course,
                "entrance_exam" => $entrance_exam,
                "rank" => $rank,
                "score" => $score,
            ];
            $application = $this->Common_model->saveCourseApplication($arr);

            if ($application) {
                $sendMailToCustomer = $this->sendMailToCustomer(
                    $student_name,
                    $email
                );
                $logArr = ["application_id" => $application];
                $tableName = "application_log";
                $addLog = $this->Common_model->addLog($logArr, $tableName);

                $clgDtl = $this->College_model->getCollegeDetailsByID($college);
                $Arr = [
                    "user_name" => $userId,
                    "email" => $email,
                    "college" => $clgDtl[0]["id"],
                    "location" => $clgDtl[0]["city"],
                    "latest_activity" => "Application Submitted.",
                ];

                // $Arr = ['user_name'=>$student_name,'email'=>$email,'location'=>'','latest_activity'=>''.$clgDtl[0]['title'].','.$clgDtl[0]['city'].' Application Submitted.'];
                $addUserActivity = $this->Common_model->addUserActivity($Arr);
                $ClgRepArr = [
                    "college" => $college,
                    "no_of_articles_linked" => 0,
                    "no_of_brochures_download" => 0,
                    "no_of_application_submitted" => 1,
                    "no_of_que_asked" => 0,
                    "no_of_answeres" => 0,
                    "no_of_review" => 0,
                ];
                $checkcollegeReport = $this->common_model->checkcollegeReport(
                    $college
                );
                if ($checkcollegeReport > 0) {
                    $updateClgReport = $this->common_model->updateClgReport(
                        $college,
                        $ClgRepArr
                    );
                } else {
                    $saveClgReport = $this->common_model->saveClgReport(
                        $ClgRepArr
                    );
                }
                $response["response_code"] = "200";
                $response["response_message"] =
                    "Thanks for submitting the details.Our counsellor will contact you shortly to provide details.";
                $response["response_data"] = $application;
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

    public function savPredictAdmission()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if (empty($_SERVER["HTTP_AUTHORIZATION"])) {
            if (
                !is_object($data) ||
                !property_exists($data, "defaultToken") ||
                empty($data->defaultToken)
            ) {
                $response["response_code"] = "401";
                $response["response_message"] =
                    "UNAUTHORIZED: Please provide an access token to continue accessing the API";
                echo json_encode($response);
                exit();
            }
            if ($data->defaultToken !== $this->config->item("defaultToken")) {
                $response["response_code"] = "402";
                $response["response_message"] =
                    "UNAUTHORIZED: Please provide a valid access token to continue accessing the API";
                echo json_encode($response);
                exit();
            }
        } else {
            $headers = apache_request_headers();
            $token = str_replace("Bearer ", "", $headers["Authorization"]);
            $kunci = $this->config->item("jwt_key");
            $userData = JWT::decode($token, $kunci);
            Utility::validateSession($userData->iat, $userData->exp);
            $tokenSession = Utility::tokenSession($userData);
        }
        if ($data) {
            $student_name = $data->student_name;
            $email = $data->email;
            $mobile_no = $data->mobile_no;
            $category = $data->category;
            $college = $data->college;
            $course = $data->course;
            $entrance_exam = $data->entrance_exam;
            $rank = $data->rank;
            $score = $data->score;
            $userId = 1;
            $arr = [
                "student_name" => $student_name,
                "email" => $email,
                "mobile_no" => $mobile_no,
                "category" => $category,
                "college" => $college,
                "course" => $course,
                "entrance_exam" => $entrance_exam,
                "rank" => $rank,
                "score" => $score,
            ];
            $application = $this->Common_model->savPredictAdmission($arr);

            if ($application) {
                $sendMailToCustomer = $this->sendMailToCustomer(
                    $student_name,
                    $email
                );
                $logArr = ["predict_id" => $application];
                $tableName = "predict_log";
                $addLog = $this->Common_model->addLog($logArr, $tableName);
                $clgDtl = $this->College_model->getCollegeDetailsByID($college);
                $Arr = [
                    "user_name" => $userId,
                    "email" => $email,
                    "college" => $clgDtl[0]["id"],
                    "location" => $clgDtl[0]["city"],
                    "latest_activity" => "Predcited Admission Submitted.",
                ];
                $addUserActivity = $this->Common_model->addUserActivity($Arr);
                // $ClgRepArr = ['college'=>$college,'no_of_articles_linked'=>0,'no_of_brochures_download'=>0,'no_of_application_submitted'=>1,'no_of_que_asked'=>0,'no_of_answeres'=>0];
                // $checkcollegeReport = $this->common_model->checkcollegeReport($college);
                // if($checkcollegeReport > 0)
                // {
                //     $updateClgReport = $this->common_model->updateClgReport($college,$ClgRepArr);

                // }
                // else
                // {
                //    $saveClgReport = $this->common_model->saveClgReport($ClgRepArr);
                // }
                $response["response_code"] = "200";
                $response["response_message"] =
                    "Thanks for submitting the details.Our counsellor will contact you shortly to provide details.";
                $response["response_data"] = $application;
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

    public function getTrendingSpecilization()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if (empty($_SERVER["HTTP_AUTHORIZATION"])) {
            if (
                !is_object($data) ||
                !property_exists($data, "defaultToken") ||
                empty($data->defaultToken)
            ) {
                $response["response_code"] = "401";
                $response["response_message"] =
                    "UNAUTHORIZED: Please provide an access token to continue accessing the API";
                echo json_encode($response);
                exit();
            }
            if ($data->defaultToken !== $this->config->item("defaultToken")) {
                $response["response_code"] = "402";
                $response["response_message"] =
                    "UNAUTHORIZED: Please provide a valid access token to continue accessing the API";
                echo json_encode($response);
                exit();
            }
        } else {
            $headers = apache_request_headers();
            $token = str_replace("Bearer ", "", $headers["Authorization"]);
            $kunci = $this->config->item("jwt_key");
            $userData = JWT::decode($token, $kunci);
            Utility::validateSession($userData->iat, $userData->exp);
            $tokenSession = Utility::tokenSession($userData);
        }
        $TrendingSpecilization = $this->Common_model->getTrendingSpecilization();

        if ($TrendingSpecilization) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["TrendingSpecilization"] = $TrendingSpecilization;
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed";
        }

        echo json_encode($response);
        exit();
    }

    public function sendContactMail()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if (empty($_SERVER["HTTP_AUTHORIZATION"])) {
            if (
                !is_object($data) ||
                !property_exists($data, "defaultToken") ||
                empty($data->defaultToken)
            ) {
                $response["response_code"] = "401";
                $response["response_message"] =
                    "UNAUTHORIZED: Please provide an access token to continue accessing the API";
                echo json_encode($response);
                exit();
            }
            if ($data->defaultToken !== $this->config->item("defaultToken")) {
                $response["response_code"] = "402";
                $response["response_message"] =
                    "UNAUTHORIZED: Please provide a valid access token to continue accessing the API";
                echo json_encode($response);
                exit();
            }
        } else {
            $headers = apache_request_headers();
            $token = str_replace("Bearer ", "", $headers["Authorization"]);
            $kunci = $this->config->item("jwt_key");
            $userData = JWT::decode($token, $kunci);
            Utility::validateSession($userData->iat, $userData->exp);
            $tokenSession = Utility::tokenSession($userData);
        }
        if ($data) {
            $senderName = $data->name;
            $bccArray = "vaishnavi.b@queenzend.com";
            $toName = "OhCampus Enquiry Team";
            $from = $data->email;
            $senderemail = $data->email;
            $to = "enquiry@ohcampus.com";

            $contactNo = $data->contactNo;
            $subject = $data->subject;
            $message = $data->message;

            $emailMessage =
                '<!DOCTYPE html>
        <html lang="en">
        <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Email Template</title>
        </head>
        <body style="font-family: Arial, sans-serif; background-color: #f5f5f5; padding: 20px;">
        
        <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
            <div style="text-align: center; margin-bottom: 20px;">
                <img src="https://win.k2key.in/ohcampus/uploads/OhCampusLogo.png" alt="Company Logo" style="max-width: 200px;">
            </div>
            <div style="background-color: #f0f0f0; padding: 20px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
    <div style="background-color: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
        <h2 style="color: #333; font-size: 24px; margin-bottom: 20px;">Enquiry from OhCampus.com</h2>
        <p style="color: #666666; font-size: 16px; line-height: 1.6;">Dear ' .
                $toName .
                ',</p>
        <p style="font-size: 16px; color: #666;">
            <strong style="color: #333;">Name:</strong>' .
                $senderName .
                '<br>
            <strong style="color: #333;">Email:</strong>' .
                $senderemail .
                '<br>
            <strong style="color: #333;">Contact No:</strong>' .
                $contactNo .
                '<br>
            <strong style="color: #333;">Subject:</strong>' .
                $subject .
                '<br>
            <strong style="color: #333;">Message:</strong>' .
                $message .
                '<br>
        </p>
    </div>
</div>
        </div>
        
        </body>
        </html>
        ';
            $subject = "New Enquiry - OhCampus";
            $url = "https://api.sendinblue.com/v3/smtp/email";

            $headers = [
                "api-key: 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",
                "Content-Type: application/json",
            ];
            $custJsonData = [
                "sender" => ["name" => $senderName, "email" => $from],
                "to" => [["name" => $toName, "email" => $to]],
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
            $response1 = curl_exec($curl);
            curl_close($curl);
            $res = json_decode($response1);
            if ($res) {
                $response["response_code"] = "200";
                $response["response_message"] =
                    "Your request has been successful submitted...! We will contact you as soon as possible. Thank you.";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "data is null";
        }

        echo json_encode($response);
        exit();
    }

    public function generateLink_reqbkp()
    {
        //print_r("ttt");exit;
        $data = json_decode(file_get_contents("php://input"));

        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }

        if ($data) {
            $id = $data->id;
            $type = $data->type;

            $result = $this->Common_model->generateLink($type, $id);


            if ($result) {

                $result->imagepath = base_url("uploads/blogs/") . $result->image;

                $response["status"] = "true";
                $response["res_code"] = 200;
                $response["res_status"] = "Success";
                $response["data"] = $result;
            } else {
                $response["status"] = "false";
                $response["res_code"] = 400;
                $response["res_status"] = "No available drivers";
            }
        } else {
            $response["response_code"] = 500;
            $response["response_message"] = "Data is null";
        }

        echo json_encode($response);
    }

    public function generateLink_req()
    {
        $data = json_decode(file_get_contents("php://input"));

        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            echo json_encode(["status" => "ok"]);
            exit();
        }

        if (!$data || empty($data->id) || empty($data->type)) {
            echo json_encode([
                "status" => "false",
                "res_code" => 400,
                "res_status" => "Invalid data"
            ]);
            exit();
        }

        $id   = $data->id;
        $type = strtolower($data->type);

        $title = "";
        $description = "";
        $imageName = "";

        /* ---------- FETCH DATA ---------- */

        switch ($type) {
            case "college":
                $detail = $this->College_model->getCollegeDetailsByID($id);
                $imageFolder = "college";
                $redirectUrl = "https://ohcampus.com/collegeDetails/$id";
                break;

            case "exam":
                $detail = $this->Exam_model->getExamDetails($id);
                $imageFolder = "exams";
                $redirectUrl = "https://ohcampus.com/examsdetails/$id";
                break;

            case "article":
                $detail = $this->Blog_model->getBlogsDetails($id);
                $imageFolder = "blogs";
                $redirectUrl = "https://ohcampus.com/articledetails/$id";
                break;

            case "event":
                $detail = $this->Event_model->getEventDetails($id);
                $imageFolder = "events";
                $redirectUrl = "https://ohcampus.com/eventdetails/$id";
                break;

            default:
                echo json_encode(["status" => "false", "res_status" => "Invalid type"]);
                exit();
        }
        $detail = json_decode(json_encode($detail), true);

        if (empty($detail) || !isset($detail[0])) {
            echo json_encode([
                "status" => "false",
                "res_code" => 404,
                "res_status" => "Data not found"
            ]);
            exit();
        }
        // print_r($detail);
        // exit;
        $title = !empty($detail[0]['title'])
            ? $detail[0]['title']
            : 'OhCampus';

        $description = !empty($detail[0]['description'])
            ? substr(strip_tags($detail[0]['description']), 0, 160)
            : 'Explore details on OhCampus';

        $imageName = !empty($detail[0]['image'])
            ? $detail[0]['image']
            : 'ohcampus_default.jpg';

        $imageUrl = "https://campusapi.ohcampus.com/uploads/$imageFolder/$imageName";

        /* ---------- FILE ---------- */

        $fileName = "{$type}_{$id}_" . time() . ".html";
        $folderPath = FCPATH . "uploads/sharing_link/";
        if (!is_dir($folderPath)) mkdir($folderPath, 0777, true);

        $shareUrl = base_url("uploads/sharing_link/$fileName");

        /* ---------- HTML ---------- */

        $htmlContent = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>{$title}</title>
<meta name="description" content="{$description}">

<meta property="og:url" content="{$shareUrl}">
<meta property="og:type" content="website">
<meta property="og:title" content="{$title}">
<meta property="og:description" content="{$description}">
<meta property="og:image" content="{$imageUrl}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{$title}">
<meta name="twitter:description" content="{$description}">
<meta name="twitter:image" content="{$imageUrl}">

<meta http-equiv="refresh" content="0;url={$redirectUrl}">
<script>window.location.href="{$redirectUrl}";</script>
</head>
<body></body>
</html>
HTML;

        file_put_contents($folderPath . $fileName, $htmlContent);

        echo json_encode([
            "status" => "true",
            "res_code" => 200,
            "res_status" => "Success",
            "data" => [
                "share_link" => $shareUrl,
                "redirect_url" => $redirectUrl
            ]
        ]);
    }

    public function getfooterNotification()
    {
        $data = json_decode(file_get_contents('php://input'));
        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data['status'] = 'ok';
            echo json_encode($data);
            exit;
        }
        // if (empty($_SERVER['HTTP_AUTHORIZATION'])) {
        //     $response["response_code"] = "401";
        //     $response["response_message"] = "Unauthorized";
        //     echo json_encode($response);
        //     exit();
        // }
        // $headers = apache_request_headers();
        // $token = str_replace("Bearer ", "", $headers['Authorization']);
        // $kunci = $this->config->item('jwt_key');
        // $userData = JWT::decode($token, $kunci);
        // Utility::validateSession($userData->iat, $userData->exp);
        // $tokenSession = Utility::tokenSession($userData);

        $result = $this->Common_model->getfooterNotification();
        // print_r($result[0]);exit;
        if ($result) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["response_data"] =  [$result[0]];;
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed";
        }

        echo json_encode($response);
        exit;
    }

    public function saveStudyAbroad()
    {
        $data = json_decode(file_get_contents('php://input'));

        /* ===== OPTIONS ===== */
        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            echo json_encode(['status' => 'ok']);
            exit;
        }

        /* ===== VALIDATE INPUT ===== */
        if (
            !$data ||
            empty($data->name) ||
            empty($data->email) ||
            empty($data->contact_no) ||
            empty($data->state_name) ||
            empty($data->city_name) ||
            empty($data->course) ||
            empty($data->country)
        ) {
            echo json_encode([
                "response_code" => 400,
                "response_message" => "All fields are required"
            ]);
            exit;
        }

        /* ===== PREPARE DATA ===== */
        $Arr = [
            'name'         => trim($data->name),
            'email'        => trim($data->email),
            'contact_no'   => trim($data->contact_no),
            'state_name'   => trim($data->state_name),
            'city_name'    => trim($data->city_name),
            'course'       => trim($data->course),
            'category'       => trim($data->category),
            'country'      => trim($data->country),
            'is_read'      => 0,
            'is_attended'  => 0,
            'is_attended_by' => null
        ];

        /* ===== SAVE ===== */
        $result = $this->Common_model->saveStudyAbroad($Arr);

        if ($result) {
            echo json_encode([
                "response_code" => 200,
                "response_message" => "Enquiry submitted successfully"
            ]);
        } else {
            echo json_encode([
                "response_code" => 500,
                "response_message" => "Failed to submit enquiry"
            ]);
        }
        exit;
    }

    public function getCountries()
    {
        /* ===== OPTIONS ===== */
        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            echo json_encode(['status' => 'ok']);
            exit;
        }

        /* ===== STATIC COUNTRY LIST ===== */
        $countries = [
            "United States",
            "United Kingdom",
            "Canada",
            "Australia",
            "Germany",
            "Ireland",
            "New Zealand",
            "France",
            "Netherlands",
            "Sweden",
            "Norway",
            "Denmark",
            "Switzerland",
            "Italy",
            "Spain",
            "Japan",
            "South Korea",
            "Singapore",
            "United Arab Emirates",
            "Malaysia"
        ];

        /* ===== RESPONSE ===== */
        echo json_encode([
            "response_code" => 200,
            "response_message" => "Country list fetched successfully",
            "data" => $countries
        ]);
        exit;
    }
}
