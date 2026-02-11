<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST");
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
        $data = json_decode(file_get_contents('php://input'));
		if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
			$data['status'] = 'ok';
			echo json_encode($data);
			exit;
		}
        $this->load->model("apps/Blog_model", "", true);
        $this->load->library("Utility");
    }

    public function getBlogs()
    {
       // echo "tttt";exit;
        $raw = file_get_contents("php://input");
        $data = json_decode($raw);

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
        // If JSON body is missing/invalid (e.g., GET call or form-data),
        // fall back to POST/GET params so the API still works.
        if (!$data) {
            $arr = $this->input->post(NULL, true);
            if (empty($arr)) {
                $arr = $this->input->get(NULL, true);
            }
            if (!empty($arr)) {
                // Normalize statename into an array (supports ["Karnataka"], "Karnataka", "Karnataka,Tamil Nadu")
                if (isset($arr['statename']) && !is_array($arr['statename'])) {
                    $sn = trim((string)$arr['statename']);
                    $decoded = json_decode($sn, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $arr['statename'] = $decoded;
                    } elseif ($sn !== '') {
                        $arr['statename'] = array_values(array_filter(array_map('trim', explode(',', $sn)), 'strlen'));
                    } else {
                        $arr['statename'] = [];
                    }
                }
                $data = (object)$arr;
            }
        }

        if ($data) {
            $searchCategory = isset($data->searchCategory) ? $data->searchCategory : '';
            $value = isset($data->value) ? $data->value : "";
            $state_name = isset($data->statename) ? $data->statename : [];
            if (!is_array($state_name)) {
                $state_name = [$state_name];
            }

           // $result = $this->Blog_model->get_Blogs($searchCategory, $value,$state_name);
          // $result1 = $this->Blog_model->getBlogsDatas($searchCategory,$value);
           //print_r($result1);exit;
            // Merge both sources, then enforce "latest first" and "10 records total"
            $blogsA = $this->Blog_model->get_Blogs($searchCategory, $value, $state_name);
            $blogsB = $this->Blog_model->getBlogsDatas($searchCategory, $value);

            if (!is_array($blogsA)) {
                $blogsA = [];
            }
            if (!is_array($blogsB)) {
                $blogsB = [];
            }

            // Use array_merge (NOT array_merge_recursive) to avoid corrupting keys/values
            $merged = array_merge($blogsA, $blogsB);

            // Deduplicate by blog id (keep the most recent)
            $byId = [];
            foreach ($merged as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $id = isset($row['id']) ? (string)$row['id'] : null;
                if ($id === null || $id === '') {
                    continue;
                }
                $byId[$id] = $row;
            }

            $result = array_values($byId);

            // Sort latest first (created_date -> post_rate_date -> id)
            usort($result, function ($a, $b) {
                $aDate = isset($a['created_date']) && $a['created_date'] !== '' ? strtotime($a['created_date']) : null;
                $bDate = isset($b['created_date']) && $b['created_date'] !== '' ? strtotime($b['created_date']) : null;

                if ($aDate === null || $aDate === false) {
                    $aDate = isset($a['post_rate_date']) && $a['post_rate_date'] !== '' ? strtotime($a['post_rate_date']) : null;
                }
                if ($bDate === null || $bDate === false) {
                    $bDate = isset($b['post_rate_date']) && $b['post_rate_date'] !== '' ? strtotime($b['post_rate_date']) : null;
                }

                $aDate = ($aDate === null || $aDate === false) ? 0 : (int)$aDate;
                $bDate = ($bDate === null || $bDate === false) ? 0 : (int)$bDate;

                if ($aDate !== $bDate) {
                    return $bDate <=> $aDate; // latest first
                }

                $aId = isset($a['id']) ? (int)$a['id'] : 0;
                $bId = isset($b['id']) ? (int)$b['id'] : 0;
                return $bId <=> $aId;
            });

            // Only 10 records total
            $result = array_slice($result, 0, 10);

            foreach ($result as $key => $value) {
              // print_r($result[$key]['image']);exit; 
                if (isset($result[$key]['image'])) {
                    $result[$key]['image'] =
                        base_url("uploads/blogs/") . $result[$key]['image'];
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
            $collegeid = $data->collegeid;
            $result = $this->Blog_model->getLatestBlogs($collegeid);
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
	
	
	//----------------

    public function getBlogsDetails()
    {
		//echo "testing...";exit;
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
            $blogId = $data->blogId;
			
            $result = $this->Blog_model->getBlogsDetailsByCatId($blogId);
			
			//print_r($result);exit;
			
            $addView = $this->Blog_model->increment_view($blogId);
			if(!empty($result)){
   foreach ($result as $key => $img) {
                $result[$key]->imageName = $img->image;

                $result[$key]->image =
                    base_url() . "/uploads/blogs/" . $img->image;
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
	
	//------
	
	 public function getBlogsbyCategory()
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
        }	*/	
		
        if ($data) {
            $categoryId = $data->CategoryId;
            //$value = isset($data->value) ? $data->value : "";

            //$result = $this->Blog_model->get_Articles($searchCategory);
			 $result = $this->Blog_model->getLatestBlogsofCat($categoryId);
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
}

?>
