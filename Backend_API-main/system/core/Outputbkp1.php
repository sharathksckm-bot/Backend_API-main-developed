<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CI_Output {

    protected $final_output = ''; // Ensure it is not NULL
    protected $headers = array();
    protected $mimes = array();
    protected $enable_profiler = FALSE;
    protected $parse_exec_vars = TRUE;

    public function __construct()
    {
        $this->mimes =& get_mimes();
        log_message('info', 'Output Class Initialized');
    }

    public function get_output()
    {
        return $this->final_output;
    }

    public function set_output($output)
    {
        $this->final_output = $output ?? ''; // Ensure it is not NULL
        return $this;
    }

    public function append_output($output)
    {
        if ($this->final_output === NULL)
        {
            $this->final_output = $output ?? '';
        }
        else
        {
            $this->final_output .= $output ?? '';
        }
        return $this;
    }

    public function set_header($header, $replace = TRUE)
    {
        if ($replace === TRUE)
        {
            $this->headers[$header] = $header;
        }
        else
        {
            $this->headers[] = $header;
        }
        return $this;
    }

    public function set_content_type($mime_type, $charset = NULL)
    {
        if (strpos($mime_type, '/') === FALSE)
        {
            $extension = ltrim($mime_type, '.');
            if (isset($this->mimes[$extension]))
            {
                $mime_type = is_array($this->mimes[$extension]) ? $this->mimes[$extension][0] : $this->mimes[$extension];
            }
        }

        $header = 'Content-Type: '.$mime_type
            .(empty($charset) ? '' : '; charset='.$charset);

        $this->headers[$header] = $header;
        return $this;
    }

    public function get_content_type()
    {
        foreach ($this->headers as $header)
        {
            if (strpos(strtolower($header), 'content-type:') === 0)
            {
                return trim(explode(':', $header, 2)[1]);
            }
        }
        return NULL;
    }

    public function set_status_header($code = 200, $text = '')
    {
        if (empty($code) OR ! is_numeric($code))
        {
            show_error('Status codes must be numeric', 500);
        }

        if (empty($text))
        {
            $stati = array(
                100 => 'Continue',
                200 => 'OK',
                301 => 'Moved Permanently',
                400 => 'Bad Request',
                401 => 'Unauthorized',
                403 => 'Forbidden',
                404 => 'Not Found',
                500 => 'Internal Server Error'
            );

            $text = $stati[$code] ?? 'Unknown Status';
        }

        $server_protocol = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1';
        $this->set_header($server_protocol.' '.$code.' '.$text, TRUE);
        return $this;
    }

    public function enable_profiler($val = TRUE)
    {
        $this->enable_profiler = is_bool($val) ? $val : TRUE;
        return $this;
    }

    public function cache($time)
    {
        if (!is_numeric($time))
        {
            return $this;
        }

        $this->set_header('Cache-Control: max-age='.$time.', public');
        $this->set_header('Expires: '.gmdate('D, d M Y H:i:s', time() + $time).' GMT');
        $this->set_header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');

        return $this;
    }

    public function display($output = '')
    {
        if ($output === '')
        {
            $output = $this->final_output;
        }

        if (empty($output))
        {
            return;
        }

        foreach ($this->headers as $header)
        {
            header($header);
        }

        echo $output;
        log_message('info', 'Final output sent to browser');
        log_message('debug', 'Total execution time: '.$this->elapsed_time());
    }

    protected function elapsed_time()
    {
        return number_format(microtime(TRUE) - ($_SERVER['REQUEST_TIME_FLOAT'] ?? 0), 4);
    }
}