<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With");
header('Access-Control-Allow-Methods: GET, POST');
/**
 * Authentication Controller
 *
 * @category   Controllers
 * @package    Admin
 * @subpackage Authentication
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    08 JAN 2024
 * 
 * Class Authentication handles secure login methods for administrators.
 */
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Authentication extends CI_Controller{

    /**
     * Constructor
     * 
     * Loads necessary libraries, helpers, and models for the Authentication controller.
     */
    function __construct() {
        parent::__construct();
        
        $this->load->library('session');
        $this->load->helper('url');
        $this->load->model('admin/Auth_model', '', TRUE);
    }

    /**
     * Login Method
     * 
     * Handles the administrator login functionality.
     *
     * @access public
     * @return void
     */
    public function Login()
    {
		//echo "ttt";exit;
        $data = json_decode(file_get_contents('php://input'));
		if($this->input->server('REQUEST_METHOD')=='OPTIONS')
		{
			$data['status']='ok';
			echo json_encode($data);exit;
		}
		if($data)
		{
			$username = $data->username;
			$password = md5($data->password);

			$result = $this->Auth_model->verifyUser($username, $password);
			// print_r($result);exit;
			if($result)
			{
				
				if ( strtolower($result['status']) !== 'active') 
				{
					$response['response_code'] = '300';
					$response['response_message'] = 'Your account is not active. Please contact administrator.';
				}
				else if (strtolower($result['type']) != 'contentmanager' && strtolower($result['type']) != 'college' && strtolower($result['type']) != 'contentwriter' && strtolower($result['type']) !== 'admin' && strtolower($result['type']) !== 'Support' && strtolower($result['type']) !== 'employee') 
				{
					
					$response['response_code'] = '300';
					$response['response_message'] = 'Sorry, you dont have rights to access the admin panel.';
				}
				else
				{
					$kunci = $this->config->item('jwt_key');
					$token['id'] = $result["userId"];  //From here
					$token['data'] = $result;
					$date1 = new DateTime();
					$token['iat'] = $date1->getTimestamp();
					$token['exp'] = $date1->getTimestamp() + 60 * 15000; //To here is to generate token
					$outputData['token'] = JWT::encode($token, $kunci); //This is the output token
					$outputData['time'] = $date1->getTimestamp();
					$outputData["user"] = $token['data'];
	
					$response['response_code'] = '1';
					$response['response_message'] = 'Success';
					$response['response_data'] = $outputData;
					// $response['user_data'] = $result;

				}
			}
			else
			{
				$response['response_code'] = '400';
				$response['response_message'] = 'Invalid username or password';
			}
		}
		else
		{
				$response['response_code'] = '500';
				$response['response_message'] = 'data is null';
		}

		echo json_encode($response);exit;

    }

	public function refresh_access_token(){
		if ($this->input->server('REQUEST_METHOD') == 'OPTIONS')
		{
			$data["status"] = "ok";
			echo json_encode($data);
			exit;
		}

		$headers = apache_request_headers();
	
		  if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode([
            'response_code' => 0,
            'response_message' => 'Authorization header missing',
            'response_data' => null
        ]);
        exit;
    }
	    try{
		$token_str = str_replace("Bearer ", "", $headers['Authorization']);
		$kunci = $this->config->item('jwt_key');
		$token = JWT::decode($token_str, $kunci);

		$token = json_decode(json_encode($token), true);
		
		$date1 = new DateTime();
		$token['iat'] = $date1->getTimestamp();
		$token['exp'] = $date1->getTimestamp() + 60 *  2000; //To here is to generate token
		$outputData['token'] = JWT::encode($token, $kunci); //This is the output token
		$outputData["user"] = $token['data'];

		$response['response_code']=1;
		$response['response_message']='Success';
		$response['response_data']=$outputData;

		
		}catch(Exception $e){

			$response['response_code']=1;
			$response['response_message']='Failed';
			$response['response_data']="Unautherised Token";
		}
		echo json_encode($response);exit;  
		

	}
}
