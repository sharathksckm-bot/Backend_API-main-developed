<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Methods: GET, POST');
defined("BASEPATH") or exit("No direct script access allowed");
/**
 * Scholarship Controller
 *
 * @category   Controllers
 * @package    Web
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    02 FEB 2024
 *
 * Class Scholarship handles all the operations related to displaying list, creating Scholarship, update, and delete.
 */

class Scholarship extends CI_Controller
{
    /**
     * Scholarship constructor.
     * Loads necessary models and libraries.
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model("web/Scholarship_model");
        $this->load->library("Utility");
    }

    /**
     * getScholarships
     * Retrieves scholarships based on search criteria.
     */
    public function getScholarships()
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
            $search = isset($data->search) ? $data->search : "";

            $getScholarships = $this->Scholarship_model->getScholarships(
                $search
            );

            if ($getScholarships) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["data"] = $getScholarships;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }

        echo json_encode($response);
    }
}
