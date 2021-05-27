<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Actions\RepositoryConection\Conect;
use App\Domain\AlmacenRepository;
use App\Infrastructure\Persistence\DataCorrelativoRepository;
use App\Infrastructure\Persistence\DataRegionalRepository;
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

    public function getAlmacen($id_almacen,$token): array {
        if(!($this->verificaPermisos($id_almacen,null,$token))){
            return array('success'=>false,'message'=>'usuario no autorizado','code'=>403,'data_almacen'=>array());
        }
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
                            'id_regional'=>$data_regional['data_regional'],
                            'id_programa'=>$data_programa['data_programa'],
                            'direccion'=>$res['direccion'],
                            'telefono'=>$res['telefono'],
                            'activo'=>$res['activo']);
            $resp = array('success'=>true,'message'=>'Exito','data_almacen'=>$result,'code'=>200);
        }else{
            $resp = array('success'=>true,'message'=>'No se encontraron registros','data_almacen'=>array(),'code'=>200);
        }
        return $resp;
    }

    public function listAlmacen($query,$token): array {
        if(!(isset($query['filtro'])&&isset($query['limite'])&&isset($query['indice']))){
            return array('success'=>false,'message'=>'Datos invalidos','code'=>202,'data_almacen'=>array());
        }
        $filtro=$query['filtro'];
        $limite=$query['limite'];
        $indice=$query['indice'];
        if($token->privilegio=='limitado'){
            $filtro_regional="id_regional='".$token->regional."' AND ";
        }else{
            $filtro_regional="";
        }
        $limite=$limite+$indice;
        $filter="%".strtolower($filtro)."%";
        $sql = "SELECT alm.*
                FROM almacen alm
                WHERE alm.activo=1 AND ".$filtro_regional."
                (LOWER(alm.nombre) LIKE LOWER(:filter) OR LOWER(alm.direccion) LIKE LOWER(:filter) OR LOWER(alm.codigo) LIKE LOWER(:filter) OR LOWER(alm.telefono) LIKE LOWER(:filter) OR DATE_FORMAT(alm.f_crea,'%d/%m/%Y') LIKE :filter)";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':filter', $filter, PDO::PARAM_STR);
        $res->execute();
        $total=$res->rowCount();
        $sql = "SELECT alm.*
                FROM almacen alm
                WHERE alm.activo=1 AND ".$filtro_regional."
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
                                'id_regional'=>$data_regional['data_regional'],
                                'id_programa'=>$data_programa['data_programa'],
                                'direccion'=>$item['direccion'],
                                'telefono'=>$item['telefono'],
                                'activo'=>$item['activo']);
                array_push($arrayres,$result);
            }
            $concat=array('resultados'=>$arrayres,'total'=>$total);
            $resp = array('success'=>true,'message'=>'Exito','data_almacen'=>$concat,'code'=>200);
        }else{
            $concat=array('resultados'=>array(),'total'=>0);
            $resp = array('success'=>true,'message'=>'No se encontraron registros','data_almacen'=>$concat,'code'=>200);
        }
        return $resp;
    }

    public function editAlmacen($id_almacen,$data_almacen,$token): array {
        if(!(isset($data_almacen['nombre'])&&isset($data_almacen['id_regional'])&&isset($data_almacen['id_programa'])&&isset($data_almacen['direccion'])&&isset($data_almacen['telefono']))){
            return array('success'=>false,'message'=>'Datos invalidos','code'=>202,'data_almacen'=>array());
        }
        if(!($this->verificaPermisos($id_almacen,$data_almacen['id_regional']['id'],$token))){
            return array('success'=>false,'message'=>'usuario no autorizado','code'=>403,'data_almacen'=>array());
        }
        $sql = "SELECT *
                FROM almacen
                WHERE nombre=:nombre AND id!=:id_almacen";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':nombre', $data_almacen['nombre'], PDO::PARAM_STR);
            $res->bindParam(':id_almacen', $id_almacen, PDO::PARAM_STR);
            $res->execute();
        if($res->rowCount()>0){
            $resp = array('success'=>false,'message'=>'Error, el nombre del almacen ya existe en otro registro','code'=>202,'data_almacen'=>array());
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
            $aux = $data_almacen['id_regional']['id'];
            $res->bindParam(':id_regional', $aux, PDO::PARAM_STR);
            $aux2 = $data_almacen['id_programa']['id'];
            $res->bindParam(':id_programa', $aux2, PDO::PARAM_STR);
            $res->bindParam(':direccion', $data_almacen['direccion'], PDO::PARAM_STR);
            $res->bindParam(':telefono', $data_almacen['telefono'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
            $res->execute();
            //$res = $res->fetchAll(PDO::FETCH_ASSOC);
            $resp = array('success'=>true,'message'=>'almacen actualizado','data_almacen'=>$data_almacen,'code'=>200);
        }
        return $resp;
    }

    public function changestatusAlmacen($id_almacen,$token): array {
        if(!($this->verificaPermisos($id_almacen,null,$token))){
            return array('success'=>false,'message'=>'usuario no autorizado','code'=>403,'data_almacen'=>array());
        }
        $sql = "UPDATE almacen 
                SET activo=0,
                f_inac=now(), 
                u_inac=:u_inac
                WHERE id=:id_almacen;";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':u_inac', $token->sub, PDO::PARAM_STR);
        $res->bindParam(':id_almacen', $id_almacen, PDO::PARAM_STR);
        $res->execute();
        //$res = $res->fetchAll(PDO::FETCH_ASSOC);
        if($res->rowCount()==1){
            $resp = array('success'=>true,'message'=>'1 fila afectada','code'=>200,'data_almacen'=>array());
        }else{
            $resp = array('success'=>false,'message'=>'0 fila afectada','code'=>202,'data_almacen'=>array());
        }
        return ($resp);
    }

    public function createAlmacen($data_almacen,$token): array {
        if(!(isset($data_almacen['nombre'])&&isset($data_almacen['id_programa'])&&isset($data_almacen['id_regional'])&&isset($data_almacen['direccion'])&&isset($data_almacen['telefono']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        if(!($this->verificaPermisos(null,$data_almacen['id_regional']['id'],$token))){
            return array('success'=>false,'message'=>'usuario no autorizado','code'=>403,'data_almacen'=>array());
        }
        $sql = "SELECT *
                FROM almacen
                WHERE nombre=:nombre";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':nombre', $data_almacen['nombre'], PDO::PARAM_STR);
        $res->execute();
        if($res->rowCount()>0){
            $resp = array('success'=>false,'message'=>'Error, el nombre del almacen ya existe','code'=>202,'data_almacen'=>array());
        }else{
            $correlativo = $this->dataCorrelativoRepository->genCorrelativo('ALM', '0', $token->sub);
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
            $aux = $data_almacen['id_regional']['id'];
            $res->bindParam(':id_regional', $aux, PDO::PARAM_STR);
            $aux2 = $data_almacen['id_programa']['id'];
            $res->bindParam(':id_programa', $aux2, PDO::PARAM_STR);
            $res->bindParam(':direccion', $data_almacen['direccion'], PDO::PARAM_STR);
            $res->bindParam(':telefono', $data_almacen['telefono'], PDO::PARAM_STR);
            $res->bindParam(':u_crea', $token->sub, PDO::PARAM_STR);
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
            $resp = array('success'=>true,'message'=>'almacen registrado exitosamente','data_almacen'=>$result,'code'=>200);
        }
        return $resp;
    }

    private function verificaPermisos($uuid_registro_a_modificar,$id_regional_registro_nuevo,$token){
        //sacamos los datos del token
        $tabla='almacen';
        $regional_usuario=$token->regional;
        $privilegio_usuario=$token->privilegio;
        if($privilegio_usuario=='total'){//el usuario tiene acceso total
            return true;
        }else{//el usuario tiene acceso limitado a su regional
            if($uuid_registro_a_modificar==null){
                //es una alta
                if($id_regional_registro_nuevo!=$regional_usuario){
                    //el nuevo registro que intenta introducir el usuario pertenecerá a otra regional
                    return false;
                }else{
                    return true;//el nuevo registro pertenece a la regional del usuario
                }
            }else{
                //es una modificacion
                $sql = "SELECT id_regional
                        FROM almacen
                        WHERE id=:uuid;";
                $res = ($this->db)->prepare($sql);
                $res->bindParam(':uuid', $uuid_registro_a_modificar, PDO::PARAM_STR);
                //$res->bindParam(':tabla', $tabla, PDO::PARAM_INT);
                $res->execute();
                if($res->rowCount()>0){
                    $res = $res->fetchAll(PDO::FETCH_ASSOC);
                    $id_regional_ant = $res[0]['id_regional'];
                    if($id_regional_ant!=$regional_usuario){
                        //el usuario intenta modificar un registro distinto al de su regional
                        return false;
                    }else{
                        if($id_regional_registro_nuevo==null){
                            return true;
                        }else{
                            if($id_regional_registro_nuevo!=$regional_usuario){
                                //el nuevo registro que intenta modificar el usuario pertenecerá a otra regional
                                return false;
                            }else{
                                return true;
                            }
                        }
                    }
                }else{
                    return false;
                }                    
            }
        }
    }
}
