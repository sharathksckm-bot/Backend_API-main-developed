<?php
defined('BASEPATH') OR exit('No direct script access allowed');


$hook['pre_system'][] = array(
    'class'    => 'Cors',
    'function' => 'handle',
    'filename' => 'Cors.php',
    'filepath' => 'hooks',
    'params'   => array()
);


/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/userguide3/general/hooks.html
|
*/
