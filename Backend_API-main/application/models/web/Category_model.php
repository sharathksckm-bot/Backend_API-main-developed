<?php

/**
 * Categorys Model
 *
 * @category   Models
 * @package    Web
 * @subpackage Categorys
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    24 JAN 2024
 * 
 * Class Category_model handles all Category-related operations.
 */

defined('BASEPATH') or exit('No direct script access allowed');

class Category_model extends CI_Model
{

    private $table = 'category';

    public function __construct()
    {
        parent::__construct();
    }

    public function getCategory()
    {
        $this->db->select('id,catname,type');
        $this->db->from('category');
        $this->db->where('type', 'college');
        $this->db->where('topmenu', 1);
        $this->db->where('status', 1);
        $this->db->order_by('menuorder', 'ASC');
        $query = $this->db->get();
        // echo $this->db->last_query();exit;
        $result = $query->result();
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
	
	public function getCategoryForMenus()
    {
        $sql = "SELECT id, catname as title, menuorder, type 
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
                    END";

        $query = $this->db->query($sql);
        $result = $query->result(); // Get the result before returning
        return $result; // Return the result
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
	public function getCourse($id)
    {
        $this->db->select('id, name as title');
        $this->db->from('courses');
        $this->db->where("course_category", $id);
        $this->db->limit(10);
        $this->db->order_by('id', 'desc');
        $query = $this->db->get();
        $result = $query->result();
        //echo $this->db->last_query();exit;
        return $result;
    }
    public function getExams($catId)
    {
        $this->db->select('id, title, description');
        $this->db->from('exams');
        $this->db->where('view_in_menu','1');
        $this->db->where_in('categoryid',$catId);

        $this->db->limit(10);
        $query = $this->db->get();
        $result = $query->result();
        //echo $this->db->last_query();exit;
        return $result;
    }
	
	public function getExam()
    {
        $this->db->select('id, title');
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
}
