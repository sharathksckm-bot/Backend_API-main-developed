<?php
ini_set("max_execution_time", 0);
ini_set("upload_max_filesize", "5M");
date_default_timezone_set("Asia/Kolkata");
ini_set("auto_detect_line_endings", true);

//error_reporting(E_ERROR);
class create_html extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        // $this->load->library('m_pdf');
        $this->load->library("Utility");
        $this->load->model(["web/create_html_Model"]);
        $this->load->library("m_pdf");
    }
    function createHtml()
    {
        $id = $_GET['id'];
        $type = $_GET['type'];

        // Set URL and SQL query based on type
        if ($type == "article") {
            $url = "https://ohcampus.com/articledetails/";
            //$sql = "SELECT title,description, image FROM `blog` WHERE id = ?";
            $sql = $this->create_html_Model->articles($id);
            $imgpath = "https://campusapi.ohcampus.com/uploads/blogs/";
        } elseif ($type == "exam") {
            $url = "https://ohcampus.com/examsdetails/";
            //$sql = "SELECT title,description, image FROM `exams` e LEFT JOIN gallery g ON g.postid = e.id WHERE e.id = ? AND g.type='exams'";
            $sql = $this->create_html_Model->exam($id);
            $imgpath = "https://campusapi.ohcampus.com/uploads/exams/";
        } elseif ($type == "event") {
            $url = "https://ohcampus.com/eventdetails/";
            //$sql = "SELECT e.event_name AS title,event_desc as description, g.image FROM `events` e LEFT JOIN gallery g ON g.postid = e.event_id WHERE e.event_id = ? AND g.type = 'events'";
            $sql = $this->create_html_Model->event($id);
            $imgpath = "https://campusapi.ohcampus.com/uploads/events/";
        } elseif ($type == "college") {
            $url = "https://ohcampus.com/collegeDetails/";
            //$sql = "SELECT  title,description, g.image FROM `college` e LEFT JOIN gallery g ON g.postid = e.id WHERE e.id = ? AND g.type = 'college'";
            $sql = $this->create_html_Model->college($id);
            $imgpath = "https://campusapi.ohcampus.com/uploads/college/";
        } else {
            die("Invalid type");
        }

        $title = substr($sql[0]['title'], 0, 5); // Gets "P E S " (with trailing space)
        $title = str_replace(' ', '', $title);
        //print_r($title); exit;

        $this->m_pdf->pdf->SetHTMLHeader('');
        $this->m_pdf->pdf->SetHTMLFooter('');
        $this->m_pdf->pdf->AddPage("", "", "", "", "", 18, 18, 20, 25, 5, 0);
        $content = $this->htmlTemplate($id, $url, $sql, $imgpath);
        //print_r(dirname(dirname(__DIR__)));exit;
        //Creating HTML file and upload it
        $path = "/home/ohcampus/public_html/campusapi.ohcampus.com/uploads/Create_HTML/";
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        
        $filename = $id . '_' . $title . '.html';
        $filePath = $path . $filename;
        file_put_contents($filePath, $content);
        //print_r($filename);exit;
        echo json_encode('https://campusapi.ohcampus.com/uploads/Create_HTML/' . $filename);
        exit();
    }

    function htmlTemplate($id, $url, $sql, $imgpath)
    {
        $content = '<!DOCTYPE html><html><head>
                    <title>Ohcampus</title>
                    <meta name="description" content="">';
        foreach ($sql as $s) {
            //print_r($s["title"]);exit;
            $content .= '<!-- Open Graph Meta Tags -->';
            $content .= '<meta property="og:url" content="' . $url . $id . '">';
            $content .= '<meta property="og:type" content="website">';
            $content .= '<meta property="og:title" content="' . $s["title"] . '">';
            $content .= '<meta property="og:description" content="' . $s["description"] . '">';
            $content .= '<meta property="og:image" content="' . $imgpath . $s["image"] . '">';
            $content .= '<meta property="og:image:width" content="600">';
            $content .= '<meta property="og:image:height" content="400">';

            $content .= '<!-- Twitter Meta Tags -->';
            $content .= '<meta name="twitter:card" content="summary_large_image">';
            $content .= '<meta property="twitter:domain" content="ohcampus.com">';
            $content .= '<meta property="twitter:url" content="' . $url . $id . '">';
            $content .= '<meta name="twitter:title" content="' . $s["title"] . '">';
            $content .= '<meta name="twitter:description" content="' . $s["description"] . '">';
            $content .= '<meta property="twitter:image" content="' . $imgpath . $s["image"] . '">';

            // Redirect to the article URL
            $content .= '<meta http-equiv="refresh" content="0; URL=' . $url . $id . '">';
            $content .= '<script type="text/javascript">window.location.href = "' . $url . $id . '";</script>';
        }
        $content .= '</head> <body><!-- Your HTML content here --></body></html>';
        return $content;
    }
}