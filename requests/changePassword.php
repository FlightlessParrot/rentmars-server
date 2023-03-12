<?php
//header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: PUT");
header('Access-Control-Request-Headers: Content-Type, Authorization');
header("Content-Type: application/json");
header("Access-Control-Allow-Credentials: true");

include_once '../user.php';
$user=new user();
if($user->authenticate())
{
    $result=$user->change_password();
    echo json_encode(($result));
}

?>