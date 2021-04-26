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
                WHERE id=:id_proveedor AND activo=1";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':id_proveedor', $id_proveedor, PDO::PARAM_STR);
        $res->execute();
        if($res->rowCount()>0){
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $res[0]['pais'] = json_decode($res[0]['pais']);
            $resp = array('success'=>true,'message'=>'Exito','data_proveedor'=>$res);
        }else{
            $resp = array('success'=>false,'message'=>'No se encontraron registros');
        }
        return $resp;
    }

    public function listProveedor($query): array {
        if(!(isset($query['filtro'])&&isset($query['limite'])&&isset($query['indice']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        $filtro=$query['filtro'];
        $limite=$query['limite'];
        $indice=$query['indice'];
        $limite=$limite+$indice;
        $filter="%".$filtro."%";
        $sql = "SELECT *
                FROM proveedor
                WHERE activo=1 AND (codigo LIKE :filter OR nombre LIKE :filter OR pais LIKE :filter OR direccion LIKE :filter OR comentarios LIKE :filter OR DATE_FORMAT(f_crea,'%d/%m/%Y') LIKE :filter)";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':filter', $filter, PDO::PARAM_STR);
        $res->execute();
        $total=$res->rowCount();
        $sql = "SELECT *
                FROM proveedor
                WHERE activo=1 AND (codigo LIKE :filter OR nombre LIKE :filter OR pais LIKE :filter OR direccion LIKE :filter OR comentarios LIKE :filter OR DATE_FORMAT(f_crea,'%d/%m/%Y') LIKE :filter)
                LIMIT :indice, :limite;";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':filter', $filter, PDO::PARAM_STR);
        $res->bindParam(':limite', $limite, PDO::PARAM_INT);
        $res->bindParam(':indice', $indice, PDO::PARAM_INT);
        $res->execute();
        if($res->rowCount()>0){
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            for ($i = 0; $i < count($res); ++$i){
                $res[$i]['pais']=json_decode($res[$i]['pais']);
            }
            $concat=array('data'=>$res,'total'=>$total);
            $resp = array('success'=>true,'message'=>'Exito','data_proveedor'=>$concat);
        }else{
            $resp = array('success'=>false,'message'=>'No se encontraron registros');
        }
        return $resp;
    }

    public function editProveedor($id_proveedor,$data_proveedor,$uuid): array {
        if(!(isset($data_proveedor['codigo'])&&isset($data_proveedor['nombre'])&&isset($data_proveedor['pais'])&&isset($data_proveedor['direccion'])&&isset($data_proveedor['comentarios'])&&isset($data_proveedor['activo']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        $sql = "SELECT *
                FROM proveedor
                WHERE codigo=:codigo";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':codigo', $data_proveedor['codigo'], PDO::PARAM_STR);
        $res->execute();
        if($res->rowCount()==1){
            $sql = "UPDATE proveedor 
                    SET codigo=:codigo,
                    nombre=:nombre,
                    pais=:pais,
                    direccion=:direccion,
                    comentarios=:comentarios,
                    activo=:activo,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_proveedor;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_proveedor', $id_proveedor, PDO::PARAM_STR);
            $res->bindParam(':codigo', $data_proveedor['codigo'], PDO::PARAM_STR);
            $res->bindParam(':nombre', $data_proveedor['nombre'], PDO::PARAM_STR);
            $aux = json_encode($data_proveedor['pais']);
            $res->bindParam(':pais', $aux, PDO::PARAM_STR);
            $res->bindParam(':direccion', $data_proveedor['direccion'], PDO::PARAM_STR);
            $res->bindParam(':comentarios', $data_proveedor['comentarios'], PDO::PARAM_STR);
            $res->bindParam(':activo', $data_proveedor['activo'], PDO::PARAM_INT);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            //$res = $res->fetchAll(PDO::FETCH_ASSOC);
            $resp = array('success'=>true,'message'=>'proveedor actualizado','data_proveedor'=>$data_proveedor);
        }else{
            $resp = array('success'=>false,'message'=>'Error, el codigo proveedor no existe');
        }
        return $resp;
    }

    public function changestatusProveedor($id_proveedor,$estado,$uuid): array {
        $sql = "UPDATE proveedor 
                SET activo=:activo,
                f_inac=now(), 
                u_inac=:u_inac
                WHERE id=:id_proveedor;";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':u_inac', $uuid, PDO::PARAM_STR);
        $res->bindParam(':activo', $estado, PDO::PARAM_STR);
        $res->bindParam(':id_proveedor', $id_proveedor, PDO::PARAM_STR);
        $res->execute();
        //$res = $res->fetchAll(PDO::FETCH_ASSOC);
        if($res->rowCount()==1){
            $resp = array('success'=>true,'message'=>'1 fila afectada');
        }else{
            $resp = array('success'=>false,'message'=>'0 fila afectada');
        }
        return ($resp);

    }

    public function createProveedor($data_proveedor,$uuid): array {
        if(!(isset($data_proveedor['codigo'])&&isset($data_proveedor['nombre'])&&isset($data_proveedor['pais'])&&isset($data_proveedor['direccion'])&&isset($data_proveedor['comentarios']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        $sql = "SELECT *
                FROM proveedor
                WHERE codigo=:codigo";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':codigo', $data_proveedor['codigo'], PDO::PARAM_STR);
        $res->execute();
        if($res->rowCount()==1){
            $resp = array('success'=>false,'message'=>'Error, el codigo proveedor ya existe');
        }else{
            $sql = "INSERT INTO proveedor (
                    id,
                    codigo,
                    nombre,
                    pais,
                    direccion,
                    comentarios,
                    activo,
                    f_crea,
                    u_crea
                    )VALUES(
                    uuid(),
                    :codigo,
                    :nombre,
                    :pais,
                    :direccion,
                    :comentarios,
                    1,
                    now(),
                    :u_crea
                    );";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':codigo', $data_proveedor['codigo'], PDO::PARAM_STR);
            $res->bindParam(':nombre', $data_proveedor['nombre'], PDO::PARAM_STR);
            $aux = json_encode($data_proveedor['pais']);
            $res->bindParam(':pais', $aux, PDO::PARAM_STR);
            $res->bindParam(':direccion', $data_proveedor['direccion'], PDO::PARAM_STR);
            $res->bindParam(':comentarios', $data_proveedor['comentarios'], PDO::PARAM_STR);
            $res->bindParam(':u_crea', $uuid, PDO::PARAM_STR);
            $res->execute();
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $sql = "SELECT *
                FROM proveedor
                WHERE codigo=:codigo";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':codigo', $data_proveedor['codigo'], PDO::PARAM_STR);
            $res->execute();
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $res[0]['pais'] = json_decode($res[0]['pais']);
            $resp = array('success'=>true,'message'=>'proveedor registrado exitosamente','data_proveedor'=>$res);
        }
        return $resp;
    }
}
