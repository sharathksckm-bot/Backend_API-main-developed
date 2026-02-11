<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST");
/**
 * College Controller
 *
 * @category   Controllers
 * @package    Web
 * @subpackage Facilities
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    30 JAN 2024
 *
 * Class College handles all the operations related to displaying list, creating college, update, and delete.
 */

if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}
class Authentication extends CI_Controller
{
    /*** Constructor ** Loads necessary libraries, helpers, and models for the college controller.*/
    public function __construct()
    {
        parent::__construct();
        $this->load->model("apps/Authentication_model", "", true);
        $this->load->library("Utility");
    }
    public function validateUser()
    {
       // echo "tttt";exit;
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
			 //echo $valUser;exit;
            $userId = $data->username;
            $password = $data->password;
           $deviceid = isset($data->deviceid) ? trim($data->deviceid) : '';
            $platform = $data->platform;
            $valUser = $this->Authentication_model->valUser($userId);
            if ($valUser > 0) {
              
            $updatedeviceId = $this->Authentication_model->updatedeviceId($userId, $deviceid, $platform);
                $result = $this->Authentication_model->validateUser($userId);
				//print_r($result);exit;
				//echo $result[0]['password'] .'==='. md5($password);exit;
                foreach ($result as &$res) {
                  //  print_r(md5($password));exit; 
                    if ($res['password'] == md5($password)) {
                       
                        if (isset($result[0]['email']) && !empty($result[0]['email'])) {
                          //  print_r($result);exit;
                            $kunci = $this->config->item('jwt_key');
                            $token['id'] = $result[0]["id"];  //From here
                            $token['data'] = $result[0];
                          
                            $date1 = new DateTime();
                            $token['iat'] = $date1->getTimestamp();
                            $token['exp'] = $date1->getTimestamp() + 60 * 15000; //To here is to generate token
                            $outputData['token'] = JWT::encode($token, $kunci); //This is the output token
                            //$outputData['time'] = $date1->getTimestamp();
                            
                            $outputData["user"] = $token['data'];
                            $outputData["message"] = 'Login successfully.';
                            $response["response_code"] = "200";
                            $response["response_status"] = "Success";
                            $response["response_message"] = $outputData;

                            $Arr = ['token'=>$outputData['token']];
                            $updateToken = $this->Authentication_model->updateToken($Arr,$userId);
                        }
                    } else {
                        $response["response_code"] = "2";
                        $response["response_message"] = "Failed";
                    }
                }
            } else {
                $response["response_code"] = "2";
                $response["response_message"] = 'Failed';
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }

        echo json_encode($response);
    }
    public function refresh_access_token()
    {
         

        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data["status"] = "ok";
            echo json_encode($data);
            exit;
        }

        $headers = apache_request_headers();
        

        try {
            $token_str = str_replace("Bearer ", "", $headers['Authorization']);
            $kunci = $this->config->item('jwt_key');
            $token = JWT::decode($token_str, $kunci);

            $token = json_decode(json_encode($token), true);

            $date1 = new DateTime();
            $token['iat'] = $date1->getTimestamp();
            $token['exp'] = $date1->getTimestamp() + 60 *  2000; //To here is to generate token
            $outputData['token'] = JWT::encode($token, $kunci); //This is the output token
            $outputData["user"] = $token['data'];

            $response['response_code'] = 1;
            $response['response_message'] = 'Success';
            $response['response_data'] = $outputData;
        } catch (Exception $e) {

            $response['response_code'] = 1;
            $response['response_message'] = 'Failed';
            $response['response_data'] = "Unautherised Token";
        }
        echo json_encode($response);
        exit;
    }
    public function access_token()
    {
        
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $token = bin2hex(random_bytes(32)); // Generate a 64-character hexadecimal token

            session_start();
            $_SESSION['token'] = $token;

            echo json_encode(array('token' => $token));
            exit;
        }

        if (!isset($_SESSION['token']) || empty($_SESSION['token']) || !isset($_SERVER['HTTP_AUTHORIZATION'])) {
            http_response_code(401); 
            echo json_encode(array('error' => 'Token not provided'));
            exit;
        }

        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        $token = trim(str_replace('Bearer', '', $authHeader));

        session_start();
        if ($_SESSION['token'] !== $token) {
            http_response_code(401); // Unauthorized
            echo json_encode(array('error' => 'Invalid token'));
            exit;
        }

        echo json_encode(array('message' => 'Welcome to the protected API!'));
    }
}