<?php
define('SECURITY_DIR',dirname(__FILE__));
require './framework/ark.php';

//application starting
ark_start(include ROOT_DIR.'data/config.php');

?>