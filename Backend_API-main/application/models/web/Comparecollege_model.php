<?php

class Comparecollege_model extends CI_Model
{

    public function __construct()
    {

        parent::__construct(); {
            $this->load->database();
        }
    }
    public function getState()
    {
        $this->db->select('*');
        $this->db->from('state');
        $query = $this->db->get()->result_array();
        return $query;
    }

    public function getCityByState($stateId)
    {

        $this->db->select('*');
        $this->db->from('city');
        $this->db->where('stateid', $stateId);

        $query = $this->db->get()->result_array();

        return $query;
    }

    public function getcc()
    {

        $this->db->select('*');
        $this->db->from('category');
        $query = $this->db->get()->result_array();
        return $query;
    }

    public function getcourse($cat)
    {

        $this->db->select('*');
        $this->db->from('courses');
        $this->db->where('category', $cat);
        $query = $this->db->get()->result_array();
        return $query;
    }

    public function getCollegeList($stateId, $cityId, $categoryId, $courseId)
    {
        $this->db->select('college.*');
        $this->db->from('college');
        $this->db->join('college_course', 'college.id = college_course.collegeid', 'left');

        if ($stateId) {
            $this->db->where('college.stateid', $stateId);
        }
        if ($cityId) {
            $this->db->where('college.cityid', $cityId);
        }
        if ($categoryId) {
            $this->db->where('college.categoryid', $categoryId);
        }
        if ($courseId) {
            $this->db->where('college_course.courseid', $courseId);
        }

        $query = $this->db->get()->result_array();
        return $query;
    }

    public function getAllClg($searchTerm, $start, $limit)
    {
        $this->db->select('id, title, logo');
        $this->db->from('college c');
        $this->db->where('c.title LIKE ', '%' . $searchTerm . '%');
        $this->db->where('c.is_deleted', 0);
        $this->db->where('c.status', 1);
        $this->db->order_by('id', 'asc');
        $this->db->limit($limit, $start);
        $query = $this->db->get()->result_array();
        //echo $this->db->last_query();exit;
        return $query;
    }
    public function getlevel($id)
    {
        $this->db->select('level, collegeid');
        $this->db->from('college_course');
        $this->db->where('collegeid ', $id);
        $this->db->where('level !=', '');
        $this->db->group_by('level');
        $query = $this->db->get()->result_array();
        //echo $this->db->last_query();exit;
        return $query;
    }
    public function getPGcourses($id)
    {
        /*$this->db->select('id, name');
        $this->db->from('course_pg');
        $this->db->where('collegeid ', $id);
        $query = $this->db->get()->result_array();
        //echo $this->db->last_query();exit;
        return $query;*/
        
         $this->db->select('c.id, c.name');
        $this->db->from('college_course cc');
        $this->db->join('courses c', 'cc.courseid = c.id', 'left');

        $this->db->where('cc.collegeid', $id);
        $this->db->where('cc.level', 'PG');

        $query = $this->db->get()->result_array();
        //echo $this->db->last_query();exit;
        return $query;
    }
    public function getUGcourses($id)
    {
        $this->db->select('c.id, c.name');
        $this->db->from('college_course cc');
        $this->db->join('courses c', 'cc.courseid = c.id', 'left');

        $this->db->where('cc.collegeid', $id);
        $this->db->where('cc.level', 'UG');

        $query = $this->db->get()->result_array();
        //echo $this->db->last_query();exit;
        return $query;
    }
    public function getFeaturedColleges()
    {
        $this->db->select('c.id, c.title, c.slug, c.description, c.address, c.web, c.estd, g.image');
        $this->db->from('college c');
        $this->db->join('gallery g', 'g.postid = c.id', 'left');
        $this->db->where('c.package_type', 'featured_listing');
        $this->db->where('c.status', '1');
        $this->db->where('c.is_deleted', '0');
        $this->db->where('g.type', 'college');
        $this->db->group_by('c.id');
        $this->db->order_by('c.id', 'DESC');
        $this->db->limit(6);
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }
    public function getCollegeDetailsByID($id)
    {
        $this->db->select('c.id, c.title, c.description, c.accreditation, c.package_type, logo, c.title, banner, estd, ci.city, co.country, g.image, ca.catname, ct.name,c.categoryid');
        $this->db->from('college c');
        $this->db->join('city ci', 'ci.id = c.cityid', 'left');
        $this->db->join('country co', 'co.id = c.countryid', 'left');
        $this->db->join('category ca', 'ca.id = c.categoryid', 'left');
        $this->db->join('college_type ct', 'ct.id = c.college_typeid', 'left');
        $this->db->join('gallery g', 'g.postid = c.id', 'left');
        $this->db->where('c.is_deleted', '0');
        $this->db->where('c.status', '1');
        $this->db->where("c.id", $id);
        $this->db->limit(1, 1);
        $query = $this->db->get();
        $result = $query->result_array();
        //echo $this->db->last_query();      exit;
        return $result;
    }
    public function getCollegeHighlightByID($id)
    {
        $this->db->select('text');
        $this->db->from('college_highlights ch');
        $this->db->where("ch.collegeid", $id);
        $query = $this->db->get();
        $result = $query->result_array();
        //echo $this->db->last_query();exit;
        return $result;
    }
    public function getCourses($id,$courselevel,$subcategory)
    {
         $this->db->select('cc.level, ca.name as coursename,ca.duration');
        $this->db->from('college_course cc');
        $this->db->where("collegeid", $id);
        if(!empty($courselevel) && !empty($subcategory))
            {
        $this->db->where("level", $courselevel);
        $this->db->where("courseid", $subcategory);
            }
            
        $this->db->join('courses ca', 'ca.id = cc.courseid', 'left');
        // $this->db->limit(1, 1);

        $query = $this->db->get();
        $result = $query->result_array();
        // echo $this->db->last_query();      exit;
        return $result;
    }
    
    public function getCollegeCoursesByID($id)
    {
        $this->db->where("collegeid", $id);
        $query = $this->db->get('college_course');
        return $query->num_rows();
    }

    public function getReviewRatingByClgId($Id)
    {
        $this->db->select('title,placement_rate,infrastructure_rate,faculty_rate,hostel_rate,campus_rate,money_rate');
        $this->db->from('review');
        $this->db->where('college_id', $Id);
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }

    public function getRankListByClgId($clgId)
    {
        $this->db->select('rc.title, cr.rank, cr.year');
        $this->db->from('rank_categories rc');
        $this->db->join('college_ranks cr', 'rc.category_id = cr.category_id AND cr.college_id = "' . $clgId . '"', 'left');
        $this->db->where('rc.is_active', 1);
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }

    public function getAcademicDataByClgId($id)
    {
        $this->db->select('ay.year,ay.no_of_companies_visited,ay.no_of_students_placed,ay.median_salary,ay.no_of_student_selected');
        $this->db->from('academic_year ay');
        $this->db->where('ay.collegeid', $id);
        $this->db->order_by('ay.id','DESC');
        $this->db->limit(1);
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }
    
    	public function getCoursesAndFeesOfClg($collegeId,$level,$subcategory)
	{
		$this->db->select('c.id, cc.total_fees, cc.eligibility, c.name, ac.name as academicCategoryName, c.duration,  ct.catname as courseCategoryName,  sc.name as subCategoryName');
		$this->db->from('college_course cc');
		$this->db->join('courses c', 'c.id = cc.courseid', 'left');
		$this->db->join('sub_category sc', 'sc.id = c.sub_category', 'left');
		$this->db->join('category ct', 'ct.id = c.course_category', 'left');
		$this->db->join('academic_categories ac', 'ac.category_id = c.academic_category', 'left');
		$this->db->where('cc.collegeid', $collegeId);
		$this->db->where('ac.name', $level);
		if(!empty($subcategory))
		{
		$this->db->where('cc.courseid', $subcategory);
        }
		$this->db->limit(1);
		$query = $this->db->get();
		$result = $query->result();
		return $result;
	}
	
		public function getCollegeAdmissionProcess($collegeId,$subcat)
	{
	    $this->db->select("COUNT(cc.courseid) AS courseCount, 
                   cc.entrance_exams, 
                   GROUP_CONCAT(DISTINCT e.title ORDER BY e.title SEPARATOR ', ') AS Accepting_Exams, 
                   cc.eligibility, 
                   c.sub_category, 
                   sc.name AS subCatName, 
                   c.duration", false);
		//$this->db->select('COUNT(cc.courseid) AS courseCount, cc.entrance_exams, GROUP_CONCAT(e.title) AS Accepting_Exams, cc.eligibility, c.sub_category, sc.name as subCatName, c.duration');
		$this->db->from('college_course cc');
		$this->db->join('courses c', 'c.id = cc.courseid', 'left');
		$this->db->join('sub_category sc', 'sc.id = c.sub_category', 'left');
		$this->db->join('exams e', 'FIND_IN_SET(e.id, cc.entrance_exams) > 0', 'left');
		$this->db->where('cc.collegeid', $collegeId);
			$this->db->where('c.sub_category IS NOT NULL');
    	if(!empty($subcat))
    		{
    		$this->db->where('c.id', $subcat);
            }
		$this->db->group_by('sc.id');
		$this->db->order_by('sc.id');
		$this->db->limit(1);
		$query = $this->db->get();
       // echo $this->db->last_query();exit;
		$result = $query->result();
		return $result;
	}
	
	
	public function getCollegeFacilities($id)
	{
	    $this->db->select('GROUP_CONCAT(f.title) AS facilities,GROUP_CONCAT(f.icon) AS icons');
		$this->db->from('college c');
		$this->db->join('facilities f', 'FIND_IN_SET(f.id, c.facilities) > 0', 'left');
		$this->db->where('c.id', $id);
		$query = $this->db->get();
        // echo $this->db->last_query();exit;
		$result = $query->result();
		return $result;
	}
}
