<?php

header("Access-Control-Allow-Origin: *");

header("Access-Control-Allow-Headers: *");

header("Access-Control-Allow-Methods: GET, POST");

if (!defined("BASEPATH")) {

    exit("No direct script access allowed");
}

class Common extends CI_Controller

{

    /**

     * Constructor

     *

     * Loads necessary libraries, helpers, and models for the app controller.

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

        $this->load->model("apps/Campus_app_model", "", true);

        $this->load->model("apps/exam_model", "", true);

        $this->load->model("apps/Common_model", "", true);

        $this->load->model("apps/Review_model", "", true);

        $this->load->model("apps/College_model", "", true);

        //$this->load->model("apps/User_model", "", true);

        $this->load->library('Utility');

        //	$this->load->library('m_pdf');

        $this->load->library('mpdf_lib');

        $config = [

            'tempDir' => APPPATH . 'tmp' // Or use FCPATH.'tmp' if you want outside of application folder

        ];

        $this->mpdf = new \Mpdf\Mpdf($config);
    }

    /**

     * To get the navlist data

     */

    public function getNavList()

    {

        // echo "ttt";exit;

        $data = json_decode(file_get_contents('php://input'));



        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {

            $data->status = 'ok';

            echo json_encode($data);

            exit;
        }

        if ($data) {

            $courseCatId = $data->courseCatId;

            $courseId = $data->courseId;

            // get category name

            $CatName = $this->Campus_app_model->getCatName($courseCatId);

            if ($CatName == 'Science') {

                $CatName = 'Arts & science';
            }

            $examId = $this->Campus_app_model->getExamCatId($CatName);

            $CourseName = $this->Campus_app_model->getSubCatByCoursesId($courseId);

            //print_r($examId);exit;

            $exam = $this->exam_model->getExamsByCategoryForNav($examId);

            //get Courses by category id and by this getting exams

            //$examCat = $this->Campus_app_model->getExamCat($CatName);

            //$exam = $this->Campus_app_model->getExam($examCat);

            //colleges data

            //$popClgInIndia = $this->Campus_app_model->getPopCollege($cat);

            //$topRankClg = $this->Campus_app_model->getCollegeListByRank($cat);

            //$specification = $this->Campus_app_model->getClgSpecification();

            //$all = 'All about ' . $CatName;

            $colleges = [['subFieldName' => 'Popular Colleges in India', 'path' => 'populerclg'], ['subFieldName' => 'Top Rank Colleges', 'path' => 'toprankclg'], ['subFieldName' => 'Find College by Specialization', 'path' => 'specialiclg']];

            //$colleges = [['subFieldName' => 'Popular Colleges in India', 'path' => 'populerclg'], ['subFieldName' => 'Top Rank Colleges', 'path' => 'toprankclg'], ['subFieldName' => 'Find College by Specification', 'path' => 'specialiclg'], ['subFieldName' => 'All about ' . $CatName, 'path' => 'allabout']];

            //$exams = [['subFieldName' => 'All ' . $CourseName . ' Exams','path' => 'coursewiseexam'], ['subFieldName' => $exam]];

            //print_r($CourseName->name);exit;

            $exams = [['subFieldName' => 'All ' . $CourseName->name . ' Exams', 'path' => 'coursewiseexam']];





            $blog = $this->Campus_app_model->getBlog();

            $faQ = $this->Campus_app_model->getFaQ();

            //$resources = [['subFieldName' => 'All Articles', 'subChild' => $blog], ['subFieldName' => 'Questions and Discussions', 'subChild' => $faQ]];

            $resources = [['subFieldName' => 'All Articles', 'path' => 'coursewisearticles']];

            $about = [['subFieldName' => 'Terms and Conditions', 'path' => 'termsncondition'], ['subFieldName' => 'Contact Us', 'path' => 'contactus'], ['subFieldName' => 'Who We Are?', 'path' => 'whoweare']];



            $result = [['fieldName' => $CatName . ' Colleges', 'Child' => $colleges], ['fieldName' => 'Exams', 'Child' => $exams], ['fieldName' => $CatName . ' Resources', 'Child' => $resources], ['fieldName' => 'About OhCampus', 'Child' => $about]];



            //$fields = [$CatName . ' colleges' => $CatName . '_colleges', 'Exams' => 'Exams', $CatName . ' Resources' => $CatName . '_Resources', 'About Oh Campus' => 'About_Oh_Campus'];



            $fields = [$CatName . ' Colleges' => $CatName . '_colleges', 'Exams' => 'Exams', $CatName . ' Resources' => $CatName . '_Resources'];



            if ($result) {

                $response["response_code"] = "200";

                $response["response_message"] = "Success";

                $response["Data"] = $result;

                $response["Fields"] = $fields;
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



    public function register()

    {

        $data = json_decode(file_get_contents('php://input'));



        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {

            $data->status = 'ok';

            echo json_encode($data);

            exit;
        }

        if ($data) {

            $name = $data->name;

            $email = $data->email;

            $pass = $data->password;

            $user = $this->Campus_app_model->checkUser($email);

            if ($user == true) {

                $response["response_code"] = "100";

                $response["response_message"] = "User already exists";
            } else {

                $OTP = mt_rand(100000, 999999);;

                $userData = array(

                    'f_name' => $name,

                    'email' => $email,

                    'password' => md5($pass),

                    'OTP' => $OTP

                );

                //print_r($userData);exit;

                $register = $this->Campus_app_model->register($userData);

                if ($register) {

                    $this->registerMail($email, $OTP, $name);

                    $response["response_code"] = "200";

                    $response["response_message"] = "Success";
                } else {

                    $response["response_code"] = "400";

                    $response["response_message"] = "Fail to register the user";
                }
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



    public function registerMail($email, $OTP, $name)

    {



        $to = $email;

        //$to = "ohcampusanalytics@gmail.com";

        $subject = "Registration OTP";

        $senderName = "OhCampus";

        //$from = "mailto:no-reply@ohCampus";

        $from = "noreply@ohcampus.com";



        //---------MAIL CONTENT GOES HERE------------------

        $message = '<p style="color:black;">Dear <span style="color:#1b3b72!important;font-weight: bold;">' . $name . '</span></p>';

        $message .= '<p style="text-align: justify;color:black!important">Your account validation code is: <b>' . $OTP . '</b></p>';

        $message .= '<p style="text-align: justify;color:black!important">This code is essential for validating your account. Please use it to complete the validation process.</p>';

        $message .= '<p style="text-align: justify;color:black!important">For security reasons, do not share this code with anyone.</p>';

        $message .= '<p style="text-align: justify;color:black!important">Thank you for Registration</p>';



        //echo $message;exit;

        //-------------------------------------------------

        /*$headers = ["api-key: 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", "Content-Type: application/json",];*/



        $url = "https://api.sendinblue.com/v3/smtp/email";



        $headers = [
            "api-key: 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",

            "Content-Type: application/json",

        ];

        $custJsonData = [

            "sender" => ["name" => $senderName, "email" => $from],

            "to" => [["name" =>  $name, "email" => $to]],

            "subject" => $subject,

            "htmlContent" => $message,

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



    public function validateOTP()

    {

        $data = json_decode(file_get_contents('php://input'));



        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {

            $data->status = 'ok';

            echo json_encode($data);

            exit;
        }

        if ($data) {

            $email = $data->email;

            $OTP = $data->OTP;



            $valOTP = $this->Campus_app_model->validateOTP($email);

            //echo $valOTP;exit;

            if ($valOTP == $OTP) {

                $response["response_code"] = "200";

                $response["response_message"] = "Success";
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

     * To get college data by search the text

     */

    public function getDataBySearch()

    {

        $data = json_decode(file_get_contents("php://input"));

        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {

            $data["status"] = "ok";

            echo json_encode($data);

            exit();
        }

        if ($data) {

            $text = $data->text;   //print_r('hello');exit;

            $Colleges = $this->Campus_app_model->getClgBySearch($text);

            //print_r($Colleges);exit;



            $result = [];

            foreach ($Colleges as $clg) {

                $nestedData["id"] = $clg->id;

                $nestedData["title"] = $clg->title;

                //$nestedData["phone"] = $clg->phone;

                $result[] = $nestedData;
            }

            if ($result) {

                $response["response_code"] = "200";

                $response["response_message"] = "Success";

                $response["Colleges"] = $result;
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



    public function getArticleList()

    {



        $data = json_decode(file_get_contents('php://input'));



        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {

            $data["status"] = "ok";

            echo json_encode($data);

            exit;
        }

        $result = $this->Campus_app_model->getBlogs();



        foreach ($result as $key => $value) {

            if (isset($result[$key]->image)) {

                $result[$key]->image = base_url('uploads/blogs/') . $result[$key]->image;
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



    public function getEventList()

    {

        $data = json_decode(file_get_contents('php://input'));



        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {

            $data->status = 'ok';

            echo json_encode($data);

            exit;
        }

        $result = $this->Campus_app_model->getEventList();

        foreach ($result as $key => $value) {

            if (isset($result[$key]->image)) {

                $result[$key]->image = base_url('uploads/events/') . $result[$key]->image;
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

        exit;
    }



    public function getLatestBlogs()

    {



        $data = json_decode(file_get_contents('php://input'));



        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {

            $data["status"] = "ok";

            echo json_encode($data);

            exit;
        }



        $result = $this->Common_model->getLatestBlogs();

        $result1 = $this->Common_model->getPopularBlogs();



        foreach ($result as $key => $value) {

            if (isset($result[$key]->image)) {

                $result[$key]->image = base_url('uploads/blogs/') . $result[$key]->image;
            }
        }

        foreach ($result1 as $key => $value) {

            if (isset($result1[$key]->image)) {

                $result1[$key]->image = base_url('uploads/blogs/') . $result1[$key]->image;
            }
        }



        if ($result) {

            $response["response_code"] = "200";

            $response["response_message"] = "Success";

            $response["latest_blogs"] = $result;

            $response["popular_blogs"] = $result1;
        } else {

            $response["response_code"] = "400";

            $response["response_message"] = "Failed";
        }

        echo json_encode($response);

        exit();
    }



    public function getReviewDetails()
    {

        $data = json_decode(file_get_contents('php://input'));

        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {

            $data['status'] = 'ok';

            echo json_encode($data);

            exit;
        }

        if ($data) {

            $collegeid = $data->collegeId;

            $Reviews = $this->Review_model->getReviewDetails($collegeid);



            if ($Reviews) {

                $response['response_code'] = '200';

                $response['response_message'] = 'Success';

                $response['data'] = $Reviews;
            } else {

                $response['response_code'] = '400';

                $response['response_message'] = 'Failed';
            }
        } else {

            $response['response_code'] = '500';

            $response['response_message'] = 'Data is null.';
        }



        echo json_encode($response);
    }



    //-----------------





    public function downloadBrochure()

    {

        //echo "testing...";exit;

        $data = json_decode(file_get_contents("php://input"));

        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {

            $data["status"] = "ok";

            echo json_encode($data);

            exit();
        }

        /*if (empty($_SERVER["HTTP_AUTHORIZATION"])) {

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

        }*/

        if ($data) {

            $collegeId = $data->collegeId;

            $userId = $data->userId;



            $clgDtl = $this->College_model->getCollegeDetailsByID($collegeId);

            //print_r($clgDtl);exit;

            $getUserDetails = $this->College_model->getUserDetailsById($userId);


            $brochures = $this->Common_model->getBrochure($collegeId);
            $brochure = isset($brochures[0]['file']) ? $brochures[0]['file'] : '';
            $filePath = FCPATH . "uploads/brochures/" . $brochure;

            if (!empty($brochures[0]) && file_exists($filePath)) {
                // echo"45678";exit;
                $brochure = $brochures[0]["file"];
                //print_r($brochures[0]["file"]);exit;
                $brochureName = $brochures[0]["title"];

                $fname = $getUserDetails[0]->f_name;

                $lname = $getUserDetails[0]->l_name;

                $name = $fname . " " . $lname;

                $email = $getUserDetails[0]->email;

                //print_r($email);exit;

                $senderName = "OhCampus Team";

                $bccArray = "";

                $toName = $name;

                $from = "enquiry@ohcampus.com";

                // $to = $getUserDetails[0]["email"];

                $to = $getUserDetails[0]->email;
                // print_r($to);exit;
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

                    <img src="https://ohcampus.com/assets/images/logo/logo.png" alt="Company Logo" style="max-width: 200px;">

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

                    <p style="color: #666666; font-size: 16px; line-height: 1.6;">Warm Regards,<br>OhCampus Team</p>

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

                //  print_r($brochure);exit;  
                //print_r($brochure);exit;
                // $brochure = '1707910964_HTML,CSS,JAVASCRIPT ,JQUERY GUIDE.pdf';

                $attachmentData = base64_encode(

                    file_get_contents("uploads/brochures/" . $brochure)

                );

                /* $attachmentData = base64_encode(

                    file_get_contents("uploads/brochures/" . $brochure)

                );*/


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

                // print_r($custJsonData);exit;

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

                //  print_r($res);exit;

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

                    // $this->load->model("admin/Common_model", "", true);

                    $checkcollegeReport = $this->Common_model->checkcollegeReport(

                        $collegeId

                    );

                    //print_r($checkcollegeReport);exit;



                    /*if ($checkcollegeReport > 0) {

                        $updateClgReport = $this->Common_model->updateClgReport(

                            $collegeId,

                            $ClgRepArr

                        );

						

						//print_r($updateClgReport);exit;

                    } else {

                        $saveClgReport = $this->Common_model->saveClgReport(

                            $ClgRepArr

                        );

                    }*/

                    $response1["response_code"] = "200";

                    $response1["response_message"] =

                        "Brochure sent sucessfully by mail";
                }
            } else {
                // echo"6789";exit;
                // $collegeDetails = $this->College_model->getCollegeDetailsByID($collegeId);
                $collegeDetails = $this->College_model->getCollegeDetailsByID(

                    $collegeId

                );



                //print_r($collegeDetails);exit;

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

                //print_r($courseDetails);exit;

                $content = $this->generatePDF(

                    $collegeDetails,

                    $courseDetails,

                    $HighlightsDetails

                );

                // print_r($content);exit;
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
                // if (file_exists($path . $filename)) {
                //     // File saved successfully
                //     echo "File saved: " . $path . $filename; exit; // for testing
                // } else {
                //     // File NOT saved
                //     echo "Error: PDF not saved!";
                //     exit;
                // }

                $pdf = base_url() . "/uploads/brochures/" . $filename;
                //print_r($pdf);exit;
                $brochuresData = [
                    "collegeid" => $collegeId,
                    "title"     => "brochure pdf",
                    "file"      => $filename,
                ];

                // Check existing brochures for college
                $existing = $this->College_model->getBrochuresByCollegeId($collegeId);

                if (!empty($existing)) {

                    if (count($existing) > 1) {

                        // Delete all and insert fresh
                        $this->College_model->deleteBrochuresByCollegeId($collegeId);
                        $this->College_model->insertBrochure($brochuresData);
                    } else {

                        // Update single brochure
                        $id = $existing[0]['id'];
                        $this->College_model->updateBrochure($id, $brochuresData);
                    }
                } else {
                    // Insert new if nothing exists
                    $this->College_model->insertBrochure($brochuresData);
                }


                sleep(3);

                $brochures = $this->Common_model->getBrochure($collegeId);

                // \print_r($brochures);
                // exit;

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
                // print_r($to);exit;
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

                    <img src="https://ohcampus.com/assets/images/logo/logo.png" alt="Company Logo" style="max-width: 200px;">

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

                    <p style="color: #666666; font-size: 16px; line-height: 1.6;">Warm Regards,<br>OhCampus Team</p>

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



                //print_r($attachmentData);exit;

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

                    // $this->load->model("admin/Common_model", "", true);

                    $checkcollegeReport = $this->Common_model->checkcollegeReport(

                        $collegeId

                    );

                    //print_r($checkcollegeReport);exit;

                    if ($checkcollegeReport > 0) {

                        $updateClgReport = $this->Common_model->updateClgReport(

                            $collegeId,

                            $ClgRepArr

                        );
                    } else {

                        $saveClgReport = $this->Common_model->saveClgReport(

                            $ClgRepArr

                        );
                    }



                    $response1["response_code"] = "200";

                    $response1["response_message"] =

                        "Brochure sent sucessfully by mail";
                }
            }
        } else {

            $response1["response_code"] = "500";

            $response1["response_message"] = "Data is null";
        }

        echo json_encode($response1);

        exit();
    }



    /*public function generatePDF(

        $collegeDetails,

        $courseDetails,

        $HighlightsDetails

    ) {

        // print_r($HighlightsDetails[0]['text']);exit;

        $collegeContent =

            '<h2>College Information</h2>

        <ul>

            <li style="font-weight:bold">' .

            $collegeDetails[0]["title"] .

            "," .

            $collegeDetails[0]["city"] .

            '</li>

            <li>' .

            $collegeDetails[0]["description"] .

            '</li>

        </ul>';

        $coursesContent .= ' <table>

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

        </table>';

        foreach ($HighlightsDetails as $key => $value) {

            $value = (array) $value;

            if (count($HighlightsDetails) > 1) {

                // If there are multiple highlights, wrap each one in <li> tags

                $highlightContent .= "<li>" . $value["text"] . "</li>";

            } else {

                // If there's only one highlight, don't use <ul> or <li> tags

                $highlightContent .= $value["text"];

            }

        }

        $template_name = "template/brochure.html";

        $content = file_get_contents($template_name);



        // Replace placeholders with actual content

        $content = str_replace(

            ["VAR_COLLEGE_DATA", "VAR_COURSE_DATA", "VAR_HIGHLIGHT_DATA"],

            [$collegeContent, $coursesContent, $highlightContent],

            $content

        );

        return $content;

    }*/

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

        </table>';

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







    //----

    public function getTrendingSpecilization()

    {

        // echo"te";exit;

        $data = json_decode(file_get_contents("php://input"));

        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {

            $data["status"] = "ok";

            echo json_encode($data);

            exit();
        }

        /* if (empty($_SERVER["HTTP_AUTHORIZATION"])) {

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

        }*/

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



    ///-----



    public function getCourseWiseExamList()

    {

        //echo "testing...";exit;

        $data = json_decode(file_get_contents('php://input'));



        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {

            $data->status = 'ok';

            echo json_encode($data);

            exit;
        }

        if ($data) {

            $courseCatId = $data->courseCatId;

            $courseId = $data->courseId;

            // get category name

            $CatName = $this->Campus_app_model->getCatNameMenu($courseCatId);



            //print_r($CatName);exit;



            if ($CatName == 'Science') {

                $CatName = 'Arts & science';
            }

            $examId = $this->Campus_app_model->getExamCatIdMenu($CatName);



            //print_r($examId);exit;



            $CourseName = $this->Campus_app_model->getSubCatByCoursesIdMenu($courseId);



            $exam = $this->exam_model->getExamsByCategoryForMenu($examId);



            //print_r($exam);exit;



            $colleges = [['subFieldName' => 'Popular Colleges in India', 'path' => 'populerclg'], ['subFieldName' => 'Top Rank Colleges', 'path' => 'toprankclg'], ['subFieldName' => 'Find College by Specification', 'path' => 'specialiclg'], ['subFieldName' => 'All about ' . $CatName, 'path' => 'allabout']];

            //$exams = [['subFieldName' => 'All Courses Exams', 'subChild' => $exam]];



            // $exams = [['subFieldName' => $exam]];



            $blog = $this->Campus_app_model->getBlog();

            $faQ = $this->Campus_app_model->getFaQ();

            //$resources = [['subFieldName' => 'All Articles', 'subChild' => $blog], ['subFieldName' => 'Questions and Discussions', 'subChild' => $faQ]];

            $resources = [['subFieldName' => 'All Articles', 'path' => 'coursewisearticles'], ['subFieldName' => 'Questions and Discussions', 'path' => 'coursewiseqna']];

            $about = [['subFieldName' => 'Terms and Conditions', 'path' => 'termsncondition'], ['subFieldName' => 'Contact Us', 'path' => 'contactus'], ['subFieldName' => 'Who we are?', 'path' => 'whoweare']];



            // $result = [['fieldName' => $CatName . ' colleges', 'Child' => $colleges], ['fieldName' => 'Exams', 'Child' => $exams], ['fieldName' => $CatName . ' Resources', 'Child' => $resources], ['fieldName' => 'About Oh Campus', 'Child' => $about]];



            //$result = [['Child' => $exams]];



            $result = $exam;



            foreach ($result as $key => $img) {



                //print_r($img->image);exit;

                $result[$key]->imageName = $img->image;



                $result[$key]->image =

                    base_url() . "/uploads/exams/" . $img->image;
            }

            //$categoryid = $result[0]['categoryid'];

            // print_r($exam);exit;



            //print_r($categoryId);exit;

            // $fields = [$CatName . ' colleges' => $CatName . '_colleges', 'Exams' => 'Exams', $CatName . ' Resources' => $CatName . '_Resources', 'About Oh Campus' => 'About_Oh_Campus'];

            if ($result) {

                $response["response_code"] = "200";

                $response["response_message"] = "Success";

                $response["Data"] = $result;

                // $response["Fields"] = $fields;

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





    public function getCourseWiseExamDetails()

    {

        // echo "testing...";exit;

        $data = json_decode(file_get_contents('php://input'));



        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {

            $data->status = 'ok';

            echo json_encode($data);

            exit;
        }

        if ($data) {

            $examId = $data->examId;

            // $courseId = $data->courseId;

            // get category name

            $CatName = $this->Campus_app_model->getCatNameMenu($courseCatId);







            /*  if ($CatName == 'Science') {

                $CatName = 'Arts & science';

            }*/

            $examId = $this->Campus_app_model->getExamCatIdMenu($CatName);



            $CourseName = $this->Campus_app_model->getSubCatByCoursesIdMenu($courseId);



            $exam = $this->exam_model->getExamsByCategoryForMenu($examId);







            //print_r($exam);exit;



            $colleges = [['subFieldName' => 'Popular Colleges in India', 'path' => 'populerclg'], ['subFieldName' => 'Top Rank Colleges', 'path' => 'toprankclg'], ['subFieldName' => 'Find College by Specification', 'path' => 'specialiclg'], ['subFieldName' => 'All about ' . $CatName, 'path' => 'allabout']];

            //$exams = [['subFieldName' => 'All Courses Exams', 'subChild' => $exam]];



            // $exams = [['subFieldName' => $exam]];



            $blog = $this->Campus_app_model->getBlog();

            $faQ = $this->Campus_app_model->getFaQ();

            //$resources = [['subFieldName' => 'All Articles', 'subChild' => $blog], ['subFieldName' => 'Questions and Discussions', 'subChild' => $faQ]];

            $resources = [['subFieldName' => 'All Articles', 'path' => 'coursewisearticles'], ['subFieldName' => 'Questions and Discussions', 'path' => 'coursewiseqna']];

            $about = [['subFieldName' => 'Terms and Conditions', 'path' => 'termsncondition'], ['subFieldName' => 'Contact Us', 'path' => 'contactus'], ['subFieldName' => 'Who we are?', 'path' => 'whoweare']];



            // $result = [['fieldName' => $CatName . ' colleges', 'Child' => $colleges], ['fieldName' => 'Exams', 'Child' => $exams], ['fieldName' => $CatName . ' Resources', 'Child' => $resources], ['fieldName' => 'About Oh Campus', 'Child' => $about]];



            //$result = [['Child' => $exams]];



            $result = $exam;



            foreach ($result as $key => $img) {



                //print_r($img->image);exit;

                $result[$key]->imageName = $img->image;



                $result[$key]->image =

                    base_url() . "/uploads/exams/" . $img->image;
            }



            //print_r($result);exit;



            // $fields = [$CatName . ' colleges' => $CatName . '_colleges', 'Exams' => 'Exams', $CatName . ' Resources' => $CatName . '_Resources', 'About OhCampus' => 'About_Oh_Campus'];

            if ($result) {

                $response["response_code"] = "200";

                $response["response_message"] = "Success";

                $response["Data"] = $result;

                // $response["Fields"] = $fields;

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



    //------

    public function getEngClgSpecialization()
    {

        //print_r("sss");exit;

        $data = json_decode(file_get_contents('php://input'));

        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {

            $data['status'] = 'ok';

            echo json_encode($data);

            exit;
        }

        if ($data) {

            $courseCatId = $data->courseCatId;



            $Reviews = $this->Common_model->getEngClgSpecialization($courseCatId);



            //print_r($Reviews);exit;



            if ($Reviews) {

                $response['response_code'] = '200';

                $response['response_message'] = 'Success';

                $response['data'] = $Reviews;
            } else {

                $response['response_code'] = '400';

                $response['response_message'] = 'Failed';
            }
        } else {

            $response['response_code'] = '500';

            $response['response_message'] = 'Data is null.';
        }



        echo json_encode($response);
    }





    public function savPredictAdmission()

    {

        $data = json_decode(file_get_contents("php://input"));

        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {

            $data["status"] = "ok";

            echo json_encode($data);

            exit();
        }

        /* if (empty($_SERVER["HTTP_AUTHORIZATION"])) {

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

        }*/

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

                    // "college" => $clgDtl[0]["id"],

                    // "location" => $clgDtl[0]["city"],

                    "latest_activity" => "Predcit Admission Application Submitted.",

                ];

                $addUserActivity = $this->Common_model->addUserActivity($Arr);

                // $ClgRepArr = ['college'=>$college,'no_of_articles_linked'=>0,'no_of_brochures_download'=>0,'no_of_application_submitted'=>1,'no_of_que_asked'=>0,'no_of_answeres'=>0];

                // $checkcollegeReport = $this->Common_model->checkcollegeReport($college);

                // if($checkcollegeReport > 0)

                // {

                //     $updateClgReport = $this->Common_model->updateClgReport($college,$ClgRepArr);



                // }

                // else

                // {

                //    $saveClgReport = $this->Common_model->saveClgReport($ClgRepArr);

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

                <img src="https://ohcampus.com/assets/images/logo/logo.png" alt="Company Logo" style="max-width: 200px;">

            </div>

            <div style="padding: 20px; background-color: #f0f0f0; border-radius: 5px;">

                <h2 style="color: #333333; margin-bottom: 10px;">Thank You for your Enquiry!</h2>

                <p style="color: #666666; font-size: 16px; line-height: 1.6;">Dear ' .

            $toName .

            ',</p>

                <p style="color: #666666; font-size: 16px; line-height: 1.6;">Thank you for contacting us regarding your enquiry. We have received your message and will get back to you as soon as possible with the information you requested.</p>

                <p style="color: #666666; font-size: 16px; line-height: 1.6;">If you have any further questions or need immediate assistance, please feel free to contact us.</p>

                <p style="color: #666666; font-size: 16px; line-height: 1.6;">Best Regards,<br>OhCampus Team<br>OhCampus.com<br><img src="https://ohcampus.com/assets/images/logo/logo.png" alt="Company Logo" style="max-width: 100px;">

                </div>

        </div>

        

        </body>

        </html>

        ';

        $subject = "Response to your Enquiry - OhCampus";

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





    public function getCareerList()

    {

        $data = json_decode(file_get_contents('php://input'));



        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {

            $data->status = 'ok';

            echo json_encode($data);

            exit;
        }

        // if($data)

        // {

        $categoryId = isset($data->categoryId) ? $data->categoryId : '';

        $result = $this->Common_model->getCareerList($categoryId);

        foreach ($result as $key => $value) {

            if (isset($result[$key]->image)) {

                $result[$key]->image = base_url('uploads/careers/') . $result[$key]->image;
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

        // }

        // else

        // {

        //     $response["response_code"] = "500";

        //     $response["response_message"] = "Data is Null";

        // }

        echo json_encode($response);

        exit;
    }



    public function getCareerDetails()

    {

        $data = json_decode(file_get_contents('php://input'));



        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {

            $data->status = 'ok';

            echo json_encode($data);

            exit;
        }

        if ($data) {

            $careerId = isset($data->careerId) ? $data->careerId : '';

            $result = $this->Common_model->getCareerDetails($careerId);

            foreach ($result as $key => $value) {

                if (isset($result[$key]->image)) {

                    $result[$key]->image = base_url('uploads/careers/') . $result[$key]->image;
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
        } else {

            $response["response_code"] = "500";

            $response["response_message"] = "Data is Null";
        }

        echo json_encode($response);

        exit;
    }



    public function getPlacementDataOfClg()

    {

        $data = json_decode(file_get_contents("php://input"));

        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {

            $data["status"] = "ok";

            echo json_encode($data);

            exit();
        }

        // if (empty($_SERVER["HTTP_AUTHORIZATION"])) {

        //     if (

        //         !is_object($data) ||

        //         !property_exists($data, "defaultToken") ||

        //         empty($data->defaultToken)

        //     ) {

        //         $response["response_code"] = "401";

        //         $response["response_message"] =

        //             "UNAUTHORIZED: Please provide an access token to continue accessing the API";

        //         echo json_encode($response);

        //         exit();

        //     }

        //     if ($data->defaultToken !== $this->config->item("defaultToken")) {

        //         $response["response_code"] = "402";

        //         $response["response_message"] =

        //             "UNAUTHORIZED: Please provide a valid access token to continue accessing the API";

        //         echo json_encode($response);

        //         exit();

        //     }

        // } else {

        //     $headers = apache_request_headers();

        //     $token = str_replace("Bearer ", "", $headers["Authorization"]);

        //     $kunci = $this->config->item("jwt_key");

        //     $userData = JWT::decode($token, $kunci);

        //     Utility::validateSession($userData->iat, $userData->exp);

        //     $tokenSession = Utility::tokenSession($userData);

        // }

        if ($data) {

            $searchYear = isset($data->searchYear)

                ? $data->searchYear

                : '';

            $searchCategory = isset($data->searchCategory)

                ? $data->searchCategory

                : "";

            $collegeId = isset($data->collegeId) ? $data->collegeId : "";

            // print_r($searchYear);exit;

            $result = $this->Common_model->getPlacementDataOfClg(

                $searchCategory,

                $searchYear,

                $collegeId

            );

            // $result2 = $this->getCommonalyAskedQ(

            //     $collegeId,

            //     $type = "PLACEMENT"

            // );



            if ($result || $result2) {

                $response["response_code"] = "200";

                $response["response_message"] = "Success";

                $response["placementlist"] = $result;

                // $response["Commonaly_Asked_Questions"] = $result2;

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

    public function getLevelById()

    {

        // echo "ttt";exit;

        $data = json_decode(file_get_contents('php://input'));



        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {

            $data->status = 'ok';

            echo json_encode($data);

            exit;
        }

        if ($data) {

            $collegeId = isset($data->Id) ? $data->Id : '';

            $result = $this->Common_model->getSubCategoryList($collegeId);

            // print_r($result);exit;

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

            $response["response_message"] = "Data is Null";
        }

        echo json_encode($response);

        exit;
    }



    public function getNotificationData()

    {

        // echo "ttt";exit;

        $data = json_decode(file_get_contents('php://input'));



        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {

            $data->status = 'ok';

            echo json_encode($data);

            exit;
        }

        $result = $this->Common_model->getNotificationData();

        // print_r($result);exit;

        if ($result) {

            $response["response_code"] = "200";

            $response["response_message"] = "Success";

            $response["response_data"] = $result;
        } else {

            $response["response_code"] = "400";

            $response["response_message"] = "Failed";
        }

        echo json_encode($response);

        exit;
    }



    public function getTrendingSpecilizationBySubCatId()

    {

        // echo "ttt";exit;

        $data = json_decode(file_get_contents('php://input'));



        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {

            $data->status = 'ok';

            echo json_encode($data);

            exit;
        }

        if ($data) {

            $subcat_Id = isset($data->subcat_Id) ? $data->subcat_Id : '';

            $statename = isset($data->statename) ? $data->statename : '';

            $result = $this->Common_model->getTrendingSpecilizationBySubCatId($subcat_Id, $statename);

            // print_r($result);exit;

            if ($result) {

                $response["response_code"] = "200";

                $response["response_message"] = "Success";

                $response["data"] = $result;
            } else {

                $response["response_code"] = "400";

                $response["response_message"] = "Failed";
            }
        } else {

            $response["response_code"] = "500";

            $response["response_message"] = "Data is Null";
        }

        echo json_encode($response);

        exit;
    }
}
