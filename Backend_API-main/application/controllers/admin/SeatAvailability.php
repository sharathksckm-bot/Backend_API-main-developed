<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

/**
 * Seat Availability Controller
 * 
 * Real-time counseling seat availability tracking
 * Supports manual updates and external API integration
 * 
 * @category   Controllers
 * @package    Admin
 * @subpackage SeatAvailability
 * @version    1.0
 */

if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}

class SeatAvailability extends CI_Controller
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
        $this->load->model("admin/SeatAvailability_model", "", true);
        $this->load->library("Utility");
    }

    /**
     * Get seat availability list with pagination
     */
    public function getSeatAvailabilityList()
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
                1 => "college_name",
                2 => "round",
                3 => "counseling_type",
            ];
            
            $limit = $data->length;
            $start = ($data->draw - 1) * $limit;
            $orderColumn = isset($columns[$data->order[0]->column]) ? $columns[$data->order[0]->column] : 'id';
            $orderDir = $data->order[0]->dir;
            
            // Filters
            $filters = [
                'state' => isset($data->state) ? $data->state : '',
                'counseling_type' => isset($data->counseling_type) ? $data->counseling_type : '',
                'round' => isset($data->round) ? $data->round : '',
                'year' => isset($data->year) ? $data->year : date('Y')
            ];
            
            $totalData = $this->SeatAvailability_model->countAllSeats($filters);
            $totalFiltered = $totalData;

            if (!empty($data->search->value)) {
                $search = $data->search->value;
                $totalFiltered = $this->SeatAvailability_model->countFilteredSeats($search, $filters);
                $seats = $this->SeatAvailability_model->getFilteredSeats(
                    $search, $filters, $start, $limit, $orderColumn, $orderDir
                );
            } else {
                $seats = $this->SeatAvailability_model->getAllSeats(
                    $filters, $start, $limit, $orderColumn, $orderDir
                );
            }

            $datas = [];
            foreach ($seats as $seat) {
                $nestedData = [];
                $nestedData["id"] = $seat->id;
                $nestedData["year"] = $seat->year;
                $nestedData["state"] = $seat->state;
                $nestedData["counseling_type"] = $seat->counseling_type;
                $nestedData["college_id"] = $seat->college_id;
                $nestedData["college_name"] = $seat->college_name;
                $nestedData["course"] = $seat->course;
                $nestedData["round"] = $seat->round;
                $nestedData["category"] = $seat->category;
                $nestedData["total_seats"] = $seat->total_seats;
                $nestedData["filled_seats"] = $seat->filled_seats;
                $nestedData["available_seats"] = $seat->available_seats;
                $nestedData["status"] = $seat->status;
                $nestedData["last_updated"] = $seat->last_updated;
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
     * Insert or Update seat availability
     */
    public function insertUpdateSeatAvailability()
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
                "year" => isset($data->year) ? $data->year : date('Y'),
                "state" => $data->state,
                "counseling_type" => $data->counseling_type,
                "college_id" => isset($data->college_id) ? $data->college_id : null,
                "college_name" => $data->college_name,
                "course" => $data->course,
                "round" => $data->round,
                "category" => $data->category,
                "total_seats" => intval($data->total_seats),
                "filled_seats" => intval($data->filled_seats),
                "available_seats" => intval($data->total_seats) - intval($data->filled_seats),
                "status" => isset($data->status) ? $data->status : 'Active',
                "source" => isset($data->source) ? $data->source : 'Manual'
            ];

            if (!empty($id)) {
                $result = $this->SeatAvailability_model->updateSeatAvailability($arr, $id);
            } else {
                $result = $this->SeatAvailability_model->insertSeatAvailability($arr);
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
     * Bulk update seat availability (for external API sync)
     */
    public function bulkUpdateSeats()
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

        if ($data && isset($data->seats) && is_array($data->seats)) {
            $insertCount = 0;
            $updateCount = 0;
            
            foreach ($data->seats as $seat) {
                $arr = [
                    "year" => isset($seat->year) ? $seat->year : date('Y'),
                    "state" => $seat->state,
                    "counseling_type" => $seat->counseling_type,
                    "college_id" => isset($seat->college_id) ? $seat->college_id : null,
                    "college_name" => $seat->college_name,
                    "course" => $seat->course,
                    "round" => $seat->round,
                    "category" => $seat->category,
                    "total_seats" => intval($seat->total_seats),
                    "filled_seats" => intval($seat->filled_seats),
                    "available_seats" => intval($seat->total_seats) - intval($seat->filled_seats),
                    "status" => isset($seat->status) ? $seat->status : 'Active',
                    "source" => isset($seat->source) ? $seat->source : 'API'
                ];

                $exists = $this->SeatAvailability_model->checkSeatExists($arr);
                
                if ($exists) {
                    $this->SeatAvailability_model->updateExistingSeat($arr);
                    $updateCount++;
                } else {
                    $this->SeatAvailability_model->insertSeatAvailability($arr);
                    $insertCount++;
                }
            }

            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["inserted"] = $insertCount;
            $response["updated"] = $updateCount;
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Invalid data format";
        }

        echo json_encode($response);
        exit();
    }

    /**
     * Delete seat availability entry
     */
    public function deleteSeatAvailability()
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
        $result = $this->SeatAvailability_model->deleteSeatAvailability($id);
        
        if ($result) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed";
        }

        echo json_encode($response);
        exit();
    }

    /**
     * Get seat availability by ID
     */
    public function getSeatById()
    {
        $data = json_decode(file_get_contents("php://input"));
        
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }

        $id = $data->id;
        $result = $this->SeatAvailability_model->getSeatById($id);
        
        if ($result) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["response_data"] = $result;
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Not found";
        }

        echo json_encode($response);
        exit();
    }

    /**
     * Get seat availability for mobile app (public endpoint)
     * Returns round-wise and category-wise availability
     */
    public function getPublicSeatAvailability()
    {
        $data = json_decode(file_get_contents("php://input"));
        
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }

        $state = isset($data->state) ? $data->state : '';
        $counseling_type = isset($data->counseling_type) ? $data->counseling_type : '';
        $course = isset($data->course) ? $data->course : 'MBBS';
        $year = isset($data->year) ? $data->year : date('Y');

        // Get all active seats for the given filters
        $seats = $this->SeatAvailability_model->getPublicSeats($state, $counseling_type, $course, $year);

        // Group by round
        $roundWise = [];
        $categoryWise = [];
        $collegeWise = [];

        foreach ($seats as $seat) {
            // Round-wise aggregation
            $round = $seat->round;
            if (!isset($roundWise[$round])) {
                $roundWise[$round] = [
                    'round' => $round,
                    'total_seats' => 0,
                    'filled_seats' => 0,
                    'available_seats' => 0,
                    'colleges_count' => 0
                ];
            }
            $roundWise[$round]['total_seats'] += intval($seat->total_seats);
            $roundWise[$round]['filled_seats'] += intval($seat->filled_seats);
            $roundWise[$round]['available_seats'] += intval($seat->available_seats);
            $roundWise[$round]['colleges_count']++;

            // Category-wise aggregation
            $category = $seat->category;
            if (!isset($categoryWise[$category])) {
                $categoryWise[$category] = [
                    'category' => $category,
                    'total_seats' => 0,
                    'filled_seats' => 0,
                    'available_seats' => 0
                ];
            }
            $categoryWise[$category]['total_seats'] += intval($seat->total_seats);
            $categoryWise[$category]['filled_seats'] += intval($seat->filled_seats);
            $categoryWise[$category]['available_seats'] += intval($seat->available_seats);

            // College-wise list
            $collegeWise[] = [
                'college_name' => $seat->college_name,
                'round' => $seat->round,
                'category' => $seat->category,
                'total_seats' => $seat->total_seats,
                'filled_seats' => $seat->filled_seats,
                'available_seats' => $seat->available_seats,
                'status' => $seat->status,
                'last_updated' => $seat->last_updated
            ];
        }

        // Calculate overall stats
        $totalAvailable = array_sum(array_column($seats, 'available_seats'));
        $totalSeats = array_sum(array_column($seats, 'total_seats'));
        $totalFilled = array_sum(array_column($seats, 'filled_seats'));

        $response["response_code"] = "200";
        $response["response_message"] = "Success";
        $response["year"] = $year;
        $response["state"] = $state;
        $response["counseling_type"] = $counseling_type;
        $response["overview"] = [
            'total_seats' => $totalSeats,
            'filled_seats' => $totalFilled,
            'available_seats' => $totalAvailable,
            'fill_percentage' => $totalSeats > 0 ? round(($totalFilled / $totalSeats) * 100, 1) : 0
        ];
        $response["round_wise"] = array_values($roundWise);
        $response["category_wise"] = array_values($categoryWise);
        $response["colleges"] = $collegeWise;

        echo json_encode($response);
        exit();
    }

    /**
     * Get active counseling rounds
     */
    public function getActiveRounds()
    {
        $data = json_decode(file_get_contents("php://input"));
        
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }

        $state = isset($data->state) ? $data->state : '';
        $counseling_type = isset($data->counseling_type) ? $data->counseling_type : '';
        
        $rounds = $this->SeatAvailability_model->getActiveRounds($state, $counseling_type);

        $response["response_code"] = "200";
        $response["response_message"] = "Success";
        $response["response_data"] = $rounds;

        echo json_encode($response);
        exit();
    }

    /**
     * Import seat availability from CSV
     */
    public function importSeatsCsv()
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

        $folder = "uploads/csv/seats";
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        if (isset($_FILES["file"]) && $_FILES["file"]["error"] == 0) {
            $allowed = ["csv" => "text/csv"];
            $filename = $_FILES["file"]["name"];
            $file_ext = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);

            if (!array_key_exists($file_ext, $allowed)) {
                $response["response_code"] = "400";
                $response["response_message"] = "Please select a valid CSV file format.";
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
                            "year" => isset($rowData["Year"]) ? $rowData["Year"] : date('Y'),
                            "state" => $rowData["State"],
                            "counseling_type" => $rowData["Counseling Type"],
                            "college_name" => $rowData["College Name"],
                            "course" => $rowData["Course"],
                            "round" => $rowData["Round"],
                            "category" => $rowData["Category"],
                            "total_seats" => intval($rowData["Total Seats"]),
                            "filled_seats" => intval($rowData["Filled Seats"]),
                            "available_seats" => intval($rowData["Total Seats"]) - intval($rowData["Filled Seats"]),
                            "status" => isset($rowData["Status"]) ? $rowData["Status"] : 'Active',
                            "source" => 'CSV Import'
                        ];

                        $exists = $this->SeatAvailability_model->checkSeatExists($arr);
                        
                        if ($exists) {
                            $this->SeatAvailability_model->updateExistingSeat($arr);
                            $updateCount++;
                        } else {
                            $this->SeatAvailability_model->insertSeatAvailability($arr);
                            $importCount++;
                        }
                    }

                    $response["response_code"] = "200";
                    $response["response_message"] = "Success";
                    $response["imported_count"] = $importCount;
                    $response["updated_count"] = $updateCount;
                } else {
                    $response["response_code"] = "400";
                    $response["response_message"] = "Failed to upload file";
                }
            }
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Please upload a file";
        }

        echo json_encode($response);
        exit();
    }

    /**
     * Get Sample CSV template for seat availability
     */
    public function getSampleCsv()
    {
        $data = json_decode(file_get_contents("php://input"));
        
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }
        
        $csvpath = base_url() . "uploads/samplecsv/SampleSeatAvailability.csv";
        
        $response["response_code"] = "200";
        $response["response_message"] = "Success";
        $response["samplecsv"] = $csvpath;

        echo json_encode($response);
        exit();
    }
}
