<?php
defined('BASEPATH') OR exit('No direct script access allowed');

#[\AllowDynamicProperties]
class MY_Controller extends CI_Controller
{
    public $benchmark;
    public $hooks;
    public $config;
    public $log;
    public $utf8;
    public $uri;
    public $router;
    public $exceptions;
    public $output;
    public $security;
    public $input;
    public $lang;
    public $load;

    private static $instance;

    /**
     * Class constructor
     */
    public function __construct()
    {
        // ✅ Set CORS headers before anything else
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

        // ✅ Exit early on preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }

        parent::__construct();

        // CodeIgniter object reference setup
        self::$instance =& $this;

        foreach (is_loaded() as $var => $class) {
            $this->$var =& load_class($class);
        }

        $this->load =& load_class('Loader', 'core');
        $this->load->library("Utility");

        // ✅ Optional: If your intention is to only check auth for certain children:
        if (!$this instanceof MY_Controller) {
            $this->checkAuthorization();
        }
    }

    public static function &get_instance()
    {
        return self::$instance;
    }

    /**
     * Authorization logic
     */
    protected function checkAuthorization()
    {
        if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $data = json_decode(file_get_contents("php://input"));
            if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
                $data["status"] = "ok";
                echo json_encode($data);
                exit();
            }
            $defaultToken = isset($data->defaultToken) ? $data->defaultToken : '';
            $this->handleUnauthorizedRequest($defaultToken);
        } else {
            $this->handleAuthorizedRequest();
        }
    }

    protected function handleUnauthorizedRequest($defaultToken)
    {
        if (!isset($_SERVER["HTTP_AUTHORIZATION"])) {
            if (empty($defaultToken)) {
                echo json_encode([
                    "response_code" => "401",
                    "response_message" => "UNAUTHORIZED: Please provide an access token to continue accessing the API"
                ]);
                exit();
            }
            if ($defaultToken !== $this->config->item("defaultToken")) {
                echo json_encode([
                    "response_code" => "402",
                    "response_message" => "UNAUTHORIZED: Please provide a valid access token to continue accessing the API"
                ]);
                exit();
            }
        }
    }

    protected function handleAuthorizedRequest()
    {
        $headers = apache_request_headers();
        $token = str_replace("Bearer ", "", $headers['Authorization']);
        $kunci = $this->config->item('jwt_key');
        $userData = JWT::decode($token, $kunci);
        Utility::validateSession($userData->iat, $userData->exp);
        $tokenSession = Utility::tokenSession($userData);
    }
}
