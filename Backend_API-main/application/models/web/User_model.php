<?php

class User_model extends CI_Model
{
    private $table = 'college';

    public function __construct()
    {

        parent::__construct(); {
            $this->load->database();
        }
    }
    public function checkUserExist($email)
    {
        // $this->db->where('email', $email);
        // $this->db->where('is_otp_verified', 1);

        // $query = $this->db->get('users');

        // if ($query->num_rows() > 0) {
        //     return true; 
        // } else {
        //     return false;
        // }
        // 		echo $this->db->last_query();exit;

        $this->db->select('id,f_name,is_otp_verified');
        $this->db->from('users');
        $this->db->where('email', $email);
        $query = $this->db->get()->result();
        return $query;
    }

    public function createUser($email, $password, $userName, $phone)
    {
        $userData = array(
            'email' => $email,
            'password' => $password,
            'f_name' => $userName,
            'user_type' => 2,
            'user_status' => 2,
            'phone' => $phone
        );

        $this->db->insert('users', $userData);
        //echo $this->db->last_query();        exit;
        return $this->db->insert_id();
    }

    public function getUserDetailsById($id)
    {
        $this->db->select('*');
        $this->db->from('users');
        $this->db->where('id', $id);
        $query = $this->db->get()->result();
        return $query;
    }

    public function updateOTP($Arr, $id)
    {
        $this->db->where("id", $id);
        $query = $this->db->update('users', $Arr);
        return $query;
    }

    public function OtpVerified($email)
    {
        $Arr = ['is_otp_verified ' => 1];
        $this->db->where("email", $email);
        $query = $this->db->update('users', $Arr);
        return $query;
    }
    public function getOtpdata($email)
    {
        $this->db->select('id,email,otp,otp_timestamp');
        $this->db->from('users');
        $this->db->where('email', $email);
        $query = $this->db->get()->result();
        return $query;
    }
    public function getUserdata($email)
    {
        $this->db->select('id,f_name,l_name,email');
        $this->db->from('users');
        $this->db->where('email', $email);
        $query = $this->db->get()->result();
        return $query;
    }

    public function updateSearchNotificationStatus($logId, $message = "")
    {
        $update = [
            "is_notification_sent" => 1,
            "notification_sent_at" => date("Y-m-d H:i:s")
        ];

        return $this->db->where("id", $logId)->update("search_logs", $update);
    }

    public function saveSearchLog($data)
    {
        return $this->db->insert("search_logs", $data);
    }
}
