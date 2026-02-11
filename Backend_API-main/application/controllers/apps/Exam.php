<?php

if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST");

class Exam extends CI_Controller
{
    /**
     * Constructor
     *
     * Loads necessary libraries, helpers, and models for the app controller.
     */
    public function __construct()
    {
        parent::__construct();
        $data = json_decode(file_get_contents('php://input'));
		if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
			$data['status'] = 'ok';
			echo json_encode($data);
			exit;
		}
        $this->load->model("apps/Campus_app_model", "", true);
		$this->load->model("apps/Exam_model", "", true);
        $this->load->model("admin/State_model", "", true);
        $this->load->library('Utility');
    }
    /**
     *  To get list of Exam
     */
    public function getExamList()
    {
        // echo "testing...";exit;
        $data = json_decode(file_get_contents('php://input'));

        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data["status"] = "ok";
            echo json_encode($data);
            exit;
        }
         
       // print_r($courseId);exit;

        $result = $this->Campus_app_model->get_ExamList();

        //print_r($result);exit;

        if ($result) {
            
             foreach ($result as $pdf) { {
                  $arr = explode(",",$pdf->questionpaper);
                /*foreach ($arr as $ind => $que) {
                    $updatedQuestionPapers[] = base_url() . "/uploads/questionpaper/" . $que;

                }*/
              // print_r($updatedQuestionPapers);exit;
                //$questionpaper = implode(",", $updatedQuestionPapers);
                //print_r($questionpaper);exit;
                //$arr1 = explode(",",$questionpaper);
                $pdf->questionpaper = base_url() . "/uploads/questionpaper/" . $pdf->questionpaper;
                $pdf->preparation = base_url() . "/uploads/preparation/" . $pdf->preparation;
                $pdf->syllabus = base_url() . "/uploads/syllabus/" . $pdf->syllabus;
                // $pdf->syllabus = base_url() . "/uploads/syllabus/" . $pdf->syllabus;
            }
        }
            
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["response_data"] = $result;
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Data is not found";
        }
        
        echo json_encode($response);
        exit();
    }

    public function getCareerByCategory()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $courseCatId = $data->courseCatId;
            $catName = $this->Campus_app_model->getCatName($courseCatId);
            $careerId = $this->Campus_app_model->getCareerCatId($catName);
            //echo $careerId;exit;
            $result = $this->Campus_app_model->getCareerByCategory($careerId);
			//print_r($result);exit;
			foreach ($result as &$img) {
				//print_r($img['image']);exit;
                //$result[$key]->imageName = $img->image;

                $img['image'] = base_url() . '/uploads/careers/' . $img['image'];
            }
            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["careerslist"] = $result;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }
        echo json_encode($response);
        exit;
    }

    public function getExamsByCategory()
    {
       //echo "ttttt";exit;
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $courseCatId = $data->courseCatId;
            $stateName = isset($data->statename)?$data->statename:'' ;
            $stateId = isset($data->stateId)?$data->stateId:'' ;
            $ac_id = isset($data->ac_id)?$data->ac_id:'' ;
           // print_r($stateName); exit ;
           if(is_array($stateName) && !empty($stateName)){
            //echo"863842";exit;
            $result =  $this->State_model->getStateIDByName($stateName[0]);

            $stateId= $result[0]->id ;  
        

           }
          
            $catName = $this->Campus_app_model->getCatName($courseCatId);
          
		/*	if($catName =='Science'){$catName = "Arts & science";}
		 //print_r($catName);exit;
            $examId = $this->Campus_app_model->getExamCatId($catName);
           // print_r($examId);exit;
            $result = $this->Exam_model->getExamsByCategory($courseCatId,$examId,$stateId);
           //  print_r($result);exit;
           
			   $arr = [];
            foreach ($result as $key => $img) {
                $arr = explode(",",$img->questionpaper);
     
      
      if(!empty($img->questionpaper)){
          foreach ($arr as $ind => $que) {
                   // $updatedQuestionPapers[] = base_url() . "/uploads/questionpaper/" . $que;
                   
                      $updatedQuestionPapers[] = [
                'exam_name' => $img->title,
                'exam_questionpaper' => base_url() . "uploads/questionpaper/" . $que,
            ];

                }
             // print_r($updatedQuestionPapers[0]);exit;
			    $questionpaper = implode(",", $updatedQuestionPapers[0]);
                //print_r($questionpaper);exit;
                $arr1 = explode(",",$questionpaper);

                $result[$key]->questionpaper = $arr1;
      }
                
                //print_r($result[$key]->questionpaper);exit;
                 $result[$key]->image =  base_url() . "/uploads/exams/" .  $result[$key]->image;
				if(!empty( $result[$key]->preparation)){ $result[$key]->preparation = base_url() . "/uploads/preparation/" .  $result[$key]->preparation;}
				if(!empty( $result[$key]->syllabus)){ $result[$key]->syllabus = base_url() . "/uploads/syllabus/" .  $result[$key]->syllabus;}

            }*/
            
            if ($catName == 'Science') {
    $catName = "Arts & science";
}

// Get Exam Category ID
$examId = $this->Campus_app_model->getExamCatId($catName);
//print_r($examId);exit;
// Get exams by category
$result = $this->Exam_model->getExamsByCategory($courseCatId, $examId, $ac_id,$stateId);
// print_r($result);exit;
// Prepare final exam question paper list
foreach ($result as $key => $exam) {
    $updatedQuestionPapers = [];

    // Process question papers
    if (!empty($exam->questionpaper)) {
        $papers = explode(",", $exam->questionpaper);

        foreach ($papers as $paper) {
            $updatedQuestionPapers[] = [
                'exam_name' => $exam->title,
                'exam_questionpaper' => base_url() . "uploads/questionpaper/" . trim($paper),
            ];
        }

        // You were trying to convert only the first paper to string before, now assigning the full array
        $result[$key]->questionpaper = $updatedQuestionPapers;
    }

    // Append base URLs to other file fields
    $result[$key]->image = base_url() . "uploads/exams/" . $exam->image;

    if (!empty($exam->preparation)) {
        $result[$key]->preparation = base_url() . "uploads/preparation/" . $exam->preparation;
    }

    if (!empty($exam->syllabus)) {
        $result[$key]->syllabus = base_url() . "uploads/syllabus/" . $exam->syllabus;
    }
}

// 			foreach($result as &$exam){
			
			 
// 				$exam['image'] =  base_url() . "/uploads/exams/" . $exam['image'];
// 				if(!empty($exam['questionpaper'])){$exam['questionpaper'] = base_url() . "/uploads/questionpaper/" . $exam['questionpaper'];}
// 				if(!empty($exam['preparation'])){$exam['preparation'] = base_url() . "/uploads/preparation/" . $exam['preparation'];}
// 				if(!empty($exam['syllabus'])){$exam['syllabus'] = base_url() . "/uploads/syllabus/" . $exam['syllabus'];}

// 			}
            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["examslist"] = $result;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }
        echo json_encode($response);
        exit;
    }
    
    
    public function getQue_PaperByExamId(){
        // echo "ttttt";exit;
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if ($data) {
            $examId = $data->examId;
         // print_r($examId);exit;
           
            $result = $this->Exam_model->getQue_PaperByExamId($examId);

            $getDocsData = $this->Exam_model->getDocs($examId);
           // print_r($getDocsData);exit;
            foreach ($getDocsData as $key => $doc) {
                $docType = $doc->docs_type;
                $doc_title = $doc->docs_title;
                $documents = explode(',', $doc->documents);
            
                $getDocsData[$key]->documents = array_map(function ($document) use ($docType,$doc_title) { 
                    $filePath = '';
            
                    if ($docType == 'Question Paper' || $docType == 'Question_Paper') {
                        $filePath = base_url() . "uploads/questionpaper/" . trim($document);
                    } elseif ($docType == 'Syllabus') {
                        $filePath = base_url() . "uploads/syllabus/" . trim($document);
                    } elseif ($docType == 'Preparation') {
                        $filePath = base_url() . "uploads/preparation/" . trim($document);
                    }
            
                    return [
                        'docs_title'=> $doc_title,
                        'exam_docs' => $filePath
                        
                ];
                }, $documents);
            
                $getDocsData[$key]->docs_title = $doc->docs_title;
            }
  
              //print_r($getDocs);exit;
			   $arr = [];
          /*  foreach ($result as $key => $img) {
                $arr = explode(",",$img->questionpaper);
                foreach ($arr as $ind => $que) {
                   // print_r($que);exit;
                    $updatedQuestionPapers[] = base_url() . "/uploads/questionpaper/" . $que;

                }
                
                
            
			    $questionpaper = implode(",", $updatedQuestionPapers);
                //print_r($questionpaper);exit;
                $arr1 = explode(",",$questionpaper);

                $result[$key]->questionpaper = $arr1;
                //print_r($result[$key]->questionpaper);exit;
                // $result[$key]->image =  base_url() . "/uploads/exams/" .  $result[$key]->image;
				if(!empty( $result[$key]->preparation)){ $result[$key]->preparation = base_url() . "/uploads/preparation/" .  $result[$key]->preparation;}
				if(!empty( $result[$key]->syllabus)){ $result[$key]->syllabus = base_url() . "/uploads/syllabus/" .  $result[$key]->syllabus;}

            }*/
 foreach ($result as $key => $item) {
    $updatedQuestionPapers = [];

    if (!empty($item->questionpaper)) {
        $arr = explode(",", $item->questionpaper);
        foreach ($arr as $que) {
            $updatedQuestionPapers[] = [
                'exam_name' => $item->title,
                'exam_question_paper' => base_url() . "uploads/questionpaper/" . trim($que),
            ];
        }
        $result[$key]->questionpaper = $updatedQuestionPapers;
    } else {
        $result[$key]->questionpaper = null; 
    }

    if (!empty($item->preparation)) {
        $result[$key]->preparation = base_url() . "uploads/preparation/" . $item->preparation;
    }

    if (!empty($item->syllabus)) {
        $result[$key]->syllabus = base_url() . "uploads/syllabus/" . $item->syllabus;
    }

    if (!empty($item->image)) {
        $result[$key]->image = base_url() . "uploads/exams/" . $item->image;
    }
}
    if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["examslist"] = $result;
                $response["docsData"] = $getDocsData;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }
        echo json_encode($response);
        exit;
    }

    public function getExamNotificationForClg()
    {
        $data = json_decode(file_get_contents('php://input'));

        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data["status"] = "ok";
            echo json_encode($data);
            exit;
        }
        if ($data) {
            $collegeid = $data->collegeId;
            $result = $this->Exam_model->getExamNotificationForClg($collegeid);
            foreach ($result as $key => $img) {
                $result[$key]->imageName = $img->image;

                $result[$key]->image = base_url() . '/uploads/blogs/' . $img->image;
            }
            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["response_data"] = $result;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
            echo json_encode($response);
            exit();
        }
        echo json_encode($response);
        exit;
    }
	
	public function getExamAccepted()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if($data){
        $collegeId = $data->collegeId;
        $SubCategory = $this->Exam_model->getExamAccepted($collegeId);

        if ($SubCategory) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["SubCategory"] = $SubCategory;
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed";
        }
    }
    else
    {
        $response["response_code"] = "500";
         $response["response_message"] = "Data is null";
    }
        echo json_encode($response);
        exit;
    }
	
	
	public function getExamSearch()
    {
		//echo "testing...";exit;
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if($data){
        $searchExam = $data->searchexam;
			
			//print_r($searchExam);exit;
        $Examlist = $this->Exam_model->getExam_search($searchExam);

        if ($Examlist) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["Exams"] = $Examlist;
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed";
        }
    }
    else
    {
        $response["response_code"] = "500";
         $response["response_message"] = "Data is null";
    }
        echo json_encode($response);
        exit;
    }
	
	
	
	public function getArticleSearch()
	{
		//echo "testing...";exit;
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if($data){
			
        $searcharticle = $data->searcharticle;
			
        $articlesearch = $this->Exam_model->getarticle_search($searcharticle);

        if ($articlesearch) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["article"] = $articlesearch;
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed";
        }
    } else {
        $response["response_code"] = "500";
         $response["response_message"] = "Data is null";
    }
        echo json_encode($response);
        exit;
	}
	
	
	
	
	 


	public function getExamDetails()
    {
        $data = json_decode(file_get_contents("php://input"));

        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
       /* if (empty($_SERVER["HTTP_AUTHORIZATION"])) {
            if (
                !is_object($data) ||
                !property_exists($data, "defaultToken") ||
                empty($data->defaultToken)
            ) {
                $response["response_code"] = "401";
                $response["response_message"] =
                    "UNAUTHORIZED: Please provide an access token to continue accessing the API";
                echo json_encode($response);
                exit();
            }
            if ($data->defaultToken !== $this->config->item("defaultToken")) {
                $response["response_code"] = "402";
                $response["response_message"] =
                    "UNAUTHORIZED: Please provide a valid access token to continue accessing the API";
                echo json_encode($response);
                exit();
            }
        } else {
            $headers = apache_request_headers();
            $token = str_replace("Bearer ", "", $headers["Authorization"]);
            $kunci = $this->config->item("jwt_key");
            $userData = JWT::decode($token, $kunci);
            Utility::validateSession($userData->iat, $userData->exp);
            $tokenSession = Utility::tokenSession($userData);
        }*/
        if ($data) {
            $examId = $data->examId;
            $result = $this->Exam_model->getExamDetails($examId);
           //  print_r($result);exit;
            
            $addView = $this->Exam_model->increment_view($examId);
            foreach ($result as $key => $img) {
                $result[$key]->imageName = $img->image;

                $result[$key]->image =
                    base_url() . "/uploads/exams/" . $img->image;
            }

           /* $relatedExams = $this->Exam_model->relatedExams(
                $result[0]->categoryid
            );
            $chunkedRelatedExams = array_chunk($relatedExams, 3);
            $groupedRelatedExams = [];
            foreach ($chunkedRelatedExams as $chunk) {
                $groupedRelatedExams[] = ["relatedExamsSub" => $chunk];
            }
            //print_r($chunkedRelatedExams);exit;
            foreach ($relatedExams as $key => $img) {
                $relatedExams[$key]->imageName = $img->image;

                $relatedExams[$key]->image =
                    base_url() . "/uploads/exams/" . $img->image;
            }*/
            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["examdetails"] = $result;
               /* $response["relatedExams"] = $groupedRelatedExams;*/
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "data is null";
        }
        echo json_encode($response);
        exit();
    }
	
}
