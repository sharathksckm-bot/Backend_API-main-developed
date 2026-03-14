<?php
/**
 * NEET Predictor Model
 * 
 * Database operations for NEET College Predictor
 * 
 * @category   Models
 * @package    Admin
 * @subpackage NeetPredictor_model
 * @version    1.0
 */

if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}

class NeetPredictor_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Count all NEET cutoffs
     */
    public function countAllNeetCutoffs()
    {
        return $this->db->count_all('neet_cutoffs');
    }

    /**
     * Count filtered NEET cutoffs
     */
    public function countFilteredNeetCutoffs($search)
    {
        $this->db->like('college_name', $search);
        $this->db->or_like('state', $search);
        $this->db->or_like('course', $search);
        $this->db->or_like('category', $search);
        return $this->db->count_all_results('neet_cutoffs');
    }

    /**
     * Get filtered NEET cutoffs with pagination
     */
    public function getFilteredNeetCutoffs($search, $start, $limit, $orderColumn, $orderDir)
    {
        $this->db->like('college_name', $search);
        $this->db->or_like('state', $search);
        $this->db->or_like('course', $search);
        $this->db->or_like('category', $search);
        $this->db->order_by($orderColumn, $orderDir);
        $this->db->limit($limit, $start);
        return $this->db->get('neet_cutoffs')->result();
    }

    /**
     * Get all NEET cutoffs with pagination
     */
    public function getAllNeetCutoffs($start, $limit, $orderColumn, $orderDir)
    {
        $this->db->order_by($orderColumn, $orderDir);
        $this->db->limit($limit, $start);
        return $this->db->get('neet_cutoffs')->result();
    }

    /**
     * Insert new NEET cutoff
     */
    public function insertNeetCutoff($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert('neet_cutoffs', $data);
        return $this->db->insert_id();
    }

    /**
     * Update NEET cutoff by ID
     */
    public function updateNeetCutoff($data, $id)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id);
        return $this->db->update('neet_cutoffs', $data);
    }

    /**
     * Get NEET cutoff by ID
     */
    public function getNeetCutoffById($id)
    {
        $this->db->where('id', $id);
        return $this->db->get('neet_cutoffs')->row();
    }

    /**
     * Delete NEET cutoff by ID
     */
    public function deleteNeetCutoff($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('neet_cutoffs');
    }

    /**
     * Check if cutoff exists (for CSV import)
     */
    public function checkCutoffExists($data)
    {
        $this->db->where('year', $data['year']);
        $this->db->where('state', $data['state']);
        $this->db->where('college_name', $data['college_name']);
        $this->db->where('course', $data['course']);
        $this->db->where('category', $data['category']);
        $this->db->where('round', $data['round']);
        return $this->db->count_all_results('neet_cutoffs');
    }

    /**
     * Update existing cutoff (for CSV import)
     */
    public function updateExistingCutoff($data)
    {
        $this->db->where('year', $data['year']);
        $this->db->where('state', $data['state']);
        $this->db->where('college_name', $data['college_name']);
        $this->db->where('course', $data['course']);
        $this->db->where('category', $data['category']);
        $this->db->where('round', $data['round']);
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->update('neet_cutoffs', $data);
    }

    /**
     * Get distinct states
     */
    public function getDistinctStates()
    {
        $this->db->distinct();
        $this->db->select('state');
        $this->db->order_by('state', 'ASC');
        return $this->db->get('neet_cutoffs')->result();
    }

    /**
     * Get state counseling rules
     */
    public function getStateRules($state)
    {
        $this->db->where('state', $state);
        return $this->db->get('neet_state_rules')->row();
    }

    /**
     * Get latest year in cutoff data
     */
    public function getLatestYear()
    {
        $this->db->select_max('year');
        $result = $this->db->get('neet_cutoffs')->row();
        return $result ? $result->year : date('Y');
    }

    /**
     * Get cutoff data for prediction
     */
    public function getCutoffDataForPrediction($year, $state, $course, $category, $counseling_type)
    {
        $this->db->where('year', $year);
        
        if (!empty($state)) {
            $this->db->where('state', $state);
        }
        
        if (!empty($course)) {
            $this->db->where('course', $course);
        }
        
        if (!empty($category)) {
            $this->db->where('category', $category);
        }
        
        if (!empty($counseling_type)) {
            $this->db->where('counseling_type', $counseling_type);
        }

        $this->db->where('closing_rank >', 0);
        $this->db->order_by('closing_rank', 'ASC');
        
        return $this->db->get('neet_cutoffs')->result();
    }

    /**
     * Log predictor usage
     */
    public function logPredictorUsage($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert('neet_predictor_logs', $data);
        return $this->db->insert_id();
    }

    /**
     * Get predictor logs
     */
    public function getPredictorLogs($limit, $offset)
    {
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit, $offset);
        return $this->db->get('neet_predictor_logs')->result();
    }

    /**
     * Count predictor logs
     */
    public function countPredictorLogs()
    {
        return $this->db->count_all('neet_predictor_logs');
    }
}
