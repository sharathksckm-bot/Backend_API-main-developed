<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Methods: GET, POST');
/**
 * Notification Controller
 *
 * @category   Controllers
 * @package    Admin
 * @subpackage User
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    10 JAN 2024
 *
 * Class User handles all the operations related to displaying list, creating user, update, and delete.
 */

if (!defined("BASEPATH")) {
	exit("No direct script access allowed");
}

class Notification extends CI_Controller
{
	/**
	 * Constructor
	 *
	 * Loads necessary libraries, helpers, and models for the Notification controller.
	 ********************************************************************************/
	public function __construct()
	{
		parent::__construct();
		$data = json_decode(file_get_contents('php://input'));
		if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
			$data['status'] = 'ok';
			echo json_encode($data);
			exit;
		}
		$this->load->model("admin/Notification_model", "", true);
		$this->load->library('Utility');
		$this->load->library('fcm', [
			'serviceAccountKeyFile' => 'ohcampus-f845f-firebase-adminsdk-fbsvc-2d94a7e898.json',
			'projectID' => 'ohcampus-f845f'
		]);
	}


	public function getUserforNotification()
	{
		// echo "tttt";exit;
		$data = json_decode(file_get_contents('php://input'));
		if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
			$data['status'] = 'ok';
			echo json_encode($data);
			exit;
		}
		if ($data) {
			$headers = apache_request_headers();
			$token = str_replace("Bearer ", "", $headers['Authorization']);
			$kunci = $this->config->item('jwt_key');
			$userData = JWT::decode($token, $kunci);
			Utility::validateSession($userData->iat, $userData->exp);
			$tokenSession = Utility::tokenSession($userData);

			$search = isset($data->search) ? $data->search : '';

			$result = $this->Notification_model->getUserforNotification($search);

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


	/*   public function saveNotification()
		 {
		     //echo "tttt";exit;
			 $data = json_decode(file_get_contents('php://input'));
			 if($this->input->server('REQUEST_METHOD')=='OPTIONS')
			 {
				 $data['status']='ok';
				 echo json_encode($data);exit;
			 }
			 if($data)
			 {
				$headers = apache_request_headers();
				$token = str_replace("Bearer ", "", $headers['Authorization']);
				$kunci = $this->config->item('jwt_key');
				$userData = JWT::decode($token, $kunci);
				Utility::validateSession($userData->iat,$userData->exp);
        		$tokenSession = Utility::tokenSession($userData);
        		
        	  $title = $data->title;
        $message = $data->message;
        $userId = $data->userId;
        $status = $data->status;
  
      if (is_array($userId)) {
    $userId = implode(',', $userId);
}
        $insertData = array(
            'title' => $title,
            'message' => $message,
              'userId' => $userId,
             // 'status'=> $status
        );
		$userIdArray = explode(',', $userId);
		$result = $this->Notification_model->saveNotification($insertData);
		$deviceIdList = $this->Notification_model->getdeviceId($userIdArray);

		foreach ($deviceIdList as $row) {
			$deviceId = $row['deviceId'];
			$this->notificationTesting($deviceId);
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
		 }*/

	public function saveNotification()
	{
		//echo "ttttt";exit;
		$data = json_decode(file_get_contents('php://input'));

		if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
			$data['status'] = 'ok';
			echo json_encode($data);
			exit;
		}

		if ($data) {
			/* $headers = apache_request_headers();
        $token = str_replace("Bearer ", "", $headers['Authorization']);
        $kunci = $this->config->item('jwt_key');
        $userData = JWT::decode($token, $kunci);
     //   Utility::validateSession($userData->iat, $userData->exp);
        $tokenSession = Utility::tokenSession($userData);
		*/

			//print_r($tokenSession); exit ;

			$title = $data->title;
			$message = $data->message;
			$isAllselected = $data->isAllselected;
			$userId = isset($data->userId) ? $data->userId : [];

			if ($isAllselected == 1) {
				$allUserIds = $this->Notification_model->getAllUserIds();
				$userIdArray = array_column($allUserIds, 'id');
			} else {
				//echo "tttt";exit;
				if (is_array($userId)) {
					$userIdArray = $userId;
				} else {
					$userIdArray = explode(',', $userId);
				}
			}
			//print_r($userIdArray);exit;
			$userIdStr = implode(',', $userIdArray);

			$insertData = array(
				'title' => $title,
				'message' => $message,
				'userId' => $userIdStr,
				'is_sent' => 1
			);

			//	print_r($insertData); exit ;

			$result = $this->Notification_model->saveNotification($insertData);

			// Get device IDs and send notification
			$deviceIdList = $this->Notification_model->getdeviceId($userIdArray);
			//	print_r($deviceIdList); exit ;
			//	print_r($deviceIdList);exit;
			foreach ($deviceIdList as $row) {
				$deviceId = $row['deviceId'];
				// $this->notificationTesting($deviceId);
			}
			$this->notificationTesting($deviceId, $title, $message);
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



	public function notificationTesting($deviceId, $title, $message)
	{
		//print_r($deviceId);exit;
		//print_r($deviceId);exit;
		//$device_id ='ech7zLYYSZGIGxp4_HJdzz:APA91bF8ON-eBXlKEYL_CA6nrrQ9Zq04g3fr1QEihMJfUVAc9GhTZn_HoutVLQhPA5rgAsk05jV56RLRGygbUeQkdgpL0QWI7d0kj8dqZkUt14ZW2_p9OhI';
		$device_id = $deviceId;
		//	$device_id = 'clgf_GD9S0W6aP0od0L8Xw:APA91bH-MHOAqvqz32Uw6QtkofGxjksNxS7QflbvD9Q7kZKeWB2sTX4m_qXZ_7-Di0SGz7-fClbzT57vEgBeMrSMrk46_RuvntMU0haWIDxMo9F0haGvX8g';
		// print_r($device_id);exit;
		$message_body = $message;
		$message_header = $title;
		$type = "ohcampus";
		$company = "ohcampus";
		$this->fcm->serviceAccountKeyFile = "google-services.json";
		$this->fcm->projectID = "ohcampus-f845f";
		$response = $this->fcm->sendNotification($device_id, $message_header, $message_body, $type, $company);
		// echo "ttt";exit;
		//  print_r($response); 

	}



	/*   public function getNotificationList()
		 {
		    // echo "tttt";exit;
			 $data = json_decode(file_get_contents('php://input'));
			 if($this->input->server('REQUEST_METHOD')=='OPTIONS')
			 {
				 $data['status']='ok';
				 echo json_encode($data);exit;
			 }
		
				$headers = apache_request_headers();
				$token = str_replace("Bearer ", "", $headers['Authorization']);
				$kunci = $this->config->item('jwt_key');
				$userData = JWT::decode($token, $kunci);
				Utility::validateSession($userData->iat,$userData->exp);
        		$tokenSession = Utility::tokenSession($userData);

			   $result = $this->Notification_model->getNotificationList();
			 //  print_r($result);exit;
			 
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
			 
			   public function getNotificationById()
		 {
		     //echo "tttt";exit;
			 $data = json_decode(file_get_contents('php://input'));
			 if($this->input->server('REQUEST_METHOD')=='OPTIONS')
			 {
				 $data['status']='ok';
				 echo json_encode($data);exit;
			 }
			 if($data)
			 {
				$headers = apache_request_headers();
				$token = str_replace("Bearer ", "", $headers['Authorization']);
				$kunci = $this->config->item('jwt_key');
				$userData = JWT::decode($token, $kunci);
				Utility::validateSession($userData->iat,$userData->exp);
        		$tokenSession = Utility::tokenSession($userData);
        		
        	  $id = $data->notificationId;
			 $result = $this->Notification_model->getNotificationById($id);
			// print_r($result);exit;
			 
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
		 }*/


	public function getNotificationList()
	{
		//echo "tttt";exit;
		$data = json_decode(file_get_contents('php://input'));

		if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
			$data->status = 'ok';
			echo json_encode($data);
			exit;
		}
		if (empty($_SERVER['HTTP_AUTHORIZATION'])) {
			$response["response_code"] = "401";
			$response["response_message"] = "Unauthorized";
			echo json_encode($response);
			exit();
		}
		if ($data) {
			$userType = 13;
			//	$UserRole = $userData->data->type;

			$columns = array(
				0 => 'id',
				1 => 'title',
				2 => 'message',
				3 => 'name'
			);
			$limit = $data->length;
			$start = ($data->draw - 1) * $limit;
			$orderColumn = $columns[$data->order[0]->column];
			$orderDir = $data->order[0]->dir;
			$totalData = $this->Notification_model->countAll($userType);
			$totalFiltered = $totalData;
			if (!empty($data->search->value)) {

				$search = $data->search->value;
				$totalFiltered = $this->Notification_model->countFiltered($search, $userType);
				$Users = $this->Notification_model->getFiltered($search, $start, $limit, $orderColumn, $orderDir, $userType);
			} else {
				$Users = $this->Notification_model->getAll($start, $limit, $orderColumn, $orderDir, $userType);
			}

			$datas = [];
			foreach ($Users as $hstl) {
				//print_r($hstl);exit;
				//$Users = $this->User_model->getBookingId($hstl->id);

				$nestedData = array();
				$nestedData['id'] = $hstl->id;
				$nestedData['title'] = $hstl->title;
				$nestedData['message'] = $hstl->message;
				$nestedData['name'] = $hstl->name;

				$datas[] = $nestedData;
			}

			$json_data = array(
				'response_code' => "200",
				'response_message' => "Success",
				'draw' => intval($data->draw),
				'recordsTotal' => intval($totalData),
				'recordsFiltered' => intval($totalFiltered),
				'data' => $datas

			);

			echo json_encode($json_data);
		} else {
			$response["response_code"] = "500";
			$response["response_message"] = "Success";
			echo json_encode($response);
			exit();
		}
	}

	public function updateNotification()
	{
		//echo "tttt";exit;
		$data = json_decode(file_get_contents('php://input'));
		if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
			$data['status'] = 'ok';
			echo json_encode($data);
			exit;
		}
		if ($data) {
			$headers = apache_request_headers();
			$token = str_replace("Bearer ", "", $headers['Authorization']);
			$kunci = $this->config->item('jwt_key');
			$userData = JWT::decode($token, $kunci);
			Utility::validateSession($userData->iat, $userData->exp);
			$tokenSession = Utility::tokenSession($userData);

			$id = $data->id;
			//	print_r($id);exit;
			$title = $data->title;
			$message = $data->message;
			$userId = $data->userId;
			// $status = $data->status;

			if (is_array($userId)) {
				$userId = implode(',', $userId);
			}
			$updateData = array(
				'title' => $title,
				'message' => $message,
				'userId' => $userId,
				//  'status' => $status
			);


			$result = $this->Notification_model->updateNotification($updateData, $id);

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

	public function deleteNotification()
	{
		//echo "tttt";exit;
		$data = json_decode(file_get_contents('php://input'));
		if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
			$data['status'] = 'ok';
			echo json_encode($data);
			exit;
		}
		if ($data) {
			$headers = apache_request_headers();
			$token = str_replace("Bearer ", "", $headers['Authorization']);
			$kunci = $this->config->item('jwt_key');
			$userData = JWT::decode($token, $kunci);
			Utility::validateSession($userData->iat, $userData->exp);
			$tokenSession = Utility::tokenSession($userData);

			$id = $data->notificationId;
			$result = $this->Notification_model->deleteNotification($id);
			// print_r($result);exit;

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

	public function saveBulkNotification()
	{
		$data = json_decode(file_get_contents("php://input"));

		if (!$data) {
			echo json_encode([
				"response_code" => "500",
				"response_message" => "Invalid JSON"
			]);
			exit();
		}

		$title       = $data->title ?? "";
		$description = $data->description ?? "";
		$createdBy   = $data->created_by ?? "";
		$createdAt   = date("Y-m-d H:i:s");

		if (empty($title) || empty($description)) {
			echo json_encode([
				"response_code" => "400",
				"response_message" => "Title and Description are required"
			]);
			exit();
		}

		// Insert notification
		$notificationData = [
			"title"       => $title,
			"message" => $description,
			"is_sent"      => "0",
			"created_by"  => $createdBy,
			"created_at"  => $createdAt
		];

		$notificationId = $this->Notification_model->saveBulkNotification($notificationData);

		if ($notificationId) {

			$filterData = [
				"notification_id" => $notificationId,
				"category"     => $data->category ?? NULL,
				"subcategory"  => $data->subcategory ?? NULL,
				"exam"         => $data->exam ?? NULL,
				"state"        => $data->state ?? NULL,
				"city"         => $data->city ?? NULL,
				"created_at"        => $createdAt
			];

			$this->Notification_model->saveNotificationFilter($filterData);

			echo json_encode([
				"response_code" => "200",
				"response_message" => "Notification saved successfully",
				"notification_id" => $notificationId
			]);
		} else {
			echo json_encode([
				"response_code" => "400",
				"response_message" => "Failed to save notification"
			]);
		}

		$this->sendNotification();

		exit();
	}
	public function sendNotification()
	{
		$notifications = $this->Notification_model->getNotifications();

		if (empty($notifications)) {
			echo json_encode(["message" => "No notifications found"]);
			exit;
		}

		$finalOutput = [];

		foreach ($notifications as $row) {

			$filters = [
				'category'     => $row->category,
				'subcategory' => $row->subcategory,
				'exam'        => $row->exam,
				'state'       => $row->state,
				'city'        => $row->city,
			];

			// All matched users
			$userList = $this->Notification_model->getUsersByFilters($filters);
			$matchedUserIds = array_unique(array_column($userList, 'user_id'));

			if (empty($matchedUserIds)) {
				continue;
			}

			// Already sent users from notification table
			$alreadySentUserIds = [];
			if (!empty($row->user_ids)) {
				$alreadySentUserIds = array_map('intval', explode(',', $row->user_ids));
			}

			// Only new users
			$newUserIds = array_values(array_diff($matchedUserIds, $alreadySentUserIds));

			// If no new users, skip sending
			if (empty($newUserIds)) {
				continue;
			}

			// Push notification
			$deviceIdList = $this->Notification_model->getDeviceIds($newUserIds);

			foreach ($deviceIdList as $device) {
				$this->notificationTesting(
					$device['deviceId'],
					$row->title,
					$row->message
				);
			}

			// ✅ Append new users into notification.user_ids
			$updatedUserIds = array_unique(array_merge($alreadySentUserIds, $newUserIds));
			$updatedUserIdsString = implode(',', $updatedUserIds);

			// Update notification record
			$this->db->where('id', $row->notification_id)
				->update('notification', [
					'userid' => $updatedUserIdsString,
					'is_sent'  => 1,
					'sent_at'  => date('Y-m-d H:i:s')
				]);

			// Response output
			$finalOutput[] = [
				"title"    => $row->title,
				"message"  => $row->message,
				"userid" => $newUserIds
			];
		}

		echo json_encode($finalOutput);
		exit;
	}



	// FCM Push Notification Function
	public function notificationTestingbulk($deviceId, $title, $message)
	{
		$this->fcm->serviceAccountKeyFile = "google-services.json";
		$this->fcm->projectID = "ohcampus-f845f";
		$resp =  $this->fcm->sendNotification(
			$deviceId,
			$title,
			$message,
			"ohcampus",
			"ohcampus"
		);

		echo json_encode($resp);
	}
}
