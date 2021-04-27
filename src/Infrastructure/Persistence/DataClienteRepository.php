<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Actions\RepositoryConection\Conect;
use App\Domain\ClienteRepository;
use \PDO;

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
    }

    public function getCliente($id_cliente): array {
        $sql = "SELECT cl.*, 
                    pg.id_param, pg.cod_grupo, pg.codigo as cod_param, pg.valor,
                    pg1.id_param as id_param1, pg1.cod_grupo as cod_grupo1, pg1.codigo as cod_param1, pg1.valor as valor1,
                    pg2.id_param as id_param2, pg2.cod_grupo as cod_grupo2, pg2.codigo as cod_param2, pg2.valor as valor2,
                    pg3.id_param as id_param3, pg3.cod_grupo as cod_grupo3, pg3.codigo as cod_param3, pg3.valor as valor3,
                    pg4.id_param as id_param4, pg4.cod_grupo as cod_grupo4, pg4.codigo as cod_param4, pg4.valor as valor4,
                    pg5.id_param as id_param5, pg5.cod_grupo as cod_grupo5, pg5.codigo as cod_param5, pg5.valor as valor5,
                    pg6.id_param as id_param6, pg6.cod_grupo as cod_grupo6, pg6.codigo as cod_param6, pg6.valor as valor6
                FROM cliente cl, param_general pg, param_general pg1, param_general pg2, param_general pg3, param_general pg4, param_general pg5, param_general pg6
                WHERE cl.id=:id_cliente AND cl.activo=1 AND cl.dependencia=pg.id_param 
                AND cl.nivel=pg1.id_param AND cl.departamento=pg2.id_param
                AND cl.provincia=pg3.id_param AND cl.municipio=pg4.id_param
                AND cl.subsector=pg5.id_param AND cl.tipo=pg6.id_param";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':id_cliente', $id_cliente, PDO::PARAM_STR);
        $res->execute();
        if($res->rowCount()>0){
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $res = $res[0];
            $result = array('id'=>$res['id'],
                            'nombre'=>$res['nombre'],
                            'telefono'=>$res['telefono'],
                            'correo'=>$res['correo'],
                            'nit'=>$res['nit'],
                            'dependencia'=>array(
                                'id_param'=>$res['id_param'],
                                'cod_grupo'=>$res['cod_grupo'],
                                'codigo'=>$res['cod_param'],
                                'valor'=>$res['valor']
                            ),
                            'nivel'=>array(
                                'id_param'=>$res['id_param1'],
                                'cod_grupo'=>$res['cod_grupo1'],
                                'codigo'=>$res['cod_param1'],
                                'valor'=>$res['valor1']
                            ),
                            'departamento'=>array(
                                'id_param'=>$res['id_param2'],
                                'cod_grupo'=>$res['cod_grupo2'],
                                'codigo'=>$res['cod_param2'],
                                'valor'=>$res['valor2']
                            ),
                            'provincia'=>array(
                                'id_param'=>$res['id_param3'],
                                'cod_grupo'=>$res['cod_grupo3'],
                                'codigo'=>$res['cod_param3'],
                                'valor'=>$res['valor3']
                            ),
                            'municipio'=>array(
                                'id_param'=>$res['id_param4'],
                                'cod_grupo'=>$res['cod_grupo4'],
                                'codigo'=>$res['cod_param4'],
                                'valor'=>$res['valor4']
                            ),
                            'ciudad'=>$res['ciudad'],
                            'direccion'=>$res['direccion'],
                            'subsector'=>array(
                                'id_param'=>$res['id_param5'],
                                'cod_grupo'=>$res['cod_grupo5'],
                                'codigo'=>$res['cod_param5'],
                                'valor'=>$res['valor5']
                            ),
                            'tipo'=>array(
                                'id_param'=>$res['id_param6'],
                                'cod_grupo'=>$res['cod_grupo6'],
                                'codigo'=>$res['cod_param6'],
                                'valor'=>$res['valor6']
                            ),
                            'activo'=>$res['activo']);
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
        $sql = "SELECT pr.*, pg.id_param, pg.cod_grupo, pg.codigo as cod_param, pg.valor
                FROM cliente pr JOIN param_general pg ON pr.pais=pg.id_param
                WHERE pr.activo=1 AND (LOWER(pr.codigo) LIKE :filter OR LOWER(pr.nombre) LIKE :filter OR LOWER(pg.valor) LIKE :filter OR LOWER(pr.direccion) LIKE :filter OR LOWER(pr.comentarios) LIKE :filter OR DATE_FORMAT(pr.f_crea,'%d/%m/%Y') LIKE :filter)";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':filter', $filter, PDO::PARAM_STR);
        $res->execute();
        $total=$res->rowCount();
        $sql = "SELECT pr.*, pg.id_param, pg.cod_grupo, pg.codigo as cod_param, pg.valor
                FROM cliente pr JOIN param_general pg ON pr.pais=pg.id_param
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
            $resp = array('success'=>true,'message'=>'Exito','data_cliente'=>$concat);
        }else{
            $resp = array('success'=>false,'message'=>'No se encontraron registros');
        }
        return $resp;
    }

    public function editCliente($id_cliente,$data_cliente,$uuid): array {
        if(!(isset($data_cliente['nombre'])&&isset($data_cliente['telefono'])&&isset($data_cliente['correo'])
        &&isset($data_cliente['nit'])&&isset($data_cliente['dependencia'])&&isset($data_cliente['nivel'])
        &&isset($data_cliente['departamento'])&&isset($data_cliente['provincia'])&&isset($data_cliente['municipio'])
        &&isset($data_cliente['ciudad'])&&isset($data_cliente['direccion'])&&isset($data_cliente['subsector'])
        &&isset($data_cliente['tipo']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        $sql = "SELECT *
                FROM cliente
                WHERE nit=:nit AND id!=:id_cliente";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':nit', $data_cliente['nit'], PDO::PARAM_STR);
            $res->bindParam(':id_cliente', $id_cliente, PDO::PARAM_STR);
            $res->execute();
        if($res->rowCount()>0){
            $resp = array('success'=>false,'message'=>'Error, el nit del cliente ya existe en otro registro');
        }else{
            $sql = "UPDATE cliente 
                    SET nombre=:nombre,
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
            $res->bindParam(':nombre', $data_cliente['nombre'], PDO::PARAM_STR);
            $res->bindParam(':telefono', $data_cliente['telefono'], PDO::PARAM_STR);
            $res->bindParam(':correo', $data_cliente['correo'], PDO::PARAM_STR);
            $res->bindParam(':nit', $data_cliente['nit'], PDO::PARAM_INT);
            $aux = $data_cliente['dependencia']['id_param'];
            $res->bindParam(':dependencia', $aux, PDO::PARAM_INT);
            $aux = $data_cliente['nivel']['id_param'];
            $res->bindParam(':nivel', $aux, PDO::PARAM_INT);
            $aux = $data_cliente['departamento']['id_param'];
            $res->bindParam(':departamento', $aux, PDO::PARAM_INT);
            $aux = $data_cliente['provincia']['id_param'];
            $res->bindParam(':provincia', $aux, PDO::PARAM_INT);
            $aux = $data_cliente['municipio']['id_param'];
            $res->bindParam(':municipio', $aux, PDO::PARAM_INT);            
            $res->bindParam(':ciudad', $data_cliente['ciudad'], PDO::PARAM_STR);
            $res->bindParam(':direccion', $data_cliente['direccion'], PDO::PARAM_STR);
            $aux = $data_cliente['subsector']['id_param'];
            $res->bindParam(':subsector', $aux, PDO::PARAM_INT);
            $aux = $data_cliente['tipo']['id_param'];
            $res->bindParam(':tipo', $aux, PDO::PARAM_INT); 
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            //$res = $res->fetchAll(PDO::FETCH_ASSOC);
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
        if(!(isset($data_cliente['nombre'])&&isset($data_cliente['telefono'])&&isset($data_cliente['correo'])
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
        $res->bindParam(':nit', $data_cliente['nit'], PDO::PARAM_STR);
        $res->bindParam(':nombre', $data_cliente['nombre'], PDO::PARAM_STR);
        $res->execute();
        if($res->rowCount()==1){
            $resp = array('success'=>false,'message'=>'Error, ya existe un cliente con el mismo NIT o nombre');
        }else{
            $sql = "INSERT INTO cliente (
                    id,
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
                    uuid(),
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
            $res->bindParam(':nombre', $data_cliente['nombre'], PDO::PARAM_STR);
            $res->bindParam(':telefono', $data_cliente['telefono'], PDO::PARAM_STR);
            $res->bindParam(':correo', $data_cliente['correo'], PDO::PARAM_STR);
            $res->bindParam(':nit', $data_cliente['nit'], PDO::PARAM_INT);
            $aux = $data_cliente['dependencia']['id_param'];
            $res->bindParam(':dependencia', $aux, PDO::PARAM_INT);
            $aux = $data_cliente['nivel']['id_param'];
            $res->bindParam(':nivel', $aux, PDO::PARAM_INT);
            $aux = $data_cliente['departamento']['id_param'];
            $res->bindParam(':departamento', $aux, PDO::PARAM_INT);
            $aux = $data_cliente['provincia']['id_param'];
            $res->bindParam(':provincia', $aux, PDO::PARAM_INT);
            $aux = $data_cliente['municipio']['id_param'];
            $res->bindParam(':municipio', $aux, PDO::PARAM_INT);            
            $res->bindParam(':ciudad', $data_cliente['ciudad'], PDO::PARAM_STR);
            $res->bindParam(':direccion', $data_cliente['direccion'], PDO::PARAM_STR);
            $aux = $data_cliente['subsector']['id_param'];
            $res->bindParam(':subsector', $aux, PDO::PARAM_INT);
            $aux = $data_cliente['tipo']['id_param'];
            $res->bindParam(':tipo', $aux, PDO::PARAM_INT); 
            $res->bindParam(':u_crea', $uuid, PDO::PARAM_STR);
            $res->execute();
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $sql = "SELECT *
                    FROM cliente cl
                    WHERE cl.nit=:nit AND cl.activo=1";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':nit', $data_cliente['nit'], PDO::PARAM_INT);
            $res->execute();
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $res = $res[0];
            $result = array('id'=>$res['id'],
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
            $resp = array('success'=>true,'message'=>'cliente registrado exitosamente','data_cliente'=>$result);
        }
        return $resp;
    }
}
