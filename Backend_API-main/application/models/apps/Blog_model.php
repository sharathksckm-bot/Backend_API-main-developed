<?php

defined('BASEPATH') or exit('No direct script access allowed');


class Blog_model extends CI_Model
{
  public function __construct()
  {
    parent::__construct();
    $this->load->database();
  }

 /* public function get_Blogs($searchCategory = '',$value='',$state_name=[])
  {
     // print_r($state_name);exit;
   $this->db->select('b.*');
    $this->db->from('blog b');
    $this->db->where('t_status','1');
    $this->db->join('exams e','b.exam_id = e.id');
     $this->db->join('state s','e.state_id = s.id');
    $this->db->where('b.image IS NOT NULL');
   $this->db->where('b.title IS NOT NULL');
    $this->db->where_in('s.statename',$state_name);
	$this->db->limit(10);
    if(!empty($searchCategory))
    {
    $this->db->where('e.categoryid',$searchCategory);
    }
    if(!empty($value))
    {
    $this->db->like('b.title',$value);
    }
    $this->db->order_by('b.id','desc');
	  //$this->db->limit(5);
    $query = $this->db->get();
    
// echo $this->db->last_query(); exit; 
    
    if ($query->num_rows() > 0) {
      return $query->result();
    } else {
      return false;
    }
	
  }*/
  
 /* public function get_Blogs($searchCategory='',$value='',$state_name=[]){
      
       $this->db->select([
        'b.*',
        'ca.catname'
    ]);
      $this->db->from('blog b');
      $this->db->join('college c', 'b.college_id = c.id', 'left');
      $this->db->join('category ca', 'c.categoryid = ca.id', 'left');
      $this->db->join('exams e', 'b.exam_id = e.id', 'left');
  $this->db->join('state s','c.stateid = s.id');
    $this->db->where('b.image IS NOT NULL');
   $this->db->where('b.title IS NOT NULL');
      $this->db->where('t_status','1');
      
     $this->db->group_start();
        $this->db->where('e.exam_level', '2'); 
        if (!empty($state_name)) {
            $this->db->or_where_in('s.statename', $state_name); 
        }
    $this->db->group_end();
    
        if(!empty($searchCategory))
    {
       $this->db->where_in('ca.id', $searchCategory);
    }
     if(!empty($value))
    {
    $this->db->like('b.title',$value);
    }
   /*  if(!empty($state_name))
    {
     $this->db->where_in('s.statename',$state_name);
    }
  
  
   $this->db->order_by('b.id','desc');
    $this->db->limit(10);
      $query = $this->db->get();
    //  echo $this->db->last_query(); exit; 
      return $query->result_array();
  }*/
  
  public function get_Blogs($searchCategory = '', $value = '', $state_name = [])
{

  $categoryArray = !empty($searchCategory) ? array_map('intval', explode(',', $searchCategory)) : [];

//print_r($categoryArray);exit;
    $this->db->select([
        'b.*',
        'ca.catname as catname',
        's.statename'
    ]);
    $this->db->from('blog b');
    $this->db->join('college c', 'b.college_id = c.id', 'left');
    $this->db->join('category ca', 'c.categoryid = ca.id', 'left');
    $this->db->join('exams e', 'b.exam_id = e.id', 'left');
    $this->db->join('state s', 'c.stateid = s.id','left');

    $this->db->where('b.image IS NOT NULL');
    $this->db->where('b.title IS NOT NULL');
    $this->db->where('t_status', '1');
//$this->db->where('e.exam_level', '2'); 
 if(!empty($state_name))
    {
     $this->db->where_in('s.statename',$state_name);
    }

    
    if (!empty($categoryArray)) { 
      $this->db->where_in('b.categoryid', $categoryArray);
  }
    if (!empty($value)) {
        $this->db->like('b.title', $value);
    }
    // Latest first
    $this->db->order_by('b.created_date', 'DESC');
    $this->db->order_by('b.id', 'DESC');
    

    $this->db->limit(10);
    
    $query = $this->db->get();
   // echo $this->db->last_query(); exit; 
    return $query->result_array();
}

public function getBlogsDatas($searchCategory,$value)
{
 // echo "ttt";exit;
    $this->db->select([
        'b.*',
        'ca.catname as catname',
    ]);
    $this->db->from('blog b');
    $this->db->join('college c', 'b.college_id = c.id', 'left');
    $this->db->join('category ca', 'c.categoryid = ca.id', 'left');
    $this->db->join('exams e', 'b.exam_id = e.id', 'left');

    
    $this->db->where('b.image IS NOT NULL');
    $this->db->where('b.title IS NOT NULL');
    $this->db->where('t_status', '1');
   

 if (!empty($searchCategory) && $searchCategory!=-2) {
       // $this->db->where_in('ca.id', $searchCategory);
        $this->db->where("FIND_IN_SET('$searchCategory',b.categoryid) > 0");
        $this->db->where('e.exam_level', '2');
    }
  else if(!empty($searchCategory) && $searchCategory==-2){

    $this->db->where("b.categoryid != 4");
  }else {


  }
    if (!empty($value)) {
        $this->db->like('b.title', $value);
    }
    // Latest first
    $this->db->order_by('b.created_date', 'DESC');
    $this->db->order_by('b.id', 'DESC');
     
  
    $this->db->limit(10);

    $query = $this->db->get();

    // Uncomment below line to debug query
    //echo $this->db->last_query(); exit;

    return $query->result_array();
}


  
  public function getLatestBlogs($collegeid)
  {
    $this->db->select('*');
    $this->db->from('blog');
    $this->db->where('t_status','1');
    $this->db->where('college_id',$collegeid);
    $this->db->order_by('created_date', 'DESC');
    $this->db->order_by('id', 'DESC');
    $this->db->limit(10);
    $query = $this->db->get();
    $result = $query->result();
    return $result;

  }
	
	public function getLatestBlogsofCat($categoryId)
  {
    $this->db->select('*');
    $this->db->from('blog');
    $this->db->where('t_status','1');
    $this->db->where('categoryid',$categoryId);
    $this->db->order_by('created_date', 'DESC');
    $this->db->order_by('id', 'DESC');
    $this->db->limit(10);
    $query = $this->db->get();
    $result = $query->result();
    return $result;

  }


  public function getPopularBlogs($collegeid)
  {
    $this->db->select('*');
    $this->db->from('blog');
    $this->db->where('t_status','1');
    $this->db->where('views >', '10');
    $this->db->where('college_id',$collegeid);
    $this->db->order_by('views', 'DESC');
    $this->db->order_by('created_date', 'DESC');
    $this->db->order_by('id', 'DESC');
    $this->db->limit(10);
    $query = $this->db->get();
    $result = $query->result();
    return $result;

  }

  public function getBlogsDetails($blogId)
  {
	 // echo "ddd";exit;
    $this->db->select('b.id as blog_id,b.categoryid,bc.name as category_name,b.title,b.subtitle,b.post_url, b.image,b.description,b.post_rate_date,MONTH( post_rate_date) as month,DAY( post_rate_date) as day,YEAR( post_rate_date) as year,SUBSTRING(`description`,1,90) as short_desc');
    $this->db->join('blog_category bc', 'bc.id = b.categoryid','left');
    $this->db->where('b.id', $blogId);
    $this->db->where('b.t_status',1);
    $query = $this->db->get('blog b');

    //  echo $this->db->last_query(); exit;
    return $query->result();
	//	return $this->db->get('blog b')->result();
  }

    public function getBlogsDetailsByCatId($id)
  {
	 // echo "ddd";exit;
    $this->db->select('b.id as blog_id,b.categoryid,bc.name as category_name,b.title,b.subtitle,b.post_url, b.image,b.description,b.post_rate_date,MONTH( post_rate_date) as month,DAY( post_rate_date) as day,YEAR( post_rate_date) as year,SUBSTRING(`description`,1,90) as short_desc');
    $this->db->join('blog_category bc', 'bc.id = b.categoryid','left');
    $this->db->where('bc.id', $id);
    $this->db->where('b.t_status',1);
    $query = $this->db->get('blog b');

    //  echo $this->db->last_query(); exit;
    return $query->result();
	//	return $this->db->get('blog b')->result();
  }
	
	
  public function increment_view($blogId = '')
  {
	  //echo "testing...";exit;
    if (!empty($blogId)) {
      $this->db->where('id', $blogId);
        $this->db->set('views', 'views+1', FALSE);
        $this->db->set('post_rate_date', date('Y-m-d H:i:s'));
        $this->db->update('blog');
       
  }
  }
  public function relatedBlogs($id,$blog_id) {
//echo "testing...";exit;
		$list = array();
		$Array = explode(',', $id);
		foreach ($Array as $categoryid)
		{
			if( count($list) < 5) {
				$this->db->select('id,title,post_url,categoryid,image,post_rate_date');
				$this->db->where("FIND_IN_SET('$categoryid',categoryid) !=", 0);
				$this->db->where('id!= ', $blog_id);
				$this->db->order_by('id','DESC');
				$this->db->where('t_status','1');
				$this->db->limit(5);
				$data=$this->db->get('blog')->result();
				if(!empty($data)) {
						foreach($data as $value){
						    if(!in_array($value, $list, true)) {
						        $list[]=$value;
						    }
						}
				}
			}
			
		}
		return $list;
	}
  
  public function getBlogCategory()
  {
    $this->db->select('bc.id,bc.name,bc.post_url,b.id as blogid');
		$this->db->where('status','1');
		$this->db->join('blog b','bc.id = b.categoryid','Left');
		$this->db->group_by('bc.id');
		return $this->db->get('blog_category bc')->result_array();
  }
	
	///------
	
	  public function get_Articles($searchCategory = '',$value='')
  {
   $this->db->select('*');
    $this->db->from('blog');
    $this->db->where('image IS NOT NULL');
    $this->db->where('image IS NOT NULL');
    $this->db->where('title IS NOT NULL');
	$this->db->limit(10);
    if(!empty($searchCategory))
    {
    $this->db->where('categoryid',$searchCategory);
    }
    if(!empty($value))
    {
    $this->db->like('title',$value);
    }
    $this->db->order_by('id','desc');
	  //$this->db->limit(5);
    $query = $this->db->get();
    if ($query->num_rows() > 0) {
      return $query->result();
    } else {
      return false;
    }
	
  }
}
