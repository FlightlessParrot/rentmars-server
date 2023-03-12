<?php

class conn{
private $servername;
private $username;
private $password ;
public $PDO;
function __construct(){
//5lH80VD4D3
$this->servername = "localhost";
$this->username = "rentmars_admin"; 
$this->password = "5lH80VD4D3";
}
function connection()
{
    try{
        $this->PDO = new PDO("mysql:host=$this->servername;dbname=rentmars_products", $this->username, $this->password);
        return true;
    }catch(PDOException $e) {
        $message=$e->getMessage();
        die(array('success'=>false,'message'=> $message ));
      
        
      }
   
}

}

?>