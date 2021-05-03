<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Actions\RepositoryConection\ConectBiometrico;
use App\Application\Actions\RepositoryConection\Conect;
use App\Domain\ParametricaRepository;
use \PDO;

class DataParametricaRepository implements ParametricaRepository {

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
                    WHERE cod_grupo=:cod_grupo AND LOWER(valor) LIKE :filtro
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
                    WHERE cod_grupo=:cod_grupo AND id_padre=:id_padre AND LOWER(valor) LIKE :filtro
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
                    r.direccion
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

}
