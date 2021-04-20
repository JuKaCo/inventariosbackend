<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Actions\RepositoryConection\Conect;
use App\Domain\ProveedorRepository;
use \PDO;

class DataProveedorRepository implements ProveedorRepository {

    /**
     * @var data[]
     */
    private $data;

    /**
     * @var $db conection db
     */
    private $db;

    /**
     * DataMenuRepository constructor.
     *
     */
    public function __construct() {
        $con = new Conect();
        $this->db = $con->getConection();
    }

    public function getProveedor($id_proveedor): array {
        $sql = "SELECT *
                FROM proveedor
                WHERE id_proveedor=:id_proveedor AND activo=1";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':id_proveedor', $id_proveedor, PDO::PARAM_INT);
        $res->execute();
        $res = $res->fetchAll(PDO::FETCH_ASSOC);
        
        return $res;
    }

    public function listProveedor($filter,$items_page,$page): array {
        $filter="%".$filter."%";
        $page=$page-1;
        $sql = "SELECT *
                FROM proveedor
                WHERE activo=1 AND (codigo LIKE :filter OR nombre LIKE :filter OR pais LIKE :filter OR direccion LIKE :filter OR comentarios LIKE :filter)
                LIMIT :items_page
                OFFSET :page;";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':filter', $filter, PDO::PARAM_STR);
        $res->bindParam(':items_page', $items_page, PDO::PARAM_INT);
        $res->bindParam(':page', $page, PDO::PARAM_INT);
        $res->execute();
        $res = $res->fetchAll(PDO::FETCH_ASSOC);
        
        return $res;
    }

    public function editProveedor($id_proveedor,$data_proveedor,$uuid): array {
        $sql = "UPDATE proveedor 
                SET codigo=:codigo,
                nombre=:nombre,
                pais=:pais,
                direccion=:direccion,
                comentarios=:comentarios,
                activo=:activo,
                f_mod=now(), 
                u_mod=:u_mod
                WHERE id_proveedor=:id_proveedor;";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':id_proveedor', $id_proveedor, PDO::PARAM_INT);
        $res->bindParam(':codigo', $data_proveedor['codigo'], PDO::PARAM_STR);
        $res->bindParam(':nombre', $data_proveedor['nombre'], PDO::PARAM_STR);
        $res->bindParam(':pais', $data_proveedor['pais'], PDO::PARAM_STR);
        $res->bindParam(':comentarios', $data_proveedor['comentarios'], PDO::PARAM_STR);
        $res->bindParam(':activo', $data_proveedor['activo'], PDO::PARAM_INT);
        $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
        $res->execute();
        //$res = $res->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }

    public function deleteProveedor($id_proveedor,$uuid): array {

        $sql = "UPDATE proveedor 
                SET activo=0,
                f_inac=now(), 
                u_inac=:u_inac
                WHERE id_proveedor=:id_proveedor;";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':u_inac', $uuid, PDO::PARAM_STR);
        $res->bindParam(':id_proveedor', $id_proveedor, PDO::PARAM_STR);
        $res->execute();
        //$res = $res->fetchAll(PDO::FETCH_ASSOC);
        if($res->rowCount()==1){
            $resp = array('success'=>true,'message'=>'1 fila afectada');
        }else{
            $resp = array('success'=>false,'message'=>'0 fila afectada');
        }
        return array($resp);

    }

    public function createProveedor($data_proveedor,$uuid): array {
        $sql = "INSERT INTO proveedor (
            codigo,
            nombre,
            pais,
            direccion,
            cometarios,
            activo,
            f_crea,
            u_crea
            )VALUES(
            :codigo,
            :nombre,
            :pais,
            :direccion,
            :comentarios,
            :activo,
            now(),
            :u_crea
            );";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':codigo', $data_proveedor['codigo'], PDO::PARAM_STR);
        $res->bindParam(':nombre', $data_proveedor['nombre'], PDO::PARAM_STR);
        $res->bindParam(':pais', $data_proveedor['pais'], PDO::PARAM_STR);
        $res->bindParam(':comentarios', $data_proveedor['comentarios'], PDO::PARAM_STR);
        $res->bindParam(':activo', $data_proveedor['activo'], PDO::PARAM_INT);
        $res->bindParam(':u_crea', $uuid, PDO::PARAM_STR);
        $res->execute();
        $res = $res->fetchAll(PDO::FETCH_ASSOC);
        
        return $res;
    }

}
