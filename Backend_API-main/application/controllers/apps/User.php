<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST");

//header('Access-Control-Allow-Origin: *');
//header("Access-Control-Allow-Headers: *");
//header('Access-Control-Allow-Methods: GET, POST');
//date_default_timezone_set('Asia/Kolkata');

/**
 * User Controller
 *
 * @category   Controllers
 * @package    Web
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    30 JAN 2024
 *
 * Class User handles all the operations related to displaying list, creating User, update, and delete.
 */

if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}

error_reporting(E_ALL);
ini_set("display_errors", 1);
class User extends CI_Controller
{
    /*** Constructor ** Loads necessary libraries, helpers, and models for the User controller.*/
    public function __construct()
    {
        parent::__construct();
        $data = json_decode(file_get_contents('php://input'));
        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data['status'] = 'ok';
            echo json_encode($data);
            exit;
        }
        $this->load->model("apps/User_model", "", true);
        $this->load->library("Utility");
        //$this->load->library("session");
        $this->load->helper('date');
    }

    /**
     * Create User
     *
     * This function is responsible for creating a new user.
     */
    /* public function createUser()
    {
        //echo "testing...";exit;
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }

        //$this->session->userdata("start");
        if ($data) {
            
            $email = $data->email;

            $password = isset($data->password)?md5($data->password):'';

            $username = isset($data->name) ? $data->name : "";
          
            $phone = isset($data->mobile_no) ? $data->mobile_no : "";
              
            if($data->type = 'loginwithgoogle')
            {
               
                if(empty($phone))
                {
                     $response["response_code"] = "400";
                    $response["response_message"] =
                        "Please Enter Mobile No.";
                    echo json_encode($response);
                    exit();
                }
               
                $userExist = $this->User_model->checkUserExist($email);
                
               // print_r($userExist);exit;
               
            if ($userExist == true) {
                 $userList = $this->User_model->getUserDetailsByEmail($email);
               $response["response_code"] = "200";
                    $response["response_message"] =
                        "The user has been created successfully.";
                    $response["response_data"] = $userList;
                    echo json_encode($response);
                    exit();
            } else {
                $OtpVerified = $this->User_model->OtpVerified($email);
                $result = $this->User_model->createUser(
                    $email,
                    $password,
                    $username,
                    $phone
                );
                if ($result) {
                    
                    $response["response_code"] = "200";
                    $response["response_message"] =
                        "The user has been created successfully.";
                    $response["response_data"] = $result;
                } else {
                    $response["response_code"] = "400";
                    $response["response_message"] = "Failed";
                }
            }
            }
            else
            {
                $userExist = $this->User_model->checkUserExist($email);
            //print_r($userExist);exit;
            if ($userExist == true) {
                $response["response_code"] = "300";
                $response["response_message"] =
                    "This user already exists! Please try another or log in.";
            } else {
                $result = $this->User_model->createUser(
                    $email,
                    $password,
                    $username,
                    $phone
                );
                if ($result) {
                    $otp = rand(100000, 999999);
                    $Arr = ["otp" => $otp];
                    $updateOTP = $this->User_model->updateOTP($Arr, $result);
                    $sendOTP = $this->sendOTPMailToCustomer(
                        $username,
                        $otp,
                        $email
                    );
                    $response["response_code"] = "200";
                    $response["response_message"] =
                        "The OTP has been sent via email. Please check.";
                } else {
                    $response["response_code"] = "400";
                    $response["response_message"] = "Failed";
                }
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
    }*/


    /* public function createUser()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
      //  $this->session->userdata("start");
        if ($data) {
            $email = $data->email;
            $password = md5($data->password);
            $username = isset($data->name) ? $data->name : "";
            $phone = isset($data->mobile_no) ? $data->mobile_no : "";

            $userExist = $this->User_model->checkUserExist($email);
            if ($userExist == true) {
                $response["response_code"] = "300";
                $response["response_message"] =
                    "This user already exists! Please try another or log in.";
            } else {
                $result = $this->User_model->createUser(
                    $email,
                    $password,
                    $username,
                    $phone
                );
                if ($result) {
                    $otp = rand(100000, 999999);
                    $Arr = ["otp" => $otp];
                    $updateOTP = $this->User_model->updateOTP($Arr, $result);
                    $sendOTP = $this->sendOTPMailToCustomer(
                        $username,
                        $otp,
                        $email
                    );
                    $response["response_code"] = "200";
                    $response["response_message"] =
                        "The OTP has been sent via email. Please check.";
                } else {
                    $response["response_code"] = "400";
                    $response["response_message"] = "Failed";
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
    }*/
    ///------------
    /*   public function createUser()
{
    // echo "tttt";exit;
    $data = json_decode(file_get_contents("php://input"));
    if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
        $data["status"] = "ok";
        echo json_encode($data);
        exit();
    }

    if ($data) {
        $email = $data->email;
        $password = md5($data->password);
        $username = isset($data->name) ? $data->name : "";
        $phone = isset($data->mobile_no) ? $data->mobile_no : "";
        if($data->type = 'loginwithgoogle')
            {
               
                /*if(empty($phone))
                {
                     $response["response_code"] = "400";
                    $response["response_message"] =
                        "Please Enter Mobile No.";
                    echo json_encode($response);
                    exit();
                }
               
                $userExist = $this->User_model->checkUserExist($email);
                
                
               
            if ($userExist == true) {
                 $userList = $this->User_model->getUserDetailsByEmail($email);
                 //print_r($userList);exit;
               $response["response_code"] = "200";
                    $response["response_message"] =
                        "The user has been created successfully.";
                    $response["response_data"] = $userList;
                    echo json_encode($response);
                    exit();
            } else {
                $OtpVerified = $this->User_model->OtpVerified($email);
                $result = $this->User_model->createUser(
                    $email,
                    $password,
                    $username,
                    $phone
                );
                if ($result) {
                    $userList = $this->User_model->getUserDetailsByEmail($email);
                    $response["response_code"] = "200";
                    $response["response_message"] =
                        "The user has been created successfully.";
                    $response["response_data"] = $userList;
                } else {
                    $response["response_code"] = "400";
                    $response["response_message"] = "Failed";
                }
            }
            }
            else{
        $userExist = $this->User_model->checkUserExist($email);
       // print_r($userExist);exit;
        if ($userExist == true) {
            $response["response_code"] = "300";
            $response["response_message"] = "This user already exists! Please try another or log in.";
        } else {
            $result = $this->User_model->createUser($email, $password, $username, $phone);
              
            if ($result) {
                // Generate OTP and send email
            
                $otp = rand(100000, 999999);
                $Arr = ["otp" => $otp];
                $updateOTP = $this->User_model->updateOTP($Arr, $result);
                $sendOTP = $this->sendOTPMailToCustomer($username, $otp, $email);

                // JWT Token generation (similar to validateUser)
                $kunci = $this->config->item('jwt_key');
                $token['id'] = $result; // Assuming $result is the newly created user's ID
                $token['data'] = [
                    "email" => $email,
                    "username" => $username,
                    "phone" => $phone
                ];
                $date1 = new DateTime();
                $token['iat'] = $date1->getTimestamp();
                $token['exp'] = $date1->getTimestamp() + 60 * 15000; // Token expiration

                // Encode the token and return it
                $outputData['token'] = JWT::encode($token, $kunci);
                $outputData["user"] = $token['data'];
                $outputData["message"] = 'User created successfully. The OTP has been sent via email.';

                $response["response_code"] = "200";
                $response["response_status"] = "Success";
                $response["response_message"] = $outputData;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }
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
}*/

    public function createUser()
    {
        // echo "tttt";exit;
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }

        if ($data) {
            $form = $data->form;
            $email = $form->email;
            $type = isset($form->type) ? $form->type : "";
            $password = $form->password;
            $username = isset($form->name) ? $form->name : "";
            $phone = isset($form->mobilenum) ? $form->mobilenum : "";
            $deviceID = isset($data->deviceID) ? $data->deviceID : "";
            $platform = isset($data->platform) ? $data->platform : "";
            // print_r($platform);exit;
            if ($type == 'loginwithgoogle') {
                //echo "56789";exit;
                /*if(empty($phone))
                {
                     $response["response_code"] = "400";
                    $response["response_message"] =
                        "Please Enter Mobile No.";
                    echo json_encode($response);
                    exit();
                }*/

                $userExist = $this->User_model->checkUserExist($email);
                // print_r($userExist);exit;

                if ($userExist == '1') {
                    $userList = $this->User_model->getUserDetailsByEmail($email);
                    // print_r($userList);exit;
                    $response["response_code"] = "400";
                    /* $response["response_message"] =
                        "This user already exists! Please try another or log in.";
                   // $response["response_data"] = $userList;
                    echo json_encode($response);
                    exit();*/
                    $response["response_code"] = "200";
                    $response["response_message"] = "This user already exists! Please try another or log in.";
                    // $response["response_data"] = $userList;
                    echo json_encode($response);
                    exit();
                } else {

                    // print_r("tttt");exit;
                    $OtpVerified = $this->User_model->OtpVerified($email);
                    $otp = rand(100000, 999999);
                    $Arr = ["otp" => $otp];
                    $sendOTP = $this->sendOTPMailToCustomer($username, $otp, $email);

                    $result = $this->User_model->createUser(
                        $email,
                        $password,
                        $username,
                        $phone,
                        $deviceID,
                        $platform
                    );
                    // $updateOTP = $this->User_model->updateOTP($Arr, $result);
                    if ($result) {
                        $userList = $this->User_model->getUserDetailsByEmail($email);
                        $response["response_code"] = "200";
                        $response["response_message"] =
                            "The user has been created successfully.";
                        $response["response_data"] = $userList;
                    } else {
                        $response["response_code"] = "400";
                        $response["response_message"] = "Failed";
                    }
                }
            } else {
                $userExist = $this->User_model->checkUserExist($email);
                //  print_r($userExist);exit;
                if ($userExist == 1) {
                    $response["response_code"] = "300";
                    $response["response_message"] = "This user already exists! Please try another or log in.";
                } else {
                    //echo "1234";exit;
                    // print_r($password);exit;
                    $result = $this->User_model->createUser(
                        $email,
                        $password,
                        $username,
                        $phone,
                        $deviceID,
                        $platform
                    );

                    if ($result) {
                        // Generate OTP and send email
                        // echo "tttt";exit;
                        $otp = rand(100000, 999999);
                        $Arr = ["otp" => $otp];
                        $updateOTP = $this->User_model->updateOTP($Arr, $result);
                        $sendOTP = $this->sendOTPMailToCustomer($username, $otp, $email);

                        // JWT Token generation (similar to validateUser)
                        $kunci = $this->config->item('jwt_key');
                        $token['id'] = $result; // Assuming $result is the newly created user's ID
                        $token['data'] = [
                            "email" => $email,
                            "username" => $username,
                            "phone" => $phone
                        ];
                        $date1 = new DateTime();
                        $token['iat'] = $date1->getTimestamp();
                        $token['exp'] = $date1->getTimestamp() + 60 * 15000; // Token expiration

                        // Encode the token and return it
                        $outputData['token'] = JWT::encode($token, $kunci);
                        $outputData["user"] = $token['data'];
                        $outputData["message"] = 'User created successfully. The OTP has been sent via email.';

                        $response["response_code"] = "200";
                        $response["response_status"] = "Success";
                        $response["response_message"] = $outputData;
                    } else {
                        $response["response_code"] = "400";
                        $response["response_message"] = "Failed";
                    }
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


    public function signUpwithGoogle()
    {
        // echo "tttt";exit;
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }

        if ($data) {
            $username = $data->fname;
            $email = $data->email;
            $deviceID = $data->device_id;
            $platform = $data->platform;
            //   $type = $data->type;
            $otp = $data->otp;
            $password = $data->password;
            $phone = $data->mobile_no;
            if ($data->type == 'loginwithgoogle') {


                $userExist = $this->User_model->checkUserExist($email);

                if ($userExist == '1') {
                    $updatedeviceId = $this->User_model->updatedeviceId($email, $deviceID, $platform);
                    $userList = $this->User_model->getUserDetailsByEmail($email);

                    $response["response_code"] = "200";
                    $response["response_message"] =
                        "The user has been created successfully.";
                    $response["response_data"] = $userList;
                    echo json_encode($response);
                    exit();
                } else {
                    $OtpVerified = $this->User_model->OtpVerified($email);
                    $otp = rand(100000, 999999);
                    $Arr = ["otp" => $otp];
                    $sendOTP = $this->sendOTPMailToCustomer($username, $otp, $email);

                    $result = $this->User_model->createUser(
                        $email,
                        $password,
                        $username,
                        $phone,
                        $deviceID,
                        $platform
                    );
                    if ($result) {
                        $userList = $this->User_model->getUserDetailsByEmail($email);
                        $response["response_code"] = "200";
                        $response["response_message"] =
                            "The user has been created successfully.";
                        $response["response_data"] = $userList;
                    } else {
                        $response["response_code"] = "400";
                        $response["response_message"] = "Failed";
                    }

                    echo json_encode($response);
                    exit();
                }
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Type is wrong. Please pass 'loginwithgoogle' in the request.";
                echo json_encode($response);
                exit();
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
            echo json_encode($response);
            exit();
        }
    }
    /**
     * Send OTP Mail to Customer
     *
     * This function sends the OTP mail to the user for verification.
     */
    public function sendOTPMailToCustomer($username, $otp, $email)
    {
        //echo "tttt";exit;
        $senderName = "OhCampus Team";
        $bccArray = "";
        $toName = $username;
        $from = "enquiry@ohcampus.com";
        $to = $email;
        $emailMessage =
            '<!DOCTYPE html>
        <html lang="en">
        <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>OTP Email Template</title>
        </head>
        <body style="font-family: Arial, sans-serif; background-color: #f5f5f5; padding: 20px;">
        
        <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
            <div style="text-align: center; margin-bottom: 20px;">
                <img src="https://win.k2key.in/ohcampus/uploads/OhCampusLogo.png" alt="Company Logo" style="max-width: 200px;">
            </div>
            <div style="padding: 20px; background-color: #f0f0f0; border-radius: 5px;">
                <h2 style="color: #333333; margin-bottom: 10px;">OTP for Verification</h2>
                <p style="color: #666666; font-size: 16px; line-height: 1.6;">Dear ' .
            $username .
            ',</p>
                <p style="color: #666666; font-size: 16px; line-height: 1.6;">Your One-Time Password (OTP - valid for 5 MIN) for verification is: <strong>' .
            $otp .
            '</strong>. Please use this code to proceed with your action.</p>
                <p style="color: #666666; font-size: 16px; line-height: 1.6;">Please note that this OTP is valid for a single use and should not be shared with anyone.</p>
                <p style="color: #666666; font-size: 16px; line-height: 1.6;">If you did not request this OTP, please ignore this email or contact us immediately.</p>
                <p style="color: #666666; font-size: 16px; line-height: 1.6;">Best regards,<br>OhCampus IT Team<br>OhCampus<br><img src="https://win.k2key.in/ohcampus/uploads/OhCampusLogo.png" alt="Company Logo" style="max-width: 100px;">
                </div>
        </div>
        
        </body>
        </html>
        
        ';
        $subject = "OTP for Verification - OhCampus";
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
        // print_r($response);exit;
        $res = json_decode($response);
        return $res;
    }

    /**
     * Verify OTP
     *
     * This function verifies the OTP entered by the user.
     */
    public function verifyOTP()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $email = $data->email;
            $OTP = $data->Otp;
            $getOtpdata = $this->User_model->getOtpdata($email);
            if (
                !empty($getOtpdata[0]->otp) &&
                !empty($getOtpdata[0]->otp_timestamp)
            ) {
                $expiry_time = 5 * 60; // 5 minutes (in seconds)
                $current_date = date('Y-m-d H:i:s');
                $current_time = strtotime($current_date);

                $otp_timestamp_unix = strtotime($getOtpdata[0]->otp_timestamp);

                $timeDiff = $current_time - $otp_timestamp_unix;

                if (($current_time - $otp_timestamp_unix) <= $expiry_time) {
                    if ($OTP == $getOtpdata[0]->otp) {
                        $OtpVerified = $this->User_model->OtpVerified($email);
                        $response["response_code"] = "200";
                        $response["response_message"] =
                            "Your account has been created. Please sign in.";
                    } else {
                        $response["response_code"] = "400";
                        $response["response_message"] =
                            "Invalid OTP! Please try again.";
                    }
                } else {
                    $response["response_code"] = "300";
                    $response["response_message"] =
                        "OTP has been expired! Please try again.";
                }
            } else {
                $response["response_code"] = "600";
                $response["response_message"] = "OTP not found.";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null.";
        }
        echo json_encode($response);
        exit();
    }

    public function resendOTP()
    {
        $data = json_decode(file_get_contents("php://input"));

        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            return; // Return instead of exit
        }

        if ($data && isset($data->email)) {
            $email = $data->email;

            // Check if user exists
            $userDetails = $this->User_model->getUserdata($email);
            if (!$userDetails) {
                $response["response_code"] = "404";
                $response["response_message"] = "User not found.";
                echo json_encode($response);
                return; // Return instead of exit
            }

            $otp = rand(100000, 999999);
            $Arr = ["otp" => $otp];

            // Update OTP
            $updateOTP = $this->User_model->updateOTP($Arr, $userDetails[0]->id);
            if (!$updateOTP) {
                $response["response_code"] = "500";
                $response["response_message"] = "Failed to update OTP.";
                echo json_encode($response);
                return; // Return instead of exit
            }

            // Send OTP via email
            $sendOTP = $this->sendOTPMailToCustomer(
                $userDetails[0]->f_name,
                $otp,
                $email
            );

            // print_r($sendOTP);exit;
            if ($sendOTP) {
                $response["response_code"] = "200";
                $response["response_message"] = "OTP has been sent via email.";
                echo json_encode($response);
                return; // Return instead of exit
            } else {
                $response["response_code"] = "500";
                $response["response_message"] = "Failed to send OTP via email.";
                echo json_encode($response);
                return; // Return instead of exit
            }
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Bad request. Email not provided.";
            echo json_encode($response);
            return; // Return instead of exit
        }
    }

    public function ResetPass()
    {
        //echo "testing...";exit;
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $email = $data->email;
            $link = $data->link;
            $userExist = $this->User_model->checkUserExist($email);
            if ($userExist == true) {
                $userDetails = $this->User_model->getUserdata($email);
                $sendOTP = $this->sendPasswordResetLink(
                    $userDetails[0]->f_name,
                    $link,
                    $email
                );
                $response["response_code"] = "200";
                $response["response_message"] =
                    "Password reset link has been successfully sent to your email. Please check your inbox for further instructions on resetting your password.";
            } else {

                $response["response_code"] = "300";
                $response["response_message"] =
                    "This user not exists! Please enter valid email.";
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

    public function sendPasswordResetLink($username, $resetLink, $email)
    {
        $senderName = "OhCampus Team";
        $bccArray = "";
        $toName = $username;
        $from = "enquiry@ohcampus.com";
        $to = $email;
        $emailMessage =
            '<!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Email Template</title>
    </head>
    <body style="font-family: Arial, sans-serif; background-color: #f5f5f5; padding: 20px;">

    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="https://win.k2key.in/ohcampus/uploads/OhCampusLogo.png" alt="Company Logo" style="max-width: 200px;">
        </div>
        <div style="padding: 20px; background-color: #f0f0f0; border-radius: 5px;">
            <h2 style="color: #333333; margin-bottom: 10px;">Password Reset Link</h2>
            <p style="color: #666666; font-size: 16px; line-height: 1.6;">Dear ' .
            $username .
            ',</p>
            <p style="color: #666666; font-size: 16px; line-height: 1.6;">Please click the following link to reset your password:</p>
            <a href="' . $resetLink . '" style="display: inline-block; background-color: #007bff; color: #ffffff; text-decoration: none; padding: 10px 20px; border-radius: 5px; margin-bottom: 20px;">Reset Password</a>
            <p style="color: #666666; font-size: 16px; line-height: 1.6;">If you did not request a password reset, please ignore this email or contact us immediately.</p>
            <p style="color: #666666; font-size: 16px; line-height: 1.6;">Best regards,<br>OhCampus IT Team<br>OhCampus<br><img src="https://win.k2key.in/ohcampus/uploads/OhCampusLogo.png" alt="Company Logo" style="max-width: 100px;">
            </div>
    </div>

    </body>
    </html>

    ';
        $subject = "Password Reset Link - OhCampus";
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
        //print_r($response);exit;
        $res = json_decode($response);
        return $res;
    }

    public function UpdateNewPass()
    {
        $data = json_decode(file_get_contents("php://input"));

        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data->status = "ok";
            echo json_encode($data);
            exit();
        }

        $response = []; // Initialize response array

        if ($data) {
            $email = $data->email;
            $newPass = $data->password;
            $confirmPass = $data->confirmPassword;

            if ($newPass == $confirmPass) {
                $Arr = ['password' => md5($newPass)];
                $userDetails = $this->User_model->getUserdata($email);

                if (!empty($userDetails)) {
                    $id = $userDetails[0]->id;
                    $updatePass = $this->User_model->updateOTP($Arr, $id);

                    if ($updatePass) {
                        $response["response_code"] = "200";
                        $response["response_message"] = "Password updated successfully.";
                    } else {
                        $response["response_code"] = "500";
                        $response["response_message"] = "Failed to update password.";
                    }
                } else {
                    $response["response_code"] = "404";
                    $response["response_message"] = "User not found with the provided email.";
                }
            } else {
                $response["response_code"] = "300";
                $response["response_message"] = "The confirmation password does not match the password you entered. Please make sure to enter the same password in both fields.";
            }
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Data is null";
        }

        echo json_encode($response);
        exit();
    }

    ////--------------------

    /*   public function saveCounselingDetails()
    {
       // echo "tttt";exit;
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        
      //  print_r($data);exit;
      //  $this->session->userdata("start");
        if ($data) {
            $Studentname = $data->Studentname;
             $Studentname = $data->State;
              $Studentname = $data->City;
               $Studentname = $data->work;
                $Studentname = $data->Passingyear;
                $Studentname = $data->CourseInterest;
                  $Studentname = $data->mobileNumber;
            $username = isset($data->name) ? $data->name : "";
            $phone = isset($data->mobile_no) ? $data->mobile_no : "";

            $userExist = $this->User_model->checkUserExist($email);
            if ($userExist == true) {
                $response["response_code"] = "300";
                $response["response_message"] =
                    "This user already exists! Please try another or log in.";
            } else {
                $result = $this->User_model->createUser(
                    $email,
                    $password,
                    $username,
                    $phone
                );
                if ($result) {
                    $otp = rand(100000, 999999);
                    $Arr = ["otp" => $otp];
                    $updateOTP = $this->User_model->updateOTP($Arr, $result);
                    $sendOTP = $this->sendOTPMailToCustomer(
                        $username,
                        $otp,
                        $email
                    );
                    $response["response_code"] = "200";
                    $response["response_message"] =
                        "The OTP has been sent via email. Please check.";
                } else {
                    $response["response_code"] = "400";
                    $response["response_message"] = "Failed";
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
    }*/

    public function saveCounselingDetails()
    {
        // echo "testing..";exit;
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $Studentname = $data->StudentName;
            $State = $data->State;
            $City = $data->City;
            $passingstatus = $data->passingstatus;
            $Passingyear = $data->Passingyear;
            $CourseInterest = $data->CourseInterest;
            $mobileNumber = $data->mobileNumber;

            $registerdata = array(
                'Studentname' => $Studentname,
                'State' => $State,
                'City' => $City,
                'passingstatus' => $passingstatus,
                'Passingyear' => $Passingyear,
                'CourseInterest' => $CourseInterest,
                'mobileNumber' => $mobileNumber

            );
            //print_r($registerdata);exit;
            $result = $this->User_model->saveCounselingDetails($registerdata);


            if ($result) {
                $response["status"] = "true";
                $response["res_code"] = 1;
                $response["res_status"] = "Success";
            } else {
                $response["status"] = "false";
                $response["res_code"] = 2;
                $response["res_status"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }

        echo json_encode($response);
    }

    public function saveManagementSeat()
    {
        // echo "testing..";exit;
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $Studentname = $data->StudentName;
            $State = $data->State;
            $City = $data->City;
            $CourseLevel = $data->CourseLevel;
            $Category = $data->Category;
            $CourseInterest = $data->interestedcourses;
            $PreferredLocation = $data->PreferredLocation;
            $PreferredCollege = $data->PreferredCollege;
            $mobileNumber = $data->mobileNumber;


            /*    {
    "StudentName": "text box",
    "State": "dropdown",
    "City": "dropdown",
    "CourseLevel": "dropdown",
    "Category": "sssddff",
    "InterestedCourses": "eeerrr",
    "PreferredLocation": "dddd",
    "PreferredCollege": "dddd",
    "mobileNumber": "1234567890"
}*/


            $registerdata = array(
                'studentname' => $Studentname,
                'state' => $State,
                'city' => $City,
                'courseLevel' => $CourseLevel,
                'category' => $Category,
                // 'interestedcourses' => $CourseInterest,
                //'preferredlocation' => $PreferredLocation,
                // 'preferredcollege' => $PreferredCollege,

                'interestedcourses' => json_encode($CourseInterest), // Convert to JSON string
                'preferredlocation' => json_encode($PreferredLocation), // Convert to JSON string
                'preferredcollege' => json_encode($PreferredCollege), // Conv
                'mobilenumber' => $mobileNumber

            );
            //print_r($registerdata);exit;
            $result = $this->User_model->saveManagementSeat($registerdata);


            if ($result) {
                $response["status"] = "true";
                $response["res_code"] = 1;
                $response["res_status"] = "Success";
            } else {
                $response["status"] = "false";
                $response["res_code"] = 2;
                $response["res_status"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }

        echo json_encode($response);
    }


    public function getCity()
    {

        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }

        /*    {
    "StudentName": "text box",
    "State": "dropdown",
    "City": "dropdown",
    "CourseLevel": "dropdown",
    "Category": "sssddff",
    "InterestedCourses": "eeerrr",
    "PreferredLocation": "dddd",
    "PreferredCollege": "dddd",
    "mobileNumber": "1234567890"
}*/

        //print_r($registerdata);exit;
        $result = $this->User_model->getCity();
        // print_r($result);exit;

        if ($result) {
            $response["status"] = "true";
            $response["res_code"] = 1;
            $response["res_status"] = "Success";
            $response["res_data"] = $result;
        } else {
            $response["status"] = "false";
            $response["res_code"] = 2;
            $response["res_status"] = "Failed";
        }
        echo json_encode($response);
    }

    public function getState()
    {

        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }



        //print_r($registerdata);exit;
        $result = $this->User_model->getState();
        // print_r($result);exit;

        if ($result) {
            $response["status"] = "true";
            $response["res_code"] = 1;
            $response["res_status"] = "Success";
            $response["res_data"] = $result;
        } else {
            $response["status"] = "false";
            $response["res_code"] = 2;
            $response["res_status"] = "Failed";
        }
        echo json_encode($response);
    }

    public function getCityByState()
    {
        // echo "testing..";exit;
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $StateId = $data->stateId;



            //print_r($registerdata);exit;
            $result = $this->User_model->getCityByState($StateId);
            // print_r($result);exit;


            if ($result) {
                $response["status"] = "true";
                $response["res_code"] = 1;
                $response["res_status"] = "Success";
                $response["res_data"] = $result;
            } else {
                $response["status"] = "false";
                $response["res_code"] = 2;
                $response["res_status"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }

        echo json_encode($response);
    }

    public function getCollegeByCity()
    {
        // echo "testing..";exit;
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $cityId = $data->cityId;
            $subcategoryId = $data->subcategoryId;



            //print_r($registerdata);exit;
            $result = $this->User_model->getCollegeByCity($cityId, $subcategoryId);
            // print_r($result);exit;


            if ($result) {
                $response["status"] = "true";
                $response["res_code"] = 1;
                $response["res_status"] = "Success";
                $response["res_data"] = $result;
            } else {
                $response["status"] = "false";
                $response["res_code"] = 2;
                $response["res_status"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }

        echo json_encode($response);
    }

    public function saveSearchLogbkp()
    {
        $data = json_decode(file_get_contents("php://input"));

        if (!$data) {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
            echo json_encode($response);
            exit();
        }

        $user_id  = isset($data->user_id) ? $data->user_id : "";
        $type     = isset($data->type) ? $data->type : "";
        $searchKey = isset($data->search_key) ? $data->search_key : "";
        $platform = isset($data->platform) ? $data->platform : "";
        $createdAt = date("Y-m-d H:i:s");

        // Validation
        if (empty($type) || empty($searchKey)) {
            $response["response_code"] = "400";
            $response["response_message"] = "Search Type and Search Key are required";
            echo json_encode($response);
            exit();
        }

        // Convert single string to array if needed
        $typeArr = is_array($type) ? $type : [$type];
        $keyArr  = is_array($searchKey) ? $searchKey : [$searchKey];

        // Insert each record separately
        foreach ($typeArr as $index => $t) {
            $saveArr = [
                "user_id"     => $user_id,
                "search_type" => $t,
                "search_key"  => $keyArr[$index] ?? "",
                "platform"    => $platform,
                "is_notification_sent" => 0,
                "created_at"  => $createdAt
            ];

            $this->User_model->saveSearchLog($saveArr);
        }

        $response["response_code"] = "200";
        $response["response_message"] = "Search log saved successfully";
        echo json_encode($response);
        exit();
    }

    public function saveSearchLog()
{
    $data = json_decode(file_get_contents("php://input"));

    if (!$data) {
        echo json_encode(["response_code" => "500", "response_message" => "Data is null"]);
        exit();
    }

    $userId  = isset($data->userid) ? $data->userid : "";
    $createdAt = date("Y-m-d H:i:s");

    if (empty($userId)) {
        echo json_encode(["response_code" => "400", "response_message" => "User ID is required"]);
        exit();
    }

    // Define types and keys dynamically
    $logData = [
        "state"   => $data->state   ?? [],
        "subcategory"  => $data->course  ?? [],
        "category" => [$data->maincat ?? ""]
    ];

    // Loop and insert
    foreach ($logData as $type => $values) {
        $values = is_array($values) ? $values : [$values]; // ensure array

        foreach ($values as $value) {
            if (!empty($value)) {
                $saveArr = [
                    "user_id"     => $userId,
                    "search_type" => $type,
                    "search_key"  => $value,
                    "platform"    => "app",
                    "is_notification_sent" => 0,
                    "created_at"  => $createdAt
                ];

                $this->User_model->saveSearchLog($saveArr);
            }
        }
    }

    echo json_encode(["response_code" => "200", "response_message" => "Search log saved successfully"]);
    exit();
}


}
