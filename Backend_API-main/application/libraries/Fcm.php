<?php
defined('BASEPATH') OR exit('No direct script access allowed');

//require_once 'S:\Ring_php_UAT/google-api-php-client/vendor/autoload.php';
//require_once 'https://ohcampus.com/home/ohcampus/public_html/campusapi.ohcampus.com/application/vendor/autoload.php';
require_once '/home/ohcampus/public_html/campusapi.ohcampus.com/google-api-php-client/vendor/autoload.php';
//require_once __DIR__ . '/vendor/autoload.php';
use Google\Auth\Credentials\ServiceAccountCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class Fcm {
    private $CI;
    public $serviceAccountKeyFile;
    public $projectID;

    public function __construct($params) {
        $this->CI =& get_instance();
        $this->serviceAccountKeyFile = $params['serviceAccountKeyFile'];
        $this->projectID = $params['projectID'];
    }

    /**
     * Get OAuth 2.0 Access Token using Service Account
     */
    private function getAccessToken() {
        // Define the scope for Firebase Cloud Messaging
        $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];

        // Use Google service account credentials
        $credentials = new ServiceAccountCredentials($scopes, $this->serviceAccountKeyFile);

        // Fetch the OAuth 2.0 access token
        $accessToken = $credentials->fetchAuthToken();

        // Return the access token
        return $accessToken['access_token'];
    }

    /**
     * Send Firebase Cloud Messaging (FCM) Notification
     */
    public function sendNotification($device_id, $message_header, $message_body, $type, $company) {
       // echo "ttt";exit;
        // public function sendNotification($token, $title, $body) {
        $accessToken = $this->getAccessToken();
        
        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectID}/messages:send";
        
        $notification = [
            'message' => [
                'token' => $device_id,
                'notification' => [
                    'title' => $message_header,
                    'body' => $message_body
                ],
                'android'=> [
                    'notification'=> [
                        'sound' => 'default',
                        'click_action' => 'FCM_PLUGIN_ACTIVITY',
                    ],
                ],
                'data' => [
                    'notifictionType' => $type,
                    'notification_heading' => $message_header,
                    'notification_body' => $message_body,
                    'company_name' => $company,
                    'priority'=> 'high'
                ]
            ]
        ];

        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json'
        ];
        

        // Send the notification using Guzzle HTTP Client
        $client = new Client();
        try {
            $response = $client->post($url, [
                'headers' => $headers,
                'json' => $notification
            ]);
            return $response->getBody()->getContents();
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                return $e->getResponse()->getBody()->getContents();
            }
            return $e->getMessage();
        }
    }
}
