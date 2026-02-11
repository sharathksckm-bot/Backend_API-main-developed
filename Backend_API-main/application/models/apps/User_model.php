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
   /* public function checkUserExist($email)
    {
		//print_r($email);exit;
        $this->db->where('email', $email);
        $this->db->where('is_otp_verified', 1);

        $query = $this->db->get('users');
//echo $this->db->last_query();exit;
       if ($query->num_rows() > 0) {
            return true; 
        } else {
               return false;
        }
    }*/
    
    
    public function checkUserExist($email)
{
  
    $this->db->where('email', $email);
    $this->db->where('is_otp_verified', 1);
    $query = $this->db->get('users');

    if ($query->num_rows() > 0) {
        return true; 
    } else {
        $this->db->where('email', $email);
        $this->db->where('is_otp_verified', 0);
        $query = $this->db->get('users');

        if ($query->num_rows() > 0) {
           
            $this->db->where('email', $email);
            $this->db->update('users', ['is_otp_verified' => 1]);

            if ($this->db->affected_rows() > 0) {
                return true; 
            } else {
                return false; 
            }
        }
        return false; 
    }
}


    public function createUser($email, $password, $userName,$phone,$deviceID,$platform)
    {
        $userData = array(
            'email' => $email,
            'password' => md5($password),
            'f_name' => $userName,
            'user_type' => 2,
            'user_status' => 2,
            'phone'=>$phone,
            'deviceId'=>$deviceID,
            'platform'=>$platform,
        );

        $this->db->insert('users', $userData);
        //echo $this->db->last_query();        exit;
        return $this->db->insert_id();
    }

    public function getUserDetailsById($id)
    {
        echo "tttt";exit;
        $this->db->select('*');
        $this->db->from('users');
        $this->db->where('id',$id);
        $query = $this->db->get()->result();
        return $query;
    }
    
      public function getUserDetailsByIdbkp($userId)
    {
      //  echo "tttt";exit;
        $this->db->select('*');
        $this->db->from('users');
        $this->db->where('id',$userId);
        $query = $this->db->get()->result();
        return $query;
    }
public function getUserDetailsByEmail($email)
    {
        $this->db->select('*');
        $this->db->from('users');
        $this->db->where('email',$email);
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
    $Arr = ['is_otp_verified '=>1];
    $this->db->where("email", $email);
        $query = $this->db->update('users', $Arr);
        return $query;
}
    public function getOtpdata($email)
    {
        $this->db->select('id,email,otp,otp_timestamp');
        $this->db->from('users');
        $this->db->where('email',$email);
        $query = $this->db->get()->result();
        return $query;
    }
    public function getUserdata($email)
    {
        $this->db->select('id,f_name,l_name,email');
        $this->db->from('users');
        $this->db->where('email',$email);
        $query = $this->db->get()->result();
        return $query;
    }
    
    //-----
    
    public function saveCounselingDetails($registerdata){
      //  print_r($registerdata);exit;
         $this->db->insert('CounselingDetails', $registerdata);
         
    // Check if the insert was successful
    if ($this->db->affected_rows() > 0) {
        return true;  // Return true if data was inserted
    } else {
        return false; // Return false if insertion failed
    }
    }
    
     public function saveManagementSeat($registerdata){
      //  print_r($registerdata);exit;
         $this->db->insert('managementseat', $registerdata);
         
    // Check if the insert was successful
    if ($this->db->affected_rows() > 0) {
        return true;  // Return true if data was inserted
    } else {
        return false; // Return false if insertion failed
    }
    }
    
    public function getCity()
{
    // Select all fields from the 'management_seats' table
    $this->db->select('*');
    $this->db->from('city');

   

    // Execute the query and get the result
    $query = $this->db->get();

    // Return the result as an array of records
    return $query->result_array();
}

   public function getState()
{
    // Select all fields from the 'management_seats' table
    $this->db->select('*');
    $this->db->from('state');
  $this->db->order_by('statename', 'ASC');
   

    // Execute the query and get the result
    $query = $this->db->get();

    // Return the result as an array of records
    return $query->result_array();
}

  public function getCityByState($StateId)
{
    //echo "tttt";exit;
    // Select all fields from the 'management_seats' table
    $this->db->select('*');
    $this->db->from('city');
    $this->db->where('stateid',$StateId);

   

    // Execute the query and get the result
    $query = $this->db->get();

    // Return the result as an array of records
    return $query->result_array();
}

public function getCollegeByCity($cityId, $subcategoryId)
{
    $this->db->select('c.id, c.title');
    $this->db->from('college c');
    
    $this->db->where('c.cityid', $cityId);
    $this->db->where('cc.categoryid', $subcategoryId);
    
    // Join with college_course table
    $this->db->join('college_course cc', 'cc.collegeid = c.id', 'left');

    // Use GROUP BY to avoid duplicates
    $this->db->group_by('c.id, c.title');
    
    $query = $this->db->get();
    
    return $query->result_array();
}

 public function updatedeviceId($userId, $deviceid, $platform)
{
    $Arr = [];

    if (!empty($deviceid)) {
        $Arr['deviceId'] = $deviceid;
    }

    if (!empty($platform)) {
        $Arr['platform'] = $platform;
    }

    if (empty($Arr)) {
        return false;
    }

    $this->db->where("email", $userId);
    return $this->db->update('users', $Arr);
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
