<?php

header('Access-Control-Allow-Origin: *');

header("Access-Control-Allow-Headers: *");

header('Access-Control-Allow-Methods: GET, POST');

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

        $this->load->model("web/Authentication_model", "", true);

        $this->load->library("Utility");
    }

    // public function validateUserbkp()

    // {

    //     $data = json_decode(file_get_contents("php://input"));

    //     if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {

    //         $data["status"] = "ok";

    //         echo json_encode($data);

    //         exit();
    //     }

    //     if ($data) {

    //         $userId = $data->username;

    //         $password = $data->password;
    //         $type = isset($data->type) ? strtolower($data->type) : 'normal';

    //         $valUser = $this->Authentication_model->valUser($userId);

    //         if ($valUser > 0) {

    //             $result = $this->Authentication_model->validateUser($userId);
    //             //   print_r($result);exit;

    //             if ($result) {

    //                 foreach ($result as &$res) {

    //                     if ($res['password'] === md5($password)) {

    //                         if (isset($result[0]['email']) && !empty($result[0]['email'])) {



    //                             $kunci = $this->config->item('jwt_key');

    //                             $token['id'] = $result[0]["id"];  //From here

    //                             $token['data'] = $result[0];



    //                             $date1 = new DateTime();

    //                             $token['iat'] = $date1->getTimestamp();

    //                             $token['exp'] = $date1->getTimestamp() + 60 * 15000; //To here is to generate token

    //                             $outputData['token'] = JWT::encode($token, $kunci); //This is the output token

    //                             $outputData['userId'] = $result[0]["id"];

    //                             //$outputData['time'] = $date1->getTimestamp();

    //                             //$outputData["user"] = $token['data'];

    //                             $outputData["message"] = 'You have logged in successfully!';

    //                             $response["response_code"] = "200";

    //                             $response["response_status"] = "Success";

    //                             $response["response_message"] = $outputData;



    //                             $Arr = ['token' => $outputData['token']];

    //                             $updateToken = $this->Authentication_model->updateToken($Arr, $userId);
    //                         }
    //                     } else {

    //                         $response["response_code"] = "2";

    //                         $response["response_message"] = "failed.";
    //                     }
    //                 }
    //             } else {

    //                 $response["response_code"] = "3";

    //                 $response["result"] = $result;

    //                 $response["response_message"] = "User has not verified yet.";
    //             }
    //         } else {

    //             $response["response_code"] = "2";

    //             $response["response_message"] = 'Failed';
    //         }
    //     } else {

    //         $response["response_code"] = "500";

    //         $response["response_message"] = "Data is null";
    //     }



    //     echo json_encode($response);
    //     exit;
    // }

    public function validateUser()
{
    $data = json_decode(file_get_contents("php://input"));

    if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
        echo json_encode(["status" => "ok"]);
        exit();
    }

    if (!$data || !isset($data->username)) {
        echo json_encode([
            "response_code" => "500",
            "response_message" => "Invalid request data"
        ]);
        exit();
    }

    $email = $data->username;
    $password = isset($data->password) ? $data->password : '';
    $type = isset($data->type) ? strtolower($data->type) : 'normal';

    /* ----------------------------------------
       GOOGLE LOGIN FLOW
    ---------------------------------------- */
    if ($type === 'google') {

        // 1️⃣ Check user exists
        $user = $this->Authentication_model->getUserByEmail($email);

        // 2️⃣ If not exists → insert user
        if (!$user) {
            $insertData = [
                'email'      => $email,
                'password'   => null,
                'login_type' => 'google',
                'status'     => 1,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $userId = $this->Authentication_model->createGoogleUser($insertData);
            $user = $this->Authentication_model->getUserById($userId);
        }

        // 3️⃣ Generate JWT
        $kunci = $this->config->item('jwt_key');
        $date1 = new DateTime();

        $token = [
            'id'   => $user['id'],
            'data' => $user,
            'iat'  => $date1->getTimestamp(),
            'exp'  => $date1->getTimestamp() + (60 * 15000)
        ];

        $jwt = JWT::encode($token, $kunci);

        // 4️⃣ Save token
        $this->Authentication_model->updateToken(
            ['token' => $jwt],
            $email
        );

        echo json_encode([
            "response_code" => "200",
            "response_status" => "Success",
            "response_message" => [
                "token" => $jwt,
                "userId" => $user['id'],
                "message" => "Google login successful"
            ]
        ]);
        exit();
    }

    /* ----------------------------------------
       NORMAL LOGIN FLOW (EXISTING)
    ---------------------------------------- */

    $valUser = $this->Authentication_model->valUser($email);

    if ($valUser <= 0) {
        echo json_encode([
            "response_code" => "2",
            "response_message" => "Failed"
        ]);
        exit();
    }

    $result = $this->Authentication_model->validateUser($email);

    foreach ($result as $res) {
        if ($res['password'] === md5($password)) {

            $kunci = $this->config->item('jwt_key');
            $date1 = new DateTime();

            $token = [
                'id'   => $res["id"],
                'data' => $res,
                'iat'  => $date1->getTimestamp(),
                'exp'  => $date1->getTimestamp() + (60 * 15000)
            ];

            $jwt = JWT::encode($token, $kunci);

            $this->Authentication_model->updateToken(
                ['token' => $jwt],
                $email
            );

            echo json_encode([
                "response_code" => "200",
                "response_status" => "Success",
                "response_message" => [
                    "token" => $jwt,
                    "userId" => $res["id"],
                    "message" => "Login successful"
                ]
            ]);
            exit();
        }
    }

    echo json_encode([
        "response_code" => "2",
        "response_message" => "Invalid credentials"
    ]);
    exit();
}



    //     public function validateUser()

    // {

    //     // Decode incoming JSON

    //     $inputData = json_decode(file_get_contents("php://input"));



    //     // Handle OPTIONS request for CORS preflight

    //     if ($this->input->server("REQUEST_METHOD") === "OPTIONS") {

    //         echo json_encode(["status" => "ok"]);

    //         exit();

    //     }



    //     // Check if data exists

    //     if (!$inputData) {

    //         $response = [

    //             "response_code" => "500",

    //             "response_message" => "Data is null"

    //         ];

    //         echo json_encode($response);

    //         exit();

    //     }



    //     $userId = $inputData->username ?? null;

    //     $password = $inputData->password ?? null;



    //     // Validate if user exists

    //     $valUser = $this->Authentication_model->valUser($userId);



    //     if ($valUser <= 0) {

    //         $response = [

    //             "response_code" => "2",

    //             "response_message" => "Failed"

    //         ];

    //         echo json_encode($response);

    //         exit();

    //     }



    //     // Get full user details

    //     $result = $this->Authentication_model->validateUser($userId);



    //     if (!$result) {

    //         $response = [

    //             "response_code" => "3",

    //             "response_message" => "User has not verified yet."

    //         ];

    //         echo json_encode($response);

    //         exit();

    //     }



    //     // Check password

    //     if ($result[0]['password'] !== md5($password)) {

    //         $response = [

    //             "response_code" => "2",

    //             "response_message" => "Invalid password."

    //         ];

    //         echo json_encode($response);

    //         exit();

    //     }



    //     // If email exists, generate JWT token

    //     if (!empty($result[0]['email'])) {

    //         $kunci = $this->config->item('jwt_key');



    //         $tokenData = [

    //             'id'   => $result[0]["id"],

    //             'data' => $result[0],

    //             'iat'  => time(),

    //             'exp'  => time() + (60 * 15000)

    //         ];



    //         $jwtToken = JWT::encode($tokenData, $kunci);



    //         // Update token in DB

    //         $this->Authentication_model->updateToken(['token' => $jwtToken], $userId);



    //         $outputData = [

    //             "token"   => $jwtToken,

    //             "userId"  => $result[0]["id"],

    //             "message" => "You have logged in successfully."

    //         ];



    //         $response = [

    //             "response_code"    => "200",

    //             "response_status"  => "Success",

    //             "response_message" => $outputData

    //         ];

    //     } else {

    //         $response = [

    //             "response_code" => "4",

    //             "response_message" => "User email not found."

    //         ];

    //     }



    //     echo json_encode($response);

    //     exit();

    //     }



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
