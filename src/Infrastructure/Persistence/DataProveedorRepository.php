<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Actions\RepositoryConection\Conect;
use App\Domain\ProveedorRepository;
use \PDO;
use AbmmHasan\Uuid;

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
        $sql = "SELECT pr.*, pg.id_param, pg.cod_grupo, pg.codigo as cod_param, pg.valor
                FROM proveedor pr, param_general pg
                WHERE pr.id=:id_proveedor AND pr.activo=1 AND pr.pais=pg.id_param";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':id_proveedor', $id_proveedor, PDO::PARAM_STR);
        $res->execute();
        if($res->rowCount()>0){
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $res = $res[0];
            $result = array('id'=>$res['id'],
                            'codigo'=>$res['codigo'],
                            'nombre'=>$res['nombre'],
                            'pais'=>array(
                                'id_param'=>$res['id_param'],
                                'cod_grupo'=>$res['cod_grupo'],
                                'codigo'=>$res['cod_param'],
                                'valor'=>$res['valor']
                            ),
                            'direccion'=>$res['direccion'],
                            'comentarios'=>$res['comentarios'],
                            'activo'=>$res['activo']);
            $resp = array('success'=>true,'message'=>'Exito','data_proveedor'=>$result);
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
        $filter="%".strtolower($filtro)."%";
        $sql = "SELECT pr.*, pg.id_param, pg.cod_grupo, pg.codigo as cod_param, pg.valor
                FROM proveedor pr JOIN param_general pg ON pr.pais=pg.id_param
                WHERE pr.activo=1 AND (LOWER(pr.codigo) LIKE :filter OR LOWER(pr.nombre) LIKE :filter OR LOWER(pg.valor) LIKE :filter OR LOWER(pr.direccion) LIKE :filter OR LOWER(pr.comentarios) LIKE :filter OR DATE_FORMAT(pr.f_crea,'%d/%m/%Y') LIKE :filter)";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':filter', $filter, PDO::PARAM_STR);
        $res->execute();
        $total=$res->rowCount();
        $sql = "SELECT pr.*, pg.id_param, pg.cod_grupo, pg.codigo as cod_param, pg.valor
                FROM proveedor pr JOIN param_general pg ON pr.pais=pg.id_param
                WHERE pr.activo=1 AND (LOWER(pr.codigo) LIKE :filter OR LOWER(pr.nombre) LIKE :filter OR LOWER(pg.valor) LIKE :filter OR LOWER(pr.direccion) LIKE :filter OR LOWER(pr.comentarios) LIKE :filter OR DATE_FORMAT(pr.f_crea,'%d/%m/%Y') LIKE :filter)
                ORDER BY pr.f_crea DESC
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
                            'pais'=>array(
                                'id_param'=>$item['id_param'],
                                'cod_grupo'=>$item['cod_grupo'],
                                'codigo'=>$item['cod_param'],
                                'valor'=>$item['valor']
                            ),
                            'direccion'=>$item['direccion'],
                            'comentarios'=>$item['comentarios'],
                            'activo'=>$item['activo']);
                array_push($arrayres,$result);
            }
            $concat=array('resultados'=>$arrayres,'total'=>$total);
            $resp = array('success'=>true,'message'=>'Exito','data_proveedor'=>$concat);
        }else{
            $resp = array('success'=>false,'message'=>'No se encontraron registros');
        }
        return $resp;
    }

    public function editProveedor($id_proveedor,$data_proveedor,$uuid): array {
        if(!(isset($data_proveedor['codigo'])&&isset($data_proveedor['nombre'])&&isset($data_proveedor['pais'])&&isset($data_proveedor['direccion'])&&isset($data_proveedor['comentarios']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        $sql = "SELECT *
                FROM proveedor
                WHERE codigo=:codigo AND id!=:id_proveedor";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':codigo', $data_proveedor['codigo'], PDO::PARAM_STR);
            $res->bindParam(':id_proveedor', $id_proveedor, PDO::PARAM_STR);
            $res->execute();
        if($res->rowCount()>0){
            $resp = array('success'=>false,'message'=>'Error, el codigo proveedor ya existe en otro registro');
        }else{
            $sql = "UPDATE proveedor 
                    SET codigo=:codigo,
                    nombre=:nombre,
                    pais=:pais,
                    direccion=:direccion,
                    comentarios=:comentarios,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_proveedor;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_proveedor', $id_proveedor, PDO::PARAM_STR);
            $res->bindParam(':codigo', $data_proveedor['codigo'], PDO::PARAM_STR);
            $res->bindParam(':nombre', $data_proveedor['nombre'], PDO::PARAM_STR);
            $aux = $data_proveedor['pais']['id_param'];
            $res->bindParam(':pais', $aux, PDO::PARAM_INT);
            $res->bindParam(':direccion', $data_proveedor['direccion'], PDO::PARAM_STR);
            $res->bindParam(':comentarios', $data_proveedor['comentarios'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            //$res = $res->fetchAll(PDO::FETCH_ASSOC);
            $resp = array('success'=>true,'message'=>'proveedor actualizado','data_proveedor'=>$data_proveedor);
        }
        return $resp;
    }

    public function changestatusProveedor($id_proveedor,$uuid): array {
        $sql = "UPDATE proveedor 
                SET activo=0,
                f_inac=now(), 
                u_inac=:u_inac
                WHERE id=:id_proveedor;";
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
            $uuid_neo=Uuid::v4();
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
                    :uuid,
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
            $res->bindParam(':uuid', $uuid_neo, PDO::PARAM_STR);
            $res->bindParam(':codigo', $data_proveedor['codigo'], PDO::PARAM_STR);
            $res->bindParam(':nombre', $data_proveedor['nombre'], PDO::PARAM_STR);
            $aux = $data_proveedor['pais']['id_param'];
            $res->bindParam(':pais', $aux, PDO::PARAM_INT);
            $res->bindParam(':direccion', $data_proveedor['direccion'], PDO::PARAM_STR);
            $res->bindParam(':comentarios', $data_proveedor['comentarios'], PDO::PARAM_STR);
            $res->bindParam(':u_crea', $uuid, PDO::PARAM_STR);
            $res->execute();
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $sql = "SELECT pr.*, pg.id_param, pg.cod_grupo, pg.codigo as cod_param, pg.valor
                    FROM proveedor pr, param_general pg
                    WHERE pr.id=:uuid AND pr.activo=1 AND pr.pais=pg.id_param";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':uuid', $uuid_neo, PDO::PARAM_STR);
            $res->execute();
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $res = $res[0];
            $result = array('id'=>$res['id'],
                            'codigo'=>$res['codigo'],
                            'nombre'=>$res['nombre'],
                            'pais'=>array(
                                'id_param'=>$res['id_param'],
                                'cod_grupo'=>$res['cod_grupo'],
                                'codigo'=>$res['cod_param'],
                                'valor'=>$res['valor']
                            ),
                            'direccion'=>$res['direccion'],
                            'comentarios'=>$res['comentarios'],
                            'activo'=>$res['activo']);
            $resp = array('success'=>true,'message'=>'proveedor registrado exitosamente','data_proveedor'=>$result);
        }
        return $resp;
    }
}
