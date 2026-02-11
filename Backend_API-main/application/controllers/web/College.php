<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Methods: GET, POST');
//phpinfo();

/**
 * College Controller
 *
 * @category   Controllers
 * @package    Web
 * @version    1.0
 * @author     Vaishnavi Badabe
 * @created    30 JAN 2024
 *
 * Class College handles all the operations related to displaying list, creating college, update, and delete.
 */

if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}
class College extends CI_Controller
{
    /**
     * Constructor
     *
     * Loads necessary libraries, helpers, and models for the college controller.
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model("web/College_model", "", true);
        $this->load->model("web/Review_model", "", true);

        $this->load->library("Utility");
        // $this->load->library("m_pdf");
        $this->load->library('mpdf_lib');
        $config = [
            'tempDir' => APPPATH . 'tmp' // Or use FCPATH.'tmp' if you want outside of application folder
        ];
        $this->mpdf = new \Mpdf\Mpdf($config);
    }

    public function getCollegeType()
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
            $search_term = isset($data->search_term) ? $data->search_term : "";

            $types = $this->College_model->getCollegeType($search_term);

            if ($types) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["data"] = $types;
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

    public function getCollegeList()
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }

        // print_r($_SERVER['HTTP_AUTHORIZATION']);exit;
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
            $columns = [
                0 => "id",
                1 => "title",
                2 => "package_type",
                3 => "status",
            ];

            $limit = $data->length;
            $start = ($data->draw - 1) * $limit;
            $orderColumn = $columns[$data->order[0]->column];
            $orderDir = $data->order[0]->dir;
            $totalData = $this->College_model->countAllClg();
            $totalFiltered = $totalData;
            $loc = isset($data->search->loc) ? $data->search->loc : "";
            $clgname = isset($data->search->clgname)
                ? $data->search->clgname
                : "";
            $courseid = isset($data->search->courseid)
                ? $data->search->courseid
                : "";
            $ownerShip = isset($data->search->ownerShip)
                ? $data->search->ownerShip
                : "";
            $rankCategory = isset($data->search->rankCategory)
                ? $data->search->rankCategory
                : "";
            $categoryid = isset($data->search->categoryid)
                ? $data->search->categoryid
                : "";
            $value = isset($data->search->value) ? $data->search->value : "";
            $rankid = isset($data->search->rankid) ? $data->search->rankid : "";
            if (
                !empty($rankid) ||
                !empty($clgname) ||
                !empty($loc) ||
                !empty($ownerShip) ||
                !empty($rankCategory ||
                    !empty($courseid) ||
                    !empty($value) ||
                    !empty($categoryid))
            ) {

                //  $search = $data->search->value;
                $totalFiltered = $this->College_model->countFilteredClg(
                    $clgname,
                    $loc,
                    $ownerShip,
                    $rankCategory,
                    $courseid,
                    $value,
                    $categoryid,
                    $rankid
                );
                $ClgList = $this->College_model->getFilteredClg(
                    $clgname,
                    $start,
                    $limit,
                    $orderColumn,
                    $orderDir,
                    $loc,
                    $ownerShip,
                    $rankCategory,
                    $courseid,
                    $value,
                    $categoryid,
                    $rankid
                );
            } else {
                $ClgList = $this->College_model->getAllClg(
                    $start,
                    $limit,
                    $orderColumn,
                    $orderDir
                );
            }
            // print_r($ClgList);exit;

            $datas = [];
            foreach ($ClgList as $clg) {
                $rnkList = $this->College_model->getRankListByClgId($clg->id);
                $RankList = [];

                foreach ($rnkList as $rn) {
                    $rankData = [
                        "title" => $rn->title,
                        "rank" => $rn->rank,
                        "year" => $rn->year,
                    ];
                    $RankList[] = $rankData;
                }
                $nestedData = [];
                $nestedData["id"] = $clg->id;
                $nestedData["title"] = $clg->title;
                if (!empty($clg->logo)) {
                    $nestedData["logo"] =
                        base_url() . "/uploads/college/" . $clg->logo;
                } else {
                    $nestedData["logo"] = '';
                }
                if (!empty($clg->gallery_image)) {
                    $nestedData["image"] =
                        base_url() . "/uploads/college/" . $clg->gallery_image;
                } else {
                    $nestedData["image"] = '';
                }
                if (!empty($clg->banner)) {

                    $nestedData["banner"] =
                        base_url() . "/uploads/college/" . $clg->banner;
                } else {
                    $nestedData["banner"] = '';
                }
                $nestedData["city"] = $clg->city;
                $nestedData["estd"] = $clg->estd;
                $nestedData["package_type"] = $clg->package_type;
                $nestedData["Rank"] = $RankList;
                $nestedData["is_accept_entrance"] = $clg->is_accept_entrance;
                $nestedData["application_link"] = $clg->application_link;
                $nestedData["slug"] = $clg->slug;

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

    public function getFeaturedColleges()
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

        $result = $this->College_model->getFeaturedColleges();
        foreach ($result as $key => $value) {
            $getTotalCourses = $this->College_model->getTotalCourses(
                $result[$key]->id
            );
            $result[$key]->totalCourseCount = $getTotalCourses;
            if (!empty($result[$key]->image)) {
                $result[$key]->image =
                    base_url("uploads/college/") . $result[$key]->image;
            }
        }

        if ($result) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["data"] = $result;
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed";
        }

        echo json_encode($response);
        exit();
    }

    public function getTrendingColleges()
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

        $result = $this->College_model->getTrendingColleges();

        if ($result) {
            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["trendingClg"] = $result;
        } else {
            $response["response_code"] = "400";
            $response["response_message"] = "Failed";
        }

        echo json_encode($response);
        exit();
    }

    public function getCollegeDetailsByID()
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
            $id = $data->id;
            $ClgList = $this->College_model->getCollegeDetailsByID($id);
            $increment_view = $this->College_model->increment_view($id);

            //print_r($ClgList);exit;
            // $ClgHighlight = $this->College_model->getCollegeHighlightByID($id);
            // print_r($ClgHighlight);exit;
            //$clgCourses = $this->College_model->getCollegeCoursesByID($id);
            $tableOfContent = $this->College_model->getTableOfContent($id);
            $clgImages = $this->College_model->getCollegeImagesByID($id);
            // $popularProgrammes = $this->College_model->getCollegeProgrammesByID($id);
            foreach ($clgImages as $key => $img) {
                $clgImages[$key]->imageName = $img->image;
                $clgImages[$key]->image =
                    base_url() . "/uploads/college/" . $img->image;
            }

            if (!empty($ClgList[0]["logo"])) {
                $ClgList[0]["logo"] =
                    base_url() . "/uploads/college/" . $ClgList[0]["logo"];
            }
            if (!empty($ClgList[0]["image"])) {
                $ClgList[0]["image"] =
                    base_url() . "/uploads/college/" . $ClgList[0]["image"];
            }
            if (!empty($ClgList[0]["banner"])) {
                $ClgList[0]["banner"] =
                    base_url() . "/uploads/college/" . $ClgList[0]["banner"];
            }
            $result = [];
            // foreach ($ClgList as $clg) {
            // //     //echo $clg['id'];exit;
            // //     $rnkList = $this->College_model->getRankListByClgId($clg["id"]);
            // //     $RankList = [];
            // //     foreach ($rnkList as $rn) {
            // //         $rankData = [
            // //             "title" => $rn->title,
            // //             "rank" => $rn->rank,
            // //             "year" => $rn->year,
            // //         ];
            // //         $RankList[] = $rankData;
            // //     }
            //     $nestedData["id"] = $clg["id"];
            //     $nestedData["title"] = $clg["title"];
            //     $nestedData["description"] = $clg["description"];
            //     $nestedData["logo"] =
            //         base_url() . "/uploads/college/" . $clg["logo"];
            //     $nestedData["image"] =
            //         base_url() . "/uploads/college/" . $clg["image"];
            //     $nestedData["banner"] =
            //         base_url() . "/uploads/college/" . $clg["banner"];   
            //     $nestedData["city"] = $clg["city"];
            //     $nestedData["cityid"] = $clg["cityid"];
            //     $nestedData["categoryid"] = $clg["categoryid"];
            //     $nestedData["country"] = $clg["country"];
            //     $nestedData["estd"] = $clg["estd"];
            //     $nestedData["accreditation"] = $clg["accreditation"];
            //     $nestedData["package_type"] = $clg["package_type"];
            //     $nestedData["category"] = $clg["catname"];
            //     $nestedData["Collage_category"] = $clg["name"];
            //     $nestedData["what_new"] = $clg["what_new"];
            //     $nestedData["application_link"] = $clg["application_link"];
            //     // $nestedData["Courses"] = $clgCourses;
            //     // $nestedData["Rank"] = $RankList;
            //     $nestedData["is_accept_entrance"] = $clg["is_accept_entrance"];

            //     // $tableOfContent = [];

            //     $result[] = $nestedData;
            // }
            if (true) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["college_detail"] = $ClgList;
                $response["table_of_content"] = $tableOfContent;
                $response["college_images"] = $clgImages;
                // $response["popular_programmes"] = $popularProgrammes;
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

    public function getCollegesByCourse_level()
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
            $id = $data->id;
            $level = $data->level;
            $Colleges = $this->College_model->getCollegesByCourse_level(
                $id,
                $level
            );
            $result = [];
            foreach ($Colleges as $clg) {
                $clgCourses = $this->College_model->getCoursesCountByCollegeID(
                    $clg["id"]
                );
                $clgReviewRate = $this->College_model->getReviewRatingByClgId(
                    $clg["id"]
                );
                $nestedData["id"] = $clg["id"];
                $nestedData["title"] = $clg["title"];
                $nestedData["address"] = $clg["address"];
                $nestedData["logo"] =
                    base_url() . "/uploads/college/" . $clg["logo"];
                $nestedData["image"] =
                    base_url() . "/uploads/college/" . $clg["image"];
                $nestedData["city"] = $clg["city"];
                $nestedData["phone"] = $clg["phone"];
                $nestedData["accreditation"] = $clg["accreditation"];
                $nestedData["file"] =
                    base_url() . "/uploads/brochures/" . $clg["file"];
                $nestedData["Courses"] = $clgCourses;
                $nestedData["Rank"] = $clgReviewRate;
                $result[] = $nestedData;
            }
            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["Colleges"] = $result;
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

    public function getCollegeListByCourseId()
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
            $courseId = $data->courseId;

            $result = $this->College_model->getCollegeListByCourseId($courseId);
            foreach ($result as $clg) {
                $clg->logo = base_url() . "/uploads/college/" . $clg->logo;
                $clg->file = base_url() . "/uploads/brochures/" . $clg->file;
            }
            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["collegelist"] = $result;
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

    public function getCollegeHighlightByID()
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
            $id = $data->id;

            $result = $this->College_model->getCollegeHighlightByID($id);
            $result2 = $this->getCommonalyAskedQ($id, $type = "HIGHLIGHTS");

            if ($result || $result2) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["CollegeHighlight"] = $result;
                $response["Commonaly_Asked_Questions"] = $result2;
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

    public function getCollegeFacilitiesByID()
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
            $id = $data->id;

            $result = $this->College_model->getCollegeFacilitiesByID($id);
            //print_r($result);exit;
            //  $result2 = $this->getCommonalyAskedQ($id, $type = "FACILITIES");

            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["CollegeFac"] = $result;
                //$response["Commonaly_Asked_Questions"] = $result2;
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
    public function getPlacementDataOfClg()
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
            $searchYear = isset($data->searchYear)
                ? $data->searchYear
                : date("Y") - 1;
            $searchCategory = isset($data->searchCategory)
                ? $data->searchCategory
                : "2";
            // print_r($searchCategory);exit;
            $collegeId = isset($data->collegeId) ? $data->collegeId : "";

            // print_r($searchYear);exit;
            $result = $this->College_model->getPlacementDataOfClg(
                $searchCategory,
                $searchYear,
                $collegeId
            );

            $result2 = $this->getCommonalyAskedQ(
                $collegeId,
                $type = "PLACEMENT"
            );

            if ($result || $result2) {
                //   if (!empty($result)) {
                //     foreach ($result as $key => $placement) {
                //         foreach ($placement as $param => $value) {
                //             if ($value == 0) {
                //                 $result[$key]->$param = "NA";
                //             }
                //         }
                //     }
                // }
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["placementlist"] = $result;
                $response["Commonaly_Asked_Questions"] = $result2;
            } else {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["response_data"] = "Data Not Found";
                $response["placementlist"] = $result;
                $response["Commonaly_Asked_Questions"] = $result2;
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }
        echo json_encode($response);
        exit();
    }

    public function getRanktDataOfClg()
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
            $collegeId = isset($data->collegeId) ? $data->collegeId : "";
            $result = $this->College_model->getRanktDataOfClg($collegeId);
            $result2 = $this->getCommonalyAskedQ($collegeId, $type = "RANKING");
            foreach ($result as $key => $value) {
                if (isset($result[$key]->image)) {
                    $result[$key]->image =
                        base_url("uploads/rankimage/") . $result[$key]->image;
                }
            }
            if ($result || $result2) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["rankList"] = $result;
                $response["Commonaly_Asked_Questions"] = $result2;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }
        echo json_encode($response);
        exit();
    }

    public function getCollegeProgrammesByID()
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
            $collegeId = isset($data->collegeId) ? $data->collegeId : "";
            $subcategory     = isset($data->subcategory) ? $data->subcategory     : "";
            $result = $this->College_model->getCollegeProgrammesByID(
                $collegeId,
                $subcategory
            );
            foreach ($result as &$course) {
                if (!empty($course['entrance_exam_names'])) {
                    $course['entrance_exam_names'] = str_replace(',', ', ', $course['entrance_exam_names']);
                }
            }
            // print_r($result);exit;

            //  foreach ($result as &$course) {
            //     $courseId = $course['courseid'];

            //     // Merge total_fees
            //     $currentFee = isset($course['total_fees']) ? trim($course['total_fees']) : '';
            //     if ($currentFee === '' || is_null($currentFee)) {
            //         $course['total_fees'] = isset($feesMap[$courseId]) ? $feesMap[$courseId] : '';
            //     }

            //     // Merge entrance_exam_names
            //     $course['entrance_exam_names'] = isset($examsMap[$courseId])
            //         ? preg_replace('/\s*,\s*/', ' , ' , $examsMap[$courseId])
            //         : null;
            // }
            // unset($course);
            $result2 = $this->getCommonalyAskedQ($collegeId, $type = "COURSES");

            if ($result || $result2) {

                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["popular_programmes"] = $result;
                $response["Commonaly_Asked_Questions"] = $result2;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }
        echo json_encode($response);
        exit();
    }

    /*  public function getCoursesAndFeesOfClg()
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
            $collegeId = isset($data->collegeId) ? $data->collegeId : "";

            $result = $this->College_model->getCoursesAndFeesOfClg($collegeId);

            $feesStructure = $this->College_model->getfeesstructure($collegeId);
           //  print_r($feesStructure);exit;
            
            foreach ($result as &$clg) {
                //print_r($clg);
                $total_fees = $this->College_model->getTotalFeesForCollege($clg['college_typeid'], $clg['categoryid'], $clg['sub_category'], $clg['entrance_exams']);

                foreach ($total_fees as $totalfees) {
                    //  print_r($totalfees['fees']);exit;
                    if (empty($clg['total_fees'])) {
                        $clg['total_fees'] = $totalfees['fees'];
                    }
                }
                if (empty($clg['total_fees']) && empty($total_fees)) {
                    $clg['total_fees'] = '';
                }
             
                foreach ($clg as $key => $value) {
                    if (empty($value)) {
                        $clg[$key] = '';
                    }
                }
              // print_r($result);exit;
            }
           
            foreach ($result as $key => $value) {
               // print_r($value['eligibility']);exit;
                $result[$key]['eligibility'] = json_decode(
                    $result[$key]['eligibility']
                );
            }
            $result2 = $this->getCommonalyAskedQ($collegeId, $type = "Fees");

            if ($result || $result2) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["courselist"] = $result;
                $response["Commonaly_Asked_Questions"] = $result2;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }
        echo json_encode($response);
        exit();
    }*/


    public function getCoursesAndFeesOfClg()
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
            $collegeId = isset($data->collegeId) ? $data->collegeId : "";

            $result = $this->College_model->getCoursesAndFeesOfClg($collegeId);
            // print_r($result);exit;


            foreach ($result as $key => &$clg) {
                // Get course_id
                $courseId = $clg['id'];

                // Fetch feesStructure for this courseId and collegeId
                $feesStructure = $this->College_model->getfeesstructure($courseId, $collegeId);

                // Assign fees_structure if available
                if (!empty($feesStructure)) {
                    $clg['fees_structure'] = $feesStructure[0]->amount; // or whatever field you need
                } else {
                    $clg['fees_structure'] = '';
                }

                // Get total fees
                $total_fees = $this->College_model->getTotalFeesForCollege(
                    $clg['college_typeid'],
                    $clg['categoryid'],
                    $clg['sub_category'],
                    $clg['entrance_exams']
                );

                foreach ($total_fees as $totalfees) {
                    if (empty($clg['total_fees'])) {
                        $clg['total_fees'] = $totalfees['fees'];
                    }
                }

                if (empty($clg['total_fees']) && empty($total_fees)) {
                    $clg['total_fees'] = '';
                }

                // Decode eligibility JSON
                if (!empty($clg['eligibility'])) {
                    $clg['eligibility'] = json_decode($clg['eligibility']);
                }

                // Convert null/empty fields to ''
                foreach ($clg as $k => $v) {
                    if (empty($v)) {
                        $clg[$k] = '';
                    }
                }
            }
            $result2 = $this->getCommonalyAskedQ($collegeId, "Fees");

            if ($result || $result2) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["courselist"] = $result;
                $response["Commonaly_Asked_Questions"] = $result2;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }

        echo json_encode($response);
        exit();
    }


    public function getFAQsOfClg()
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
            $collegeId = isset($data->collegeId) ? $data->collegeId : "";
            $result = $this->College_model->getFAQsOfClg($collegeId);
            $FAQs = [];
            foreach ($result as $item) {
                $faq_ids = explode(",", $item["faq_id"]);
                $questions = explode(",", $item["question"]);

                for ($i = 0; $i < count($faq_ids); $i++) {
                    $description = $this->College_model->getDescriptionForFAQ(
                        $faq_ids[$i]
                    );
                    if (!empty($description)) {
                        $FAQs[] = [
                            "faq_id" => $faq_ids[$i],
                            "question" => $questions[$i],
                            "answere" => $description[0]["answere"],
                        ];
                    }
                }
            }

            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["FAQs"] = $FAQs;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }
        echo json_encode($response);
        exit();
    }

    public function getCommonalyAskedQ($collegeId, $type)
    {
        //print_r($type);exit;
        //  $type = 'SCIENCE';
        $getType = $this->College_model->getFaqType($type);
        //  print_r($getType);exit;
        $type = $getType[0]->id;
        $result = $this->College_model->getCommonalyAskedQ($collegeId, $type);
        //print_r($result);exit;
        $FAQs = [];
        foreach ($result as $item) {
            $faq_ids = explode(",", $item["faq_id"]);
            $questions = explode(",", $item["question"]);

            for ($i = 0; $i < count($faq_ids); $i++) {
                $description = $this->College_model->getDescriptionForFAQ(
                    $faq_ids[$i]
                );
                if (!empty($description)) {
                    $FAQs[] = [
                        "faq_id" => $faq_ids[$i],
                        "question" => $questions[$i],
                        "answere" => $description[0]["answere"],
                    ];
                }
            }
        }

        return $FAQs;
    }

    public function getcollegeByLocation()
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
            $collegeId = $data->collegeid;
            $cityid = $data->cityid;
            $result = $this->College_model->collegeByLocation(
                $cityid,
                $collegeId
            );

            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["collegeByLocation"] = $result;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }
        echo json_encode($response);
        exit();
    }

    public function getCollegeContactDetails()
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
            $collegeId = isset($data->collegeId) ? $data->collegeId : "";
            $result = $this->College_model->getCollegeContactDetails(
                $collegeId
            );

            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["ContactDetails"] = $result;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }
        echo json_encode($response);
        exit();
    }

    public function getCollegeAdmissionProcess()
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
            $collegeId = isset($data->collegeId) ? $data->collegeId : "";
            $result = $this->College_model->getCollegeAdmissionProcess(
                $collegeId
            );
            $EXAMs = [];
            foreach ($result as $key => $value) {
                if (is_string($result[$key]->eligibility) && is_array(json_decode($result[$key]->eligibility, true)) && (json_last_error() == JSON_ERROR_NONE)) {

                    $array = unserialize($result[$key]->eligibility);
                    $json = json_encode($array, JSON_PRETTY_PRINT);

                    $result[$key]->eligibility = json_decode($json);
                }

                $result[$key]->entrance_exams = explode(",", $result[$key]->entrance_exams ?? "");
                $acceptingExams = explode(",", $result[$key]->Accepting_Exams ?? "");

                $combinedExams = [];
                foreach ($result[$key]->entrance_exams as $index => $examId) {
                    $combinedExams[] = [
                        "id" => $examId,
                        "value" => isset($acceptingExams[$index])
                            ? $acceptingExams[$index]
                            : "",
                    ];
                }
                $result[$key]->acceptingExams = $combinedExams;
                for ($i = 0; $i < count($result[$key]->entrance_exams); $i++) {
                    $exams = $this->College_model->getExamsNotification(
                        $result[$key]->entrance_exams[$i]
                    );
                    if (!empty($exams)) {
                        $EXAMs[] = [
                            "Examnotification_or_ImportantDates" =>
                            $exams[0]["notification"],
                        ];
                        $result[$key]->Examnotification_or_ImportantDates =
                            $exams[0]["notification"];
                    }
                }
            }
            $result2 = $this->getCommonalyAskedQ(
                $collegeId,
                $type = "Admissions"
            );
            //$this->CollegeAdmissionProcessImportantDatesPDF($result);
            if ($result || $result2) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["AdmissionProcess"] = $result;
                $response["Commonaly_Asked_Questions"] = $result2;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }
        echo json_encode($response);
        exit();
    }

    public function getScholarShipOfClg()
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
            $collegeId = isset($data->collegeId) ? $data->collegeId : "";
            $result = $this->College_model->getScholarShipOfClg($collegeId);
            //print_r($result[0]['scholarship'] );exit;
            $result2 = $this->getCommonalyAskedQ(
                $collegeId,
                $type = "SCHOLARSHIP"
            );

            if ($result || $result2) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["scholarship_data"] = $result;
                $response["Commonaly_Asked_Questions"] = $result2;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }
        echo json_encode($response, JSON_UNESCAPED_SLASHES);
        exit();
    }

    public function getCollegeListForCompare()
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
            $searchClg = isset($data->searchClg) ? $data->searchClg : "";
            $result = $this->College_model->getCollegeListForCompare(
                $searchClg
            );
            foreach ($result as $key => $value) {
                if (!empty($result[$key]->image)) {
                    $result[$key]->image = base_url("uploads/college/") . $result[$key]->image;
                }
                $TotalRate = $this->Review_model->getCollegeTotalRate($result[$key]->id);
                $result[$key]->total_rate = $TotalRate;
            }
            // print_r($result);exit;
            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["CollegeListForCompare"] = $result;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }
        echo json_encode($response);
        exit();
    }

    public function getPopularClgByLocation()
    {
        //  echo "tttt";exit;
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
            $categoryid = isset($data->categoryid) ? $data->categoryid : "";
            $cityid = isset($data->cityid) ? $data->cityid : "";
            $courseid = isset($data->courseid) ? $data->courseid : "";
            $getCategoryId = $this->College_model->getCategoryId($courseid);
            $courseCategory = isset($getCategoryId['course_category']) ? $getCategoryId['course_category'] : "";
            // print_r($courseCategory);exit;
            //print_r($getCategoryId[0]['course_category']);exit;
            $result = $this->College_model->getPopularClgByLocation($cityid, $courseCategory, $categoryid);
            foreach ($result as $key => $value) {
                if (isset($result[$key]->image)) {
                    $result[$key]->image =
                        base_url("uploads/college/") . $result[$key]->image;
                }
            }
            $chunkedColleges = array_chunk($result, 3);
            $groupedPopColleges = [];
            foreach ($chunkedColleges as $chunk) {
                $groupedPopColleges[] = ["popularColleges" => $chunk];
            }
            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["CollegeListForCompare"] = $groupedPopColleges;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }
        echo json_encode($response);
        exit();
    }

    public function getPopularClgByLoc_cat()
    {
        // echo "tttt";exit;
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
            // $cityid = isset($data->cityid) ? $data->cityid : "";

            //$categoryId = isset($data->categoryId) ? $data->categoryId : "";

            $cityid = isset($data->cityid) ? $data->cityid : "";
            $courseid = isset($data->courseid) ? $data->courseid : "";
            $getCategoryId = $this->College_model->getCategoryId($courseid);
            //  $courseid= isset($data->courseid) ? $data->courseid : "";
            //print_r($getCategoryId['course_category']);exit;
            $result = $this->College_model->getPopularClgByLoc_cat($cityid, $getCategoryId['course_category']);
            foreach ($result as $key => $value) {
                if (isset($result[$key]->image)) {
                    $result[$key]->image =
                        base_url("uploads/college/") . $result[$key]->image;
                }
            }
            $chunkedColleges = array_chunk($result, 3);
            $groupedPopColleges = [];
            foreach ($chunkedColleges as $chunk) {
                $groupedPopColleges[] = ["popularColleges" => $chunk];
            }
            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["CollegeListForCompare"] = $groupedPopColleges;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }
        echo json_encode($response);
        exit();
    }

    public function getCollegesAccordingCategory()
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
            $collegeId = $data->collegeid;
            $categories = $data->categories;
            $result = $this->College_model->getCollegesAccordingCategory(
                $collegeId,
                $categories
            );
            foreach ($result as $key => $value) {
                if (isset($result[$key]->image)) {
                    $result[$key]->image =
                        base_url("uploads/college/") . $result[$key]->image;
                }
            }
            $chunkedColleges = array_chunk($result, 3);
            $groupedPopColleges = [];
            foreach ($chunkedColleges as $chunk) {
                $groupedPopColleges[] = ["bestColleges" => $chunk];
            }
            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["bestSuitedColleges"] = $groupedPopColleges;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }
        echo json_encode($response);
        exit();
    }

    /*************CREATE Admission Process and Important Dates PDF *******************/
    public function AdmissionProcessImportantDatesPDF()
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
            $collegeId = isset($data->collegeId) ? $data->collegeId : "";
            $category = $data->sub_category;
            $result = $this->College_model->getCollegeAdmissionProcess(
                $collegeId
            );
            $EXAMs = [];
            foreach ($result as $key => $value) {
                $result[$key]->eligibility = json_decode(
                    $result[$key]->eligibility
                );
                /*  $result[$key]->entrance_exams = explode(
                    ",",
                    $result[$key]->entrance_exams
                );*/

                $entrance = $result[$key]->entrance_exams ?? '';
                $result[$key]->entrance_exams = explode(",", $entrance);

                //  $acceptingExams = explode(",", $result[$key]->Accepting_Exams);
                $acceptingExams = explode(",", $result[$key]->Accepting_Exams ?? '');
                $combinedExams = [];
                foreach ($result[$key]->entrance_exams as $index => $examId) {
                    $combinedExams[] = [
                        "id" => $examId,
                        "value" => isset($acceptingExams[$index])
                            ? $acceptingExams[$index]
                            : "",
                    ];
                }
                $result[$key]->acceptingExams = $combinedExams;
                for ($i = 0; $i < count($result[$key]->entrance_exams); $i++) {
                    $exams = $this->College_model->getExamsNotification(
                        $result[$key]->entrance_exams[$i]
                    );
                    if (!empty($exams)) {
                        $EXAMs[] = [
                            "Examnotification_or_ImportantDates" =>
                            $exams[0]["notification"],
                        ];
                        $result[$key]->Examnotification_or_ImportantDates =
                            $exams[0]["notification"];
                    }
                }
            }
            $content = '<!DOCTYPE html>
                            <html lang="en">
                            <head>
                                <meta charset="UTF-8">
                                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                                <title>OhCampus</title>
                                <style>
                                    body{font-size: 14px!important;font-family: Arial!important;padding: 5px!important;margin: 5px!important;}
                                    table{ width: 100%;border-collapse: collapse;}
                                    table th, table td{padding: 7px!important; border: 1px solid #e7e7e7;}
                                    .noborder th, .noborder td{border: 0px!important;}
                                    table th{text-align: left;}
                                    .textcenter{text-align: center;}
                                    .textright{text-align: right;}
                                    .textleft{text-align: left;}        
                                    .margintopcss{margin-top: 1em;}
                                    .margintopcss2{margin-top: 2em;}
                                    .w100{width: 100%;}
                                    .bgcolor{background: aliceblue;}
                                    .layout{max-width:700px; margin: auto; border: double; border-color: #e7e7e7;}
                                </style>
                            </head>
                            <body>
                             <header class="textcenter">
        <img src="https://campusapi.ohcampus.com/uploads/Untitled.jpeg" alt="Campus Logo" style="width:350px;">
    </header>
                            <section class="margintopcss">
                                <div class="layout">
                                    <header>
                                        <table class="noborder">
                                            <tbody>
                                            </tr><tr><td style="text-align:center;font-wieght:bold;color:#88d834">Admission Process & Important Dates</td></tr>

                                            </tbody>
                                        </table>
                                    </header>';

            foreach ($result as $object) {
                // print_r($object);exit;
                $course = $object->sub_category;
                $courseName = $object->subCatName;
                $duration = $object->duration;
                //  $eligibility = json_decode($object->eligibility);
                $eligibility = !empty($object->eligibility)
                    ? json_decode($object->eligibility, true)
                    : [];
                // Set HTML header
                // $this->m_pdf->pdf->SetHTMLHeader('
                // <div style="text-align: center;">
                //     <img src="https://win.k2key.in/ohcampus/uploads/ohCampusLogo.png" alt="#" style="width: 200px; height: auto;">
                // </div>
                // ');

                // Set HTML footer


                $this->mpdf->SetHTMLFooter('
                                <div style="text-align: center; font-size: 14px; color: #333;font-wieght:bold;color:#88d834">
                                    <p>OhCampus.com, Comet Career India (R), 2nd Floor, SMG Plaza, MG Road, Chikkamagaluru, Karnataka.</p>
                                </div>
                                ');
                // Check if sub_category matches the desired value
                if ($course == $data->sub_category) {
                    $content .=
                        '<table class="margintopcss"><tr class="bgcolor"><th colspan="2">' .
                        $courseName .
                        '<br><span style="font-weight:normal";>Duration : ' .
                        $duration .
                        " years</span></th></tr>";
                    if (
                        property_exists(
                            $object,
                            "Examnotification_or_ImportantDates"
                        )
                    ) {
                        $important_dates =
                            $object->Examnotification_or_ImportantDates;
                        $content .=
                            '<tbody><tr><th colspan="2">' .
                            $important_dates .
                            "</th></tr></tbody>";
                    } else {
                        $content .=
                            '<tbody><tr><th colspan="2">Important dates not available for this Course.</th></tr></tbody>';
                    }
                    $content .= "</table>"; // Close the table
                }
            }

            $content .= "</div></section></body></html>";

            $outputDirectory =
                FCPATH . "AllPdf/AdmissionProcessImportantDatesPDFDocs/";
            if (!is_dir($outputDirectory)) {
                mkdir($outputDirectory, 0777, true);
            }

            $filename = time() . "_Admission_impdates_" . ".pdf";
            $this->mpdf->WriteHTML($content);
            // ob_end_clean();
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            $this->mpdf->Output($outputDirectory . $filename, "F");

            if ($filename) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["PDF"] =
                    base_url("AllPdf/AdmissionProcessImportantDatesPDFDocs/") .
                    $filename;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed to generate PDF";
            }

            echo json_encode($response);
            exit();
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null.";
        }
    }

    public function collegesOffereingSameCourseAtSameCity()
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
            $courseid = $data->courseid;
            $cityid = $data->cityid;
            $collegeId = isset($data->collegeId) ? $data->collegeId : "";

            $result = $this->College_model->collegesOffereingSameCourseAtSameCity(
                $courseid,
                $cityid,
                $collegeId
            );
            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["colleges_Offereing_SameCourse"] = $result;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }
        echo json_encode($response);
        exit();
    }

    public function getLastThreeYearsPlacementData()
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
            $CurrentYear = date("Y");
            $collegeId = isset($data->collegeId) ? $data->collegeId : "";
            // print_r($searchYear);exit;
            $result = $this->College_model->getLastThreeYearsPlacementData(
                $CurrentYear,
                $collegeId
            );
            $result2 = $this->getCommonalyAskedQ(
                $collegeId,
                $type = "PLACEMENT"
            );

            if ($result || $result2) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["placementlist"] = $result;
                $response["Commonaly_Asked_Questions"] = $result2;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }
        echo json_encode($response);
        exit();
    }


    public function getPopularCollegeListForCompare()
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
            $categoryid = isset($data->categoryid) ? $data->categoryid : "";

            $result = $this->College_model->getPopularCollegeListForCompare(
                $categoryid
            );

            foreach ($result as $key => $value) {
                if (!empty($result[$key]['image'])) {
                    $result[$key]['image'] = base_url("uploads/college/") . $result[$key]['image'];
                }
                $TotalRate = $this->Review_model->getCollegeTotalRate($result[$key]['id']);

                $result[$key]['total_rate'] = $TotalRate;
            }


            if ($result) {
                $response["response_code"] = "200";
                $response["response_message"] = "Success";
                $response["CollegeListForCompare"] = $result;
                // $response['rating'] = $TotalRate;
            } else {
                $response["response_code"] = "400";
                $response["response_message"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }
        echo json_encode($response, JSON_UNESCAPED_SLASHES);
        exit();
    }

    public function getHostelDetailsByCollegeId()
    {
        $data = json_decode(file_get_contents('php://input'));
        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data['status'] = 'ok';
            echo json_encode($data);
            exit;
        }
        if ($data) {

            $collegeId = $data->collegeId;

            $result = $this->College_model->getHostelDetailsById($collegeId);

            // print_r($result);exit;
            $result->imagepath = base_url() . '/uploads/Hostels/' . $result->image;

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
        }
        echo json_encode($response);
        exit();
    }

    public function savehostelinquiry()
    {
        $data = json_decode(file_get_contents("php://input"));

        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }

        if ($data) {
            $name = $data->name;
            $email = $data->email;
            $phone = $data->phone_no;
            $state = $data->state;
            $city = $data->city;
            $message = $data->message;
            $type = $data->type;
            $hostelId = $data->hostelId;

            $insertData = array(
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'state' => $state,
                'city' => $city,
                'message' => $message,
                'type' => $type,
                'postid' => $hostelId
            );

            $result = $this->College_model->savehostelinquiry($insertData);
            //print_r($result);exit;
            if ($result) {
                $response["status"] = "true";
                $response["res_code"] = 1;
                $response["res_status"] = "Success";
            } else {
                $response["status"] = "false";
                $response["res_code"] = 2;
                $response["res_status"] = "Failed";
            }
        } else {
            $response["response_code"] = "500";
            $response["response_message"] = "Data is null";
        }

        echo json_encode($response);
    }

    public function getYearOfCollege()
    {
        $data = json_decode(file_get_contents("php://input"));

        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }

        $result = $this->College_model->getYearOfCollege();
        //  print_r($result);exit;
        if ($result) {
            $response["status"] = "true";
            $response["res_code"] = 1;
            $response["res_status"] = "Success";
            $response["res_data"] = $result;
        } else {
            $response["status"] = "false";
            $response["res_code"] = 2;
            $response["res_status"] = "Failed";
        }
        echo json_encode($response);
    }
}
