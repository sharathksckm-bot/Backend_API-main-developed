<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Methods: GET, POST');
/**
 * Dashboard Controller
 *
 * @category   Controllers
 * @package    Admin
 * @subpackage Dashboard
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    09 JAN 2024
 * 
 * Class Dashboard displays the counts of all users, colleges, events, and blogs.
 */

 if (!defined('BASEPATH')) exit('No direct script access allowed');

class Dashboard extends CI_Controller {

    /**
     * Constructor
     * 
     * Loads necessary libraries, helpers, and models for the Dashboard controller.
     */
    public function __construct() {
        parent::__construct();

    	$data = json_decode(file_get_contents('php://input'));
		if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
			$data['status'] = 'ok';
			echo json_encode($data);
			exit;
		}

        $this->load->library('session');
        $this->load->helper('url');
        $this->load->model('admin/College_model', '', TRUE);
        $this->load->model('admin/Events_model', '', TRUE);
        $this->load->model('admin/Blogs_model', '', TRUE);
        $this->load->model('admin/Users_model', '', TRUE);
		$this->load->model('admin/Courses_model', '', TRUE);
		$this->load->model('admin/CourseApplication_model','', TRUE);
		$this->load->model('admin/Enquiry_model','', TRUE);
		

    }

    /**
     * Fetches dashboard data including counts of users, colleges, events, and blogs.
     */
    public function getDashboardData() {
        $data = json_decode(file_get_contents('php://input'), true);

        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data['status'] = 'ok';
            echo json_encode($data);
            exit;
        }

        if($data)
        {
        $fromdate = isset($data->fromdate) ? $data->fromdate :'';
        $todate = isset($data->todate) ? $data->todate :'';

        $college_count = $this->College_model->countAllClg('','');
        $events_count = $this->Events_model->getEventsCount();
        $blogs_count = $this->Blogs_model->getBlogCount();
        $user_count = $this->Users_model->getUsersCount();
		$course_enq_count = $this->Courses_model->getCourseEnqCount($fromdate,$todate);
		$enq_count = $this->Enquiry_model->countAllEnquiry($fromdate,$todate);
		$course_app_count = $this->CourseApplication_model->countAllCourseApplication($fromdate,$todate);
		$course_count = $this->Courses_model->countAllCourse();


        if (true) {
            $response['blogs_count'] = $blogs_count;
            $response['user_count'] = $user_count;
            $response['college_count'] = $college_count;
            $response['event_count'] = $events_count; 
			$response['courseEnquiry_count'] = $course_enq_count;
			$response['enquiry_count'] = $enq_count; 
			$response['application_count'] = $course_app_count; 
			$response['courses_count'] = $course_count; 

            $response['response_message'] = 'Success';
            $response['response_code'] = 200;
        } else {
            $response['response_code'] = 400;
            $response['response_message'] = 'Failed';
        }
    }
    else{
        $response['response_code'] = 500;
        $response['response_message'] = 'Data is null';
    }
        echo json_encode($response);
        exit;
    }
}
