<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Methods: GET, POST');
if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}

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
        $this->load->model("web/Blog_model", "", true);
        $this->load->library("Utility");
    }

    public function getBlogs()
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
            $searchCategory = $data->searchCategory;
            $value = isset($data->value) ? $data->value : "";

            $result = $this->Blog_model->get_Blogs($searchCategory, $value);

            foreach ($result as $key => $value) {
                if (isset($result[$key]->image)) {
                    $result[$key]->image =
                        base_url("uploads/blogs/") . $result[$key]->image;
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
            $response["response_message"] = "Data is null";
        }
        echo json_encode($response);
        exit();
    }

    public function getLatestBlogs()
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
            $result = $this->Blog_model->getLatestBlogs($collegeid);
           // print_r($result);exit;
            $result1 = $this->Blog_model->getPopularBlogs($collegeid);

            foreach ($result as $key => $value) {
                if (isset($result[$key]->image)) {
                    $result[$key]->image =
                        base_url("uploads/blogs/") . $result[$key]->image;
                }
            }
            foreach ($result1 as $key => $value) {
                if (isset($result1[$key]->image)) {
                    $result1[$key]->image =
                        base_url("uploads/blogs/") . $result1[$key]->image;
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
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "data is null.";
        }
        echo json_encode($response);
        exit();
    }

    public function getBlogsDetails()
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
            $blogId = $data->blogId;
            $result = $this->Blog_model->getBlogsDetails($blogId);
            $addView = $this->Blog_model->increment_view($blogId);
            foreach ($result as $key => $img) {
                $result[$key]->imageName = $img->image;

                $result[$key]->image =
                    base_url() . "/uploads/blogs/" . $img->image;
                    /*$img = file_get_contents( $result[$key]->image); 
  
                        $base64ImgString = base64_encode($img); 
                        $result[$key]->base64ImgString = $base64ImgString;*/
            }
            $relatedBlogs = $this->Blog_model->relatedBlogs(
                $result[0]->categoryid,
                $blogId
            );

            foreach ($relatedBlogs as $key => $img) {
                $relatedBlogs[$key]->imageName = $img->image;

                $relatedBlogs[$key]->image =
                    base_url() . "/uploads/blogs/" . $img->image;
            }
            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["blogdetails"] = $result;
                $response["relatedblog"] = $relatedBlogs;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "data is null";
        }
        echo json_encode($response);
        exit();
    }

    public function getBlogCategory()
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
        $result = $this->Blog_model->getBlogCategory();
        if ($result) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["blogcategory"] = $result;
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed";
        }
        echo json_encode($response);
        exit();
    }
}

?>
