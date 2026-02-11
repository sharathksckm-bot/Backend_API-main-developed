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
        $this->db->select("id,name,sub_category");
        $this->db->from("courses");

        if ($search_term) {
            $this->db->like("name", $search_term);
        }
        $this->db->limit(10);

        $query = $this->db->get()->result_array();

        return $query;
    }

    public function increment_view($id)
    {
        // Ensure $id is sanitized and valid
        if (empty($id) || !is_numeric($id)) {
            return false; // Early return for invalid input
        }

        // Increment the views column in the courses table
        $this->db->set('views', 'views+1', FALSE); // FALSE allows for SQL literal
        $this->db->where('id', $id);
        $result = $this->db->update('courses');

        // Return the success or failure of the query
        return $result;
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
        $this->db->select('c.course_category,c.id,c.duration,c.image,c.name,c.sub_category,c.academic_category,ac.name as courseLevel');
        $this->db->from('courses c');
        $this->db->join('academic_categories ac', 'ac.category_id = c.academic_category', 'left');
        $this->db->where('c.course_category', $CatId);
        $this->db->where('c.view_in_menu', 1);

        //$this->db->limit(8);
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
    public function checkCourse($coursesCategoryIds, $collegeId)
    {
        $this->db->select('ay.course_category, pc.name as categoryName');
        $this->db->from('academic_year ay');
        $this->db->join('placement_category pc', 'pc.id = ay.course_category', 'left');
        $this->db->where('collegeid', $collegeId);
        $this->db->where_in('ay.course_category', $coursesCategoryIds);
        $query = $this->db->get();
        return $query->result();
    }


    public function getCourseByCategory($categoryId, $search)
    {
        $this->db->select('id,name');
        $this->db->from('courses');
        $this->db->where('academic_category', $categoryId);
        $this->db->like('name', $search);
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }

    public function getCourseByCategoryClg($categoryId, $collegeId)
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

    public function getCoursesOfCollegebkp($collegeId, $subcategory = NULL, $courselevel = NULL, $total_fees = NULL, $exam_accepted = NULL, $CourseName = NULL, $categoryId = NULL, $collegeTypeId = NULL)
    {
        //    print_r($CourseName);exit;
        $catid = array_map('intval', explode(',', $categoryId));
        //print_r($catid);exit;
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
            $sql .= "order by c.name asc";
            $query = $this->db->query($sql, array($collegeId, $total_fees, $total_fees, $total_fees, $total_fees, $total_fees, $total_fees, $total_fees, $total_fees));
        } else {

            $catid = !empty($categoryId) ? implode(',', array_map('intval', explode(',', $categoryId))) : '';
            $collegeIdInt = intval($collegeId);
            $collegeTypeIdInt = (!empty($collegeTypeId) && is_numeric($collegeTypeId)) ? intval($collegeTypeId) : '';
            $this->db->select("
            c.sub_category,
                cc.courseid, 
                ce.id, 
                ce.college_typeid, 
                ce.categoryid, 
                c.name, 
                c.duration,
                cc.total_fees,
                cf.fees as counselling_fees,
                cc.median_salary, 
                cc.total_intake, 
                cc.level,
                cc.entrance_exams,
                (SELECT GROUP_CONCAT(ex.title) 
                FROM exams ex 
                WHERE FIND_IN_SET(ex.id, cc.entrance_exams)) AS examNames
            ");
            $this->db->from('college_course cc');
            $this->db->join('courses c', 'c.id = cc.courseid', 'left');
            $this->db->join('college ce', 'ce.id = cc.collegeid', 'left');

            $this->db->join('counseling_fees cf', 'cf.sub_category = cc.categoryid', 'left');
            if (!empty($CourseName)) {
                $this->db->like('c.name', $CourseName);
            }
            if (!empty($categoryId)) {
                if (!is_array($categoryId)) {
                    $categoryId = explode(',', $categoryId);
                }
                $this->db->where_in('cf.category', $categoryId);
            }
            if (!empty($subcategory)) {
                if (is_array($subcategory)) {
                    $this->db->where_in('c.sub_category', $subcategory);
                } else {
                    $this->db->where('c.sub_category', $subcategory);
                }
            }
            if ($collegeTypeIdInt != '') {
                $this->db->where(
                    "(cf.college_type IS NULL OR cf.college_type = " . (int)$collegeTypeIdInt . ")",
                    null,
                    false
                );
            }
            if (!empty($exam_accepted)) {
                $this->db->where("FIND_IN_SET('$exam_accepted', cc.entrance_exams) > 0");
            }
            if (!empty($courselevel)) {
                $this->db->where('c.academic_category', $courselevel);
            }


            $this->db->where('cc.collegeid', $collegeIdInt);
            $this->db->where('cc.is_deleted', 0);
            $this->db->group_by('cc.courseid');
            $this->db->order_by('c.name', 'asc');
            $query = $this->db->get();
            // echo $this->db->last_query(); exit;
            $result = $query->result();

            return $result;
        }
    }

    public function getCoursesOfCollege(
        $collegeId,
        $subcategory = NULL,
        $courselevel = NULL,
        $total_fees = NULL,
        $exam_accepted = NULL,
        $CourseName = NULL,
        $categoryId = NULL,
        $collegeTypeId = NULL
    ) {

        $collegeIdInt = (int) $collegeId;

        /* ======================================================
     * PART 1 : WHEN TOTAL FEES FILTER IS APPLIED
     * (college_course + counseling_fees)
     * ====================================================== */
        if (!empty($total_fees)) {

            // -------- Parse fee filter in PHP --------
            $minFee = null;
            $maxFee = null;
            $feeType = null;

            if (strpos($total_fees, '-') !== false) {
                [$minFee, $maxFee] = array_map('intval', explode('-', $total_fees));
                $feeType = 'between';
            } elseif (strpos($total_fees, '<') !== false) {
                $maxFee = (int) str_replace('<', '', $total_fees);
                $feeType = 'less';
            } elseif (strpos($total_fees, '>') !== false) {
                $minFee = (int) str_replace('>', '', $total_fees);
                $feeType = 'greater';
            } else {
                $minFee = (int) $total_fees;
                $feeType = 'equal';
            }

            // -------- SQL --------
            $sql = "
            SELECT 
                cc.courseid,
                c.name,
                c.duration,
                cc.total_fees,
                cf.fees AS counselling_fees,
                cc.median_salary,
                cc.total_intake,
                cc.entrance_exams,
                IF(
                    cc.entrance_exams IS NULL OR cc.entrance_exams = '',
                    'N/A',
                    (SELECT GROUP_CONCAT(title)
                     FROM exams
                     WHERE FIND_IN_SET(id, cc.entrance_exams))
                ) AS examNames
            FROM college_course cc
            LEFT JOIN courses c ON c.id = cc.courseid
            LEFT JOIN counseling_fees cf 
                ON cf.sub_category = cc.categoryid
            WHERE cc.collegeid = ?
        ";

            $params = [$collegeIdInt];

            // -------- Fee conditions (college OR counselling) --------
            if ($feeType === 'between') {
                $sql .= "
                AND (
                    CAST(cc.total_fees AS UNSIGNED) BETWEEN ? AND ?
                    OR CAST(cf.fees AS UNSIGNED) BETWEEN ? AND ?
                )";
                array_push($params, $minFee, $maxFee, $minFee, $maxFee);
            } elseif ($feeType === 'less') {
                $sql .= "
                AND (
                    CAST(cc.total_fees AS UNSIGNED) < ?
                    OR CAST(cf.fees AS UNSIGNED) < ?
                )";
                array_push($params, $maxFee, $maxFee);
            } elseif ($feeType === 'greater') {
                $sql .= "
                AND (
                    CAST(cc.total_fees AS UNSIGNED) > ?
                    OR CAST(cf.fees AS UNSIGNED) > ?
                )";
                array_push($params, $minFee, $minFee);
            } elseif ($feeType === 'equal') {
                $sql .= "
                AND (
                    CAST(cc.total_fees AS UNSIGNED) = ?
                    OR CAST(cf.fees AS UNSIGNED) = ?
                )";
                array_push($params, $minFee, $minFee);
            }

            $sql .= " GROUP BY cc.courseid ORDER BY c.name ASC";

            return $this->db->query($sql, $params)->result();
        }

        /* ======================================================
     * PART 2 : NORMAL FLOW (NO FEE FILTER)
     * ====================================================== */

        $collegeTypeIdInt = (!empty($collegeTypeId) && is_numeric($collegeTypeId))
            ? (int) $collegeTypeId
            : '';

            $this->db->select("
            c.sub_category,
            cc.courseid,
            ce.id,
            ce.college_typeid,
            ce.categoryid,
            c.name,
            c.duration,
            cc.total_fees,
            cf.fees AS counselling_fees,
            cc.median_salary,
            cc.total_intake,
            cc.level,
            cc.entrance_exams,
            IF(
                cc.entrance_exams IS NULL OR cc.entrance_exams = '',
                'N/A',
                (SELECT GROUP_CONCAT(ex.title)
                FROM exams ex
                WHERE FIND_IN_SET(ex.id, cc.entrance_exams))
            ) AS examNames
        ");

        $this->db->from('college_course cc');
        $this->db->join('courses c', 'c.id = cc.courseid', 'left');
        $this->db->join('college ce', 'ce.id = cc.collegeid', 'left');
        $this->db->join('counseling_fees cf', 'cf.sub_category = cc.categoryid', 'left');

        if (!empty($CourseName)) {
            $this->db->like('c.name', $CourseName);
        }

        if (!empty($categoryId)) {
            if (!is_array($categoryId)) {
                $categoryId = explode(',', $categoryId);
            }
            $this->db->where_in('cf.category', $categoryId);
        }

        if (!empty($subcategory)) {
            if (is_array($subcategory)) {
                $this->db->where_in('c.sub_category', $subcategory);
            } else {
                $this->db->where('c.sub_category', $subcategory);
            }
        }

        if ($collegeTypeIdInt !== '') {
            $this->db->where(
                "(cf.college_type IS NULL OR cf.college_type = {$collegeTypeIdInt})",
                null,
                false
            );
        }

        if (!empty($exam_accepted)) {
            $this->db->where("FIND_IN_SET('$exam_accepted', cc.entrance_exams) > 0", null, false);
        }

        if (!empty($courselevel)) {
            $this->db->where('c.academic_category', $courselevel);
        }

        $this->db->where('cc.collegeid', $collegeIdInt);
        $this->db->where('cc.is_deleted', 0);
        $this->db->group_by('cc.courseid');
        $this->db->order_by('c.name', 'asc');

        return $this->db->get()->result();
    }


    public function getCoursesOfCollegefilteredtotal_fees($collegeId, $subcategory = NULL, $courselevel = NULL, $total_fees = NULL, $exam_accepted = NULL, $CourseName = NULL, $categoryId = NULL, $collegeTypeId = NULL)
    {

        $catid = array_map('intval', explode(',', $categoryId));

        if (!empty($total_fees)) {
            $catid = !empty($categoryId) ? implode(',', array_map('intval', explode(',', $categoryId))) : '';
            $collegeIdInt = intval($collegeId);
            $collegeTypeIdInt = (!empty($collegeTypeId) && is_numeric($collegeTypeId)) ? intval($collegeTypeId) : '';

            $this->db->select("
          c.sub_category,
              cc.courseid, 
              ce.id, 
              ce.college_typeid, 
              ce.categoryid, 
              c.name, 
              c.duration,
              cc.total_fees,
              cf.fees as counselling_fees,
              cc.median_salary, 
              cc.total_intake, 
              cc.level,
              cc.entrance_exams,
              (SELECT GROUP_CONCAT(ex.title) 
               FROM exams ex 
               WHERE FIND_IN_SET(ex.id, cc.entrance_exams)) AS examNames
          ");
            $this->db->from('college_course cc');
            $this->db->join('courses c', 'c.id = cc.courseid', 'left');
            $this->db->join('college ce', 'ce.id = cc.collegeid', 'left');
            // $this->db->join(
            //     'counseling_fees cf',
            //     "cf.sub_category = cc.categoryid 
            //     AND cf.category IN ($catid) 
            //     AND cf.college_type = $collegeTypeIdInt",
            //     'left'
            // );
            $this->db->join('counseling_fees cf', 'cf.sub_category = cc.categoryid', 'left');
            if ($catid != '') {
                $this->db->where('cf.category', $catid);
            }
            if ($collegeTypeIdInt != '') {
                $this->db->where('cf.college_type', $collegeTypeIdInt);
            }

            $this->db->where_in('cc.total_fees', $total_fees);

            $this->db->where('cc.collegeid', $collegeIdInt);
            $this->db->where('cc.is_deleted', 0);
            $this->db->group_by('cc.courseid');
            $this->db->order_by('c.name', 'asc');
            $query = $this->db->get();
            // echo $this->db->last_query(); exit;
            $result = $query->result();
            return $result;
        } else {

            $catid = !empty($categoryId) ? implode(',', array_map('intval', explode(',', $categoryId))) : '';
            $collegeIdInt = intval($collegeId);
            $collegeTypeIdInt = (!empty($collegeTypeId) && is_numeric($collegeTypeId)) ? intval($collegeTypeId) : '';

            $this->db->select("
            c.sub_category,
                cc.courseid, 
                ce.id, 
                ce.college_typeid, 
                ce.categoryid, 
                c.name, 
                c.duration,
                cc.total_fees,
                cf.fees as counselling_fees,
                cc.median_salary, 
                cc.total_intake, 
                cc.level,
                cc.entrance_exams,
                (SELECT GROUP_CONCAT(ex.title) 
                FROM exams ex 
                WHERE FIND_IN_SET(ex.id, cc.entrance_exams)) AS examNames
            ");
            $this->db->from('college_course cc');
            $this->db->join('courses c', 'c.id = cc.courseid', 'left');
            $this->db->join('college ce', 'ce.id = cc.collegeid', 'left');

            $this->db->join('counseling_fees cf', 'cf.sub_category = cc.categoryid', 'left');
            if ($catid != '') {
                $this->db->where('cf.category', $catid);
            }
            if ($collegeTypeIdInt != '') {
                $this->db->where('cf.college_type', $collegeTypeIdInt);
            }

            $this->db->where('cc.collegeid', $collegeIdInt);
            $this->db->where('cc.is_deleted', 0);
            $this->db->group_by('cc.courseid');
            $this->db->order_by('c.name', 'asc');
            $query = $this->db->get();
            //echo $this->db->last_query(); exit;
            $result = $query->result();



            // Check for null fees and perform second query if necessary
            /*foreach ($result as $row) {
   // echo "ttt";exit;
  
    if ($row['total_fees'] == '' || $row['total_fees'] == null) {
       // echo "tt";exit;
       $this->db->select('cc.courseid, c.name, c.duration, cf.fees AS total_fees, cc.median_salary, cc.total_intake, cc.entrance_exams');
        $this->db->select('(SELECT GROUP_CONCAT(ex.title) FROM exams ex WHERE FIND_IN_SET(ex.id, cc.entrance_exams)) AS examNames');
        $this->db->from('college_course cc');
        
        $this->db->join('counseling_fees cf', 'cf.sub_category = cc.categoryid AND FIND_IN_SET(cc.entrance_exams, cf.exam_id)', 'LEFT');
        
        $this->db->join('college ce', 'ce.id = cc.collegeid AND ce.college_typeid = cf.college_type', 'LEFT');
        $this->db->join('exams ex', 'FIND_IN_SET(cc.entrance_exams, cf.exam_id)', 'LEFT'); // Updated join condition for entrance exams
        $this->db->join('courses c', 'c.id = cc.courseid', 'LEFT');
        $this->db->where('cc.collegeid', $collegeId);
        $this->db->where('cc.is_deleted', 0);
        $this->db->group_by('cc.courseid');
// Add group by clause





        // Apply the same filters to the second query
        if (!empty($subcategory)) {
            $this->db->where('cc.courseid', $subcategory);
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
        return $query->result_array();
    }
    print_r($row['total_fees']);
}*/

            //	echo $this->db->last_query();exit;
            // Return the final result if no further query is executed
            return $result;
        }
    }



    public function getCoursesBySubcategorybkp($collegeId, $subcategory, $categoryId, $collegeTypeId)
    {
        $this->db->select('cc.courseid, c.name, c.duration, cc.total_fees, cc.median_salary, cc.entrance_exams');
        $this->db->select('(SELECT GROUP_CONCAT(title) FROM exams WHERE FIND_IN_SET(id, cc.entrance_exams)) AS examNames');
        $this->db->from('college_course cc');
        $this->db->join('courses c', 'c.id = cc.courseid', 'LEFT');
        $this->db->where('cc.collegeid', $collegeId);
        $this->db->where('c.sub_category', $subcategory);

        $query = $this->db->get();
        $result = $query->result_array();
        foreach ($result as $row) {

            if ($row['total_fees'] == '' || $row['total_fees'] == NULL) {
                // echo"345678";exit;

                $this->db->select('cc.courseid, c.name, c.duration, cf.fees AS total_fees, cc.median_salary, cc.total_intake, cc.entrance_exams,cf.category,cf.college_type');
                $this->db->select('(SELECT GROUP_CONCAT(ex.title) FROM exams ex WHERE FIND_IN_SET(ex.id, cc.entrance_exams)) AS examNames');
                $this->db->from('college_course cc');
                $this->db->join('counseling_fees cf', 'cf.sub_category = cc.categoryid AND FIND_IN_SET(cf.exam_id,cc.entrance_exams)', 'LEFT');
                $this->db->join('college ce', 'ce.id = cc.collegeid', 'LEFT');
                $this->db->join('exams ex', 'FIND_IN_SET(cc.entrance_exams, cf.exam_id)', 'LEFT'); // Updated join condition for entrance exams
                $this->db->join('courses c', 'c.id = cc.courseid', 'LEFT');
                $this->db->where('cc.collegeid', $collegeId);
                $this->db->where('c.sub_category', $subcategory);
                if (!empty($categoryId)) {
                    if (!is_array($categoryId)) {
                        $categoryId = explode(',', $categoryId);
                    }
                    $this->db->where_in('cf.category', $categoryId);
                }

                if ($collegeTypeId != '') {
                    $this->db->where('cf.college_type', $collegeTypeId);
                }

                // if (!empty($categoryId)) {
                //     if (!is_array($categoryId)) {
                //         $categoryId = explode(',', $categoryId);
                //     }

                //     $this->db->group_start();
                //     $this->db->where_in('cf.category', $categoryId);
                //     $this->db->or_where_in('c.course_category', $categoryId);
                //     $this->db->group_end();
                // }

                // // COLLEGE TYPE FILTER ( cf.college_type = ? OR ce.college_typeid = ? )
                // if ($collegeTypeId !== '') {
                //     $this->db->group_start();
                //     $this->db->where('cf.college_type', $collegeTypeId);
                //     $this->db->or_where('ce.college_typeid', $collegeTypeId);
                //     $this->db->group_end();
                // }

                $this->db->where('cc.is_deleted', 0);
                $this->db->group_by('cc.courseid');

                $query = $this->db->get();
                echo $this->db->last_query();
                exit;
                $result = $query->result_array();

                // print_r($result1);exit;
            }
        }
        return $result;
    }

    public function getCoursesBySubcategory($collegeId, $subcategory, $categoryId, $collegeTypeId)
    {
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

            // If fees already present in college_course
            if (!empty($row['total_fees'])) {
                continue;
            }

            $this->db->select('cf.fees');
            $this->db->from('counseling_fees cf');
            $this->db->where('cf.sub_category', $subcategory);
            $this->db->where("FIND_IN_SET(cf.exam_id, '{$row['entrance_exams']}')", null, false);

            // Category filter
            if (!empty($categoryId)) {
                if (!is_array($categoryId)) {
                    $categoryId = explode(',', $categoryId);
                }
                $this->db->where_in('cf.category', $categoryId);
            }

            // College Type filter
            if ($collegeTypeId !== '') {
                $this->db->where('cf.college_type', $collegeTypeId);
            }

            $feeRow = $this->db->get()->row_array();

            // Assign fee or N/A
            $row['total_fees'] = !empty($feeRow['fees']) ? $feeRow['fees'] : NULL;
        }

        return $courses;
    }


    public function getOtherCollegesOfferingSameCourseInSameCity($cityId, $collegeId, $subcatid)
    {   //print_r($subcatid);exit;
        $this->db->select('id, title,categoryid');
        $this->db->from('college');
        $this->db->where('cityid', $cityId);
        $this->db->where('id !=', $collegeId);
        $this->db->where("FIND_IN_SET('$subcatid', categoryid) >", 0);


        $this->db->group_start();
        $this->db->where('package_type', 'featured_listing');
        $this->db->or_where('package_type', 'feature_listing');
        $this->db->or_where('status', 1);

        $this->db->group_end();

        $this->db->order_by('create_date', 'DESC');
        $this->db->limit(20);

        $query = $this->db->get();

        $result = $query->result();

        return $result;
    }

    // public function getFeesDataOfCollege($id)
    // {
    //   $this->db->select('total_fees');
    //   $this->db->from('college_course');
    //   $this->db->where('collegeid', $id); 
    //   $this->db->where('(total_fees IS NOT NULL AND total_fees != "")');
    //   $query1 = $this->db->get();
    //   $result1 = $query1->result_array();

    //   $this->db->select('MIN(CAST(TRIM(SUBSTRING_INDEX(total_fees, "-", 1)) AS UNSIGNED)) AS lowest_fee');
    //   $this->db->select('MAX(CAST(TRIM(SUBSTRING_INDEX(total_fees, "-", -1)) AS UNSIGNED)) AS highest_fee');
    //   $this->db->from('college_course');
    //   $this->db->where('collegeid', $id); 
    //   $this->db->where('total_fees IS NOT NULL');
    //   $this->db->where('total_fees !=', '');
    //   $query2 = $this->db->get();
    //   $result2 = $query2->row_array();

    //   //echo $this->db->last_query(); exit ;

    //   return array(
    //       'all_fees' => $result1,
    //       'lowest_fee' => $result2['lowest_fee'],
    //       'highest_fee' => $result2['highest_fee']
    //   );


    // }


    public function getFeesDataOfCollege($id)
    {
        //print_r($id);exit;
        $this->db->select('COALESCE(cc.total_fees, cf.fees) AS total_fees,cc.collegeid');
        $this->db->from('college_course cc');
        $this->db->join('counseling_fees cf', 'cc.categoryid = cf.sub_category', 'left');
        $this->db->where('collegeid', $id);


        $query = $this->db->get();

        // Print the last executed query
        //   echo $this->db->last_query();exit; 

        return $query->result_array();
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
    public function countFilteredCourse($search, $cat)
    {
        $this->db->join("academic_categories ac", "ac.category_id = c.academic_category", "left");
        $this->db->join("category ca", "ca.id = c.course_category", "left");
        // if (!empty($cat)) {
        // 	$this->db->where('ac.name', $cat);
        // }
        $this->db->group_start();
        $this->db->like('c.name', $search);
        if (!empty($cat)) {
            $this->db->like('c.course_category', $cat);
        }
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

    public function getFilteredCourse($search, $cat, $start, $limit, $order, $dir)
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
        if (!empty($cat)) {
            $this->db->like('c.course_category', $cat);
        }
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

    public function getAllCourse($start, $limit, $order, $dir, $cat)
    {
        $this->db->select("c.*,ac.name as type,ca.catname as category");
        $this->db->from($this->table . " c");
        $this->db->join("academic_categories ac", "ac.category_id = c.academic_category", "left");
        $this->db->join("category ca", "ca.id = c.course_category", "left");
        if (!empty($cat)) {
            $this->db->where("ca.id", $cat);
        }

        $this->db->order_by($order, $dir);
        $this->db->limit($limit, $start);

        return $this->db->get()->result();
    }
    public function addLog($logArr, $tableName)
    {
        $this->db->insert($tableName, $logArr);
        return $this->db->insert_id();
    }

    public function getCoursesInfo($collegeId, $courseId, $college_typeid, $categoryid)
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
        if (!empty($entrance_exam)) {
            $entrance_exam = is_array($entrance_exam)
                ? $entrance_exam
                : explode(',', $entrance_exam);
        }



        // print_r($entrance_exam);exit;
        $ids = array_map('intval', explode(",", str_replace("\n", "", $categoryid)));
        // print_r($ids);exit;
        $this->db->select('c.id,c.academic_category as levelid, c.name, c.course_description, cc.total_fees, cc.total_intake, cc.median_salary, cc.rank, c.duration, ac.name as level, cc.website, cc.eligibility, ct.name as college_type,c.scope,c.job_profile,c.certification');
        $this->db->from('courses c');
        $this->db->join('college_course cc', 'cc.courseid = c.id', 'left');
        $this->db->join('college cl', 'cl.id = cc.collegeid', 'left');
        $this->db->join('college_type ct', 'ct.id = cl.college_typeid', 'left');
        $this->db->join('academic_categories ac', 'ac.category_id = c.academic_category', 'left');

        $this->db->where('c.id', $courseId);
        $this->db->where('cc.collegeid', $collegeId);
        $query = $this->db->get();
        $result = $query->result_array();
        foreach ($result as $row) {

            if ($row['total_fees'] == '' || $row['total_fees'] == NULL) {
                $this->db->select('c.id,c.academic_category as levelid, c.name, c.course_description, cf.fees as total_fees, cc.total_intake, cc.median_salary, cc.rank, c.duration, ac.name as level, cc.website, cc.eligibility, ct.name as college_type,c.scope,c.job_profile,c.certification');
                $this->db->from('courses c');
                $this->db->join('college_course cc', 'cc.courseid = c.id', 'left');
                $this->db->join('college cl', 'cl.id = cc.collegeid', 'left');
                $this->db->join('college_type ct', 'ct.id = cl.college_typeid', 'left');
                $this->db->join('academic_categories ac', 'ac.category_id = c.academic_category', 'left');
                $this->db->join('counseling_fees cf', 'cf.sub_category = cc.categoryid', 'LEFT');
                $this->db->join('exams ex', 'FIND_IN_SET(cc.entrance_exams, cf.exam_id)', 'LEFT'); // Updated join condition for entrance exams

                // $this->db->join('counseling_fees cf', 'cf.sub_category = cc.courseid', 'LEFT');
                //    AND FIND_IN_SET(cf.exam_id,cc.entrance_exams)
                $this->db->where('c.id', $courseId);
                $this->db->where('cc.collegeid', $collegeId);
                $this->db->where_in('cf.category', $ids);
                $this->db->where('cf.college_type', $college_typeid);
                $this->db->where('cf.sub_category', $sub_category);
                $this->db->where_in('cf.exam_id', $entrance_exam);
                $this->db->group_by('c.id');

                $query = $this->db->get();
                //echo $this->db->last_query();
                //exit;
                $result = $query->result_array();

                // print_r($result1);exit;
            }
        }
        if (empty($result)) {
            $this->db->select('c.id,c.academic_category as levelid, c.name, c.course_description, cc.total_fees, cc.total_intake, cc.median_salary, cc.rank, c.duration, ac.name as level, cc.website, cc.eligibility, ct.name as college_type,c.scope,c.job_profile,c.certification');
            $this->db->from('courses c');
            $this->db->join('college_course cc', 'cc.courseid = c.id', 'left');
            $this->db->join('college cl', 'cl.id = cc.collegeid', 'left');
            $this->db->join('college_type ct', 'ct.id = cl.college_typeid', 'left');
            $this->db->join('academic_categories ac', 'ac.category_id = c.academic_category', 'left');

            $this->db->where('c.id', $courseId);
            $this->db->where('cc.collegeid', $collegeId);
            $query = $this->db->get();
            //echo $this->db->last_query();exit;
            $result = $query->result_array();
            return $result;
        }
        return $result;
    }

    public function getCoursesInfo_bkp($collegeId, $courseId, $collegeTypeId, $categoryId)
    {
        // Base SELECT statement to avoid repetition
        $baseSelect = 'c.id, c.academic_category AS levelid, c.name, c.course_description, 
                   cc.total_fees, cc.total_intake, cc.median_salary, cc.rank, c.duration, 
                   ac.name AS level, cc.website, cc.eligibility, ct.name AS college_type, 
                   c.scope, c.job_profile, c.certification';

        // Build the base query
        $this->db->select($baseSelect)
            ->from('courses c')
            ->join('college_course cc', 'cc.courseid = c.id', 'left')
            ->join('college cl', 'cl.id = cc.collegeid', 'left')
            ->join('college_type ct', 'ct.id = cl.college_typeid', 'left')
            ->join('academic_categories ac', 'ac.category_id = c.academic_category', 'left')
            ->where('c.id', $courseId)
            ->where('cc.collegeid', $collegeId);

        // Execute the base query
        $query = $this->db->get();
        $result = $query->result_array();

        // Check if total_fees is missing and fetch from counseling_fees if needed
        if (!empty($result) && (empty($result[0]['total_fees']))) {
            $this->db->select($baseSelect . ', cf.fees AS total_fees')
                ->from('courses c')
                ->join('college_course cc', 'cc.courseid = c.id', 'left')
                ->join('college cl', 'cl.id = cc.collegeid', 'left')
                ->join('college_type ct', 'ct.id = cl.college_typeid', 'left')
                ->join('academic_categories ac', 'ac.category_id = c.academic_category', 'left')
                ->join('counseling_fees cf', 'cf.sub_category = cc.categoryid AND cf.category = ' . $categoryId . ' AND cf.college_type = ' . $collegeTypeId, 'left')
                ->where('c.id', $courseId)
                ->where('cc.collegeid', $collegeId);

            $query = $this->db->get();
            $result = $query->result_array();
        }

        // Return the result
        return $result;
    }

    // public function getCoursesfeeStructure($collegeId,$courseid)
    // {
    //     $query = $this->db->select('f.details as feecomponent,f.amount')
    //             ->from('fee_structure f')
    //             ->where('college_id', $collegeId)
    //             ->where('course_id', $courseid)
    //             ->get();
    //             echo $this->db->get_compiled_select();
    //             exit;
    //         $result = $query->result();
    //         return $result;
    // }

    public function getCoursesfeeStructure($collegeId, $courseid)
    {
        $this->db->select('f.details as feecomponent, f.amount');
        $this->db->from('fee_structure f');
        $this->db->where('f.college_id', $collegeId);
        $this->db->where('f.course_id', $courseid);
        // echo $this->db->get_compiled_select();
        // exit;

        $query = $this->db->get();
        $result = $query->result();
        return $result;
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

    public function getEntranceExamsForCourse($collegeId, $courseid)
    {
        $this->db->select('CASE WHEN FIND_IN_SET(e.id, cc.entrance_exams) THEN e.title ELSE NULL END AS exam_name,e.id, e.description, e.criteria');
        $this->db->from('(SELECT entrance_exams FROM college_course WHERE collegeid = ' . $collegeId . ' AND courseid = ' . $courseid . ') AS cc');
        $this->db->join('exams AS e', 'FIND_IN_SET(e.id, cc.entrance_exams)', 'left');
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }
}
