
<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: PUT");
header('Access-Control-Request-Headers: Content-Type, Authorization');
header("Content-Type: application/json");
header("Access-Control-Allow-Credentials: true");
require_once './user_error_class.php';

class change_password extends user_error
{
private $new_password;

function __construct(){
    parent::__construct();
$json = file_get_contents('php://input');
$data =base64_decode(json_decode($json));
$this->new_password=$data;
}

function change_password()
{
    $myFile=fopen('login.json','r');
    $file=json_decode(fread($myFile, filesize('login.json')), true);
    $data=array('login'=>$file['login'], 'password'=>$this->new_password);
    $json=json_encode($data);
    fclose($myFile);
    $myFile=fopen('login.json','w');
    fwrite($myFile, $json);
    fclose($myFile);
}
function execute()
{
    if($this->check_data())
    {
        $this->change_password();
        return true;
    }
    else return false;
} 
}
$newPass= new change_password;
echo json_encode($newPass->execute())
?>