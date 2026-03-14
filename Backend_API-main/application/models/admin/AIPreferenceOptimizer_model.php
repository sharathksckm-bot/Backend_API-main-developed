<?php
/**
 * AI Preference Optimizer Model
 * 
 * Database operations for AI optimization logging
 * 
 * @category   Models
 * @package    Admin
 * @subpackage AIPreferenceOptimizer_model
 * @version    1.0
 */

if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}

class AIPreferenceOptimizer_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Log optimization request
     */
    public function logOptimizationRequest($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert('ai_optimization_logs', $data);
        return $this->db->insert_id();
    }

    /**
     * Get optimization history for user
     */
    public function getOptimizationHistory($user_id, $limit = 10)
    {
        $this->db->where('user_id', $user_id);
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get('ai_optimization_logs')->result();
    }

    /**
     * Get total optimizations count
     */
    public function getTotalOptimizations()
    {
        return $this->db->count_all('ai_optimization_logs');
    }

    /**
     * Get optimization stats
     */
    public function getOptimizationStats()
    {
        $this->db->select('DATE(created_at) as date, COUNT(*) as count');
        $this->db->group_by('DATE(created_at)');
        $this->db->order_by('date', 'DESC');
        $this->db->limit(30);
        return $this->db->get('ai_optimization_logs')->result();
    }
}
