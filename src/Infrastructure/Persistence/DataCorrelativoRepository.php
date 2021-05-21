<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Actions\RepositoryConection\Conect;
use App\Domain\CorrelativoRepository;
use \PDO;
use AbmmHasan\Uuid;

class DataCorrelativoRepository implements CorrelativoRepository {

    /**
     * @var data[]
     */
    private $data;

    /**
     * @var $db conection db
     */
    private $db;

    /**
     * DataCorrelativoRepository constructor.
     *
     */
    public function __construct() {
        $con = new Conect();
        $this->db = $con->getConection();
    }
/*
 * return array {codigo:? , parametro:?, correlativo:?}
 */
    public function genCorrelativo($codigo,$parametro,$user_uuid): array {

        $sql = "SELECT MAX(correlativo)
                FROM correlativo
                WHERE codigo=:codigo AND parametro=:parametro
                ORDER BY correlativo DESC";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':codigo', $codigo, PDO::PARAM_STR);
        $res->bindParam(':parametro', $parametro, PDO::PARAM_STR);
        $res->execute();
        
        if($res->rowCount()>0){
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $correlativo=$res[0]['MAX(correlativo)'];
            $correlativo++;
        }else{
            $correlativo=1;
        }
        
        $sql = "INSERT INTO correlativo (
                id,
                codigo,
                parametro,
                correlativo,
                f_crea,
                u_crea)
                VALUES (
                UUID(),
                :codigo,
                :parametro,
                :correlativo,
                now(),
                :user_uuid)";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':codigo', $codigo, PDO::PARAM_STR);
        $res->bindParam(':parametro', $parametro, PDO::PARAM_STR);
        $res->bindParam(':correlativo', $correlativo, PDO::PARAM_STR);
        $res->bindParam(':user_uuid', $user_uuid, PDO::PARAM_STR);
        $res->execute();
        $res = $res->fetchAll(PDO::FETCH_ASSOC);
        return array('codigo'=>$codigo,'parametro'=>$parametro,'correlativo'=>$correlativo);
    }

}
