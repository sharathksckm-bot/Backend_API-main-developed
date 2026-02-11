<?php

class Courses_model extends CI_Model
{
    private $table = 'courses';

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
    public function getCoursesById($Id)
    {
        $this->db->select('c.*');
        $this->db->from('courses c');
        // $this->db->join('academic_categories ac', 'ac.category_id = c.academic_category', 'left');
        $this->db->where('c.id', $Id);
        // $this->db->limit(8);
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

    public function getCourseByCategoryClg($categoryId, $collegeId)
    {
        //print_r("testing...");exit;
        $this->db->select('cc.*, c.id, c.name');
        $this->db->from('college_course cc');
        $this->db->join('courses c', 'cc.courseid = c.id', 'left');
        if (!empty($collegeId)) {
            $this->db->where('cc.collegeid', $collegeId);
        }
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

    public function getCoursesOfCollege($collegeId, $subcategory = NULL, $courselevel = NULL, $total_fees = NULL, $exam_accepted = NULL, $CourseName = NULL)
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
        } else {
            $this->db->select('cc.courseid, c.name, c.duration, cc.total_fees, cc.median_salary,cc.total_intake, cc.entrance_exams');
            $this->db->select('(SELECT GROUP_CONCAT(title) FROM exams WHERE FIND_IN_SET(id, cc.entrance_exams)) AS examNames');
            $this->db->from('college_course cc');
            $this->db->join('courses c', 'c.id = cc.courseid', 'LEFT');
            $this->db->where('cc.collegeid', $collegeId);
            if (!empty($subcategory)) {
                $this->db->where('c.sub_category', $subcategory);

                //$this->db->where('c.sub_category !=', $subcategory);
            }
            if (!empty($courselevel)) {
                $this->db->where('c.academic_category', $courselevel);
            }
            if (!empty($exam_accepted)) {
                $this->db->where("FIND_IN_SET('$exam_accepted', cc.entrance_exams) > 0");
            }
            if (!empty($CourseName)) {
                $this->db->like('c.name', $CourseName);
            }
            $query = $this->db->get();
            //echo $this->db->last_query();exit;
        }
        $result = $query->result_array();
        return $result;
    }

    public function getCoursesBySubcategorybkp($collegeId, $subcategory, $categoryId, $collegetype)
    {
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

        $this->db->select('cc.courseid, c.name, c.duration,COALESCE(cc.total_fees, cf.fees) AS total_fees,cc.total_intake, cc.median_salary as sal, cc.entrance_exams,cf.category');
        $this->db->select('(SELECT GROUP_CONCAT(title) FROM exams WHERE FIND_IN_SET(id, cc.entrance_exams)) AS examNames');
        $this->db->from('college_course cc');
        $this->db->join('courses c', 'c.id = cc.courseid', 'LEFT');
        $this->db->join('counseling_fees cf', 'cc.categoryid = cf.sub_category', 'left');
        $this->db->join('college cl', 'cl.id = cc.collegeid', 'LEFT');

        $this->db->where('cc.collegeid', $collegeId);
        $this->db->where('c.sub_category', $subcategory);
        //      if (!empty($collegetypeid)) {
        //     $this->db->join('college_type ct', 'ct.id = cl.collegetypeid', 'LEFT');
        //     $this->db->where('ct.name', $collegetypeid);
        // }
        if (!empty($categoryId)) {
            if (!is_array($categoryId)) {
                $categoryId = explode(',', $categoryId);
            }
            $this->db->where_in('cf.category', $categoryId);
        }

        if (!empty($typeid)) {
            $this->db->where('cf.college_type', $typeid);
        }
        $this->db->group_by('cc.courseid');

        $query = $this->db->get();
        // print_r($query);exit;

        $result = $query->result();
        // print_r($result);exit;
        // echo $this->db->last_query();exit;
        return $result;
    }

    public function getCoursesBySubcategory($collegeId, $subcategory, $categoryId, $collegeTypeId)
    {
        // Normalize filters once (avoid mutating inside the per-course loop)
        $categoryIds = [];
        if ($categoryId !== '' && $categoryId !== null) {
            $categoryIds = is_array($categoryId) ? $categoryId : explode(',', (string) $categoryId);
            $categoryIds = array_values(array_filter(array_map('trim', $categoryIds), 'strlen'));
        }

        // `collegeTypeId` is sometimes sent as a *name* (e.g. "Private Unaided") instead of numeric id.
        // Normalize to numeric id when possible, since `counseling_fees.college_type` is typically an int FK.
        $collegeTypeIdInt = null;
        if ($collegeTypeId !== '' && $collegeTypeId !== null) {
            if (is_numeric($collegeTypeId)) {
                $collegeTypeIdInt = (int) $collegeTypeId;
            } else {
                $typeRow = $this->db
                    ->select('id')
                    ->from('college_type')
                    ->where('name', (string) $collegeTypeId)
                    ->get()
                    ->row_array();
                if (!empty($typeRow['id'])) {
                    $collegeTypeIdInt = (int) $typeRow['id'];
                }
            }
        }

        // Prefer using the DB view if it exists.
        // Note: views in MySQL are returned by SHOW TABLES, so `table_exists` typically works for views too.
        if ($this->db->table_exists('vw_college_course_effective_fees')) {
            $this->db->select('v.courseid, v.name, v.duration, v.effective_total_fees AS total_fees, v.median_salary, v.entrance_exams, v.examNames');
            $this->db->from('vw_college_course_effective_fees v');
            $this->db->where('v.collegeid', $collegeId);
            $this->db->where('v.sub_category', $subcategory);

            // Keep course fees when explicitly present; else allow entrance-exam fee fallback (if available)
            // while respecting category + college type filters.
            $this->db->group_start();
            $this->db->where("(v.course_total_fees IS NOT NULL AND TRIM(v.course_total_fees) <> '')", null, false);
            $this->db->or_group_start();
            $this->db->where("(v.entrance_exam_fee IS NOT NULL AND TRIM(v.entrance_exam_fee) <> '')", null, false);
            if (!empty($categoryIds)) {
                $this->db->where_in('v.counseling_category', $categoryIds);
            }
            if ($collegeTypeIdInt !== null) {
                $this->db->where('v.counseling_college_type', $collegeTypeIdInt);
            }
            $this->db->group_end();
            $this->db->group_end();

            $this->db->order_by('v.name', 'ASC');
            return $this->db->get()->result_array();
        }

        /* -------------------------
         * STEP 1: Fetch Courses
         * ------------------------- */
        $this->db->select('
        cc.courseid,
        c.name,
        c.duration,
        cc.total_fees,
        cc.median_salary,
        cc.entrance_exams,
        IF(
        cc.entrance_exams IS NULL OR cc.entrance_exams = "",
        "N/A",
        (SELECT GROUP_CONCAT(ex.title)
         FROM exams ex
         WHERE FIND_IN_SET(ex.id, cc.entrance_exams)
        )
    ) AS examNames
    ');
        $this->db->from('college_course cc');
        $this->db->join('courses c', 'c.id = cc.courseid', 'LEFT');
        $this->db->where('cc.collegeid', $collegeId);
        $this->db->where('c.sub_category', $subcategory);
        $this->db->where('cc.is_deleted', 0);
        $this->db->group_by('cc.courseid');

        $courses = $this->db->get()->result_array();

        if (empty($courses)) {
            return [];
        }

        /* -------------------------
         * STEP 2: Fetch Counseling Fees
         * ------------------------- */
        foreach ($courses as &$row) {

            // If fees already present in college_course, prefer it.
            // Note: don't use `empty()` because "0" would be treated as empty.
            if ($row['total_fees'] !== null && trim((string) $row['total_fees']) !== '') {
                continue;
            }

            // Requirement: if course fee is not available, show entrance-exam fee
            // only when an entrance exam is linked to the college/course.
            $entranceExams = isset($row['entrance_exams']) ? trim((string) $row['entrance_exams']) : '';
            $entranceExamsClean = preg_replace('/[^0-9,]/', '', $entranceExams);
            if ($entranceExamsClean === '') {
                $row['total_fees'] = null;
                continue;
            }

            $this->db->select('cf.fees');
            $this->db->from('counseling_fees cf');
            $this->db->where('cf.sub_category', $subcategory);
            $this->db->where('cf.fees IS NOT NULL', null, false);
            $this->db->where('cf.fees !=', '');
            $this->db->where(
                'FIND_IN_SET(cf.exam_id, ' . $this->db->escape($entranceExamsClean) . ') > 0',
                null,
                false
            );

            // Category filter
            if (!empty($categoryIds)) {
                $this->db->where_in('cf.category', $categoryIds);
            }

            // College Type filter
            if ($collegeTypeIdInt !== null) {
                $this->db->where('cf.college_type', $collegeTypeIdInt);
            }

            // If multiple exams match, pick the lowest numeric fee.
            $this->db->order_by('CAST(cf.fees AS UNSIGNED)', 'ASC', false);
            $this->db->limit(1);

            $feeRow = $this->db->get()->row_array();

            // Assign entrance-exam fee (or NULL if not found)
            $row['total_fees'] = (!empty($feeRow) && isset($feeRow['fees']) && trim((string) $feeRow['fees']) !== '')
                ? $feeRow['fees']
                : null;
        }

        return $courses;
    }


    public function getOtherCollegesOfferingSameCourseInSameCity($cityId, $collegeId, $subcat)
    {
        //$subcatArray = explode(',', $subcat);
        //print_r($subcatArray);exit;
        $this->db->select('cc.id,cc.title');
        $this->db->from('college cc');
        $this->db->join('college_course c', 'c.collegeid = cc.id', 'LEFT');

        $this->db->where('cc.cityid', $cityId);
        $this->db->where('cc.id !=', $collegeId);

        $this->db->where_in('c.categoryid ', $subcat);
        //$this->db->where('package_type', 'featured_listing');

        $this->db->order_by('cc.create_date', 'DESC');
        //$this->db->limit(20);
        $this->db->group_by('cc.id');


        $query = $this->db->get();
        $result = $query->result();
        //echo $this->db->last_query();exit;
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
        $this->db->where('status', 1);
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

    public function getFilteredCourse($search, $cat)
    {
        $this->db->select("c.*,ac.name as type,ca.catname as category,sc.name as subcategory");
        $this->db->from($this->table . " c");
        $this->db->join("academic_categories ac", "ac.category_id = c.academic_category", "left");
        $this->db->join("category ca", "ca.id = c.course_category", "left");
        $this->db->join("sub_category sc", "sc.id = c.sub_category", "left");
        if (!empty($cat)) {
            $this->db->where('sc.id', $cat);
        }
        $this->db->group_start();
        $this->db->like('c.name', $search);
        // $this->db->or_like('ac.name', $search);
        // $this->db->or_like('ca.catname', $search);

        $this->db->order_by('c.name', 'ASC');
        // 		 $this->db->order_by('ca.catname', 'ASC');

        $this->db->group_end();
        //	$this->db->order_by($order, $dir);
        //	$this->db->limit($limit, $start);
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
        $this->db->from($this->table . " c");
        $this->db->join("academic_categories ac", "ac.category_id = c.academic_category", "left");
        $this->db->join("category ca", "ca.id = c.course_category", "left");
        $this->db->order_by('c.name', 'ASC');
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

    public function getTrendingCoursesList($categoryId, $ac_id)
    {
        $this->db->select("c.id, c.name, c.image, ac.name as type, ca.catname as category");
        $this->db->from($this->table . " c");
        $this->db->join("academic_categories ac", "ac.category_id = c.academic_category", "left");
        $this->db->join("category ca", "ca.id = c.course_category", "left");
        $this->db->where('c.course_category', $categoryId);

        if (!empty($ac_id)) {
            $this->db->where('ac.category_id', $ac_id);
        }

        $this->db->order_by('c.views', 'DESC');
        $this->db->limit(20);

        $query = $this->db->get();
        // echo $this->db->last_query();exit;
        $result = $query->result();
        return $result;
    }

    public function getTrendingCoursesDetailsById($courseId)
    {
        $this->db->select("c.*,ac.name as type,ca.catname as category");
        $this->db->from($this->table . " c");
        $this->db->join("academic_categories ac", "ac.category_id = c.academic_category", "left");
        $this->db->join("category ca", "ca.id = c.course_category", "left");
        $this->db->where('c.id', $courseId);

        return $this->db->get()->result();
    }

    public function getCoursesInfo1($collegeId, $courseId, $typeid = '', $subcat = '')
    {
        $sub_category = $this->db
            ->select('sub_category')
            ->from('courses')
            ->where('id', $courseId)
            ->get()
            ->row('sub_category');

        $entrance_exam = $this->db
            ->select('entrance_exams')
            ->from('college_course')
            ->where('courseid', $courseId)
            ->get()
            ->row('entrance_exams');

        //   $this->db->select('c.id,c.academic_category as levelid, c.name, c.course_description, cc.total_fees, cc.total_intake, cc.median_salary, cc.rank, c.duration, ac.name as level, cc.website, cc.eligibility, ct.name as college_type');

        $this->db->select(
            'c.id,
     c.academic_category AS levelid,
     c.name,
     c.course_description,
     cc.total_fees,
     cc.total_intake,
     cc.median_salary,
     cc.rank,
     c.duration,
     ac.name AS level,
     cc.website,
     COALESCE(cc.eligibility, c.eligibility, "") AS eligibility,
     ct.name AS college_type',
            false
        );

        $this->db->from('courses c');
        $this->db->join('college_course cc', 'cc.courseid = c.id', 'left');
        $this->db->join('college cl', 'cl.id = cc.collegeid', 'left');
        $this->db->join('college_type ct', 'ct.id = cl.college_typeid', 'left');
        $this->db->join('academic_categories ac', 'ac.category_id = c.academic_category', 'left');

        if (!empty($courseId)) {
            $this->db->where('c.id', $courseId);
        }
        $this->db->where('cc.collegeid', $collegeId);
        $query = $this->db->get();
        //echo $this->db->last_query();exit;
        $result = $query->result();

        if (empty($result[0]->total_fees)) {
            $this->db->select('course_category,sub_category');
            $this->db->from('courses c');
            $this->db->where('c.id', $courseId);
            $query1 = $this->db->get();
            $result1 = $query1->result();
            //print_r($result1);exit;
            $this->db->select('
                c.id, 
                c.scope,
                c.job_profile,
                c.certification,
                c.academic_category AS levelid, 
                c.name, 
                c.course_description, 
                COALESCE(cc.total_fees, cf.fees) AS total_fees,
                cc.total_intake, 
                cc.median_salary, 
                cc.rank, 
                c.duration, 
                ac.name AS level, 
                cc.website, 
                cc.eligibility, 
                ct.name AS college_type, 
                cc.entrance_exams, 
                c.course_category
            ');
            $this->db->from('courses c');
            $this->db->join('college_course cc', 'cc.courseid = c.id', 'left');
            $this->db->join('college cl', 'cl.id = cc.collegeid', 'left');
            $this->db->join('college_type ct', 'ct.id = cl.college_typeid', 'left');
            $this->db->join('academic_categories ac', 'ac.category_id = c.academic_category', 'left');
            $this->db->join('counseling_fees cf', 'cf.college_type = cl.college_typeid', 'left');
            //$this->db->join('counseling_fees cf1', 'cf1.category = c.course_category', 'left');

            $this->db->where('c.id', $courseId);

            $this->db->where('cc.collegeid', $collegeId);
            if ($typeid != '') {
                $this->db->where('cf.college_type', $typeid);
            }
            if ($subcat != '') {
                $this->db->where('cf.sub_category', $subcat);
            }
            //$this->db->where('cf.category',  $result1[0]->course_category);
            //$this->db->where('cf.sub_category',  $result1[0]->sub_category);
            //echo $this->db->last_query();exit;
            // $this->db->where('FIND_IN_SET(cf.exam_id, cc.entrance_exams) > 0', NULL, FALSE); // Using FIND_IN_SET

            $this->db->where('cf.sub_category', $sub_category);
            // $this->db->where_in('cf.exam_id', $entrance_exam);

            $this->db->group_by('c.id, cc.collegeid');

            // Execute the query
            $query = $this->db->get();
            // echo $this->db->last_query();exit;
            $result = $query->result();
            // print_r($result);exit;
            // echo $this->db->last_query();
            // exit;
        }
        // echo $this->db->last_query();exit;

        return $result;
    }

    public function getCoursesInfo($collegeId, $courseId, $typeid = '', $subcat = '')
    {
        // Get course sub_category (used for fallback counseling fees filter)
        $courseSubCat = $this->db->select('sub_category')
            ->from('courses')
            ->where('id', $courseId)
            ->get()
            ->row('sub_category');

        // Final subcat rule: if passed in param use it else use courseSubCat
        $finalSubCat = ($subcat !== '') ? $subcat : $courseSubCat;

        // -----------------------------
        // 1) Primary query (college_course fees)
        // -----------------------------
        $this->db->select("
        c.id,
        c.academic_category AS levelid,
        c.name,
        c.course_description,
        cc.total_fees,
        cc.total_intake,
        cc.median_salary,
        cc.rank,
        c.duration,
        ac.name AS level,
        cc.website,
        COALESCE(cc.eligibility, c.eligibility, '') AS eligibility,
        ct.name AS college_type
    ", false);

        $this->db->from('courses c');
        $this->db->join('college_course cc', 'cc.courseid = c.id', 'left');
        $this->db->join('college cl', 'cl.id = cc.collegeid', 'left');
        $this->db->join('college_type ct', 'ct.id = cl.college_typeid', 'left');
        $this->db->join('academic_categories ac', 'ac.category_id = c.academic_category', 'left');

        $this->db->where('c.id', $courseId);
        $this->db->where('cc.collegeid', $collegeId);

        $result = $this->db->get()->result();

        // If no record found, return empty array
        if (empty($result)) {
            return [];
        }

        // If total_fees present, return primary result
        $totalFees = $result[0]->total_fees ?? null;
        if (!empty($totalFees) && $totalFees != 0) {
            return $result;
        }

        // -----------------------------
        // 2) Fallback query (counseling_fees)
        // -----------------------------

        // Build JOIN condition for counseling_fees (so LEFT JOIN remains LEFT JOIN)
        $cfJoin = "cf.college_type = cl.college_typeid";

        if ($typeid !== '') {
            $cfJoin .= " AND cf.college_type = " . (int) $typeid;
        }

        if (!empty($finalSubCat)) {
            $cfJoin .= " AND cf.sub_category = " . (int) $finalSubCat;
        }

        $this->db->select("
        c.id,
        c.scope,
        c.job_profile,
        c.certification,
        c.academic_category AS levelid,
        c.name,
        c.course_description,
        COALESCE(cc.total_fees, cf.fees) AS total_fees,
        cc.total_intake,
        cc.median_salary,
        cc.rank,
        c.duration,
        ac.name AS level,
        cc.website,
        COALESCE(cc.eligibility, c.eligibility, '') AS eligibility,
        ct.name AS college_type,
        cc.entrance_exams,
        c.course_category
    ", false);

        $this->db->from('courses c');
        $this->db->join('college_course cc', 'cc.courseid = c.id', 'left');
        $this->db->join('college cl', 'cl.id = cc.collegeid', 'left');
        $this->db->join('college_type ct', 'ct.id = cl.college_typeid', 'left');
        $this->db->join('academic_categories ac', 'ac.category_id = c.academic_category', 'left');
        $this->db->join('counseling_fees cf', $cfJoin, 'left', false);

        $this->db->where('c.id', $courseId);
        $this->db->where('cc.collegeid', $collegeId);

        $this->db->group_by('c.id, cc.collegeid');

        return $this->db->get()->result();
    }

    public function getCoursesAdmissionProcess($collegeId, $courseid)
    {
        $this->db->select('COUNT(cc.courseid) AS courseCount, cc.entrance_exams, GROUP_CONCAT(e.title) AS Accepting_Exams, IF(cc.eligibility IS NULL OR cc.eligibility = "", sc.eligibility, cc.eligibility) AS eligibility, c.sub_category, sc.name as subCatName, c.duration');
        $this->db->from('college_course cc');
        $this->db->join('courses c', 'c.id = cc.courseid', 'left');
        $this->db->join('sub_category sc', 'sc.id = c.sub_category', 'left');
        $this->db->join('exams e', 'FIND_IN_SET(e.id, cc.entrance_exams) > 0', 'left');
        $this->db->where('cc.collegeid', $collegeId);
        $this->db->where('cc.courseid', $courseid);
        $this->db->where('c.sub_category IS NOT NULL');
        $this->db->group_by('sc.id');
        $query = $this->db->get();
        //echo $this->db->last_query();exit;
        $result = $query->result();
        return $result;
    }
    public function getCoursesfeeStructure($collegeId, $courseid)
    {
        $query = $this->db->select('f.details as feecomponent,f.amount')
            ->from('fee_structure f')
            ->where('college_id', $collegeId)
            ->where('course_id', $courseid)
            ->get();

        $result = $query->result();
        return $result;
    }

    public function getSubCategoryForClg($collegeId)
    {
        $this->db->select('categoryid');
        $this->db->from('college_course');
        $this->db->where('collegeid', $collegeId);
        $this->db->where('categoryid IS NOT NULL');

        $this->db->group_by('categoryid');
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }
    //   public function getEntranceExamsForCourse($collegeId,$courseid)
    // {
    //    // echo "tttt";exit;
    //     $this->db->select('CASE WHEN FIND_IN_SET(e.id, cc.entrance_exams) THEN e.title ELSE NULL END AS exam_name,e.id, e.description, e.criteria');
    //     $this->db->from('(SELECT entrance_exams FROM college_course WHERE collegeid = '.$collegeId.' AND courseid = '.$courseid.') AS cc');
    //     $this->db->join('exams AS e', 'FIND_IN_SET(e.id, cc.entrance_exams)', 'left');
    //     $query = $this->db->get();
    //     //echo $this->db->last_query();exit;
    //     $result = $query->result();
    //     return $result;

    // }

    public function getEntranceExamsForCourse($collegeId, $courseid)
    {
        $this->db->select("CASE WHEN FIND_IN_SET(e.id, cc.entrance_exams) THEN e.title ELSE NULL END AS exam_name, e.id, e.description, e.criteria");
        $subquery = 'SELECT entrance_exams FROM college_course WHERE collegeid = ' . (int) $collegeId;
        if (!empty($courseid)) {
            $subquery .= ' AND courseid = ' . (int) $courseid;
        }
        $this->db->from('(' . $subquery . ') AS cc');
        $this->db->join('exams AS e', 'FIND_IN_SET(e.id, cc.entrance_exams)', 'left');

        $query = $this->db->get();
        return $query->result();
    }
}
