<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Actions\RepositoryConection\Conect;
use App\Domain\NotificacionRepository;
use \PDO;
use AbmmHasan\Uuid;

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

    public function getNotificacionSimple($id_usuario): array {
        $sql = " SELECT id, mensaje, confirmacion, f_crea
                 FROM notificacion n
                 WHERE id_usuario = :id_usuario
                       AND activo = true
                 ORDER BY f_crea DESC";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':id_usuario', $id_usuario, PDO::PARAM_STR);
        $res->execute();
        if($res->rowCount()>0){
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $resp = array('success'=>true,'message'=>'Exito','data_notificacion'=>$res);
        } else {
            $resp = array('success'=>false,'message'=>'No se encontraron registros');
        }

        return $resp;
    }


    public function getNotificacion($id_usuario, $query): array {
        if(!(isset($query['filtro'])&&isset($query['limite'])&&isset($query['indice']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }

        $filtro=$query['filtro'];
        $limite=$query['limite'];
        $indice=$query['indice'];
        $limite=$limite+$indice;
        $filter="%".strtolower($filtro)."%";


        $sql = "SELECT id
                FROM notificacion
                WHERE ( LOWER(mensaje) LIKE LOWER(:filtro) OR
                            DATE_FORMAT(f_crea,'%d/%m/%Y') LIKE :filtro )";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':filtro', $filter, PDO::PARAM_STR);
        $res->execute();
        $total=$res->rowCount();

        $sql = "SELECT id, mensaje, activo, f_crea, confirmacion
                FROM notificacion
                WHERE ( LOWER(mensaje) LIKE LOWER(:filtro) OR
                            DATE_FORMAT(f_crea,'%d/%m/%Y') LIKE :filtro )
                ORDER BY f_crea DESC
                LIMIT :indice, :limite;";
        $res = ($this->db)->prepare($sql);

        $res->bindParam(':filtro', $filter, PDO::PARAM_STR);
        $res->bindParam(':limite', $limite, PDO::PARAM_INT);
        $res->bindParam(':indice', $indice, PDO::PARAM_INT);
        $res->execute();

        if($res->rowCount()>0){
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $concat = array('resultados'=>$res,'total'=>$total);
            $resp = array('success'=>true,'message'=>'Exito','data_notificacion'=>$concat);
        } else {
            $resp = array('success'=>false,'message'=>'No se encontraron registros');
        }
        return $resp;
    }


    public function getNotificacionId($id_usuario, $id_notificacion): array {
        $sql = " SELECT id, mensaje, confirmacion,  f_crea
                 FROM notificacion n
                 WHERE id_usuario = :id_usuario
                       AND id = :id
                       AND activo = true";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':id', $id_notificacion, PDO::PARAM_STR);
        $res->bindParam(':id_usuario', $id_usuario, PDO::PARAM_STR);
        $res->execute();
        $res = $res->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }

    public function createNotificacion($id_usuario, $data_notificacion): array {
        if(!(isset($data_notificacion['mensaje'])&&isset($data_notificacion['id_usuario']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }

        $sql = "SELECT UUID() as uuid;";
        $uuid = $this->db->prepare($sql);
        $uuid->execute();
        $uuid = $uuid->fetch();
        $uuid = $uuid["uuid"];
        $sql = " INSERT INTO notificacion (id, mensaje, id_usuario, confirmacion, activo, f_crea, u_crea) 
                                    VALUES(:id, :mensaje, :id_usuario, 0, 1, now(), :u_crea)";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':id', $uuid, PDO::PARAM_STR);
        $res->bindParam(':mensaje', $data_notificacion['mensaje'], PDO::PARAM_STR);
        $res->bindParam(':id_usuario', $data_notificacion['id_usuario'], PDO::PARAM_STR);
        $res->bindParam(':u_crea', $id_usuario, PDO::PARAM_STR);
        $res->execute();
        $res = $res->fetchAll(PDO::FETCH_ASSOC); 

        $sql = "SELECT id, mensaje, id_usuario, confirmacion
                FROM notificacion
                WHERE id = :id";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':id', $uuid, PDO::PARAM_STR);
        $res->execute();
        $res = $res->fetchAll(PDO::FETCH_ASSOC);

            
        $resp = array('success'=>true,'message'=>'Notificación registrado exitosamente','data_notificacion'=>$res);
        return $resp;
    }

    public function inactivaNotificacion($id_usuario, $id_notificacion): array {
        $sql = "UPDATE notificacion 
                SET activo = 0,
                confirmacion = 1,
                f_inac=now(),
                u_inac=:u_inac
                WHERE id=:id;";
        $res = ($this->db)->prepare($sql);    
        $res->bindParam(':id', $id_notificacion, PDO::PARAM_STR);
        $res->bindParam(':u_inac', $id_usuario, PDO::PARAM_STR);
        $res->execute();
        $res = $res->fetchAll(PDO::FETCH_ASSOC);

        $resp = array('success'=>true,'message'=>'Notificación inactivado exitosamente','data_notificacion'=>$res);
        return $resp;
    }

    public function confirmaNotificacion($id_usuario, $id_notificacion): array {
        $sql = "UPDATE notificacion 
                SET confirmacion = 1,
                f_mod=now(),
                u_mod=:u_mod
                WHERE id=:id;";
        $res = ($this->db)->prepare($sql);    
        $res->bindParam(':id', $id_notificacion, PDO::PARAM_STR);
        $res->bindParam(':u_mod', $id_usuario, PDO::PARAM_STR);
        $res->execute();
        $res = $res->fetchAll(PDO::FETCH_ASSOC);

        $resp = array('success'=>true,'message'=>'Notificación modificado exitosamente','data_notificacion'=>$res);
        return $resp;
    }

}
