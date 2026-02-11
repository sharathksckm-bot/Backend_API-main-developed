<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Campus_app_model extends CI_Model
{
    private $table = 'college';
    private $clgCourse = 'college_course';
    public function __construct()
    {
        parent::__construct();
    }

    public function register($userData)
    {
        $this->db->insert('users', $userData);
	    //echo $this->db->last_query(); 
	    return $this->db->affected_rows() > 0; 
    }
	
	public function checkUser($email)
    {
        $this->db->select('*');
        $this->db->from('users');
		$this->db->where('email',$email);
        $query = $this->db->get()->result_array();
        //echo $this->db->last_query();exit;
        return $query;
    }
	public function validateOTP($email)
    {
        $this->db->select('OTP');
        $this->db->from('users');
        $this->db->where('email', $email);
        $query = $this->db->get()->row();
        if ($query) {
            return $query->OTP;
        } else {
            return null;
        }
    }
	
    /**
     * Get category by using category 
     * 
     * @param int $cat  The category id
     * @return string   The name of category
     */
    public function getCatName($cat)
    {
        $this->db->select('catname');
        $this->db->from('category');
        $this->db->where('id', $cat);
        $query = $this->db->get()->row();
        if ($query) {
            return $query->catname;
        } else {
            return null;
        }
    }

    /**
     * Get all Course details by category id.
     *
     * @param int    $cat  The category id.
     * @return array The list of Course details.
     */
    public function getExamCat($CatName)
    {
        $this->db->select('id');
        $this->db->from('category');
        $this->db->where('catname ', $CatName);
        $this->db->where('type  ', 'exams');
        $query = $this->db->get()->row();
        if ($query) {
            return $query->id;
        } else {
            return null;
        }
    }

    /**
     * Get exam details by course id.
     *
     * @param int    $cou  The course id.
     * @return array The list of exam details.
     */
    public function getExam($examCat)
    {
        $this->db->select('id, title');
        $this->db->from('exams');
        $this->db->where('categoryid', $examCat);
        $this->db->where('view_in_menu', '1');
        $this->db->limit(5);
        $query = $this->db->get()->result_array();
        //echo $this->db->last_query();exit;
        return $query;
    }

    /**
     * Get college specification.
     *
     * @return array The list of college specification.
     */
    public function getClgSpecification()
    {
        $this->db->select('*');
        $this->db->from('facilities');
        $this->db->where('status ', '1');
        $query = $this->db->get()->result_array();
        //echo $this->db->last_query();exit;
        return $query;
    }

    /**
     * Get all frequently asked question.
     *
     * @return array The list of frequently asked question.
     */
    public function getFaQ()
    {
        $this->db->select('*');
        $this->db->from('faq');
        $this->db->where('categoryid ', '222');
        $query = $this->db->get()->result_array();
        //echo $this->db->last_query();exit;
        return $query;
    }

    /**
     * Get all blog.
     *
     * @return array The list of blog.
     */
    public function getBlog()
    {
        $this->db->select('*');
        $this->db->from('blog');
        $this->db->where('image IS NOT NULL');
        $this->db->where('image IS NOT NULL');
        $this->db->where('title IS NOT NULL');
        $this->db->where('categoryid', '4');
        $this->db->order_by('id', 'desc');
        $this->db->limit(5);
        $query = $this->db->get()->result_array();
        //echo $this->db->last_query();exit;
        return $query;
    }

    /**
     * Get all popular colleges by category id.
     *
     * @param int $cat  The category id.
     * @return array The list of popular colleges.
     */
    public function getPopCollege($cat)
    {
        $this->db->select('c.id, c.title');
        $this->db->from('college c');
        $this->db->join('city ci', 'ci.id = c.cityid', 'left');
        $this->db->join('state s', 's.id = c.stateid', 'left');
        $this->db->join('gallery g', 'g.postid = c.id', 'left');
        $this->db->where('c.package_type', 'featured_listing');
        $this->db->like('c.categoryid ', $cat);
        $this->db->where('c.status', '1');
        $this->db->where('c.is_deleted', '0');
        $this->db->where('g.type', 'college');
        $this->db->group_by('c.id');
        $this->db->order_by('c.id', 'DESC');
        $this->db->limit(10);
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }

    /**
     * Get all colleges by rank by category id.
     *
     * @param int    $cat  The category id.
     * @return array The list of colleges by rank.
     */
    public function getCollegeListByRank($cat)
    {
        $this->db->select('c.id, c.title');
        $this->db->from('college_course cc');
        $this->db->join('college c', 'c.id=cc.collegeid', 'left');
        $this->db->join('courses cs', 'cs.id=cc.courseid', 'left');
        $this->db->join('college_ranks cr', 'cr.college_id=cc.collegeid', 'left');
        $this->db->join('city ci', 'ci.id = c.cityid', 'left');
        $this->db->join('rank_categories rc', 'rc.category_id=cr.category_id', 'left');
        $this->db->join('brochures b', 'b.collegeid = cc.collegeid', 'left');
        $this->db->like('c.categoryid ', $cat);
        $this->db->where('c.title IS NOT NULL');
        $this->db->where('b.file IS NOT NULL');
        $this->db->where('cr.rank IS NOT NULL');
        $this->db->order_by('cr.rank', 'ASC');
        $this->db->group_by('cr.rank');
        $this->db->limit(10);
        $query = $this->db->get();
        $result = $query->result();
        //echo $this->db->last_query();exit;
        return $result;
    }

    /**
     * Get all colleges count.
     *
     * @return int The count of all college.
     */
    public function countAllClg()
    {
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
     * Get count of colleges by location and course.
     *
     * @param int    $loc  The location id.
     * @param string $course  The Course name.
     * @return int The count of colleges by location and course.
     */
    public function countFilteredClgByLoc($loc, $course)
    {
        $this->db->select('*');
        $this->db->join('college c', 'c.id = cc.collegeid', 'left');
        $this->db->join('gallery g', 'g.postid = c.id', 'left');
        $this->db->join('brochures b', 'b.collegeid = cc.collegeid', 'left');
        $this->db->join('city ci', 'ci.id = c.cityid', 'left');
        $this->db->join('courses co', 'co.id = cc.courseid', 'left');
        $this->db->where('c.is_deleted', '0');
        $this->db->where('c.status', '1');
        $this->db->like('co.name', $course);
        $this->db->where("ci.id", $loc);

        $query =  $this->db->get('college_course cc')->num_rows();
        //echo $this->db->last_query();exit;
        return $query;
    }

    /**
     * Get list of colleges by location and course.
     *
     * @param int    $loc  The location id.
     * @param string $course  The Course name.
     * @return array The list of colleges by location and course.
     */
    public function getClgListByLoc($loc, $course,$startlimit=0,$endlimit=10)
    {
       
       $locArray = array_map('intval', explode(",", $loc));
       
        // $locations = explode(',', $loc);
         // print_r($locArray);exit;
        $this->db->select('c.id,ci.id as cityid, c.title, c.address, c.phone, c.email, c.categoryid, cc.total_fees, ci.city, c.web, c.logo, g.image, b.file, cc.total_fees, c.accreditation');
        $this->db->from('college_course cc');
        $this->db->join('college c', 'c.id = cc.collegeid', 'left');
          $this->db->join('city ci', 'ci.id = c.cityid', 'left');
        $this->db->join('gallery g', 'g.postid = c.id', 'left');
        $this->db->join('brochures b', 'b.collegeid = cc.collegeid', 'left');
       // $this->db->join('city ci', 'ci.id = c.cityid', 'left');
        $this->db->join('courses co', 'co.id = cc.courseid', 'left');
        $this->db->where_in("ci.id", $locArray);
        $this->db->where('c.is_deleted', '0');
        $this->db->where('c.status', '1');
        $this->db->where('cc.categoryid', $course);
        
        $this->db->group_by('c.id');
        $this->db->limit($endlimit, $startlimit);

        $query = $this->db->get()->result_array();
       // echo $this->db->last_query();exit;
        return $query;
    }

    /**
     * Get list of colleges by location and course.
     *
     * @param int    $loc  The location id.
     * @param string $course  The Course name.
     * @return array The list of colleges by location and course.
     */
    public function getCollegeListByLoc($loc, $course, $limit)
    {
        $this->db->select('c.id, c.title, c.address, c.phone, c.email, c.categoryid, cc.total_fees, ci.city, c.web, c.logo, g.image, b.file, cc.total_fees, c.accreditation , co.name');
        $this->db->from('college_course cc');
        $this->db->join('college c', 'c.id = cc.collegeid', 'left');
        $this->db->join('gallery g', 'g.postid = c.id', 'left');
        $this->db->join('brochures b', 'b.collegeid = cc.collegeid', 'left');
        $this->db->join('city ci', 'ci.id = c.cityid', 'left');
        $this->db->join('courses co', 'co.id = cc.courseid', 'left');
        $this->db->where('c.is_deleted', '0');
        $this->db->where('c.status', '1');
        $this->db->like('c.categoryid', $course);
        $this->db->where("ci.id", $loc);
        $this->db->group_by('c.id');
        $this->db->limit(10);
        echo $this->db->last_query();exit;
        $query = $this->db->get()->result_array();
        //echo $this->db->last_query();exit;
        return $query;
    }
	
	public function getCollegeListByLoc__($loc, $course)
    {
        $this->db->select('c.id, c.title, c.address, c.phone, c.email, c.categoryid, cc.total_fees, ci.city, c.web, c.logo, g.image, b.file, cc.total_fees, c.accreditation , co.name');
        $this->db->from('college_course cc');
        $this->db->join('college c', 'c.id = cc.collegeid', 'left');
        $this->db->join('gallery g', 'g.postid = c.id', 'left');
        $this->db->join('brochures b', 'b.collegeid = cc.collegeid', 'left');
        $this->db->join('city ci', 'ci.id = c.cityid', 'left');
        $this->db->join('courses co', 'co.id = cc.courseid', 'left');
        $this->db->where('c.is_deleted', '0');
        $this->db->where('c.status', '1');
        $this->db->like('c.categoryid', $course);
        $this->db->where("ci.id", $loc);
        $this->db->group_by('c.id');
        $this->db->limit(10);
        $query = $this->db->get()->result_array();
        //echo $this->db->last_query();exit;
        return $query;
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

    public function getAllClg($start, $limit, $order, $dir)
    {
        $this->db->select("*");
        $this->db->from($this->table);
        $this->db->where('is_deleted', '0');
        $this->db->where('status', '1');
        $this->db->order_by($order, $dir);
        $this->db->limit($limit, $start);
        $query = $this->db->get()->result_array();
        //echo $this->db->last_query();exit;
        return $query;
    }

    /**
     * Get list of colleges by Fees.
     *
     * @param int    $min_fees  The minimum fees.
     * @param int $max_fees  The maximun fees.
     * @return array The list of colleges by Fees.
     */
    public function getClgbyFees($min_fees, $max_fees,$stateName, $course = '',$startlimit=0,$endlimit=5)
{
      // print_r($stateName);exit;
       
       if (!is_array($stateName)) {
        $stateName = [$stateName]; // Convert to array if single string is passed
    }
    // First query to get college courses within the fee range
    $this->db->select('c.id as collegeid, c.accreditation, c.title, c.logo, ci.city, cc.total_fees, cr.rank, cr.year, b.file');
    $this->db->from('college_course cc');
    $this->db->join('college c', 'c.id = cc.collegeid', 'left');
    $this->db->join('brochures b', 'b.collegeid = cc.collegeid', 'left');
    $this->db->join('city ci', 'ci.id = c.cityid', 'left');
    $this->db->join('counseling_fees cf','cc.categoryid = cf.sub_category','left');
    $this->db->join('state s','ci.stateid = s.id','left');
    $this->db->join('college_ranks cr', 'cr.college_id = cc.collegeid', 'left');
    
    $this->db->where('c.is_deleted', '0');
    $this->db->where('c.status', '1');

    $this->db->where('cc.categoryid', $course);
    $this->db->where('COALESCE(cc.total_fees, cf.fees) >=', $min_fees);
    $this->db->where('COALESCE(cc.total_fees, cf.fees) <=', $max_fees);

    $this->db->group_by('c.id');
    $query = $this->db->get()->result_array();
    //echo $this->db->last_query();exit;
    foreach ($query as &$val) {
      // print_r($val['total_fees']);exit;
        // Check if total_fees is NULL or empty
       
          
    $this->db->select('c.id AS collegeid,cf.sub_category, c.accreditation, c.title, c.logo, ci.city, cf.fees as total_fees, cr.rank, cr.year, b.file,c.college_typeid,cc.categoryid');
    $this->db->from('college c');
    
    // Consider changing to INNER JOIN if non-matching rows can be ignored
    $this->db->join('brochures b', 'b.collegeid = c.id', 'left');
    $this->db->join('city ci', 'ci.id = c.cityid', 'left');
    $this->db->join('college_ranks cr', 'cr.college_id = c.id', 'left');
    $this->db->join('counseling_fees cf', 'cf.college_type = c.college_typeid', 'left');
    $this->db->join('college_course cc', 'cc.collegeid = c.id', 'left');
    $this->db->join('state s','ci.stateid = s.id','left');

    $this->db->where('c.is_deleted', 0);
    $this->db->where('c.status', 1);
    $this->db->where('cc.categoryid', $course);
    $this->db->where('cf.sub_category',$course);
    $this->db->where_in('s.statename',$stateName);
    $this->db->where('cf.fees >=', $min_fees);
    $this->db->where('cf.fees <=', $max_fees);
    
    $this->db->limit($endlimit,$startlimit);
  
    // Consider using HAVING if aggregating
    $this->db->group_by('c.id');
        
    $query = $this->db->get();
 // echo $this->db->last_query();exit; 
    $query = $query->result_array();
    return $query;
        }
    // If no results found, fallback to a second query with counseling_fees
    

    
}




    public function getCollegebyFees($min_fees, $max_fees, $course)
    {
        $this->db->select('c.id as collegeid, c.accreditation, c.title, c.logo, ci.city, cc.total_fees, cr.rank, cr.year, b.file');
        $this->db->from('college_course cc');
        $this->db->join('college c', 'c.id = cc.collegeid', 'left');
        $this->db->join('brochures b', 'b.collegeid = cc.collegeid', 'left');
        $this->db->join('city ci', 'ci.id = c.cityid', 'left');
        $this->db->join('college_ranks cr', 'cr.college_id = cc.collegeid', 'left');
        $this->db->where('c.is_deleted', '0');
        $this->db->where('c.status', '1');
        $this->db->where('cc.total_fees BETWEEN ' . $min_fees . ' AND ' . $max_fees);
        //$this->db->where('b.file IS NOT NULL');
        $this->db->group_by('c.id');
        $query = $this->db->get()->result_array();
        //echo $this->db->last_query();exit;
        return $query;
    }
    /**
     * Get count of colleges by fees.
     *
     * @param int    $min_fees  The minimum fees.
     * @param int $max_fees  The maximun fees.
     * @return int The count of colleges by fees.
     */
    public function countFilteredClgByfees($min_fees, $max_fees)
    {
        $this->db->select('*');
        $this->db->join('college c', 'c.id = cc.collegeid', 'left');
        $this->db->join('gallery g', 'g.postid = c.id', 'left');
        $this->db->join('brochures b', 'b.collegeid = cc.collegeid', 'left');
        $this->db->join('city ci', 'ci.id = c.cityid', 'left');
        $this->db->join('courses co', 'co.id = cc.courseid', 'left');
        $this->db->where('c.is_deleted', '0');
        $this->db->where('c.status', '1');
        $this->db->where('cc.total_fees BETWEEN ' . $min_fees . ' AND ' . $max_fees);
        $this->db->where('b.file IS NOT NULL');
        $this->db->group_by('c.id');
        $query =  $this->db->get('college_course cc')->num_rows();
        //echo $this->db->last_query();exit;
        return $query;
    }

    public function countCourseByClgID($ID)
    {
        $this->db->select('*');
        $this->db->where('cc.collegeid', $ID);
        $query =  $this->db->get('college_course cc')->num_rows();
        //echo $this->db->last_query();exit;
        return $query;
    }
    /**
     * Get list of courses by academic categroy and course category.
     *
     * @param int    $CouCat  The Course category id.
     * @param string $AcaCat  The academic category id.
     * @return array The list of courses by academic categroy and course category.
     */
    public function getCoursesByAcat_CCat($CouCat, $AcaCat)
    {
        $this->db->select('*');
        $this->db->from('sub_category sc');
        $this->db->where('sc.parent_category', $CouCat);
        $this->db->where('sc.academic_category', $AcaCat);
        $this->db->where('sc.status', '1');
        $query = $this->db->get();
        $result = $query->result();
        //echo $this->db->last_query();exit;
        return $result;
    }

    /**
     * Get colleges list by course id.
     *
     * @param int    $CourseId  The Course 
     * @return array  The list of colleges by course id.
     */
   /* public function getCollegeListByCourse($CourseId)
    {
        $this->db->select('c.id as collegeid, c.accreditation, c.title, c.logo, ci.city, c.address, cc.total_fees, cr.rank, cr.year, rc.title as category, b.file');
        $this->db->from('college_course cc');
        $this->db->join('college c', 'c.id=cc.collegeid', 'left');
        $this->db->join('courses cs', 'cs.id=cc.courseid', 'left');
        $this->db->join('college_ranks cr', 'cr.college_id=cc.collegeid', 'left');
        $this->db->join('city ci', 'ci.id = c.cityid', 'left');
        $this->db->join('rank_categories rc', 'rc.category_id=cr.category_id', 'left');
        $this->db->join('brochures b', 'b.collegeid = cc.collegeid', 'left');
        
        
        $this->db->where('cc.categoryid', $CourseId);
        $this->db->where('c.title IS NOT NULL');
        //$this->db->where('b.file IS NOT NULL');
        //$this->db->where('cr.rank IS NOT NULL');
        //$this->db->where('cc.total_fees IS NOT' ,'');
        $this->db->group_by('c.id');
        //$this->db->order_by('cr.rank', 'ASC');
        $this->db->limit(20);
        $query = $this->db->get()->result_array();
        //echo $this->db->last_query();exit;
        return $query;
    }*/

    public function getTotalFeesForCollege($typeid, $cat, $subcat,$exams) {
    if (!is_array($cat)) {$cat = explode(',', $cat); }
    if (!is_array($exams)) {
        $exams = !empty($exams) ? explode(',', $exams) : [];
    }

        $this->db->select("cf.*, COALESCE(NULLIF(cc.total_fees, ''), cf.fees) AS total_fees, cc.collegeid");
        $this->db->from("counseling_fees cf");
        $this->db->join("college_course cc", "cc.categoryid = cf.sub_category", "left");
        $this->db->where("cf.college_type", $typeid);
        $this->db->where_in("cf.category", $cat);
        $this->db->where("cf.sub_category",$subcat);
        //$this->db->where_in('exam_id', $exams);
        $query = $this->db->get();
        //echo $this->db->last_query();exit;

        $result = $query->result(); 
        
        return $result;

    /*  $this->db->select('cf.*, COALESCE(NULLIF(cf.fees, ""), cc.total_fees) AS total_fees');
    // $this->db->select('cf.*');
    $this->db->from('counseling_fees cf'); 
    $this->db->join('college_course cc', 'cc.categoryid = cf.sub_category', 'left');
    $this->db->where('college_type', $typeid); 
    $this->db->where_in('category', $cat); 
    $this->db->where('sub_category', $subcat); 
    // $this->db->where_in('exam_id', $exams);
    
    //$this->db->where("FIND_IN_SET('exam_id',".$exams."));


    $query = $this->db->get();
    //echo $this->db->last_query();exit;
    if ($query->num_rows() > 0) {
        return $query->result(); // Return the result set
    }
    
    return []; */
}


public function getCollegeListByCourse123bkp($CourseId,$stateId,$sLmit,$eLmit)
{
   // print_r($stateId);exit;
    $this->db->select('
        c.id as collegeid, 
        c.college_typeid,
        c.categoryid,
        c.accreditation, 
        c.title, 
        c.logo, 
        ci.city, 
        c.address, 
        IFNULL(cc.total_fees, cf.fees) as total_fees, 
        cr.rank, 
        cr.year, 
        rc.title as rankcategory, 
        b.file,
        s.statename
    ');
    $this->db->from('college_course cc');
    $this->db->join('college c', 'c.id=cc.collegeid', 'left');
    $this->db->join('courses cs', 'cs.id=cc.courseid', 'left');
    $this->db->join('college_ranks cr', 'cr.college_id=cc.collegeid', 'left');
    $this->db->join('city ci', 'ci.id = c.cityid', 'left');
    $this->db->join('state s', 's.id = ci.stateid', 'left');
    $this->db->join('rank_categories rc', 'rc.category_id=cr.category_id', 'left');
    $this->db->join('brochures b', 'b.collegeid = cc.collegeid', 'left');
    $this->db->join('counseling_fees cf', 'cf.category = cs.course_category 
                                           AND cf.sub_category = cs.sub_category 
                                           AND cf.college_type = c.college_typeid', 'left');  // Join counseling_fees table
    
    $this->db->where('cc.categoryid', $CourseId);
    $this->db->where('ci.stateid',$stateId);
    $this->db->where('c.title IS NOT NULL');
    $this->db->where('c.is_deleted', 0);
    $this->db->group_by('c.id');
   // $this->db->limit($sLimit, $eLimit);
    $this->db->limit($eLimit, $sLimit);

    
    $query = $this->db->get()->result_array();
   // $this->db->query();
 //echo $this->db->last_query();
    return $query;
}

public function getCollegeListByCourse123($CourseId,$statename, $sLmit,$eLmit)
{
   // echo "ttt";exit;      
//print_r($statename);exit;
    $this->db->select('
    c.id as collegeid, 
    c.college_typeid,
    c.categoryid,
    c.accreditation, 
    c.title, 
    c.logo, 
    ci.city, 
    c.address, 
    cr.rank, 
    cr.year, 
    rc.title as rankcategory, 
    b.file,
    s.statename,
    cc.entrance_exams,
    COALESCE(NULLIF(cc.total_fees, ""), cf.fees) AS total_fees, 
    cc.collegeid
    ');

    $this->db->from('college_course cc');
    $this->db->join('college c', 'c.id=cc.collegeid', 'left');
    $this->db->join('courses cs', 'cs.id=cc.courseid', 'left');
    $this->db->join('college_ranks cr', 'cr.college_id=cc.collegeid', 'left');
    $this->db->join('city ci', 'ci.id = c.cityid', 'left');
    $this->db->join('state s', 's.id = ci.stateid', 'left');
    $this->db->join('rank_categories rc', 'rc.category_id=cr.category_id', 'left');
    $this->db->join('brochures b', 'b.collegeid = cc.collegeid', 'left');
    $this->db->join('counseling_fees cf', 'cf.category = cs.course_category 
                                           AND cf.sub_category = cc.categoryid 
                                           AND cf.college_type = c.college_typeid
                                           AND cf.exam_id = cc.entrance_exams', 'left'); // Join counseling_fees table
   if (!empty($statename)) {
        $this->db->where('s.statename', $statename);
    }
    $this->db->where('cc.categoryid', $CourseId);
    
    $this->db->where('c.title IS NOT NULL');
    $this->db->where('c.is_deleted', 0);
     $this->db->where('c.status', 1);
     $this->db->where('cr.rank IS NOT NULL');
        $this->db->where('cr.rank !=' , '');
        $this->db->order_by("CASE WHEN rc.title = 'NIRF' THEN 0 ELSE 1 END", 'ASC');
        $this->db->order_by("CAST(cr.rank AS UNSIGNED)", 'ASC');
        //echo $this->db->last_query();
        // $this->db->group_by('cr.rank');
        //$this->db->group_by('cc.collegeid');
    $this->db->group_by('c.id');

    // Limit to 5 records starting from the offset $sLmit
    $this->db->limit($eLmit, $sLmit);
 //echo $this->db->last_query();exit;
    $query = $this->db->get()->result_array();
    return $query;
}
    /**
     * Get count of colleges by course id.
     *
     * @param int    $CourseId  The course id.
     * @param string $clgID  The college id.
     * @return int The count of colleges by course id.
     */
    public function countCoursesByCourseId($CourseId, $clgID)
    {
        $this->db->select('*');
        // $this->db->join('courses cs', 'cs.id=cc.courseid', 'left');
        // $this->db->join('college c', 'c.id=cc.collegeid', 'left');
        //$this->db->like('cc.categoryid', $CourseId);
        $this->db->where('cc.collegeid', $clgID);
        $query =  $this->db->get('college_course cc')->num_rows();
        //echo $this->db->last_query();
        return $query;
    }

    /**
     * Get all engineering colleges by rank.
     *
     * @return array The list of engineering colleges by rank.
     */
    public function getCollegesListByRanks($id)
    {
       // echo "tttt";exit;
        $this->db->select('c.id as collegeid, cr.rank,rc.title as rank_title, c.accreditation, c.title, c.logo, c.categoryid, ci.city, c.address,
         COALESCE(cc.total_fees, cf.fees) AS total_fees, cc.eligibility, cr.year, rc.title as category, b.file, s.statename ');
        $this->db->from('college_course cc');
        $this->db->join('college c', 'c.id=cc.collegeid', 'left');
        $this->db->join('courses cs', 'cs.id=cc.courseid', 'left');
        $this->db->join('college_ranks cr', 'cr.college_id=cc.collegeid', 'left');
        $this->db->join('city ci', 'ci.id = c.cityid', 'left');
        $this->db->join('rank_categories rc', 'rc.category_id=cr.category_id', 'left');
        $this->db->join('brochures b', 'b.collegeid = cc.collegeid', 'left');
        
        $this->db->join('state s','s.id = ci.stateid','left');
        
        $this->db->join('counseling_fees cf', 'cf.sub_category = cc.categoryid', 'left');
       
        $this->db->where('cc.categoryid ', $id);
        $this->db->where('c.title IS NOT NULL');
        $this->db->where('b.file IS NOT NULL');
        $this->db->where('cr.rank IS NOT NULL');
        $this->db->where('cr.rank != ""');
        $this->db->where('c.title !=','Mysore University (MU)');
        
       $this->db->order_by("CASE WHEN rc.title = 'NIRF' THEN 0 ELSE 1 END", 'ASC');
        $this->db->order_by("CAST(cr.rank AS UNSIGNED)", 'ASC');
        $this->db->order_by("CASE WHEN rc.title != 'NIRF' THEN 0 ELSE 1 END", 'ASC');
        // $this->db->group_by('cr.rank');
        $this->db->group_by('cc.collegeid');
        $this->db->limit(20);
        $query = $this->db->get();
        $result = $query->result();
        // echo $this->db->last_query();exit;
        return $result;
    }

    /**
     * Get all popular engineering colleges.
     *
     * @return array The list of popular engineeringcolleges.
     */
    public function getPopColleges($courseId,$categoryid,$getCollegeType)
    {
        //$collegeTypeArray = array_column($getCollegeType, 'college_type'); 
        $this->db->select('c.id as collegeid, cr.rank, c.accreditation,rc.title as rank_title, c.title, c.logo, c.categoryid, ci.city, c.address,
        cc.total_fees, cc.eligibility, cr.year, rc.title as category, b.file ,s.statename,c.views');
        $this->db->from('college_course cc');
        $this->db->join('college c', 'c.id=cc.collegeid', 'left');
        $this->db->join('courses cs', 'cs.id=cc.courseid', 'left');
        $this->db->join('college_ranks cr', 'cr.college_id=cc.collegeid', 'left');
        $this->db->join('city ci', 'ci.id = c.cityid', 'left');
         $this->db->join('state s','s.id = ci.stateid','left');
        $this->db->join('rank_categories rc', 'rc.category_id=cr.category_id', 'left');
        $this->db->join('brochures b', 'b.collegeid = cc.collegeid', 'left');
        
        // $this->db->join('counseling_fees cf', 'cf.sub_category = cc.categoryid', 'left');
        
     //   $this->db->join('sub_category sc', 'sc.parent_category = c.categoryid','left'); comment by navin
        
        $this->db->where("FIND_IN_SET('$categoryid',c.categoryid) > 0");
                $this->db->where('cc.categoryid', $courseId); //comment by navin

        $this->db->where('c.title IS NOT NULL');
    //   $this->db->where_in('cf.college_type',$getCollegeType);
       // $this->db->where('b.file IS NOT NULL');
        //$this->db->where('cr.rank IS NOT NULL');
        //$this->db->where('cr.rank !=' , '');
      //$this->db->order_by("CASE WHEN rc.title = 'NIRF' THEN 0 ELSE 1 END", 'ASC');
//$this->db->order_by("CAST(cr.rank AS UNSIGNED)", 'ASC');
         $this->db->order_by('c.views', 'DESC');

        

        $this->db->group_by('cc.collegeid');
        // $this->db->group_by('cr.rank');
        $this->db->limit(20);
        $query = $this->db->get();
      ///echo $this->db->last_query();exit;
        $result = $query->result_array();
       
        return $result;
    }

    public function getCollegeType($courseId){
        $this->db->select('college_type');
        $this->db->from('counseling_fees');
        $this->db->where('sub_category',$courseId);

        $query = $this->db->get();
       //  echo $this->db->last_query();exit;
          $result = $query->result_array();
         
          return $result;
    }

    /**
     * Get college specification.
     *
     * @return array The list of college specification.
     */
    public function getListBySpecification($ccID, $acID,$subID)
    {
		//echo "testing...";exit;
        $this->db->select('id , name, academic_category ,course_category, sub_category');
        $this->db->from('courses');
        $this->db->where('course_category', $ccID);
        $this->db->where('academic_category', $acID);
		$this->db->where('sub_category', $subID);
        $this->db->where('status ', '1'); 
		$this->db->limit(20);//SELECT * FROM `courses` where course_category =91 and academic_category =2
        $query = $this->db->get()->result_array();
        //echo $this->db->last_query();exit;
        return $query;
    }
	
	public function getCollege_Count($courseId)
	{
    $this->db->select('COUNT(*) as count');
    $this->db->from('college_course');
    $this->db->where('courseid', $courseId);
    $query = $this->db->get();
    $result = $query->row()->count;
    return $result;
   }

    /**
     * Get all city list.
     *
     * @return array The list of city.
     */
    public function getCityByCourse($course)
    { 
        /*$this->db->select('*');
        $this->db->from('city');
        $this->db->order_by('view_in_menu', 'DESC');
        $this->db->LIMIT(10);

        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }*/
        
        $this->db->select('CI.*, COUNT(DISTINCT c.id) AS collegeCount');
        $this->db->from('college_course cc');
        $this->db->join('college c', 'c.id = cc.collegeid', 'left');
        $this->db->join('city CI', 'CI.id = c.cityid', 'left');
        $this->db->where('cc.courseid', $course);
        $this->db->where('CI.city IS NOT NULL', null, false); // Raw SQL to check for not NULL city
        $this->db->group_by('CI.city');
        $query = $this->db->get();
        
        // To get the result as an array of objects
        $result = $query->result();
        return $result;

    }

    public function getCity($text)
    {
       // echo "ttt";exit;
        $this->db->select('*');
        $this->db->from('city');
        $this->db->where('city !=', 'Bagalkot'); 
        $this->db->where('city !=', 'Bidar'); 
        $this->db->like('city', $text);
        $query = $this->db->get()->result_array();
        //echo $this->db->last_query();exit;
        return $query;
    }
    /**
     * Get count of colleges by city id  and course id.
     * 
     * @param int $cityId  The city id.
     * @param int $CourseId  The course id.
     * @return int The count of colleges by city id  and course id.
     */
    public function getCollegeCount($cityId, $CourseId)
    {
        $this->db->select('*');
        $this->db->from('college c');
        $this->db->like('c.categoryid', $CourseId);
        $this->db->where('c.cityid', $cityId);
        $query = $this->db->get();
        //echo $this->db->last_query();exit;

        return $query->num_rows();;
    }

    /**
     * Get course details by course id.
     * 
     * @param int $course  The course id.
     * @return array The course details by course id.
     */
    public function getcoursesId($course)
    {
        $this->db->select("cs.id, cs.name, cs.parent_category");
        $this->db->from('sub_category cs');
        $this->db->like('cs.name', $course);
        $this->db->group_by('cs.name');
        $query = $this->db->get()->result_array();
        //echo $this->db->last_query();exit;
        return $query;
    }

    public function getSubCatByCoursesId($course)
    {
       // echo "ttt";exit;
        $this->db->select("cs.id, cs.name, cs.parent_category");
        $this->db->from('sub_category cs');
        $this->db->like('cs.id', $course);
        //$this->db->group_by('cs.name');
        $query = $this->db->get()->row();
        //echo $this->db->last_query();exit;
        if ($query) {
           // return $query->name;
              return $query;
        } else {
            return null;
        }
    }

    public function getcourseParentId($course)
    {
        $this->db->select("cs.name, cs.parent_category");
        $this->db->from('sub_category cs');
        $this->db->where('cs.id', $course);
        $this->db->group_by('cs.name');
        //$query = $this->db->get()->row();
        //echo $this->db->last_query();exit;
        /*if ($query) {
            return $query->parent_category;
        } else {
            return null;
        }*/
		$query = $this->db->get();
        $result = $query->result_array();
        //echo $this->db->last_query();exit;
        return $result;
    }
	
	public function getcourseParentId__($course)
    {
        $this->db->select("cs.name, cs.parent_category");
        $this->db->from('sub_category cs');
        $this->db->where('cs.id', $course);
        $this->db->group_by('cs.name');
        $query = $this->db->get()->row();
        //echo $this->db->last_query();exit;
        if ($query) {
            return $query->parent_category;
        } else {
            return null;
        }
    }

    /**
     * Get the exam list.
     * 
     * @return array The list of exam.
     */
    /*public function get_ExamList()
    {
        $this->db->select('id,title,questionpaper,preparation,syllabus,categoryid,status,views,description,criteria,notification');
        $this->db->from('exams');
        $this->db->where('questionpaper IS NOT NULL');
        $this->db->where('preparation IS NOT NULL');
        $this->db->where('syllabus IS NOT NULL');
        $this->db->where('status', '1');
        $query = $this->db->get();
        //echo $this->db->last_query();      exit;
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }*/
    
    public function get_ExamList()
{
    //echo "tt";exit;
    $this->db->select('e.id, e.title, e.questionpaper, e.preparation, e.syllabus, e.categoryid, e.status, e.views, e.description, e.criteria, e.notification');
    $this->db->from('exams e');
    $this->db->where('e.questionpaper IS NOT NULL');
    $this->db->where('e.preparation IS NOT NULL');
    $this->db->where('e.syllabus IS NOT NULL');
    $this->db->where('e.status', '1');

   // $this->db->where('e.categoryid', $categoryId);

    $query = $this->db->get();

    //echo $this->db->last_query(); exit;

    if ($query->num_rows() > 0) {
        return $query->result();
    } else {
        return false;
    }
}

    /**
     * Get list of courses.
     * 
     * @return array The list of course.
     */
    public function get_Course()
    {
        $this->db->select('*');
        $this->db->from('courses');
        $this->db->where('course_category', 91);
        $query = $this->db->get();
        //echo $this->db->last_query();      exit;
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }

    /**
     * Get the college list by using search text.
     * 
     * @param int $text  The search text.
     * @param int $CourseId  The course id.
     * @return array The college list by using search text.
     */
    public function getClgBySearch($text)
    {
        $this->db->select('c.id, c.title');
        $this->db->from('college c');
        $this->db->join('city ci', 'ci.id = c.cityid', 'left');
        $this->db->join('country co', 'co.id = c.countryid', 'left');
        $this->db->join('category ca', 'ca.id = c.categoryid', 'left');
        $this->db->join('college_type ct', 'ct.id = c.college_typeid', 'left');
        $this->db->join('gallery g', 'g.postid = c.id', 'left');
        //$this->db->join('brochures b', 'b.collegeid = cc.collegeid', 'left');
        $this->db->where('c.is_deleted', '0');
        $this->db->where('c.status', '1');
        $this->db->where('g.type', 'college');
        $this->db->where('g.image IS NOT NULL');
        $this->db->where('c.logo IS NOT NULL');
        $this->db->where('c.logo !=', '');
        $this->db->where('ci.city', $text);
        $this->db->or_like('c.title', $text);
        $this->db->or_like('c.address', $text);
        $this->db->or_where('c.college_typeid', $text);
        $this->db->group_by('c.id');
        $this->db->LIMIT(20);
        $query = $this->db->get();
        $result = $query->result();
        //echo $this->db->last_query();exit;
        return $result;
    }

    public function getCollegeDetailsByID($id)
    {
        $this->db->select('c.id, c.cityid,c.title,c.what_new, c.description, c.accreditation, c.package_type, logo, c.title, banner, estd, ci.city, co.country, g.image, ca.catname, ct.name');
        $this->db->from('college c');
        $this->db->join('city ci', 'ci.id = c.cityid', 'left');
        $this->db->join('country co', 'co.id = c.countryid', 'left');
        $this->db->join('category ca', 'ca.id = c.categoryid', 'left');
        $this->db->join('college_type ct', 'ct.id = c.college_typeid', 'left');
        $this->db->join('gallery g', 'g.postid = c.id', 'left');
        $this->db->where('c.is_deleted', '0');
        $this->db->where('c.status', '1');
        $this->db->where('g.type', 'college');
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
    public function getCollegeCoursesByID($id)
    {
        $this->db->select('cc.id, cc.courseid, c.name As courseName, cc.total_fees, cc.duration,cc.level,cc.website,cc.description,cc.eligibility,cc.entrance_exams,cc.placement,cc.brochure,cc.fees');
        $this->db->from('college_course cc');
        $this->db->join('courses c', 'c.id = cc.courseid', 'left');
        $this->db->where("cc.collegeid", $id);
        $this->db->where("cc.is_deleted", '0');
        $query = $this->db->get();
        $result = $query->result_array();
        //echo $this->db->last_query();exit;
        return $result;
    }
    public function getTableOfContent($id)
    {
        $this->db->select('*');
        $this->db->from('table_of_content');
        $this->db->where('status', '1');
        $this->db->where('college_id', $id);

        $result = $this->db->get()->result_array();
        return $result;
    }
    public function getCollegeImagesByID($id)
    {
        $this->db->select('*');
        $this->db->from('gallery');
        $this->db->where('postid', $id);
        $this->db->where('type', 'college');
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
    public function get()
    {
        $this->db->select('id, title, cityid, categoryid');
        $this->db->from('college');
        $this->db->limit(5);
        $query = $this->db->get();
        $result = $query->result_array();
        return $result;
    }
    public function getCategoryId($value)
    {
        $this->db->select('id, catname, type');
        $this->db->from('category');
        $this->db->where('id', $value);
        $query = $this->db->get();
        $result = $query->result_array();
        return $result;
    }

    public function getCareerCatId($catName)
    {
        $this->db->select('*');
        $this->db->from('category');
        $this->db->where('catname', $catName);
        $this->db->where('type', 'careers');
        $query = $this->db->get()->row();
        //echo $this->db->last_query();exit;
        if ($query) {
            return $query->id;
        } else {
            return null;
        }
    }

    public function getCareerByCategory($careerId)
    {
        $this->db->select('c.id, c.title, c.description, c.categoryid, g.image');
        $this->db->from('careers c');
		$this->db->join('gallery g', 'g.postid = c.id', 'left');
		$this->db->where('g.type', 'careers');
        $this->db->where('categoryid', $careerId);
        $this->db->where('status', '1');
        $query = $this->db->get();
        $result = $query->result_array();
        return $result;
    }

    public function getExamCatId($catName)
    {
        $this->db->select('*');
        $this->db->from('category');
        $this->db->where('catname', $catName);
        $this->db->where('type', 'exams');
        $query = $this->db->get()->row();
        if ($query) {
            return $query->id;
        } else {
            return null;
        }
    }
	
	public function getExamsByCategoryForNav($examId)
    {
        $this->db->select('id, title');
        $this->db->from('exams');
        $this->db->where('categoryid', $examId);
        $this->db->where('status', '1');
		$this->db->where('view_in_menu', '1');
        $this->db->limit(5);
        $query = $this->db->get();
        $result = $query->result_array();
        return $result;
    }

    public function getCategory()
    {
        //echo "ttt";exit;
        $this->db->select('id,catname,type');
        $this->db->from('category');
        $this->db->where('type', 'college');
       // $this->db->where('topmenu', 1);
        $this->db->where('status', 1);
        $query = $this->db->get();
        // echo $this->db->last_query();exit;
        $result = $query->result();
        return $result;
    }
    public function getAcadamicCategory()
    {
        $this->db->select('*');
        $this->db->from('academic_categories');
        $this->db->where('status', 1);
        $this->db->order_by('display_order', 'asc');
        $this->db->group_by('name');
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }

    public function getCourseCount($id)
    {
        $this->db->where("course_category", $id);
        $query = $this->db->get('courses');
        return $query->num_rows();
    }

    public function getCourses($id)
    {
        $this->db->select('*');
        $this->db->from('courses');
        $this->db->where("course_category", $id);
        $this->db->limit(10);
        $this->db->order_by('id', 'desc');
        $query = $this->db->get();
        $result = $query->result();
        //echo $this->db->last_query();exit;
        return $result;
    }

    public function getExams()
    {
        $this->db->select('id, title, description');
        $this->db->from('exams');
        $this->db->limit(10);
        $query = $this->db->get();
        $result = $query->result();
        //echo $this->db->last_query();exit;
        return $result;
    }

    public function getPlacementCategory()
    {
        $this->db->select('*');
        $this->db->from('placement_category');
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

    public function getBlogs()
    {
        $this->db->select('*');
        $this->db->from('blog');
        $this->db->where('t_status', '1');
        $this->db->order_by('id');
        $this->db->limit(10);
        $query = $this->db->get()->result_array();
        return $query;
    }

    public function getLatestBlogs()
    {
        $this->db->select('*');
        $this->db->from('blog');
        $this->db->where('t_status', '1');
        $this->db->order_by('created_date', 'DESC');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(10);
        $query = $this->db->get();
        $result = $query->result_array();
        return $result;
    }


    public function getPopularBlogs()
    {
        $this->db->select('*');
        $this->db->from('blog');
        $this->db->where('t_status', '1');
        $this->db->where('views >', 500);
        $this->db->order_by('views', 'DESC');
        $this->db->order_by('created_date', 'DESC');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(10);
        $query = $this->db->get();
        $result = $query->result_array();
        return $result;
    }
	
	public function getEventList()
	{
		$this->db->select('e.event_id, e.event_name, e.event_desc, e.event_website, e.event_address,g.image, e.event_start_date, e.event_end_date');
		$this->db->from('events e');
		$this->db->join('gallery g', 'g.postid = e.event_id', 'left');
		$this->db->where('e.event_status', '1');
		$this->db->where('g.type', 'events');
		$this->db->group_by('e.event_id');
		$this->db->order_by('e.event_id', 'DESC');
		$this->db->limit(5);
		$query = $this->db->get();
        $result = $query->result_array();
        return $result;
	}
	
	 public function getCategoryForMenu()
    {
        $sql = "SELECT id, catname, menuorder, type 
                FROM category 
                WHERE status = 1 AND type = 'college' 
                ORDER BY 
                    CASE 
                        WHEN type = 'college' THEN 0 
                        ELSE 1 
                    END,
                    CASE 
                        WHEN menuorder = 0 THEN 9999 
                        ELSE menuorder 
                    END;
                ";

        $query = $this->db->query($sql);
        $result = $query->result(); // Get the result before returning
        return $result; // Return the result
    }
	
	public function getCollegeListForRateByCourse($CourseId)
    {
        $this->db->select('c.id as collegeid, c.accreditation, c.title, c.logo, ci.city, c.address, cc.total_fees, cr.rank, cr.year, rc.title as category, b.file');
        $this->db->from('college_course cc');
        $this->db->join('college c', 'c.id=cc.collegeid', 'left');
        $this->db->join('courses cs', 'cs.id=cc.courseid', 'left');
        $this->db->join('college_ranks cr', 'cr.college_id=cc.collegeid', 'left');
        $this->db->join('city ci', 'ci.id = c.cityid', 'left');
        $this->db->join('rank_categories rc', 'rc.category_id=cr.category_id', 'left');
        $this->db->join('brochures b', 'b.collegeid = cc.collegeid', 'left');
        $this->db->like('c.categoryid', $CourseId);
        $this->db->where('c.title IS NOT NULL');
        $this->db->where('b.file IS NOT NULL');
        $this->db->where('cr.rank IS NOT NULL');
        //$this->db->where('cc.total_fees IS NOT' ,'');
        $this->db->group_by('c.id');
        //$this->db->order_by('cr.rank', 'ASC');
        //$this->db->limit(10);
        $query = $this->db->get()->result_array();
        //echo $this->db->last_query();
        return $query;
    }
	
	//------

	    public function getCatNameMenu($cat)
    {
        $this->db->select('catname');
        $this->db->from('category');
        $this->db->where('id', $cat);
        $query = $this->db->get()->row();
        if ($query) {
            return $query->catname;
        } else {
            return null;
        }
    }
	
  public function getExamCatIdMenu($courseCatId)
    {
        $this->db->select('*');
        $this->db->from('category');
        $this->db->where('id', $courseCatId);
        // $this->db->where('type', 'exams');
        $query = $this->db->get()->row();
        if ($query) {
            return $query->id;
        } else {
            return null;
        }
    }
    
	
  public function getSubCatByCoursesIdMenu($course)
    {
        $this->db->select("cs.id, cs.name, cs.parent_category");
        $this->db->from('sub_category cs');
        $this->db->like('cs.id', $course);
        //$this->db->group_by('cs.name');
        $query = $this->db->get()->row();
        //echo $this->db->last_query();exit;
        if ($query) {
            return $query->name;
        } else {
            return null;
        }
    }
	
  public function getExamImage($examId)
    {
	//   print_r($examId);exit;
        $this->db->select("image");
        $this->db->from('gallery');
        $this->db->where('postid', $examId);
        //$this->db->group_by('cs.name');
        $query = $this->db->get()->row();
        //echo $this->db->last_query();exit;
      $result = $query->result;
        return $result;
    }
	
	 public function getByCity($course,$statename,$start,$end)
    {
         if (!is_array($statename)) {
        $statename = [$statename]; 
    }
         $this->db->select('CI.*, COUNT(DISTINCT c.id) AS collegeCount');
        $this->db->from('college_course cc');
        $this->db->join('college c', 'c.id = cc.collegeid', 'left');
        $this->db->join('city CI', 'CI.id = c.cityid', 'left');
        $this->db->join('state s','s.id = c.stateid');
        $this->db->where('cc.categoryid', $course);
        $this->db->where_in('s.statename', $statename);
         $this->db->where('c.is_deleted', '0');
        $this->db->where('c.status', '1');
        $this->db->where('CI.city !=', 'Bagalkot'); 
        $this->db->where('CI.view_in_menu', 1); 
        $this->db->where('CI.city IS NOT NULL', null, false); 
        // Raw SQL to check for not NULL city
        $this->db->group_by('CI.city');
        $this->db->limit($end,$start);
        $query = $this->db->get();
        
          //echo $this->db->last_query();exit;
        // To get the result as an array of objects
        $result = $query->result();
        return $result;
    }
	
   public function getPopCollegesNew($courseId, $categoryid)
    {
    // First Query: Retrieve college details based on the main category
    $this->db->select('c.id as collegeid, cr.rank,cc.entrance_exams, c.accreditation, rc.title as rank_title, c.title, c.logo, c.categoryid, ci.city, c.address, cc.total_fees, cc.eligibility,cs.sub_category, cr.year, rc.title as category, b.file, s.statename, c.views,COUNT(cs.id) as courses_count');
    $this->db->from('college_course cc');
    $this->db->join('college c', 'c.id = cc.collegeid', 'left');
    $this->db->join('courses cs', 'cs.id = cc.courseid', 'left');
    $this->db->join('college_ranks cr', 'cr.college_id = cc.collegeid', 'left');
    $this->db->join('city ci', 'ci.id = c.cityid', 'left');
    $this->db->join('state s', 's.id = ci.stateid', 'left');
    $this->db->join('rank_categories rc', 'rc.category_id = cr.category_id', 'left');
    $this->db->join('brochures b', 'b.collegeid = cc.collegeid', 'left');
    //$this->db->join('sub_category sc', 'cc.categoryid = sc.id', 'left');
    $this->db->where('cs.sub_category', $courseId);
    $this->db->where('cs.course_category', $categoryid);

    $this->db->where('c.title IS NOT NULL');
    $this->db->order_by('c.views', 'DESC');
    $this->db->group_by('cc.collegeid');
    $this->db->limit(20);

    $query = $this->db->get();
    //echo $this->db->last_query();exit;
    $data = $query->result_array();
    //print_r($data);exit;
    foreach ($data as &$val) {
        if(empty($val['total_fees']) || $val['total_fees'] == NULL){
        $collegeid = $val['collegeid'];
        $categoryid = $val['categoryid'];
        $sub_category = $val['sub_category'];

        $this->db->select('c.id, c.categoryid, c.college_typeid, s.id as subcategoryid, cc.entrance_exams as selectedexam');
        $this->db->from('college c');
        $this->db->join('college_course cc', 'c.id = cc.collegeid', 'left');
        $this->db->join('sub_category s', 'c.categoryid = s.parent_category', 'left');
        $this->db->where('c.id', $collegeid);
        $this->db->where_in('c.categoryid' , $categoryid);
        $this->db->where('s.id ', $sub_category);
        
        $subcategoryQuery = $this->db->get();
       //echo $this->db->last_query();exit;
        $subcategoryData = $subcategoryQuery->row();
       //print_r($subcategoryData);
        if ($subcategoryData) {
            $subcategoryid = $subcategoryData->subcategoryid;
            $collegeTypeId = $subcategoryData->college_typeid;
            $categoryid = $subcategoryData->categoryid;
            $entrance_exams = $subcategoryData->selectedexam;
            if (!is_array($entrance_exams) && $entrance_exams != '' || $entrance_exams != NULL) {
            $entrance_exams = explode(',', $entrance_exams);
            }
            //print_r($entrance_exams);exit;
            $this->db->select('cf.fees as total_fees');
            $this->db->from('counseling_fees cf');
            $this->db->where_IN('cf.category', $categoryid);
            $this->db->where('cf.sub_category', $subcategoryid);
            $this->db->where_in('cf.college_type', $collegeTypeId);
            $this->db->where_in('cf.exam_id', $entrance_exams);

            $querys = $this->db->get();
            //echo $this->db->last_query();exit;
            $datas = $querys->row();
            //print_r($datas);
           // $fees = $datas->total_fees;
            if(empty($datas) || $datas==NULL){$val['total_fees'] = 'N/A';}
            else{$fees = $datas->total_fees;$val['total_fees'] = $fees;}
       }
        }
        else
        {
            $val['total_fees'] = 'N/A';
        }
    }
    unset($val);
    return $data;
    }
	
public function getPopCollegesOld($collegeid)
{
    
        $this->db->select('c.categoryid, c.college_typeid, s.id as subcategoryid');
        $this->db->from('college c');
        $this->db->where('c.id', $collegeid);
        $this->db->join('sub_category s', 'c.categoryid = s.parent_category', 'left');
        $subcategoryQuery = $this->db->get();
        $subcategoryData = $subcategoryQuery->row();

        if ($subcategoryData) {
            $subcategoryid = $subcategoryData->subcategoryid;
            $collegeTypeId = $subcategoryData->college_typeid;
            $categoryid = $subcategoryData->categoryid;

            // Second Query using subcategory and counseling fees
            $this->db->select('c.id as collegeid, cr.rank, c.accreditation, rc.title as rank_title, c.title, c.logo, c.categoryid, ci.city, c.address, cf.fees as total_fees, cc.eligibility, cr.year, rc.title as category, b.file, s.statename, c.views');
            $this->db->from('college_course cc');
            $this->db->join('college c', 'c.id = cc.collegeid', 'left');
            $this->db->join('courses cs', 'cs.id = cc.courseid', 'left');
            $this->db->join('college_ranks cr', 'cr.college_id = cc.collegeid', 'left');
            $this->db->join('city ci', 'ci.id = c.cityid', 'left');
            $this->db->join('state s', 's.id = ci.stateid', 'left');
            $this->db->join('rank_categories rc', 'rc.category_id = cr.category_id', 'left');
            $this->db->join('brochures b', 'b.collegeid = cc.collegeid', 'left');
            $this->db->join('counseling_fees cf', 'cf.sub_category = cc.categoryid', 'left');

            $this->db->where_IN('cf.category', $categoryid);
            $this->db->where('cf.sub_category', $subcategoryid);
            $this->db->where_in('cf.college_type', $collegeTypeId);
            $this->db->where('c.title IS NOT NULL');
            $this->db->order_by('c.views', 'DESC');
            $this->db->group_by('cc.collegeid');
            //$this->db->limit(20);

            $query = $this->db->get();
        }
   
    return $query->result_array();
}
}
