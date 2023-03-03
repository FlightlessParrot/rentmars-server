<?php

include_once './PDO.php';
//header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: DELETE");
header('Access-Control-Request-Headers: Content-Type, Authorization');
header("Access-Control-Allow-Credentials: true");

class product
{
    private $PDO;
    private $connection;
    function __construct()
    {
        $connect=new conn;
        $this->connection=$connect->connection();
        if($this->connection)
        {
        $this->PDO=$connect->PDO;
        }
        else{
            die(json_encode(array('message'=>'wystąpił błąd z bazą danych')));
        }
    }
    
    private function checkImageType($fileName)
    {
        $imageType=strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        switch($imageType)
        {
        case 'jpg': return true;
        case 'jpeg': return true;
        case 'png': return true;
        case 'webp': return true;
        default: return false;

        }
    }
   private function fileExist($fileDirectory)
    {
        if(file_exists($fileDirectory))
        {
            return true;
        }else{
            die(json_encode(array('success'=>false,'message'=>'Plik o takiej nazwie już istnieje. ')));
        }
    }
    private function save_photo($file, $fileDirectory)
    {
        move_uploaded_file($file, $fileDirectory);
    }
    function getImageFromId()
    {
        if(isset($_GET['id']) and isset($_GET['shop']))
        {
            $id=(int)htmlspecialchars(addslashes($_GET['id']));
            $shop=(int)htmlspecialchars(addslashes($_GET['shop']));
            $sql=$this->PDO->prepare('SELECT path FROM images WHERE productId=:id AND shop=:shop');
            $sql->bindParam('id',$id, PDO::PARAM_INT);
            $sql->bindParam('shop',$shop, PDO::PARAM_INT);
            $sql->execute();
            $data=$sql->fetchAll(PDO::FETCH_ASSOC);
            return array('success'=>true,'images'=>$data);
           
        } 
        else {die(json_encode(array('success'=>false,'message'=>'Przesłano za mało danych. ')));}
    }
    private function addImageToDataBase($path, $main, $productId, $shop)
    {
        $sql=$this->PDO->prepare('INSERT INTO images(path, main, productId, shop) VALUES (:path, :main, :productId, :shop)');
        $sql->bindParam('path',$path, PDO::PARAM_STR);
        $sql->bindParam('main',$main, PDO::PARAM_BOOL);
        $sql->bindParam('productId',$productId, PDO::PARAM_INT);
        $sql->bindParam('shop',$shop, PDO::PARAM_BOOL);
        if($sql->execute()) return $this->PDO->lastInsertId();
        else die(array('success' => false, 'message' => 'Błąd bazy danych '));
    }
    function addImage()
    {
        if (!(isset($_GET['id']) and isset($_GET['main']) && isset($_GET['shop']))) {
            die(json_encode(array('success' => false, 'message' => 'Przesłano za mało danych. ')));
        }if (!getimagesize($_FILES['image']['tmp_name'])) {
            die(json_encode(array('success' => false, 'message' => 'Plik nie został załadowany poprawnie')));
        }
        $id = (int)htmlspecialchars(addslashes($_GET['id']));
        $main = (int)htmlspecialchars(addslashes($_GET['main']));
        $shop = (int)htmlspecialchars(addslashes($_GET['shop']));
        if ($this->checkImageType($_FILES['image']['tmp_name'])) {
            move_uploaded_file($_FILES['image']['tmp_name'], './images/' + $id + $_FILES['image']['name']);
        }
        
     
    }
    function addProduct()
    {
        if(!(isset($_POST['shop']) && isset($_POST['name']) && $_POST['name']!=='' && isset($_POST['description']) && $_POST['description']!==''))
        {
            die(json_encode(array('success'=>false, 'message'=>'Nie przesłano wymaganych danych')));
        }else{
        $shop=(int)htmlspecialchars(addslashes($_POST['shop']));
        $name=htmlspecialchars(addslashes($_POST['name']));
        $new=(int)(isset($_POST['new']) ? true: false);
        $price=0.00;
        $description='';
       
        if(isset($_POST['price']) && $_POST['price']!=='')
        {
            $price=floatval(filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_FLOAT));
        }
        isset($_POST['description']) && $description=htmlspecialchars($_POST['description']);
        if($shop===1)
        {
           
            $sql=$this->PDO->prepare('INSERT INTO shop(name, price, description, new) VALUES (:name, :price, :description, :new)');
            $sql->bindParam('name',$name, PDO::PARAM_STR);
            $sql->bindParam('price',$price, PDO::PARAM_STR);
            $sql->bindParam('description',$description, PDO::PARAM_STR);
            $sql->bindParam('new',$new, PDO::PARAM_BOOL);
            $success=$sql->execute(); 
            $id=$this->PDO->lastInsertId();
            mkdir('./images/'.$id);
            return array('success'=>$success,'id'=>$id );
        }else{
            $sql=$this->PDO->prepare('INSERT INTO rent(name, description, new) VALUES (?, ?, ?)');
            $sql->bindParam('name',$name, PDO::PARAM_STR);
            $sql->bindParam('description',$description, PDO::PARAM_STR);
            $sql->bindParam('new',$new, PDO::PARAM_BOOL);
            return array('success'=>$sql->execute());
            
        }}


        

        
    }


    
        
    
}
