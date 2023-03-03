<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
// header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET");
// header('Access-Control-Request-Headers: Content-Type, Authorization');
//header("Content-Type: multipart/form-data");
header("Access-Control-Allow-Credentials: true");

include_once './product.php';

$image=new product;
echo json_encode($image->getImageFromId());


?>