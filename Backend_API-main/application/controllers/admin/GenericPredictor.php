<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

/**
 * Generic College Predictor Controller
 * 
 * Supports KCET, COMEDK, and JEE exam cutoffs with reservation-based predictions
 * Handles column-based cutoff data (reservations as columns)
 * 
 * @category   Controllers
 * @package    Admin
 * @subpackage GenericPredictor
 * @version    1.0
 */

if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}

class GenericPredictor extends CI_Controller
{
    // Exam-specific reservation codes
    private $kcetReservations = [
        '1G', '1H', '1K', '1KH', '1R', '1RH',
        '2AG', '2AH', '2AK', '2AKH', '2AR', '2ARH',
        '2BG', '2BH', '2BK', '2BKH', '2BR', '2BRH',
        '3AG', '3AH', '3AK', '3AKH', '3AR', '3ARH',
        '3BG', '3BH', '3BK', '3BKH', '3BR', '3BRH',
        'GM', 'GMH', 'GMK', 'GMKH', 'GMR', 'GMRH',
        'SCG', 'SCH', 'SCK', 'SCKH', 'SCR', 'SCRH',
        'STG', 'STH', 'STK', 'STKH', 'STR', 'STRH'
    ];

    private $comedkReservations = ['GM', 'OBC', 'SC', 'ST'];
    private $jeeReservations = ['GEN', 'OBC-NCL', 'SC', 'ST', 'EWS', 'GEN-PwD', 'OBC-NCL-PwD', 'SC-PwD', 'ST-PwD', 'EWS-PwD'];

    public function __construct()
    {
        parent::__construct();
        $data = json_decode(file_get_contents('php://input'));
        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data['status'] = 'ok';
            echo json_encode($data);
            exit;
        }
        $this->load->model("admin/GenericPredictor_model", "", true);
        $this->load->library("Utility");
    }

    /**
     * Get list of generic cutoff data with pagination
     */
    public function getCutoffList()
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
                1 => "exam_type",
                2 => "year",
                3 => "round",
            ];
            
            $limit = $data->length;
            $start = ($data->draw - 1) * $limit;
            $orderColumn = isset($columns[$data->order[0]->column]) ? $columns[$data->order[0]->column] : 'id';
            $orderDir = $data->order[0]->dir;
            
            $examType = isset($data->exam_type) ? $data->exam_type : '';
            
            $totalData = $this->GenericPredictor_model->countAllCutoffs($examType);
            $totalFiltered = $totalData;

            if (!empty($data->search->value)) {
                $search = $data->search->value;
                $totalFiltered = $this->GenericPredictor_model->countFilteredCutoffs($search, $examType);
                $cutoffs = $this->GenericPredictor_model->getFilteredCutoffs(
                    $search, $start, $limit, $orderColumn, $orderDir, $examType
                );
            } else {
                $cutoffs = $this->GenericPredictor_model->getAllCutoffs(
                    $start, $limit, $orderColumn, $orderDir, $examType
                );
            }

            $datas = [];
            foreach ($cutoffs as $cutoff) {
                $nestedData = [];
                $nestedData["id"] = $cutoff->id;
                $nestedData["exam_type"] = $cutoff->exam_type;
                $nestedData["year"] = $cutoff->year;
                $nestedData["round"] = $cutoff->round;
                $nestedData["category"] = $cutoff->category;
                $nestedData["college_name"] = $cutoff->college_name;
                $nestedData["course"] = $cutoff->course;
                $nestedData["college_type"] = $cutoff->college_type;
                $nestedData["address"] = $cutoff->address;
                $nestedData["accreditation"] = $cutoff->accreditation;
                $nestedData["affiliated_to"] = $cutoff->affiliated_to;
                $nestedData["url"] = $cutoff->url;
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
     * Insert or Update Generic Cutoff data
     */
    public function insertUpdateCutoff()
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
            
            // Main cutoff data
            $arr = [
                "exam_type" => $data->exam_type,
                "year" => $data->year,
                "round" => isset($data->round) ? $data->round : 'Round 1',
                "category" => $data->category,
                "college_name" => $data->college_name,
                "course" => $data->course,
                "college_type" => isset($data->college_type) ? $data->college_type : 1,
                "address" => isset($data->address) ? $data->address : '',
                "url" => isset($data->url) ? $data->url : '',
                "accreditation" => isset($data->accreditation) ? $data->accreditation : '',
                "affiliated_to" => isset($data->affiliated_to) ? $data->affiliated_to : '',
                "cutoff_data" => isset($data->cutoff_data) ? json_encode($data->cutoff_data) : '{}'
            ];

            if (!empty($id)) {
                $result = $this->GenericPredictor_model->updateCutoff($arr, $id);
            } else {
                $result = $this->GenericPredictor_model->insertCutoff($arr);
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
     * Get Generic Cutoff by ID
     */
    public function getCutoffById()
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
        $result = $this->GenericPredictor_model->getCutoffById($id);
        
        if ($result) {
            // Decode cutoff_data JSON
            if (isset($result->cutoff_data)) {
                $result->cutoff_data = json_decode($result->cutoff_data, true);
            }
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
     * Delete Generic Cutoff
     */
    public function deleteCutoff()
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
        $result = $this->GenericPredictor_model->deleteCutoff($id);
        
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
     * Import Cutoff data from Excel (KCET format with reservations as columns)
     */
    public function importCutoffExcel()
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

        $folder = "uploads/csv/generic";
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        $examType = isset($_POST['exam_type']) ? $_POST['exam_type'] : 'KCET';
        $year = isset($_POST['year']) ? $_POST['year'] : date('Y');
        $round = isset($_POST['round']) ? $_POST['round'] : 'Round 1';

        if (isset($_FILES["file"]) && $_FILES["file"]["error"] == 0) {
            $allowed = ["csv" => "text/csv", "xlsx" => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", "xls" => "application/vnd.ms-excel"];
            $filename = $_FILES["file"]["name"];
            $filesize = $_FILES["file"]["size"];
            $file_ext = strtolower(pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION));
            $maxsize = 20 * 1024 * 1024; // 20 MB

            if (!in_array($file_ext, ['csv', 'xlsx', 'xls'])) {
                $response["status"] = "false";
                $response["response_code"] = 1;
                $response["response_message"] = "Please select a valid CSV or Excel file format.";
                echo json_encode($response);
                exit();
            }
            
            if ($filesize > $maxsize) {
                $response["status"] = "false";
                $response["response_code"] = 2;
                $response["response_message"] = "File size is larger than the allowed limit (20MB)";
                echo json_encode($response);
                exit();
            }

            $fileNameFinal = time() . "_" . $filename;
            $finalFile = $folder . "/" . $fileNameFinal;
            $upload = move_uploaded_file($_FILES["file"]["tmp_name"], $finalFile);

            if ($upload) {
                $importCount = 0;
                $updateCount = 0;

                // Load PhpSpreadsheet if available, otherwise use CSV
                if ($file_ext === 'csv') {
                    $result = $this->importFromCSV($finalFile, $examType, $year, $round);
                } else {
                    $result = $this->importFromExcel($finalFile, $examType, $year, $round);
                }

                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["imported_count"] = $result['imported'];
                $response["updated_count"] = $result['updated'];
                $response["File"] = $fileNameFinal;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed to upload file";
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
     * Import from CSV file
     */
    private function importFromCSV($filePath, $examType, $year, $round)
    {
        $strFileHandle = fopen($filePath, "r");
        $dataRows = [];
        while (($line_of_text = fgetcsv($strFileHandle, 4096, ",")) !== false) {
            $dataRows[] = $line_of_text;
        }
        fclose($strFileHandle);

        return $this->processImportData($dataRows, $examType, $year, $round);
    }

    /**
     * Import from Excel file using simple parsing
     */
    private function importFromExcel($filePath, $examType, $year, $round)
    {
        // Try to use PhpSpreadsheet if available
        if (class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
            require_once 'vendor/autoload.php';
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $dataRows = $worksheet->toArray();
            return $this->processImportData($dataRows, $examType, $year, $round);
        }

        // Fallback: Convert Excel to CSV using system command if available
        $csvPath = str_replace(['.xlsx', '.xls'], '.csv', $filePath);
        
        // Use LibreOffice or ssconvert if available
        $converted = false;
        if (function_exists('shell_exec')) {
            // Try ssconvert (gnumeric)
            $output = shell_exec("ssconvert '$filePath' '$csvPath' 2>&1");
            if (file_exists($csvPath)) {
                $converted = true;
            } else {
                // Try LibreOffice
                $output = shell_exec("libreoffice --headless --convert-to csv --outdir '" . dirname($filePath) . "' '$filePath' 2>&1");
                if (file_exists($csvPath)) {
                    $converted = true;
                }
            }
        }

        if ($converted) {
            return $this->importFromCSV($csvPath, $examType, $year, $round);
        }

        return ['imported' => 0, 'updated' => 0, 'error' => 'Excel parsing not available'];
    }

    /**
     * Process import data (common for CSV and Excel)
     */
    private function processImportData($dataRows, $examType, $year, $round)
    {
        if (count($dataRows) < 2) {
            return ['imported' => 0, 'updated' => 0];
        }

        $fileHeaders = array_map('trim', $dataRows[0]);
        $importCount = 0;
        $updateCount = 0;

        // Map headers to our expected columns
        $headerMap = $this->mapHeaders($fileHeaders);

        // Get reservation columns (columns after the main data columns)
        $reservationCols = $this->getReservationColumns($fileHeaders, $examType);

        for ($i = 1; $i < count($dataRows); $i++) {
            if (count($dataRows[$i]) < 3) continue; // Skip invalid rows
            
            $rowData = [];
            foreach ($fileHeaders as $idx => $header) {
                $rowData[$header] = isset($dataRows[$i][$idx]) ? trim($dataRows[$i][$idx]) : '';
            }

            // Build cutoff_data JSON from reservation columns
            $cutoffData = [];
            foreach ($reservationCols as $resCode) {
                $value = isset($rowData[$resCode]) ? $rowData[$resCode] : '';
                if ($value !== '' && $value !== '--' && $value !== 'NA' && is_numeric(str_replace(',', '', $value))) {
                    $cutoffData[$resCode] = intval(str_replace(',', '', $value));
                }
            }

            $arr = [
                "exam_type" => $examType,
                "year" => $year,
                "round" => $round,
                "category" => $this->getColumnValue($rowData, $headerMap, 'category'),
                "college_name" => $this->getColumnValue($rowData, $headerMap, 'college_name'),
                "course" => $this->getColumnValue($rowData, $headerMap, 'course'),
                "college_type" => $this->getColumnValue($rowData, $headerMap, 'college_type') == '2' ? 2 : 1,
                "address" => $this->getColumnValue($rowData, $headerMap, 'address'),
                "url" => $this->getColumnValue($rowData, $headerMap, 'url'),
                "accreditation" => $this->getColumnValue($rowData, $headerMap, 'accreditation'),
                "affiliated_to" => $this->getColumnValue($rowData, $headerMap, 'affiliated_to'),
                "cutoff_data" => json_encode($cutoffData)
            ];

            // Skip if no college name or course
            if (empty($arr['college_name']) || empty($arr['course'])) continue;

            $checkExists = $this->GenericPredictor_model->checkCutoffExists($arr);
            
            if ($checkExists > 0) {
                $this->GenericPredictor_model->updateExistingCutoff($arr);
                $updateCount++;
            } else {
                $this->GenericPredictor_model->insertCutoff($arr);
                $importCount++;
            }
        }

        return ['imported' => $importCount, 'updated' => $updateCount];
    }

    /**
     * Map headers to expected column names
     */
    private function mapHeaders($headers)
    {
        $map = [];
        $headerLower = array_map('strtolower', $headers);
        
        $mapping = [
            'category' => ['category', 'stream', 'type'],
            'college_name' => ['college name', 'college_name', 'collegename', 'institute', 'institution'],
            'course' => ['course', 'branch', 'program', 'discipline'],
            'college_type' => ['college type', 'college_type', 'collegetype', 'type'],
            'address' => ['address', 'location'],
            'url' => ['url', 'link', 'website'],
            'accreditation' => ['accreditation', 'naac', 'nba'],
            'affiliated_to' => ['affiliated to', 'affiliated_to', 'affiliatedto', 'university', 'affiliation']
        ];

        foreach ($mapping as $key => $possibleNames) {
            foreach ($possibleNames as $name) {
                $idx = array_search($name, $headerLower);
                if ($idx !== false) {
                    $map[$key] = $headers[$idx];
                    break;
                }
            }
        }

        return $map;
    }

    /**
     * Get reservation columns from headers
     */
    private function getReservationColumns($headers, $examType)
    {
        $reservations = [];
        
        switch (strtoupper($examType)) {
            case 'KCET':
                $reservations = $this->kcetReservations;
                break;
            case 'COMEDK':
                $reservations = $this->comedkReservations;
                break;
            case 'JEE':
                $reservations = $this->jeeReservations;
                break;
            default:
                $reservations = $this->kcetReservations;
        }

        $foundReservations = [];
        foreach ($headers as $header) {
            $cleanHeader = trim(strtoupper($header));
            if (in_array($cleanHeader, array_map('strtoupper', $reservations))) {
                $foundReservations[] = $header;
            }
        }

        return $foundReservations;
    }

    /**
     * Get column value from row data
     */
    private function getColumnValue($rowData, $headerMap, $key)
    {
        if (isset($headerMap[$key]) && isset($rowData[$headerMap[$key]])) {
            return $rowData[$headerMap[$key]];
        }
        return '';
    }

    /**
     * Get Sample CSV template
     */
    public function getSampleCsv()
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

        $examType = isset($data->exam_type) ? $data->exam_type : 'KCET';
        $csvpath = base_url() . "uploads/samplecsv/SampleGeneric" . $examType . "Cutoff.csv";
        
        $response["response_code"] = "200";
        $response["response_message"] = "Success";
        $response["samplecsv"] = $csvpath;

        echo json_encode($response);
        exit();
    }

    /**
     * Get available exam types
     */
    public function getExamTypes()
    {
        $data = json_decode(file_get_contents("php://input"));
        
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }

        $examTypes = [
            ['code' => 'KCET', 'name' => 'Karnataka CET (KCET)', 'state' => 'Karnataka'],
            ['code' => 'COMEDK', 'name' => 'COMEDK UGET', 'state' => 'Karnataka'],
            ['code' => 'JEE', 'name' => 'JEE Main', 'state' => 'All India']
        ];
        
        $response["response_code"] = "200";
        $response["response_message"] = "Success";
        $response["response_data"] = $examTypes;

        echo json_encode($response);
        exit();
    }

    /**
     * Get reservations for an exam type
     */
    public function getReservations()
    {
        $data = json_decode(file_get_contents("php://input"));
        
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }

        $examType = isset($data->exam_type) ? strtoupper($data->exam_type) : 'KCET';
        $reservations = [];
        
        switch ($examType) {
            case 'KCET':
                $reservations = [
                    ['code' => 'GM', 'name' => 'General Merit', 'group' => 'General'],
                    ['code' => 'GMH', 'name' => 'General Merit - HK', 'group' => 'General'],
                    ['code' => 'GMK', 'name' => 'General Merit - Kannada Medium', 'group' => 'General'],
                    ['code' => 'GMR', 'name' => 'General Merit - Rural', 'group' => 'General'],
                    ['code' => '1G', 'name' => 'Category 1 - General', 'group' => 'Category 1'],
                    ['code' => '2AG', 'name' => 'Category 2A - General', 'group' => 'Category 2A'],
                    ['code' => '2BG', 'name' => 'Category 2B - General', 'group' => 'Category 2B'],
                    ['code' => '3AG', 'name' => 'Category 3A - General', 'group' => 'Category 3A'],
                    ['code' => '3BG', 'name' => 'Category 3B - General', 'group' => 'Category 3B'],
                    ['code' => 'SCG', 'name' => 'SC - General', 'group' => 'SC'],
                    ['code' => 'STG', 'name' => 'ST - General', 'group' => 'ST']
                ];
                break;
            case 'COMEDK':
                $reservations = [
                    ['code' => 'GM', 'name' => 'General Merit', 'group' => 'General'],
                    ['code' => 'OBC', 'name' => 'OBC', 'group' => 'Reserved'],
                    ['code' => 'SC', 'name' => 'SC', 'group' => 'Reserved'],
                    ['code' => 'ST', 'name' => 'ST', 'group' => 'Reserved']
                ];
                break;
            case 'JEE':
                $reservations = [
                    ['code' => 'GEN', 'name' => 'General', 'group' => 'General'],
                    ['code' => 'OBC-NCL', 'name' => 'OBC Non-Creamy Layer', 'group' => 'Reserved'],
                    ['code' => 'SC', 'name' => 'SC', 'group' => 'Reserved'],
                    ['code' => 'ST', 'name' => 'ST', 'group' => 'Reserved'],
                    ['code' => 'EWS', 'name' => 'EWS', 'group' => 'Reserved'],
                    ['code' => 'GEN-PwD', 'name' => 'General PwD', 'group' => 'PwD'],
                    ['code' => 'OBC-NCL-PwD', 'name' => 'OBC NCL PwD', 'group' => 'PwD'],
                    ['code' => 'SC-PwD', 'name' => 'SC PwD', 'group' => 'PwD'],
                    ['code' => 'ST-PwD', 'name' => 'ST PwD', 'group' => 'PwD'],
                    ['code' => 'EWS-PwD', 'name' => 'EWS PwD', 'group' => 'PwD']
                ];
                break;
        }
        
        $response["response_code"] = "200";
        $response["response_message"] = "Success";
        $response["response_data"] = $reservations;

        echo json_encode($response);
        exit();
    }

    /**
     * Get available years for an exam
     */
    public function getYears()
    {
        $data = json_decode(file_get_contents("php://input"));
        
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }

        $examType = isset($data->exam_type) ? $data->exam_type : '';
        $result = $this->GenericPredictor_model->getDistinctYears($examType);
        
        if ($result) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["response_data"] = $result;
        } else {
            $response["response_code"] = "200";
            $response["response_message"] = "No data";
            $response["response_data"] = [];
        }

        echo json_encode($response);
        exit();
    }

    /**
     * Get available rounds for an exam and year
     */
    public function getRounds()
    {
        $data = json_decode(file_get_contents("php://input"));
        
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }

        $examType = isset($data->exam_type) ? $data->exam_type : '';
        $year = isset($data->year) ? $data->year : '';
        $result = $this->GenericPredictor_model->getDistinctRounds($examType, $year);
        
        if ($result) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["response_data"] = $result;
        } else {
            $response["response_code"] = "200";
            $response["response_message"] = "No data";
            $response["response_data"] = [];
        }

        echo json_encode($response);
        exit();
    }

    /**
     * Get categories for an exam
     */
    public function getCategories()
    {
        $data = json_decode(file_get_contents("php://input"));
        
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }

        $examType = isset($data->exam_type) ? $data->exam_type : '';
        $result = $this->GenericPredictor_model->getDistinctCategories($examType);
        
        if ($result) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["response_data"] = $result;
        } else {
            $response["response_code"] = "200";
            $response["response_message"] = "No data";
            $response["response_data"] = [];
        }

        echo json_encode($response);
        exit();
    }

    /**
     * Generic College Predictor API - Main prediction endpoint
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
        if (!isset($data->exam_type) || !isset($data->rank) || !isset($data->reservation)) {
            $response["response_code"] = "400";
            $response["response_message"] = "Missing required fields: exam_type, rank, reservation";
            echo json_encode($response);
            exit();
        }

        $examType = strtoupper($data->exam_type);
        $rank = intval($data->rank);
        $reservation = $data->reservation;
        $category = isset($data->category) ? $data->category : '';
        $course = isset($data->course) ? $data->course : '';
        $year = isset($data->year) ? $data->year : '';
        $round = isset($data->round) ? $data->round : '';
        $collegeTypeFilter = isset($data->college_type) ? $data->college_type : '';

        // Get latest year if not specified
        if (empty($year)) {
            $year = $this->GenericPredictor_model->getLatestYear($examType);
        }

        // Get latest round if not specified
        if (empty($round)) {
            $round = $this->GenericPredictor_model->getLatestRound($examType, $year);
        }

        // Fetch cutoff data
        $cutoffData = $this->GenericPredictor_model->getCutoffDataForPrediction(
            $examType, $year, $round, $category, $course
        );

        // Prediction algorithm - classify colleges
        $safeColleges = [];
        $possibleColleges = [];
        $dreamColleges = [];

        foreach ($cutoffData as $college) {
            $cutoffJson = json_decode($college->cutoff_data, true);
            
            // Get closing rank for the user's reservation
            $closingRank = 0;
            if (isset($cutoffJson[$reservation])) {
                $closingRank = intval($cutoffJson[$reservation]);
            }
            
            // Skip if no closing rank for this reservation
            if ($closingRank <= 0) continue;
            
            // Apply college type filter if specified
            if (!empty($collegeTypeFilter) && $collegeTypeFilter != 'Both') {
                $collegeType = $college->college_type == 1 ? 'Government' : 'Private';
                if ($collegeType !== $collegeTypeFilter) continue;
            }

            // Create result object
            $resultCollege = [
                'id' => $college->id,
                'college_name' => $college->college_name,
                'course' => $college->course,
                'category' => $college->category,
                'college_type' => $college->college_type == 1 ? 'Government' : 'Private',
                'address' => $college->address,
                'url' => $college->url,
                'accreditation' => $college->accreditation,
                'affiliated_to' => $college->affiliated_to,
                'closing_rank' => $closingRank,
                'reservation' => $reservation,
                'round' => $college->round
            ];

            // Prediction logic based on Safe/Possible/Dream classification
            if ($rank <= $closingRank * 0.85) {
                // Safe: High probability (rank is well within cutoff)
                $resultCollege['probability'] = 'High';
                $resultCollege['chance_type'] = 'Safe';
                $safeColleges[] = $resultCollege;
            } elseif ($rank <= $closingRank) {
                // Possible: Medium probability (rank is within cutoff)
                $resultCollege['probability'] = 'Medium';
                $resultCollege['chance_type'] = 'Possible';
                $possibleColleges[] = $resultCollege;
            } elseif ($rank <= $closingRank * 1.15) {
                // Dream: Low probability (rank is slightly above cutoff)
                $resultCollege['probability'] = 'Low';
                $resultCollege['chance_type'] = 'Dream';
                $dreamColleges[] = $resultCollege;
            }
        }

        // Sort each category: Government first, then by closing rank
        $sortFunction = function($a, $b) {
            // Government colleges first
            $typeA = $a['college_type'] === 'Government' ? 0 : 1;
            $typeB = $b['college_type'] === 'Government' ? 0 : 1;
            
            if ($typeA !== $typeB) return $typeA - $typeB;
            
            // Then by closing rank (ascending)
            return $a['closing_rank'] - $b['closing_rank'];
        };

        usort($safeColleges, $sortFunction);
        usort($possibleColleges, $sortFunction);
        usort($dreamColleges, $sortFunction);

        // Log the prediction request
        $this->GenericPredictor_model->logPredictorUsage([
            'user_id' => isset($data->user_id) ? $data->user_id : null,
            'exam_type' => $examType,
            'rank' => $rank,
            'reservation' => $reservation,
            'category' => $category,
            'course' => $course,
            'year' => $year,
            'round' => $round
        ]);

        $response["response_code"] = "200";
        $response["response_message"] = "Success";
        $response["exam_type"] = $examType;
        $response["cutoff_year"] = $year;
        $response["cutoff_round"] = $round;
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
                'chance_type' => $college->chance_type,
                'reservation' => $college->reservation
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
        $examType = isset($data->exam_type) ? $data->exam_type : '';

        $result = $this->GenericPredictor_model->getPredictorLogs($limit, $offset, $examType);
        $total = $this->GenericPredictor_model->countPredictorLogs($examType);
        
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
     * Get distinct courses for an exam
     */
    public function getCourses()
    {
        $data = json_decode(file_get_contents("php://input"));
        
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }

        $examType = isset($data->exam_type) ? $data->exam_type : '';
        $category = isset($data->category) ? $data->category : '';
        $result = $this->GenericPredictor_model->getDistinctCourses($examType, $category);
        
        if ($result) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["response_data"] = $result;
        } else {
            $response["response_code"] = "200";
            $response["response_message"] = "No data";
            $response["response_data"] = [];
        }

        echo json_encode($response);
        exit();
    }

    /**
     * Delete all cutoffs for an exam type, year, and round
     */
    public function deleteBulkCutoffs()
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

        $examType = isset($data->exam_type) ? $data->exam_type : '';
        $year = isset($data->year) ? $data->year : '';
        $round = isset($data->round) ? $data->round : '';

        if (empty($examType)) {
            $response["response_code"] = "400";
            $response["response_message"] = "exam_type is required";
            echo json_encode($response);
            exit();
        }

        $result = $this->GenericPredictor_model->deleteBulkCutoffs($examType, $year, $round);
        
        if ($result) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["deleted_count"] = $result;
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed to delete";
        }

        echo json_encode($response);
        exit();
    }
}
