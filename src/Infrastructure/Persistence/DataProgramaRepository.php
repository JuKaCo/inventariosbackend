<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Actions\RepositoryConection\Conect;
use App\Domain\ProgramaRepository;
use App\Infrastructure\Persistence\DataCorrelativoRepository;
use \PDO;

class DataProgramaRepository implements ProgramaRepository {

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
     * DataProgramaRepository constructor.
     *
     */
    public function __construct() {
        $con = new Conect();
        $this->db = $con->getConection();
        $this->dataCorrelativoRepository = new DataCorrelativoRepository;
    }

    public function getPrograma($id_programa): array {
        $sql = "SELECT *
                FROM programa
                WHERE id=:id_programa";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':id_programa', $id_programa, PDO::PARAM_STR);
        $res->execute();
        if($res->rowCount()>0){
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $res = $res[0];
            $result = array('id'=>$res['id'],
                            'codigo'=>$res['codigo'],
                            'nombre'=>$res['nombre'],
                            'referencia'=>$res['referencia'],
                            'activo'=>$res['activo']);
            $resp = array('success'=>true,'message'=>'Exito','data_programa'=>$result);
        }else{
            $resp = array('success'=>false,'message'=>'No se encontraron registros');
        }
        return $resp;
    }

    public function listPrograma($query): array {
        if(!(isset($query['filtro'])&&isset($query['limite'])&&isset($query['indice']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        $filtro=$query['filtro'];
        $limite=$query['limite'];
        $indice=$query['indice'];
        $limite=$limite+$indice;
        $filter="%".strtolower($filtro)."%";
        $sql = "SELECT *
                FROM programa
                WHERE activo=1 AND (LOWER(codigo) LIKE LOWER(:filter) OR LOWER(nombre) LIKE LOWER(:filter) OR LOWER(referencia) LIKE LOWER(:filter) OR DATE_FORMAT(f_crea,'%d/%m/%Y') LIKE :filter)";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':filter', $filter, PDO::PARAM_STR);
        $res->execute();
        $total=$res->rowCount();
        $sql = "SELECT *
                FROM programa
                WHERE activo=1 AND (LOWER(codigo) LIKE LOWER(:filter) OR LOWER(nombre) LIKE LOWER(:filter) OR LOWER(referencia) LIKE LOWER(:filter) OR DATE_FORMAT(f_crea,'%d/%m/%Y') LIKE :filter)
                ORDER BY f_crea DESC
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
                            'referencia'=>$item['referencia'],
                            'activo'=>$item['activo']);
                array_push($arrayres,$result);
            }
            $concat=array('resultados'=>$arrayres,'total'=>$total);
            $resp = array('success'=>true,'message'=>'Exito','data_programa'=>$concat);
        }else{
            $resp = array('success'=>false,'message'=>'No se encontraron registros');
        }
        return $resp;
    }

    public function editPrograma($id_programa,$data_programa,$uuid): array {
        if(!(isset($data_programa['nombre'])&&isset($data_programa['referencia']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        $sql = "SELECT *
                FROM programa
                WHERE nombre=:nombre AND id!=:id_programa";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':nombre', $data_programa['nombre'], PDO::PARAM_STR);
            $res->bindParam(':id_programa', $id_programa, PDO::PARAM_STR);
            $res->execute();
        if($res->rowCount()>0){
            $resp = array('success'=>false,'message'=>'Error, el nombre del programa ya existe en otro registro');
        }else{
            $sql = "UPDATE programa 
                    SET nombre=:nombre,
                    referencia=:referencia,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_programa;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_programa', $id_programa, PDO::PARAM_STR);
            $res->bindParam(':nombre', $data_programa['nombre'], PDO::PARAM_STR);
            $res->bindParam(':referencia', $data_programa['referencia'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            //$res = $res->fetchAll(PDO::FETCH_ASSOC);
            $resp = array('success'=>true,'message'=>'programa actualizado','data_programa'=>$data_programa);
        }
        return $resp;
    }

    public function changestatusPrograma($id_programa,$uuid): array {
        $sql = "UPDATE programa 
                SET activo=0,
                f_inac=now(), 
                u_inac=:u_inac
                WHERE id=:id_programa;";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':u_inac', $uuid, PDO::PARAM_STR);
        $res->bindParam(':id_programa', $id_programa, PDO::PARAM_STR);
        $res->execute();
        //$res = $res->fetchAll(PDO::FETCH_ASSOC);
        if($res->rowCount()==1){
            $resp = array('success'=>true,'message'=>'1 fila afectada');
        }else{
            $resp = array('success'=>false,'message'=>'0 fila afectada');
        }
        return ($resp);

    }

    public function createPrograma($data_programa,$uuid): array {
        if(!(isset($data_programa['nombre'])&&isset($data_programa['referencia']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        $sql = "SELECT *
                FROM programa
                WHERE nombre=:nombre";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':nombre', $data_programa['nombre'], PDO::PARAM_STR);
        $res->execute();
        if($res->rowCount()==1){
            $resp = array('success'=>false,'message'=>'Error, el nombre del programa ya existe');
        }else{
            $correlativo = $this->dataCorrelativoRepository->genCorrelativo('PROG', '0', $uuid);
            $correlativo = $correlativo['correlativo'];
            $correlativo = 'PROG-' . $correlativo;
            $sql = "INSERT INTO programa (
                    id,
                    codigo,
                    nombre,
                    referencia,
                    activo,
                    f_crea,
                    u_crea
                    )VALUES(
                    uuid(),
                    :codigo,
                    :nombre,
                    :referencia,
                    1,
                    now(),
                    :u_crea
                    );";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':codigo', $correlativo, PDO::PARAM_STR);
            $res->bindParam(':nombre', $data_programa['nombre'], PDO::PARAM_STR);
            $res->bindParam(':referencia', $data_programa['referencia'], PDO::PARAM_STR);
            $res->bindParam(':u_crea', $uuid, PDO::PARAM_STR);
            $res->execute();
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $sql = "SELECT *
                    FROM programa
                    WHERE codigo=:codigo AND activo=1";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':codigo', $correlativo, PDO::PARAM_STR);
            $res->execute();
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $res = $res[0];
            $result = array('id'=>$res['id'],
                            'codigo'=>$res['codigo'],
                            'nombre'=>$res['nombre'],
                            'referencia'=>$res['referencia'],
                            'activo'=>$res['activo']);
            $resp = array('success'=>true,'message'=>'programa registrado exitosamente','data_programa'=>$result);
        }
        return $resp;
    }
}
