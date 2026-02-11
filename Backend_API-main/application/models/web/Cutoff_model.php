    <?php
/**
 * Cutoff Model
 *
 * @category   Models
 * @package    web
 * @subpackage Cutoff
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    22 APRIL 2024
 * 
 * Class Cutoff_model handles all Cutoff-related operations.
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Cutoff_model extends CI_Model {

    private $KCETtable = 'cutoff_kcet';

    public function __construct() {
        parent::__construct();
    }

    public function getKCETCutoffCat($searchval)
    {
        $this->db->select('*');
        $this->db->from('cutoff_category');
        if(!empty($searchval))
        {
        $this->db->like('name', $searchval);
        }
        $this->db->limit(10);

        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }

   
    public function getKCETCutOff($college_id, $round, $category) {
        $this->db->select('ck.id, ck.year, ck.' . $category . ' as category, c.title as collegename, ct.catname as categoryname, cs.name as coursename');
        $this->db->from('cutoff_kcet ck');
        $this->db->join('college c', 'c.id = ck.college_id', 'left');
        $this->db->join('category ct', 'ct.id = ck.category_id', 'left');
        $this->db->join('courses cs', 'cs.id = ck.course_id', 'left');
        $this->db->where('ck.college_id', $college_id);
        $this->db->where('ck.round', $round);
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }
    
    public function getCOMDEKCutOff($college_id,$round,$category)
    {
         $this->db->select('ck.id, ck.year,ck.rank, ck.category,ck.round,ck.category, c.title as collegename,  cs.name as coursename');
        $this->db->from('cutoff_comedk ck');
        $this->db->join('college c', 'c.id = ck.college_id', 'left');
        $this->db->join('courses cs', 'cs.id = ck.course_id', 'left');
        $this->db->where('ck.college_id', $college_id);
        if(!empty($round))
        {
        $this->db->where('ck.round', $round);
        }
        if(!empty($category))
        {
          $this->db->where('ck.category', $category);

        }
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }
    
 public function getCoutOffRoundWise($college_id)
{
    $this->db->select('ct.catname as Category, cs.name as Course,`round`,  `year`, `1G`, `1H`, `1K`, `1KH`, `1R`, `1RH`, `2AG`, `2AH`, `2AK`, `2AKH`, `2AR`, `2ARH`, `2BG`, `2BH`, `2BK`, `2BKH`, `2BR`, `2BRH`, `2BRG`, `3AG`, `3AH`, `3AK`, `3AKH`, `3AR`, `3ARH`, `3BG`, `3BH`, `3BK`, `3BKH`, `3BR`, `3BRH`, `GM`, `GMH`, `GMK`, `GMKH`, `GMR`, `GMRH`, `SCG`, `SCH`, `SCK`, `SCKH`, `SCR`, `SCRH`, `STG`, `STH`, `STK`, `STKH`, `STR`, `STRH`');
    $this->db->from('cutoff_kcet ck');
    $this->db->join('college c', 'c.id = ck.college_id', 'left');
    $this->db->join('category ct', 'ct.id = ck.category_id', 'left');
    $this->db->join('courses cs', 'cs.id = ck.course_id', 'left');
    $this->db->where('ck.college_id', $college_id);
    $query = $this->db->get();

    if ($query->num_rows() > 0) {
        $result = $query->result();

        $roundData = []; // Initialize array to store data for each round

        foreach ($result as $row) {
    $roundNumber = $row->round;
    if (!isset($roundData['round'.$roundNumber])) {
        $roundData['round'.$roundNumber] = [];
    }

    $rowArray = [];
    foreach ($row as $column_name => $value) {
        $rowArray[] = array('label' => $column_name, 'value' => $value);
    }
    $roundData['round'.$roundNumber][] = $rowArray;
}


        return $roundData;
    }

    return []; // Return an empty array if no rows found
}



    public function getCounsellingFees($typeid,$category,$exam)
    {
        $category = explode(',',$category) ;
        $category = array_map('intval', $category);
        if(!empty($exam) ){
            $exam = explode(',',$exam);
            $exam = array_map('intval', $exam);
        }else{
              $exam = '';
        }
        
        // print_r($category);exit;
        $this->db->select('cf.*,ct.catname,e.title as exam,c.name as type');
        $this->db->from('counseling_fees cf');
        $this->db->join('category ct', 'ct.id = cf.category', 'left');
        $this->db->join('exams e', 'e.id = cf.exam_id', 'left');
        $this->db->join('college_type c', 'c.id = cf.college_type', 'left');

        $this->db->where('cf.college_type', $typeid);
        $this->db->where_in('cf.category', $category);
        $this->db->where_in('cf.exam_id', $exam);
        $query = $this->db->get();
        // echo $this->db->last_query();exit;
        $result = $query->result();
        return $result;
    }
    
     public function getCOMEDKCutOffRoundWise($college_id)
{
    $this->db->select('ck.category ,cs.name as Course,`round`,  `year`');
    $this->db->from('cutoff_comedk ck');
    $this->db->join('college c', 'c.id = ck.college_id', 'left');
    $this->db->join('courses cs', 'cs.id = ck.course_id', 'left');
    $this->db->where('ck.college_id', $college_id);
    $query = $this->db->get();

    if ($query->num_rows() > 0) {
        $result = $query->result();

        $roundData = []; // Initialize array to store data for each round

        foreach ($result as $row) {
    $roundNumber = $row->round;
    if (!isset($roundData['round'.$roundNumber])) {
        $roundData['round'.$roundNumber] = [];
    }

    $rowArray = [];
    foreach ($row as $column_name => $value) {
        $rowArray[] = array('label' => $column_name, 'value' => $value);
    }
    $roundData['round'.$roundNumber][] = $rowArray;
}


        return $roundData;
    }

    return []; // Return an empty array if no rows found
}
}