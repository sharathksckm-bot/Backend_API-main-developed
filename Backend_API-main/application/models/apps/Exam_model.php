<?php

class Exam_model extends CI_Model
{
  public function __construct()
  {
    parent::__construct();
    $this->load->database();
  }

  public function countAllExam()
  {
    $this->db->select("count(*) as exam_count");
    $this->db->from("exams e");
    $this->db->where("e.status", "1");

    $result = $this->db->get()->result_array();

    if (!empty($result)) {
      return $result[0]["exam_count"];
    } else {
      return 0;
    }
  }

  public function getExamNotificationForClg($collegeid)
  {
    $this->db->select('e.id as examId, e.title as examName, b.title as notification, b.image, b.datesubmit');
    $this->db->from('blog b');
    // $this->db->join('college_course cc', 'FIND_IN_SET(e.id, cc.entrance_exams) > 0', 'left');
    $this->db->join('exams e', 'e.id = b.exam_id', 'left');
    $this->db->where('b.college_id', $collegeid);
    $this->db->where('b.categoryid', 4);
    $this->db->group_by('e.id');
    $query = $this->db->get();
    // echo $this->db->last_query();exit;
    $result = $query->result();
    return $result;
  }
  // public function getExamsByCategory($courseCatId, $examId, $ac_id, $stateId = '')
  // {
  //     $this->db->select('e.*, e.id as examId, g.image');
  //     $this->db->from('exams e');
  //     $this->db->join('gallery g', 'g.postid = e.id AND g.type = "exams"', 'left'); // Move condition inside JOIN
  
  //     $this->db->where("FIND_IN_SET('$courseCatId', e.categoryid) >", 0); // Corrected FIND_IN_SET
  
  //     $this->db->where('e.status', '1');
  
  //     if (!empty($ac_id)) {
  //     //  echo "tttt";exit;
  //         $this->db->where('e.course_level', $ac_id);
  //     }
  
  //     if (!empty($stateId)) {
  //         $this->db->where('e.state_id', $stateId);
  //         $this->db->or_where('e.exam_level', 2);
  //         $this->db->order_by("e.state_id", 'DESC'); 
  //         $this->db->order_by("e.exam_level", 'ASC'); 
  //     }
  //     else{
  //       $this->db->order_by('e.exam_level', 'DESC');
  //     }
  //     $this->db->group_by('e.title');
  //     $this->db->order_by('e.title', 'DESC');
  
  //     $query = $this->db->get();
  //   echo $this->db->last_query();exit;
  //     return $query->result();
  // }

  public function getExamsByCategory($courseCatId, $examId, $ac_id, $stateId = '')
  {
      $this->db->select('e.*, e.id as examId, g.image');
      $this->db->from('exams e');
      $this->db->join('gallery g', "g.postid = e.id AND g.type = 'exams'", 'left');
      $this->db->where('e.status', '1');
      $this->db->where('e.course_level', $ac_id);
      $this->db->where("FIND_IN_SET('" . $courseCatId . "', e.categoryid) >", 0);
      $this->db->group_start();
      if (!empty($stateId)) {
          $this->db->where('e.state_id', $stateId);
      }
      $this->db->or_where('e.exam_level', 2);
      $this->db->group_end();
      $this->db->group_by('e.title');

      $orderCase = "CASE 
          WHEN e.state_id = '" . $stateId . "' THEN 0 
          WHEN e.exam_level = 2 THEN 1 
          ELSE 2 
      END";
      $this->db->order_by($orderCase, '', false);
      $this->db->order_by('e.exam_level', 'ASC');
      $this->db->order_by('e.state_id', 'DESC');
      $this->db->order_by('e.title', 'DESC');
      $query = $this->db->get();
       // echo $this->db->last_query();exit;
      return $query->result();
  }
  
  
    
    	public function getQue_PaperByExamId($examId)
    {
       //print_r($examId);exit;
        $this->db->select('e.*,e.id as examId, g.image');
        $this->db->from('exams e');
	    	$this->db->join('gallery g', 'g.postid = e.id', 'left');
      //  $this->db->where('e.categoryid', $courseCatId);
	   	$this->db->where('e.id', $examId);
        $this->db->where('e.status', '1');
		$this->db->where('g.type', 'exams');
	$this->db->group_by('e.id');
        $this->db->limit(10);
        $query = $this->db->get();
         //  echo $this->db->last_query();exit;
        $result = $query->result();
      
        return $result;
    }

public function getDocs($examId){
  //echo "tttt";exit;
  $this->db->select('d.*,e.title');
  $this->db->from('documents d');
  $this->db->join('exams e','d.examId = e.id','left');
  $this->db->where('d.examId',$examId);
  $this->db->where('d.is_deleted', 0);

  $query = $this->db->get();
  //  echo $this->db->last_query();exit;
 $result = $query->result();

 return $result;

}
    

  public function getExamsByCategoryForNav($examId)
  {
    $this->db->select('id, title');
    $this->db->from('exams');
    $this->db->where('categoryid', $examId);
    $this->db->where('status', '1');
    $this->db->where('view_in_menu', '1');
    $this->db->limit(5);
    $query = $this->db->get();
    $result = $query->result_array();
    return $result;
  }
  public function getExamList()
  {
    $this->db->select("e.id,e.title,e.slug,g.image,e.questionpaper,e.preparation,e.syllabus,e.notification");
    $this->db->from("exams e");
    $this->db->join('gallery g', 'g.postid = e.id', 'left');
    $this->db->where('g.type', 'exams');
    $this->db->where("e.status", "1");
    $result = $this->db->get()->result();
    return $result;
  }


  public function getExamDetailsbkp($examId)
  {
     // echo "ttt";exit;
    $this->db->select('g.image,e.id as eid,e.categoryid, e.title,e.description,e.criteria,e.process,SUBSTRING(`description`, 1,10) as short_exam_desc,e.pattern,e.slug,c.catname');
    $this->db->where('e.id', $examId);
    $this->db->where('c.type', 'exams');
    $this->db->where('g.type', 'exams');
    $this->db->where("e.status", "1");
    $this->db->join('category c', 'c.id = e.categoryid', 'left');
    $this->db->join('gallery g', 'g.postid = e.id', 'left');
 //echo $this->db->last_query(); 
    return $data = $this->db->get('exams e')->result();
  }

public function getExamDetails($examId){
    $this->db->select('g.image,e.id as eid,e.categoryid, e.title,e.description,e.criteria,e.process,SUBSTRING(`description`, 1,10) as short_exam_desc,e.pattern,e.slug,c.catname');
    $this->db->from('exams e');
    $this->db->where('e.id', $examId);
 //    $this->db->where('c.type', 'exams');
    $this->db->where('g.type', 'exams');
    $this->db->where("e.status", "1");
    $this->db->join('category c', 'c.id = e.categoryid', 'left');
    $this->db->join('gallery g', 'g.postid = e.id', 'left');
      $result = $this->db->get()->result();
   //   echo $this->db->last_query(); exit;
    return $result;
} 

  public function increment_view($examId = '')
  {
    if (!empty($examId)) {
      $this->db->where('id', $examId);
      $this->db->set('views', 'views+1', FALSE);
      $this->db->update('exams');
    }
  }
  public function listgallary($id)
  {
    $this->db->select('image');
    $this->db->where('postid', $id);
    $this->db->where('type', 'exams');
    return $this->db->get('gallery')->row();
  }

  function relatedExams($categoryid = '')
  {
    $this->db->select('e.title,e.id as eid,e.slug,e.description,c.catname,SUBSTRING(`description`, 1,100 ) as short_exam_desc,g.image');
    if (!empty($categoryid)) {
      $this->db->where('e.categoryid', $categoryid);
    }
    $this->db->where('e.status', '1');
    $this->db->where('g.type', 'exams');
    //$this->db->group_by('e.categoryid');
    $this->db->join('category c', 'c.id = e.categoryid', 'left');
    $this->db->join('gallery g', 'g.postid = e.id', 'left');
    $this->db->order_by('e.id', 'DESC');
    if (!empty($categoryid)) {
      $this->db->limit(10);
    } else {
      $this->db->limit(5);
    }
    //echo $this->db->last_query(); 
    return $this->db->get('exams e')->result();
  }
	
	public function getExamAccepted($collegeId)
    {
        $this->db->select('e.title, e.id, COUNT(cc.entrance_exams) as totalCount');
        $this->db->from('exams e');
        $this->db->join('college_course cc', 'FIND_IN_SET(e.id, cc.entrance_exams) > 0', 'left');
        $this->db->where('cc.collegeid', $collegeId);
        $this->db->group_by('e.id');

        $query = $this->db->get();
        $result = $query->result();

        return $result;

    }
	
	public function getExam_search($searchExam)
	{
		$this->db->select('id,title');
        $this->db->from('exams');
        $this->db->like('title', $searchExam);
        $this->db->limit(10);
    
    $query = $this->db->get(); // Execute the query

    if ($query->num_rows() > 0) {
        return $query->result(); // Return the result if there are rows
    } else {
        return array(); // Return an empty array if no rows found
    }
	}
	
	public function getarticle_search($searcharticle)
	{
		$this->db->select('id,title');
        $this->db->from('blog');
        $this->db->like('title', $searcharticle);
        $this->db->limit(10);
        $this->db->where('t_status',1);
    
        $query = $this->db->get(); // Execute the query

        if ($query->num_rows() > 0) {
        return $query->result(); // Return the result if there are rows
        } else {
        return array(); // Return an empty array if no rows found
    }
	}
	
	
	///------
	
/*	  public function getExamsByCategoryForMenu($examId,$statename)
  {
    //  print_r($statename);exit;
    //   if (!is_array($statename)) {
    //     $statename = [$statename]; 
    // }
  //  print_r($statename);exit;
    $this->db->select("e.id,e.title,e.slug,g.image,e.questionpaper,e.preparation,e.syllabus,e.notification");
    $this->db->from("exams e");
    $this->db->join('gallery g', 'g.postid = e.id', 'left');
    
   // $this->db->join('college c','c.categoryid = e.categoryid','left');
  //  $this->db->join('city ci','ci.id = c.cityid','left');
  //  $this->db->join('state s','s.id = ci.stateid','left');
    
 //   $this->db->where_in('s.statename',$statename);
    $this->db->where('g.type', 'exams');
    $this->db->where("e.status", "1");
    echo $this->db->last_query(); exit;
    $result = $this->db->get()->result();
    
    return $result;
  }*/
  
  	public function getExamsByCategoryForMenu123($statename,$courseCatId,$courseId)
    {
       // print_r($statename);exit;
          if (!is_array($statename)) {
         $statename = [$statename]; 
     }

     if (!is_array($courseCatId)) {
      $courseCatId = [$courseCatId]; 
  }
     
     $this->db->distinct(); 
    $this->db->select('e.id,e.title,e.slug,g.image,e.questionpaper,e.preparation,e.syllabus,e.notification,s.statename as statename');
    $this->db->from('exams e');
    $this->db->join('gallery g', 'g.postid = e.id', 'left');
   $this->db->join('college co',' e.categoryid = co.categoryid','left');
    $this->db->join('state s','co.stateid = s.id','left');
    $this->db->join('college_course cc','cc.collegeid = co.id','left');
    $this->db->join('sub_category sc','sc.id = cc.categoryid','left');
    if (!empty($courseCatId)) {
      $findInSetCondition = implode(" OR ", array_map(function($id) {
          return "FIND_IN_SET($id, e.categoryid) > 0";
      }, $courseCatId));

      $this->db->where("($findInSetCondition)");
  }
    $this->db->where_in('s.statename', $statename);
    $this->db->where_in('cc.categoryid',$courseId);
  //  $this->db->where_in('e.categoryid',$courseCatId);

      // Handling FIND_IN_SET for multiple category IDs
   
  
     $this->db->where('g.type', 'exams');
     $this->db->where("e.status", "1");
     $query = $this->db->get();
     
        echo $this->db->last_query(); exit;
        return $query->result_array();
    }

//     public function getExamsByCategoryForMenu($statename,$courseCatId,$courseId)
//     {
//        // print_r($statename);exit;
//           if (!is_array($statename)) {
//          $statename = [$statename]; 
//      }

//   //    if (!is_array($courseCatId)) {
//   //     $courseCatId = [$courseCatId]; 
//   // }
     
//      $this->db->distinct(); 
//     $this->db->select('e.id,e.title,e.slug,g.image,e.questionpaper,e.preparation,e.syllabus,e.notification,s.statename as statename');
//     $this->db->from('exams e');
//     $this->db->join('gallery g', 'g.postid = e.id', 'left');
//    $this->db->join('college co',' e.categoryid = co.categoryid','left');
//     $this->db->join('state s','co.stateid = s.id','left');
//     $this->db->join('college_course cc','cc.collegeid = co.id','left');
//   //  $this->db->join('sub_category sc','sc.id = cc.categoryid','left');

//    // Add the FIND_IN_SET condition in the LEFT JOIN with sub_category
//    $this->db->join('sub_category sc', 'sc.id = cc.categoryid AND FIND_IN_SET(' . (int)$courseCatId . ', e.categoryid) > 0', 'left');
//  //  $this->db->join('sub_category sc', 'sc.id = cc.categoryid AND FIND_IN_SET(' . $this->db->escape($courseCatId) . ', e.categoryid) > 0', 'left');


//     $this->db->where_in('s.statename', $statename);
//     $this->db->where_in('cc.categoryid',$courseId);
//   //  $this->db->where_in('e.categoryid',$courseCatId);

//       // Handling FIND_IN_SET for multiple category IDs
   
  
//      $this->db->where('g.type', 'exams');
//      $this->db->where("e.status", "1");
//      $query = $this->db->get();
     
//         //echo $this->db->last_query(); exit;
//         return $query->result_array();
//     }

public function getAcademicaCat($courseCatId,$courseId)
{
     $this->db->select("academic_category");
     $this->db->from("sub_category ");
      $this->db->where('parent_category', $courseCatId);
	$this->db->where('id', $courseId);
    $result = $this->db->get()->result();
    return $result;
}
public function getExamsByCategoryForMenu($statename, $courseCatId, $courseId,$academic_cat='')
{
    if (is_array($statename)) {
        $statename = reset($statename); 
    }

    $this->db->select('
        e.id, 
        e.title, 
        e.slug, 
        g.image, 
        e.questionpaper, 
        e.preparation, 
        e.syllabus, 
        e.notification, 
        s.statename AS statename, 
        cc.categoryid, 
        e.state_id, 
        e.categoryid
    ');
    $this->db->from('exams e');
    $this->db->join('gallery g', 'g.postid = e.id', 'left');
    $this->db->join('state s', 'e.state_id = s.id', 'left');
    $this->db->join('college_course cc', 'e.id = cc.entrance_exams', 'left');
  
    if (!empty($courseCatId) && is_numeric($courseCatId)) {
        $this->db->where("FIND_IN_SET(" . $this->db->escape_str($courseCatId) . ", e.categoryid) > 0", NULL, FALSE);
    }

    if (!empty($statename)) {
     // print_r($statename);exit;
        $this->db->where_in('s.statename', $statename);
    }

    if (!empty($courseId) && is_numeric($courseId)) {
        $this->db->where('cc.categoryid', $courseId);
    }
    if(!empty($courseCatId) && !empty($courseId)){
    //  print_r($academic_cat);exit;
    $this->db->where('e.course_level', $academic_cat);
    }
    $this->db->where('g.type', 'exams');
    $this->db->where("e.status", "1");
    $this->db->group_by('e.id');

    $query = $this->db->get();
   // echo $this->db->last_query(); exit;
    return $query->result_array();
}




    
  
	
/*public function getImageById($examId)
  {
    $this->db->select("g.image");
    $this->db->from("gallery g");
    //$this->db->join('gallery g', 'g.postid = e.id', 'left');
    $this->db->where('g.type', 'exams');
	$this->db->where('g.postid', $examId);
    //$this->db->where("e.status", "1");
    $result = $this->db->get()->result_array();
    return $result;
  }*/

}

?>
