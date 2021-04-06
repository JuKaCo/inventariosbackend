<?php

namespace App\Application\Actions\RepositoryConection;
use \PDO;
class Conect {

    private $db;

    public function __construct() {
        $dbHost = $_ENV['DB_AUTH_HOST'];
        $dbUser = $_ENV['DB_AUTH_USER'];
        $dbPass = $_ENV['DB_AUTH_PASSWORD'];
        $dbName = $_ENV['DB_AUTH_DB'];
        $dbtype = $_ENV['DB_AUTH_TYPE'];
        $dbport = $_ENV['DB_AUTH_PORT'];
        $dbcollation=$_ENV['DB_AUTH_COLLATION'];
        $connect = $dbtype . ":host=" . $dbHost . ";dbname=" . $dbName . ";port=" . $dbport;
        $dbConnecion = new PDO($connect, $dbUser, $dbPass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES ".$dbcollation));
        $dbConnecion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db = $dbConnecion;
    }

    function getConection() {
        return $this->db;
    }

}
