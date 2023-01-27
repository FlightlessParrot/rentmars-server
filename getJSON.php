<?php
header("Access-Control-Allow-Origin: http://mustang-jk.pl");
header("Access-Control-Allow-Headers: Content-Type");
header('Access-Control-Request-Headers: Content-Type');
header("Content-Type: aplication/json");
header("Access-Control-Allow-Credentials: true");

class returnPhotosArray
{
    private $js;
    private $encoded;
    function __construct()
    {
        $this->js = file_get_contents('php://input');
        $this->encoded = json_decode($this->js);
        
            $this->sendPhotos();
    }
    private function sendPhotos()
    {
        $myFile = fopen('galery.json', 'r') or die("Unable to open file!");
        $json = fread($myFile, filesize('galery.json'));
        echo $json;
       fclose($myFile);
    }
}
new returnPhotosArray;