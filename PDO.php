<?php

class conn{
private $servername;
private $username;
private $password ;
public $PDO;
function __construct(){

$this->servername = "localhost";
$this->username = "products"; 
$this->password = "admin";
}
function connection()
{
    try{
        $this->PDO = new PDO("mysql:host=$this->servername;dbname=products", $this->username, $this->password);
        return true;
    }catch(PDOException $e) {
        return false;
        
      }
   
}

}

?>