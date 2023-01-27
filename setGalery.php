<?php

class setGalery
{
    public $arr;
    function __construct()
    {
     $this->arr=array();
        for($x=0; $x<=23; $x++)
        {
            $str=strval($x);
            $photo='.\\images\\galery\\'.$str.'.jpg';
            $id=$x;
            $title='';
            $text='';
            $this->arr[]=array('photo'=>$photo, 'id'=>$id, 'title'=>$title, 'text'=>$text);
        }
    }
    function saveArray()
    {
        $myFile= fopen('galery.json', 'w') or die("Unable to open file!");
        $json=json_encode($this->arr);
        fwrite($myFile,$json );
        fclose($myFile);

    }
}

// $writer=new setGalery;
// $writer->saveArray();
?>