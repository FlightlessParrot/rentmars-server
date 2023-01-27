<?php

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: DELETE");
header('Access-Control-Request-Headers: Content-Type, Authorization');
header("Content-Type: application/json");
header("Access-Control-Allow-Credentials: true");
require_once './user_error_class.php';
class delete_photo extends user_error
{
private $id;
function __construct()
{
    parent::__construct();
    $this->id=$_GET['id'];
}
function delete()
{
   $ident=intval($this->id);
   
    if($this->check_data())
    {
        
        $myFile= fopen('galery.json', 'r');
        $file=fread($myFile, filesize('galery.json'));
        $galery=json_decode($file, true);
        $new_galery=array_filter($galery, function($var) use ($ident){
            if($var["id"]!==$ident)
        {
            return true;
        }else return false;});
        $element=array_filter($galery, function($var) use ($ident){
            if($var["id"]===$ident)
        {
            return true;
        }else return false;});
        array_map((fn($element)=>unlink($element['photo'])),$element );
        $order_galery=array();
        foreach($new_galery as $value)
        {
            $order_galery[]=$value;
        }
        fclose($myFile);
        $myFile= fopen('galery.json', 'w');
        $json=json_encode($order_galery);
        fwrite($myFile,$json );
        fclose($myFile);
        return true;

    }
    else return false;
}

}
$delete= new delete_photo;
echo json_encode($delete->delete());


?>