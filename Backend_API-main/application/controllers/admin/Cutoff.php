<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST");
/**
 * Cutoff Controller
 *
 * @category   Controllers
 * @package    Admin
 * @subpackage Cutoff
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    22 APRIL 2024
 *
 * Class Cutoff handles all the operations related to displaying list, creating Cutoff, update, and delete of exames like (KCET,COMEDK,JEE )
 */

if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}
class Cutoff extends CI_Controller
{
    /**
     * Constructor
     *
     * Loads necessary libraries, helpers, and models for the Cutoff controller.
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
        $this->load->model("admin/Cutoff_model", "", true);
        $this->load->library("Utility");
    }

    /*** insert KCET Cutoff */
    public function insertKCETCutoff()
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
        $tokenSession = Utility::tokenSession($userData);
        $variableNames = [
            "college_id" => $data->college_id,
            "round" => $data->round,
            "course_id" => isset($data->course_id) ? $data->course_id : "",
            "category_id" => isset($data->category_id)
                ? $data->category_id
                : "",
            "year" => $data->year,
            "1G" => $data->G1,
            "1H" => $data->H1,
            "1K" => $data->K1,
            "1KH" => $data->KH1,
            "1R" => $data->R1,
            "1RH" => $data->RH1,
            "2AG" => $data->AG2,
            "2AH" => $data->AH2,
            "2AK" => $data->AK2,
            "2AKH" => $data->AKH2,
            "2AR" => $data->AR2,
            "2ARH" => $data->ARH2,
            "2BG" => $data->BG2,
            "2BH" => $data->BH2,
            "2BK" => $data->BK2,
            "2BKH" => $data->BKH2,
            "2BR" => $data->BR2,
            "2BRH" => $data->BRH2,
            "3AG" => $data->AG3,
            "3AH" => $data->AH3,
            "3AK" => $data->AK3,
            "3AKH" => $data->AKH3,
            "3AR" => $data->AR3,
            "3ARH" => $data->ARH3,
            "3BG" => $data->BG3,
            "3BH" => $data->BH3,
            "3BK" => $data->BK3,
            "3BKH" => $data->BKH3,
            "3BR" => $data->BR3,
            "3BRH" => $data->BRH3,
            "GM" => $data->GM,
            "GMH" => $data->GMH,
            "GMK" => $data->GMK,
            "GMKH" => $data->GMKH,
            "GMR" => $data->GMR,
            "GMRH" => $data->GMRH,
            "SCG" => $data->SCG,
            "SCH" => $data->SCH,
            "SCK" => $data->SCK,
            "SCKH" => $data->SCKH,
            "SCR" => $data->SCR,
            "SCRH" => $data->SCRH,
            "STG" => $data->STG,
            "STH" => $data->STG,
            "STK" => $data->STK,
            "STKH" => $data->STKH,
            "STR" => $data->STR,
            "STRH" => $data->STRH,
        ];
        $checkIsExists = $this->Cutoff_model->checkIsExists($variableNames);
        if ($checkIsExists > 0) {
            $result = $this->Cutoff_model->updateKCETCutoff($variableNames);
        } else {
            $result = $this->Cutoff_model->insertKCETCutoff($variableNames);
        }
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

    public function getKCETCutOffByCollegeId()
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
        $tokenSession = Utility::tokenSession($userData);

        $college_id = $data->college_id;
        $result = $this->Cutoff_model->getKCETCutOffByCollegeId($college_id);
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

    public function getKCETCutOffList()
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
            $tokenSession = Utility::tokenSession($userData);

            $columns = [
                0 => "id",
                1 => "round",
            ];
            $limit = $data->length;
            $start = ($data->draw - 1) * $limit;
            $orderColumn = $columns[$data->order[0]->column];
            $orderDir = $data->order[0]->dir;
            $totalData = $this->Cutoff_model->countAllKCETCut();

            $totalFiltered = $totalData;

            if (!empty($data->search->value)) {
                $search = $data->search->value;
                $totalFiltered = $this->Cutoff_model->countFilteredKCETCut(
                    $search
                );
                $kcet = $this->Cutoff_model->getFilteredKCETCut(
                    $search,
                    $start,
                    $limit,
                    $orderColumn,
                    $orderDir
                );
            } else {
                $kcet = $this->Cutoff_model->getAllKCETCut(
                    $start,
                    $limit,
                    $orderColumn,
                    $orderDir
                );
            }

            $datas = [];
            foreach ($kcet as $cutoff) {
                $nestedData = [];
                $nestedData["id"] = $cutoff->id;
                $nestedData["round"] = $cutoff->round;
                $nestedData["year"] = $cutoff->year;
                $nestedData["category"] = $cutoff->categoryname;
                $nestedData["course"] = $cutoff->coursename;
                $nestedData["collegename"] = $cutoff->collegename;

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
            exit();
        }
        exit;
    }

    public function viewMoreKCET()
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
        $tokenSession = Utility::tokenSession($userData);

        $id = $data->id;
        $result = $this->Cutoff_model->viewMoreKcet($id);
        $collegename = $result[0][52]["value"];
        $categoryname = $result[0][53]["value"];
        $coursename = $result[0][54]["value"];
        foreach ($result as &$innerArray) {
            $innerArray = array_slice($innerArray, 0, -3);
        }

        if ($result) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["response_data"] = $result;
            $response["collegename"] = $collegename;
            $response["categoryname"] = $categoryname;
            $response["coursename"] = $coursename;
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed";
        }

        echo json_encode($response);
        exit();
    }

    public function deleteKCET()
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
        $tokenSession = Utility::tokenSession($userData);

        $id = $data->id;
        $result = $this->Cutoff_model->deletedKCET($id);
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

    public function importKCETcsv()
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
        $tokenSession = Utility::tokenSession($userData);
        $folder = "uploads/csv";
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }
        if (isset($_FILES["file"]) && $_FILES["file"]["error"] == 0) {
            $allowed = [
                "csv" => "text/csv", // CSV
            ];
            $filename = $_FILES["file"]["name"];
            $filesize = $_FILES["file"]["size"];
            $file_ext = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            $maxsize = 6 * 1024 * 1024; // 6 megabytes in bytes
            if (!array_key_exists($file_ext, $allowed)) {
                $response["status"] = "false";
                $response["response_code"] = 1;
                $response["response_message"] =
                    " Please select a valid file format.It should CSV File";
            } elseif ($filesize > $maxsize) {
                $response["status"] = "false";
                $response["response_code"] = 2;
                $response["response_message"] =
                    "File size is larger than the allowed limit";
            } else {
                $fileNameFinal = time() . "_" . $filename . "";
                $finalFile = $folder . "/" . $fileNameFinal;
                $upload = move_uploaded_file(
                    $_FILES["file"]["tmp_name"],
                    $finalFile
                );
                if ($upload) {
                    $strFileHandle = fopen($finalFile, "r");
                    $dataRows = [];
                    while (
                        ($line_of_text = fgetcsv($strFileHandle, 1024, ",")) !==
                        false
                    ) {
                        $dataRows[] = $line_of_text;
                    }
                    fclose($strFileHandle);
                    $headers = $dataRows[0];
                    for ($i = 1; $i < count($dataRows); $i++) {
                        $rowData = array_combine($headers, $dataRows[$i]);
                        // print_r($rowData->year);
                        $year = $rowData["year"];
                        $round = $rowData["round"];
                        $category_id = $rowData["category_id"];
                        $checkIsExists = $this->Cutoff_model->checkIsExists(
                            $rowData
                        );
                        if ($checkIsExists > 0) {
                            $result = $this->Cutoff_model->updateKCETCutoff(
                                $rowData
                            );
                        } else {
                            $result = $this->Cutoff_model->insertKCETCutoff(
                                $rowData
                            );
                        }
                        if ($result) {
                            $response["response_code"] = "200";
                            $response["response_message"] = "Success";
                            $response["File"] = $fileNameFinal;
                            $response["FileDir"] = base_url() . $finalFile;
                            $response["response_data"] = $result;
                        } else {
                            $response["response_code"] = "400";
                            $response["response_message"] = "Failed";
                        }
                    }
                }
            }
        } else {
            $response["status"] = "false";
            $response["response_code"] = 3;
            $response["response_message"] = "please Upload the file";
        }

        echo json_encode($response);
        exit();
    }

    public function getDetailsById()
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
        $tokenSession = Utility::tokenSession($userData);

        $id = $data->id;
        $result = $this->Cutoff_model->getDetailsById($id);
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

    public function getSampleCSV()
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
        $tokenSession = Utility::tokenSession($userData);

        $type = isset($data->type)?$data->type:'';
        if($type =='COMEDK')
        {
          $csvpath = base_url() . "uploads/samplecsv/SampleCOMEDK.csv";

        }
        else
        {
        $csvpath = base_url() . "uploads/samplecsv/SampleKCET.csv";
        }
        if ($csvpath) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["samplecsv"] = $csvpath;
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed";
        }

        echo json_encode($response);
        exit();
    }

    public function getCOMEDKCutOffList()
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
            $tokenSession = Utility::tokenSession($userData);

            $columns = [
                0 => "id",
                1 => "round",
            ];
            $limit = $data->length;
            $start = ($data->draw - 1) * $limit;
            $orderColumn = $columns[$data->order[0]->column];
            $orderDir = $data->order[0]->dir;
            $totalData = $this->Cutoff_model->countAllCOMEDKCut();

            $totalFiltered = $totalData;

            if (!empty($data->search->value)) {
                $search = $data->search->value;
                $totalFiltered = $this->Cutoff_model->countFilteredCOMEDKCut(
                    $search
                );
                $comedk = $this->Cutoff_model->getFilteredCOMEDKCut(
                    $search,
                    $start,
                    $limit,
                    $orderColumn,
                    $orderDir
                );
            } else {
                $comedk = $this->Cutoff_model->getAllCOMEDKCut(
                    $start,
                    $limit,
                    $orderColumn,
                    $orderDir
                );
            }

            $datas = [];
            foreach ($comedk as $cutoff) {
                $nestedData = [];
                $nestedData["id"] = $cutoff->id;
                $nestedData["round"] = $cutoff->round;
                $nestedData["year"] = $cutoff->year;
                $nestedData["rank"] = $cutoff->rank;
                $nestedData["category"] = $cutoff->category;
                $nestedData["course"] = $cutoff->coursename;
                $nestedData["collegename"] = $cutoff->collegename;

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
            exit();
        }
        exit;
    }
        /*** insert COMEDK Cutoff */
    public function insertUpdateCOMEDKCutoff()
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
        $tokenSession = Utility::tokenSession($userData);
        if($data)
        {
        $id = isset($data->id)?$data->id:'';
        $Arr = [
            "college_id" => $data->college_id,
            "round" => $data->round,
            "course_id" => isset($data->course_id) ? $data->course_id : "",
            "category" => isset($data->category)
                ? $data->category
                : "",
            "year" => $data->year,
            "rank" => $data->rank

            ];
            if(!empty($id))
            {
               $result = $this->Cutoff_model->updateCOMEDKCutoff($Arr,$id);

            }
            else
            {
            $result = $this->Cutoff_model->insertCOMEDKCutoff($Arr);
            }
            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["response_data"] = $result;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }
        }
        else
        {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is NULL.";
        }
        
        echo json_encode($response);
        exit();
        
    }
    
    public function getCOMEDKDetailsById()
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
        $tokenSession = Utility::tokenSession($userData);

        $id = $data->id;
        $result = $this->Cutoff_model->getCOMEDKDetailsById($id);
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
    
    
    public function deleteCOMEDK()
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
        $tokenSession = Utility::tokenSession($userData);

        $id = $data->id;
        $result = $this->Cutoff_model->deleteCOMEDK($id);
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
    
        public function importCOMEDKcsv()
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
        $tokenSession = Utility::tokenSession($userData);
        $folder = "uploads/csv";
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }
        //print_r($_FILES);exit;
        if (isset($_FILES["file"]) && $_FILES["file"]["error"] == 0) {
            $allowed = [
                "csv" => "text/csv", // CSV
            ];
            $filename = $_FILES["file"]["name"];
            $filesize = $_FILES["file"]["size"];
            $file_ext = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            $maxsize = 6 * 1024 * 1024; // 6 megabytes in bytes
            if (!array_key_exists($file_ext, $allowed)) {
                $response["status"] = "false";
                $response["response_code"] = 1;
                $response["response_message"] =
                    " Please select a valid file format.It should CSV File";
            } elseif ($filesize > $maxsize) {
                $response["status"] = "false";
                $response["response_code"] = 2;
                $response["response_message"] =
                    "File size is larger than the allowed limit";
            } else {
                $fileNameFinal = time() . "_" . $filename . "";
                $finalFile = $folder . "/" . $fileNameFinal;
                $upload = move_uploaded_file(
                    $_FILES["file"]["tmp_name"],
                    $finalFile
                );
                if ($upload) {
                    $strFileHandle = fopen($finalFile, "r");
                    $dataRows = [];
                    while (
                        ($line_of_text = fgetcsv($strFileHandle, 1024, ",")) !==
                        false
                    ) {
                        $dataRows[] = $line_of_text;
                    }
                    fclose($strFileHandle);
                    $headers = $dataRows[0];
                    for ($i = 1; $i < count($dataRows); $i++) {
                        $rowData = array_combine($headers, $dataRows[$i]);
                        // print_r($rowData);
                        // $year = $rowData["year"];
                        // $round = $rowData["round"];
                        // $category_id = $rowData["category"];
                        $checkIsExists = $this->Cutoff_model->checkIsExistsComedk(
                            $rowData
                        );
                        if ($checkIsExists > 0) {
                            $result = $this->Cutoff_model->updateComdekCSVCutoff(
                                $rowData
                            );
                            // print_r($result);exit;
                        } else {

                            $result = $this->Cutoff_model->insertComedkCutoff(
                                $rowData
                            );
                        }
                        if ($result) {
                            $response["response_code"] = "200";
                            $response["response_message"] = "Success";
                            $response["File"] = $fileNameFinal;
                            $response["FileDir"] = base_url() . $finalFile;
                            $response["response_data"] = $result;
                        } else {
                            $response["response_code"] = "400";
                            $response["response_message"] = "Failed";
                        }
                    }
                }
            }
        } else {
            $response["status"] = "false";
            $response["response_code"] = 3;
            $response["response_message"] = "please Upload the file";
        }

        echo json_encode($response);
        exit();
    }
    
            
}
