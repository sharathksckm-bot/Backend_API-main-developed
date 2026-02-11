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
    public function getClgListByLoc($loc, $course)
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
        $this->db->where("cc.total_fees !=", '');
        $this->db->limit(10);
        $query = $this->db->get()->result_array();
        //echo $this->db->last_query();
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
        $this->db->limit($limit);
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
    public function getClgbyFees($min_fees, $max_fees)
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
    public function getCollegeListByCourse($CourseId)
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
        $this->db->limit(10);
        $query = $this->db->get()->result_array();
        //echo $this->db->last_query();
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
        $this->db->join('courses cs', 'cs.id=cc.courseid', 'left');
        $this->db->join('college c', 'c.id=cc.collegeid', 'left');
        $this->db->like('c.categoryid', $CourseId);
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
    public function getCollegesListByRank($id)
    {
        $this->db->select('c.id as collegeid, cr.rank, c.accreditation, c.title, c.logo, c.categoryid, ci.city, c.address,
        cc.total_fees, cc.eligibility, cr.year, rc.title as category, b.file ');
        $this->db->from('college_course cc');
        $this->db->join('college c', 'c.id=cc.collegeid', 'left');
        $this->db->join('courses cs', 'cs.id=cc.courseid', 'left');
        $this->db->join('college_ranks cr', 'cr.college_id=cc.collegeid', 'left');
        $this->db->join('city ci', 'ci.id = c.cityid', 'left');
        $this->db->join('rank_categories rc', 'rc.category_id=cr.category_id', 'left');
        $this->db->join('brochures b', 'b.collegeid = cc.collegeid', 'left');
        $this->db->like('c.categoryid ', $id);
        $this->db->where('c.title IS NOT NULL');
        $this->db->where('b.file IS NOT NULL');
        $this->db->where('cr.rank IS NOT NULL');
        $this->db->order_by('cr.rank', 'ASC');
        $this->db->group_by('cr.rank');
        $this->db->limit(20);
        $query = $this->db->get();
        $result = $query->result();
        //echo $this->db->last_query();exit;
        return $result;
    }

    /**
     * Get all popular engineering colleges.
     *
     * @return array The list of popular engineeringcolleges.
     */
    public function getPopColleges($id)
    {
        $this->db->select('c.id as collegeid, cr.rank, c.accreditation, c.title, c.logo, c.categoryid, ci.city, c.address,
        cc.total_fees, cc.eligibility, cr.year, rc.title as category, b.file ');
        $this->db->from('college_course cc');
        $this->db->join('college c', 'c.id=cc.collegeid', 'left');
        $this->db->join('courses cs', 'cs.id=cc.courseid', 'left');
        $this->db->join('college_ranks cr', 'cr.college_id=cc.collegeid', 'left');
        $this->db->join('city ci', 'ci.id = c.cityid', 'left');
        $this->db->join('rank_categories rc', 'rc.category_id=cr.category_id', 'left');
        $this->db->join('brochures b', 'b.collegeid = cc.collegeid', 'left');
        $this->db->like('c.categoryid ', $id);
        $this->db->where('c.title IS NOT NULL');
        $this->db->where('b.file IS NOT NULL');
        $this->db->group_by('c.id');
        $this->db->limit(20);
        $query = $this->db->get();
        $result = $query->result_array();
        //echo $this->db->last_query();exit;
        return $result;
    }

    /**
     * Get college specification.
     *
     * @return array The list of college specification.
     */
    public function getListBySpecification($ccID, $acID)
    {
        $this->db->select('id , name, academic_category ,course_category, sub_category');
        $this->db->from('courses');
        $this->db->where('course_category', $ccID);
        $this->db->where('academic_category', $acID);
        $this->db->where('status ', '1'); //SELECT * FROM `courses` where course_category =91 and academic_category =2
        $query = $this->db->get()->result_array();
        //echo $this->db->last_query();exit;
        return $query;
    }

    /**
     * Get all city list.
     *
     * @return array The list of city.
     */
    public function get_City()
    {
        $this->db->select('*');
        $this->db->from('city');
        $this->db->order_by('view_in_menu', 'DESC');
        $this->db->LIMIT(10);

        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }

    public function getCity($text)
    {
        $this->db->select('*');
        $this->db->from('city');
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

    public function getcourseParentId($course)
    {
        $this->db->select("cs.parent_category");
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
    public function get_ExamList()
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
        $this->db->select('c.id, c.title, c.address, c.description,  ct.name AS college_type, c.accreditation, c.phone, c.package_type, c.logo, c.banner, c.estd, ci.city, co.country, g.image, ca.catname, (CASE WHEN c.package_type = "featured_listing" THEN 0 ELSE 1 END) AS sort_order');
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
        $this->db->LIMIT(25);
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
        $this->db->select('id, title, description, categoryid');
        $this->db->from('careers');
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
        $this->db->select('id,catname,type');
        $this->db->from('category');
        $this->db->where('type', 'college');
        $this->db->where('topmenu', 1);
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
}
