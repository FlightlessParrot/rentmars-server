<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Authorization");

header('Access-Control-Request-Headers: Authorization');
//header("Content-Type: multipart/form-data");
header("Access-Control-Allow-Credentials: true");
include_once '../user.php';
include_once '../product.php';
$user= new user;
if($user->authenticate()){
    $product = new product;
   // header('Content-Type: application/json; charset=utf-8');
    echo json_encode($product->remove());
}



?>