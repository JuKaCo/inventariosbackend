<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Actions\RepositoryConection\Conect;
use App\Domain\RegionalRepository;
use App\Infrastructure\Persistence\DataCorrelativoRepository;
use \PDO;
use AbmmHasan\Uuid;

class DataRegionalRepository implements RegionalRepository {

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
     * DataRegionalRepository constructor.
     *
     */
    public function __construct() {
        $con = new Conect();
        $this->db = $con->getConection();
        $this->dataCorrelativoRepository = new DataCorrelativoRepository;
    }

    public function getRegional($id_regional): array {
        $sql = "SELECT reg.*, pg.id_param, pg.cod_grupo, pg.codigo as cod_param, pg.valor
                FROM regional reg, param_general pg
                WHERE reg.id=:id_regional AND reg.activo=1 AND reg.departamento=pg.id_param";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':id_regional', $id_regional, PDO::PARAM_STR);
        $res->execute();
        if($res->rowCount()>0){
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $res = $res[0];
            $result = array('id'=>$res['id'],
                            'codigo'=>$res['codigo'],
                            'nombre'=>$res['nombre'],
                            'departamento'=>array(
                                'id_param'=>$res['id_param'],
                                'cod_grupo'=>$res['cod_grupo'],
                                'codigo'=>$res['cod_param'],
                                'valor'=>$res['valor']
                            ),
                            'direccion'=>$res['direccion'],
                            'telefono'=>$res['telefono'],
                            'activo'=>$res['activo']);
            $resp = array('success'=>true,'message'=>'Exito','data_regional'=>$result);
        }else{
            $resp = array('success'=>false,'message'=>'No se encontraron registros');
        }
        return $resp;
    }

    public function listRegional($query): array {
        if(!(isset($query['filtro'])&&isset($query['limite'])&&isset($query['indice']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        $filtro=$query['filtro'];
        $limite=$query['limite'];
        $indice=$query['indice'];
        $limite=$limite+$indice;
        $filter="%".strtolower($filtro)."%";
        $sql = "SELECT reg.*, pg.id_param, pg.cod_grupo, pg.codigo as cod_param, pg.valor
                FROM regional reg LEFT JOIN param_general pg ON reg.departamento=pg.id_param
                WHERE reg.activo=1 AND (LOWER(reg.codigo) LIKE LOWER(:filter) OR LOWER(reg.nombre) LIKE LOWER(:filter) OR LOWER(pg.valor) LIKE LOWER(:filter) OR LOWER(reg.direccion) LIKE LOWER(:filter) OR LOWER(reg.telefono) LIKE LOWER(:filter) OR DATE_FORMAT(reg.f_crea,'%d/%m/%Y') LIKE :filter)";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':filter', $filter, PDO::PARAM_STR);
        $res->execute();
        $total=$res->rowCount();
        $sql = "SELECT reg.*, pg.id_param, pg.cod_grupo, pg.codigo as cod_param, pg.valor
                FROM regional reg LEFT JOIN param_general pg ON reg.departamento=pg.id_param
                WHERE reg.activo=1 AND (LOWER(reg.codigo) LIKE LOWER(:filter) OR LOWER(reg.nombre) LIKE LOWER(:filter) OR LOWER(pg.valor) LIKE LOWER(:filter) OR LOWER(reg.direccion) LIKE LOWER(:filter) OR LOWER(reg.telefono) LIKE LOWER(:filter) OR DATE_FORMAT(reg.f_crea,'%d/%m/%Y') LIKE :filter)
                ORDER BY reg.f_crea DESC
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
                            'departamento'=>array(
                                'id_param'=>$item['id_param'],
                                'cod_grupo'=>$item['cod_grupo'],
                                'codigo'=>$item['cod_param'],
                                'valor'=>$item['valor']
                            ),
                            'direccion'=>$item['direccion'],
                            'telefono'=>$item['telefono'],
                            'activo'=>$item['activo']);
                array_push($arrayres,$result);
            }
            $concat=array('resultados'=>$arrayres,'total'=>$total);
            $resp = array('success'=>true,'message'=>'Exito','data_regional'=>$concat);
        }else{
            $resp = array('success'=>false,'message'=>'No se encontraron registros');
        }
        return $resp;
    }

    public function editRegional($id_regional,$data_regional,$uuid): array {
        if(!(isset($data_regional['nombre'])&&isset($data_regional['departamento'])&&isset($data_regional['direccion'])&&isset($data_regional['telefono']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        $sql = "SELECT *
                FROM regional
                WHERE nombre=:nombre AND id!=:id_regional";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':nombre', $data_regional['nombre'], PDO::PARAM_STR);
            $res->bindParam(':id_regional', $id_regional, PDO::PARAM_STR);
            $res->execute();
        if($res->rowCount()>0){
            $resp = array('success'=>false,'message'=>'Error, el nombre de la regional ya existe en otro registro');
        }else{
            $sql = "UPDATE regional 
                    SET nombre=:nombre,
                    departamento=:departamento,
                    direccion=:direccion,
                    telefono=:telefono,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_regional;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_regional', $id_regional, PDO::PARAM_STR);
            $res->bindParam(':nombre', $data_regional['nombre'], PDO::PARAM_STR);
            $aux = $data_regional['departamento']['id_param'];
            $res->bindParam(':departamento', $aux, PDO::PARAM_INT);
            $res->bindParam(':direccion', $data_regional['direccion'], PDO::PARAM_STR);
            $res->bindParam(':telefono', $data_regional['telefono'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            //$res = $res->fetchAll(PDO::FETCH_ASSOC);
            $resp = array('success'=>true,'message'=>'regional actualizada','data_regional'=>$data_regional);
        }
        return $resp;
    }

    public function changestatusRegional($id_regional,$uuid): array {
        $sql = "UPDATE regional 
                SET activo=0,
                f_inac=now(), 
                u_inac=:u_inac
                WHERE id=:id_regional;";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':u_inac', $uuid, PDO::PARAM_STR);
        $res->bindParam(':id_regional', $id_regional, PDO::PARAM_STR);
        $res->execute();
        //$res = $res->fetchAll(PDO::FETCH_ASSOC);
        if($res->rowCount()==1){
            $resp = array('success'=>true,'message'=>'1 fila afectada');
        }else{
            $resp = array('success'=>false,'message'=>'0 fila afectada');
        }
        return ($resp);

    }

    public function createRegional($data_regional,$uuid): array {
        if(!(isset($data_regional['nombre'])&&isset($data_regional['departamento'])&&isset($data_regional['direccion'])&&isset($data_regional['telefono']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        $sql = "SELECT *
                FROM regional
                WHERE nombre LIKE :nombre";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':nombre', $data_regional['nombre'], PDO::PARAM_STR);
        $res->execute();
        if($res->rowCount()==1){
            $resp = array('success'=>false,'message'=>'Error, el nombre de la regional ya existe');
        }else{
            $correlativo = $this->dataCorrelativoRepository->genCorrelativo('REGIO', '0', $uuid);
            $correlativo = $correlativo['correlativo'];
            $correlativo = "REGIO-".$correlativo;
            $sql = "INSERT INTO regional (
                    id,
                    codigo,
                    nombre,
                    departamento,
                    direccion,
                    telefono,
                    activo,
                    f_crea,
                    u_crea
                    )VALUES(
                    uuid(),
                    :codigo,
                    :nombre,
                    :departamento,
                    :direccion,
                    :telefono,
                    1,
                    now(),
                    :u_crea
                    );";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':codigo', $correlativo, PDO::PARAM_STR);
            $res->bindParam(':nombre', $data_regional['nombre'], PDO::PARAM_STR);
            $aux = $data_regional['departamento']['id_param'];
            $res->bindParam(':departamento', $aux, PDO::PARAM_INT);
            $res->bindParam(':direccion', $data_regional['direccion'], PDO::PARAM_STR);
            $res->bindParam(':telefono', $data_regional['telefono'], PDO::PARAM_STR);
            $res->bindParam(':u_crea', $uuid, PDO::PARAM_STR);
            $res->execute();
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $sql = "SELECT reg.*, pg.id_param, pg.cod_grupo, pg.codigo as cod_param, pg.valor
                    FROM regional reg, param_general pg
                    WHERE reg.codigo=:codigo AND reg.activo=1 AND reg.departamento=pg.id_param";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':codigo', $correlativo, PDO::PARAM_STR);
            $res->execute();
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $res = $res[0];
            $result = array('id'=>$res['id'],
                            'codigo'=>$res['codigo'],
                            'nombre'=>$res['nombre'],
                            'departamento'=>array(
                                'id_param'=>$res['id_param'],
                                'cod_grupo'=>$res['cod_grupo'],
                                'codigo'=>$res['cod_param'],
                                'valor'=>$res['valor']
                            ),
                            'direccion'=>$res['direccion'],
                            'telefono'=>$res['telefono'],
                            'activo'=>$res['activo']);
            $resp = array('success'=>true,'message'=>'regional registrado exitosamente','data_regional'=>$result);
        }
        return $resp;
    }
}
