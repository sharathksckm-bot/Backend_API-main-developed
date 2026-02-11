<?php

class Authentication_model extends CI_Model
{
    private $table = 'college';

    public function __construct()
    {

        parent::__construct(); {
            $this->load->database();
        }
    }
	
	public function validateUser($userId)
    {
        $this->db->select("u.id, u.email, u.password, CONCAT(u.f_name, ' ', u.l_name) AS name");
        $this->db->from('users u');
		$this->db->join('user_status us', 'us.id = u.user_status', 'left');
        $this->db->where("u.email", $userId);
        //$this->db->where("cc.is_deleted", '0');
        $query = $this->db->get();
        $result = $query->result_array();
        //echo $this->db->last_query();exit;
        return $result;
    }
	
	public function valUser($userId)
    {
        $this->db->where("email", $userId);
        $query = $this->db->get('users');
        return $query->num_rows();
    }


    public function updateToken($Arr,$userId)
    {
        $this->db->where("email", $userId);
        $query = $this->db->update('users',$Arr);
        return $query;
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
//print_r($Arr);exit;
    $this->db->where("email", $userId);
    return $this->db->update('users', $Arr);
}
}
