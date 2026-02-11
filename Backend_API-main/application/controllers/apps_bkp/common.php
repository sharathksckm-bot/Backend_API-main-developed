<?php
if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}
class common extends CI_Controller
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
        $this->load->model("apps/exam_model", "", true);
        $this->load->model("apps/Common_model", "", true);
		$this->load->model("apps/Review_model", "", true);
        $this->load->library('Utility');
    }
    /**
     * To get the navlist data
     */
    public function getNavList()
    {
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
            //$colleges = [['subFieldName' => 'Popular Colleges in India', 'subChild' => $popClgInIndia], ['subFieldName' => 'Top Rank Colleges', 'subChild' => $topRankClg], ['subFieldName' => 'Find College by Specification', 'subChild' => $specification], ['subFieldName' => 'All about ' . $CatName, 'subChild' => $all]];
            $colleges = [['subFieldName' => 'Popular Colleges in India', 'path' => 'populerclg'], ['subFieldName' => 'Top Rank Colleges', 'path' => 'toprankclg'], ['subFieldName' => 'Find College by Specification', 'path' => 'specialiclg'], ['subFieldName' => 'All about ' . $CatName, 'path' => 'allabout']];
            //$exams = [['subFieldName' => 'All Courses Exams', 'subChild' => $exam]];
            $exams = [['subFieldName' => 'All ' . $CourseName . ' Exams', 'Exams' => $exam]];
            $blog = $this->Campus_app_model->getBlog();
            $faQ = $this->Campus_app_model->getFaQ();
            //$resources = [['subFieldName' => 'All Articles', 'subChild' => $blog], ['subFieldName' => 'Questions and Discussions', 'subChild' => $faQ]];
            $resources = [['subFieldName' => 'All Articles'], ['subFieldName' => 'Questions and Discussions']];
            $about = [['subFieldName' => 'About_Us'], ['subFieldName' => 'Feedback'], ['subFieldName' => 'Privacy']];
            $result = [['fieldName' => $CatName . ' colleges', 'Child' => $colleges], ['fieldName' => 'Exams', 'Child' => $exams], ['fieldName' => $CatName . ' Resources', 'Child' => $resources], ['fieldName' => 'About Oh Campus', 'Child' => $about]];
            $fields = [$CatName . ' colleges' => $CatName . '_colleges', 'Exams' => 'Exams', $CatName . ' Resources' => $CatName . '_Resources', 'About Oh Campus' => 'About_Oh_Campus'];
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
                $response["response_message"] = "User is already exits";
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

        //$to = $email;
        $to = "saurabhople@gmail.com";
        $subject = "Registration OTP";
        $senderName = "OhCampus";
        //$from = "mailto:no-reply@ohCampus";
        $from = "saurabh.b@queenzend.com";

        //---------MAIL CONTENT GOES HERE------------------
        $massage = '<p style="color:black;">Dear <span style="color:#1b3b72!important;font-weight: bold;">' . $name . '</span></p>';
        $massage .= '<p style="text-align: justify;color:black!important">Your account validation code is: <b>' . $OTP . '</b></p>';
        $massage .= '<p style="text-align: justify;color:black!important">This code is essential for validating your account. Please use it to complete the validation process.</p>';
        $massage .= '<p style="text-align: justify;color:black!important">For security reasons, do not share this code with anyone.</p>';
        $massage .= '<p style="text-align: justify;color:black!important">Thank you for Registration</p>';

        //echo $massage;exit;
        //-------------------------------------------------
        $headers = ["api-key: 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", "Content-Type: application/json",];

        $url = "https://api.sendinblue.com/v3/smtp/email";

        $headers = [
            "api-key: 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",
            "Content-Type: application/json",
        ];
        $custJsonData = [
            "sender" => ["name" => $senderName, "email" => $from],
            "to" => [["name" =>  $name, "email" => $to]],
            "subject" => $subject,
            "htmlContent" => $massage,
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
                $nestedData["address"] = $clg->address;
                $nestedData["description"] = $clg->description;
                $nestedData["college_type"] = $clg->college_type;
                $nestedData["accreditation"] = $clg->accreditation;
                $nestedData["logo"] =  base_url() . "/uploads/college/" . $clg->logo;
                $nestedData["image"] = base_url() . "/uploads/college/" . $clg->image;
                $nestedData["package_type"] = $clg->package_type;
                $nestedData["city"] = $clg->city;
                $nestedData["country"] = $clg->country;
                $nestedData["phone"] = $clg->phone;
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
	
	public function getReviewDetails() {
        $data = json_decode(file_get_contents('php://input'));
            if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
                $data['status'] = 'ok';
                echo json_encode($data);
                exit;
            }
        if($data)
        {
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
        }
        else
        {
            $response['response_code'] = '500';
            $response['response_message'] = 'Data is null.';
        }
    
        echo json_encode($response);
    }
}
