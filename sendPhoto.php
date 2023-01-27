<?php
include_once './user_error_class.php';
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: DELETE");
header('Access-Control-Request-Headers: Content-Type, Authorization');
header("Access-Control-Allow-Credentials: true");

class new_photo extends user_error
{
    private $file_directory;
    private $file_id;
    function __construct()
    {
        parent::__construct();

    }
    function findId()
    {
        $myFile= fopen('galery.json', 'r');
        $file=fread($myFile, filesize('galery.json'));
        $array=json_decode($file, true);
        $number=count($array);
        $before_id=$array[$number-1]['id'];
        return $before_id+1;
    }
    function checkType()
    {
        $imageType=strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        switch($imageType)
        {
        case 'jpg': return true;
        case 'jpeg': return true;
        case 'png': return true;
        case 'webp': return true;
        default: return false;

        }
    }
    function exist()
    {
        return file_exists($this->file_directory);
    }
    function save_photo()
    {
        move_uploaded_file($_FILES["file"]["tmp_name"], $this->file_directory);
    }
    function add_record_file()
        {
            $myFile= fopen('galery.json', 'r');
            $file=fread($myFile, filesize('galery.json'));
            $galery=json_decode($file, true);
            $galery[]=array('photo'=>$this->file_directory,'id'=>$this->file_id, 'title'=>'', 'text'=>'');
            fclose($myFile);

            $myFile= fopen('galery.json', 'w');
            $json=json_encode($galery);
            fwrite($myFile,$json );
            fclose($myFile);

        }
        function execute()
        {
            if($this->check_data())
            {
            if(isset($_FILES['file']) && $this->checkType())
            {
               $this->file_id=$this->findId();
               $this->file_directory='.\\images\\'.$this->file_id.'.'.pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
               if(!$this->exist())
               {
                $this->save_photo();
                $this->add_record_file();
                return true;
               }
            }
            else return false;
        }else return false;
        }
    
}

$new_photo= new new_photo;
echo json_encode($new_photo->execute());



?>