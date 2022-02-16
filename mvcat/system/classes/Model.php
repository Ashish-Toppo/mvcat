<?php

class Model{
    // use trait debugger
    use modelDebugger;

    private $host = HOST;
    private $user = USER;
    private $database = DATABASE;
    private $password = PASSWORD;

    public $con;
    public $result;

    public function __construct () {

        $host = $this->host;
        $user = $this->user;
        $db = $this->database;
        $pswd = $this->password;
        
        try{
            $this->con = new PDO('mysql:host='.$host.';dbname='.$db, $user, $pswd);
        } catch (PDOException $e) {
            $this->showDBConnectionError($e);
        }
    }
}