<?php
/**
 * Courses Model
 *
 * @category   Models
 * @package    Admin
 * @subpackage courses
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    31 JAN 2024
 * 
 * Class courses_model handles all courses-related operations.
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Courses_model extends CI_Model {

    private $table = 'courses';
	private $courseEnq = 'course_inquiry';


    public function __construct() {
        parent::__construct();
    }

	/**
     * Get the count of all courses.
     *
     * @return int The count of courses.
     */
    public function countAllCourse()
    {
		$this->db->where('status',1);
        return $this->db->count_all($this->table);
    }

	/**
     * Count filtered courses based on the search term.
     *
     * @param string $search The search term.
     * @return int The number of filtered courses.
     */
    public function countFilteredCourse($search, $cat)
	{
		$this->db->join("academic_categories ac", "ac.category_id = c.academic_category", "left");
		$this->db->join("category ca", "ca.id = c.course_category", "left");
		if (!empty($cat)) {
			$this->db->where('ac.name', $cat);
		}
		$this->db->group_start();
		$this->db->like('c.name', $search);
		$this->db->or_like('ac.name', $search);
		$this->db->or_like('ca.catname', $search);
		$this->db->group_end();
		$query = $this->db->get($this->table . " c");
		//echo $this->db->last_query();exit;
		return $query->num_rows();
	}

	/**
     * Get filtered courses.
     *
     * @param string $search The search term.
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated courses with additional information.
     */

	 public function getFilteredCourse($search, $start, $limit, $order, $dir, $cat)
	{
		$this->db->select("c.*,ac.name as type,ca.catname as category");
		$this->db->from($this->table . " c");
		$this->db->join("academic_categories ac", "ac.category_id = c.academic_category", "left");
		$this->db->join("category ca", "ca.id = c.course_category", "left");
		if (!empty($cat)) {
			$this->db->where('ac.name', $cat);
		}
		$this->db->group_start();
		$this->db->like('c.name', $search);
		$this->db->or_like('ac.name', $search);
		$this->db->or_like('ca.catname', $search);

		$this->db->group_end();
		$this->db->order_by($order, $dir);
		$this->db->limit($limit, $start);
		$query = $this->db->get();
		$result = $query->result();
		//echo $this->db->last_query();exit;
		return $result;
	}


	 /**
     * Get all courses with filtering, ordering, and pagination.
     *
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated courses.
     */

	 public function getAllCourse($start, $limit, $order, $dir)
	 {
		$this->db->select("c.*,ac.name as type,ca.catname as category");
		$this->db->from($this->table. " c");
		$this->db->join("academic_categories ac", "ac.category_id = c.academic_category", "left");
		$this->db->join("category ca", "ca.id = c.course_category", "left");
		 $this->db->order_by($order, $dir);
		 $this->db->limit($limit, $start);
 
		 return $this->db->get()->result();
	 }

	 public function getTrendingCourses($fromdate='',$todate='')
	 {
		$this->db->select("c.*,ac.name as type,ca.catname as category");
		$this->db->from($this->table. " c");
		$this->db->join("academic_categories ac", "ac.category_id = c.academic_category", "left");
		$this->db->join("category ca", "ca.id = c.course_category", "left");
		if (!empty($fromdate) && !empty($todate)) {
			$this->db->where('create_dt >=', $fromdate);
			$this->db->where('create_dt <=', $todate);
		} elseif (!empty($fromdate)) {
			$this->db->where('create_dt >=', $fromdate);
		} elseif (!empty($todate)) {
			$this->db->where('create_dt <=', $todate);
		}
		$this->db->order_by('views', 'DESC');
		$this->db->limit(25);
		$query = $this->db->get();
		return $query->result();
	 }
	 public function getUGCourses($searchUg='')
	 {
		$this->db->select('c.*,ac.name as type');
		$this->db->from($this->table. " c");
		$this->db->join("academic_categories ac", "ac.category_id = c.academic_category", "left");
		if(!empty($searchUg))
		{
		
			$this->db->like('c.name', $searchUg);

		}
		$this->db->where('academic_category', 2);
		$query = $this->db->get();
		$result = $query->result();
		return $result;
	 }
	 
	public function searchPGCourses($searchUg = '', $collegeid = NULL) {
		//echo "tttt";exit;
        $this->db->select('c.id, c.name, ac.name as type, cc.collegeid');
        $this->db->from('courses c');
        $this->db->join('academic_categories ac', 'c.academic_category = ac.category_id', 'left');
        $this->db->join('college_course cc', 'cc.courseid = c.id', 'left');
        $this->db->where('ac.name', 'PG');
        $this->db->where('cc.collegeid', $collegeid);
        	if(!empty($searchUg))
        		{
        		
        			$this->db->like('c.name', $searchUg);
        
        		}
        
            $query = $this->db->get();
            $result = $query->result();
            return $result;
}



	 public function getUGCoursesDropDown($searchUg='',$collegeid = NULL)
	 {
		/*$this->db->select('c.id,c.name,ac.name as type');
		$this->db->from($this->table. " c");
		$this->db->join("academic_categories ac", "ac.category_id = c.academic_category", "left");
		if(!empty($searchUg))
		{
		
			$this->db->like('c.name', $searchUg);

		}
		$this->db->where('academic_category', 2);
		$query = $this->db->get();
		$result = $query->result();
		return $result;*/
		
		
		$this->db->select('c.id, c.name, ac.name AS type,c.duration,c.sub_category, FIND_IN_SET(c.id, (SELECT GROUP_CONCAT(cc.courseid) FROM college_course cc WHERE cc.collegeid='.$collegeid.')) AS CIds');
        $this->db->from('courses c');
        $this->db->join('academic_categories ac', 'c.academic_category = ac.category_id', 'left');
        $this->db->where('ac.name', 'UG');
        if(!empty($searchUg))
		{
		
			$this->db->like('c.name', $searchUg);

		}
        $this->db->order_by('CIds', 'DESC');
        
        $query = $this->db->get();
        return $query->result_array();
	 }
	 
	 public function getPGCoursesDropDown($searchPg='',$collegeid = NULL)
	 {
		/*$this->db->select('c.id,c.name,ac.name as type');
		$this->db->from($this->table. " c");
		$this->db->join("academic_categories ac", "ac.category_id = c.academic_category", "left");
		if(!empty($searchUg))
		{
		
			$this->db->like('c.name', $searchUg);

		}
		$this->db->where('academic_category', 2);
		$query = $this->db->get();
		$result = $query->result();
		return $result;*/
		
		
		$this->db->select('c.id, c.name, ac.name AS type,c.duration,c.sub_category, FIND_IN_SET(c.id, (SELECT GROUP_CONCAT(cc.courseid) FROM college_course cc WHERE cc.collegeid='.$collegeid.')) AS CIds');
        $this->db->from('courses c');
        $this->db->join('academic_categories ac', 'c.academic_category = ac.category_id', 'left');
        $this->db->where('ac.name', 'PG');
        if(!empty($searchPg))
		{
		
			$this->db->like('c.name', $searchPg);

		}
        $this->db->order_by('CIds', 'DESC');
        
        $query = $this->db->get();
        return $query->result_array();
	 }
	 public function getPGCourses($searchPg)
	 {
		$this->db->select('c.*,ac.name as type');
		$this->db->from($this->table. " c");
		$this->db->join("academic_categories ac", "ac.category_id = c.academic_category", "left");
		if(!empty($searchPg))
		{
		
			$this->db->like('c.name', $searchPg);

		}
		$this->db->where_in('academic_category', 3);
		$query = $this->db->get();
		$result = $query->result();
		return $result;
	 }
	 public function getDiplomaCourses($searchDp)
	 {
		$this->db->select('c.*,ac.name as type');
		$this->db->from($this->table. " c");
		$this->db->join("academic_categories ac", "ac.category_id = c.academic_category", "left");
		if(!empty($searchDp))
		{
		
			$this->db->like('c.name', $searchDp);

		}
		$this->db->where_in('academic_category', 1);
		$query = $this->db->get();
		$result = $query->result();
		return $result;
	 }
	 public function getDocCoursesDropdownlist($searchDoc,$collegeid)
	 {
	     
	    $this->db->select('c.id, c.name, ac.name AS type,c.duration,c.sub_category, FIND_IN_SET(c.id, (SELECT GROUP_CONCAT(cc.courseid) FROM college_course cc WHERE cc.collegeid='.$collegeid.')) AS CIds');
        $this->db->from('courses c');
        $this->db->join('academic_categories ac', 'c.academic_category = ac.category_id', 'left');
        $this->db->where('ac.name', 'Doctorial');
        if(!empty($searchDoc))
		{
		
			$this->db->like('c.name', $searchDoc);

		}
        $this->db->order_by('CIds', 'DESC');
        
        $query = $this->db->get();
        return $query->result_array();
	 }
	 
	 public function getAdvMasterCoursesdropdownlist($searcAdvMas,$collegeid)
	 {
	    $this->db->select('c.id, c.name, ac.name AS type,c.duration,c.sub_category, FIND_IN_SET(c.id, (SELECT GROUP_CONCAT(cc.courseid) FROM college_course cc WHERE cc.collegeid='.$collegeid.')) AS CIds');
        $this->db->from('courses c');
        $this->db->join('academic_categories ac', 'c.academic_category = ac.category_id', 'left');
        $this->db->where('ac.name', 'Advanced Masters');
        if(!empty($searcAdvMas))
		{
		
			$this->db->like('c.name', $searcAdvMas);

		}
        $this->db->order_by('CIds', 'DESC');
        
        $query = $this->db->get();
        return $query->result_array();
	 }
	 
	 public function getOtherCoursesDropdownlist($searchOther,$collegeid)
	 {
	      $this->db->select('c.id, c.name, ac.name AS type,c.duration,c.sub_category, FIND_IN_SET(c.id, (SELECT GROUP_CONCAT(cc.courseid) FROM college_course cc WHERE cc.collegeid='.$collegeid.')) AS CIds');
        $this->db->from('courses c');
        $this->db->join('academic_categories ac', 'c.academic_category = ac.category_id', 'left');
        $this->db->where('ac.name', 'Others');
        if(!empty($searchOther))
		{
		
			$this->db->like('c.name', $searchOther);

		}
        $this->db->order_by('CIds', 'DESC');
        
        $query = $this->db->get();
        return $query->result_array();
	 }
	 public function getDiplomaCoursesDropdown($searchDp='',$collegeid = NULL)
	 {
	     
	     	$this->db->select('c.id, c.name, ac.name AS type,c.duration,c.sub_category, FIND_IN_SET(c.id, (SELECT GROUP_CONCAT(cc.courseid) FROM college_course cc WHERE cc.collegeid='.$collegeid.')) AS CIds');
        $this->db->from('courses c');
        $this->db->join('academic_categories ac', 'c.academic_category = ac.category_id', 'left');
        $this->db->where('ac.name', 'Diploma');
        if(!empty($searchDp))
		{
		
			$this->db->like('c.name', $searchDp);

		}
        $this->db->order_by('CIds', 'DESC');
        
        $query = $this->db->get();
        return $query->result_array();
        
	    /*$this->db->select('c.*,ac.name as type');
		$this->db->from($this->table. " c");
		$this->db->join("academic_categories ac", "ac.category_id = c.academic_category", "left");
		if(!empty($searchDp))
		{
		
			$this->db->like('c.name', $searchDp);

		}
		$this->db->where_in('academic_category', 1);
		$query = $this->db->get();
		$result = $query->result();
		return $result;*/
	 }
	 public function getDocCourses($searchDoc)
	 {
		$this->db->select('c.*,ac.name as type');
		$this->db->from($this->table. " c");
		$this->db->join("academic_categories ac", "ac.category_id = c.academic_category", "left");
		if(!empty($searchDoc))
		{
		
			$this->db->like('c.name', $searchDoc);

		}
		$this->db->where_in('academic_category', 4);
		$query = $this->db->get();
		$result = $query->result();
		return $result;
	 }
	 public function getOtherCourses($searchOther)
	 {
		$this->db->select('c.*,ac.name as type');
		$this->db->from($this->table. " c");
		$this->db->join("academic_categories ac", "ac.category_id = c.academic_category", "left");
		if(!empty($searchOther))
		{
		
			$this->db->like('c.name', $searchOther);

		}
		$this->db->where_in('academic_category', 5);
		$query = $this->db->get();
		$result = $query->result();
		return $result;
	 }

	 public function getCourseEnqCount($fromdate='',$todate='')
    {
		if (!empty($fromdate) && !empty($todate)) {
			$this->db->where('create_date >=', $fromdate);
			$this->db->where('create_date <=', $todate);
		} elseif (!empty($fromdate)) {
			$this->db->where('create_date >=', $fromdate);
		} elseif (!empty($todate)) {
			$this->db->where('create_date <=', $todate);
		}
        return $this->db->count_all($this->courseEnq);
    }
	 /**
     * Check if an Courses exists .
     *
     * @param string $slug and $slug to check.
     * @return int The count of Courses .
     */
	public function chkIfExists($slug)
    {
        $this->db->where("slug", $slug);
        $query = $this->db->get($this->table);
        return $query->num_rows();
    }

	/**
     * Insert details details for course into the database.
     *
     * @param array $data The data to be inserted.
     * @return bool True if data insertion is successful, otherwise false.
     */
	public function insertCourseDetails($data)
	{
		$query = $this->db->insert($this->table, $data);
		 $CourseId['CourseId'] = $this->db->insert_id();
		 return  $CourseId;
	}

	/**
     * Check if an Course exists while updatte.
     *
     * @param string $data,$id The Course  to check.
     * @return int The count of Course .
     */
	function chkWhileUpdate($crsId,$slug) {
		$this->db->where('slug', $slug);
		$this->db->where('status', '1');
		$this->db->where('id !=', $crsId);
		$query = $this->db->get($this->table);
		return $query->num_rows();

	}

	public function updateCourseDetails($crsId,$Arr)
	{
		$this->db->where("id", $crsId);
        $query = $this->db->update($this->table, $Arr);
        return $query;
	}

	public function deleteCourses($id)
    {
        $this->db->where("id", $id);
        $query = $this->db->delete($this->table);
        return $query;
    }

	public function deleteClgCourses($courseid,$collegeid)
	{
		$this->db->where("courseid", $courseid);
		$this->db->where("collegeid", $collegeid);
        $query = $this->db->delete('college_course');
        return $query;
	}
	public function getCourseDetailsById($id)
	{
		$this->db->select('c.*, ac.name as academic_categoryName, ca.catname as course_categoryName');
		$this->db->from($this->table. " c");
		$this->db->join('academic_categories ac', 'ac.category_id = c.academic_category', 'left');
		$this->db->join('category ca', 'ca.id = c.course_category', 'left');
		$this->db->where('c.id', $id);
		$query = $this->db->get();
		$result = $query->result();
		return $result;

	}

	/**
     * Get the count of all CourseOffered.
     *
     * @return int The count of CourseOffered.
     */
    public function countAllCourseOffered()
    {
			$this->db->select('c.id AS Id, c.title AS college_name');
			$this->db->from('college c');
			$this->db->join('college_course cc', 'cc.collegeid = c.id', 'left');
			$this->db->join('courses cs', 'cs.id = cc.courseid', 'left');
			// $this->db->group_by('c.id');
			$query = $this->db->get();
			$count = $query->num_rows(); 
			return $count;
    }

	/**
     * Count filtered CourseOffered based on the search term.
     *
     * @param string $search The search term.
     * @return int The number of filtered CourseOffered.
     */
		public function countFilteredCourseOffered($search)
		{
			$this->db->select('c.id AS Id, c.title AS college_name, GROUP_CONCAT(cs.name) AS courses');
			$this->db->from('college c');
			$this->db->join('college_course cc', 'cc.collegeid = c.id', 'left');
			$this->db->join('courses cs', 'cs.id = cc.courseid', 'left');
			$this->db->group_by('c.id');
			$this->db->like('c.title', $search);
			$this->db->or_like('cs.name', $search);
			
			$query = $this->db->get();
			return $query->num_rows();
		}

		/**
     * Get filtered CourseOffered.
     *
     * @param string $search The search term.
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated CourseOffered with additional information.
     */

	 public function getFilteredCourseOffered($search, $start, $limit, $order, $dir)
		{
			$this->db->select('c.id AS Id, c.title AS college_name, GROUP_CONCAT(cs.name) AS courses');
			$this->db->from('college c');
			$this->db->join('college_course cc', 'cc.collegeid = c.id', 'left');
			$this->db->join('courses cs', 'cs.id = cc.courseid', 'left');
			$this->db->group_by('c.id');
			$this->db->like('c.title', $search);
			$this->db->or_like('cs.name', $search);
			$this->db->order_by($order, $dir);
			$this->db->limit($limit, $start);

			return $this->db->get()->result_array();
		}

	 /**
     * Get all CourseOffered with filtering, ordering, and pagination.
     *
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated CourseOffered.
     */

	 public function getAllCourseOffered($start, $limit, $order, $dir) {
		$this->db->select('c.id AS Id, c.title AS college_name');
		$this->db->from('college c');
		$this->db->join('college_course cc', 'cc.collegeid = c.id', 'left');
		$this->db->join('courses cs', 'cs.id = cc.courseid', 'left');
		// $this->db->group_by('c.id'); 
		$this->db->order_by($order, $dir);
		$this->db->limit($limit, $start);         
		$query = $this->db->get();
		return $query->result_array();
	}


	public function updateCourses($collegeId,$courseId,$NewArr)
	{
			$this->db->where('courseid', $courseId);
			$this->db->where('collegeid', $collegeId);
			return $this->db->update('college_course', $NewArr);
	}

	public function getCollegeCourseDetail($id, $clgid)
{
	$this->db->select('college_course.*, c.name, c.slug, GROUP_CONCAT(e.title) as examName');
	$this->db->join('courses c', 'c.id = college_course.courseid', 'left');
	$this->db->join('exams e', "FIND_IN_SET(e.id, college_course.entrance_exams)", 'left'); 
	
	$this->db->where('college_course.courseid', $id);
	$this->db->where('college_course.collegeid', $clgid);
	return $this->db->get('college_course')->result_array();
}

public function getPostDocCourses($searcPostDoc)
{
		$this->db->select('c.*,ac.name as type');
		$this->db->from($this->table. " c");
		$this->db->join("academic_categories ac", "ac.category_id = c.academic_category", "left");
		if(!empty($searcPostDoc))
		{
		
			$this->db->like('c.name', $searcPostDoc);

		}
		$this->db->where('academic_category', 7);
		$query = $this->db->get();
		$result = $query->result();
		return $result;
}
	
public function getAdvMasterCourses($searcAdvMas)
{
		$this->db->select('c.*,ac.name as type');
		$this->db->from($this->table. " c");
		$this->db->join("academic_categories ac", "ac.category_id = c.academic_category", "left");
		if(!empty($searcAdvMas))
		{
		
			$this->db->like('c.name', $searcAdvMas);

		}
		$this->db->where('academic_category', 6);
		$query = $this->db->get();
		$result = $query->result();
		return $result;
}

public function getCourses($search_courses = NULL)
	{
		$this->db->select("*");
		$this->db->from($this->table); 
		if ($search_courses !== NULL) {
			$this->db->like('name', $search_courses);
		}
		$this->db->limit(10);
		return $this->db->get()->result();

	}
	
public function saveExamForSubCat($NewArr, $collegeid)
{
    $this->db->where('categoryid', $NewArr['categoryid']);
    $this->db->where('collegeid', $collegeid);
    $result = $this->db->update('college_course', $NewArr);

    // Print the last query
   // echo $this->db->last_query();exit;

    return $result;
}

public function getCollegeSubCat($collegeid)
{
    $this->db->select('sc.name, sc.id,ct.catname');
    $this->db->from('college_course cc');
    $this->db->join('courses c', 'c.id = cc.courseid', 'left');
    $this->db->join('sub_category sc', 'sc.id = c.sub_category', 'left');
    $this->db->join('category ct', 'ct.id = sc.parent_category', 'left');
    $this->db->where('cc.collegeid', $collegeid);
    $this->db->group_by('sc.id');
    $query = $this->db->get();
    $result = $query->result();
	return $result;

}

public function getExamForSubCat($collegeid)
{
    $this->db->select('cc.categoryid, cc.entrance_exams, GROUP_CONCAT(DISTINCT e.title) AS exams,s.name as sub_categoryname,ct.catname');
    $this->db->from('college_course cc');
    $this->db->join('exams e', 'FIND_IN_SET(e.id, cc.entrance_exams) > 0', 'left');
    $this->db->join('sub_category s', 'cc.categoryid  = s.id', 'left');
    $this->db->join('category ct', 'ct.id = s.parent_category', 'left');

    $this->db->where('cc.collegeid', $collegeid);
    $this->db->where('cc.entrance_exams !=', '');
    $this->db->where('cc.entrance_exams IS NOT NULL');
    $this->db->group_by('cc.categoryid, cc.entrance_exams');
    $this->db->order_by('cc.categoryid', 'DESC');
    $query = $this->db->get();
    $result = $query->result();

	// echo $this->db->last_query();exit;

	return $result;

}

public function getExamForCollege($collegeid,$subcat)
{
    $this->db->select('cc.categoryid, cc.entrance_exams, GROUP_CONCAT(DISTINCT e.title) AS exams,s.name as sub_categoryname');
    $this->db->from('college_course cc');
    $this->db->join('exams e', 'FIND_IN_SET(e.id, cc.entrance_exams) > 0', 'left');
    $this->db->join('sub_category s', 'cc.categoryid  = s.id', 'left');

    $this->db->where('cc.collegeid', $collegeid);
    $this->db->where('cc.categoryid', $subcat);
    $this->db->where('cc.entrance_exams !=', '');
    $this->db->where('cc.entrance_exams IS NOT NULL');
    $this->db->group_by('cc.categoryid, cc.entrance_exams');
    $this->db->order_by('cc.categoryid', 'DESC');
    $query = $this->db->get();
    $result = $query->result();

	// echo $this->db->last_query();exit;

	return $result;

}

public function getCourse()
{
    $this->db->select('*'); 
    $query = $this->db->get('academic_categories');
    return $query->result();
}

public function getState(){
   // echo "tttt";exit;
   
   $this->db->select('*'); 
    $query = $this->db->get('state');
    return $query->result();
   
   
}
}
