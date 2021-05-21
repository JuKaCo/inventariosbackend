<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Actions\RepositoryConection\Conect;
use App\Domain\CompraRepository;
use App\Infrastructure\Persistence\DataCorrelativoRepository;
use \PDO;
use AbmmHasan\Uuid;

class DataCompraRepository implements CompraRepository {

    /**
     * @var data[]
     */
    private $data;

    /**
     * @var $db conection db
     */
    private $db;
    private $dataCorrelativoRepository;

    /**
     * DataCompraRepository constructor.
     *
     */
    public function __construct() {
        $con = new Conect();
        $this->db = $con->getConection();
        $this->dataCorrelativoRepository = new DataCorrelativoRepository;
    }

    public function getCompra($id_compra): array {
        $sql = "SELECT com.*
                FROM compra com
                WHERE com.id=:id_compra AND com.activo=1";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':id_compra', $id_compra, PDO::PARAM_STR);
        $res->execute();
        if($res->rowCount()>0){
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $res = $res[0];
            $result = array('id'=>$res['id'],
                            'codigo'=>$res['codigo'],
                            'nombre'=>$res['nombre'],
                            'gestion'=>$res['gestion'],
                            'descripcion'=>$res['descripcion'],
                            'estado'=>$res['estado'],
                            'activo'=>$res['activo']);
            $resp = array('success'=>true,'message'=>'Exito','data_compra'=>$result);
        }else{
            $resp = array('success'=>false,'message'=>'No se encontraron registros');
        }
        return $resp;
    }

    public function listCompra($query): array {
        if(!(isset($query['filtro'])&&isset($query['limite'])&&isset($query['indice']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        $filtro=$query['filtro'];
        $limite=$query['limite'];
        $indice=$query['indice'];
        $limite=$limite+$indice;
        $filter="%".strtolower($filtro)."%";
        $sql = "SELECT com.*
                FROM compra com 
                WHERE com.activo=1 AND 
                (LOWER(com.codigo) LIKE LOWER(:filter) OR LOWER(com.nombre) LIKE LOWER(:filter) OR LOWER(com.descripcion) LIKE LOWER(:filter) OR LOWER(com.gestion) LIKE LOWER(:filter) OR DATE_FORMAT(com.f_crea,'%d/%m/%Y') LIKE :filter)";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':filter', $filter, PDO::PARAM_STR);
        $res->execute();
        $total=$res->rowCount();
        $sql = "SELECT com.*
                FROM compra com 
                WHERE com.activo=1 AND (LOWER(com.codigo) LIKE LOWER(:filter) OR LOWER(com.nombre) LIKE LOWER(:filter) OR LOWER(com.descripcion) LIKE LOWER(:filter) OR LOWER(com.gestion) LIKE LOWER(:filter) OR DATE_FORMAT(com.f_crea,'%d/%m/%Y') LIKE :filter)
                ORDER BY com.f_crea DESC
                LIMIT :indice, :limite;";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':filter', $filter, PDO::PARAM_STR);
        $res->bindParam(':limite', $limite, PDO::PARAM_INT);
        $res->bindParam(':indice', $indice, PDO::PARAM_INT);
        $res->execute();
        if($res->rowCount()>0){
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $arrayres = array();
            foreach ($res as $item){
                $result = array('id'=>$item['id'],
                                'codigo'=>$item['codigo'],
                                'nombre'=>$item['nombre'],
                                'gestion'=>$item['gestion'],
                                'descripcion'=>$item['descripcion'],
                                'estado'=>$item['estado'],
                                'activo'=>$item['activo']);
                array_push($arrayres,$result);
            }
            $concat=array('resultados'=>$arrayres,'total'=>$total);
            $resp = array('success'=>true,'message'=>'Exito','data_compra'=>$concat);
        }else{
            $resp = array('success'=>false,'message'=>'No se encontraron registros');
        }
        return $resp;
    }

    public function editCompra($id_compra,$data_compra,$uuid): array {
        if(!(isset($data_compra['nombre'])&&isset($data_compra['descripcion'])&&isset($data_compra['gestion'])&&isset($data_compra['codigo']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        $sql = "SELECT *
                FROM compra
                WHERE codigo=:codigo AND id!=:id_compra";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':codigo', $data_compra['codigo'], PDO::PARAM_STR);
            $res->bindParam(':id_compra', $id_compra, PDO::PARAM_STR);
            $res->execute();
        if($res->rowCount()>0){
            $resp = array('success'=>false,'message'=>'Error, el codigo de la compra ya existe en otro registro');
        }else{
            $sql = "UPDATE compra 
                    SET codigo=:codigo,
                    nombre=:nombre,
                    descripcion=:descripcion,
                    gestion=:gestion,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_compra;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_compra', $id_compra, PDO::PARAM_STR);
            $res->bindParam(':codigo', $data_compra['codigo'], PDO::PARAM_STR);
            $res->bindParam(':nombre', $data_compra['nombre'], PDO::PARAM_STR);
            $res->bindParam(':descripcion', $data_compra['descripcion'], PDO::PARAM_STR);
            $res->bindParam(':gestion', $data_compra['gestion'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            //$res = $res->fetchAll(PDO::FETCH_ASSOC);
            $resp = array('success'=>true,'message'=>'compra actualizada','data_compra'=>$data_compra);
        }
        return $resp;
    }

    public function modifyCompra($id_compra,$data_compra,$uuid): array {
        $success=true;
        $resp=array();
        if(isset($data_compra['codigo'])){
            $sql = "SELECT *
                    FROM compra
                    WHERE codigo=:codigo AND id!=:id_compra";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':codigo', $data_compra['codigo'], PDO::PARAM_STR);
            $res->bindParam(':id_compra', $id_compra, PDO::PARAM_STR);
            $res->execute();
            if($res->rowCount()>0){
                //$resp = array('success'=>false,'message'=>'Error, el codigo de la compra ya existe en otro registro');
                $success=false;
                $resp += ['codigo' => 'error, ya existe registro'];
            }else{
                $sql = "UPDATE compra 
                        SET codigo=:codigo,
                        f_mod=now(), 
                        u_mod=:u_mod
                        WHERE id=:id_compra;";
                $res = ($this->db)->prepare($sql);
                $res->bindParam(':id_compra', $id_compra, PDO::PARAM_STR);
                $res->bindParam(':codigo', $data_compra['codigo'], PDO::PARAM_STR);
                $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
                $res->execute();
                $resp += ['codigo' => 'dato actualizado'];
            }
        }
        if(isset($data_compra['nombre'])){
            $sql = "UPDATE compra 
                        SET nombre=:nombre,
                        f_mod=now(), 
                        u_mod=:u_mod
                        WHERE id=:id_compra;";
                $res = ($this->db)->prepare($sql);
                $res->bindParam(':id_compra', $id_compra, PDO::PARAM_STR);
                $res->bindParam(':nombre', $data_compra['nombre'], PDO::PARAM_STR);
                $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
                $res->execute();
                $resp += ['nombre' => 'dato actualizado'];
        }
        if(isset($data_compra['gestion'])){
            $sql = "UPDATE compra 
                        SET gestion=:gestion,
                        f_mod=now(), 
                        u_mod=:u_mod
                        WHERE id=:id_compra;";
                $res = ($this->db)->prepare($sql);
                $res->bindParam(':id_compra', $id_compra, PDO::PARAM_STR);
                $res->bindParam(':gestion', $data_compra['gestion'], PDO::PARAM_STR);
                $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
                $res->execute();
                $resp += ['gestion' => 'dato actualizado'];
        }
        if(isset($data_compra['descripcion'])){
            $sql = "UPDATE compra 
                        SET descripcion=:descripcion,
                        f_mod=now(), 
                        u_mod=:u_mod
                        WHERE id=:id_compra;";
                $res = ($this->db)->prepare($sql);
                $res->bindParam(':id_compra', $id_compra, PDO::PARAM_STR);
                $res->bindParam(':descripcion', $data_compra['descripcion'], PDO::PARAM_STR);
                $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
                $res->execute();
                $resp += ['descripcion' => 'dato actualizado'];
        }
        if(isset($data_compra['estado'])){
            $sql = "UPDATE compra 
                        SET estado=:estado,
                        f_mod=now(), 
                        u_mod=:u_mod
                        WHERE id=:id_compra;";
                $res = ($this->db)->prepare($sql);
                $res->bindParam(':id_compra', $id_compra, PDO::PARAM_STR);
                $res->bindParam(':estado', $data_compra['estado'], PDO::PARAM_STR);
                $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
                $res->execute();
                $resp += ['estado' => 'dato actualizado'];
        }
        $resp = array('success'=>$success,'message'=>'datos actualizados','data_compra'=>$resp);
        return $resp;
    }

    public function changestatusCompra($id_compra,$uuid): array {
        $sql = "UPDATE compra 
                SET activo=0,
                f_inac=now(), 
                u_inac=:u_inac
                WHERE id=:id_compra;";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':u_inac', $uuid, PDO::PARAM_STR);
        $res->bindParam(':id_compra', $id_compra, PDO::PARAM_STR);
        $res->execute();
        //$res = $res->fetchAll(PDO::FETCH_ASSOC);
        if($res->rowCount()==1){
            $resp = array('success'=>true,'message'=>'1 fila afectada');
        }else{
            $resp = array('success'=>false,'message'=>'0 fila afectada');
        }
        return ($resp);

    }

    public function createCompra($data_compra,$uuid): array {
        if(!(isset($data_compra['nombre'])&&isset($data_compra['gestion'])&&isset($data_compra['descripcion'])&&isset($data_compra['codigo']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        $sql = "SELECT *
                FROM compra
                WHERE codigo LIKE :codigo";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':codigo', $data_compra['codigo'], PDO::PARAM_STR);
        $res->execute();
        if($res->rowCount()==1){
            $resp = array('success'=>false,'message'=>'Error, el codigo de la compra ya existe');
        }else{
            //$correlativo = $this->dataCorrelativoRepository->genCorrelativo('REGIO', '0', $uuid);
            //$correlativo = $correlativo['correlativo'];
            //$correlativo = "REGIO-".$correlativo;
            $uuid_neo = Uuid::v4();
            $sql = "INSERT INTO compra (
                    id,
                    codigo,
                    nombre,
                    gestion,
                    descripcion,
                    estado,
                    activo,
                    f_crea,
                    u_crea
                    )VALUES(
                    uuid(),
                    :codigo,
                    :nombre,
                    :gestion,
                    :descripcion,
                    'PENDIENTE',
                    1,
                    now(),
                    :u_crea
                    );";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':uuid', $uuid_neo, PDO::PARAM_STR);
            $res->bindParam(':codigo', $data_compra['codigo'], PDO::PARAM_STR);
            $res->bindParam(':nombre', $data_compra['nombre'], PDO::PARAM_STR);
            $res->bindParam(':gestion', $data_compra['gestion'], PDO::PARAM_INT);
            $res->bindParam(':descripcion', $data_compra['descripcion'], PDO::PARAM_STR);
            $res->bindParam(':u_crea', $uuid, PDO::PARAM_STR);
            $res->execute();
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $sql = "SELECT com.*
                    FROM compra com
                    WHERE com.id=:uuid AND com.activo=1";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':uuid', $uuid_neo, PDO::PARAM_STR);
            $res->execute();
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $res = $res[0];
            $result = array('id'=>$res['id'],
                            'codigo'=>$res['codigo'],
                            'nombre'=>$res['nombre'],
                            'gestion'=>$res['gestion'],
                            'descripcion'=>$res['descripcion'],
                            'estado'=>$res['estado'],
                            'activo'=>$res['activo']);
            $resp = array('success'=>true,'message'=>'compra registrada exitosamente','data_compra'=>$result);
        }
        return $resp;
    }
}
