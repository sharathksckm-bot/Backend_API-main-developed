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
        //$this->db->where('tc.status', '1');
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

    /* public function getFilteredClg($start, $limit, $order, $dir, $loc, $ownerShip, $rankCategory)
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
    }*/


    public function getFilteredClg($clgname, $loc, $ownerShip, $rankCategory, $courseid, $value, $categoryid)
    {
        // echo "ttt";exit;
        $this->db->select('c.id, c.package_type,s.statename as statename, c.application_link, c.is_accept_entrance, c.logo, c.title, c.banner, c.estd, ci.city, COALESCE(g.image, "") AS gallery_image, (CASE WHEN c.package_type = "feature_listing" THEN 0 ELSE 1 END) AS sort_order');
        $this->db->from('college c');
        $this->db->join('city ci', 'ci.id = c.cityid', 'left');
        $this->db->join('state s', 'ci.stateid = s.id', 'left');
        $this->db->join('gallery g', 'g.postid = c.id', 'left');
        $this->db->where('c.is_deleted', '0');
        $this->db->where('c.status', '1');
        if (!empty($value)) {
            $this->db->like('c.title', $value);
        }
        if (!empty($categoryid)) {
            $this->db->where_in('c.categoryid', $categoryid);
        }
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
            $this->db->join('college_ranks cr', 'cr.college_id = c.id', 'left');
            $this->db->where('cr.category_id', $rankCategory);
        }
        if (!empty($courseid)) {
            $this->db->join('college_course cc', 'cc.collegeid = c.id', 'left');
            $this->db->where('cc.courseid', $courseid);
        }
        $this->db->group_by('c.id');

        // $this->db->order_by($order, $dir);
        //  $this->db->limit($limit, $start);


        $query = $this->db->get();
        //echo $this->db->last_query();exit;
        $result = $query->result();
        return $result;
        //return $this->db->get()->result();
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
        // echo "tttt";exit;
        $this->db->select('c.id,s.statename as statename, c.package_type, logo, c.title, bannerf, estd, ci.city, g.image, (CASE WHEN c.package_type = "feature_listing" THEN 0 ELSE 1 END) AS sort_order');
        $this->db->from('college c');
        $this->db->join('city ci', 'ci.id = c.cityid', 'left');
        $this->db->join('state s', 'ci.stateid = s.id', 'left');
        $this->db->join('gallery g', 'g.postid = c.id', 'left');
        $this->db->where('c.is_deleted', '0');
        $this->db->where('c.status', '1');
        $this->db->where('g.type', 'college');
        $this->db->group_by('c.id');
        $this->db->order_by('sort_order', 'asc');
        $this->db->limit($limit, $start);
        $query = $this->db->get();
        //echo $this->db->last_query();exit;
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


    public function getFeaturedColleges($categoryid)
    {
        //$this->db->select('c.id, c.title, c.slug, c.description, c.accreditation, c.address, c.web, c.estd, g.image, ct.city, st.statename, c.notification, c.notification_link');
        $this->db->select('c.id, c.title, c.slug, c.description, c.accreditation,  c.address, c.web, c.estd, g.image, ct.city, c.logo,  IFNULL(cc.total_fees, cf.fees) AS total_fees, cc.eligibility, cr.rank, cr.year, b.file, st.statename, c.notification, c.notification_link');
        $this->db->from('college c');
        $this->db->join('college_course cc', 'cc.collegeid = c.id', 'left');
        $this->db->join('gallery g', 'g.postid = c.id', 'left');
        $this->db->join('courses cs', 'cs.id=cc.courseid', 'left');
        $this->db->join('college_ranks cr', 'cr.college_id=cc.collegeid', 'left');
        $this->db->join('city ct', 'ct.id = c.cityid', 'left');
        $this->db->join('state st', 'st.id = c.stateid', 'left');
        $this->db->join('brochures b', 'b.collegeid = cc.collegeid', 'left');
        $this->db->join('counseling_fees cf', 'cc.categoryid = cf.sub_category', 'left');
        $this->db->where('c.package_type', 'feature_listing');
        $this->db->where("FIND_IN_SET('$categoryid',c.categoryid) > 0");
        $this->db->where('c.status', '1');
        $this->db->where('c.is_deleted', '0');
        $this->db->where('g.type', 'college');
        $this->db->group_by('c.id');
        $this->db->order_by('RAND()');
        $this->db->order_by('c.id', 'DESC');
        // $this->db->limit(8);
        $query = $this->db->get();
        $result = $query->result();
        //echo $this->db->last_query();exit;
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
        $this->db->where("c.package_type", "feature_listing");
        $this->db->or_where("cr.rank", 1);
        $this->db->group_by("c.id");
        $this->db->order_by('RAND()');
        $this->db->limit(30);

        $query = $this->db->get();
        $result = $query->result_array();
        //echo $this->db->last_query();exit;
        return $result;
    }

    /*public function getCollegeDetailsByID($id)
    {
		//print_r($id);exit;
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
        $this->db->limit(1,1);
        $query = $this->db->get();
        $result = $query->result_array();
		//echo $this->db->last_query();exit;
        return $result;

    }*/

    public function getCollegeDetailsByID($id)
    {
        // print_r($id);exit;
        $this->db->select('c.id,c.college_typeid, c.cityid, c.title, c.what_new,c.address, c.categoryid, c.description, c.accreditation, c.package_type, c.logo, ca.id as catID, ca.catname AS catname, c.banner, c.estd, ci.city, co.country, g.image, ct.name,s.statename');
        $this->db->from('college c');
        $this->db->join('city ci', 'ci.id = c.cityid', 'left');
        $this->db->join('country co', 'co.id = c.countryid', 'left');
        $this->db->join('category ca', 'FIND_IN_SET(ca.id, c.categoryid)', 'left');
        $this->db->join('college_type ct', 'ct.id = c.college_typeid', 'left');
        $this->db->join('gallery g', 'g.postid = c.id', 'left');
        $this->db->join('state s', 's.id = ci.stateid', 'left');
        $this->db->where('c.is_deleted', '0');
        $this->db->where('c.status', '1');
        // $this->db->where_or('g.type', 'college');
        $this->db->where('c.id', $id);
        $this->db->group_by('c.id, c.cityid, c.title, c.what_new, c.categoryid, c.description, c.accreditation, c.package_type, c.logo, c.banner, c.estd, ci.city, co.country, g.image, ct.name');
        $this->db->limit(1);
        $query = $this->db->get();
        $result = $query->result_array();
        // echo $this->db->last_query();exit;
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
    public function getCollegeProgrammesByID($id,  $subcategoryid = NULL)
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


        /*$subquery = $this->db->select("cc.courseid, GROUP_CONCAT(e.title SEPARATOR ', ') AS entrance_exam_names")
        ->from('college_course cc')
        ->join('exams e', 'FIND_IN_SET(e.id, cc.entrance_exams)', 'left')
        ->where('cc.collegeid', $id)
        ->group_by('cc.courseid')
        ->get_compiled_select();

        $this->db->select('COUNT(c.sub_category) AS total_courses, 
            cc.courseid, 
            cc.entrance_exams, 
            cc.duration, 
			COALESCE(cc.total_fees, cf.fees) AS total_fees,
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
        $this->db->join('counseling_fees cf','cc.categoryid = cf.sub_category','left');
        $this->db->where('cc.collegeid', $id);
        if(!empty($subcat))
        {
         $this->db->where('c.sub_category',$subcat);
        }
        $this->db->where('c.sub_category IS NOT NULL', null, false);
         //$this->db->where('c.courseid IS NOT NULL', null, false);
        $this->db->group_by('c.sub_category, cc.entrance_exams');

        $query = $this->db->get();
        //echo $this->db->last_query();exit;
        $result = $query->result_array();*/

        /*  $subquery = $this->db->select('cc.courseid, GROUP_CONCAT(e.title) AS entrance_exam_names')
            ->from('college_course cc')
            ->join('exams e', 'FIND_IN_SET(e.id, cc.entrance_exams)', 'left')
            ->where('cc.collegeid', $id)
            ->group_by('cc.courseid')
            ->get_compiled_select();*/

        /*  $this->db->select('COUNT(c.sub_category) AS total_courses, 
            cc.courseid, 
            cc.entrance_exams, 
            cc.duration, 
            c.name, 
            c.course_category, 
            ct.catname AS course_category_name, 
            c.sub_category,
            sc.eligibility,
            COALESCE(cc.total_fees, cf.fees) AS total_fees,
            sc.name AS sub_category_name, 
            e.entrance_exam_names');
        $this->db->from('college_course cc');
        $this->db->join('courses c', 'c.id = cc.courseid', 'left');
        $this->db->join('sub_category sc', 'sc.id = c.sub_category', 'left');
        $this->db->join('category ct', 'ct.id = c.course_category', 'left');
        $this->db->join('counseling_fees cf','cc.categoryid = cf.sub_category','left');
        $this->db->join("($subquery) e", 'e.courseid = cc.courseid', 'left');
        $this->db->where('cc.collegeid', $id);
        $this->db->where('c.sub_category IS NOT NULL', null, false);
        $this->db->group_by('c.sub_category, cc.entrance_exams');*/



        //-------------------------

        $subquery = $this->db->select("cc.courseid, GROUP_CONCAT(e.title SEPARATOR ',  ') AS entrance_exam_names")
            ->from('college_course cc')
            ->join('exams e', 'FIND_IN_SET(e.id, cc.entrance_exams)', 'left')
            ->where('cc.collegeid', $id)
            ->group_by('cc.courseid')
            ->get_compiled_select();

        $this->db->select('COUNT(c.sub_category) AS total_courses, 
            cc.courseid, 
            cc.entrance_exams, 
            cc.duration, 
            c.name, 
            c.course_category, 
            ct.catname AS course_category_name, 
            c.sub_category,
            sc.eligibility,
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
        //$this->db->where('cc.categoryid !=', $subcategoryid);


        $query = $this->db->get();
        // echo $this->db->last_query();exit;
        $result = $query->result_array();

        return $result;
    }

    public function getCollegeOtherProgrammesByID($id, $subcat)
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


        $subquery = $this->db->select("cc.courseid, GROUP_CONCAT(e.title SEPARATOR ',  ') AS entrance_exam_names")
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
        if (!empty($subcat)) {
            $this->db->where('c.sub_category !=', $subcat);
        }
        $this->db->where('c.sub_category IS NOT NULL', null, false);
        $this->db->group_by('c.sub_category, cc.entrance_exams');

        $query = $this->db->get();
        //echo $this->db->last_query();exit;
        $result = $query->result_array();
        return $result;
    }

    /*	public function getPlacementDataOfClg($searchCategory,$searchYear,$collegeId)
	{
	   // print_r($collegeId);exit;
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

	}*/

    public function getPlacementDataOfClg($searchCategory, $searchYear, $collegeId)
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
        //echo $this->db->last_query();exit;
        $result = $query->result();

        return $result;
    }

    public function getCommonalyAskedQ($collegeId, $type)
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
        $this->db->order_by('cf.created_date', 'desc');

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

    /*	public function getCoursesAndFeesOfClg($collegeId)
	{
		$this->db->select('
    c.id,
    c.name,
    c.academic_category,
    ac.name AS academicCategoryName,
    c.duration,
    c.course_category,
    ct.catname AS courseCategoryName,
    c.sub_category,
    sc.name AS subCategoryName,
    cc.eligibility,
    e.title AS entranceexams,
    cc.entrance_exams,
    cc.total_fees,
    ce.categoryid,
    ce.college_typeid
');
$this->db->from('college_course cc');
$this->db->join('courses c', 'c.id = cc.courseid', 'left');
$this->db->join('college ce', 'ce.id = cc.collegeid', 'left');
$this->db->join('sub_category sc', 'sc.id = c.sub_category', 'left');
$this->db->join('category ct', 'ct.id = c.course_category', 'left');
$this->db->join('academic_categories ac', 'ac.category_id = c.academic_category', 'left');
$this->db->join('exams e', 'e.id = cc.entrance_exams', 'left');
$this->db->where('cc.collegeid', $collegeId);
$this->db->where('c.id IS NOT NULL');

$query = $this->db->get();
// echo $this->db->last_query();exit;
return $query->result_array();

	} */

    public function getCoursesAndFeesOfClg($collegeId)
    {
        $this->db->select('
        c.id,
        c.name,
        c.academic_category,
        ac.name AS academicCategoryName,
        c.duration,
        c.course_category,
        ct.catname AS courseCategoryName,
        c.sub_category,
        sc.name AS subCategoryName,
        cc.eligibility,
        cc.entrance_exams,
        cc.total_fees,
        ce.categoryid,
        ce.college_typeid
    ');
        $this->db->from('college_course cc');
        $this->db->join('courses c', 'c.id = cc.courseid', 'left');
        $this->db->join('college ce', 'ce.id = cc.collegeid', 'left');
        $this->db->join('sub_category sc', 'sc.id = c.sub_category', 'left');
        $this->db->join('category ct', 'ct.id = c.course_category', 'left');
        $this->db->join('academic_categories ac', 'ac.category_id = c.academic_category', 'left');
        $this->db->where('cc.collegeid', $collegeId);
        $this->db->where('c.id IS NOT NULL');

        $query = $this->db->get();
        $courses = $query->result_array();

        // Fetch all entrance exam IDs in one go
        // $allExamIds = [];
        /*foreach ($courses as $course) {
        $ids = explode(',', $course['entrance_exams']);
        $allExamIds = array_merge($allExamIds, $ids);
    }*/

        $allExamIds = [];

        foreach ($courses as $course) {
            $examField = $course['entrance_exams'];

            if (!empty($examField)) {
                if (strpos($examField, ',') !== false) {
                    $ids = explode(',', $examField);
                    $allExamIds = array_merge($allExamIds, $ids);
                } else {
                    $allExamIds[] = $examField;
                }
            }
        }

        $allExamIds = array_unique(array_filter($allExamIds));

        if (!empty($allExamIds)) {
            $this->db->select('id, title');
            $this->db->from('exams');
            $this->db->where_in('id', $allExamIds);
            $examQuery = $this->db->get();
            $examMap = [];
            foreach ($examQuery->result() as $exam) {
                $examMap[$exam->id] = $exam->title;
            }

            /*foreach ($courses as &$course) {
            $ids = explode(',', $course['entrance_exams']);
            $names = [];
            foreach ($ids as $id) {
                $id = trim($id);
                if (isset($examMap[$id])) {
                    $names[] = $examMap[$id];
                }
            }
            $course['entranceexams'] = implode(', ', $names); // Follows ID order
        }*/


            foreach ($courses as &$course) {
                $names = [];

                if (!empty($course['entrance_exams'])) {
                    $ids = strpos($course['entrance_exams'], ',') !== false
                        ? explode(',', $course['entrance_exams'])
                        : [$course['entrance_exams']];

                    foreach ($ids as $id) {
                        $id = trim($id);
                        if (isset($examMap[$id])) {
                            $names[] = $examMap[$id];
                        }
                    }
                }

                $course['entranceexams'] = implode(', ', $names); // Preserves original order
            }
        }

        return $courses;
    }

    public function getCoursesAndFeesByClg($collegeId)
    {
        //print_r($collegeId);exit;
        $this->db->select([
            'c.id',
            'c.name',
            'c.academic_category',
            'ac.name AS academicCategoryName',
            'c.duration',
            'c.course_category',
            'ct.catname AS courseCategoryName',
            'c.sub_category',
            'sc.name AS subCategoryName',
            'cc.eligibility',
            'cc.entrance_exams',
            'cc.total_fees',
            'ce.categoryid',
            'ce.college_typeid',
            "(SELECT GROUP_CONCAT(e1.title ORDER BY 
            FIND_IN_SET(e1.id, cc.entrance_exams) DESC
         SEPARATOR ', ') 
         FROM exams e1 
         WHERE FIND_IN_SET(e1.id, cc.entrance_exams)) AS entranceexams"
        ]);

        $this->db->from('college_course cc');
        $this->db->join('courses c', 'c.id = cc.courseid', 'left');
        $this->db->join('college ce', 'ce.id = cc.collegeid', 'left');
        $this->db->join('sub_category sc', 'sc.id = c.sub_category', 'left');
        $this->db->join('category ct', 'ct.id = c.course_category', 'left');
        $this->db->join('academic_categories ac', 'ac.category_id = c.academic_category', 'left');
        $this->db->join('exams e', 'FIND_IN_SET(e.id, cc.entrance_exams)', 'left');

        $this->db->where('cc.collegeid', $collegeId);
        $this->db->where('c.id IS NOT NULL', null, false);
        $this->db->group_by('c.id');

        $query = $this->db->get();
        //echo $this->db->last_query();exit;
        return $query->result_array();
    }

    public function getTotalFeesForCollege($typeid, $cat, $subcat, $exams)
    {
        //print_r($exams);
        if (!is_array($cat)) {
            $cat = !empty($cat) ? explode(',', $cat) : [];
        }

        if (!is_array($exams)) {
            $exams = !empty($exams) ? explode(',', $exams) : [];
        }

        $this->db->select('*');
        $this->db->from('counseling_fees');
        $this->db->where('college_type', $typeid);
        $this->db->where_in('category', $cat);
        $this->db->where('sub_category', $subcat);
        if (!empty($exams)) {
            $this->db->where_in('exam_id', $exams);
        }

        $query = $this->db->get();
        return $query->result_array();
    }

    public function getCollegeFacilitiesByID($id)
    {
        $this->db->select("
        c.facilities,
        GROUP_CONCAT(f.title ORDER BY f.title SEPARATOR ', ') AS facility_titles,
        GROUP_CONCAT(f.icon ORDER BY f.icon SEPARATOR ', ') AS facility_icons
    ");
        $this->db->from('college c');
        $this->db->join('facilities f', "FIND_IN_SET(f.id, c.facilities)", 'left');
        $this->db->where('c.id', $id);
        $this->db->group_by('c.facilities');

        $query = $this->db->get();
        $result = $query->row_array(); // Use row() for object, or result_array() for all rows
        return $result;
    }

    /*public function getCoursesAndFeesOfClg($collegeId)
{
    $this->db->select('c.id, c.name, c.academic_category, ac.name AS academicCategoryName, 
                   c.duration, c.course_category, ct.catname AS courseCategoryName, 
                   c.sub_category, sc.name AS subCategoryName, 
                   cc.eligibility, cf.fees AS total_fees, e.title AS entranceexams');
$this->db->from('college_course cc');
$this->db->join('courses c', 'c.id = cc.courseid', 'left');
$this->db->join('college ce', 'ce.id = cc.collegeid', 'left');
$this->db->join('sub_category sc', 'sc.id = c.sub_category', 'left');
$this->db->join('category ct', 'ct.id = c.course_category', 'left');
$this->db->join('academic_categories ac', 'ac.category_id = c.academic_category', 'left');
$this->db->join('counseling_fees cf', 'cf.category = c.course_category 
                                      OR cf.sub_category = c.sub_category 
                                      AND cf.college_type = ce.college_typeid', 'left');
$this->db->join('exams e', 'e.id = cf.exam_id', 'left');

$this->db->where('cc.collegeid', $collegeId);

$this->db->where('c.id IS NOT NULL', null, false);
$query = $this->db->get();

//	echo $this->db->last_query();exit;
    // For debugging, use logging instead of echo
    if ($query->num_rows() > 0) {
        return $query->result();
    } else {
        return [];
    }
}*/

    public function getCoursesAndFeesOfClgbkp($collegeId)
    {
        $this->db->select('');
        $this->db->from('college_course cc');
        $this->db->join('college c', 'c.id = cc.collegeid', 'left');
        $this->db->join('sub_category sc', 'sc.id = c.sub_category', 'left');
        $this->db->join('category ct', 'ct.id = c.course_category', 'left');
        $this->db->join('academic_categories ac', 'ac.category_id = c.academic_category', 'left');
        $this->db->join('counseling_fees cf', 'cf.category = c.course_category 
                                      AND cf.sub_category = c.sub_category 
                                      AND cf.college_type = ce.college_typeid', 'left');
        $this->db->join('exams e', 'e.id = cf.exam_id', 'left');

        $this->db->where('cc.collegeid', $collegeId);

        $this->db->where('c.id IS NOT NULL', null, false);
        $query = $this->db->get();

        echo $this->db->last_query();
        exit;
        // For debugging, use logging instead of echo
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return [];
        }
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


    /*public function getCollegeAdmissionProcess($collegeId)
{
   // echo "tttt";exit;
    // Select course details, count, and concatenate exams
    $this->db->select('
        COUNT(cc.courseid) AS courseCount, 
        cc.entrance_exams, 
        GROUP_CONCAT(DISTINCT e.title ORDER BY e.title SEPARATOR ", ") AS Accepting_Exams, 
        cc.eligibility, 
        c.sub_category, 
        sc.name as subCatName, 
        c.duration'
    );
    $this->db->from('college_course cc');
    $this->db->join('courses c', 'c.id = cc.courseid', 'left');
    $this->db->join('sub_category sc', 'sc.id = c.sub_category', 'left');
    $this->db->join('exams e', 'FIND_IN_SET(e.id, cc.entrance_exams) > 0', 'left');
    $this->db->where('cc.collegeid', $collegeId);
    $this->db->where('c.sub_category IS NOT NULL');

    // Group by sub-category only, not by individual exams
    $this->db->group_by('sc.id');

    $query = $this->db->get();
    // echo $this->db->last_query();exit;

    $result = $query->result();
    return $result;
}*/


    // public function getCollegeAdmissionProcess($collegeId)
    // {
    //     // Select course details, count, and concatenate exams
    //     $this->db->select(
    //         '
    //     COUNT(cc.courseid) AS courseCount, 
    //     cc.entrance_exams, 
    //     GROUP_CONCAT(DISTINCT e.title ORDER BY e.title SEPARATOR ", ") AS Accepting_Exams, 
    //     cc.eligibility, 
    //     c.sub_category, 
    //     sc.name as subCatName, 
    //     c.duration'
    //     );
    //     $this->db->from('college_course cc');
    //     $this->db->join('courses c', 'c.id = cc.courseid', 'left');
    //     $this->db->join('sub_category sc', 'sc.id = c.sub_category', 'left');
    //     $this->db->join('exams e', 'FIND_IN_SET(e.id, cc.entrance_exams) > 0', 'left');
    //     $this->db->where('cc.collegeid', $collegeId);
    //     $this->db->where('c.sub_category IS NOT NULL');

    //     // Group by sub-category only, not by individual exams
    //     $this->db->group_by('sc.id');

    //     $query = $this->db->get();

    //     //echo $this->db->last_query();exit;
    //     $result = $query->result();

    //     // Loop through the result and handle null values
    //     foreach ($result as $key => $row) {
    //         // Unserialize the eligibility data if it is serialized, handle null eligibility
    //         if (!empty($row->eligibility)) {
    //             $eligibilityData = @unserialize($row->eligibility);
    //             if ($eligibilityData !== false) {
    //                 $result[$key]->eligibility = $eligibilityData;
    //             } else {
    //                 $result[$key]->eligibility = $row->eligibility; // Assuming eligibility is already simple
    //             }
    //         } else {
    //             // Replace null eligibility with an empty array or string
    //             $result[$key]->eligibility = [];
    //         }

    //         // Handle null Accepting_Exams
    //         if (empty($row->Accepting_Exams)) {
    //             $result[$key]->Accepting_Exams = '';
    //         }

    //         // Handle acceptingExams array if entrance_exams is null
    //         if (empty($row->entrance_exams)) {
    //             $result[$key]->acceptingExams = [];
    //         } else {
    //             // Handle exams data processing
    //             $entranceExams = explode(',', $row->entrance_exams);
    //             $acceptingExams = explode(',', $row->Accepting_Exams);

    //             // Ensure both arrays have the same length before combining
    //             $combinedExams = array();
    //             foreach ($entranceExams as $index => $examId) {
    //                 $combinedExams[] = array(
    //                     'id' => $examId,
    //                     'value' => isset($acceptingExams[$index]) ? $acceptingExams[$index] : ''
    //                 );
    //             }

    //             $result[$key]->acceptingExams = $combinedExams;
    //         }
    //     }

    //     return $result;
    // }

    public function getCollegeAdmissionProcess($collegeId)
    {
        $this->db->select('
        COUNT(DISTINCT cc.courseid) AS courseCount,
        cc.entrance_exams,
        GROUP_CONCAT(DISTINCT e.title) AS Accepting_Exams,
        
        sc.eligibility As eligibility,
        c.sub_category,
        sc.name as subCatName,
        c.duration
    ');
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



    public function getExamsNotification($examsId)
    {
        $this->db->select('notification');
        $this->db->from('exams');
        $this->db->where('id', $examsId);
        $query = $this->db->get();
        $result = $query->result_array();
        return $result;
    }

    public function collegeByLocation($cityid, $collegeId)
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
        $this->db->from($this->table . " c");
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
        $this->db->where('c.package_type', 'feature_listing');
        $this->db->group_by('c.id');
        $this->db->order_by('RAND()');
        $this->db->order_by('c.create_date', 'desc');
        $this->db->limit(10);

        $query = $this->db->get();
        return $query->result();
    }

    public function getCollegesAccordingCategory($collegeId, $categories)
    {
        /* Explode categories string into an array
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
    //$this->db->where('c.package_type', 'featured_listing');
    $this->db->order_by('RAND()'); // This might not work as expected in MySQL
    $this->db->order_by('c.create_date', 'DESC');
    $this->db->group_by('c.id'); // Group by college id to avoid duplicate results
    $this->db->limit(10);
	
    $query = $this->db->get();
    $result = $query->result_array();
	echo $this->db->last_query();exit;
    return $result;*/
        $categoriesArray = explode(',', $categories);

        // Select required fields and aggregate category names using GROUP_CONCAT
        $this->db->select('c.id,ci.city AS cityname, c.cityid,c.title, c.what_new, c.categoryid, c.description, c.accreditation, c.package_type, c.logo, GROUP_CONCAT(ca.catname) AS catname, c.banner, c.estd, g.image');
        $this->db->from('college c');
        $this->db->join('category ca', 'FIND_IN_SET(ca.id, c.categoryid)');
        $this->db->join('gallery g', 'g.postid = c.id', 'left');
        $this->db->join('city ci', 'ci.id = c.cityid', 'left');

        // Filter by categories and exclude the current college
        $this->db->where_in('c.categoryid', $categoriesArray);
        $this->db->where('c.id !=', $collegeId);

        // Filter by package type and set ordering
        $this->db->where('c.package_type', 'feature_listing');
        $this->db->order_by('RAND()'); // This might not work as expected in MySQL
        $this->db->order_by('c.create_date', 'DESC');
        $this->db->group_by('c.id'); // Group by college id to avoid duplicate results
        $this->db->limit(10);

        $query = $this->db->get();
        $result = $query->result_array();
        return $result;
    }
    public function collegesOffereingSameCourseAtSameCity($courseid, $cityid, $collegeId)
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
    public function getLastThreeYearsPlacementData($CurrentYear, $collegeId)
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

    public function get_colleges($search_term)
    {
        //$this->db->select('id,title');
        $this->db->select('*');
        $this->db->from('college');
        $this->db->where('status', '1');
        $this->db->where('is_deleted', '0');
        $this->db->like('title', $search_term);
        $this->db->limit(10);

        $query = $this->db->get(); // Execute the query

        if ($query->num_rows() > 0) {
            return $query->result(); // Return the result if there are rows
        } else {
            return array(); // Return an empty array if no rows found
        }
    }

    public function getPopularCollegeListForCompare($categoryid)
    {
        // $sql = "SELECT `c`.`id`, `c`.`title` 
        // FROM `college` `c` 
        // LEFT JOIN `city` `ci` ON `ci`.`id` = `c`.`cityid` 
        // LEFT JOIN `state` `s` ON `s`.`id` = `c`.`stateid` 
        // WHERE `c`.`is_trending` = 1 
        // LIMIT 6";

        $this->db->select('c.id, c.cityid, c.title, c.application_link, c.what_new, c.categoryid, c.description, c.accreditation, c.package_type, c.logo, c.banner, c.estd, ci.city, co.country, g.image, ct.name');
        $this->db->from('college c');
        $this->db->join('city ci', 'ci.id = c.cityid', 'left');
        $this->db->join('country co', 'co.id = c.countryid', 'left');
        // $this->db->join('category ca', 'FIND_IN_SET(ca.id, c.categoryid)', 'left');
        $this->db->join('college_type ct', 'ct.id = c.college_typeid', 'left');
        $this->db->join('gallery g', 'g.postid = c.id', 'left');
        $this->db->where('c.is_deleted', '0');
        $this->db->where('c.status', '1');
        $this->db->where('g.type', 'college');
        $category_ids = explode(',', $categoryid); // Convert string to array
        $category_ids = array_map('intval', $category_ids); // Convert array elements to integers
        $this->db->where_in('c.categoryid', $category_ids);

        $this->db->where('c.is_trending', 1);
        // $this->db->group_by('c.id, c.cityid, c.title, c.what_new, c.categoryid, c.description, c.accreditation, c.package_type, c.logo, c.banner, c.estd, ci.city, co.country, g.image, ct.name');
        $this->db->limit(10);

        $query = $this->db->get();
        // echo $this->db->last_query();exit;
        $result = $query->result_array();
        return $result;
    }



    /*	 public function countFilteredCourseinquiry($search)
  {
    $this->db->like('id', $search);
    $this->db->like('name', $search);
    $this->db->like('email', $search);
    $this->db->like('phone', $search);
    $this->db->like('category', $search);
    $this->db->like('coursename', $search);
    $this->db->like('interested', $search);
    $this->db->like('is_read', $search);
    $this->db->like('create_date', $search);

    $query = $this->db->get('course_inquiry');
    return $query->num_rows();
  }*/


    public function getCollegeDetailsBy_ID($collegeId)
    {
        // echo "testing...";exit;
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
        $this->db->where('c.id', $collegeId);
        $this->db->group_by('c.id, c.cityid, c.title, c.what_new, c.categoryid, c.description, c.accreditation, c.package_type, c.logo, c.banner, c.estd, ci.city, co.country, g.image, ct.name');
        $this->db->limit(1, 1);

        $query = $this->db->get();
        $result = $query->result_array();
        echo $this->db->last_query();
        return $result;
    }

    //---
    public function getUserDetailsById($id)
    {
        $this->db->select('*');
        $this->db->from('users');
        $this->db->where('id', $id);
        $query = $this->db->get()->result();
        return $query;
    }

    public function getCollgeFacilities($collegeId)
    {
        $this->db->select('facilities');
        $this->db->from('college');
        $this->db->where('id', $collegeId);
        $query = $this->db->get()->result();
        return $query;
    }

    public function GetFacilities($Id)
    {
        $this->db->select('*');
        $this->db->from('facilities');
        $this->db->where('id', $Id);
        $query = $this->db->get()->result();
        return $query;
    }

    //--------------

    public function getCollegePlacement($categoryId, $limit)
    {
        // print_r($limit);exit;
        /*$this->db->select('c.id as collegeid,c.title,c.logo,cc.total_fees,city.city as city_name');
        $this->db->from('college_course cc');
		$this->db->join('college AS c', 'c.id = cc.collegeid', 'left');
		$this->db->join('city', 'city.id = c.cityid', 'left');
        $this->db->where('cc.placement !=', '');
		$this->db->like('c.categoryid', $categoryId);
        $this->db->limit($limit);
        $this->db->group_by('c.id');
        $query = $this->db->get()->result();
        return $query;*/
        $this->db->distinct();
        $this->db->select('c.id as collegeid, c.title, c.logo, cc.total_fees, city.city as city_name,c.categoryid');
        $this->db->from('college_course cc');
        $this->db->join('college AS c', 'c.id = cc.collegeid', 'left');
        $this->db->join('city', 'city.id = c.cityid', 'left');
        $this->db->join('academic_year ay', 'ay.collegeid = c.id', 'inner'); // Ensures college has placement data
        $this->db->where('cc.placement !=', '');
        $this->db->like('c.categoryid', $categoryId);
        $this->db->limit($limit);
        $this->db->group_by('c.id');

        $query = $this->db->get()->result();
        return $query;
    }

    ///--------///

    public function get_collegesId($courseId)
    {
        $this->db->select('c.id as collegeid, c.accreditation, c.title, c.logo, ci.city, c.address, cc.total_fees, cr.rank, cr.year, rc.title as category, b.file');
        $this->db->from('college_course cc');
        $this->db->join('college c', 'c.id=cc.collegeid', 'left');
        $this->db->join('courses cs', 'cs.id=cc.courseid', 'left');
        $this->db->join('college_ranks cr', 'cr.college_id=cc.collegeid', 'left');
        $this->db->join('city ci', 'ci.id = c.cityid', 'left');
        $this->db->join('rank_categories rc', 'rc.category_id=cr.category_id', 'left');
        $this->db->join('brochures b', 'b.collegeid = cc.collegeid', 'left');
        $this->db->like('cc.courseid', $courseId);
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
        /*
    $this->db->select('*');
    $this->db->from('college c');
	$this->db->join('college_course AS cc', 'cc.collegeid = c.id', 'left');
    $this->db->where('courseid', $courseId);
    $this->db->limit(10);
    
    $query = $this->db->get(); // Execute the query

    if ($query->num_rows() > 0) {
        return $query->result(); // Return the result if there are rows
    } else {
        return array(); // Return an empty array if no rows found
    }*/
    }


    function getCollegeTotalRate($collegeId = '')
    {
        $reviewDt = $this->getReviewBreakup($collegeId);
        $totalRate = $totalRateCount = $totalReview = 0;
        if (!empty($reviewDt[$collegeId])) {
            $data = $reviewDt[$collegeId];
            $totalRate = $data["ratingPercent"];
            $totalRateCount = $data["totalRating"];
            $totalReview = $data["total_reviews"];
        }
        return [
            'totalRate' => $totalRate,
            'totalRateCount' => $totalRateCount,
            'totalReview' => $totalReview
        ];
    }





    /*	    function getCollegeTotalRate($collegeId = '')
    {
        $reviewDt = $this->getReviewBreakup($collegeId);
			
			print_r($reviewDt);exit;
        $totalRate = $totalRateCount = $totalReview = 0;
        if (!empty($reviewDt[$collegeId])) {
            $data = $reviewDt[$collegeId];
            $totalRate = $data["ratingPercent"];
            $totalRateCount = $data["totalRating"];
            $totalReview = $data["total_reviews"];
        }
        return [
            'totalRate' => $totalRate,
            'totalRateCount' => $totalRateCount,
            'totalReview' => $totalReview
        ];
    }*/


    function getReviewBreakup($collegeIds = [])
    {
        $returnData = [];
        $collegeIds = !is_array($collegeIds) ? [$collegeIds] : $collegeIds;
        if (!empty($collegeIds)) {
            foreach ($collegeIds as $collegeId) {
                $avgReview = $this->db->select('AVG(placement_rate) as placement_rate, AVG(infrastructure_rate) as infrastructure_rate, AVG(faculty_rate) as faculty_rate, AVG(hostel_rate) as hostel_rate, AVG(campus_rate) as campus_rate, AVG(money_rate) as money_rate, COUNT(*) as total_reviews')
                    ->where('college_id', $collegeId)
                    ->where('status', '1')
                    ->get('review r')->row();
                if (!empty($avgReview)) {
                    $returnData[$collegeId]['total_reviews'] = $avgReview->total_reviews;
                    unset($avgReview->total_reviews);
                    $avgReview = (array)$avgReview;
                    foreach ($avgReview as $key => &$value) {
                        if (is_numeric($value)) {
                            $value = number_format($value, 1);
                        } else {
                            // Handle the case where $value is not a valid number
                            // For example, you might set it to 0 or a default value
                            $value = 0;
                        }
                    }

                    $returnData[$collegeId]['catAvgReview'] = (array)$avgReview;

                    $reviews = $this->db->where('college_id', $collegeId)->where('status', '1')->get('review r')->result_array();
                    $returnData[$collegeId]['totalRating'] = 0;
                    $returnData[$collegeId]['ratingPercent'] = 0;
                    if (!empty($reviews)) {
                        $totalAvg = 0;
                        foreach ($reviews as $review) {
                            $avg = ($review['placement_rate'] + $review['infrastructure_rate'] + $review['faculty_rate'] + $review['hostel_rate'] + $review['campus_rate'] + $review['money_rate']) / 6;
                            $totalAvg += $avg;
                        }
                        $returnData[$collegeId]['totalRating'] = number_format($totalAvg / count($reviews), 1);
                        $returnData[$collegeId]['ratingPercent'] = ($returnData[$collegeId]['totalRating'] * 100) / 5;
                    }
                }
            }
        }
        return $returnData;
    }

    public function updateStatus($collegeId, $status)
    {

        $this->db->select('*');
        $this->db->from('facilities');
        $this->db->where('id', $Id);
        $query = $this->db->get()->result();
        return $query;
    }

    public function insertSpecificCollege($data)
    {
        $query = $this->db->insert('specific_college', $data);
        ///echo $this->db->last_query();exit;
        return $this->db->insert_id();
    }

    public function updateSpecificCollege($updateData, $userId, $collegeId)
    {
        $this->db->where('userId', $userId);
        $this->db->where('collegeId', $collegeId);
        $result =  $this->db->update('specific_college', $updateData);
        return $result;
    }

    public function checkSpecificCollege($userId, $collegeId)
    {
        $this->db->select('*');
        $this->db->from('specific_college');
        $this->db->where('userId', $userId);
        $this->db->where('collegeId', $collegeId);
        $this->db->where('active', 1);

        $query = $this->db->get()->result();
        return $query;
    }

    public function getSpecificCollegeList($userId, $collegeId)
    {
        $this->db->select('*');
        $this->db->from('specific_college');
        $this->db->where('userId', $userId);
        //$this->db->where('collegeId',$collegeId);
        $this->db->where('active', 1);
        $this->db->order_by('create_date', 'DESC');
        $query = $this->db->get()->result();
        //echo $this->db->last_query();exit;
        return $query;
    }


    public function getCollegeListById($collegeId)
    {
        $this->db->select('c.id as collegeid, c.accreditation, c.title, c.logo, c.address, cs.name, cc.total_fees, cc.eligibility, cr.rank, cr.year, rc.title as category, b.file');
        $this->db->from('college_course cc');
        $this->db->join('college c', 'c.id=cc.collegeid', 'left');
        $this->db->join('courses cs', 'cs.id=cc.courseid', 'left');
        $this->db->join('college_ranks cr', 'cr.college_id=cc.collegeid', 'left');
        $this->db->join('rank_categories rc', 'rc.category_id=cr.category_id', 'left');
        $this->db->join('brochures b', 'b.collegeid = cc.collegeid', 'left');
        $this->db->where('c.id', $collegeId);
        $this->db->where('c.title IS NOT NULL');
        $query = $this->db->get();
        $result = $query->result_array();
        //echo $this->db->last_query();exit;
        return $result;
    }

    public function getImageById($Id)
    {
        $this->db->select("g.image");
        $this->db->from("gallery g");
        $this->db->where('g.type', 'college');
        $this->db->where('g.postid', $Id);
        $result = $this->db->get()->result_array();
        return $result;
    }

    public function getListOfShortListedColleges($userId)
    {
        $this->db->select('s.userid, s.collegeid, c.title as collegename, c.accreditation, c.logo, c.address, cr.rank, cr.year, rc.title as category, b.file, cc.total_fees, cc.eligibility, ci.city');
        $this->db->from('specific_college s');
        $this->db->join('college c', 'c.id = s.collegeid', 'left');
        $this->db->join('college_course cc', 'cc.id=s.collegeid', 'left');
        $this->db->join('college_ranks cr', 'cr.college_id= s.collegeid', 'left');
        $this->db->join('rank_categories rc', 'rc.category_id=cr.category_id', 'left');
        $this->db->join('brochures b', 'b.collegeid = s.collegeid', 'left');
        $this->db->join('city ci', 'ci.id = c.cityid', 'left');
        $this->db->where('userId', $userId);
        //$this->db->where('collegeId',$collegeId);
        $this->db->where('s.active', 1);
        $this->db->group_by('s.collegeid');
        $this->db->order_by('s.create_date', 'DESC');
        $query = $this->db->get()->result();
        //echo $this->db->last_query();exit;
        return $query;
    }

    public function getTrendColleges()
    {
        $this->db->select('c.id, c.title, g.image');
        $this->db->from('college c');
        $this->db->join('gallery g', 'g.postid = c.id', 'left');
        $this->db->where('is_trending ', 1);
        $query = $this->db->get()->result();
        $this->db->limit(10);
        return $query;
    }

    public function getGallaryOfTrendColleges($id)
    {
        $this->db->select('*');
        $this->db->from('gallery');
        $this->db->where('type ', 'College');
        $this->db->where('postid ', $id);
        $query = $this->db->get()->result_array();
        return $query;
    }

    public function increment_view($id)
    {
        // Increment view in the question table
        $this->db->where('id', $id);
        $this->db->set('views', 'views+1', FALSE);
        $this->db->update($this->table);

        // Get current views count
        $this->db->select('views');
        $this->db->where('id', $id);
        $query = $this->db->get($this->table);
        $views = $query->row()->views;

        // Update views count in college_report table
        $this->db->where('college', $id);
        $this->db->set('no_of_views', $views);
        $this->db->update('college_report');
    }

    function saveBrochures($brochureData)
    {
        $this->db->insert('brochures', $brochureData);
        return $this->db->insert_id();
    }


    ///------------

    public function getCourses($id, $courselevel, $subcategory)
    {
        $this->db->select('cc.courseid,cc.level, ca.name as coursename,ca.duration');
        $this->db->from('college_course cc');
        $this->db->where("collegeid", $id);
        if (!empty($courselevel) && !empty($subcategory)) {
            $this->db->where("level", $courselevel);
            $this->db->where("categoryid", $subcategory);
        }

        $this->db->join('courses ca', 'ca.id = cc.courseid', 'left');
        $this->db->limit(1, 1);

        $query = $this->db->get();
        $result = $query->result_array();
        //echo $this->db->last_query();      exit;
        return $result;
    }




    public function getAcademicDataByClgId($id)
    {
        $this->db->select('ay.year,ay.no_of_companies_visited,ay.no_of_students_placed,ay.median_salary,ay.no_of_student_selected');
        $this->db->from('academic_year ay');
        $this->db->where('ay.collegeid', $id);
        $this->db->order_by('ay.id', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }


    public function getCollegeCoursesCountByID($id)
    {
        $this->db->where("collegeid", $id);
        return $this->db->count_all_results('college_course');
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

    public function checkdata($searchCategoryId, $collegeId)
    {
        $this->db->select('ay.*, pc.name as categoryName, cc.total_fees, cc.eligibility');
        $this->db->from('academic_year ay');
        $this->db->where('ay.collegeid', $collegeId);
        $this->db->join('placement_category pc', 'pc.id = ay.course_category', 'left');
        $this->db->join('college_course cc', 'cc.collegeid=ay.collegeid', 'left');
        $this->db->where_in('course_category', $searchCategoryId);
        $query = $this->db->get();
        $result = $query->result();

        return $result;
    }

    public function getYearByCategory($collegeId, $categoryId)
    {
        $this->db->select('year');
        $this->db->from('academic_year');
        $this->db->where('collegeid', $collegeId);
        $this->db->where('course_category', $categoryId);


        $query = $this->db->get()->result();
        //echo $this->db->last_query();exit;
        return $query;
    }

    public function getfees($id, $subcat, $collegetype)
    {
        // print_r($collegetype);exit;
        $typeid = null;

        if (!empty($collegetype)) {
            $typeQuery = $this->db
                ->select('id')
                ->from('college_type')
                ->where('name', $collegetype)
                ->get();

            if ($typeQuery->num_rows() > 0) {
                $typeid = $typeQuery->row()->id;
            }
        }

        $this->db->select('COALESCE(cc.total_fees, cf.fees) AS total_fees, cc.courseid');
        $this->db->from('college_course cc');
        $this->db->join('courses c', 'c.id = cc.courseid', 'left');
        $this->db->join('counseling_fees cf', 'cc.categoryid = cf.sub_category', 'left');
        $this->db->join('college cl', 'cl.id = cc.collegeid', 'LEFT');

        $this->db->where('cc.collegeid', $id);
        $this->db->where('c.sub_category IS NOT NULL', null, false);

        if (!empty($typeid)) {
        $this->db->where('cf.college_type', $typeid);
    }

        $this->db->group_by('c.sub_category, cc.entrance_exams');
        //$this->db->where('cc.categoryid !=', $subcategoryid);


        $query = $this->db->get();
        // echo $this->db->last_query();
        // exit;
        $result = $query->result_array();

        return $result;
    }
    public function getexams($id, $subcat)
    {
        $this->db->select('cc.courseid, GROUP_CONCAT(e.title) AS entrance_exam_names');
        $this->db->from('college_course cc');
        $this->db->join('exams e', 'FIND_IN_SET(e.id, cc.entrance_exams)', 'left', false);
        $this->db->where('cc.collegeid', $id);
        $this->db->group_by('cc.courseid');

        $query = $this->db->get();
        return $query->result_array();
    }

    public function getBrochuresByCollegeId($collegeId)
    {
        return $this->db->where('collegeid', $collegeId)
            ->get('brochures')
            ->result_array();
    }

    public function deleteBrochuresByCollegeId($collegeId)
    {
        return $this->db->where('collegeid', $collegeId)
            ->delete('brochures');
    }

    public function insertBrochure($data)
    {
        return $this->db->insert('brochures', $data);
    }

    public function updateBrochure($id, $data)
    {
        return $this->db->where('id', $id)
            ->update('brochures', $data);
    }
}
