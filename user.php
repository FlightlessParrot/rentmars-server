<?php
//header("Access-Control-Allow-Origin: http://localhost:3000");
// header("Access-Control-Allow-Headers: Content-Type, Authorization");
// header("Access-Control-Allow-Methods: PUT");
// header('Access-Control-Request-Headers: Content-Type, Authorization');
// header("Content-Type: application/json");
// header("Access-Control-Allow-Credentials: true");
class user
{
    protected $user_login;
    protected $user_password;
    protected $cred;
    public $file_name;
    function __construct()
    {

        //$head= getallheaders();
        $this->file_name = "login.json";
        $this->user_login = $_SERVER['PHP_AUTH_USER'];
        $this->user_password = $_SERVER['PHP_AUTH_PW'];
    }
    function authenticate()
    {
        $dane = fopen($this->file_name, "r");
        $file = fread($dane, filesize($this->file_name));
        $this->cred = json_decode($file, true);
        if ($this->cred['login'] === $this->user_login && $this->cred['password'] === $this->user_password) {

         
            return true;
        } else {

            return false;
        }
    }
    function change_password()
    {

   
       
        $new_password = $_POST['newPass'];
      //base64_decode(json_decode($json));
        try{
        $myFile = fopen('login.json', 'r');
        $file = json_decode(fread($myFile, filesize('login.json')), true);
        $data = array('login' => $file['login'], 'password' => $new_password);
        $json_data = json_encode($data);
        fclose($myFile);

        $myFile = fopen('login.json', 'w');
        fwrite($myFile, $json_data);
        fclose($myFile);
        return(array("success"=>true, "message"=> 'Hasło zostało zmienione. '));
        }
        catch(Exception $e)
        {
            $message=$e->getMessage();
            return(array("success"=>false, "message"=> $message));
        }
    }
}
?>