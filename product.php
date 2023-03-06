<?php

include_once '../PDO.php';
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
        $connect = new conn;
        $this->connection = $connect->connection();
        if ($this->connection) {
            $this->PDO = $connect->PDO;
        } else {
            die(json_encode(array('message' => 'wystąpił błąd z bazą danych')));
        }
    }

   public function getProducts()
    {
        if(!(isset($_GET['category'])))
        {
            die(json_encode(array('success'=>false, 'message'=>'Nie wybrano kategorii')));
        }
        $shop=htmlspecialchars(addslashes($_GET['category']))==='shop'?'shop':'rent';
        $search=isset($_GET['search']) && $_GET['search']!=='' ? '%'.htmlspecialchars(addslashes($_GET['search'])).'%' :'';
        $new= isset($_GET['new']) ? true : false;
        $request='SELECT * FROM '.$shop;
        $additional='';
        if($new)
        {
            $additional.=' WHERE new=1';
            if ($search!=='')$additional.=' AND';
        }
        if($search!=='')
        {
            if(!$new)$additional.=' WHERE';
            $additional.=' name LIKE :search';
        }
        $request.=$additional;
         echo($request);
        
        $sql=$this->PDO->prepare($request);
        if($search!=='')$sql->bindParam('search', $search, PDO::PARAM_STR);
       
        $sql->execute();
        $products=$sql->fetchAll(PDO::FETCH_ASSOC);
        
        $secRequest='SELECT id, images.*  from '.$shop.' INNER JOIN images ON '.$shop.'.id=images.productId'.$additional;
        echo($secRequest);
        $srq=$this->PDO->prepare($secRequest);
        if($search!=='')$srq->bindParam('search', $search, PDO::PARAM_STR);
        $srq->execute();
        $results=$srq->FetchAll(PDO::FETCH_ASSOC);
        return array('products'=>$products, 'images'=>$results);
        

    }
    private function checkImageType($fileName)
    {
        $imageType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        switch ($imageType) {
            case 'jpg':
                return true;
            case 'jpeg':
                return true;
            case 'png':
                return true;
            case 'webp':
                return true;
            default:
                return false;
        }
    }
    private function fileExist($fileDirectory)
    {
        if (file_exists($fileDirectory)) {
            return true;
        } else {
            die(json_encode(array('success' => false, 'message' => 'Plik o takiej nazwie już istnieje. ')));
        }
    }

    function getImageFromId()
    {
        if (isset($_GET['id']) and isset($_GET['shop'])) {
            $id = (int)htmlspecialchars(addslashes($_GET['id']));
            $shop = (int)htmlspecialchars(addslashes($_GET['shop']));
            $sql = $this->PDO->prepare('SELECT path FROM images WHERE productId=:id AND shop=:shop');
            $sql->bindParam('id', $id, PDO::PARAM_INT);
            $sql->bindParam('shop', $shop, PDO::PARAM_INT);
            $sql->execute();
            $data = $sql->fetchAll(PDO::FETCH_ASSOC);
            return array('success' => true, 'images' => $data);
        } else {
            die(json_encode(array('success' => false, 'message' => 'Przesłano za mało danych. ')));
        }
    }
    private function removeMainImage($productId)
    {
        $sql = $this->PDO->prepare('INSERT INTO images(main) VALUES (0) WHERE productId=:id');
        $sql->bindParam('productId', $productId, PDO::PARAM_INT);
        $sql->execute();
    }
    private function addImageToDataBase($path, $main, $productId, $shop)
    {
        if ($main) $this->removeMainImage($productId);
        $sql = $this->PDO->prepare('INSERT INTO images(path, main, productId, shop) VALUES (:path, :main, :productId, :shop)');
        $sql->bindParam('path', $path, PDO::PARAM_STR);
        $sql->bindParam('main', $main, PDO::PARAM_BOOL);
        $sql->bindParam('productId', $productId, PDO::PARAM_INT);
        $sql->bindParam('shop', $shop, PDO::PARAM_BOOL);
        if ($sql->execute()) return $this->PDO->lastInsertId();
        else die(array('success' => false, 'message' => 'Błąd bazy danych '));
    }
    function addImage()
    {
        if (!(isset($_GET['id']) and isset($_GET['main']) && isset($_GET['shop']))) {
            die(json_encode(array('success' => false, 'message' => 'Przesłano za mało danych. ')));
        }
        if (!getimagesize($_FILES['image']['tmp_name'])) {
            die(json_encode(array('success' => false, 'message' => 'Plik nie został załadowany poprawnie')));
        }
        $id = (int)htmlspecialchars(addslashes($_GET['id']));
        $main = (int)htmlspecialchars(addslashes($_GET['main']));
        $shop = (int)htmlspecialchars(addslashes($_GET['shop']));
        $path = './images/' + $id + $_FILES['image']['name'];
        if ($this->checkImageType($_FILES['image']['tmp_name']) && $this->fileExist($path)) {
            $this->addImageToDataBase($path, $main, $id, $shop);
            move_uploaded_file($_FILES['image']['tmp_name'], $path);
            return array('success' => true);
        }
    }
    function remove()
    {
        $dataBase = htmlspecialchars(addslashes($_GET['category']));
        $id = htmlspecialchars(addslashes($_GET['id']));
        if ($dataBase === 'images') $this->removeImage($id);
        else $this->removeAllImages($dataBase, $id);
        $sql = $this->PDO->prepare('DELETE from :dataBase WHERE id=:id');
        $sql->bindParam('dataBase', $dataBase, PDO::PARAM_STR);
        $sql->bindParam('id', $id, PDO::PARAM_INT);
        $response = $sql->execute();
        return (array('success' => $response));
    }
    private function removeImage($id)
    {

        $sql = $this->PDO->prepare('SELECT path FROM images WHERE id=:id');
        $sql->bindParam('id', $id, PDO::PARAM_INT);
        $sql->execute();
        $response = $sql->fetch(PDO::FETCH_NUM);
        unlink($response[0]);
    }
    private function removeAllImages($dataBase, $productId)
    {

        $sql = $this->PDO->prepare('SELECT path FROM images WHERE id=:id');
        $sql->bindParam('id', $productId, PDO::PARAM_INT);
        $sql->execute();
        $response = $sql->fetchAll(PDO::FETCH_ASSOC);
        foreach ($response as $value) {
            unlink($value->path);
        }
        rmdir('./images/' . $productId);
    }
    function addProduct()
    {
        if (!(isset($_POST['shop']) && isset($_POST['name']) && $_POST['name'] !== '' && isset($_POST['description']) && $_POST['description'] !== '')) {
            die(json_encode(array('success' => false, 'message' => 'Nie przesłano wymaganych danych')));
        }
        $shop = (int)htmlspecialchars(addslashes($_POST['shop']));
        $name = htmlspecialchars(addslashes($_POST['name']));
        $new = (int)(isset($_POST['new']) ? true : false);
        $price = 0.00;
        $description = '';
        $dataBase = $shop ? 'shop' : 'rent';
        if (isset($_POST['price']) && $_POST['price'] !== '') {
            $price = floatval(filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_FLOAT));
        }
        isset($_POST['description']) && $description = htmlspecialchars($_POST['description']);


        $sql = $this->PDO->prepare('INSERT INTO :dataBase(name, price, description, new) VALUES (:name, :price, :description, :new)');
        $sql->bindParam('name', $name, PDO::PARAM_STR);
        $sql->bindParam('price', $price, PDO::PARAM_STR);
        $sql->bindParam('description', $description, PDO::PARAM_STR);
        $sql->bindParam('new', $new, PDO::PARAM_BOOL);
        $sql->bindParam('dataBase', $dataBase, PDO::PARAM_STR);
        $success = $sql->execute();
        $id = $this->PDO->lastInsertId();
        mkdir('./images/' . $id);
        return array('success' => $success, 'id' => $id);
    }
    function changeProduct()
    {
        if (!(isset($_POST['shop']) && isset($_POST['name']) && $_POST['name'] !== '' && isset($_POST['description']) && $_POST['description'] !== '' && isset($_POST['id']) && $_POST['id'] !== '')) {
            die(json_encode(array('success' => false, 'message' => 'Nie przesłano wymaganych danych')));
        } else {
            $shop = (int)htmlspecialchars(addslashes($_POST['shop']));
            $id = (int)htmlspecialchars(addslashes($_POST['id']));
            $name = htmlspecialchars(addslashes($_POST['name']));
            $new = (int)(isset($_POST['new']) ? true : false);
            $price = 0.00;
            $description = '';
            $dataBase = $shop ? 'shop' : 'rent';
            if (isset($_POST['price']) && $_POST['price'] !== '') {
                $price = floatval(filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_FLOAT));
            }
            isset($_POST['description']) && $description = htmlspecialchars($_POST['description']);


            $sql = $this->PDO->prepare('INSERT INTO dataBase(name, price, description, new) VALUES (:name, :price, :description, :new) WHERE id=:id');
            $sql->bindParam('name', $name, PDO::PARAM_STR);
            $sql->bindParam('price', $price, PDO::PARAM_STR);
            $sql->bindParam('description', $description, PDO::PARAM_STR);
            $sql->bindParam('new', $new, PDO::PARAM_BOOL);
            $sql->bindParam('id', $id, PDO::PARAM_INT);
            $sql->bindParam('dataBase', $dataBase, PDO::PARAM_STR);
            $success = $sql->execute();
            return array('success' => $success, 'id' => $id);
        }
    }
}
