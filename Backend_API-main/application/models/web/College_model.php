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

    public function getCategoryId($course)
    {
        $this->db->select('course_category');
        $this->db->from('courses');
        $this->db->where('id', $course);

        $query = $this->db->get();
        //echo $this->db->last_query();exit; 
        return $query->row_array();;
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
    public function countFilteredClg($search, $loc, $ownerShip, $rankCategory, $courseid, $value, $categoryid = '', $rankid = NULL)
    {
        $this->db->select('c.id, c.package_type, c.application_link, c.logo, c.is_accept_entrance, c.title, c.banner, c.estd, ci.city, COALESCE(g.image, "") AS gallery_image, (CASE WHEN c.package_type IN ("featured_listing", "feature_listing") THEN 0 ELSE 1 END) AS sort_order');
        $this->db->from($this->table . ' c');
        $this->db->join('gallery g', 'g.postid = c.id', 'left');
        $this->db->join('city ci', 'ci.id = c.cityid', 'left');

        $this->db->where('c.is_deleted', '0');
        $this->db->where('c.status', '1');
        //$this->db->where('g.type', 'college');
        if (!empty($search)) {
            $this->db->like('c.title', $search);
        }
        if (!empty($value)) {
            $this->db->like('c.title', $value);
        }
        if (!empty($loc)) {
            $locArray = array_map('intval', explode(',', $loc));

            $this->db->where_in('c.cityid', $locArray);
        }
        if (!empty($ownerShip)) {
            $ownerArray = array_map('intval', explode(',', $ownerShip));

            $this->db->where_in('c.college_typeid', $ownerArray);
        }
        if (!empty($categoryid)) {
            $this->db->where_in('c.categoryid', (int)$categoryid);
            // $this->db->where('c.package_type', 'featured_listing');
        }
        if (!empty($rankCategory)) {
            $this->db->join('college_ranks cr', 'cr.college_id = c.id', 'left');
            $this->db->where('cr.category_id', $rankCategory);
        }
        if (!empty($courseid)) {
            $CourseArray = array_map('intval', explode(',', $courseid));
            $this->db->join('college_course cc', 'cc.collegeid = c.id', 'left');
            $this->db->where_in('cc.courseid', $courseid);
        }
        if (!empty($rankid)) {
            $ranges = explode(',', $rankid);
            $this->db->join('college_ranks crs', 'crs.college_id = c.id', 'left');

            foreach ($ranges as $range) {

                list($start, $end) = explode('-', $range);
                $this->db->group_start();
                $this->db->where('crs.rank >=', $start);
                $this->db->where('crs.rank <=', $end);
                $this->db->group_end();
            }
        }



        $this->db->group_start();
        $this->db->where('(g.postid = c.id OR g.postid IS NULL)');
        $this->db->or_where('g.postid', '');
        $this->db->group_end();

        $this->db->group_by('c.id');
        $this->db->order_by('sort_order', 'asc');
        $this->db->order_by('c.id', 'desc');
        //$this->db->limit(10);

        $result = $this->db->get();
        //echo $this->db->last_query();exit;  
        return $result->num_rows();
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

    public function getFilteredClg($clgname, $start, $limit, $order, $dir, $loc, $ownerShip, $rankCategory, $courseid, $value, $categoryid = '', $rankid = '')
    {
        $this->db->select('c.id,c.slug, c.package_type, c.application_link, c.logo, c.is_accept_entrance, c.title, c.banner, c.estd, ci.city, COALESCE(g.image, "") AS gallery_image, (CASE WHEN c.package_type IN ("featured_listing", "feature_listing") THEN 0 ELSE 1 END) AS sort_order');

        $this->db->from('college c');
        $this->db->join('city ci', 'ci.id = c.cityid', 'left');
        $this->db->join('gallery g', 'g.postid = c.id', 'left');
        $this->db->where('c.is_deleted', 0);
        $this->db->where('c.status', '1');
        $this->db->where('g.type', 'college');
        if (!empty($value)) {
            $this->db->like('c.title', $value);
        }
        if (!empty($categoryid)) {
            $this->db->where("FIND_IN_SET(" . (int)$categoryid . ", c.categoryid) >", 0);
        }


        if (!empty($loc)) {
            $locArray = array_map('intval', explode(',', $loc));

            $this->db->where_in('c.cityid', $locArray);
        }
        if (!empty($clgname)) {
            $this->db->like('c.title', $clgname);
        }
        if (!empty($ownerShip)) {
            $ownerArray = array_map('intval', explode(',', $ownerShip));

            $this->db->where_in('c.college_typeid', $ownerArray);
        }
        if (!empty($rankCategory)) {
            $this->db->join('college_ranks cr', 'cr.college_id = c.id', 'left');
            $this->db->where('cr.category_id', $rankCategory);
        }
        if (!empty($rankid)) {
            $ranges = explode(',', $rankid);
            $this->db->join('college_ranks crs', 'crs.college_id = c.id', 'left');

            foreach ($ranges as $range) {

                list($start, $end) = explode('-', $range);
                $this->db->group_start();
                $this->db->where('crs.rank >=', $start);
                $this->db->where('crs.rank <=', $end);
                $this->db->group_end();
            }
        }

        if (!empty($courseid)) {
            $CourseArray = array_map('intval', explode(',', $courseid));
            $this->db->join('college_course cc', 'cc.collegeid = c.id', 'left');
            $this->db->where_in('cc.courseid', $courseid);
        }
        $this->db->group_start();
        $this->db->where('(g.postid = c.id OR g.postid IS NULL)');
        $this->db->or_where('g.postid', '');
        $this->db->group_end();

        $this->db->group_by('c.id');
        $this->db->order_by('sort_order', 'asc');
        $this->db->order_by('c.id', 'desc');

        // $this->db->order_by($order, $dir);
        $this->db->limit($limit, $start);
        //$this->db->limit(5);

        $query = $this->db->get();
        //  echo $this->db->last_query();exit;
        $result = $query->result();
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

    public function getAllClg($start, $limit, $order, $dir)
    {
        $this->db->select('c.id,c.slug, c.package_type, c.application_link, c.logo, c.is_accept_entrance, c.title, c.banner, c.estd, ci.city, COALESCE(g.image, "") AS gallery_image, (CASE WHEN c.package_type IN ("featured_listing", "feature_listing") THEN 0 ELSE 1 END) AS sort_order');

        $this->db->from('college c');
        $this->db->join('city ci', 'ci.id = c.cityid', 'left');
        $this->db->join('gallery g', 'g.postid = c.id', 'left');
        $this->db->where('c.is_deleted', 0);
        $this->db->where('c.status', '1');
        $this->db->where('g.type', 'college');
        $this->db->group_by('c.id');
        $this->db->order_by('sort_order', 'ASC');
        $this->db->order_by('c.id', 'DESC');
        $this->db->limit($limit, $start);
        $query = $this->db->get();
        // echo $this->db->last_query();exit;
        $result = $query->result();
        return $result;
    }

    public function getRankListByClgId($clgId)
    {
        $this->db->select('rc.title, cr.rank, cr.year');
        $this->db->from('rank_categories rc');
        $this->db->join('college_ranks cr', 'rc.category_id = cr.category_id AND cr.college_id = "' . $clgId . '"', 'left');
        $this->db->where('rc.is_active', 1);
        $this->db->where('cr.rank IS NOT NULL', null, false);
        $this->db->where('cr.year IS NOT NULL', null, false);


        $this->db->order_by('cr.rank', 'ASC');
        $this->db->limit(5);
        $this->db->group_by('rc.title, cr.rank, cr.year');

        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }


    public function getFeaturedColleges()
    {
        $this->db->select('c.id, c.title, c.accreditation,c.estd, COALESCE(g.image, "") AS image,,ct.city,st.statename,c.notification,c.notification_link');
        $this->db->from('college c');
        $this->db->join('gallery g', 'g.postid = c.id', 'left');
        $this->db->join('city ct', 'ct.id = c.cityid', 'left');
        $this->db->join('state st', 'st.id = c.stateid', 'left');
        $this->db->group_start();
        $this->db->where('c.package_type', 'featured_listing');
        $this->db->or_where('c.package_type', 'feature_listing');

        $this->db->group_end();
        $this->db->where('c.status', '1');
        $this->db->where('c.view_in_menu', '1');
        $this->db->where('c.is_deleted', 0);
        $this->db->where('g.type', 'college');

        $this->db->group_by('c.id');
        $this->db->order_by('RAND()');
        $this->db->order_by('c.id', 'DESC');
        $this->db->limit(8);
        $query = $this->db->get();
        //echo $this->db->last_query();exit;
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
        $this->db->where("c.is_trending", 1);
        $this->db->where("status", 1);
        $this->db->where("is_deleted", 0);
        $this->db->or_where("cr.rank", 1);
        $this->db->group_by("c.id");
        $this->db->order_by('RAND()');
        $this->db->limit(30);

        $query = $this->db->get();
        $result = $query->result_array();
        //  echo $this->db->last_query();exit;
        return $result;
    }

    public function getCollegeDetailsByID($id)
    {
        $this->db->select('c.id,c.logo,c.banner,c.address, c.cityid,c.is_accept_entrance,c.college_typeid,cc.categoryid as subcategory, c.title,c.application_link, c.what_new, c.categoryid, c.description, c.accreditation, c.package_type, c.logo, GROUP_CONCAT(ca.catname) AS catname, c.banner, c.estd, ci.city, co.country, COALESCE(g.image, "") AS image, ct.name');
        $this->db->from('college c');
        $this->db->join('city ci', 'ci.id = c.cityid', 'left');
        $this->db->join('college_course cc', 'cc.collegeid = c.id', 'left');

        $this->db->join('country co', 'co.id = c.countryid', 'left');
        $this->db->join('category ca', 'FIND_IN_SET(ca.id, c.categoryid)', 'left');
        $this->db->join('college_type ct', 'ct.id = c.college_typeid', 'left');
        $this->db->join('gallery g', 'g.postid = c.id', 'left');
        $this->db->where('c.is_deleted', 0);
        $this->db->where('c.status', 1);
        $this->db->where('c.id', $id);
        $this->db->group_by('c.id, c.cityid, c.title, c.what_new, c.categoryid, c.description, c.accreditation, c.package_type, c.logo, c.banner, c.estd, ci.city, co.country, g.image, ct.name');
        $this->db->limit(1);

        $query = $this->db->get();
        // echo $this->db->last_query();exit;
        $result = $query->result_array();
        return $result;
    }
    public function getExamsForClg($college_id)
    {
        $this->db->select('id,entrance_exams');
        $this->db->from('college_course');
        $this->db->where('collegeid', $college_id);

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
    public function getCollegeProgrammesByID($id, $subcategoryid = NULL)
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





        // $query = $this->db->get();
        // echo $this->db->last_query();exit;
        // $result = $query->result_array();
        return $result;
    }

    public function getPlacementDataOfClg($searchCategory, $searchYear, $collegeId)
    {
        // print_r($searchCategory);exit;
        $this->db->select('*,pc.name as categoryName');
        $this->db->from('academic_year ay');
        $this->db->where('ay.collegeid', $collegeId);
        $this->db->join('placement_category pc', 'pc.id = ay.course_category', 'left');
        if (!empty($searchCategory)) {
            $this->db->where('ay.course_category', $searchCategory);
        }
        if (!empty($searchYear)) {
            $this->db->like('ay.year', $searchYear);
        }
        $query = $this->db->get();
        //echo $this->db->last_query();exit;
        $result = $query->result();

        return $result;
    }

    /*public function getCommonalyAskedQ($collegeId,$type)
	{
		$this->db->select('cf.faq_ids as faq_id, GROUP_CONCAT(f.heading) AS question');
		$this->db->from('college_faq cf');
		$this->db->join('faq f', 'FIND_IN_SET(f.id, cf.faq_ids) > 0', 'left');
		$this->db->where('cf.collegeid', $collegeId);
		$this->db->where('cf.faq_type', $type);
		$this->db->group_by('cf.faq_ids');
		$query = $this->db->get();
        // echo $this->db->last_query();exit;
		$result = $query->result_array();

		return $result;
		
	}
*/
    public function getCommonalyAskedQ($collegeId, $type)
    {
        $this->db->select('f.id AS faq_id, f.heading AS question');
        $this->db->from('college_faq cf');
        $this->db->join('faq f', 'FIND_IN_SET(f.id, cf.faq_ids)', 'inner');  // Ensure only valid matches
        $this->db->where('cf.collegeid', $collegeId);
        $this->db->where('cf.faq_type', $type);
        $this->db->order_by('FIELD(f.id, cf.faq_ids)');  // Maintain order
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
        //  echo $this->db->last_query(); exit;
        return $result;
    }



    public function getDescriptionForFAQ($faq_id)
    {
        $this->db->select('description as answere');
        $this->db->from('faq');
        $this->db->where('id', $faq_id);
        // 		$this->db->order_by('id','asc');

        $query = $this->db->get();
        $result = $query->result_array();

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
                                      AND cf.sub_category = c.sub_category 
                                      AND cf.college_type = ce.college_typeid', 'left');
$this->db->join('exams e', 'e.id = cf.exam_id', 'left');
$this->db->where('cc.collegeid', $collegeId);
$this->db->where('c.id IS NOT NULL', null, false);
$query = $this->db->get();


    // For debugging, use logging instead of echo
    if ($query->num_rows() > 0) {
        return $query->result();
    } else {
        return [];
    }
}*/
    public function getTotalFeesForCollege($typeid, $cat, $subcat, $exams)
    {

        if (!is_array($cat)) {
            $cat = !empty($cat) ? explode(',', $cat) : [];
        }

        if (!is_array($exams)) {
            $exams = !empty($exams) ? explode(',', $exams) : [];
        }

        $this->db->select('*');
        $this->db->from('counseling_fees');
        $this->db->where('college_type', $typeid);
        $this->db->where('sub_category', $subcat);
        if (!empty($cat)) {
            $this->db->where_in('category', $cat);
        }

        if (!empty($exams)) {
            $this->db->where_in('exam_id', $exams);
        }
        $query = $this->db->get();

        return $query->result_array();
    }
    /*	public function getCoursesAndFeesOfClg($collegeId)
	{
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
    'GROUP_CONCAT(e.title SEPARATOR ", ") AS entranceexams', // Combine exam titles
    'cc.entrance_exams',
    'cc.total_fees',
    'ce.categoryid',
    'ce.college_typeid'
]);
$this->db->from('college_course cc');
$this->db->join('courses c', 'c.id = cc.courseid', 'left');
$this->db->join('college ce', 'ce.id = cc.collegeid', 'left');
$this->db->join('sub_category sc', 'sc.id = c.sub_category', 'left');
$this->db->join('category ct', 'ct.id = c.course_category', 'left');
$this->db->join('academic_categories ac', 'ac.category_id = c.academic_category', 'left');
$this->db->join('exams e', 'FIND_IN_SET(e.id, cc.entrance_exams)', 'left'); // Match multiple IDs
$this->db->where('cc.collegeid', $collegeId);
$this->db->where('c.id IS NOT NULL', null, false); // Raw condition
$this->db->group_by('c.id');
$query = $this->db->get();
//$result = $query->result_array();
return $query->result_array();

	}*/

    public function getCoursesAndFeesOfClg($collegeId)
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
            'cf.fees as counseling_fees',
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
        $this->db->join('counseling_fees cf', 'cc.categoryid = cf.sub_category', 'left');
        $this->db->join('category ct', 'ct.id = c.course_category', 'left');
        $this->db->join('academic_categories ac', 'ac.category_id = c.academic_category', 'left');
        $this->db->join('exams e', 'FIND_IN_SET(e.id, cc.entrance_exams)', 'left');

        $this->db->where('cc.collegeid', $collegeId);
        $this->db->where('c.id IS NOT NULL', null, false);
        $this->db->group_by('c.id');

        $query = $this->db->get();
        //  echo $this->db->last_query();exit;
        return $query->result_array();
    }


    public function getfeesstructure($courseId, $collegeId)
    {
        $this->db->select('fs.amount');
        $this->db->from('fee_structure fs');
        $this->db->where('fs.college_id', $collegeId);
        $this->db->where('fs.course_id', $courseId);
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

    //IF(cc.eligibility IS NULL OR cc.eligibility = "", sc.eligibility, cc.eligibility) AS eligibility, 
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
        // print_r();exit;
        // First Query: Fetching 'categoryid' for a specific college by its ID
        $this->db->select('categoryid');
        $this->db->from('college');
        $this->db->where('id', $collegeId);
        $query = $this->db->get();
        $result1 = $query->row(); // This returns a single row, not an array
        //print_r($result1);exit;
        // Check if categoryid exists and assign it to $categoryIds
        $categoryIds = !empty($result1) ? $result1->categoryid : '';

        // Second Query: Fetching all columns based on cityid and categoryid from the first query
        $this->db->select('*');
        $this->db->from('college');
        $this->db->where('cityid', $cityid);

        // Use FIND_IN_SET only if there are categories available
        if (!empty($categoryIds)) {
            $this->db->where("FIND_IN_SET(categoryid, '$categoryIds') > 0", null, false);
        }

        $this->db->limit(10); // Limit the results to 10

        $query = $this->db->get();
        $result2 = $query->result_array(); // Fetch the results

        return $result2; // Return the results

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
        if ($search_college !== NULL) {
            $this->db->like('title', $search_college);
        }
        // $this->db->where('c.is_trending', 1);
        // $this->db->group_by('c.id, c.cityid, c.title, c.what_new, c.categoryid, c.description, c.accreditation, c.package_type, c.logo, c.banner, c.estd, ci.city, co.country, g.image, ct.name');
        $this->db->limit(10);

        $query = $this->db->get();
        // echo $this->db->last_query();exit;
        $result = $query->result();
        return $result;
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
        $this->db->where('c.is_deleted', 0);
        $this->db->where('c.status', 1);
        $this->db->where('g.type', 'college');
        $category_ids = explode(',', $categoryid); // Convert string to array
        $category_ids = array_map('intval', $category_ids); // Convert array elements to integers
        $this->db->where_in('c.categoryid', $category_ids);

        $this->db->where('c.is_trending', 1);
        $this->db->group_by('c.id');
        $this->db->limit(10);

        $query = $this->db->get();
        //echo $this->db->last_query();exit;
        $result = $query->result_array();
        return $result;
    }




    public function getPopularClgByLocationbkp($cityid, $catid, $categoryid)
    {
        // print_r($catid);exit;

        $this->db->select('c.id as collegeid, c.title, c.address, c.accreditation, g.image,c.cityid, ci.city as cityname, cr.category_id, rc.title as categoryName, cr.rank, cr.year');
        $this->db->from('college c');
        $this->db->join('city ci', 'ci.id = c.cityid', 'left');
        $this->db->join('college_ranks cr', 'cr.college_id = c.id', 'left');
        $this->db->join('rank_categories rc', 'rc.category_id = cr.category_id', 'left');
        $this->db->join('gallery g', 'g.postid = c.id', 'left');


        // if(!empty($catid)){
        //    // print_r("ttt");exit;
        //       $this->db->where_in('c.categoryid', $catid);
        // }
        if (!empty($categoryid)) {
            // print_r("ttt");exit;
            $this->db->where_in('c.categoryid', $catid);
        }

        $this->db->where('c.is_deleted', 0);
        $this->db->where('c.status', 1);

        $this->db->where('c.package_type', 'featured_listing');
        $this->db->or_where('c.package_type', 'feature_listing');
        $this->db->where('c.cityid', $cityid);
        $this->db->group_by('c.id');
        $this->db->order_by('RAND()');
        $this->db->order_by('c.create_date', 'desc');
        $this->db->limit(10);

        $query = $this->db->get();
        // echo $this->db->last_query();      exit;
        return $query->result();
    }

    public function getPopularClgByLocation($cityid, $catid, $categoryid)
{
    $this->db->select('
        c.id AS collegeid,
        c.title,
        c.address,
        c.accreditation,
        c.cityid,
        ci.city AS cityname,
        cr.category_id,
        rc.title AS categoryName,
        cr.rank,
        cr.year,
        IF(g.image IS NULL, "N/A", g.image) AS image
    ');

    $this->db->from('college c');

    $this->db->join('city ci', 'ci.id = c.cityid', 'LEFT');
    $this->db->join('college_ranks cr', 'cr.college_id = c.id', 'LEFT');
    $this->db->join('rank_categories rc', 'rc.category_id = cr.category_id', 'LEFT');

    // ✅ IMPORTANT: gallery condition inside JOIN
    $this->db->join(
        'gallery g',
        "g.postid = c.id AND g.type = 'college'",
        'LEFT'
    );

    if (!empty($categoryid)) {
        $this->db->where_in('c.categoryid', $catid);
    }

    $this->db->where('c.is_deleted', 0);
    $this->db->where('c.status', 1);

    // ✅ Proper OR grouping
    $this->db->group_start();
        $this->db->where('c.package_type', 'featured_listing');
        $this->db->or_where('c.package_type', 'feature_listing');
    $this->db->group_end();

    $this->db->where('c.cityid', $cityid);

    $this->db->group_by('c.id');

    $this->db->order_by('RAND()');
    $this->db->order_by('c.create_date', 'DESC');
    $this->db->limit(10);

    return $this->db->get()->result();
}

    public function getPopularClgByLoc_cat($cityid, $catid)
    {

        $number = (int)$catid;
        //   print_r($number);exit;
        // $categoryId = $catid[0];
        $this->db->select('c.id as collegeid, c.title, c.address, c.accreditation, g.image,c.cityid, ci.city as cityname, cr.category_id, rc.title as categoryName, cr.rank, cr.year');
        $this->db->from('college c');
        $this->db->join('city ci', 'ci.id = c.cityid', 'left');
        $this->db->join('college_ranks cr', 'cr.college_id = c.id', 'left');
        $this->db->join('rank_categories rc', 'rc.category_id = cr.category_id', 'left');
        $this->db->join('gallery g', 'g.postid = c.id', 'left');
        $this->db->where('c.cityid', $cityid);

        if (!empty($catid)) {
            // print_r("ttt");exit;
            $this->db->where_in('c.categoryid', $number);
        }


        $this->db->where('c.is_deleted', 0);
        $this->db->where('c.status', 1);

        // $this->db->where('c.package_type', 'featured_listing');
        // $this->db->or_where('c.package_type', 'feature_listing');

        $this->db->group_by('c.id');
        $this->db->order_by('RAND()');
        $this->db->order_by('c.create_date', 'desc');
        $this->db->limit(10);

        $query = $this->db->get();
        // echo $this->db->last_query();      exit;
        return $query->result();
    }

    public function getCollegesAccordingCategory($collegeId, $categories)
    {
        // Explode categories string into an array
        $categoriesArray = explode(',', $categories);

        // Select required fields and aggregate category names using GROUP_CONCAT
        $this->db->select('c.id, c.cityid, c.title, c.what_new, c.categoryid, c.description, c.accreditation, c.package_type, c.logo, GROUP_CONCAT(ca.catname) AS catname, c.banner, c.estd, g.image');
        $this->db->from('college c');
        $this->db->join('category ca', 'FIND_IN_SET(ca.id, c.categoryid)');
        $this->db->join('gallery g', 'g.postid = c.id', 'left');

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
        // echo $this->db->last_query();      exit;
        $result = $query->result_array();

        return $result;
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

    public function getHostelDetailsById($collegeId)
    {
        $this->db->select('hp.*, c.title as college_name');
        $this->db->from('hostelpg hp');
        $this->db->join('college c', 'hp.collegeid = c.id');
        $this->db->where('hp.collegeid', $collegeId);
        $query = $this->db->get();
        return $query->row();
    }

    public function savehostelinquiry($insertData)
    {
        //echo "ttt";exit;
        $this->db->insert('inquiry', $insertData);
        return $this->db->insert_id();
    }

    public function getYearOfCollege()
    {
        $this->db->select('year');
        $this->db->from('academic_year');
        $this->db->group_by('year');

        $this->db->where('year !=', '10');
        $this->db->where('year !=', '100');
        $query = $this->db->get();
        return $query->result_array();
    }
}
