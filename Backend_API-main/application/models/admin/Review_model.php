<?php
/**
 * review Model
 *
 * @category   Models
 * @package    Admin
 * @subpackage review
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    06 FEB 2024
 * 
 * Class review_model handles all review-related operations.
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Review_model extends CI_Model {

    private $table = 'review';


    public function __construct() {
        parent::__construct();
    }
	/**
     * Get the count of all Reviews.
     *
     * @return int The count of Reviews.
     */
    public function countAllReview()
    {
		$this->db->where('status',1);
        return $this->db->count_all($this->table);
    }

	/**
     * Count filtered Reviews based on the search term.
     *
     * @param string $search The search term.
     * @return int The number of filtered Reviews.
     */
    public function countFilteredReview($search)
    {
		$this->db->join("college c", "c.id = r.college_id", "left");
		$this->db->join("courses cc	", "cc.id = r.Review_id", "left");
        $this->db->like('c.title', $search);
		$this->db->or_like('r.title', $search);
		$this->db->or_like('cc.name', $search);
        return $this->db->get($this->table. " r")->num_rows();
    }

	/**
     * Get filtered Reviews.
     *
     * @param string $search The search term.
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated Reviews with additional information.
     */

	 public function getFilteredReview($search, $start, $limit, $order, $dir)
	 {
		$this->db->select("r.*,c.title as collegeName,cc.name as coursesName");
		 $this->db->from($this->table. " r");
		 $this->db->join("college c", "c.id = r.college_id", "left");
		 $this->db->join("courses cc	", "cc.id = r.Review_id", "left");
		 $this->db->where('r.is_deleted','0');

		 $this->db->group_start(); 
		 $this->db->like('c.title', $search);
		 $this->db->like('cc.name', $search);
		 $this->db->or_like('r.title', $search);
		 $this->db->group_end(); 
		 $this->db->order_by($order, $dir);
		 $this->db->limit($limit, $start);
 
		 $data = $this->db->get()->result_array();
		 if (!empty($data)) {
			foreach ($data as &$review) {
				$avg = ($review['placement_rate'] + $review['infrastructure_rate'] + $review['faculty_rate'] + $review['hostel_rate'] + $review['campus_rate'] + $review['money_rate']) / 6;
				$review['rating'] = ($avg * 100) / 5;
				$courseType = 'college_course';
	
				if ($review['course_type'] == 2) {
					$courseType = 'college_course';
				}
	
				$course = $this->db->where('id', $review['course_id'])->get('courses')->row();
	
				if (!empty($course)) {
					$review['course'] = $course->name;
				} else {
					$review['course'] = '';
				}
			}
		}
		return $data;
	 }

	 /**
     * Get all Reviews with filtering, ordering, and pagination.
     *
     * @param int    $start  The starting index for pagination.
     * @param int    $limit  The number of records to retrieve.
     * @param string $order  The column to order by.
     * @param string $dir    The direction of sorting.
     * @return array The list of filtered and paginated Reviews.
     */

	 public function getAllReview($start, $limit, $order, $dir)
{
    $this->db->select("r.*, c.title as collegeName, cc.name as coursesName");
    $this->db->from($this->table . " r");
    $this->db->join("college c", "c.id = r.college_id", "left");
    $this->db->join("courses cc", "cc.id = r.course_id", "left"); // Changed "Review_id" to "course_id"
	$this->db->where('r.is_deleted','0');

    $this->db->order_by($order, $dir);
    $this->db->limit($limit, $start);

    $data = $this->db->get()->result_array();

    if (!empty($data)) {
        foreach ($data as &$review) {
            $avg = ($review['placement_rate'] + $review['infrastructure_rate'] + $review['faculty_rate'] + $review['hostel_rate'] + $review['campus_rate'] + $review['money_rate']) / 6;
            $review['rating'] = ($avg * 100) / 5;
            $courseType = 'college_course';

            if ($review['course_type'] == 2) {
                $courseType = 'college_course'; 
            }

            $course = $this->db->where('id', $review['course_id'])->get('courses')->row();

            if (!empty($course)) {
                $review['course'] = $course->name;
            } else {
                $review['course'] = '';
            }
        }
    }

    return $data;
}
public function getReviewDetailsById($Id)
{
	$this->db->select("r.*, c.title as collegeName, cc.name as coursesName");
    $this->db->from($this->table . " r");
    $this->db->join("college c", "c.id = r.college_id", "left");
    $this->db->join("courses cc", "cc.id = r.course_id", "left"); // Changed "Review_id" to "course_id"
	$this->db->where('r.review_id',$Id);
	$this->db->where('r.is_deleted','0');
    $data = $this->db->get()->result_array();

    if (!empty($data)) {
        foreach ($data as &$review) {
            $avg = ($review['placement_rate'] + $review['infrastructure_rate'] + $review['faculty_rate'] + $review['hostel_rate'] + $review['campus_rate'] + $review['money_rate']) / 6;
            $review['rating'] = ($avg * 100) / 5;
            $courseType = 'college_course';

            if ($review['course_type'] == 2) {
                $courseType = 'college_course'; 
            }

            $course = $this->db->where('id', $review['course_id'])->get('courses')->row();

            if (!empty($course)) {
                $review['course'] = $course->name;
            } else {
                $review['course'] = '';
            }
        }
    }

    return $data;
}

public function deleteReview($Id)
{
	$data = ['is_deleted' =>1];
	$this->db->where("review_id", $Id);
	$query = $this->db->update($this->table, $data);
	return $query;
}

public function updateStatus($reviewId,$Arr)
{
	$this->db->where("review_id", $reviewId);
	$query = $this->db->update($this->table, $Arr);
	return $query;
}
}
