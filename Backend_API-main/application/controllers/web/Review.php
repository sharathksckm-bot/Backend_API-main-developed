<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Methods: GET, POST');
defined("BASEPATH") or exit("No direct script access allowed");
/**
 * Review Controller
 *
 * @category   Controllers
 * @package    Web
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    03 FEB 2024
 *
 * Class Review handles all the operations related to displaying list, creating Review, update, and delete.
 */
class Review extends CI_Controller
{
    public function __construct()
    {
        /**
     * Review constructor.
     * Loads necessary models and libraries.
     */
        parent::__construct();
        $this->load->model("web/Review_model");
        // $this->load->model("admin/Common_model");
        $this->load->model("web/User_model", "", true);
        $this->load->model("web/College_model");
        $this->load->model("web/Common_model");
        $this->load->library("Utility");
    }

    /**
     * getReviewDetails
     * Retrieves review details for a specific college.
     */
    public function getReviewDetails()
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
            $collegeid = $data->collegeid;
            $Reviews = $this->Review_model->getReviewDetails($collegeid);

            if ($Reviews) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["data"] = $Reviews;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null.";
        }

        echo json_encode($response);exit;
    }

     /**
     * getCollegeTotalRate
     * Retrieves the total rating of a college.
     */
    public function getCollegeTotalRate()
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
            $collegeid = $data->collegeid;
            $TotalRate = $this->Review_model->getCollegeTotalRate($collegeid);
            $TotalComments = $this->Review_model->TotalComments($collegeid);

            if ($TotalRate || $TotalComments) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["data"] = $TotalRate;
                $response["TotalComments"] = $TotalComments;

            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null.";
        }

        echo json_encode($response);exit;
    }

    /**
     * voteReview
     * Adds a vote to a review.
     */
    public function voteReview()
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
            $userid = $data->user_id;
            $reviewid = $data->reviewid;
            $ishelpful = $data->ishelpful;
            $getRevById = $this->Review_model->getRevById($reviewid);
            $userArr = explode(",", $getRevById->voted_users);
            // print_r($getRevById);exit;

            $userArr[] = $userid; // Add the latest user ID to the existing array
            $userArr = array_filter(array_unique($userArr));
            $voteReview = $this->Review_model->voteReview(
                $reviewid,
                $userArr,
                $ishelpful
            );

            if ($voteReview) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["data"] = $voteReview;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null.";
        }

        echo json_encode($response);exit;
    }

    /**
     * addReview
     * Adds a new review.
     */
    public function addReview()
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
            $userid = $data->user_id;
            $collegeid = $data->collegeid;
            $courtype = $data->courtype;
            $courseid = $data->courseid;
            $title = $data->title;
            $getUserDetails = $this->User_model->getUserDetailsById($userid);

            $data = [
                "college_id" => $collegeid,
                "course_id" => $courseid,
                "course_type" => $courtype,
                "title" => $title,
                "placement_rate" => $data->placement_rate,
                "placement_desc" => $data->placement_description,
                "infrastructure_rate" => $data->infrastructure_rate,
                "infrastructure_desc" => $data->infrastructure_description,
                "faculty_rate" => $data->faculty_rate,
                "faculty_desc" => $data->faculty_description,
                "hostel_rate" => $data->hostel_rate,
                "hostel_desc" => $data->hostel_description,
                "campus_rate" => $data->campus_rate,
                "campus_desc" => $data->campus_description,
                "money_rate" => $data->money_rate,
                "money_desc" => $data->money_description,
            ];

            $voteReview = $this->Review_model->addReview($data);

            if ($voteReview) {
                $userid = $getUserDetails[0]->id;
                $email = $getUserDetails[0]->email;
                $fname = $getUserDetails[0]->f_name;
                $lname = $getUserDetails[0]->l_name;
                $name = $fname . " " . $lname;
                $clgDtl = $this->College_model->getCollegeDetailsByID(
                    $collegeid
                );
                $Arr = [
                    "user_name" => $userid,
                    "email" => $email,
                    "college" => $clgDtl[0]["id"],
                    "location" => $clgDtl[0]["city"],
                    "latest_activity" => "Review Added",
                ];

                // $Arr = ['user_name'=>$name,'email'=>$email,'location'=>'','latest_activity'=>''.$clgDtl[0]['title'].','.$clgDtl[0]['city'].' Review Added.'];
                $addUserActivity = $this->Common_model->addUserActivity($Arr);
                $ClgRepArr = [
                    "college" => $collegeid,
                    "no_of_articles_linked" => 0,
                    "no_of_brochures_download" => 0,
                    "no_of_application_submitted" => 0,
                    "no_of_que_asked" => 0,
                    "no_of_answeres" => 0,
                    "no_of_review" => 1,
                ];
                $checkcollegeReport = $this->Common_model->checkcollegeReport(
                    $collegeid
                );
                if ($checkcollegeReport > 0) {
                    $updateClgReport = $this->Common_model->updateClgReport(
                        $collegeid,
                        $ClgRepArr
                    );
                } else {
                    $saveClgReport = $this->Common_model->saveClgReport(
                        $ClgRepArr
                    );
                }
                $logArr = ["review_id" => $voteReview];
                $tableName = "review_log ";
                $addLog = $this->Common_model->addLog($logArr, $tableName);
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["data"] = $voteReview;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null.";
        }

        echo json_encode($response);exit;
    }

    /**
     * getPlacementRating
     * Retrieves placement rating for a college.
     */
    public function getPlacementRating()
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
            $collegeid = $data->collegeid;
            $Reviews = $this->Review_model->getPlacementRating($collegeid);

            if ($Reviews) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["data"] = $Reviews;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null.";
        }

        echo json_encode($response);exit;
    }

     /**
     * getInfrastructureRating
     * Retrieves infrastructure rating for a college.
     */

    public function getInfrastructureRating()
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
            $collegeid = $data->collegeid;
            $Reviews = $this->Review_model->getInfrastructureRating($collegeid);

            if ($Reviews) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["data"] = $Reviews;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null.";
        }

        echo json_encode($response);exit;
    }

     /**
     * getRatingList
     * Retrieves a list of ratings.
     */
    public function getRatingList()
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
        $Rating = $this->Review_model->getRatingList();

        if ($Rating) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["Rating"] = $Rating;
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed";
        }
        echo json_encode($response);exit;
    }
}
