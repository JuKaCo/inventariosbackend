<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Actions\RepositoryConection\Conect;
use App\Domain\NotificacionRepository;
use \PDO;

class DataNotificacionRepository implements NotificacionRepository {

    /**
     * @var data[]
     */
    private $data;

    /**
     * @var $db conection db
     */
    private $db;

    /**
     * DataNotificacionRepository constructor.
     *
     */
    public function __construct() {
        $con = new Conect();
        $this->db = $con->getConection();
    }

    public function getNotificacion($id_usuario): array {
        $sql = " SELECT id, mensaje
                 FROM notificacion n
                 WHERE id_usuario = :id_usuario
                       AND activo = true";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':id_usuario', $id_usuario, PDO::PARAM_STR);
        $res->execute();
        $res = $res->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }

    public function getNotificacionId($id_usuario, $id_notificacion): array {
        $sql = " SELECT id, mensaje
                 FROM notificacion n
                 WHERE id_usuario = :id_usuario
                       AND id = :id
                       AND activo = true";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':id', $id_notificacion, PDO::PARAM_INT);
        $res->bindParam(':id_usuario', $id_usuario, PDO::PARAM_STR);
        $res->execute();
        $res = $res->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }

    public function createNotificacion($id_usuario, $data_notificacion): array {
        $sql = " INSERT INTO notificacion (mensaje, confirmacion, activo, f_crea, u_crea) 
                                    VALUES(:mensaje, 0, 1, now(), :u_crea)";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':mensaje', $data_notificacion['mensaje'], PDO::PARAM_STR);
        //$res->bindParam(':confirmacion', 0, PDO::PARAM_INT);
        //$res->bindParam(':activo', 1, PDO::PARAM_INT);
        $res->bindParam(':u_crea', $id_usuario, PDO::PARAM_STR);
        $res->execute();
        $res = $res->fetchAll(PDO::FETCH_ASSOC); 
        $resp = array('success'=>true,'message'=>'Notificación registrado exitosamente','data_notificacion'=>$res);
        return $resp;
    }

}
