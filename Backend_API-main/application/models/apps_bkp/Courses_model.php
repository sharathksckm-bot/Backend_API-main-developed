<?php

class Courses_model extends CI_Model
{    private $table = 'courses';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function getCoursesList($search_term = null)
    {
        $this->db->select("*");
        $this->db->from("courses");

        if ($search_term) {
            $this->db->like("name", $search_term);
        }
        $this->db->limit(10);

        $query = $this->db->get()->result_array();

        return $query;
    }

    public function countAllcourses()
    {
        $this->db->select("count(*) as course_count");
        $this->db->from("courses c");
        $this->db->where("c.status", "1");

        $result = $this->db->get()->result_array();

        if (!empty($result)) {
            return $result[0]["course_count"];
        } else {
            return 0;
        }
    }

    public function getCoursesByCatId($CatId)
    {
        $this->db->select('c.*,ac.name as courseLevel');
        $this->db->from('courses c');
        $this->db->join('academic_categories ac', 'ac.category_id = c.academic_category', 'left');
        $this->db->where('c.course_category', $CatId);
		$this->db->limit(8);
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }

    public function getCoursesByAcat_CCat($CouCat, $AcaCat)
    {
        $this->db->select('*');
        $this->db->from('courses');
        $this->db->where('course_category', $CouCat);
		$this->db->where('academic_category', $AcaCat);
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }

    public function getCourseCategory()
    {
        $this->db->select('category_id,name');
        $this->db->from('academic_categories');
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }

    public function getCourseByCategory($categoryId)
    {
        $this->db->select('id,name');
        $this->db->from('courses');
        $this->db->where('academic_category', $categoryId);
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }

    public function getCourseByCategoryClg($categoryId,$collegeId)
    {
        $this->db->select('cc.*, c.id, c.name');
        $this->db->from('college_course cc');
        $this->db->join('courses c', 'cc.courseid = c.id', 'left');
        $this->db->where('cc.collegeid', $collegeId);
        $this->db->where('c.academic_category', $categoryId);
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }
    public function saveCourseInquiry($Arr)
    {
        $this->db->insert('course_inquiry', $Arr);
        $insert_id = $this->db->insert_id();
        return $insert_id;
    }

    public function coursesOfferedInSameGroup($collegeId)
{
    $this->db->select('cc.*, cs.*, cs.name AS course_name, c.slug AS college_slug');
    $this->db->from('college c');
    $this->db->join('college_course cc', 'cc.collegeid = c.id', 'left');
    $this->db->join('courses cs', 'cs.id = cc.courseid', 'left');
    $this->db->where('c.id', $collegeId);
    $this->db->where('cc.collegeid', $collegeId);
    $this->db->limit(10);
    $query = $this->db->get();
    return $query->result_array();

	/* echo '<pre>';
	print_r($dt);
	exit; */
}

public function getCoursesOfCollege($collegeId,$subcategory = NULL,$courselevel = NULL,$total_fees = NULL,$exam_accepted = NULL,$CourseName = NULL)
{     
        if (!empty($total_fees)) {
            $sql = "SELECT cc.courseid, c.name, c.duration, cc.total_fees, cc.median_salary,cc.total_intake, cc.entrance_exams,
                    (SELECT GROUP_CONCAT(title) FROM exams WHERE FIND_IN_SET(id, cc.entrance_exams)) AS examNames
                    FROM college_course cc
                    LEFT JOIN courses c ON c.id = cc.courseid
                    WHERE cc.collegeid = ?";
            
            $sql .= " AND (
                        CASE
                            WHEN INSTR(?, '-') > 0 THEN
                                CAST(SUBSTRING_INDEX(cc.total_fees, '-', 1) AS UNSIGNED) BETWEEN CAST(SUBSTRING_INDEX(?, '-', 1) AS UNSIGNED) AND CAST(SUBSTRING_INDEX(?, '-', -1) AS UNSIGNED)
                            WHEN INSTR(?, '<') > 0 THEN
                                CAST(cc.total_fees AS UNSIGNED) < CAST(SUBSTRING_INDEX(?, '<', -1) AS UNSIGNED)
                            WHEN INSTR(?, '>') > 0 THEN
                                CAST(cc.total_fees AS UNSIGNED) > CAST(SUBSTRING_INDEX(?, '>', -1) AS UNSIGNED)
                            ELSE
                                CAST(cc.total_fees AS UNSIGNED) >= CAST(? AS UNSIGNED)
                        END
                    )";
            
            $query = $this->db->query($sql, array($collegeId, $total_fees, $total_fees, $total_fees, $total_fees, $total_fees, $total_fees, $total_fees, $total_fees));
        }
    else {  
    $this->db->select('cc.courseid, c.name, c.duration, cc.total_fees, cc.median_salary,cc.total_intake, cc.entrance_exams');
    $this->db->select('(SELECT GROUP_CONCAT(title) FROM exams WHERE FIND_IN_SET(id, cc.entrance_exams)) AS examNames');
    $this->db->from('college_course cc');
    $this->db->join('courses c', 'c.id = cc.courseid', 'LEFT');
    $this->db->where('cc.collegeid', $collegeId);
    if(!empty($subcategory))
    {
        $this->db->where('c.sub_category', $subcategory);

    }
    if(!empty($courselevel))
    {
        $this->db->where('c.academic_category', $courselevel);

    }
    if(!empty($exam_accepted))
    {
        $this->db->where("FIND_IN_SET('$exam_accepted', cc.entrance_exams) > 0");

    }
    if(!empty($CourseName))
    {
        $this->db->like('c.name', $CourseName);

    }
    $query = $this->db->get();
    //echo $this->db->last_query();exit;
    }
    $result = $query->result_array();
    return $result;

}

public function getCoursesBySubcategory($collegeId,$subcategory)
{
    $this->db->select('cc.courseid, c.name, c.duration, cc.total_fees, cc.median_salary, cc.entrance_exams');
    $this->db->select('(SELECT GROUP_CONCAT(title) FROM exams WHERE FIND_IN_SET(id, cc.entrance_exams)) AS examNames');
    $this->db->from('college_course cc');
    $this->db->join('courses c', 'c.id = cc.courseid', 'LEFT');
    $this->db->where('cc.collegeid', $collegeId);
    $this->db->where('c.sub_category', $subcategory);

    $query = $this->db->get();
    $result = $query->result();
    return $result;
}

public function getOtherCollegesOfferingSameCourseInSameCity($cityId)
{
    $this->db->select('title');
    $this->db->from('college');
    $this->db->where('cityid', $cityId);
    $this->db->where('package_type', 'featured_listing');
    $this->db->order_by('create_date','DESC');
    $this->db->limit(20);

    $query = $this->db->get();
    $result = $query->result();

    return $result;

}

public function getFeesDataOfCollege($id)
{
  $this->db->select('total_fees');
  $this->db->from('college_course');
  $this->db->where('collegeid', $id); 
  $this->db->where('(total_fees IS NOT NULL AND total_fees != "")');
  $query1 = $this->db->get();
  $result1 = $query1->result_array();

  $this->db->select('MIN(CAST(TRIM(SUBSTRING_INDEX(total_fees, "-", 1)) AS UNSIGNED)) AS lowest_fee');
  $this->db->select('MAX(CAST(TRIM(SUBSTRING_INDEX(total_fees, "-", -1)) AS UNSIGNED)) AS highest_fee');
  $this->db->from('college_course');
  $this->db->where('collegeid', $id); 
  $this->db->where('total_fees IS NOT NULL');
  $this->db->where('total_fees !=', '');
  $query2 = $this->db->get();
  $result2 = $query2->row_array();

  return array(
      'all_fees' => $result1,
      'lowest_fee' => $result2['lowest_fee'],
      'highest_fee' => $result2['highest_fee']
  );

   
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
    public function countFilteredCourse($search)
	{
		$this->db->join("academic_categories ac", "ac.category_id = c.academic_category", "left");
		$this->db->join("category ca", "ca.id = c.course_category", "left");
		// if (!empty($cat)) {
		// 	$this->db->where('ac.name', $cat);
		// }
		$this->db->group_start();
		$this->db->like('c.name', $search);
		// $this->db->or_like('ac.name', $search);
		// $this->db->or_like('ca.catname', $search);
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

	 public function getFilteredCourse($search, $start, $limit, $order, $dir)
	{
		$this->db->select("c.*,ac.name as type,ca.catname as category");
		$this->db->from($this->table . " c");
		$this->db->join("academic_categories ac", "ac.category_id = c.academic_category", "left");
		$this->db->join("category ca", "ca.id = c.course_category", "left");
		// if (!empty($cat)) {
		// 	$this->db->where('ac.name', $cat);
		// }
		$this->db->group_start();
		$this->db->like('c.name', $search);
		// $this->db->or_like('ac.name', $search);
		// $this->db->or_like('ca.catname', $search);

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
public function getAcademicCategory($collegeId)
    {
        $this->db->select('ac.category_id as id, ac.name, COUNT(c.academic_category) as totalCount');
        $this->db->from('college_course cc');
        $this->db->join('courses c', 'c.id = cc.courseid', 'left');
        $this->db->join('academic_categories ac', 'ac.category_id = c.academic_category', 'left');
        $this->db->where('cc.collegeid', $collegeId);
        $this->db->where('c.academic_category IS NOT NULL', null, false);
        $this->db->group_by('c.academic_category');
        $query = $this->db->get();
        $result = $query->result();
        return $result;

    }
}
