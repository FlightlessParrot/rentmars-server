<?php

include_once './user_error_class.php';
//header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Access-Control-Request-Headers: Content-Type, Authorization');
header("Content-Type: application/json");
header("Access-Control-Allow-Credentials: true");



$a=new user_error;
echo json_encode($a->check_data());







?>