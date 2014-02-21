<?php
define('SECURITY_DIR',realpath(dirname(__FILE__).'/..'));
define ( 'ROOT_DIR', SECURITY_DIR.'/' );
require '../framework/ark.php';

//application starting
ark_start(include ROOT_DIR.'data/config.php');

?>