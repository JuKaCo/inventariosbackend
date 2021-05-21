<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Actions\RepositoryConection\ConectBiometrico;
use App\Application\Actions\RepositoryConection\Conect;
use App\Application\Actions\RepositoryConection\Conectkeycloak;
use App\Domain\ParametricaRepository;
use \PDO;
use AbmmHasan\Uuid;

class DataParametricaRepository implements ParametricaRepository {

    /**
     * @var $db conection db
     */
    private $db;
    private $dbKeycloak;

    /**
     * DataMenuRepository constructor.
     *
     */
    public function __construct() {
        $con = new Conect();
        $this->db = $con->getConection();
    }

    public function getParametrica($cod_grupo, $id_padre, $filtro = ''): array {
        try {
            $filtro = '%' . strtolower($filtro) . '%';
            if($id_padre==0){
                $sql = "SELECT 
                    id_param,
                    cod_grupo,
                    codigo,
                    valor
                    FROM param_general
                    WHERE cod_grupo=:cod_grupo AND (LOWER(valor) LIKE LOWER(:filtro) OR LOWER(codigo) LIKE LOWER(:filtro))
                    ORDER BY id_param";
                $res = ($this->db)->prepare($sql);
                $res->bindParam(':filtro', $filtro, PDO::PARAM_STR);
                $res->bindParam(':cod_grupo', $cod_grupo, PDO::PARAM_STR);
            }else{
                $sql = "SELECT 
                    id_param,
                    cod_grupo,
                    codigo,
                    valor
                    FROM param_general
                    WHERE cod_grupo=:cod_grupo AND id_padre=:id_padre AND (LOWER(valor) LIKE LOWER(:filtro) OR LOWER(codigo) LIKE LOWER(:filtro))
                    ORDER BY id_param";
                $res = ($this->db)->prepare($sql);
                $res->bindParam(':filtro', $filtro, PDO::PARAM_STR);
                $res->bindParam(':cod_grupo', $cod_grupo, PDO::PARAM_STR);
                $res->bindParam(':id_padre', $id_padre, PDO::PARAM_INT);
            }
            $res->execute();
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            return $res;
        } catch (Exception $e) {
            return array('error' => true);
        }
    }

    public function getCodParametrica($cod_grupo, $id_padre, $codigo = '') {
        try {
            //$codigo = '%' . strtolower($codigo) . '%';
            if($id_padre==0){
                $sql = "SELECT 
                    id_param,
                    cod_grupo,
                    codigo,
                    valor
                    FROM param_general
                    WHERE cod_grupo=:cod_grupo AND (LOWER(codigo) = LOWER(:codigo) OR LOWER(id_param) = LOWER(:codigo))
                    ORDER BY id_param";
                $res = ($this->db)->prepare($sql);
                $res->bindParam(':codigo', $codigo, PDO::PARAM_STR);
                $res->bindParam(':cod_grupo', $cod_grupo, PDO::PARAM_STR);
            }else{
                $sql = "SELECT 
                    id_param,
                    cod_grupo,
                    codigo,
                    valor
                    FROM param_general
                    WHERE cod_grupo=:cod_grupo AND id_padre=:id_padre AND LOWER(codigo) = LOWER(:codigo)
                    ORDER BY id_param";
                $res = ($this->db)->prepare($sql);
                $res->bindParam(':codigo', $codigo, PDO::PARAM_STR);
                $res->bindParam(':cod_grupo', $cod_grupo, PDO::PARAM_STR);
                $res->bindParam(':id_padre', $id_padre, PDO::PARAM_INT);
            }
            $res->execute();
            if($res->rowCount()==0){
                return array('id_param'=>null,
                            'cod_grupo'=>null,
                            'codigo'=>null,
                            'valor'=>null);
            }else{
                $res = $res->fetchAll(PDO::FETCH_ASSOC);
                return $res[0];
            }
            
        } catch (Exception $e) {
            return array('error' => true);
        }
    }

    public function getTerminalBiometrico(): array {
        $con = new ConectBiometrico();
        $this->db = $con->getConection();
        try {
            $sql = "SELECT id as id_param,
                terminal_name as valor, 
                terminal_type as codigo,
                'terminal_biometrico' as cod_grupo 
                    FROM att_terminal";
            $res = ($this->db)->prepare($sql);
            $res->execute();
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            return $res;
        } catch (Exception $e) {
            return array('error' => true);
        }
    }

    public function getLiname($filtro): array {
        $filtro = '%' . $filtro . '%';
        try {
            $sql = "SELECT 
                    l.id as id_liname,
                    l.codigo as cod_liname,
                    l.medicamento,
                    l.for_farma,
                    l.concen,
                    l.class_atq,
                    l.pre_ref,
                    l.aclara_parti
                    FROM param_liname_archivo pa, param_liname l
                    WHERE pa.activo=true and pa.id=l.id_param_liname_archivo
                    and (upper(l.codigo) like upper(:filtro) or upper(l.medicamento) like upper(:filtro)
                    or upper(l.class_atq) like upper(:filtro) or upper(l.concen) like upper(:filtro)
                    or upper(l.for_farma) like upper(:filtro) or upper(l.pre_ref) like upper(:filtro)
                    or upper(l.aclara_parti) like upper(:filtro)
                    )";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':filtro', $filtro, PDO::PARAM_STR);
            $res->execute();
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            return $res;
        } catch (Exception $e) {
            return array('error' => true);
        }
        return array();
    }

    public function getLinadime($filtro): array {
        $filtro = '%' . $filtro . '%';
        try {
            $sql = "SELECT
                    l.id as id_linadime,
                    l.codigo as cod_linadime,
                    l.dispositivo,
                    l.esp_tec,
                    l.presen,
                    l.niv_uso_I,
                    l.niv_uso_II,
                    l.niv_uso_III

                    FROM param_linadime_archivo pa, param_linadime l
                    WHERE pa.activo=true and pa.id=l.id_param_liname_archivo
                    and (upper(l.codigo) like upper(:filtro) or upper(l.dispositivo) like upper(:filtro)
                    or upper(l.esp_tec) like upper(:filtro) or upper(l.presen) like upper(:filtro)
                    or upper(l.niv_uso_I) like upper(:filtro) or upper(l.niv_uso_II) like upper(:filtro)
                    or upper(l.niv_uso_III) like upper(:filtro)
                    )";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':filtro', $filtro, PDO::PARAM_STR);
            $res->execute();
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            return $res;
        } catch (Exception $e) {
            return array('error' => true);
        }
        return array();
    }

    public function getProveedor($filtro): array {
        $filtro = '%' . $filtro . '%';
        try {
            $sql = "SELECT
                    pr.id,
                    pr.codigo,
                    pr.nombre,
                    pr.direccion,
                    pg.valor,
                    pg.id_param
                    FROM proveedor pr JOIN param_general pg ON pr.pais=pg.id_param
                    WHERE pr.activo = 1 
                          AND (LOWER(pr.codigo) LIKE :filter OR LOWER(pr.nombre) LIKE :filter)";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':filter', $filtro, PDO::PARAM_STR);
            $res->execute();
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $arrayres = array();
            foreach ($res as $item){
                $result = array('id'=>$item['id'],
                                'codigo'=>$item['codigo'],
                                'nombre'=>$item['nombre'],
                                'pais'=>array(
                                    'id_param'=>$item['id_param'],
                                    'valor'=>$item['valor']
                                ),
                                'direccion'=>$item['direccion']);
                array_push($arrayres,$result);
            }
            return $arrayres;
        } catch (Exception $e) {
            return array('error' => true);
        }
        return array();
    }


    public function getRegional($filtro): array {
        $filtro = '%' . $filtro . '%';
        try {
            $sql = "SELECT
                    r.id,
                    r.codigo,
                    r.nombre,
                    r.direccion,
                    r.telefono
                    FROM regional r
                    WHERE r.activo = 1 
                          AND (LOWER(r.codigo) LIKE :filter OR LOWER(r.nombre) LIKE :filter)";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':filter', $filtro, PDO::PARAM_STR);
            $res->execute();
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            return $res;
        } catch (Exception $e) {
            return array('error' => true);
        }
        return array();
    }

    public function getPrograma($filtro): array {

        
        $filtro = '%' . $filtro . '%';
        try {
            $sql = "SELECT
                    pr.id,
                    pr.codigo,
                    pr.nombre,
                    pr.referencia
                    FROM programa pr
                    WHERE pr.activo = 1 
                          AND (LOWER(pr.codigo) LIKE :filter OR LOWER(pr.nombre) LIKE :filter)";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':filter', $filtro, PDO::PARAM_STR);
            $res->execute();
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            return $res;
        } catch (Exception $e) {
            return array('error' => true);
        }
        return array();
    }

    public function getProducto($filtro): array {
        $filtro = '%' . $filtro . '%';
        try {
            $sql = "SELECT pr.*, pl.id as id_liname, pl.codigo as cod_liname, pld.id as id_linadime, pld.codigo as cod_linadime
                    FROM (producto pr LEFT JOIN param_liname pl ON pr.codigo_liname=pl.id) 
									  LEFT JOIN param_linadime pld ON pr.codigo_linadime=pld.id
                    WHERE pr.activo=1 
                          AND (LOWER(pr.codigo) LIKE :filter OR LOWER(pr.nombre_comercial) LIKE :filter)";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':filter', $filtro, PDO::PARAM_STR);
            $res->execute();
            $resultado = $res->fetchAll(PDO::FETCH_ASSOC);
            $arrayres = array();
            
            foreach ($resultado as $res){
                $result = array('id'=>$res['id'],
                                'codigo'=>$res['codigo'],
                                'nombre_comercial'=>$res['nombre_comercial'],
                                'codigo_liname'=>array(
                                    'id_liname'=>$res['id_liname'],
                                    'cod_liname'=>$res['cod_liname'],
                                ),
                                'codigo_linadime'=>array(
                                    'id_linadime'=>$res['id_linadime'],
                                    'cod_linadime'=>$res['cod_linadime'],
                                ),
                                'reg_san'=>$res['reg_san'],
                                'referencia'=>$res['referencia'],
                                'medicamento'=>$res['medicamento'],
                                'form_farm'=>$res['form_farm'],
                                'concen'=>$res['concen'],
                                'atq'=>$res['atq'],
                                'precio_ref'=>$res['precio_ref'],
                                'aclara_parti'=>$res['aclara_parti'],
                                'dispositivo'=>$res['dispositivo'],
                                'especificacion_tec'=>$res['especificacion_tec'],
                                'presentacion'=>$res['presentacion'],
                                'nivel_uso_i'=>$res['nivel_uso_i'],
                                'nivel_uso_ii'=>$res['nivel_uso_ii'],
                                'nivel_uso_iii'=>$res['nivel_uso_iii']);
                if($result['codigo_liname']['id_liname']==null){$result['codigo_liname']=json_decode ("{}");}
                if($result['codigo_linadime']['id_linadime']==null){$result['codigo_linadime']=json_decode ("{}");}  
                array_push($arrayres,$result);
            }
            return $arrayres;
        } catch (Exception $e) {
            return array('error' => true);
        }
        return array();
    }


    public function getAlmacen($query): array {
        if(!isset($query['id_regional'])){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        $filtro=$query['filtro'];
        $id_regional = $query['id_regional'];

        $filtro = '%' . $filtro . '%';
        try {
            $sql = "SELECT alm.*
                    FROM almacen alm
                    WHERE alm.activo = 1 
                          AND alm.id_regional=:id_regional
                          AND (LOWER(alm.nombre) LIKE LOWER(:filter) 
                          OR LOWER(alm.direccion) LIKE LOWER(:filter)
                          OR LOWER(alm.codigo) LIKE LOWER(:filter))";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':filter', $filtro, PDO::PARAM_STR);
            $res->bindParam(':id_regional', $id_regional, PDO::PARAM_STR);
            $res->execute();
            $res = $res->fetchAll(PDO::FETCH_ASSOC);

            $arrayres = array();
            foreach ($res as $item){
                $result = array('id'=>$item['id'],
                                'codigo'=>$item['codigo'],
                                'nombre'=>$item['nombre'],
                                'direccion'=>$item['direccion'],
                                'telefono'=>$item['telefono']);
                array_push($arrayres,$result);
            }
            return $arrayres;
        } catch (Exception $e) {
            return array('error' => true);
        }
        return array();
    }

    public function getUsuario($filtro): array {
        $con = new Conectkeycloak();
        $this->dbKeycloak = $con->getConection();

        $filtro = '%' . $filtro . '%';
        try {
            $sql = "SELECT
                    ur.ID as id_usuario, 
                    ur.FIRST_NAME as nombres, 
                    ur.LAST_NAME as apellidos,
                    CONCAT(ur.FIRST_NAME, ' ', ur.LAST_NAME) as nombre_completo, 
                    ua.VALUE as cargo
                    FROM USER_ENTITY ur, USER_ATTRIBUTE ua
                    WHERE ur.ENABLED = 1
                          AND ur.REALM_ID = 'web'
                          AND ur.ID = ua.USER_ID
                          AND ua.NAME = 'cargo_usuario'
                          AND (LOWER(ur.FIRST_NAME) LIKE :filter OR LOWER(ur.LAST_NAME) LIKE :filter OR LOWER(ua.VALUE) LIKE :filter )";
            $res = ($this->dbKeycloak)->prepare($sql);
            $res->bindParam(':filter', $filtro, PDO::PARAM_STR);
            $res->execute();
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            return $res;
        } catch (Exception $e) {
            return array('error' => true);
        }
        return array();
    }

    public function getCompra($filtro): array {
        $filtro = '%' . $filtro . '%';
        try {
            $sql = "SELECT com.id,
                           com.codigo,
                           com.nombre,
                           com.gestion,
                           com.descripcion,
                           com.estado
                    FROM compra com 
                    WHERE com.activo=1
                          AND com.estado = 'VERIFICADO'
                          AND (LOWER(com.codigo) LIKE LOWER(:filter) 
                            OR LOWER(com.nombre) LIKE LOWER(:filter) 
                            OR LOWER(com.descripcion) LIKE LOWER(:filter))";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':filter', $filtro, PDO::PARAM_STR);
            $res->execute();
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            return $res;
        } catch (Exception $e) {
            return array('error' => true);
        }
        return array();
    }

    public function getConfiguracion($codigo): array {
        try {
            $sql = "SELECT id,
                           codigo,
                           descripcion,
                           recurso
                    FROM configuracion_general 
                    WHERE codigo=:codigo";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':codigo', $codigo, PDO::PARAM_STR);
            $res->execute();
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            return $res;
        } catch (Exception $e) {
            return array('error' => true);
        }
        return array();
    }
}
