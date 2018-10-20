<?php
ob_start();
ini_set('max_execution_time', 90000);
ini_set("memory_limit", -1);
error_reporting(E_ALL ^ E_DEPRECATED);

if ($_SERVER['HTTP_HOST'] == "localhost") {
    $isLocal = true;
} else {
    $isLocal = false;
}

if ($isLocal == true) {
    DEFINE('DB_SERVER', 'localhost');
    DEFINE('DB_SERVER_USERNAME', 'DATABASE-USERNAME');
    DEFINE('DB_SERVER_PASSWORD', 'DATABASE-USER-PASSWORD');
    DEFINE('DB_DATABASE', 'YOUR-DATABASE-NAME');
    DEFINE('SITE_FOLDER', 'YOUR-SITE-FOLDER');
    $conn = mysqli_connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD,DB_DATABASE) or die("Error " . mysql_error($conn));
    DEFINE("SITE_URL_REMOTE", "http://" . $_SERVER['HTTP_HOST'] . '/' . SITE_FOLDER);
}
else{
	// Use live credentials
} 

mysqli_query($conn,"SET SESSION time_zone = '+0:00'");
date_default_timezone_set('UTC');
DEFINE('ENCRYPT_KEY', 'vah@_inf0s0l');
mysqli_query($conn,"SET NAMES 'utf8'");
mysqli_query($conn,"SET CHARACTER SET utf8");
?>