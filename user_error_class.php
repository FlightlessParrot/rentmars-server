<?php

class user_error
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
    function check_data()
    {
        $dane = fopen($this->file_name, "r");
        $file = fread($dane, filesize($this->file_name));
        $this->cred = json_decode($file, true);
        if ($this->cred['login'] === $this->user_login && $this->cred['password'] === $this->user_password) {

            fclose($dane);
            return $this->accept();
        } else {


            fclose($dane);
            return $this->reject();
        }
    }
    function accept()
    {
        header('WWW-Authenticate: Basic realm="logowanie"');
        header("HTTP/1.1 200 OK");
        return true;
    }
    function reject()
    {
        //     header('HTTP/1.1 401 Unauthorized');
        //    header('WWW-Authenticate: Basic realm="logowanie"');

        return false;
    }
}
