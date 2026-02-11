<?php
/**
 * Event Model
 *
 * @category   Models
 * @package    Admin
 * @subpackage Event
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    09 JAN 2024
 * 
 * Class events_model handles all events-related operations.
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Events_model extends CI_Model {

    private $table = 'events';

    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Get the count of all events.
     *
     * @return int The count of events.
     */
    public function getEventsCount() {
        $this->db->select('COUNT(*) as count');
        return $this->db->get($this->table)->row()->count;
    }
}
