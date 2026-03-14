<?php
/**
 * Generic Predictor Model
 * 
 * Database operations for Generic College Predictor (KCET, COMEDK, JEE)
 * 
 * @category   Models
 * @package    Admin
 * @subpackage GenericPredictor_model
 * @version    1.0
 */

if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}

class GenericPredictor_model extends CI_Model
{
    private $cutoffsTable = 'generic_college_cutoffs';
    private $logsTable = 'generic_predictor_logs';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Count all cutoffs
     */
    public function countAllCutoffs($examType = '')
    {
        if (!empty($examType)) {
            $this->db->where('exam_type', $examType);
        }
        return $this->db->count_all_results($this->cutoffsTable);
    }

    /**
     * Count filtered cutoffs
     */
    public function countFilteredCutoffs($search, $examType = '')
    {
        if (!empty($examType)) {
            $this->db->where('exam_type', $examType);
        }
        $this->db->group_start();
        $this->db->like('college_name', $search);
        $this->db->or_like('course', $search);
        $this->db->or_like('category', $search);
        $this->db->group_end();
        return $this->db->count_all_results($this->cutoffsTable);
    }

    /**
     * Get filtered cutoffs with pagination
     */
    public function getFilteredCutoffs($search, $start, $limit, $orderColumn, $orderDir, $examType = '')
    {
        if (!empty($examType)) {
            $this->db->where('exam_type', $examType);
        }
        $this->db->group_start();
        $this->db->like('college_name', $search);
        $this->db->or_like('course', $search);
        $this->db->or_like('category', $search);
        $this->db->group_end();
        $this->db->order_by($orderColumn, $orderDir);
        $this->db->limit($limit, $start);
        return $this->db->get($this->cutoffsTable)->result();
    }

    /**
     * Get all cutoffs with pagination
     */
    public function getAllCutoffs($start, $limit, $orderColumn, $orderDir, $examType = '')
    {
        if (!empty($examType)) {
            $this->db->where('exam_type', $examType);
        }
        $this->db->order_by($orderColumn, $orderDir);
        $this->db->limit($limit, $start);
        return $this->db->get($this->cutoffsTable)->result();
    }

    /**
     * Insert new cutoff
     */
    public function insertCutoff($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->cutoffsTable, $data);
        return $this->db->insert_id();
    }

    /**
     * Update cutoff by ID
     */
    public function updateCutoff($data, $id)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id);
        return $this->db->update($this->cutoffsTable, $data);
    }

    /**
     * Get cutoff by ID
     */
    public function getCutoffById($id)
    {
        $this->db->where('id', $id);
        return $this->db->get($this->cutoffsTable)->row();
    }

    /**
     * Delete cutoff by ID
     */
    public function deleteCutoff($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete($this->cutoffsTable);
    }

    /**
     * Check if cutoff exists (for import)
     */
    public function checkCutoffExists($data)
    {
        $this->db->where('exam_type', $data['exam_type']);
        $this->db->where('year', $data['year']);
        $this->db->where('round', $data['round']);
        $this->db->where('college_name', $data['college_name']);
        $this->db->where('course', $data['course']);
        return $this->db->count_all_results($this->cutoffsTable);
    }

    /**
     * Update existing cutoff (for import)
     */
    public function updateExistingCutoff($data)
    {
        $this->db->where('exam_type', $data['exam_type']);
        $this->db->where('year', $data['year']);
        $this->db->where('round', $data['round']);
        $this->db->where('college_name', $data['college_name']);
        $this->db->where('course', $data['course']);
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->update($this->cutoffsTable, $data);
    }

    /**
     * Get distinct years for an exam type
     */
    public function getDistinctYears($examType = '')
    {
        $this->db->distinct();
        $this->db->select('year');
        if (!empty($examType)) {
            $this->db->where('exam_type', $examType);
        }
        $this->db->order_by('year', 'DESC');
        return $this->db->get($this->cutoffsTable)->result();
    }

    /**
     * Get distinct rounds for an exam type and year
     */
    public function getDistinctRounds($examType = '', $year = '')
    {
        $this->db->distinct();
        $this->db->select('round');
        if (!empty($examType)) {
            $this->db->where('exam_type', $examType);
        }
        if (!empty($year)) {
            $this->db->where('year', $year);
        }
        $this->db->order_by('round', 'ASC');
        return $this->db->get($this->cutoffsTable)->result();
    }

    /**
     * Get distinct categories for an exam type
     */
    public function getDistinctCategories($examType = '')
    {
        $this->db->distinct();
        $this->db->select('category');
        if (!empty($examType)) {
            $this->db->where('exam_type', $examType);
        }
        $this->db->order_by('category', 'ASC');
        return $this->db->get($this->cutoffsTable)->result();
    }

    /**
     * Get distinct courses for an exam type and category
     */
    public function getDistinctCourses($examType = '', $category = '')
    {
        $this->db->distinct();
        $this->db->select('course');
        if (!empty($examType)) {
            $this->db->where('exam_type', $examType);
        }
        if (!empty($category)) {
            $this->db->where('category', $category);
        }
        $this->db->order_by('course', 'ASC');
        return $this->db->get($this->cutoffsTable)->result();
    }

    /**
     * Get latest year in cutoff data
     */
    public function getLatestYear($examType = '')
    {
        $this->db->select_max('year');
        if (!empty($examType)) {
            $this->db->where('exam_type', $examType);
        }
        $result = $this->db->get($this->cutoffsTable)->row();
        return $result ? $result->year : date('Y');
    }

    /**
     * Get latest round for an exam type and year
     */
    public function getLatestRound($examType, $year)
    {
        $this->db->select('round');
        $this->db->where('exam_type', $examType);
        $this->db->where('year', $year);
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $result = $this->db->get($this->cutoffsTable)->row();
        return $result ? $result->round : 'Round 1';
    }

    /**
     * Get cutoff data for prediction
     */
    public function getCutoffDataForPrediction($examType, $year, $round, $category = '', $course = '')
    {
        $this->db->where('exam_type', $examType);
        $this->db->where('year', $year);
        $this->db->where('round', $round);
        
        if (!empty($category)) {
            $this->db->where('category', $category);
        }
        
        if (!empty($course)) {
            $this->db->like('course', $course);
        }

        $this->db->order_by('college_name', 'ASC');
        
        return $this->db->get($this->cutoffsTable)->result();
    }

    /**
     * Log predictor usage
     */
    public function logPredictorUsage($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->logsTable, $data);
        return $this->db->insert_id();
    }

    /**
     * Get predictor logs
     */
    public function getPredictorLogs($limit, $offset, $examType = '')
    {
        if (!empty($examType)) {
            $this->db->where('exam_type', $examType);
        }
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit, $offset);
        return $this->db->get($this->logsTable)->result();
    }

    /**
     * Count predictor logs
     */
    public function countPredictorLogs($examType = '')
    {
        if (!empty($examType)) {
            $this->db->where('exam_type', $examType);
        }
        return $this->db->count_all_results($this->logsTable);
    }

    /**
     * Delete bulk cutoffs
     */
    public function deleteBulkCutoffs($examType, $year = '', $round = '')
    {
        $this->db->where('exam_type', $examType);
        if (!empty($year)) {
            $this->db->where('year', $year);
        }
        if (!empty($round)) {
            $this->db->where('round', $round);
        }
        $this->db->delete($this->cutoffsTable);
        return $this->db->affected_rows();
    }

    /**
     * Get statistics for dashboard
     */
    public function getStatistics($examType = '')
    {
        $stats = [];
        
        // Total records
        if (!empty($examType)) {
            $this->db->where('exam_type', $examType);
        }
        $stats['total_records'] = $this->db->count_all_results($this->cutoffsTable);
        
        // Distinct colleges
        $this->db->distinct();
        $this->db->select('college_name');
        if (!empty($examType)) {
            $this->db->where('exam_type', $examType);
        }
        $stats['total_colleges'] = $this->db->count_all_results($this->cutoffsTable);
        
        // Distinct courses
        $this->db->distinct();
        $this->db->select('course');
        if (!empty($examType)) {
            $this->db->where('exam_type', $examType);
        }
        $stats['total_courses'] = $this->db->count_all_results($this->cutoffsTable);
        
        return $stats;
    }
}
