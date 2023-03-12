<?php

include_once '../PDO.php';
//header("Access-Control-Allow-Origin: http://localhost:3000");
// header("Access-Control-Allow-Headers: Content-Type, Authorization");
// header("Access-Control-Allow-Methods: DELETE");
// header('Access-Control-Request-Headers: Content-Type, Authorization');
// header("Access-Control-Allow-Credentials: true");

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
        if (!(isset($_GET['category']))) {
            die(json_encode(array('success' => false, 'message' => 'Nie wybrano kategorii')));
        }
        $shop=filter_var(htmlspecialchars(addslashes($_GET['shop'])), FILTER_VALIDATE_BOOL) ? 'shop' : 'rent' ;
        $category = filter_var(addslashes($_GET['category']),FILTER_VALIDATE_BOOLEAN);
        $search = isset($_GET['search']) && $_GET['search'] !== '' ? '%' . htmlspecialchars(addslashes($_GET['search'])) . '%' : '';
        $new = isset($_GET['new']);
        $request = 'SELECT * FROM ' . $shop;
        $additional = ' WHERE building=:category';
        if ($new) {
            $additional .= ' AND new=1';
        }
        if ($search !== '') {
            $additional .= ' AND name LIKE :search';
        }
        $request .= $additional;

        $sql = $this->PDO->prepare($request);
        $sql->bindParam('category', $category, PDO::PARAM_BOOL);
        if ($search !== '') $sql->bindParam('search', $search, PDO::PARAM_STR);
        
        $sql->execute();
        $products = $sql->fetchAll(PDO::FETCH_ASSOC);

        $secRequest = 'SELECT '.$shop.'.id, images.*  from '.$shop. ' INNER JOIN images ON '.$shop.'.id=images.productId' . $additional;
        $srq = $this->PDO->prepare($secRequest);
        $srq->bindParam('category', $category, PDO::PARAM_BOOL);
        if ($search !== '') $srq->bindParam('search', $search, PDO::PARAM_STR);
        $srq->execute();
        $results = $srq->FetchAll(PDO::FETCH_ASSOC);
        return array('products' => $products, 'images' => $results);
    }
    public function getProductFromId()
    {
        if (!(isset($_GET['id']) && isset($_GET['shop'])) ) {
            die(json_encode(array('success' => false, 'message' => 'Nie wybrano kategorii')));
        }
        $shop=filter_var(htmlspecialchars(addslashes($_GET['shop'])), FILTER_VALIDATE_BOOL) ? 'shop' : 'rent' ;
        $id=filter_var(addslashes($_GET['id']),FILTER_VALIDATE_INT);
        $request = 'SELECT * FROM ' . $shop .' WHERE id=:id';
        $sql = $this->PDO->prepare($request);
        $sql->bindParam('id', $id, PDO::PARAM_INT);
        try{
            $sql->execute();
        }catch(Exception $e)
        {
            die(json_encode(array('success'=>false, 'message'=>'błąd bazy danych')));
        }
        $result=$sql->FetchAll(PDO::FETCH_ASSOC);
        $images=$this->getImageFromId();
        return array('success'=>true, 'product' => $result, 'images'=>$images['images']);


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
            die(json_encode(array('success' => false, 'message' => $fileName)));
                
        }
    }
    private function fileExist($fileDirectory)
    {
        if (!file_exists($fileDirectory)) {
            return true;
        } else {
            die(json_encode(array('success' => false, 'message' => 'Plik o takiej nazwie już istnieje. ')));
        }
    }

    function getImageFromId()
    {
        if (isset($_GET['id']) and isset($_GET['shop']) ) {
            $id = (int)htmlspecialchars(addslashes($_GET['id']));
            $shop = filter_var(addslashes($_GET['shop']), FILTER_VALIDATE_BOOLEAN);
            $sql = $this->PDO->prepare('SELECT * FROM images WHERE productId=:id and shop=:shop' );
            $sql->bindParam('id', $id, PDO::PARAM_INT);
            $sql->bindParam('shop', $shop, PDO::PARAM_BOOL);
            $sql->execute();
            $data = $sql->fetchAll(PDO::FETCH_ASSOC);
            return array('success' => true, 'images' => $data);
        } else {
            die(json_encode(array('success' => false, 'message' => 'Przesłano za mało danych. ')));
        }
    }
    private function removeMainImage($productId, $shop)
    {
        $sql = $this->PDO->prepare('UPDATE images SET main=0 WHERE productId=:productId and shop=:shop');
        $sql->bindParam('productId', $productId, PDO::PARAM_INT);
        $sql->bindParam('shop', $shop, PDO::PARAM_BOOL);
        $sql->execute();
    }
    function setMainImage()
    {
        if(!(isset($_POST['id']) && isset($_POST['productId']) && isset($_POST['shop']))) 
        {
            die(json_encode(array('success'=>false, 'message'=>'przesłano za mało danych.')));
        }
        $id=filter_var(addslashes($_POST['id']), FILTER_VALIDATE_INT);
        $productId=filter_var(addslashes($_POST['productId']), FILTER_VALIDATE_INT);
        $shop=filter_var(addslashes($_POST['shop']), FILTER_VALIDATE_BOOL);
        $this->removeMainImage($productId, $shop);
        $sql = $this->PDO->prepare('UPDATE images SET main=1 WHERE id=:id' );
        $sql->bindParam('id', $id, PDO::PARAM_INT);
        $result=$sql->execute();
        return array('success'=>$result);
    }
    private function addImageToDataBase($path, $main, $productId, $shop)
    {
        if ($main) $this->removeMainImage($productId, $shop);
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
        if (!(isset($_POST['productId']) && isset($_POST['shop']) && isset($_FILES['photo']))) {
            die(json_encode(array('success' => false, 'message' => 'Przesłano za mało danych. ')));
        }
        if (!getimagesize($_FILES['photo']['tmp_name'])) {
            die(json_encode(array('success' => false, 'message' => 'Plik nie został załadowany poprawnie')));
        }
        $targetDir = "uploads/";
        $targetFile=$targetDir.$_FILES['photo']['name'];
        $id = (int)htmlspecialchars(addslashes($_POST['productId']));
        $main = isset($_POST['main']);
        $shopBool=filter_var(addslashes($_POST['shop']),FILTER_VALIDATE_BOOLEAN);
        $shop = $shopBool ? 'shop' :'rent';
        $path = '../images/'.$shop .'/'. strval($id) .'/'.$_FILES['photo']['name'];
       $imagesPath='/images/'.$shop .'/'. strval($id) .'/'.$_FILES['photo']['name'];
        if ($this->checkImageType($targetFile) && $this->fileExist($path)) {
            $this->addImageToDataBase($imagesPath, $main, $id, $shopBool);
            move_uploaded_file($_FILES['photo']['tmp_name'], $path);
            return array('success' => true);
        }
        else return array('success' => false);
    }
    function remove()
    {
        $database = htmlspecialchars(addslashes($_GET['database']));
        $id = (int)htmlspecialchars(addslashes($_GET['id']));
        $shop =($database!=='images' && $database==='shop') ? 'shop' :'rent';;
        if ($database === 'images') 
        {
            $this->removeOneImage($id);
            $sql = $this->PDO->prepare('DELETE from '.$database.' WHERE id=:id');
            $sql->bindParam('id', $id, PDO::PARAM_INT);
            $response = $sql->execute();
            return (array('success' => $response));
        }
        else 
        {$this->removeAllImages($id, $shop);
        $sql = $this->PDO->prepare('DELETE from '.$shop.' WHERE id=:id');
        $sql->bindParam('id', $id, PDO::PARAM_INT);
        $response = $sql->execute();
        return (array('success' => $response, 'message'=>'Obiekt został usunięty'));
        }
    }
    private function removeOneImage($photoId)
    {

        $sql = $this->PDO->prepare('SELECT path FROM images WHERE id=:id');
        $sql->bindParam('id', $photoId, PDO::PARAM_INT);
        $sql->execute();
        $response = $sql->fetch(PDO::FETCH_NUM);
        $path='..'.$response[0];
        unlink($path);
    }
    private function removeAllImages( $productId, $shop)
    {
        $shopBool= $shop==='shop';
        $sql = $this->PDO->prepare('SELECT path FROM images WHERE productId=:id and shop=:shop');
        $sql->bindParam('id', $productId, PDO::PARAM_INT);
        $sql->bindParam('shop', $shopBool, PDO::PARAM_BOOL);
        
        $sql->execute();
        $response = $sql->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($response as $value) {
            
            unlink('..'.$value['path']);
        }
        rmdir('../images/'.$shop .'/'. $productId);
        $sql = $this->PDO->prepare('DELETE FROM images WHERE productId=:id and shop=:shop');
        $sql->bindParam('id', $productId, PDO::PARAM_INT);
        $sql->bindParam('shop', $shopBool, PDO::PARAM_BOOL);
        try{
            $sql->execute();}
            catch(PDOException $e)
            {
                $message=$e->getMessage();
                die(json_encode(array('success'=>false, 'message'=>'Błąd bazy danych. '.$message)));
            }
    }
    function addProduct()
    {
        if (!(isset($_POST['shop']) && isset($_POST['name']) && $_POST['name'] !== '' && isset($_POST['description']) 
        && $_POST['description'] !== '') && isset($_POST['category'])) 
        {
            die(json_encode(array('success' => false, 'message' => 'Nie przesłano wymaganych danych')));
        }
        $shop = filter_var(addslashes($_POST['shop']),FILTER_VALIDATE_BOOLEAN);
        $name = htmlspecialchars(addslashes($_POST['name']));
        $new = (int)(isset($_POST['new']) ? true : false);
        $category = filter_var(addslashes($_POST['category']),FILTER_VALIDATE_BOOLEAN);
        $price = 0.00;
        $description = '';
        $dataBase = $shop ? 'shop' : 'rent';
        if (isset($_POST['price']) && $_POST['price'] !== '') {
            $price = floatval(filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_FLOAT));
        }
        isset($_POST['description']) && $description = htmlspecialchars($_POST['description']);
        if($shop)$statement='INSERT INTO ' . $dataBase . '(name, price, description, new, building) VALUES (:name, :price, :description, :new, :category)';
        if(!$shop) $statement='INSERT INTO ' . $dataBase . '(name, description, new, building) VALUES (:name,  :description, :new, :category)';
        $sql = $this->PDO->prepare($statement);
        $sql->bindParam('name', $name, PDO::PARAM_STR);
        $shop and $sql->bindParam('price', $price, PDO::PARAM_STR);
        $sql->bindParam('description', $description, PDO::PARAM_STR);
        $sql->bindParam('new', $new, PDO::PARAM_BOOL);
        $sql->bindParam('category', $category, PDO::PARAM_BOOL);
        $success = $sql->execute();
        $id = $this->PDO->lastInsertId();
        mkdir('../images/'.$dataBase.'/' . $id);
        return array('success' => $success, 'id' => $id);
    }
    function changeProduct()
    {
        if (!(isset($_POST['shop']) && isset($_POST['name']) && $_POST['name'] !== '' && isset($_POST['description']) 
        && $_POST['description'] !== '' && isset($_POST['id']) && $_POST['id'] !== '') && isset($_POST['category'])) 
        {
            die(json_encode(array('success' => false, 'message' => 'Nie przesłano wymaganych danych')));
        } else {
            $shop = filter_var(addslashes($_POST['shop']),FILTER_VALIDATE_BOOLEAN) ? 'shop' :'rent';
            $id = (int)htmlspecialchars(addslashes($_POST['id']));
            $name = htmlspecialchars(addslashes($_POST['name']));
            $new = isset($_POST['new']);
            $price = 0.00;
            $description = htmlspecialchars(addslashes($_POST['description']));
            $dataBase = $shop ? 'shop' : 'rent';
            $category = filter_var(addslashes($_POST['category']),FILTER_VALIDATE_BOOLEAN);
            if (isset($_POST['price']) && $_POST['price'] !== '' && $_POST['price'] !== '0') {
                $price = floatval(filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_FLOAT));
            }

            $sql = $this->PDO->prepare('UPDATE ' . $dataBase . ' SET name=:name, price=:price, description=:description, new=:new, building=:category WHERE id=:id');
            $sql->bindParam('name', $name, PDO::PARAM_STR);
            $sql->bindParam('price', $price, PDO::PARAM_STR);
            $sql->bindParam('description', $description, PDO::PARAM_STR);
            $sql->bindParam('new', $new, PDO::PARAM_BOOL);
            $sql->bindParam('category', $category, PDO::PARAM_BOOL);
            $sql->bindParam('id', $id, PDO::PARAM_INT);
            try{
            $success = $sql->execute();
            return array('success' => $success, 'id' => $id);
            }
            catch(PDOException $e)
            {
                $message = $e->getMessage();
                die(json_encode(array('success' => false, 'message' => 'błąd bazy danych. '.$message)));
            }
        }
    }
}
?>