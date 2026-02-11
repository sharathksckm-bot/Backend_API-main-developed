<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Methods: GET, POST');

if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}

class BulkNotificationJob extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model("admin/Notification_model", "", true);
        $this->load->library('Utility');
        $this->load->library('fcm', [
            'serviceAccountKeyFile' => 'ohcampus-f845f-firebase-adminsdk-fbsvc-2d94a7e898.json',
            'projectID' => 'ohcampus-f845f'
        ]);
    }

 public function sendNotificationbkp()
{
    $notifications = $this->Notification_model->getNotifications();

    if (empty($notifications)) {
        echo json_encode(["message" => "No notifications found"]);
        exit;
    }

    $finalOutput = [];

    foreach ($notifications as $row) {
        //print_r($row);
        // Prepare filters
        $filters = [
            'category'      => $row->category,
            'subcategory'  => $row->subcategory,
            'exam'    => $row->exam,
            'state'   => $row->state,
            'city'    => $row->city,
        ];

        // Get filtered users
        $userList = $this->Notification_model->getUsersByFilters($filters);
        $userIds = array_unique(array_column($userList, 'user_id'));

        // // ❗ Skip if no user found
        if (empty($userIds)) {
            continue;
        }

        // // Add to response JSON
        $finalOutput[] = [
            "title"      => $row->title,
            "message"    => $row->message,
            "user_ids"   => $userIds
        ];
        // // Get device IDs for push
        $deviceIdList = $this->Notification_model->getDeviceIds($userIds);

        foreach ($deviceIdList as $device) {
            $this->notificationTesting($device['deviceId'], $row->title, $row->message);
        }

        // // update status after sending
        $this->Notification_model->updateSendStatus($userIds);
    }

    echo json_encode($finalOutput);
    exit;
}
public function sendNotification()
{
    $notifications = $this->Notification_model->getNotifications();

    if (empty($notifications)) {
        echo json_encode(["message" => "No notifications found"]);
        exit;
    }

    $finalOutput = [];
    $allUserIds = []; // collect all users across notifications

    foreach ($notifications as $row) {

        $filters = [
            'category'     => $row->category,
            'subcategory' => $row->subcategory,
            'exam'        => $row->exam,
            'state'       => $row->state,
            'city'        => $row->city,
        ];

        $userList = $this->Notification_model->getUsersByFilters($filters);
        $userIds  = array_unique(array_column($userList, 'user_id'));

        if (empty($userIds)) {
            continue;
        }

        // collect user ids (do NOT update status yet)
        $allUserIds = array_merge($allUserIds, $userIds);

        $finalOutput[] = [
            "title"    => $row->title,
            "message"  => $row->message,
            "user_ids" => $userIds
        ];

        $deviceIdList = $this->Notification_model->getDeviceIds($userIds);

        foreach ($deviceIdList as $device) {
            $this->notificationTesting(
                $device['deviceId'],
                $row->title,
                $row->message
            );
        }
    }

    // ✅ Update notification status ONLY ONCE after loop
    if (!empty($allUserIds)) {
        $allUserIds = array_unique($allUserIds);
        $this->Notification_model->updateSendStatus($allUserIds);
    }

    echo json_encode($finalOutput);
    exit;
}


    // FCM Push Notification Function
    public function notificationTesting($deviceId, $title, $message)
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
