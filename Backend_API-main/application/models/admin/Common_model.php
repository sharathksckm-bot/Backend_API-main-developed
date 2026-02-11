<?php

/**
 * Common Model
 *
 * @category   Models
 * @package    Admin
 * @subpackage common
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    26 JAN 2024
 * 
 * Class common_model handles all  operations.
 */

defined('BASEPATH') or exit('No direct script access allowed');

class Common_model extends CI_Model
{

	private $imgtable = 'gallery';
	private $page_category = 'page_category';
	private $table = 'counseling_fees';
	public function __construct()
	{
		parent::__construct();
	}

	public function insertDocsDetails($data)
	{
		$query = $this->db->insert($this->imgtable, $data);
		$imageId['imageId'] = $this->db->insert_id();
		return  $imageId;
	}
	public function updateDocsDetails($id, $postid, $Arr)
	{
		$this->db->where("id", $id);
		$this->db->where("postid", $postid);
		$query = $this->db->update($this->imgtable, $Arr);
		return $query;
	}
	public function getPageCategory($type)
	{
		$this->db->select('*');
		$this->db->from($this->page_category);
		$this->db->where('type', $type);
		return $this->db->get()->result();
	}

	public function getyear($searchyear)
	{
		$this->db->select('*');
		$this->db->from('year');
		if (!empty($searchyear)) {
			$this->db->like("year", $searchyear);
		}
		$this->db->limit(10);
		return $this->db->get()->result();
	}
	public function saveCounselingFees($Arr)
	{
		$query = $this->db->insert('counseling_fees', $Arr);
		$counselingId['counselingId'] = $this->db->insert_id();
		return  $counselingId;
	}

	public function updateCounselingFees($Arr, $id)
	{
		$this->db->where("id", $id);
		$query = $this->db->update('counseling_fees', $Arr);
		return $query;
	}

	public function deleteCounselingFees($id)
	{
		$this->db->where("id", $id);
		$query = $this->db->delete('counseling_fees');
		return $query;
	}

	public function chkIsExists($sub_category, $collegeType, $categoryid)
	{
		$this->db->select('*');
		$this->db->from('counseling_fees');
		$this->db->where('sub_category', $sub_category);
		$this->db->where('college_type', $collegeType);
		$this->db->where('category', $categoryid);
		return $this->db->get()->num_rows();
	}

	public function chkIsExistsWhileUpdate($sub_category, $collegeType, $categoryid, $id)
	{
		$this->db->select('*');
		$this->db->from('counseling_fees');
		$this->db->where('sub_category', $sub_category);
		$this->db->where('college_type', $collegeType);
		$this->db->where('category', $categoryid);
		$this->db->where('id !=', $id);
		return $this->db->get()->num_rows();
	}

	public function countAllCounselingFees()
	{
		return $this->db->count_all($this->table);
	}

	public function countFilteredCounselingFees($search)
	{
		$this->db->join("sub_category sc", "sc.id = c.sub_category", "left");
		$this->db->join("category ca", "ca.id = c.category", "left");
		$this->db->join("college_type ct", "ct.id = c.college_type", "left");

		$this->db->group_start();
		$this->db->like('ca.catname', $search);
		$this->db->or_like('sc.name', $search);
		$this->db->or_like('ct.name', $search);
		$this->db->group_end();
		$query = $this->db->get($this->table . " c");
		//echo $this->db->last_query();exit;
		return $query->num_rows();
	}

	public function getFilteredCounselingFees($search, $start, $limit, $orderColumn, $orderDir)
	{
		$this->db->select("c.*,sc.name as subCategoryName,ca.catname as categoryName,ct.name as collegeTypeName,e.title as exam_name");
		$this->db->from($this->table . " c");
		$this->db->join("sub_category sc", "sc.id = c.sub_category", "left");
		$this->db->join("category ca", "ca.id = c.category", "left");
		$this->db->join("college_type ct", "ct.id = c.college_type", "left");
		$this->db->join("exams e", "e.id = c.exam_id", "left");

		$this->db->group_start();
		$this->db->like('ca.catname', $search);
		$this->db->or_like('sc.name', $search);
		$this->db->or_like('ct.name', $search);

		$this->db->group_end();
		$this->db->order_by($orderColumn, $orderDir);
		$this->db->limit($limit, $start);
		$query = $this->db->get();
		$result = $query->result();
		return $result;
	}

	public function getAllCounselingFees($start, $limit, $orderColumn, $orderDir)
	{
		$this->db->select("c.*,sc.name as subCategoryName,ca.catname as categoryName,ct.name as collegeTypeName,e.title as exam_name");
		$this->db->from($this->table . " c");
		$this->db->join("sub_category sc", "sc.id = c.sub_category", "left");
		$this->db->join("category ca", "ca.id = c.category", "left");
		$this->db->join("college_type ct", "ct.id = c.college_type", "left");
		$this->db->join("exams e", "e.id = c.exam_id", "left");
		$this->db->order_by($orderColumn, $orderDir);
		$this->db->limit($limit, $start);

		return $this->db->get()->result();
	}

	public function getCounselingFeesById($id)
	{
		$this->db->select("c.*,sc.name as subCategoryName,ca.catname as categoryName,ct.name as collegeTypeName, e.title as examName");
		$this->db->from($this->table . " c");
		$this->db->join("sub_category sc", "sc.id = c.sub_category", "left");
		$this->db->join("category ca", "ca.id = c.category", "left");
		$this->db->join("college_type ct", "ct.id = c.college_type", "left");
		$this->db->join("exams e", "e.id = c.exam_id", "left");
		$this->db->where("c.id", $id);
		return $this->db->get()->result();
	}

	public function checkTeamReport($created_by, $userType, $created_date)
	{
		$this->db->select("*");
		$this->db->from("team_report");
		$this->db->where("userid", $created_by);
		$this->db->where("usertype", $userType);
		$this->db->where("DATE(created_date)", $created_date);
		return $this->db->get()->num_rows();
	}



	public function saveTeamReport($Arr)
	{
		$this->db->set('no_of_colleges_added', 'no_of_colleges_added + ' . $Arr['no_of_colleges_added'], FALSE);
		$this->db->set('no_of_exams_added', 'no_of_exams_added + ' . $Arr['no_of_exams_added'], FALSE);
		$this->db->set('no_of_events_added', 'no_of_events_added + ' . $Arr['no_of_events_added'], FALSE);
		$this->db->set('no_of_articles_added', 'no_of_articles_added + ' . $Arr['no_of_articles_added'], FALSE);
		$this->db->set('userid', $Arr['userid']);
		$this->db->set('usertype', $Arr['usertype']);

		$this->db->insert('team_report');
		$team['teamRepId'] = $this->db->insert_id();
		return $team;
	}

	public function updateTeamReport($created_by, $Arr, $create_date)
	{
		if (isset($Arr['no_of_colleges_added'])) {
			$this->db->set('no_of_colleges_added', 'no_of_colleges_added + ' . $Arr['no_of_colleges_added'], FALSE);
		}
		if (isset($Arr['no_of_exams_added'])) {
			$this->db->set('no_of_exams_added', 'no_of_exams_added + ' . $Arr['no_of_exams_added'], FALSE);
		}
		if (isset($Arr['no_of_events_added'])) {
			$this->db->set('no_of_events_added', 'no_of_events_added + ' . $Arr['no_of_events_added'], FALSE);
		}
		if (isset($Arr['no_of_articles_added'])) {
			$this->db->set('no_of_articles_added', 'no_of_articles_added + ' . $Arr['no_of_articles_added'], FALSE);
		}
		$this->db->set('updated_date', $Arr['updated_date']);
		$this->db->where('userid', $created_by);
		$this->db->where("DATE(created_date)", $create_date);
		$query = $this->db->update('team_report');
		return $query;
	}

	public function getUserActivity($start, $limit, $orderColumn, $orderDir)
	{
		$this->db->select('ua.*,CONCAT(u.f_name, " ", u.l_name) as fullname,u.phone');
		$this->db->from('user_activity ua');
		$this->db->join('users u', 'u.id = ua.user_name', 'left');

		$this->db->order_by($orderColumn, $orderDir);
		$this->db->limit($limit, $start);
		$query = $this->db->get()->result();
		return $query;
	}

	public function getTeamReport($start, $limit, $orderColumn, $orderDir)
	{
		$this->db->select('t.*, CONCAT(u.f_name, "", u.l_name) as username, ut.name as user_type');
		$this->db->from('team_report t');
		$this->db->join('users u', 'u.id = t.userid', 'left');
		$this->db->join('user_type ut', 'ut.id = t.usertype', 'left');
		$this->db->order_by($orderColumn, $orderDir);
		$this->db->limit($limit, $start);
		$query = $this->db->get();
		return $query->result();
	}
	public function countAllTeamReport()
	{
		return $this->db->count_all('team_report');
	}

	public function countAllUserActivity()
	{
		return $this->db->count_all('user_activity');
	}

	public function updateClgReport($college_id, $ClgRepArr)
	{
		if (isset($ClgRepArr['no_of_articles_linked'])) {
			$this->db->set('no_of_articles_linked', 'no_of_articles_linked + ' . $ClgRepArr['no_of_articles_linked'], FALSE);
		}
		if (isset($ClgRepArr['no_of_brochures_download'])) {
			$this->db->set('no_of_brochures_download', 'no_of_brochures_download + ' . $ClgRepArr['no_of_brochures_download'], FALSE);
		}
		if (isset($ClgRepArr['no_of_application_submitted'])) {
			$this->db->set('no_of_application_submitted', 'no_of_application_submitted + ' . $ClgRepArr['no_of_application_submitted'], FALSE);
		}
		if (isset($ClgRepArr['no_of_que_asked'])) {
			$this->db->set('no_of_que_asked', 'no_of_que_asked + ' . $ClgRepArr['no_of_que_asked'], FALSE);
		}
		if (isset($ClgRepArr['no_of_answeres'])) {
			$this->db->set('no_of_answeres', 'no_of_answeres + ' . $ClgRepArr['no_of_answeres'], FALSE);
		}
		if (isset($ClgRepArr['no_of_review'])) {

			$this->db->set('no_of_review', 'no_of_review + ' . $ClgRepArr['no_of_review'], FALSE);
		}
		$this->db->where('college', $college_id);
		$query = $this->db->update('college_report');
		return $query;
	}

	/*public function saveClgReport($ClgRepArr)
{
	$this->db->set('no_of_articles_linked', 'no_of_articles_linked + '.$ClgRepArr['no_of_articles_linked'], FALSE);
    $this->db->set('no_of_brochures_download', 'no_of_brochures_download + '.$ClgRepArr['no_of_brochures_download'], FALSE);
    $this->db->set('no_of_application_submitted', 'no_of_application_submitted + '.$ClgRepArr['no_of_application_submitted'], FALSE);
    $this->db->set('no_of_que_asked', 'no_of_que_asked + '.$ClgRepArr['no_of_que_asked'], FALSE);
	$this->db->set('no_of_answeres', 'no_of_answeres + '.$ClgRepArr['no_of_answeres'], FALSE);
	$this->db->set('no_of_review', 'no_of_review + '.$ClgRepArr['no_of_review'],FALSE);
	$this->db->set('college', $ClgRepArr['college'], FALSE);
    $this->db->insert('college_report');
    $collegeRep['college_report_id'] = $this->db->insert_id();
    return $collegeRep;
}*/

	public function saveClgReport($ClgRepArr)
	{
		$this->db->where('college', $ClgRepArr['college']);
		$query = $this->db->get('college_report');

		if ($query->num_rows() > 0) {
			$this->db->set('no_of_articles_linked', 'no_of_articles_linked + ' . (int)$ClgRepArr['no_of_articles_linked'], FALSE);
			$this->db->set('no_of_brochures_download', 'no_of_brochures_download + ' . (int)$ClgRepArr['no_of_brochures_download'], FALSE);
			$this->db->set('no_of_application_submitted', 'no_of_application_submitted + ' . (int)$ClgRepArr['no_of_application_submitted'], FALSE);
			$this->db->set('no_of_que_asked', 'no_of_que_asked + ' . (int)$ClgRepArr['no_of_que_asked'], FALSE);
			$this->db->set('no_of_answeres', 'no_of_answeres + ' . (int)$ClgRepArr['no_of_answeres'], FALSE);
			$this->db->set('no_of_review', 'no_of_review + ' . (int)$ClgRepArr['no_of_review'], FALSE);
			$this->db->where('college', $ClgRepArr['college']);
			$this->db->update('college_report');
			return ['message' => 'Updated successfully'];
		} else {
			$this->db->insert('college_report', $ClgRepArr);
			return ['message' => 'Inserted successfully', 'insert_id' => $this->db->insert_id()];
		}
	}


	public function checkcollegeReport($college_id)
	{
		$this->db->select("*");
		$this->db->from("college_report");
		$this->db->where("college", $college_id);
		$query = $this->db->get();
		// echo $this->db->last_query();exit;
		$result = $query->num_rows();
		return $result;
	}

	public function getCollegeReport($start, $limit, $orderColumn, $orderDir)
	{
		$this->db->select("cr.*,c.title as collegename,ci.city,s.statename");
		$this->db->from("college_report cr");
		$this->db->join('college c', 'c.id = cr.college', 'left');
		$this->db->join('city ci', 'ci.id = c.cityid', 'left');
		$this->db->join('state s', 's.id = c.stateid', 'left');
		$this->db->order_by($orderColumn, $orderDir);
		$this->db->limit($limit, $start);
		$query = $this->db->get();
		$result = $query->result();
		return $result;
	}

	public function countAllCollegeReport()
	{
		return $this->db->count_all('college_report');
	}

	public function countFilteredCollegeReport($search)
	{
		$this->db->select("cr.*,c.title as collegename,ci.city,s.statename");
		$this->db->from("college_report cr");
		$this->db->join('college c', 'c.id = cr.college', 'left');
		$this->db->join('city ci', 'ci.id = c.cityid', 'left');
		$this->db->join('state s', 's.id = c.stateid', 'left');
		$this->db->like('c.title', $search);
		$this->db->or_like('ci.city', $search);
		$this->db->or_like('s.statename', $search);
		return $this->db->get($this->table)->num_rows();
	}

	public function countFilteredTeamReport($search, $usertype, $fromdate, $todate)
	{
		$this->db->select('t.*, CONCAT(u.f_name, "", u.l_name) as username, ut.name as user_type');
		$this->db->from('team_report t');
		$this->db->join('users u', 'u.id = t.userid', 'left');
		$this->db->join('user_type ut', 'ut.id = t.usertype', 'left');
		if (!empty($usertype)) {
			$this->db->where('ut.name', $usertype);
		}

		if (!empty($fromdate) && !empty($todate)) {
			$this->db->where('t.created_date >=', $fromdate);
			$this->db->where('t.created_date <=', $todate);
		} elseif (!empty($fromdate)) {
			$this->db->where('t.created_date >=', $fromdate);
		} elseif (!empty($todate)) {
			$this->db->where('t.created_date <=', $todate);
		}
		$this->db->group_start();
		$this->db->like('u.f_name', $search);
		$this->db->or_like('u.l_name', $search);
		$this->db->or_like('ut.name', $search);
		$this->db->group_end();

		$this->db->group_by('t.userid');
		return $this->db->get($this->table)->num_rows();
	}

	public function countFilteredUserActivity($search)
	{
		$this->db->select('*');
		$this->db->from('user_activity');
		$this->db->like('user_name', $search);
		$this->db->or_like('email', $search);
		$query = $this->db->get()->result_array();
		return $query;
	}
	public function getFilteredCollegeReport($search, $start, $limit, $orderColumn, $orderDir)
	{
		$this->db->select("cr.*,c.title as collegename,ci.city,s.statename");
		$this->db->from("college_report cr");
		$this->db->join('college c', 'c.id = cr.college', 'left');
		$this->db->join('city ci', 'ci.id = c.cityid', 'left');
		$this->db->join('state s', 's.id = c.stateid', 'left');
		$this->db->group_start();
		$this->db->like('c.title', $search);
		$this->db->or_like('ci.city', $search);
		$this->db->or_like('s.statename', $search);
		$this->db->group_end();

		$this->db->order_by($orderColumn, $orderDir);
		$this->db->limit($limit, $start);

		return $this->db->get()->result();
	}

	public function getFilteredTeamReport($search, $usertype, $fromdate, $todate, $start, $limit, $orderColumn, $orderDir)
	{
		$this->db->select('t.*, CONCAT(u.f_name, "", u.l_name) as username, ut.name as user_type');
		$this->db->from('team_report t');
		$this->db->join('users u', 'u.id = t.userid', 'left');
		$this->db->join('user_type ut', 'ut.id = t.usertype', 'left');
		if (!empty($usertype)) {
			$this->db->where('ut.name', $usertype);
		}

		if (!empty($fromdate) && !empty($todate)) {
			$this->db->where('t.created_date >=', $fromdate);
			$this->db->where('t.created_date <=', $todate);
		} elseif (!empty($fromdate)) {
			$this->db->where('t.created_date >=', $fromdate);
		} elseif (!empty($todate)) {
			$this->db->where('t.created_date <=', $todate);
		}
		$this->db->group_start();
		$this->db->like('u.f_name', $search);
		$this->db->or_like('u.l_name', $search);
		$this->db->or_like('ut.name', $search);
		$this->db->group_end();

		$this->db->order_by($orderColumn, $orderDir);
		$this->db->group_by('t.userid');
		$this->db->limit($limit, $start);
		// echo $this->db->last_query();exit;
		return $this->db->get()->result();
	}

	public function getFilteredUserActivity($search, $start, $limit, $orderColumn, $orderDir)
	{
		$this->db->select('ua.*,CONCAT(u.f_name, " ", u.l_name) as fullname,u.phone');
		$this->db->from('user_activity ua');
		$this->db->join('users u', 'u.id = ua.user_name', 'left');

		$this->db->like('ua.user_name', $search);
		$this->db->or_like('ua.email', $search);
		$this->db->order_by($orderColumn, $orderDir);
		$this->db->limit($limit, $start);
		$query = $this->db->get()->result();
		return $query;
	}

	public function addUserActivity($Arr)
	{
		$this->db->insert('user_activity', $Arr);
		return $this->db->insert_id();
	}

	public function viewUserActivity($userid)
	{
		$this->db->select('ua.location, ua.latest_activity, ua.created_date, ua.email, CONCAT(u.f_name, " ", u.l_name) as user_name, c.title as college_name, e.title as examname, q.question, a.answer');
		$this->db->from('user_activity ua');
		$this->db->join('users u', 'u.id = ua.user_name', 'left');
		$this->db->join('college c', 'c.id = ua.college', 'left');
		$this->db->join('exams e', 'e.id = ua.exam', 'left');
		$this->db->join('question q', 'q.question_id = ua.question', 'left');
		$this->db->join('answer a', 'a.answer_id = ua.answere', 'left');
		$this->db->where('ua.user_name', $userid);
		$this->db->order_by('ua.created_date', 'desc');

		$query = $this->db->get();
		return $query->result();
	}

	public function EnqnotificationCount($userid)
	{
		$this->db->select('COUNT(el.log_id) AS COUNT, MAX(el.log_created_date) AS max_date,el.log_id');
		$this->db->select("CASE WHEN seen_by = $userid THEN '1' ELSE '0' END AS seen_status", FALSE);

		$this->db->from('enquiry_log el');
		$this->db->where('el.status', 0);
		$query = $this->db->get();
		$result = $query->row(); // Assuming you expect only one row
		$count = $result->COUNT;
		$log_created_date = $result->max_date ? new DateTime($result->max_date) : new DateTime();
		$current_date = new DateTime();

		$current_date = new DateTime() ?? 'now';

		$interval = $current_date->diff($log_created_date);
		$time = '';

		if ($interval->days > 0) {
			$time = $interval->days . ' day(s) ago';
		} elseif ($interval->h > 0) {
			$time = $interval->h . ' hour(s) ago';
		} elseif ($interval->i > 0) {
			$time = $interval->i . ' minute(s) ago';
		} else {
			$time = 'Just now';
		}

		$data = array(
			'log_id' => $result->log_id,
			'name' => 'Enquiries',
			'type' => 'enquiry_log',
			'count' => $count,
			'seen_status' => $result->seen_status,
			'message' => ($count > 0) ? "You have new $count enquiries. Click here to view." : "No new enquiry.",
			'time' => $time
		);

		return $data;
	}
	public function ApplicationNotificationCount($userid)
	{
		$this->db->select('COUNT(al.log_id) AS COUNT, MAX(al.log_created_date) AS max_date,al.log_id');
		$this->db->select("CASE WHEN seen_by = $userid THEN '1' ELSE '0' END AS seen_status", FALSE);

		$this->db->from('application_log al');
		$this->db->where('al.status', 0);
		$query = $this->db->get();
		$result = $query->row(); // Assuming you expect only one row
		$count = $result->COUNT;
		$log_created_date = $result->max_date ? new DateTime($result->max_date) : new DateTime();
		$current_date = new DateTime() ?? 'now';
		$interval = $current_date->diff($log_created_date);
		$time = '';

		if ($interval->days > 0) {
			$time = $interval->days . ' day(s) ago';
		} elseif ($interval->h > 0) {
			$time = $interval->h . ' hour(s) ago';
		} elseif ($interval->i > 0) {
			$time = $interval->i . ' minute(s) ago';
		} else {
			$time = 'Just now';
		}

		$data = array(
			'log_id' => $result->log_id,
			'name' => 'Applications',
			'type' => 'application_log',
			'count' => $count,
			'seen_status' => $result->seen_status,
			'message' => ($count > 0) ? "You have new $count applications. Click here to view." : "No new application.",
			'time' => $time
		);

		return $data;
	}
	public function CourseEnquiryNotificationCount($userid)
	{
		$this->db->select('COUNT(cl.log_id) AS COUNT, MAX(cl.log_created_date) AS max_date,cl.log_id');
		$this->db->select("CASE WHEN seen_by = $userid THEN '1' ELSE '0' END AS seen_status", FALSE);

		$this->db->from('courseenquiry_log cl');
		$this->db->where('cl.status', 0);
		$query = $this->db->get();
		$result = $query->row(); // Assuming you expect only one row
		$count = $result->COUNT;
		$log_created_date = $result->max_date ? new DateTime($result->max_date) : new DateTime();
		$current_date = new DateTime();
		$interval = $current_date->diff($log_created_date);
		$time = '';

		if ($interval->days > 0) {
			$time = $interval->days . ' day(s) ago';
		} elseif ($interval->h > 0) {
			$time = $interval->h . ' hour(s) ago';
		} elseif ($interval->i > 0) {
			$time = $interval->i . ' minute(s) ago';
		} else {
			$time = 'Just now';
		}

		$data = array(
			'log_id' => $result->log_id,
			'name' => 'Course Enquires',
			'seen_status' => $result->seen_status,
			'type' => 'courseenquiry_log',
			'count' => $count,
			'message' => ($count > 0) ? "You have new $count course enquiries. Click here to view." : "No new course enquiry.",
			'time' => $time
		);

		return $data;
	}

	public function PredictionNotificationCount($userid)
	{
		$this->db->select('COUNT(pl.log_id) AS COUNT, MAX(pl.log_created_date) AS max_date,pl.log_id');
		$this->db->select("CASE WHEN seen_by = $userid THEN '1' ELSE '0' END AS seen_status", FALSE);

		$this->db->from('predict_log pl');
		$this->db->where('pl.status', 0);
		$query = $this->db->get();
		$result = $query->row(); // Assuming you expect only one row
		$count = $result->COUNT;
		$log_created_date = $result->max_date ? new DateTime($result->max_date) : new DateTime();
		$current_date = new DateTime();
		$interval = $current_date->diff($log_created_date);
		$time = '';

		if ($interval->days > 0) {
			$time = $interval->days . ' day(s) ago';
		} elseif ($interval->h > 0) {
			$time = $interval->h . ' hour(s) ago';
		} elseif ($interval->i > 0) {
			$time = $interval->i . ' minute(s) ago';
		} else {
			$time = 'Just now';
		}

		$data = array(
			'log_id' => $result->log_id,
			'name' => 'Predict Admission Applications',
			'type' => 'predict_log',
			'count' => $count,
			'seen_status' => $result->seen_status,
			'message' => ($count > 0) ? "You have new $count predict admission applications. Click here to view." : "No new predicted admission application.",
			'time' => $time
		);

		return $data;
	}
	public function ReviewNotificationCount($userid)
	{
		$this->db->select('COUNT(rl.log_id) AS COUNT, MAX(rl.log_created_date) AS max_date,rl.log_id');
		$this->db->select("CASE WHEN seen_by = $userid THEN '1' ELSE '0' END AS seen_status", FALSE);

		$this->db->from('review_log rl');
		$this->db->where('rl.status', 0);
		$query = $this->db->get();
		$result = $query->row(); // Assuming you expect only one row
		$count = $result->COUNT;
		$log_created_date = $result->max_date ? new DateTime($result->max_date) : new DateTime();
		$current_date = new DateTime();
		$interval = $current_date->diff($log_created_date);
		$time = '';

		if ($interval->days > 0) {
			$time = $interval->days . ' day(s) ago';
		} elseif ($interval->h > 0) {
			$time = $interval->h . ' hour(s) ago';
		} elseif ($interval->i > 0) {
			$time = $interval->i . ' minute(s) ago';
		} else {
			$time = 'Just now';
		}

		$data = array(
			'log_id' => $result->log_id,
			'name' => 'Review',
			'type' => 'review_log',
			'count' => $count,
			'seen_status' => $result->seen_status,
			'message' => ($count > 0) ? "You have new $count reviews. Click here to view." : "No new review.",
			'time' => $time
		);

		return $data;
	}

	public function QuestionNotificationCount($userid)
	{
		$this->db->select('COUNT(ql.log_id) AS COUNT, MAX(ql.log_created_date) AS max_date,ql.log_id');
		$this->db->select("CASE WHEN seen_by = $userid THEN '1' ELSE '0' END AS seen_status", FALSE);

		$this->db->from('question_log ql');
		$this->db->where('ql.status', 0);
		$query = $this->db->get();
		$result = $query->row(); // Assuming you expect only one row
		$count = $result->COUNT;
		$log_created_date = $result->max_date ? new DateTime($result->max_date) : new DateTime();
		$current_date = new DateTime();
		$interval = $current_date->diff($log_created_date);
		$time = '';

		if ($interval->days > 0) {
			$time = $interval->days . ' day(s) ago';
		} elseif ($interval->h > 0) {
			$time = $interval->h . ' hour(s) ago';
		} elseif ($interval->i > 0) {
			$time = $interval->i . ' minute(s) ago';
		} else {
			$time = 'Just now';
		}

		$data = array(
			'log_id' => $result->log_id,
			'name' => 'Question',
			'type' => 'question_log',
			'count' => $count,
			'seen_status' => $result->seen_status,
			'message' => ($count > 0) ? "You have new $count questions. Click here to view." : "No new question.",
			'time' => $time
		);

		return $data;
	}

	public function getdatabylogid($logid, $table)
	{
		$this->db->select('seen_by');
		$this->db->from($table);
		$this->db->where('log_id', $logid);
		$query = $this->db->get();
		return $query->result();
	}

	public function updateLogStatus($Arr, $logid, $type)
	{
		// $this->db->where("log_id", $logid);
		$query = $this->db->update($type, $Arr);
		return $query;
	}


	public function countAllSpec()
	{
		return $this->db->count_all('specialization');
	}
	public function countFilteredSpec($search)
	{
		$this->db->join("users u1", "u1.id = s.created_by", "left");
		$this->db->join("users u2", "u2.id = s.updated_by", "left");
		$this->db->group_start();
		$this->db->like('s.name', $search);
		$this->db->or_like('u1.f_name', $search);
		$this->db->or_like('u2.f_name', $search);
		$this->db->or_like('u1.l_name', $search);
		$this->db->or_like('u2.l_name', $search);
		$this->db->group_end();
		$query = $this->db->get('specialization s');
		return $query->num_rows();
	}

	public function getFilteredSpec($search, $start, $limit, $orderColumn, $orderDir)
	{
		$this->db->select('s.*,CONCAT(u1.f_name, " ", u1.l_name) AS created_by_name,CONCAT(u2.f_name, " ", u2.l_name) AS updated_by_name');
		$this->db->from('specialization s');
		$this->db->join("users u1", "u1.id = s.created_by", "left");
		$this->db->join("users u2", "u2.id = s.updated_by", "left");

		$this->db->group_start();
		$this->db->like('s.name', $search);
		$this->db->or_like('u1.f_name', $search);
		$this->db->or_like('u2.f_name', $search);
		$this->db->or_like('u1.l_name', $search);
		$this->db->or_like('u2.l_name', $search);
		$this->db->group_end();
		$this->db->order_by($orderColumn, $orderDir);
		$this->db->limit($limit, $start);
		$query = $this->db->get();
		$result = $query->result();
		return $result;
	}

	public function getAllSpec($start, $limit, $orderColumn, $orderDir)
	{
		$this->db->select('s.*,CONCAT(u1.f_name, " ", u1.l_name) AS created_by_name,CONCAT(u2.f_name, " ", u2.l_name) AS updated_by_name');
		$this->db->from('specialization s');
		$this->db->join("users u1", "u1.id = s.created_by", "left");
		$this->db->join("users u2", "u2.id = s.updated_by", "left");
		$this->db->order_by($orderColumn, $orderDir);
		$this->db->limit($limit, $start);
		$query = $this->db->get();
		$result = $query->result();
		return $result;
	}

	public function deleteSpecialization($id)
	{
		$this->db->where("id", $id);
		$query = $this->db->delete('specialization');
		return $query;
	}

	public function getSpecializationById($id)
	{
		$this->db->select('*');
		$this->db->from('specialization');
		$this->db->where('id', $id);
		return $this->db->get()->result();
	}

	public function updateSpecialization($Arr, $id)
	{
		$this->db->where("id", $id);
		$query = $this->db->update('specialization', $Arr);
		return $query;
	}

	public function chkIsSpecExistsWhileUpdate($id, $name)
	{
		$this->db->select('*');
		$this->db->from('specialization');
		$this->db->where('name', $name);
		$this->db->where('id !=', $id);
		return $this->db->get()->num_rows();
	}


	public function saveSpecialization($Arr)
	{
		$query = $this->db->insert('specialization', $Arr);
		$specId['specId'] = $this->db->insert_id();
		return  $specId;
	}


	public function chkIsSpecExists($name)
	{
		$this->db->select("*");
		$this->db->from("specialization");
		$this->db->where("name", $name);
		return $this->db->get()->num_rows();
	}

	public function getContentCategory()
	{
		$this->db->select("id,name,status");
		$this->db->from("content_category");
		return $this->db->get()->result();
	}


	public function saveTblOfContent($Arr)
	{
		$query = $this->db->insert('table_of_content', $Arr);
		return $this->db->affected_rows() > 0;
	}

	public function updateTblOfContentbyId($id, $Arr)
	{
		$this->db->where('id', $id);
		$this->db->update('table_of_content', $Arr);
		return $this->db->affected_rows() > 0;
	}

	// Method to update a row in table_of_content based on certain criteria
	public function updateTblOfContent($criteria, $data)
	{
		$this->db->where($criteria);
		$query =  $this->db->update('table_of_content', $data);
		return $query;
	}

	// Method to get a row from table_of_content based on certain criteria
	public function getTblOfContent($criteria)
	{
		$this->db->where($criteria);
		$query = $this->db->get('table_of_content');
		return $query->row_array();
	}

	public function getTblOfContentById($criteria)
	{
		$this->db->select('tc.*,cc.name as titlename');
		$this->db->where($criteria);
		$this->db->join('content_category cc', 'cc.id = tc.title', 'left');
		$query = $this->db->get('table_of_content tc');
		//  echo $this->db->last_query();
		//exit;
		return $query->result_array();
	}

	public function deleteExisting($collegeid)
	{
		$this->db->where("college_id", $collegeid);
		$query = $this->db->delete('table_of_content');
		return $query;
	}

	// Retrieve existing titleids for a given collegeid
	public function getTitlesByCollegeId($collegeid)
	{
		$this->db->select('title');
		$this->db->where('college_id', $collegeid);
		$query = $this->db->get('table_of_content');
		$result = $query->result_array();
		$titles = array_column($result, 'title');
		return $titles;
	}

	// Delete existing titles for a given collegeid
	public function deleteTitles($titles, $collegeid)
	{
		$this->db->where('college_id', $collegeid);
		$this->db->where_in('title', $titles);
		$this->db->delete('table_of_content');
		return $this->db->affected_rows() > 0;
	}

	public function saveFooterNotification($data)
	{
		return $this->db->insert('footer_notification', $data);
	}
	public function toggleFooterNotification($status, $id)
	{
		return $this->db
			->where_in('id', $id)
			->update('footer_notification', [
				'is_enabled' => $status
			]);
	}
	public function deleteFooterNotification($id)
	{
		return $this->db
			->where_in('id', $id)
			->update('footer_notification', [
				'is_deleted' => 1
			]);
	}
	public function updateFooterNotification($id, $data)
	{
		return $this->db->update(
			'footer_notification',
			$data,
			['id' => (int)$id]
		);
	}
	public function saveStudyAbroad($data)
	{
		return $this->db->insert('study_abroad_enquiries', $data);
	}
	public function countAllStudyAbroad()
	{
		return $this->db->count_all('study_abroad_enquiries');
	}

	public function countFilteredStudyAbroad($search)
	{
		$this->db->like('name', $search);
		$this->db->or_like('email', $search);
		$this->db->or_like('contact_no', $search);
		$this->db->or_like('country', $search);
		return $this->db->get('study_abroad_enquiries')->num_rows();
	}

	public function getFilteredStudyAbroad($search, $start, $limit, $orderColumn, $orderDir)
	{
		$this->db->like('name', $search);
		$this->db->or_like('email', $search);
		$this->db->or_like('contact_no', $search);
		$this->db->or_like('country', $search);
		$this->db->order_by($orderColumn, $orderDir);
		$this->db->limit($limit, $start);
		return $this->db->get('study_abroad_enquiries')->result();
	}

	public function getAllStudyAbroad($start, $limit, $orderColumn, $orderDir)
	{
		$this->db->order_by($orderColumn, $orderDir);
		$this->db->limit($limit, $start);
		return $this->db->get('study_abroad_enquiries')->result();
	}
	/**
	 * Count all footer notifications
	 */
	public function countAllFooterNotification()
	{
		$this->db->where('is_deleted', 0);
		return $this->db->count_all_results('footer_notification');
	}

	/**
	 * Count filtered footer notifications
	 */
	public function countFilteredFooterNotification($search)
	{
		$this->db->from('footer_notification');
		$this->db->group_start();
		$this->db->like('notification_text', $search);
		// $this->db->or_like('message', $search);
		// $this->db->or_like('status', $search);
		$this->db->group_end();
		$this->db->where('is_deleted', 0);

		return $this->db->count_all_results();
	}

	/**
	 * Get all footer notifications
	 */
	public function getAllFooterNotification($start, $limit, $orderColumn, $orderDir)
	{
		$this->db->select('*');
		$this->db->from('footer_notification');
		$this->db->where('is_deleted', 0);

		$this->db->order_by($orderColumn, $orderDir);
		$this->db->limit($limit, $start);

		return $this->db->get()->result();
	}

	/**
	 * Get filtered footer notifications
	 */
	public function getFilteredFooterNotification($search, $start, $limit, $orderColumn, $orderDir)
	{
		$this->db->select('*');
		$this->db->from('footer_notification');

		$this->db->group_start();
		$this->db->like('notification_text', $search);
		// $this->db->or_like('message', $search);
		$this->db->group_end();
		$this->db->where('is_deleted', 0);

		$this->db->order_by($orderColumn, $orderDir);
		$this->db->limit($limit, $start);

		return $this->db->get()->result();
	}
}
