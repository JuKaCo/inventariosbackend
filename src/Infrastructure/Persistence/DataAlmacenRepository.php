<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Actions\RepositoryConection\Conect;
use App\Domain\AlmacenRepository;
use App\Infrastructure\Persistence\DataCorrelativoRepository;
use App\Infrastructure\Persistence\DataregionalRepository;
use App\Infrastructure\Persistence\DataProgramaRepository;
use \PDO;
use AbmmHasan\Uuid;

class DataAlmacenRepository implements AlmacenRepository {

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
     * DataAlmacenRepository constructor.
     *
     */
    public function __construct() {
        $con = new Conect();
        $this->db = $con->getConection();
        $this->dataCorrelativoRepository = new DataCorrelativoRepository;
        $this->dataRegionalRepository = new DataRegionalRepository;
        $this->dataProgramaRepository = new DataProgramaRepository;
    }

    public function getAlmacen($id_almacen): array {
        $sql = "SELECT alm.*
                FROM almacen alm
                WHERE alm.id=:id_almacen AND alm.activo=1";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':id_almacen', $id_almacen, PDO::PARAM_STR);
        $res->execute();
        if($res->rowCount()>0){
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $res = $res[0];
            $data_regional = $this->dataRegionalRepository->getRegional($res['id_regional']);
            $data_programa = $this->dataProgramaRepository->getPrograma($res['id_programa']);
            $result = array('id'=>$res['id'],
                            'codigo'=>$res['codigo'],
                            'nombre'=>$res['nombre'],
                            'regional'=>$data_regional['data_regional'],
                            'programa'=>$data_programa['data_programa'],
                            'direccion'=>$res['direccion'],
                            'telefono'=>$res['telefono'],
                            'activo'=>$res['activo']);
            $resp = array('success'=>true,'message'=>'Exito','data_almacen'=>$result);
        }else{
            $resp = array('success'=>false,'message'=>'No se encontraron registros');
        }
        return $resp;
    }

    public function listAlmacen($query): array {
        if(!(isset($query['filtro'])&&isset($query['limite'])&&isset($query['indice']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        $filtro=$query['filtro'];
        $limite=$query['limite'];
        $indice=$query['indice'];
        $limite=$limite+$indice;
        $filter="%".strtolower($filtro)."%";
        $sql = "SELECT alm.*
                FROM almacen alm
                WHERE alm.activo=1 AND 
                (LOWER(alm.nombre) LIKE LOWER(:filter) OR LOWER(alm.direccion) LIKE LOWER(:filter) OR LOWER(alm.codigo) LIKE LOWER(:filter) OR LOWER(alm.telefono) LIKE LOWER(:filter) OR DATE_FORMAT(alm.f_crea,'%d/%m/%Y') LIKE :filter)";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':filter', $filter, PDO::PARAM_STR);
        $res->execute();
        $total=$res->rowCount();
        $sql = "SELECT alm.*
                FROM almacen alm
                WHERE alm.activo=1 AND 
                (LOWER(alm.nombre) LIKE LOWER(:filter) OR LOWER(alm.direccion) LIKE LOWER(:filter) OR LOWER(alm.codigo) LIKE LOWER(:filter) OR LOWER(alm.telefono) LIKE LOWER(:filter) OR DATE_FORMAT(alm.f_crea,'%d/%m/%Y') LIKE :filter)
                ORDER BY alm.f_crea DESC
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
                $data_regional = $this->dataRegionalRepository->getRegional($item['id_regional']);
                $data_programa = $this->dataProgramaRepository->getPrograma($item['id_programa']);
                $result = array('id'=>$item['id'],
                                'codigo'=>$item['codigo'],
                                'nombre'=>$item['nombre'],
                                'regional'=>$data_regional['data_regional'],
                                'programa'=>$data_programa['data_programa'],
                                'direccion'=>$item['direccion'],
                                'telefono'=>$item['telefono'],
                                'activo'=>$item['activo']);
                array_push($arrayres,$result);
            }
            $concat=array('resultados'=>$arrayres,'total'=>$total);
            $resp = array('success'=>true,'message'=>'Exito','data_almacen'=>$concat);
        }else{
            $resp = array('success'=>false,'message'=>'No se encontraron registros');
        }
        return $resp;
    }

    public function editAlmacen($id_almacen,$data_almacen,$uuid): array {
        if(!(isset($data_almacen['nombre'])&&isset($data_almacen['regional'])&&isset($data_almacen['programa'])&&isset($data_almacen['direccion'])&&isset($data_almacen['telefono']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        $sql = "SELECT *
                FROM almacen
                WHERE nombre=:nombre AND id!=:id_almacen";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':nombre', $data_almacen['nombre'], PDO::PARAM_STR);
            $res->bindParam(':id_almacen', $id_almacen, PDO::PARAM_STR);
            $res->execute();
        if($res->rowCount()>0){
            $resp = array('success'=>false,'message'=>'Error, el nombre del almacen ya existe en otro registro');
        }else{
            $sql = "UPDATE almacen 
                    SET nombre=:nombre,
                    id_regional=:id_regional,
                    id_programa=:id_programa,
                    direccion=:direccion,
                    telefono=:telefono,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_almacen;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_almacen', $id_almacen, PDO::PARAM_STR);
            $res->bindParam(':nombre', $data_almacen['nombre'], PDO::PARAM_STR);
            $aux = $data_almacen['regional']['id'];
            $res->bindParam(':id_regional', $aux, PDO::PARAM_STR);
            $aux2 = $data_almacen['programa']['id'];
            $res->bindParam(':id_programa', $aux2, PDO::PARAM_STR);
            $res->bindParam(':direccion', $data_almacen['direccion'], PDO::PARAM_STR);
            $res->bindParam(':telefono', $data_almacen['telefono'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            //$res = $res->fetchAll(PDO::FETCH_ASSOC);
            $resp = array('success'=>true,'message'=>'almacen actualizado','data_almacen'=>$data_almacen);
        }
        return $resp;
    }

    public function changestatusAlmacen($id_almacen,$uuid): array {
        $sql = "UPDATE almacen 
                SET activo=0,
                f_inac=now(), 
                u_inac=:u_inac
                WHERE id=:id_almacen;";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':u_inac', $uuid, PDO::PARAM_STR);
        $res->bindParam(':id_almacen', $id_almacen, PDO::PARAM_STR);
        $res->execute();
        //$res = $res->fetchAll(PDO::FETCH_ASSOC);
        if($res->rowCount()==1){
            $resp = array('success'=>true,'message'=>'1 fila afectada');
        }else{
            $resp = array('success'=>false,'message'=>'0 fila afectada');
        }
        return ($resp);
    }

    public function createAlmacen($data_almacen,$uuid): array {
        if(!(isset($data_almacen['nombre'])&&isset($data_almacen['programa'])&&isset($data_almacen['regional'])&&isset($data_almacen['direccion'])&&isset($data_almacen['telefono']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        $sql = "SELECT *
                FROM almacen
                WHERE nombre=:nombre";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':nombre', $data_almacen['nombre'], PDO::PARAM_STR);
        $res->execute();
        if($res->rowCount()>0){
            $resp = array('success'=>false,'message'=>'Error, el nombre del almacen ya existe');
        }else{
            $correlativo = $this->dataCorrelativoRepository->genCorrelativo('ALM', '0', $uuid);
            $correlativo = $correlativo['correlativo'];
            $correlativo = 'ALM-' . $correlativo;
            $uuid_neo = Uuid::v4();
            $sql = "INSERT INTO almacen (
                    id,
                    codigo,
                    nombre,
                    id_programa,
                    id_regional,
                    direccion,
                    telefono,
                    activo,
                    f_crea,
                    u_crea
                    )VALUES(
                    :uuid,
                    :codigo,
                    :nombre,
                    :id_programa,
                    :id_regional,
                    :direccion,
                    :telefono,
                    1,
                    now(),
                    :u_crea
                    );";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':uuid', $uuid_neo, PDO::PARAM_STR);
            $res->bindParam(':codigo', $correlativo, PDO::PARAM_STR);
            $res->bindParam(':nombre', $data_almacen['nombre'], PDO::PARAM_STR);
            $aux = $data_almacen['regional']['id'];
            $res->bindParam(':id_regional', $aux, PDO::PARAM_STR);
            $aux2 = $data_almacen['programa']['id'];
            $res->bindParam(':id_programa', $aux2, PDO::PARAM_STR);
            $res->bindParam(':direccion', $data_almacen['direccion'], PDO::PARAM_STR);
            $res->bindParam(':telefono', $data_almacen['telefono'], PDO::PARAM_STR);
            $res->bindParam(':u_crea', $uuid, PDO::PARAM_STR);
            $res->execute();
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $sql = "SELECT alm.*
                    FROM almacen alm
                    WHERE alm.codigo=:codigo AND alm.activo=1";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':codigo', $correlativo, PDO::PARAM_STR);
            $res->execute();
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $res = $res[0];
            $data_regional = $this->dataRegionalRepository->getRegional($res['id_regional']);
            $data_programa = $this->dataProgramaRepository->getPrograma($res['id_programa']);
            $result = array('id'=>$res['id'],
                            'codigo'=>$res['codigo'],
                            'nombre'=>$res['nombre'],
                            'regional'=>$data_regional['data_regional'],
                            'programa'=>$data_programa['data_programa'],
                            'direccion'=>$res['direccion'],
                            'telefono'=>$res['telefono'],
                            'activo'=>$res['activo']);
            $resp = array('success'=>true,'message'=>'almacen registrado exitosamente','data_almacen'=>$result);
        }
        return $resp;
    }
}
