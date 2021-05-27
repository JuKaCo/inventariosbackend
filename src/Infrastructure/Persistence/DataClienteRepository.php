<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Actions\RepositoryConection\Conect;
use App\Domain\ClienteRepository;
use App\Infrastructure\Persistence\DataRegionalRepository;
use App\Infrastructure\Persistence\DataParametricaRepository;
use \PDO;
use AbmmHasan\Uuid;

class DataClienteRepository implements ClienteRepository {

    /**
     * @var data[]
     */
    private $data;

    /**
     * @var $db conection db
     */
    private $db;

    /**
     * DataClienteRepository constructor.
     *
     */
    public function __construct() {
        $con = new Conect();
        $this->db = $con->getConection();
        $this->dataRegionalRepository = new DataRegionalRepository;
        $this->dataParametricaRepository = new DataParametricaRepository;
    }

    public function getCliente($id_cliente): array {
        $sql = "SELECT cl.*
        FROM ((((((cliente cl LEFT JOIN param_general pg ON cl.dependencia=pg.id_param) 
        LEFT JOIN param_general pg1 ON cl.nivel=pg1.id_param)
        LEFT JOIN param_general pg2 ON cl.departamento=pg2.id_param)
        LEFT JOIN param_general pg3 ON cl.provincia=pg3.id_param)
        LEFT JOIN param_general pg4 ON cl.municipio=pg4.id_param)
        LEFT JOIN param_general pg5 ON cl.subsector=pg5.id_param)
        LEFT JOIN param_general pg6 ON cl.tipo=pg6.id_param
        WHERE cl.id=:id_cliente AND cl.activo=1";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':id_cliente', $id_cliente, PDO::PARAM_STR);
        $res->execute();
        if($res->rowCount()>0){
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $res = $res[0];
            $data_regional = $this->dataRegionalRepository->getRegional($res['id_regional']);
            $data_dependencia = $this->dataParametricaRepository->getCodParametrica('param_dependencia',0,$res['dependencia']);
            $data_nivel = $this->dataParametricaRepository->getCodParametrica('param_nivel_hosp',0,$res['nivel']);
            $data_departamento = $this->dataParametricaRepository->getCodParametrica('param_departamentos_bol',0,$res['departamento']);
            $data_provincia = $this->dataParametricaRepository->getCodParametrica('param_provincia',0,$res['provincia']);
            $data_municipio = $this->dataParametricaRepository->getCodParametrica('param_municipio',0,$res['municipio']);
            $data_subsector = $this->dataParametricaRepository->getCodParametrica('param_subsec_hosp',0,$res['subsector']);
            $data_tipo = $this->dataParametricaRepository->getCodParametrica('param_tipo_hosp',0,$res['tipo']);
            $result = array('id'=>$res['id'],
                            'id_regional'=>$data_regional['data_regional'],
                            'nombre'=>$res['nombre'],
                            'telefono'=>$res['telefono'],
                            'correo'=>$res['correo'],
                            'nit'=>$res['nit'],
                            'dependencia'=>$data_dependencia,
                            'nivel'=>$data_nivel,
                            'departamento'=>$data_departamento,
                            'provincia'=>$data_provincia,
                            'municipio'=>$data_municipio,
                            'ciudad'=>$res['ciudad'],
                            'direccion'=>$res['direccion'],
                            'subsector'=>$data_subsector,
                            'tipo'=>$data_tipo,
                            'activo'=>$res['activo']);
            if($res['id_param']==null){$result['dependencia']=json_decode ("{}");}
            if($res['id_param1']==null){$result['nivel']=json_decode ("{}");}
            if($res['id_param2']==null){$result['departamento']=json_decode ("{}");}
            if($res['id_param3']==null){$result['provincia']=json_decode ("{}");}
            if($res['id_param4']==null){$result['municipio']=json_decode ("{}");}
            if($res['id_param5']==null){$result['subsector']=json_decode ("{}");}
            if($res['id_param6']==null){$result['tipo']=json_decode ("{}");}
            $resp = array('success'=>true,'message'=>'Exito','data_cliente'=>$result);
        }else{
            $resp = array('success'=>false,'message'=>'No se encontraron registros');
        }
        return $resp;
    }

    public function listCliente($query): array {
        if(!(isset($query['filtro'])&&isset($query['limite'])&&isset($query['indice']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        $filtro=$query['filtro'];
        $limite=$query['limite'];
        $indice=$query['indice'];
        $limite=$limite+$indice;
        $filter="%".strtolower($filtro)."%";

        $sql = "SELECT cl.id
            FROM ((((((cliente cl LEFT JOIN param_general pg ON cl.dependencia=pg.id_param) 
            LEFT JOIN param_general pg1 ON cl.nivel=pg1.id_param)
            LEFT JOIN param_general pg2 ON cl.departamento=pg2.id_param)
            LEFT JOIN param_general pg3 ON cl.provincia=pg3.id_param)
            LEFT JOIN param_general pg4 ON cl.municipio=pg4.id_param)
            LEFT JOIN param_general pg5 ON cl.subsector=pg5.id_param)
            LEFT JOIN param_general pg6 ON cl.tipo=pg6.id_param
            WHERE cl.activo=1 AND (
            LOWER(cl.nombre) LIKE LOWER(:filtro) OR LOWER(cl.telefono) LIKE LOWER(:filtro) OR (cl.nit) LIKE (:filtro) OR
            LOWER(cl.correo) LIKE LOWER(:filtro) OR LOWER(cl.ciudad) LIKE LOWER(:filtro) OR LOWER(cl.direccion) LIKE LOWER(:filtro) OR
            LOWER(pg.valor) LIKE LOWER(:filtro) OR LOWER(pg1.valor) LIKE LOWER(:filtro) OR LOWER(pg2.valor) LIKE LOWER(:filtro) OR
            LOWER(pg3.valor) LIKE LOWER(:filtro) OR LOWER(pg4.valor) LIKE LOWER(:filtro) OR LOWER(pg5.valor) LIKE LOWER(:filtro) OR
            LOWER(pg6.valor) LIKE LOWER(:filtro) )";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':filtro', $filter, PDO::PARAM_STR);
        $res->execute();
        $total=$res->rowCount();
        $sql = "SELECT cl.*, 
                pg.id_param, pg.cod_grupo, pg.codigo as cod_param, pg.valor,
                pg1.id_param as id_param1, pg1.cod_grupo as cod_grupo1, pg1.codigo as cod_param1, pg1.valor as valor1,
                pg2.id_param as id_param2, pg2.cod_grupo as cod_grupo2, pg2.codigo as cod_param2, pg2.valor as valor2,
                pg3.id_param as id_param3, pg3.cod_grupo as cod_grupo3, pg3.codigo as cod_param3, pg3.valor as valor3,
                pg4.id_param as id_param4, pg4.cod_grupo as cod_grupo4, pg4.codigo as cod_param4, pg4.valor as valor4,
                pg5.id_param as id_param5, pg5.cod_grupo as cod_grupo5, pg5.codigo as cod_param5, pg5.valor as valor5,
                pg6.id_param as id_param6, pg6.cod_grupo as cod_grupo6, pg6.codigo as cod_param6, pg6.valor as valor6
                FROM ((((((cliente cl LEFT JOIN param_general pg ON cl.dependencia=pg.id_param) 
                LEFT JOIN param_general pg1 ON cl.nivel=pg1.id_param)
                LEFT JOIN param_general pg2 ON cl.departamento=pg2.id_param)
                LEFT JOIN param_general pg3 ON cl.provincia=pg3.id_param)
                LEFT JOIN param_general pg4 ON cl.municipio=pg4.id_param)
                LEFT JOIN param_general pg5 ON cl.subsector=pg5.id_param)
                LEFT JOIN param_general pg6 ON cl.tipo=pg6.id_param
                WHERE cl.activo=1 AND (
                LOWER(cl.nombre) LIKE LOWER(:filtro) OR LOWER(cl.telefono) LIKE (:filtro) OR (cl.nit) LIKE (:filtro) OR
                LOWER(cl.correo) LIKE LOWER(:filtro) OR LOWER(cl.ciudad) LIKE LOWER(:filtro) OR LOWER(cl.direccion) LIKE LOWER(:filtro) OR
                LOWER(pg.valor) LIKE LOWER(:filtro) OR LOWER(pg1.valor) LIKE LOWER(:filtro) OR LOWER(pg2.valor) LIKE LOWER(:filtro) OR
                LOWER(pg3.valor) LIKE LOWER(:filtro) OR LOWER(pg4.valor) LIKE LOWER(:filtro) OR LOWER(pg5.valor) LIKE LOWER(:filtro) OR
                LOWER(pg6.valor) LIKE LOWER(:filtro) )
                ORDER BY cl.f_crea DESC
                LIMIT :indice, :limite;";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':filtro', $filter, PDO::PARAM_STR);
        $res->bindParam(':limite', $limite, PDO::PARAM_INT);
        $res->bindParam(':indice', $indice, PDO::PARAM_INT);
        $res->execute();
        if($res->rowCount()>0){
            $restodo = $res->fetchAll(PDO::FETCH_ASSOC);
            $arrayres = array();
            foreach ($restodo as $res){
                $data_regional = $this->dataRegionalRepository->getRegional($res['id_regional']);
                $data_dependencia = $this->dataParametricaRepository->getCodParametrica('param_dependencia',0,$res['dependencia']);
                $data_nivel = $this->dataParametricaRepository->getCodParametrica('param_nivel_hosp',0,$res['nivel']);
                $data_departamento = $this->dataParametricaRepository->getCodParametrica('param_departamentos_bol',0,$res['departamento']);
                $data_provincia = $this->dataParametricaRepository->getCodParametrica('param_provincia',0,$res['provincia']);
                $data_municipio = $this->dataParametricaRepository->getCodParametrica('param_municipio',0,$res['municipio']);
                $data_subsector = $this->dataParametricaRepository->getCodParametrica('param_subsec_hosp',0,$res['subsector']);
                $data_tipo = $this->dataParametricaRepository->getCodParametrica('param_tipo_hosp',0,$res['tipo']);
                $result = array('id'=>$res['id'],
                                'id_regional'=>$data_regional['data_regional'],
                                'nombre'=>$res['nombre'],
                                'telefono'=>$res['telefono'],
                                'correo'=>$res['correo'],
                                'nit'=>$res['nit'],
                                'dependencia'=>$data_dependencia,
                                'nivel'=>$data_nivel,
                                'departamento'=>$data_departamento,
                                'provincia'=>$data_provincia,
                                'municipio'=>$data_municipio,
                                'ciudad'=>$res['ciudad'],
                                'direccion'=>$res['direccion'],
                                'subsector'=>$data_subsector,
                                'tipo'=>$data_tipo,
                                'activo'=>$res['activo']);
                if($data_dependencia['id_param']==null){$result['dependencia']=json_decode ("{}");}
                if($data_nivel['id_param']==null){$result['nivel']=json_decode ("{}");}
                if($data_departamento['id_param']==null){$result['departamento']=json_decode ("{}");}
                if($data_provincia['id_param']==null){$result['provincia']=json_decode ("{}");}
                if($data_municipio['id_param']==null){$result['municipio']=json_decode ("{}");}
                if($data_subsector['id_param']==null){$result['subsector']=json_decode ("{}");}
                if($data_tipo['id_param']==null){$result['tipo']=json_decode ("{}");}
                array_push($arrayres,$result);
            }
            $concat=array('resultados'=>$arrayres,'total'=>$total);
            $resp = array('success'=>true,'message'=>'Exito','data_cliente'=>$concat);
        }else{
            $resp = array('success'=>false,'message'=>'No se encontraron registros');
        }
        return $resp;
    }

    public function editCliente($id_cliente,$data_cliente,$uuid): array {
        if(!(isset($data_cliente['id_regional'])&&isset($data_cliente['nombre'])&&isset($data_cliente['telefono'])&&isset($data_cliente['correo'])
        &&isset($data_cliente['nit'])&&isset($data_cliente['dependencia'])&&isset($data_cliente['nivel'])
        &&isset($data_cliente['departamento'])&&isset($data_cliente['provincia'])&&isset($data_cliente['municipio'])
        &&isset($data_cliente['ciudad'])&&isset($data_cliente['direccion'])&&isset($data_cliente['subsector'])
        &&isset($data_cliente['tipo']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        $backup_data_cliente=$data_cliente;
        $sql = "SELECT *
                FROM cliente
                WHERE nit=:nit AND id!=:id_cliente";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':nit', $data_cliente['nit'], PDO::PARAM_INT);
            $res->bindParam(':id_cliente', $id_cliente, PDO::PARAM_STR);
            $res->execute();
        if($res->rowCount()>0){
            $resp = array('success'=>false,'message'=>'Error, el nit del cliente ya existe en otro registro');
        }else{
            $sql = "UPDATE cliente 
                    SET id_regional=:id_regional,
                    nombre=:nombre,
                    telefono=:telefono,
                    correo=:correo,
                    nit=:nit,
                    dependencia=:dependencia,
                    nivel=:nivel,
                    departamento=:departamento,
                    provincia=:provincia,
                    municipio=:municipio,
                    ciudad=:ciudad,
                    direccion=:direccion,
                    subsector=:subsector,
                    tipo=:tipo,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_cliente;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_cliente', $id_cliente, PDO::PARAM_STR);
            $res->bindParam(':id_regional', $data_cliente['id_regional']['id'], PDO::PARAM_STR);
            $res->bindParam(':nombre', $data_cliente['nombre'], PDO::PARAM_STR);
            $res->bindParam(':telefono', $data_cliente['telefono'], PDO::PARAM_STR);
            $res->bindParam(':correo', $data_cliente['correo'], PDO::PARAM_STR);
            $res->bindParam(':nit', $data_cliente['nit'], PDO::PARAM_INT);
            $res->bindParam(':dependencia', $data_cliente['dependencia']['id_param'], PDO::PARAM_INT);
            $res->bindParam(':nivel', $data_cliente['nivel']['id_param'], PDO::PARAM_INT);
            $res->bindParam(':departamento', $data_cliente['departamento']['id_param'], PDO::PARAM_INT);
            $res->bindParam(':provincia', $data_cliente['provincia']['id_param'], PDO::PARAM_INT);
            $res->bindParam(':municipio', $data_cliente['municipio']['id_param'], PDO::PARAM_INT);            
            $res->bindParam(':ciudad', $data_cliente['ciudad'], PDO::PARAM_STR);
            $res->bindParam(':direccion', $data_cliente['direccion'], PDO::PARAM_STR);
            $res->bindParam(':subsector', $data_cliente['subsector']['id_param'], PDO::PARAM_INT);
            $res->bindParam(':tipo', $data_cliente['tipo']['id_param'], PDO::PARAM_INT); 
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            //$res = $res->fetchAll(PDO::FETCH_ASSOC);
            if($data_cliente['dependencia']['id_param']==null){$data_cliente['dependencia']=json_decode ("{}");}
            if($data_cliente['nivel']['id_param']==null){$data_cliente['nivel']=json_decode ("{}");}
            if($data_cliente['departamento']['id_param']==null){$data_cliente['departamento']=json_decode ("{}");}
            if($data_cliente['provincia']['id_param']==null){$data_cliente['provincia']=json_decode ("{}");}
            if($data_cliente['municipio']['id_param']==null){$data_cliente['municipio']=json_decode ("{}");}
            if($data_cliente['subsector']['id_param']==null){$resuldata_clientet['subsector']=json_decode ("{}");}
            if($data_cliente['tipo']['id_param']==null){$data_cliente['tipo']=json_decode ("{}");}  
            $resp = array('success'=>true,'message'=>'cliente actualizado','data_cliente'=>$data_cliente);
        }
        return $resp;
    }

    public function changestatusCliente($id_cliente,$uuid): array {
        $sql = "UPDATE cliente 
                SET activo=0,
                f_inac=now(), 
                u_inac=:u_inac
                WHERE id=:id_cliente;";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':u_inac', $uuid, PDO::PARAM_STR);
        $res->bindParam(':id_cliente', $id_cliente, PDO::PARAM_STR);
        $res->execute();
        //$res = $res->fetchAll(PDO::FETCH_ASSOC);
        if($res->rowCount()==1){
            $resp = array('success'=>true,'message'=>'1 fila afectada');
        }else{
            $resp = array('success'=>false,'message'=>'0 fila afectada');
        }
        return ($resp);

    }

    public function createCliente($data_cliente,$uuid): array {
        if(!(isset($data_cliente['id_regional'])&&isset($data_cliente['nombre'])&&isset($data_cliente['telefono'])&&isset($data_cliente['correo'])
        &&isset($data_cliente['nit'])&&isset($data_cliente['dependencia'])&&isset($data_cliente['nivel'])
        &&isset($data_cliente['departamento'])&&isset($data_cliente['provincia'])&&isset($data_cliente['municipio'])
        &&isset($data_cliente['ciudad'])&&isset($data_cliente['direccion'])&&isset($data_cliente['subsector'])
        &&isset($data_cliente['tipo']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        $sql = "SELECT *
                FROM cliente
                WHERE nit=:nit OR nombre=:nombre";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':nit', $data_cliente['nit'], PDO::PARAM_INT);
        $res->bindParam(':nombre', $data_cliente['nombre'], PDO::PARAM_STR);
        $res->execute();
        if($res->rowCount()==1){
            $resp = array('success'=>false,'message'=>'Error, ya existe un cliente con el mismo NIT o nombre');
        }else{
            $uuid_neo = Uuid::v4();
            $sql = "INSERT INTO cliente (
                    id,
                    id_regional,
                    nombre,
                    telefono,
                    correo,
                    nit,
                    dependencia,
                    nivel,
                    departamento,
                    provincia,
                    municipio,
                    ciudad,
                    direccion,
                    subsector,
                    tipo,
                    activo,
                    f_crea,
                    u_crea
                    )VALUES(
                    :uuid,
                    :id_regional,
                    :nombre,
                    :telefono,
                    :correo,
                    :nit,
                    :dependencia,
                    :nivel,
                    :departamento,
                    :provincia,
                    :municipio,
                    :ciudad,
                    :direccion,
                    :subsector,
                    :tipo,
                    1,
                    now(),
                    :u_crea
                    );";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':uuid', $uuid_neo, PDO::PARAM_STR);
            $res->bindParam(':id_regional', $data_cliente['id_regional']['id'], PDO::PARAM_STR);
            $res->bindParam(':nombre', $data_cliente['nombre'], PDO::PARAM_STR);
            $res->bindParam(':telefono', $data_cliente['telefono'], PDO::PARAM_STR);
            $res->bindParam(':correo', $data_cliente['correo'], PDO::PARAM_STR);
            $res->bindParam(':nit', $data_cliente['nit'], PDO::PARAM_INT);
            $res->bindParam(':dependencia', $data_cliente['dependencia']['id_param'], PDO::PARAM_INT);
            $res->bindParam(':nivel', $data_cliente['nivel']['id_param'], PDO::PARAM_INT);
            $res->bindParam(':departamento', $data_cliente['departamento']['id_param'], PDO::PARAM_INT);
            $res->bindParam(':provincia', $data_cliente['provincia']['id_param'], PDO::PARAM_INT);
            $res->bindParam(':municipio', $data_cliente['municipio']['id_param'], PDO::PARAM_INT);            
            $res->bindParam(':ciudad', $data_cliente['ciudad'], PDO::PARAM_STR);
            $res->bindParam(':direccion', $data_cliente['direccion'], PDO::PARAM_STR);
            $res->bindParam(':subsector', $data_cliente['subsector']['id_param'], PDO::PARAM_INT);
            $res->bindParam(':tipo', $data_cliente['tipo']['id_param'], PDO::PARAM_INT); 
            $res->bindParam(':u_crea', $uuid, PDO::PARAM_STR);
            $res->execute();
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $sql = "SELECT *
                    FROM cliente cl
                    WHERE cl.id=:uuid AND cl.activo=1";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':uuid', $uuid_neo, PDO::PARAM_STR);
            $res->execute();
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $res = $res[0];
            $result = array('id'=>$res['id'],
                            'id_regional'=>$data_cliente['id_regional'],
                            'nombre'=>$res['nombre'],
                            'telefono'=>$res['telefono'],
                            'correo'=>$res['correo'],
                            'nit'=>$res['nit'],
                            'dependencia'=>$data_cliente['dependencia'],
                            'nivel'=>$data_cliente['nivel'],
                            'departamento'=>$data_cliente['departamento'],
                            'provincia'=>$data_cliente['provincia'],
                            'municipio'=>$data_cliente['municipio'],
                            'ciudad'=>$res['ciudad'],
                            'direccion'=>$res['direccion'],
                            'subsector'=>$data_cliente['subsector'],
                            'tipo'=>$data_cliente['tipo'],
                            'activo'=>$res['activo']);
            if($data_cliente['dependencia']['id_param']==null){$result['dependencia']=json_decode ("{}");}
            if($data_cliente['nivel']['id_param']==null){$result['nivel']=json_decode ("{}");}
            if($data_cliente['departamento']['id_param']==null){$result['departamento']=json_decode ("{}");}
            if($data_cliente['provincia']['id_param']==null){$result['provincia']=json_decode ("{}");}
            if($data_cliente['municipio']['id_param']==null){$result['municipio']=json_decode ("{}");}
            if($data_cliente['subsector']['id_param']==null){$result['subsector']=json_decode ("{}");}
            if($data_cliente['tipo']['id_param']==null){$result['tipo']=json_decode ("{}");}               
            $resp = array('success'=>true,'message'=>'cliente registrado exitosamente','data_cliente'=>$result);
        }
        return $resp;
    }
}
