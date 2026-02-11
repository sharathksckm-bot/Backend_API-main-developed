<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Methods: GET, POST');
if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}

class Exam extends CI_Controller
{
    /**
     * Constructor
     *
     * Loads necessary libraries, helpers, and models for the Blogs controller.
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model("web/Exam_model", "", true);
        $this->load->library("Utility");
    }

    public function getExamNotificationForClg()
    {
        $data = json_decode(file_get_contents("php://input"));

        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if (empty($_SERVER["HTTP_AUTHORIZATION"])) {
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
        }
        if ($data) {
            $collegeid = $data->collegeid;
            $result = $this->Exam_model->getExamNotificationForClg($collegeid);
            foreach ($result as $key => $img) {
                $result[$key]->imageName = $img->image;

                $result[$key]->image =
                    base_url() . "/uploads/blogs/" . $img->image;
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
        exit();
    }

    public function getExamList_bkp()
    {
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        $data = json_decode(file_get_contents("php://input"));
         
        if (empty($_SERVER["HTTP_AUTHORIZATION"])) {
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
        }


        if ($data) {
            $value = isset($data->value) ? $data->value : "";
            $result = $this->Exam_model->getExamList($value);
            
            
            $arr = [];
            foreach ($result as $key => $img) {
                if(!empty($img->questionpaper) || $img->questionpaper != NULL){
                $arr = explode(",",$img->questionpaper);
                foreach ($arr as $ind => $que) {
                    $updatedQuestionPapers[] = base_url() . "/uploads/questionpaper/" . $que;

                }
                $questionpaper = implode(",", $updatedQuestionPapers);
                $arr1 = explode(",",$questionpaper);
                $result[$key]->questionpaper = $arr1;

              
                $examId = $result[$key]->id;
                $getDocsData = $this->Exam_model->getDocsData($examId);

                foreach ($getDocsData as $key1 => $doc) {
                    $docType = $doc['docs_type'];
                    $documents = explode(',', $doc['documents']);
        
                    $getDocsData[$key1]['documents'] = array_map(function ($file) use ($docType) {
                        $filePath = '';
        
                        if ($docType == 'Question_Paper' || $docType == 'Question Paper') {
                            $filePath = base_url() . "uploads/questionpaper/" . trim($file);
                        }
                 
        
                        return ['path' => $filePath];
                    }, $documents);
        
                    $getDocsData[$key1]['docs_title'] = $doc['docs_title'];
                }

                $result[$key]->documents = $getDocsData; 
                
                }
                $result[$key]->imageName = $img->image;
                $result[$key]->image =
                    base_url() . "/uploads/exams/" . $img->image;
                $result[$key]->questionpaper_Name = $img->questionpaper;
               
                $result[$key]->preparationName = $img->preparation;
                $result[$key]->preparation =
                    base_url() . "/uploads/preparation/" . $img->preparation;
                $result[$key]->syllabusName = $img->syllabus;
                $result[$key]->syllabus =
                    base_url() . "/uploads/syllabus/" . $img->syllabus;
            
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
            $response["response_message"] = "Failed";
        }
        echo json_encode($response);
        exit();
    }
public function getExamList()
{
    if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
        $data["status"] = "ok";
        echo json_encode($data);
        exit();
    }

    $data = json_decode(file_get_contents("php://input"));

    if (empty($_SERVER["HTTP_AUTHORIZATION"])) {
        if (
            !is_object($data) ||
            !property_exists($data, "defaultToken") ||
            empty($data->defaultToken)
        ) {
            $response["response_code"] = "401";
            $response["response_message"] = "UNAUTHORIZED: Please provide an access token to continue accessing the API";
            echo json_encode($response);
            exit();
        }
        if ($data->defaultToken !== $this->config->item("defaultToken")) {
            $response["response_code"] = "402";
            $response["response_message"] = "UNAUTHORIZED: Please provide a valid access token to continue accessing the API";
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
    }

    if ($data) {
        $value = isset($data->value) ? $data->value : "";
        $result = $this->Exam_model->getExamList($value);

        foreach ($result as $key => $img) {
            // Initialize per exam
            $updatedQuestionPapers = [];

            // Process Question Paper URLs
            if (!empty($img->questionpaper)) {
                $arr = explode(",", $img->questionpaper);
                foreach ($arr as $que) {
                    $updatedQuestionPapers[] = base_url("uploads/questionpaper/" . trim($que));
                }
                $result[$key]->questionpaper = $updatedQuestionPapers;
            } else {
                $result[$key]->questionpaper = [];
            }

            // Get Documents related to exam ID
            $examId = $img->id;
            $getDocsData = $this->Exam_model->getDocsData($examId);

            foreach ($getDocsData as $key1 => $doc) {
                $docType = $doc['docs_type'];
                $documents = explode(',', $doc['documents']);

                $getDocsData[$key1]['documents'] = array_map(function ($file) use ($docType) {
                    $filePath = '';

                    if ($docType == 'Question_Paper' || $docType == 'Question Paper') {
                        $filePath = base_url("uploads/questionpaper/" . trim($file));
                    }

                    return ['path' => $filePath];
                }, $documents);

                $getDocsData[$key1]['docs_title'] = $doc['docs_title'];
            }

            $result[$key]->documents = $getDocsData;

            // Other resources
            $result[$key]->imageName = $img->image;
            $result[$key]->image = base_url("uploads/exams/" . $img->image);

            $result[$key]->questionpaper_Name = $img->questionpaper;

            $result[$key]->preparationName = $img->preparation;
            $result[$key]->preparation = base_url("uploads/preparation/" . $img->preparation);

            $result[$key]->syllabusName = $img->syllabus;
            $result[$key]->syllabus = base_url("uploads/syllabus/" . $img->syllabus);
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
        $response["response_message"] = "Failed";
    }

    echo json_encode($response);
    exit();
}

    public function getExamDetails()
    {
        $data = json_decode(file_get_contents("php://input"));

        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        if (empty($_SERVER["HTTP_AUTHORIZATION"])) {
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
        }
        if ($data) {
            $examId = $data->examId;
            $result = $this->Exam_model->getExamDetails($examId);
            // print_r($result);exit;
            $getDocsData = $this->Exam_model->getDocsData($examId);

            foreach ($getDocsData as $key => $doc) {
        
                $docType = $doc['docs_type'];
               // print_r($docType . '---');
                $documents = explode(',', $doc['documents']);
          
                $getDocsData[$key]['documents'] = array_map(function ($documents) use ($docType) {
                    if ($docType == 'Question_Paper' || $docType == 'Question Paper') {
                        $filePath = base_url() . "uploads/questionpaper/" . trim($documents);
                    } elseif ($docType == 'Syllabus') {
                        $filePath = base_url() . "uploads/syllabus/" . trim($documents);
                    } elseif ($docType == 'Preparation') {
                        $filePath = base_url() . "uploads/preparation/" . trim($documents);
                    }
                    return [
                        'path' => $filePath
                    ];
                }, $documents);
            
                $getDocsData[$key]['docs_title'] = $doc['docs_title']; 
            }

        
            $addView = $this->Exam_model->increment_view($examId);
            foreach ($result as $key => $img) {
                
                $result[$key]->imageName = $img->image;

                $result[$key]->image =
                    base_url() . "/uploads/exams/" . $img->image;

            }
            
                $questionPapers = $this->Exam_model->get_question_papers($examId);  // ← custom method
                        

  $questionPaperUrls = [];
foreach ($questionPapers as $qp) {
    $questionPaperUrls[] = [
        'name' => $qp->questionpaper,
        'path' => base_url() . "uploads/questionpaper/" . $qp->questionpaper
    ];
}

$result[$key]->questionpaper = $questionPaperUrls;

            $relatedExams = $this->Exam_model->relatedExams(
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
            }
            if ($result || $relatedExams) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["examdetails"] = $result;
                $response["docsData"] = $getDocsData;
                $response["relatedExams"] = $groupedRelatedExams;
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
