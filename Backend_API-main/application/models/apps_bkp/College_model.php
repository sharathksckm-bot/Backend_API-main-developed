<?php

class College_model extends CI_Model
{
    private $table = 'college';

    public function __construct()
    {

        parent::__construct(); {
            $this->load->database();
        }
    }

    public function getCollegeType($search_term = null)
    {
        $this->db->select('*');
        $this->db->from('college_type');

        if ($search_term) {
            $this->db->like('name', $search_term);
        }

        $query = $this->db->get()->result_array();

        return $query;
    }


    /**
     * Get the count of all colleges.
     *
     * @return int The count of colleges.
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

    public function getTableOfContent($id)
    {
        $this->db->select('tc.id,tc.college_id, tc.type, tc.title as titleId, cc.name as title');
        $this->db->from('table_of_content tc');
        $this->db->join('content_category cc', 'cc.id = tc.title', 'left');
        $this->db->where('tc.type', 'college');
        $this->db->where('tc.status', '1');
        $this->db->where('tc.college_id', $id);
        $result = $this->db->get()->result_array();
        return $result;
    }
    /**
     * Count filtered colleges based on the search term.
     *
     * @param string $search The search term.
     * @return int The number of filtered colleges.
     */
    public function countFilteredClg($search, $loc, $ownerShip, $rankCategory)
    {
        $this->db->select('*');
        $this->db->from($this->table . ' c');
        $this->db->where('c.is_deleted', '0');
        $this->db->where('c.status', '1');

        if (!empty($search)) {
            $this->db->like('c.title', $search);
        }
        if (!empty($loc)) {
            $this->db->where('c.cityid', $loc);
        }
        if (!empty($ownerShip)) {
            $this->db->where('c.college_typeid', $ownerShip);
        }
        if (!empty($rankCategory)) {
            $this->db->join('college_ranks cr', 'c.id = cr.college_id', 'left');
            $this->db->where('cr.category_id', $rankCategory);
        }

        return $this->db->get()->num_rows();
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

    public function getFilteredClg($clgname, $start, $limit, $order, $dir, $loc, $ownerShip, $rankCategory)
    {
        $this->db->select('c.id, c.package_type, c.logo, c.title, c.banner, c.estd, ci.city, g.image, (CASE WHEN c.package_type = "featured_listing" THEN 0 ELSE 1 END) AS sort_order');
        $this->db->from('college c');
        $this->db->join('city ci', 'ci.id = c.cityid', 'left');
        $this->db->join('gallery g', 'g.postid = c.id', 'left');
        $this->db->where('c.is_deleted', '0');
        $this->db->where('c.status', '1');
        $this->db->where('g.type', 'college');
        if (!empty($loc)) {
            $this->db->where('c.cityid', $loc);
        }
        if (!empty($clgname)) {
            $this->db->like('c.title', $clgname);
        }
        if (!empty($ownerShip)) {
            $this->db->where('college_typeid', $ownerShip);
        }
        if (!empty($rankCategory)) {
            $this->db->join('college_ranks cr', 'c.id = cr.college_id', 'left');
            $this->db->where('cr.category_id', $rankCategory);
        }
        $this->db->group_by('c.id');

        $this->db->order_by($order, $dir);
        $this->db->limit($limit, $start);

        return $this->db->get()->result();
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
        $this->db->select('c.id, c.package_type, logo, c.title, banner, estd, ci.city, g.image, (CASE WHEN c.package_type = "featured_listing" THEN 0 ELSE 1 END) AS sort_order');
        $this->db->from('college c');
        $this->db->join('city ci', 'ci.id = c.cityid', 'left');
        $this->db->join('gallery g', 'g.postid = c.id', 'left');
        $this->db->where('c.is_deleted', '0');
        $this->db->where('c.status', '1');
        $this->db->where('g.type', 'college');
        $this->db->group_by('c.id');
        $this->db->order_by('sort_order', 'asc');
        $this->db->limit($limit, $start);
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


    public function getFeaturedColleges()
    {
        $this->db->select('c.id, c.title, c.slug, c.description,c.accreditation, c.address, c.web, c.estd, g.image,ct.city,st.statename,c.notification,c.notification_link');
        $this->db->from('college c');
        $this->db->join('gallery g', 'g.postid = c.id', 'left');
		$this->db->join('city ct', 'ct.id = c.cityid', 'left');
		$this->db->join('state st', 'st.id = c.stateid', 'left');
        $this->db->where('c.package_type', 'featured_listing');
        $this->db->where('c.status', '1');
        $this->db->where('c.is_deleted', '0');
        $this->db->where('g.type', 'college');
        $this->db->group_by('c.id');
        $this->db->order_by('RAND()');
        $this->db->order_by('c.id', 'DESC');
        $this->db->limit(8);
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }

    public function getTotalCourses($id)
    {
        $this->db->select('COUNT(courseid) as totalCourses');
        $this->db->where('collegeid', $id);
        $query = $this->db->get('college_course');
        $result = $query->row()->totalCourses;
        return $result;
    }

    public function getTrendingColleges()
    {
        $this->db->select('c.id, c.title, c.tag');
        $this->db->from('college c');
        $this->db->join('college_ranks cr', 'cr.college_id = c.id', 'left');
        $this->db->where("c.package_type", "featured_listing");
        $this->db->or_where("cr.rank", 1);
        $this->db->group_by("c.id");
        $this->db->order_by('RAND()');
        $this->db->limit(30);

        $query = $this->db->get();
        $result = $query->result_array();
        //echo $this->db->last_query();exit;
        return $result;
    }

    public function getCollegeDetailsByID($id)
    {
        $this->db->select('c.id, c.cityid, c.title, c.what_new, c.categoryid, c.description, c.accreditation, c.package_type, c.logo, GROUP_CONCAT(ca.id) as catID, GROUP_CONCAT(ca.catname) AS catname, c.banner, c.estd, ci.city, co.country, g.image, ct.name');
        $this->db->from('college c');
        $this->db->join('city ci', 'ci.id = c.cityid', 'left');
        $this->db->join('country co', 'co.id = c.countryid', 'left');
        $this->db->join('category ca', 'FIND_IN_SET(ca.id, c.categoryid)', 'left');
        $this->db->join('college_type ct', 'ct.id = c.college_typeid', 'left');
        $this->db->join('gallery g', 'g.postid = c.id', 'left');
        $this->db->where('c.is_deleted', '0');
        $this->db->where('c.status', '1');
        $this->db->where('g.type', 'college');
        $this->db->where('c.id', $id);
        $this->db->group_by('c.id, c.cityid, c.title, c.what_new, c.categoryid, c.description, c.accreditation, c.package_type, c.logo, c.banner, c.estd, ci.city, co.country, g.image, ct.name');
        $this->db->limit(1, 1);

        $query = $this->db->get();
        $result = $query->result_array();
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

    public function getCollegesByCourse_level($id, $level)
    {
        $this->db->select('c.id, c.title, c.address, c.phone, c.email, ci.city, c.web, c.logo, g.image, b.file, cc.total_fees, c.accreditation');
        $this->db->from('college_course cc');
        $this->db->join('college c', 'c.id = cc.collegeid', 'left');
        $this->db->join('gallery g', 'g.postid = c.id', 'left');
        $this->db->join('brochures b', 'b.collegeid = cc.collegeid', 'left');
        $this->db->join('city ci', 'ci.id = c.cityid', 'left');
        $this->db->where("cc.courseid", $id);
        $this->db->where("cc.level", $level);
        $this->db->where("c.title !=", '');
        $this->db->where("b.file !=", '');
        $this->db->group_by("c.id");
        $this->db->order_by('RAND()');
        $this->db->limit(10);
        $query = $this->db->get();
        $result = $query->result_array();
        //echo $this->db->last_query();exit;
        return $result;
    }

    public function getCoursesCountByCollegeID($id)
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
    public function getCollegeListByCourseId($courseId)
    {
        $this->db->select('c.id as collegeid, c.accreditation, c.title, c.logo, c.address, cs.name, cc.total_fees, cc.eligibility, cr.rank, cr.year, rc.title as category, b.file');
        $this->db->from('college_course cc');
        $this->db->join('college c', 'c.id=cc.collegeid', 'left');
        $this->db->join('courses cs', 'cs.id=cc.courseid', 'left');
        $this->db->join('college_ranks cr', 'cr.college_id=cc.collegeid', 'left');
        $this->db->join('rank_categories rc', 'rc.category_id=cr.category_id', 'left');
        $this->db->join('brochures b', 'b.collegeid = cc.collegeid', 'left');
        $this->db->where('cc.courseid', $courseId);
        $this->db->where('c.title IS NOT NULL');
        $query = $this->db->get();
        $result = $query->result();
		//echo $this->db->last_query();exit;
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
    public function getCollegeProgrammesByID($id)
    {
        // $this->db->select('COUNT(c.sub_category) AS total_courses');
        // $this->db->select('cc.courseid');
        // $this->db->select('cc.entrance_exams');
        // $this->db->select('cc.duration');
        // $this->db->select('c.name');
        // $this->db->select('c.course_category');
        // $this->db->select('ct.catname AS course_category_name');
        // $this->db->select('c.sub_category');
        // $this->db->select('sc.name AS sub_category_name');
        // $this->db->select('GROUP_CONCAT(e.title) AS entrance_exam_names');

        // $this->db->from('college_course cc');
        // $this->db->join('courses c', 'c.id = cc.courseid', 'left');
        // $this->db->join('sub_category sc', 'sc.id = c.sub_category', 'left');
        // $this->db->join('category ct', 'ct.id = c.course_category', 'left');
        // $this->db->join('exams e', "FIND_IN_SET(e.id, cc.entrance_exams)", 'left');

        // $this->db->where('cc.collegeid', $id);
        // $this->db->where('c.sub_category IS NOT NULL');

        // $this->db->group_by('c.sub_category,cc.entrance_exams');


        $subquery = $this->db->select('cc.courseid, GROUP_CONCAT(e.title) AS entrance_exam_names')
        ->from('college_course cc')
        ->join('exams e', 'FIND_IN_SET(e.id, cc.entrance_exams)', 'left')
        ->where('cc.collegeid', $id)
        ->group_by('cc.courseid')
        ->get_compiled_select();

        $this->db->select('COUNT(c.sub_category) AS total_courses, 
            cc.courseid, 
            cc.entrance_exams, 
            cc.duration, 
			cc.total_fees,
            c.name, 
            c.course_category, 
            ct.catname AS course_category_name, 
            c.sub_category, 
            sc.name AS sub_category_name, 
            e.entrance_exam_names');
        $this->db->from('college_course cc');
        $this->db->join('courses c', 'c.id = cc.courseid', 'left');
        $this->db->join('sub_category sc', 'sc.id = c.sub_category', 'left');
        $this->db->join('category ct', 'ct.id = c.course_category', 'left');
        $this->db->join("($subquery) e", 'e.courseid = cc.courseid', 'left');
        $this->db->where('cc.collegeid', $id);
        $this->db->where('c.sub_category IS NOT NULL', null, false);
        $this->db->group_by('c.sub_category, cc.entrance_exams');

        $query = $this->db->get();
        //echo $this->db->last_query();exit;
        $result = $query->result_array();





        // $query = $this->db->get();
        //echo $this->db->last_query();exit;
        // $result = $query->result_array();
        return $result;
    }

	public function getPlacementDataOfClg($searchCategory,$searchYear,$collegeId)
	{
		$this->db->select('*,pc.name as categoryName');
		$this->db->from('academic_year ay');
		$this->db->where('collegeid', $collegeId);
		$this->db->join('placement_category pc', 'pc.id = ay.course_category', 'left');
		if (!empty($searchCategory)) {
            $this->db->where('course_category', $searchCategory);
        }
		if (!empty($searchYear)) {
			$this->db->like('year', $searchYear);
        }
		$query = $this->db->get();
		$result = $query->result();

		return $result;

	}

	public function getCommonalyAskedQ($collegeId,$type)
	{
		$this->db->select('cf.faq_ids as faq_id, GROUP_CONCAT(f.heading) AS question');
		$this->db->from('college_faq cf');
		$this->db->join('faq f', 'FIND_IN_SET(f.id, cf.faq_ids) > 0', 'left');
		$this->db->where('cf.collegeid', $collegeId);
		$this->db->where('cf.faq_type', $type);
		$this->db->group_by('cf.faq_ids');
		$query = $this->db->get();
		$result = $query->result_array();

		return $result;
		
	}

	public function getFAQsOfClg($collegeId)
	{
		$this->db->select('cf.faq_ids as faq_id, GROUP_CONCAT(f.heading) AS question');
		$this->db->from('college_faq cf');
		$this->db->join('faq f', 'FIND_IN_SET(f.id, cf.faq_ids) > 0', 'left');
		$this->db->where('cf.collegeid', $collegeId);
		$this->db->where('cf.faq_type', 143);
		$this->db->group_by('cf.faq_ids');
		$this->db->order_by('cf.created_date','desc');

		$query = $this->db->get();
		$result = $query->result_array();

		return $result;
	}

	public function getFaqType($type)
{
    $this->db->select('*');
    $this->db->from('category');
    $this->db->where("UPPER(catname)", "'$type'", FALSE); // Enclose $type in single quotes
    $query = $this->db->get();
    $result = $query->result();

    return $result;
}



	public function getDescriptionForFAQ($faq_id)
	{
		$this->db->select('description as answere');
		$this->db->from('faq');
		$this->db->where('id', $faq_id);
		$query = $this->db->get();
		$result = $query->result_array();

		return $result;
	}

	public function getCoursesAndFeesOfClg($collegeId)
	{
		$this->db->select('c.id, cc.total_fees, cc.eligibility, c.name, c.academic_category, ac.name as academicCategoryName, c.duration, c.course_category, ct.catname as courseCategoryName, c.sub_category, sc.name as subCategoryName');
		$this->db->from('college_course cc');
		$this->db->join('courses c', 'c.id = cc.courseid', 'left');
		$this->db->join('sub_category sc', 'sc.id = c.sub_category', 'left');
		$this->db->join('category ct', 'ct.id = c.course_category', 'left');
		$this->db->join('academic_categories ac', 'ac.category_id = c.academic_category', 'left');
		$this->db->where('cc.collegeid', $collegeId);
		$query = $this->db->get();
		$result = $query->result();
		return $result;
	}

	public function getRanktDataOfClg($collegeId)
	{
		$this->db->select('cr.*, rc.title as categoryName,rc.image');
		$this->db->from('college_ranks cr');
		$this->db->join('rank_categories rc', 'rc.category_id = cr.category_id', 'left');
		$this->db->where('cr.college_id', $collegeId);
		$query = $this->db->get();
		$result = $query->result();
		return $result;

	}

	public function getCollegeContactDetails($collegeId)
	{
		$this->db->select('phone, email, web, address, map_location');
		$this->db->from('college');
		$this->db->where('id', $collegeId);
		$query = $this->db->get();
		$result = $query->result();
		return $result;
	}


	public function getCollegeAdmissionProcess($collegeId)
	{
		$this->db->select('COUNT(cc.courseid) AS courseCount, cc.entrance_exams, GROUP_CONCAT(e.title) AS Accepting_Exams, cc.eligibility, c.sub_category, sc.name as subCatName, c.duration');
		$this->db->from('college_course cc');
		$this->db->join('courses c', 'c.id = cc.courseid', 'left');
		$this->db->join('sub_category sc', 'sc.id = c.sub_category', 'left');
		$this->db->join('exams e', 'FIND_IN_SET(e.id, cc.entrance_exams) > 0', 'left');
		$this->db->where('cc.collegeid', $collegeId);
		$this->db->where('c.sub_category IS NOT NULL');
		$this->db->group_by('sc.id');
		$query = $this->db->get();
        // echo $this->db->last_query();exit;
		$result = $query->result();
		return $result;
	}

	public function getExamsNotification($examsId) {
		$this->db->select('notification');
		$this->db->from('exams');
		$this->db->where('id', $examsId);
		$query = $this->db->get();
		$result = $query->result_array();
		return $result;
	}

	public function collegeByLocation($cityid,$collegeId)
	{
		$this->db->select('*');
		$this->db->from('college');
		$this->db->where('cityid', $cityid);
		$query = $this->db->get();
		$result = $query->result_array();
		return $result;
	}

	public function getScholarShipOfClg($collegeId)
	{
		$this->db->select('scholarship');
		$this->db->from('college');
		$this->db->where('id', $collegeId);
		$query = $this->db->get();
		$result = $query->result_array();
		return $result;
	}

    public function getCollegeListForCompare($search_college = NULL)
	{
		$this->db->select("c.id,c.title");
		$this->db->from($this->table. " c"); 
		$this->db->join('city ci', 'ci.id = c.cityid', 'left');
		$this->db->join('state s', 's.id = c.stateid', 'left');
		if ($search_college !== NULL) {
			$this->db->like('title', $search_college);
		}
		$this->db->limit(10);
		return $this->db->get()->result();

	}

    public function getPopularClgByLocation($cityid)
    {
        $this->db->select('c.id as collegeid, c.title, c.logo, c.address, c.accreditation, g.image,c.cityid, ci.city as cityname, cr.category_id, rc.title as categoryName, cr.rank, cr.year');
        $this->db->from('college c');
		//$this->db->from('college_course cc');
        //$this->db->join('college c', 'c.id=cc.collegeid', 'left');
        $this->db->join('city ci', 'ci.id = c.cityid', 'left');
        $this->db->join('college_ranks cr', 'cr.college_id = c.id', 'left');
        $this->db->join('rank_categories rc', 'rc.category_id = cr.category_id', 'left');
        $this->db->join('gallery g', 'g.postid = c.id', 'left');
        $this->db->where('c.cityid', $cityid);
        $this->db->where('c.package_type', 'featured_listing');
        $this->db->group_by('c.id');
        $this->db->order_by('RAND()');
        $this->db->order_by('c.create_date', 'desc');
        $this->db->limit(10);
        
        $query = $this->db->get();
        return $query->result();
        
    }

    public function getCollegesAccordingCategory($collegeId, $categories)
{
    // Explode categories string into an array
    $categoriesArray = explode(',', $categories);

    // Select required fields and aggregate category names using GROUP_CONCAT
    $this->db->select('c.id, c.cityid, ci.city as cityname, c.title, c.what_new, c.categoryid, c.description, c.accreditation, c.package_type, c.logo, GROUP_CONCAT(ca.catname) AS catname, c.banner, c.estd, g.image');
    $this->db->from('college c');
    $this->db->join('category ca', 'FIND_IN_SET(ca.id, c.categoryid)');
    $this->db->join('gallery g', 'g.postid = c.id', 'left');
	$this->db->join('city ci', 'ci.id = c.cityid', 'left');
    // Filter by categories and exclude the current college
    $this->db->where_in('c.categoryid', $categoriesArray);
    $this->db->where('c.id !=', $collegeId);

    // Filter by package type and set ordering
    $this->db->where('c.package_type', 'featured_listing');
    $this->db->order_by('RAND()'); // This might not work as expected in MySQL
    $this->db->order_by('c.create_date', 'DESC');
    $this->db->group_by('c.id'); // Group by college id to avoid duplicate results
    $this->db->limit(10);

    $query = $this->db->get();
    $result = $query->result_array();

    return $result;
}
public function collegesOffereingSameCourseAtSameCity($courseid,$cityid,$collegeId)
    {
        $this->db->select('c.id, c.title');
        $this->db->from('college_course AS cc');
        $this->db->join('college AS c', 'c.id = cc.collegeid', 'left');
        $this->db->where('cc.courseid', $courseid);
        $this->db->where('cc.is_deleted', 0);
        $this->db->where('c.cityid', $cityid);
        $this->db->where('c.status', '1');
        $this->db->where('c.is_deleted', 0);
        $this->db->where('c.id !=', $collegeId);

        $this->db->order_by('c.create_date', 'desc');
        $this->db->limit(20);
        $query = $this->db->get();
        $result = $query->result_array();

        return $result;
    }
    public function getLastThreeYearsPlacementData($CurrentYear,$collegeId)
    {
        $this->db->select('ay.*, pc.name as course_category_name');
        $this->db->from('academic_year ay');
        $this->db->join('placement_category pc', 'pc.id = ay.course_category', 'left');
        $this->db->where('ay.year BETWEEN YEAR(CURRENT_DATE()) - 3 AND YEAR(CURRENT_DATE())');
        $this->db->where('ay.collegeid', $collegeId);
        $this->db->order_by('ay.year', 'DESC');

        $query = $this->db->get();
        $result = $query->result();
        return $result;

    }
}
