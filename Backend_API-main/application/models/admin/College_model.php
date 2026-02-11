<?php

/**
 * College Model
 *
 * @category   Models
 * @package    Admin
 * @subpackage College
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    24 JAN 2024
 * 
 * Class college_model handles all college-related operations.
 */

defined('BASEPATH') or exit('No direct script access allowed');

class College_model extends CI_Model
{

	private $table = 'college';
	private $imgtable = 'gallery';
	private $clgCourse = 'college_course';
	private $clgHighlights = 'college_highlights ';
	private $clgFee = 'fee_structure';
	private $brochures = 'brochures';
	private $academic_year = 'academic_year';
	private $college_ranks = 'college_ranks';
	private $placement_category = 'placement_category';
	private $tableOfConetent = 'table_of_content';
	private $sample_format = 'sample_format';


	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Get the count of all colleges.
	 *
	 * @return int The count of colleges.
	 */
	public function countAllClg($userId, $userType)
	{
		if ($userType == 14 || $userType == 3) {
			$this->db->where('c.created_by', $userId);
		}
		$this->db->select('count(*) as college_count');
		$this->db->from('college c');
		$this->db->where('c.is_deleted', '0');
		$this->db->where('c.status', '1');

		$result = $this->db->get()->result_array();

		// Check if there are results before returning the count
		if (!empty($result)) {
			return $result[0]['college_count'];
		} else {
			return 0;
		}
	}

	/**
	 * Count filtered colleges based on the search term.
	 *
	 * @param string $search The search term.
	 * @return int The number of filtered colleges.
	 */
	public function countFilteredClg($search, $userId, $userType, $start, $limit)
	{
		$this->db->select('*');
		$this->db->where('is_deleted', '0');
		//$this->db->where('status', '1');
		if ($userType == 14 || $userType == 3) {
			$this->db->where('c.created_by', $userId);
		}
		$this->db->like('title', $search);
		$this->db->or_like('package_type', $search);
		$this->db->or_like('map_location', $search);
		$this->db->limit(10);
		return $this->db->get($this->table)->num_rows();
	}

	/**
	 * Get filtered colleges .
	 *
	 * @param string $search The search term.
	 * @param int    $start  The starting index for pagination.
	 * @param int    $limit  The number of records to retrieve.
	 * @param string $order  The column to order by.
	 * @param string $dir    The direction of sorting.
	 * @return array The list of filtered and paginated colleges with additional information.
	 */

	public function getFilteredClg($search, $start, $limit, $orderColumn, $orderDir, $userId, $userType)
	{
		$this->db->select("c.*, ci.city as cityname, s.statename, CONCAT(u1.f_name, ' ', u1.l_name) as created_by_name, CONCAT(u2.f_name, ' ', u2.l_name) as updated_by_name");
		$this->db->from($this->table . " c");
		$this->db->join('city ci', 'ci.id = c.cityid', 'left');
		$this->db->join('state s', 's.id = c.stateid', 'left');
		$this->db->join('users u1', 'u1.id = c.created_by', 'left');
		$this->db->join('users u2', 'u2.id = c.updated_by', 'left');
		$this->db->where('c.is_deleted', '0');
		//$this->db->where('c.status', '1');
		if ($userType == 14 || $userType == 3) {
			$this->db->where('c.created_by', $userId);
		}
		$this->db->group_start();
		$this->db->like('title', $search);
		$this->db->or_like('package_type', $search);
		$this->db->or_like('map_location', $search);
		$this->db->group_end();

		$this->db->order_by($orderColumn, $orderDir);
		$this->db->limit($limit, $start);
		//$this->db->limit(10);
		$result = $this->db->get()->result();
		//echo $this->db->last_query();exit;
		return $result;
	}

	/**
	 * Get all colleges with filtering, ordering, and pagination.
	 *
	 * @param int    $start  The starting index for pagination.
	 * @param int    $limit  The number of records to retrieve.
	 * @param string $order  The column to order by.
	 * @param string $dir    The direction of sorting.
	 * @return array The list of filtered and paginated colleges.
	 */

	public function getAllClg($start, $limit, $order, $dir, $userId, $userType)
	{
		$this->db->select("c.*, ci.city as cityname, s.statename, CONCAT(u1.f_name, ' ', u1.l_name) as created_by_name, CONCAT(u2.f_name, ' ', u2.l_name) as updated_by_name");
		$this->db->from($this->table . " c");
		$this->db->join('city ci', 'ci.id = c.cityid', 'left');
		$this->db->join('state s', 's.id = c.stateid', 'left');
		$this->db->join('users u1', 'u1.id = c.created_by', 'left');
		$this->db->join('users u2', 'u2.id = c.updated_by', 'left');

		$this->db->where('c.is_deleted', '0');
		//	$this->db->where('c.status', '1');
		if ($userType == 14 || $userType == 3) {
			$this->db->where('c.created_by', $userId);
		}
		$this->db->order_by($order, $dir);
		$this->db->limit($limit, $start);

		return $this->db->get()->result();
	}


	/**
	 * Check if an college exists .
	 *
	 * @param string $slug and $slug to check.
	 * @return int The count of college .
	 */
	public function chkIfExists($slug)
	{
		$this->db->where("slug", $slug);
		$this->db->where("is_deleted", 0);
		$query = $this->db->get($this->table);
		// echo $this->db->last_query(); 
		//exit; 

		return $query->num_rows();
	}

	/**
	 * Insert details for college into the database.
	 *
	 * @param array $data The data to be inserted.
	 * @return bool True if data insertion is successful, otherwise false.
	 */
	public function insertCollegeDetails($data)
	{
		$query = $this->db->insert($this->table, $data);
		return $this->db->insert_id();
	}

	/**
	 * Update the details of a college by ID.
	 *
	 * @param string $id   The ID of the college to be updated.
	 * @param array  $data An associative array containing the data to be updated.
	 * @return bool        True if the update operation is successful, otherwise false.
	 */
	public function updateCollegeDetails($id, $data)
	{
		$this->db->where("id", $id);
		$query = $this->db->update($this->table, $data);
		return $query;
	}


	/**
	 * Get the details of a college by ID.
	 *
	 * @param string $id The ID to retrieve college details.
	 * @return object The details of the college as an object.
	 */
	public function getCollegeDetailsById($id)
	{
		$this->db->where("id", $id);
		$this->db->where('is_deleted', '0');
		//$this->db->where('status', '1');
		return $this->db->get($this->table)->row();
	}
	public function getFaqByClgId($id)
	{
		$this->db->select('cf.*, GROUP_CONCAT(f.heading) as faq_name');
		$this->db->from('college_faq cf');
		$this->db->join('faq f', 'FIND_IN_SET(f.id, cf.faq_ids)', 'left');
		$this->db->where("cf.collegeid", $id);
		$this->db->group_by('cf.id');
		$query = $this->db->get();
		$result = $query->result_array();
		return $result;
	}
	/**
	 * Delete the details of a college by ID.
	 *
	 * @param string $id   The ID of the college to be deleted.
	 * @return bool        True if the delete operation is successful, otherwise false.
	 */
	public function deleteCollege($id, $data)
	{
		$this->db->where("id", $id);
		$query = $this->db->update($this->table, $data);
		return $query;
	}

	/**
	 * Check if an college exists while updatte.
	 *
	 * @param string $data,$id The State  to check.
	 * @return int The count of State .
	 */
	function chkWhileUpdate($clgId, $slug)
	{
		$this->db->where('slug', $slug);
		$this->db->where('is_deleted', '0');
		$this->db->where('id !=', $clgId);
		$query = $this->db->get($this->table);
		return $query->num_rows();
	}

	/**
	 * Insert docs details for exam into the database.
	 *
	 * @param array $data The data to be inserted.
	 * @return bool True if data insertion is successful, otherwise false.
	 */
	public function insertClgDocsDetails($data)
	{
		$query = $this->db->insert($this->imgtable, $data);
		$imageId['imageId'] = $this->db->insert_id();
		return  $imageId;
	}

	public function updateClgDocsDetails($id, $postid, $Arr)
	{
		$this->db->where("id", $id);
		$this->db->where("postid", $postid);
		$query = $this->db->update($this->imgtable, $Arr);
		return $query;
	}
	public function chkIfExistsCrs($clgId, $courseIds)
	{
		$this->db->from($this->clgCourse);
		$this->db->where('collegeid', $clgId);
		$this->db->where('courseid', $courseIds);
		$query = $this->db->get();
		return $query->num_rows();
	}


	public function updateCourseForClg($clgId, $courseIds, $Arr)
	{
		$this->db->where("collegeid", $clgId);
		$this->db->where('courseid', $courseIds);
		$query = $this->db->update($this->clgCourse, $Arr);
		//	echo $this->db->last_query();exit;
		return $query;
	}

	function deleteCourse($clgId)
	{
		$this->db->where("collegeid", $clgId);
		$query = $this->db->delete($this->clgCourse);
		return $query;
	}

	public function insertCourseForClg($Arr)
	{
		$query = $this->db->insert($this->clgCourse, $Arr);
		return $query;
	}

	public function chkIfExistsHighlights($clgId, $id, $text)
	{
		$this->db->from($this->clgHighlights);
		$this->db->where('collegeid', $clgId);
		$this->db->where('id', $id);
		// 		$this->db->where('text', $text);
		$query = $this->db->get();
		//echo $this->db->last_query();exit;
		return $query->num_rows();
	}

	public function updateHighlightsForClg($clgId, $Arr)
	{
		$this->db->where("collegeid", $clgId);
		$this->db->where("id", $Arr['id']);

		$query = $this->db->update($this->clgHighlights, $Arr);
		return $query;
	}

	public function insertHighlightsForClg($Arr)
	{
		$query = $this->db->insert($this->clgHighlights, $Arr);
		return $query;
	}


	public function chkIfExistsFeeStructure($clgId, $crsid, $text)
	{
		$this->db->from($this->clgFee);
		$this->db->where('college_id', $clgId);
		$this->db->where('course_id', $crsid);
		$this->db->where('details', $text);
		$query = $this->db->get();
		return $query->num_rows();
	}

	public function updateFeeStructureForClg($id, $clgId, $crid, $Arr)
	{
		$this->db->where("college_id", $clgId);
		$this->db->where("course_id", $crid);
		$this->db->where('id', $id);
		$query = $this->db->update($this->clgFee, $Arr);
		log_message('debug', 'Insert query: ' . $this->db->last_query());

		return $query;
	}

	public function insertFeeStructureForClg($Arr)
	{
		$query = $this->db->insert($this->clgFee, $Arr);

		// Log the query for debugging

		return $query;
	}

	public function getClgImageByClgId($Id)
	{
		$this->db->select('*');
		$this->db->from($this->imgtable);
		$this->db->where('postid', $Id);
		$this->db->where('type', 'college');
		return $this->db->get()->result();
	}

	public function deleteDoc($Id)
	{
		$this->db->where('id', $Id);
		return $this->db->delete($this->imgtable);
	}

	public function getClgCoursesByClgId($ClgId)
	{
		$this->db->select('cc.id,cc.collegeid,cc.courseid,cc.level,cc.duration,c.name');
		$this->db->from($this->clgCourse . " cc");
		$this->db->join('courses c', 'c.id = cc.courseid', 'left');
		$this->db->where('cc.collegeid', $ClgId);
		return $this->db->get()->result();
	}

	//
	public function getClgHighlightsByClgId($clgId)
	{
		$this->db->select('*');
		$this->db->from($this->clgHighlights);
		$this->db->where('collegeid', $clgId);
		return $this->db->get()->result();
	}

	public function updateFacilityForClg($clgId, $facilities)
	{
		$Arr = ['facilities' => $facilities];
		$this->db->where("id", $clgId);
		$query = $this->db->update($this->table, $Arr);
		return $query;
	}

	public function updateClgBrochuresDocsDetails($imgid, $postId, $updateArr)
	{

		$this->db->where("id", $imgid);
		$this->db->where("collegeid", $postId);
		$query = $this->db->update($this->brochures, $updateArr);

		return $query;
	}

	public function insertClgBrochuresDocsDetails($insertArr)
	{
		$this->db->insert($this->brochures, $insertArr);
		$imageId['imageId'] = $this->db->insert_id();
		return  $imageId;
	}

	public function getClgFeeStructureByClgId($Id)
	{
		//echo "tttt";exit;
		$this->db->select('cf.*,c.name as coursename');
		$this->db->from($this->clgFee . " cf");
		$this->db->join('courses c', 'c.id = cf.course_id', 'left');
		$this->db->where('college_id', $Id);
		//	$query = $this->db->get();
		// echo $this->db->last_query();
		return $this->db->get()->result();
	}

	public function getClgBrochuresByClgId($Id)
	{
		$this->db->select('*');
		$this->db->from($this->brochures);
		$this->db->where('collegeid', $Id);
		return $this->db->get()->result();
	}

	public function chkPlacementIfExists($clgId, $year, $courseCategory)
	{
		$this->db->from($this->academic_year);
		$this->db->where("year", $year);
		$this->db->where("collegeid", $clgId);
		$this->db->where("course_category", $courseCategory);
		$query = $this->db->get();
		// echo $this->db->last_query();
		return $query->num_rows();
	}

	public function chkPlacementIfExistsWhileUpdate($clgId, $year, $courseCategory, $id)
	{
		$this->db->from($this->academic_year);
		$this->db->where("year", $year);
		$this->db->where("collegeid", $clgId);
		$this->db->where("course_category", $courseCategory);
		$this->db->where("id !=", $id);

		$query = $this->db->get();
		// echo $this->db->last_query();
		return $query->num_rows();
	}

	public function updateAcademicYearForClg($Arr, $clgId, $year, $courseCategory)
	{

		$this->db->where("collegeid", $clgId);
		$this->db->where("year", $year);
		$this->db->where("course_category", $courseCategory);

		$query = $this->db->update($this->academic_year, $Arr);
		// echo $this->db->last_query();exit;
		return $query;
	}

	public function updateAcademicYearForClgById($Arr, $id)
	{

		$this->db->where("id", $id);
		$query = $this->db->update($this->academic_year, $Arr);
		return $query;
	}
	public function insertAcademicYearForClg($insertArr)
	{
		$this->db->insert($this->academic_year, $insertArr);
		$academicyearID['academicyearID'] = $this->db->insert_id();
		return  $academicyearID;
	}

	public function getClgPlacementsByClgId($id)
	{
		$this->db->where("collegeid", $id);
		$query = $this->db->get($this->academic_year);
		return $query->result();
	}

	public function getColleges($search_college = NULL)
	{
		// $this->db->where('is_deleted', '0');
		// $this->db->where('status', '1');
		// return $this->db->get($this->table)->result();
		$this->db->select("c.*");
		$this->db->from($this->table . " c");
		$this->db->join('city ci', 'ci.id = c.cityid', 'left');
		$this->db->join('state s', 's.id = c.stateid', 'left');
		if ($search_college !== NULL) {
			$this->db->like('title', $search_college);
		}
		$this->db->limit(10);
		return $this->db->get()->result();
	}

	public function chkRankIfExists($clgId, $category_id, $year, $rank)
	{
		$this->db->where("year", $year);
		$this->db->where("college_id", $clgId);
		$this->db->where("category_id", $category_id);
		$this->db->where("rank", $rank);

		$query = $this->db->get($this->college_ranks);
		return $query->num_rows();
	}

	public function updateRankForClg($Arr, $id)
	{
		// 		$this->db->where("college_id", $clgId);
		// 		$this->db->where("year", $year);
		// 		$this->db->where("id", $category_id);
		$this->db->where("rank_id", $id);

		$query = $this->db->update($this->college_ranks, $Arr);
		return $query;
	}

	public function insertRankForClg($insertArr)
	{
		$this->db->insert($this->college_ranks, $insertArr);
		$rankId['rankId'] = $this->db->insert_id();
		return  $rankId;
	}

	public function getClgRankByClgId($id)
	{
		$this->db->where("college_id", $id);
		$query = $this->db->get($this->college_ranks);
		return $query->result();
	}

	public function deleteRankForCollege($rankId)
	{
		$this->db->where('rank_id', $rankId);
		return $this->db->delete($this->college_ranks);
	}

	public function deleteAcadmicPlacements($placementId)
	{
		$this->db->where('id', $placementId);
		return $this->db->delete($this->academic_year);
	}

	public function update_ScholarshipsForClg($clgId, $scholarships)
	{
		$arr = ['scholarship' => $scholarships];
		$this->db->where("id", $clgId);
		$query = $this->db->update('college', $arr);
		return $query;
	}

	public function deleteHighlightsOfCollege($highlightsId)
	{
		$this->db->where('id', $highlightsId);
		return $this->db->delete($this->clgHighlights);
	}
	public function deleteBrochureOfCollege($brochureId)
	{
		$this->db->where('id', $brochureId);
		return $this->db->delete($this->brochures);
	}
	public function deleteFeeStructOfCollege($feeId)
	{
		$this->db->where('id', $feeId);
		return $this->db->delete($this->clgFee);
	}

	public function getPlacementCategory()
	{
		$this->db->select('*');
		$this->db->from($this->placement_category);
		return $this->db->get()->result();
	}

	public function getSampleFormat()
	{
		$this->db->select('id,name');
		$this->db->from($this->sample_format);
		return $this->db->get()->result();
	}

	public function getFormatDataUsingId($id)
	{
		$this->db->select('*');
		$this->db->from($this->sample_format);
		$this->db->where('id', $id);
		return $this->db->get()->result();
	}

	public function getCourseUsingClgId($clgId, $searchCrs = '')
	{
		$this->db->select('cc.courseid, c.name');
		$this->db->from('college_course cc');
		$this->db->join('courses c', 'c.id = cc.courseid', 'left');
		if ($searchCrs != '' && $searchCrs != NULL) {
			$this->db->like('c.name', $searchCrs);
		}
		$this->db->where('cc.collegeid', $clgId);
		$query = $this->db->get()->result();
		return $query;
	}


	public function getTrendingColleges($fromdate = '', $todate = '')
	{
		$this->db->select("c.*, ci.city as cityname, s.statename, CONCAT(u1.f_name, ' ', u1.l_name) as created_by_name, CONCAT(u2.f_name, ' ', u2.l_name) as updated_by_name");
		$this->db->from($this->table . " c");
		$this->db->join('city ci', 'ci.id = c.cityid', 'left');
		$this->db->join('state s', 's.id = c.stateid', 'left');
		$this->db->join('users u1', 'u1.id = c.created_by', 'left');
		$this->db->join('users u2', 'u2.id = c.updated_by', 'left');
		$this->db->where('c.is_deleted', '0');
		$this->db->where('c.status', '1');
		if (!empty($fromdate) && !empty($todate)) {
			$this->db->where('c.create_date >=', $fromdate);
			$this->db->where('c.create_date <=', $todate);
		} elseif (!empty($fromdate)) {
			$this->db->where('c.create_date >=', $fromdate);
		} elseif (!empty($todate)) {
			$this->db->where('c.create_date <=', $todate);
		}
		// 		$this->db->order_by('views', 'DESC');
		$this->db->order_by("CAST(views AS UNSIGNED)", "DESC");

		$this->db->limit(25);
		$query = $this->db->get();
		return $query->result();
	}

	public function getCollegeCategory($college_id)
	{
		$this->db->select('c.categoryid, GROUP_CONCAT(ct.catname) as categoryname');
		$this->db->from('college c');
		$this->db->join('category ct', 'FIND_IN_SET(ct.id, c.categoryid)', 'left');
		$this->db->where('c.id', $college_id);
		$this->db->group_by('c.categoryid');
		$query = $this->db->get();
		return $query->result();
	}

	public function getCollegeCourses($college_id, $category_id)
	{
		$this->db->select('cc.courseid ,c.name as coursename');
		$this->db->from('college_course cc');
		$this->db->join('courses c', 'c.id = cc.courseid', 'left');
		$this->db->where('cc.collegeid', $college_id);
		if (!empty($category_id)) {
			$this->db->where('c.course_category', $category_id);
		}
		$query = $this->db->get();
		return $query->result();
	}


	public function getExamsForCat($Id)
	{
		$this->db->select('sc.name, sc.id, ct.catname, cc.entrance_exams, GROUP_CONCAT(e.title) as examname');
		$this->db->from('college_course cc');
		$this->db->join('courses c', 'c.id = cc.courseid', 'left');
		$this->db->join('sub_category sc', 'sc.id = c.sub_category', 'left');
		$this->db->join('category ct', 'ct.id = sc.parent_category', 'left');
		$this->db->join('exams e', 'FIND_IN_SET(e.id, cc.entrance_exams)', 'left');
		$this->db->where('cc.collegeid', $Id);
		$this->db->where('cc.entrance_exams != ', '');

		$this->db->group_by('sc.id');
		$query = $this->db->get();
		// echo $this->db->last_query();exit;
		$result = $query->result();
		return $result;
	}

	
}
