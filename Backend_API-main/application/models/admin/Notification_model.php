<?php

/**
 * Notification Model
 *
 * @category   Models
 * @package    Admin
 * @subpackage User
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    09 JAN 2024
 *
 * Class Users_model handles all user-related operations.
 */
defined("BASEPATH") or exit("No direct script access allowed");

class Notification_model extends CI_Model
{

  public function __construct()
  {
    parent::__construct();
  }


  public function countAll()
  {

    return $this->db->count_all_results('notification');
  }
  public function countFiltered($search)
  {

    // $this->db->like('f_name', $search);
    return $this->db->count_all_results('notification');
  }

  public function getFiltered($search, $start, $limit, $order, $dir)
  {
    //  echo "ttt";exit;
    $this->db->select('n.*,u.f_name as name');
    $this->db->from('notification n');
    $this->db->like('u.f_name', $search);
    $this->db->join('users u', 'n.userid = u.id', 'left');
    //  $this->db->order_by($order, $dir);
    $this->db->limit($limit, $start);
    return $this->db->get()->result();
  }
  public function getAll($start, $limit, $order, $dir)
  {
    $this->db->select('n.*,u.f_name as name');
    $this->db->from('notification n');
    $this->db->join('users u', 'n.userid = u.id', 'left');
    //  $this->db->join('pnj_bookings pb', 'pb.customer_id = pu.id', 'left');
    $this->db->where('is_deleted', 0);

    //  $this->db->order_by($order, $dir);
    $this->db->limit($limit, $start);

    // Debug SQL query
    // echo $this->db->get_compiled_select(); exit;

    // Uncomment this line when you're done debugging
    return $this->db->get()->result();
  }

  public function getUserforNotification($search = '')
  {
    $this->db->select('*');
    $this->db->from('users');
    $this->db->where('deviceId !=', '');

    if (!empty($search)) {
      $this->db->group_start();
      $this->db->like('f_name', $search);
      $this->db->or_like('l_name', $search);
      $this->db->group_end();
    }
    $query = $this->db->get();
    // echo $this->db->last_query(); exit;

    return $query->result_array();
  }
  public function saveNotification($insertData)
  {
    $this->db->insert('notification', $insertData);
    return ($this->db->affected_rows() > 0);
  }

  public function getAllUserIds()
  {
    $this->db->select('id');
    $this->db->from('users');
    $this->db->where('deviceId !=', '');
    $query = $this->db->get();
    // echo $this->db->last_query(); exit;
    return $query->result_array();
  }

  public function getdeviceId($userIdArray)
  {
    //  print_r($userIdArray);exit;
    $this->db->select('deviceId');
    $this->db->from('users');
    $this->db->where_in('id', $userIdArray);
    $this->db->where('deviceId !=', '');
    $query = $this->db->get();
    // echo $this->db->last_query(); exit;

    return $query->result_array();
  }

  /*   public function getNotificationList()
    {
        //echo "ttt";exit;
        $this->db->select('n.*,u.f_name as first_name,u.l_name as Last_name');
        $this->db->from('notification n');
       $this->db->join('users u', 'u.id = n.userid', 'left');
      // echo $this->db->last_query(); exit;
  $query = $this->db->get();

    return $query->result_array();
       
    }*/

  public function getNotificationList()
  {
    $this->db->select('n.*');
    $this->db->from('notification n');
    $this->db->where('is_deleted', 0);
    $query = $this->db->get();
    $notifications = $query->result_array();

    foreach ($notifications as &$noti) {
      $userIds = explode(',', $noti['userid']);

      $this->db->select('f_name, l_name');
      $this->db->from('users');
      $this->db->where_in('id', $userIds);
      $this->db->where('user_status', '2');
      $users = $this->db->get()->result_array();

      $userNames = [];
      foreach ($users as $user) {
        $userNames[] = $user['f_name'] . ' ' . $user['l_name'];
      }

      $noti['user_names'] = implode(', ', $userNames);
    }

    $this->db->from('notification');
    $this->db->where('is_deleted', 0);
    $total_count = $this->db->count_all_results();

    return [
      'total_count' => $total_count,
      'data' => $notifications
    ];
  }


  public function getNotificationById($id)
  {
    $this->db->select('n.*');
    $this->db->from('notification n');
    $this->db->where('n.id', $id);
    $this->db->where('is_deleted', 0);
    $query = $this->db->get();
    $notifications = $query->result_array();

    foreach ($notifications as &$noti) {
      $userIds = explode(',', $noti['userid']);
      $this->db->select('f_name, l_name');
      $this->db->from('users');
      $this->db->where_in('id', $userIds);
      $users = $this->db->get()->result_array();

      $userNames = [];
      foreach ($users as $user) {
        $userNames[] = $user['f_name'] . ' ' . $user['l_name'];
      }

      $noti['user_names'] = implode(', ', $userNames);
    }

    return $notifications;
  }



  public function updateNotification($updateData, $id)
  {
    //print_r($updateData);exit;
    $this->db->where('id', $id);
    $this->db->update('notification', $updateData);

    return $updateData;
  }

  public function deleteNotification($id)
  {
    $updateData = ['is_deleted' => 1];

    $this->db->where('id', $id);
    $this->db->update('notification', $updateData);

    return ($this->db->affected_rows() > 0);
  }

  public function saveBulkNotification($data)
  {
    $this->db->insert("notification", $data);
    return $this->db->insert_id();
  }

  public function saveNotificationFilter($data)
  {
    return $this->db->insert("notification_filters", $data);
  }


  public function getNotifications()
  {
    $this->db->select('n.id AS notification_id, n.title, n.message, n.is_sent, n.sent_at, 
                       nf.category, nf.subcategory, nf.exam, nf.state, nf.city');
    $this->db->from('notification n');
    $this->db->join('notification_filters nf', 'nf.notification_id = n.id', 'left');
    $this->db->where("is_sent", 0);

    $this->db->group_by("n.id");

    $this->db->order_by('n.id', 'DESC');

    return $this->db->get()->result();
  }


  public function getUsersByFilters($filters)
  {
    $this->db->select("user_id");
    $this->db->from("search_logs");

    // Always apply notification condition
    // $this->db->where("is_notification_sent", 0);

    $filterTypes = ['category', 'subcategory', 'exam', 'state', 'city'];
    $hasValidFilter = false;

    // First pass: check if any filter exists
    foreach ($filterTypes as $type) {
      if (!empty($filters[$type])) {
        $hasValidFilter = true;
        break;
      }
    }

    // Only apply grouped OR filters if at least one filter is present
    if ($hasValidFilter) {
      $this->db->group_start();

      foreach ($filterTypes as $type) {
        if (!empty($filters[$type])) {
          $this->db->or_group_start();
          $this->db->where("search_type", $type);
          $this->db->where_in("search_key", $filters[$type]);
          $this->db->group_end();
        }
      }

      $this->db->group_end();
    } else {
      // Force no results safely
      $this->db->where('1 = 0');
    }

    $query = $this->db->get();
    // echo $this->db->last_query(); exit;

    return $query->result_array();
  }




  public function getDeviceIds($userIds)
  {
    $this->db->select('deviceId');
    $this->db->from('users');
    $this->db->where_in('id', $userIds);

    return $this->db->get()->result_array();
  }

  // public function updateSendStatus($userIds)
  // {
  //   $this->db->where_in('user_id', $userIds);
  //   return $this->db->update('search_logs', ['is_notification_sent' => 1]);
  // }
}
