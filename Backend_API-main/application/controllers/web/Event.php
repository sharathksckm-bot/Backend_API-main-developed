<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Methods: GET, POST');
/**
 * Event Controller
 *
 * @category   Controllers
 * @package    Web
 * @subpackage Event
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    26 JAN 2024
 *
 * Class Event handles all the operations related to displaying list, creating Event, update, and delete.
 */

if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}
class Event extends CI_Controller
{
    /**
     * Constructor
     *
     * Loads necessary libraries, helpers, and models for the Event controller.
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model("web/Event_model", "", true);
        $this->load->library("Utility");
    }

    /*** Get list of Event */
    public function getEventList()
    {
        $data = json_decode(file_get_contents("php://input"));

        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data->status = "ok";
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
            $value = isset($data->value) ? $data->value : "";
            $result = $this->Event_model->getEventList($value);
            foreach ($result as $key => $value) {
                if (isset($result[$key]->image)) {
                    $result[$key]->image =
                        base_url("uploads/events/") . $result[$key]->image;
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

    public function getEventDetails()
    {
        $data = json_decode(file_get_contents("php://input"));

        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data->status = "ok";
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
            $eventid = $data->eventid;
            $result = $this->Event_model->getEventDetails($eventid);
          // print_r($result[0]->event_category);exit;
           
           
           foreach($result as $eventcategory){
               $event_categoryId = $eventcategory->event_category;
              
               $event_categories = explode(",", $event_categoryId);
           }
            $getEventCatname = $this->Event_model->getEventCatname($event_categories);
           // print_r($getEventCatname);exit;
            foreach ($result as $key => $value) {
                if (isset($result[$key]->image)) {
                    $result[$key]->image =
                        base_url("uploads/events/") . $result[$key]->image;
                }
                $getUpcomingEvents = $this->Event_model->getUpcomingEvents(
                    $result[$key]->college_id
                );
                
            }
            
            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["event_details"] = $result;
                $response["upcoming_events"] = $getUpcomingEvents;
                $response["eventCatename"] = $getEventCatname;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null.";
        }
        echo json_encode($response);
        exit();
    }
}
