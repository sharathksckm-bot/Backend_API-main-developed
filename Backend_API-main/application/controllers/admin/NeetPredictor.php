<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

/**
 * NEET College Predictor Controller
 * 
 * Advanced NEET UG College Predictor for State and AIQ counseling
 * Supports Safe, Possible, and Dream college classifications
 * 
 * @category   Controllers
 * @package    Admin
 * @subpackage NeetPredictor
 * @version    1.0
 */

if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}

class NeetPredictor extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $data = json_decode(file_get_contents('php://input'));
        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data['status'] = 'ok';
            echo json_encode($data);
            exit;
        }
        $this->load->model("admin/NeetPredictor_model", "", true);
        $this->load->library("Utility");
    }

    /**
     * Get list of all NEET cutoff data with pagination
     */
    public function getNeetCutoffList()
    {
        $data = json_decode(file_get_contents("php://input"));

        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data->status = "ok";
            echo json_encode($data);
            exit();
        }

        if ($data) {
            $headers = apache_request_headers();
            $token = str_replace("Bearer ", "", $headers["Authorization"]);
            $kunci = $this->config->item("jwt_key");
            $userData = JWT::decode($token, $kunci);
            Utility::validateSession($userData->iat, $userData->exp);

            $columns = [
                0 => "id",
                1 => "year",
                2 => "state",
                3 => "counseling_type",
            ];
            
            $limit = $data->length;
            $start = ($data->draw - 1) * $limit;
            $orderColumn = isset($columns[$data->order[0]->column]) ? $columns[$data->order[0]->column] : 'id';
            $orderDir = $data->order[0]->dir;
            
            $totalData = $this->NeetPredictor_model->countAllNeetCutoffs();
            $totalFiltered = $totalData;

            if (!empty($data->search->value)) {
                $search = $data->search->value;
                $totalFiltered = $this->NeetPredictor_model->countFilteredNeetCutoffs($search);
                $cutoffs = $this->NeetPredictor_model->getFilteredNeetCutoffs(
                    $search, $start, $limit, $orderColumn, $orderDir
                );
            } else {
                $cutoffs = $this->NeetPredictor_model->getAllNeetCutoffs(
                    $start, $limit, $orderColumn, $orderDir
                );
            }

            $datas = [];
            foreach ($cutoffs as $cutoff) {
                $nestedData = [];
                $nestedData["id"] = $cutoff->id;
                $nestedData["year"] = $cutoff->year;
                $nestedData["state"] = $cutoff->state;
                $nestedData["counseling_type"] = $cutoff->counseling_type;
                $nestedData["college_name"] = $cutoff->college_name;
                $nestedData["course"] = $cutoff->course;
                $nestedData["category"] = $cutoff->category;
                $nestedData["round"] = $cutoff->round;
                $nestedData["opening_rank"] = $cutoff->opening_rank;
                $nestedData["closing_rank"] = $cutoff->closing_rank;
                $nestedData["college_type"] = $cutoff->college_type;
                $nestedData["annual_fee"] = $cutoff->annual_fee;
                $datas[] = $nestedData;
            }

            $json_data = [
                "draw" => intval($data->draw),
                "recordsTotal" => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data" => $datas,
            ];

            echo json_encode($json_data);
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
            echo json_encode($response);
        }
        exit;
    }

    /**
     * Insert or Update NEET Cutoff data
     */
    public function insertUpdateNeetCutoff()
    {
        $data = json_decode(file_get_contents("php://input"));
        
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        
        if (!isset($_SERVER["HTTP_AUTHORIZATION"])) {
            $response["response_code"] = "401";
            $response["response_message"] = "Unauthorized";
            echo json_encode($response);
            exit();
        }
        
        $headers = apache_request_headers();
        $token = str_replace("Bearer ", "", $headers["Authorization"]);
        $kunci = $this->config->item("jwt_key");
        $userData = JWT::decode($token, $kunci);
        Utility::validateSession($userData->iat, $userData->exp);

        if ($data) {
            $id = isset($data->id) ? $data->id : '';
            
            $arr = [
                "year" => $data->year,
                "state" => $data->state,
                "counseling_type" => $data->counseling_type,
                "college_id" => isset($data->college_id) ? $data->college_id : null,
                "college_name" => $data->college_name,
                "course" => $data->course,
                "category" => $data->category,
                "round" => isset($data->round) ? $data->round : 'Round 1',
                "opening_rank" => $data->opening_rank,
                "closing_rank" => $data->closing_rank,
                "college_type" => isset($data->college_type) ? $data->college_type : 'Government',
                "annual_fee" => isset($data->annual_fee) ? $data->annual_fee : 0
            ];

            if (!empty($id)) {
                $result = $this->NeetPredictor_model->updateNeetCutoff($arr, $id);
            } else {
                $result = $this->NeetPredictor_model->insertNeetCutoff($arr);
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
            $response["response_message"] = "Data is NULL.";
        }

        echo json_encode($response);
        exit();
    }

    /**
     * Get NEET Cutoff by ID
     */
    public function getNeetCutoffById()
    {
        $data = json_decode(file_get_contents("php://input"));
        
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        
        if (!isset($_SERVER["HTTP_AUTHORIZATION"])) {
            $response["response_code"] = "401";
            $response["response_message"] = "Unauthorized";
            echo json_encode($response);
            exit();
        }
        
        $headers = apache_request_headers();
        $token = str_replace("Bearer ", "", $headers["Authorization"]);
        $kunci = $this->config->item("jwt_key");
        $userData = JWT::decode($token, $kunci);
        Utility::validateSession($userData->iat, $userData->exp);

        $id = $data->id;
        $result = $this->NeetPredictor_model->getNeetCutoffById($id);
        
        if ($result) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["response_data"] = $result;
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed";
        }

        echo json_encode($response);
        exit();
    }

    /**
     * Delete NEET Cutoff
     */
    public function deleteNeetCutoff()
    {
        $data = json_decode(file_get_contents("php://input"));
        
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        
        if (!isset($_SERVER["HTTP_AUTHORIZATION"])) {
            $response["response_code"] = "401";
            $response["response_message"] = "Unauthorized";
            echo json_encode($response);
            exit();
        }
        
        $headers = apache_request_headers();
        $token = str_replace("Bearer ", "", $headers["Authorization"]);
        $kunci = $this->config->item("jwt_key");
        $userData = JWT::decode($token, $kunci);
        Utility::validateSession($userData->iat, $userData->exp);

        $id = $data->id;
        $result = $this->NeetPredictor_model->deleteNeetCutoff($id);
        
        if ($result) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["response_data"] = $result;
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed";
        }

        echo json_encode($response);
        exit();
    }

    /**
     * Import NEET Cutoff data from CSV/Excel
     */
    public function importNeetCutoffCsv()
    {
        $data = json_decode(file_get_contents("php://input"));
        
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        
        if (!isset($_SERVER["HTTP_AUTHORIZATION"])) {
            $response["response_code"] = "401";
            $response["response_message"] = "Unauthorized";
            echo json_encode($response);
            exit();
        }
        
        $headers = apache_request_headers();
        $token = str_replace("Bearer ", "", $headers["Authorization"]);
        $kunci = $this->config->item("jwt_key");
        $userData = JWT::decode($token, $kunci);
        Utility::validateSession($userData->iat, $userData->exp);

        $folder = "uploads/csv/neet";
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        if (isset($_FILES["file"]) && $_FILES["file"]["error"] == 0) {
            $allowed = ["csv" => "text/csv"];
            $filename = $_FILES["file"]["name"];
            $filesize = $_FILES["file"]["size"];
            $file_ext = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
            $maxsize = 10 * 1024 * 1024; // 10 MB

            if (!array_key_exists($file_ext, $allowed)) {
                $response["status"] = "false";
                $response["response_code"] = 1;
                $response["response_message"] = "Please select a valid CSV file format.";
            } elseif ($filesize > $maxsize) {
                $response["status"] = "false";
                $response["response_code"] = 2;
                $response["response_message"] = "File size is larger than the allowed limit (10MB)";
            } else {
                $fileNameFinal = time() . "_" . $filename;
                $finalFile = $folder . "/" . $fileNameFinal;
                $upload = move_uploaded_file($_FILES["file"]["tmp_name"], $finalFile);

                if ($upload) {
                    $strFileHandle = fopen($finalFile, "r");
                    $dataRows = [];
                    while (($line_of_text = fgetcsv($strFileHandle, 2048, ",")) !== false) {
                        $dataRows[] = $line_of_text;
                    }
                    fclose($strFileHandle);

                    $fileHeaders = $dataRows[0];
                    $importCount = 0;
                    $updateCount = 0;

                    for ($i = 1; $i < count($dataRows); $i++) {
                        $rowData = array_combine($fileHeaders, $dataRows[$i]);
                        
                        $arr = [
                            "year" => isset($rowData["Year"]) ? $rowData["Year"] : $rowData["year"],
                            "state" => isset($rowData["State"]) ? $rowData["State"] : $rowData["state"],
                            "counseling_type" => isset($rowData["Counseling Type"]) ? $rowData["Counseling Type"] : (isset($rowData["counseling_type"]) ? $rowData["counseling_type"] : 'State Quota'),
                            "college_id" => isset($rowData["College ID"]) ? $rowData["College ID"] : (isset($rowData["college_id"]) ? $rowData["college_id"] : null),
                            "college_name" => isset($rowData["College Name"]) ? $rowData["College Name"] : $rowData["college_name"],
                            "course" => isset($rowData["Course"]) ? $rowData["Course"] : $rowData["course"],
                            "category" => isset($rowData["Category"]) ? $rowData["Category"] : $rowData["category"],
                            "round" => isset($rowData["Round"]) ? $rowData["Round"] : (isset($rowData["round"]) ? $rowData["round"] : 'Round 1'),
                            "opening_rank" => isset($rowData["Opening Rank"]) ? $rowData["Opening Rank"] : $rowData["opening_rank"],
                            "closing_rank" => isset($rowData["Closing Rank"]) ? $rowData["Closing Rank"] : $rowData["closing_rank"],
                            "college_type" => isset($rowData["College Type"]) ? $rowData["College Type"] : (isset($rowData["college_type"]) ? $rowData["college_type"] : 'Government'),
                            "annual_fee" => isset($rowData["Annual Fee"]) ? $rowData["Annual Fee"] : (isset($rowData["annual_fee"]) ? $rowData["annual_fee"] : 0)
                        ];

                        $checkExists = $this->NeetPredictor_model->checkCutoffExists($arr);
                        
                        if ($checkExists > 0) {
                            $this->NeetPredictor_model->updateExistingCutoff($arr);
                            $updateCount++;
                        } else {
                            $this->NeetPredictor_model->insertNeetCutoff($arr);
                            $importCount++;
                        }
                    }

                    $response["response_code"] = "200";
                    $response["response_message"] = "Success";
                    $response["imported_count"] = $importCount;
                    $response["updated_count"] = $updateCount;
                    $response["File"] = $fileNameFinal;
                } else {
                    $response["response_code"] = "400";
                    $response["response_message"] = "Failed to upload file";
                }
            }
        } else {
            $response["status"] = "false";
            $response["response_code"] = 3;
            $response["response_message"] = "Please upload a file";
        }

        echo json_encode($response);
        exit();
    }

    /**
     * Get Sample CSV template for NEET cutoff data
     */
    public function getSampleNeetCsv()
    {
        $data = json_decode(file_get_contents("php://input"));
        
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        
        if (empty($_SERVER["HTTP_AUTHORIZATION"])) {
            $response["response_code"] = "401";
            $response["response_message"] = "Unauthorized";
            echo json_encode($response);
            exit();
        }
        
        $headers = apache_request_headers();
        $token = str_replace("Bearer ", "", $headers["Authorization"]);
        $kunci = $this->config->item("jwt_key");
        $userData = JWT::decode($token, $kunci);
        Utility::validateSession($userData->iat, $userData->exp);

        $csvpath = base_url() . "uploads/samplecsv/SampleNEETCutoff.csv";
        
        $response["response_code"] = "200";
        $response["response_message"] = "Success";
        $response["samplecsv"] = $csvpath;

        echo json_encode($response);
        exit();
    }

    /**
     * Get distinct states list
     */
    public function getStatesList()
    {
        $data = json_decode(file_get_contents("php://input"));
        
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }

        $result = $this->NeetPredictor_model->getDistinctStates();
        
        if ($result) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["response_data"] = $result;
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "No states found";
        }

        echo json_encode($response);
        exit();
    }

    /**
     * Get state counseling rules
     */
    public function getStateRules()
    {
        $data = json_decode(file_get_contents("php://input"));
        
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }

        $state = isset($data->state) ? $data->state : '';
        $result = $this->NeetPredictor_model->getStateRules($state);
        
        if ($result) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["response_data"] = $result;
        } else {
            // Return default rules if not found
            $response["response_code"] = "200";
            $response["response_message"] = "Default rules applied";
            $response["response_data"] = [
                "state" => $state,
                "domicile_required" => true,
                "private_open_seats" => true,
                "notes" => ""
            ];
        }

        echo json_encode($response);
        exit();
    }

    /**
     * NEET College Predictor API - Main prediction endpoint
     * This is the advanced prediction algorithm
     */
    public function predictColleges()
    {
        $data = json_decode(file_get_contents("php://input"));
        
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }

        // Validate required inputs
        if (!isset($data->rank) || !isset($data->category)) {
            $response["response_code"] = "400";
            $response["response_message"] = "Missing required fields: rank, category";
            echo json_encode($response);
            exit();
        }

        $rank = intval($data->rank);
        $category = $data->category;
        $domicile_state = isset($data->domicile_state) ? $data->domicile_state : '';
        $preferred_state = isset($data->preferred_state) ? $data->preferred_state : '';
        $course = isset($data->course) ? $data->course : 'MBBS';
        $counseling_type = isset($data->counseling_type) ? $data->counseling_type : '';
        $college_type_filter = isset($data->college_type) ? $data->college_type : '';
        $max_fee = isset($data->max_fee) ? intval($data->max_fee) : 0;

        // Get latest year cutoff data
        $latestYear = $this->NeetPredictor_model->getLatestYear();

        // Fetch cutoff data based on filters
        $cutoffData = $this->NeetPredictor_model->getCutoffDataForPrediction(
            $latestYear, $preferred_state, $course, $category, $counseling_type
        );

        // Check state eligibility
        $stateRules = $this->NeetPredictor_model->getStateRules($preferred_state);
        $eligibilityWarning = '';
        
        if ($stateRules && $stateRules->domicile_required && $domicile_state !== $preferred_state) {
            $eligibilityWarning = "You may not be eligible for state quota seats in $preferred_state.";
            
            // Filter to only private colleges if domicile mismatch
            if ($stateRules->private_open_seats) {
                $filteredData = array_filter($cutoffData, function($item) {
                    return $item->college_type === 'Private';
                });
                $cutoffData = array_values($filteredData);
            }
        }

        // Prediction algorithm - classify colleges
        $safeColleges = [];
        $possibleColleges = [];
        $dreamColleges = [];

        foreach ($cutoffData as $college) {
            $closingRank = intval($college->closing_rank);
            
            // Skip if closing rank is 0 or null
            if ($closingRank <= 0) continue;
            
            // Apply max fee filter if specified
            if ($max_fee > 0 && intval($college->annual_fee) > $max_fee) continue;
            
            // Apply college type filter if specified
            if (!empty($college_type_filter) && $college_type_filter !== 'Both') {
                if ($college->college_type !== $college_type_filter) continue;
            }

            // Prediction logic based on the specification
            if ($rank <= $closingRank * 0.85) {
                // Safe: High probability
                $college->probability = 'High';
                $college->chance_type = 'Safe';
                $safeColleges[] = $college;
            } elseif ($rank <= $closingRank) {
                // Possible: Medium probability
                $college->probability = 'Medium';
                $college->chance_type = 'Possible';
                $possibleColleges[] = $college;
            } elseif ($rank <= $closingRank * 1.15) {
                // Dream: Low probability
                $college->probability = 'Low';
                $college->chance_type = 'Dream';
                $dreamColleges[] = $college;
            }
            // Else: Not likely - excluded
        }

        // Sort each category by priority: Govt first, then by fee, then by closing rank
        $sortFunction = function($a, $b) {
            // Government colleges first
            $typeOrder = ['Government' => 0, 'Private' => 1, 'Deemed' => 2];
            $typeA = isset($typeOrder[$a->college_type]) ? $typeOrder[$a->college_type] : 3;
            $typeB = isset($typeOrder[$b->college_type]) ? $typeOrder[$b->college_type] : 3;
            
            if ($typeA !== $typeB) return $typeA - $typeB;
            
            // Then by annual fee (ascending)
            $feeA = intval($a->annual_fee);
            $feeB = intval($b->annual_fee);
            if ($feeA !== $feeB) return $feeA - $feeB;
            
            // Then by closing rank (ascending)
            return intval($a->closing_rank) - intval($b->closing_rank);
        };

        usort($safeColleges, $sortFunction);
        usort($possibleColleges, $sortFunction);
        usort($dreamColleges, $sortFunction);

        // Log the prediction request
        $this->NeetPredictor_model->logPredictorUsage([
            'user_id' => isset($data->user_id) ? $data->user_id : null,
            'rank' => $rank,
            'category' => $category,
            'domicile_state' => $domicile_state,
            'preferred_state' => $preferred_state,
            'counseling_type' => $counseling_type,
            'course' => $course
        ]);

        $response["response_code"] = "200";
        $response["response_message"] = "Success";
        $response["cutoff_year"] = $latestYear;
        $response["eligibility_warning"] = $eligibilityWarning;
        $response["safe"] = $safeColleges;
        $response["possible"] = $possibleColleges;
        $response["dream"] = $dreamColleges;
        $response["total_safe"] = count($safeColleges);
        $response["total_possible"] = count($possibleColleges);
        $response["total_dream"] = count($dreamColleges);

        echo json_encode($response);
        exit();
    }

    /**
     * Generate preference list for user
     */
    public function generatePreferenceList()
    {
        $data = json_decode(file_get_contents("php://input"));
        
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }

        if (!isset($data->colleges) || !is_array($data->colleges)) {
            $response["response_code"] = "400";
            $response["response_message"] = "No colleges provided";
            echo json_encode($response);
            exit();
        }

        $preferenceList = [];
        $preference = 1;

        foreach ($data->colleges as $college) {
            $preferenceList[] = [
                'preference' => $preference++,
                'college_name' => $college->college_name,
                'college_type' => $college->college_type,
                'course' => $college->course,
                'closing_rank' => $college->closing_rank,
                'annual_fee' => $college->annual_fee,
                'chance_type' => $college->chance_type
            ];
        }

        $response["response_code"] = "200";
        $response["response_message"] = "Success";
        $response["preference_list"] = $preferenceList;

        echo json_encode($response);
        exit();
    }

    /**
     * Get predictor logs for admin
     */
    public function getPredictorLogs()
    {
        $data = json_decode(file_get_contents("php://input"));
        
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        
        if (!isset($_SERVER["HTTP_AUTHORIZATION"])) {
            $response["response_code"] = "401";
            $response["response_message"] = "Unauthorized";
            echo json_encode($response);
            exit();
        }
        
        $headers = apache_request_headers();
        $token = str_replace("Bearer ", "", $headers["Authorization"]);
        $kunci = $this->config->item("jwt_key");
        $userData = JWT::decode($token, $kunci);
        Utility::validateSession($userData->iat, $userData->exp);

        $limit = isset($data->limit) ? $data->limit : 50;
        $offset = isset($data->offset) ? $data->offset : 0;

        $result = $this->NeetPredictor_model->getPredictorLogs($limit, $offset);
        $total = $this->NeetPredictor_model->countPredictorLogs();
        
        if ($result) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["response_data"] = $result;
            $response["total"] = $total;
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "No logs found";
        }

        echo json_encode($response);
        exit();
    }

    /**
     * Get distinct categories list
     */
    public function getCategoriesList()
    {
        $data = json_decode(file_get_contents("php://input"));
        
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }

        $categories = ['GEN', 'OBC', 'SC', 'ST', 'EWS', 'GM', '2A', '2B', '3A', '3B'];
        
        $response["response_code"] = "200";
        $response["response_message"] = "Success";
        $response["response_data"] = $categories;

        echo json_encode($response);
        exit();
    }

    /**
     * Get counseling types list
     */
    public function getCounselingTypes()
    {
        $data = json_decode(file_get_contents("php://input"));
        
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }

        $counselingTypes = ['State Quota', 'All India Quota', 'Deemed Universities'];
        
        $response["response_code"] = "200";
        $response["response_message"] = "Success";
        $response["response_data"] = $counselingTypes;

        echo json_encode($response);
        exit();
    }
}
