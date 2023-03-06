<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST");
header('Access-Control-Request-Headers: Content-Type, Authorization');
header("Content-Type: multipart/form-data");
header("Access-Control-Allow-Credentials: true");
include_once '../user.php';
include_once '../product.php';
$user= new user;
if($user->authenticate()){
    $product = new product;
    
    echo json_encode($product->addImage());
}
?>