<?php

/**
 * CourseApplication Model
 *
 * @category   Models
 * @package    Admin
 * @subpackage CourseApplication
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    22 JAN 2024
 * 
 * Class CourseApplication_model handles all CourseApplication-related operations.
 */

defined('BASEPATH') or exit('No direct script access allowed');

class CourseApplication_model extends CI_Model
{

    private $table = "course_application";

    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Count all CourseApplication in the table.
     *
     * @return int The total number of CourseApplication.
     */
    public function countAllCourseApplication($fromdate='',$todate='')
    { 
        if (!empty($fromdate) && !empty($todate)) {
			$this->db->where('Created_date >=', $fromdate);
			$this->db->where('Created_date <=', $todate);
		} elseif (!empty($fromdate)) {
			$this->db->where('Created_date >=', $fromdate);
		} elseif (!empty($todate)) {
			$this->db->where('Created_date <=', $todate);
		}
        return $this->db->count_all($this->table);
    }


    /**
     * Count filtered CourseApplication based on the search term.
     *
     * @param string $search The search term.
     * @return int The number of filtered CourseApplication.
     */
    public function countFilteredCourseApplication($search)
    {
        $this->db->select('COUNT(*) as count');
        $this->db->from($this->table . ' ca');
        $this->db->join('courses c', 'c.id = ca.course', 'left');
        $this->db->join('exams e', 'e.id = ca.entrance_exam', 'left');

        $this->db->group_start();
        $this->db->like('ca.student_name', $search);
        $this->db->or_like('ca.college', $search);
        $this->db->or_like('c.name', $search);
        $this->db->or_like('e.title', $search);
        $this->db->or_like('ca.category', $search);
        $this->db->group_end();

        return $this->db->get()->row()->count;
    }

    /**
     * Get filtered CourseApplication.
     *
     * @param string $search The search term.
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated CourseApplication with additional information.
     */

    public function getFilteredCourseApplication($search, $start, $limit, $order, $dir)
    {
        $this->db->select('ca.*,c.name as courses,e.title as exam,CONCAT(u.f_name, " ", u.l_name) as attended_by_name, co.title');
        $this->db->from($this->table . ' ca');
        $this->db->join('courses c', 'c.id = ca.course', 'left');
        $this->db->join('exams e', 'e.id = ca.entrance_exam', 'left');
        $this->db->join('users u', 'u.id = ca.attended_by', 'left');
		$this->db->join('college co', 'co.id = ca.college', 'left');
        $this->db->group_start();
        $this->db->like('ca.student_name', $search);
        $this->db->or_like('ca.college', $search);
        $this->db->or_like('c.name', $search);
        $this->db->or_like('e.title', $search);
        $this->db->or_like('ca.category', $search);
        $this->db->group_end();

        $this->db->order_by($order, $dir);
        $this->db->limit($limit, $start);

        return $this->db->get()->result();
    }

    /**
     * Get all CourseApplication with filtering, ordering, and pagination.
     *
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated CourseApplication.
     */

    public function getAllCourseApplication($start, $limit, $order, $dir)
    {
        $this->db->select('ca.*,c.name as courses,e.title as exam,CONCAT(u.f_name, " ", u.l_name) as attended_by_name, co.title');
        $this->db->from($this->table . ' ca');
        $this->db->join('courses c', 'c.id = ca.course', 'left');
        $this->db->join('exams e', 'e.id = ca.entrance_exam', 'left');
        $this->db->join('users u', 'u.id = ca.attended_by', 'left');
		$this->db->join('college co', 'co.id = ca.college', 'left');
        $this->db->order_by($order, $dir);
        $this->db->limit($limit, $start);

        return $this->db->get()->result();
    }

    public function deleteApplication($id)
    {
        $this->db->where("Id", $id);
        $query = $this->db->delete($this->table);
        return $query;
    }

    public function updateData($enquiryId,$Arr)
    {
        $this->db->where("id", $enquiryId);
        $query = $this->db->update($this->table, $Arr);
        return $query;
    }

public function getName($to)
{
    $this->db->select('student_name');
    $this->db->from('course_application');
    $this->db->where('email', $to);

    $query = $this->db->get(); 
    $result = $query->row();    
    return $result;
}
}
