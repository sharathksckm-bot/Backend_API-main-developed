<?php
/**
 * Seat Availability Model
 * 
 * Database operations for seat availability tracking
 * 
 * @category   Models
 * @package    Admin
 * @subpackage SeatAvailability_model
 * @version    1.0
 */

if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}

class SeatAvailability_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Count all seats with filters
     */
    public function countAllSeats($filters = [])
    {
        if (!empty($filters['state'])) {
            $this->db->where('state', $filters['state']);
        }
        if (!empty($filters['counseling_type'])) {
            $this->db->where('counseling_type', $filters['counseling_type']);
        }
        if (!empty($filters['round'])) {
            $this->db->where('round', $filters['round']);
        }
        if (!empty($filters['year'])) {
            $this->db->where('year', $filters['year']);
        }
        return $this->db->count_all_results('seat_availability');
    }

    /**
     * Count filtered seats
     */
    public function countFilteredSeats($search, $filters = [])
    {
        $this->db->group_start();
        $this->db->like('college_name', $search);
        $this->db->or_like('state', $search);
        $this->db->or_like('category', $search);
        $this->db->group_end();
        
        if (!empty($filters['state'])) {
            $this->db->where('state', $filters['state']);
        }
        if (!empty($filters['counseling_type'])) {
            $this->db->where('counseling_type', $filters['counseling_type']);
        }
        if (!empty($filters['round'])) {
            $this->db->where('round', $filters['round']);
        }
        if (!empty($filters['year'])) {
            $this->db->where('year', $filters['year']);
        }
        
        return $this->db->count_all_results('seat_availability');
    }

    /**
     * Get filtered seats with pagination
     */
    public function getFilteredSeats($search, $filters, $start, $limit, $orderColumn, $orderDir)
    {
        $this->db->group_start();
        $this->db->like('college_name', $search);
        $this->db->or_like('state', $search);
        $this->db->or_like('category', $search);
        $this->db->group_end();
        
        if (!empty($filters['state'])) {
            $this->db->where('state', $filters['state']);
        }
        if (!empty($filters['counseling_type'])) {
            $this->db->where('counseling_type', $filters['counseling_type']);
        }
        if (!empty($filters['round'])) {
            $this->db->where('round', $filters['round']);
        }
        if (!empty($filters['year'])) {
            $this->db->where('year', $filters['year']);
        }
        
        $this->db->order_by($orderColumn, $orderDir);
        $this->db->limit($limit, $start);
        return $this->db->get('seat_availability')->result();
    }

    /**
     * Get all seats with pagination
     */
    public function getAllSeats($filters, $start, $limit, $orderColumn, $orderDir)
    {
        if (!empty($filters['state'])) {
            $this->db->where('state', $filters['state']);
        }
        if (!empty($filters['counseling_type'])) {
            $this->db->where('counseling_type', $filters['counseling_type']);
        }
        if (!empty($filters['round'])) {
            $this->db->where('round', $filters['round']);
        }
        if (!empty($filters['year'])) {
            $this->db->where('year', $filters['year']);
        }
        
        $this->db->order_by($orderColumn, $orderDir);
        $this->db->limit($limit, $start);
        return $this->db->get('seat_availability')->result();
    }

    /**
     * Insert new seat availability
     */
    public function insertSeatAvailability($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['last_updated'] = date('Y-m-d H:i:s');
        $this->db->insert('seat_availability', $data);
        return $this->db->insert_id();
    }

    /**
     * Update seat availability by ID
     */
    public function updateSeatAvailability($data, $id)
    {
        $data['last_updated'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id);
        return $this->db->update('seat_availability', $data);
    }

    /**
     * Delete seat availability by ID
     */
    public function deleteSeatAvailability($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('seat_availability');
    }

    /**
     * Get seat by ID
     */
    public function getSeatById($id)
    {
        $this->db->where('id', $id);
        return $this->db->get('seat_availability')->row();
    }

    /**
     * Check if seat exists (for upsert)
     */
    public function checkSeatExists($data)
    {
        $this->db->where('year', $data['year']);
        $this->db->where('state', $data['state']);
        $this->db->where('college_name', $data['college_name']);
        $this->db->where('course', $data['course']);
        $this->db->where('round', $data['round']);
        $this->db->where('category', $data['category']);
        return $this->db->count_all_results('seat_availability') > 0;
    }

    /**
     * Update existing seat (for upsert)
     */
    public function updateExistingSeat($data)
    {
        $this->db->where('year', $data['year']);
        $this->db->where('state', $data['state']);
        $this->db->where('college_name', $data['college_name']);
        $this->db->where('course', $data['course']);
        $this->db->where('round', $data['round']);
        $this->db->where('category', $data['category']);
        $data['last_updated'] = date('Y-m-d H:i:s');
        return $this->db->update('seat_availability', $data);
    }

    /**
     * Get public seats for mobile app
     */
    public function getPublicSeats($state, $counseling_type, $course, $year)
    {
        $this->db->where('year', $year);
        $this->db->where('status', 'Active');
        
        if (!empty($state)) {
            $this->db->where('state', $state);
        }
        if (!empty($counseling_type)) {
            $this->db->where('counseling_type', $counseling_type);
        }
        if (!empty($course)) {
            $this->db->where('course', $course);
        }
        
        $this->db->order_by('round', 'ASC');
        $this->db->order_by('college_name', 'ASC');
        
        return $this->db->get('seat_availability')->result();
    }

    /**
     * Get active rounds
     */
    public function getActiveRounds($state, $counseling_type)
    {
        $this->db->select('DISTINCT(round) as round');
        $this->db->where('status', 'Active');
        $this->db->where('year', date('Y'));
        
        if (!empty($state)) {
            $this->db->where('state', $state);
        }
        if (!empty($counseling_type)) {
            $this->db->where('counseling_type', $counseling_type);
        }
        
        $this->db->order_by('round', 'ASC');
        return $this->db->get('seat_availability')->result();
    }
}
