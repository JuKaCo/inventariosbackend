<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Actions\RepositoryConection\Conect;
use App\Domain\DosificacionRepository;
use \PDO;
use AbmmHasan\Uuid;

class DataDosificacionRepository implements DosificacionRepository {

    /**
     * @var data[]
     */
    private $data;

    /**
     * @var $db conection db
     */
    private $db;

    /**
     * DataDosificacionRepository constructor.
     *
     */
    public function __construct() {
        $con = new Conect();
        $this->db = $con->getConection();
    }

    public function getDoficifacion($id_dosificacion): array {
        $sql = "SELECT fd.*, reg.id as id_regional, reg.nombre as nombre_regional, reg.codigo, reg.direccion, reg.telefono
                FROM fac_dosificacion fd LEFT JOIN 
                     regional as reg ON fd.id_regional = reg.id
                WHERE fd.id=:id_dosificacion";
                
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':id_dosificacion', $id_dosificacion, PDO::PARAM_STR);
        $res->execute();
        if($res->rowCount()>0){
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $res = $res[0];
            $result = array('id' => $res['id'],
                            'llave_dosificacion' => $res['llave_dosificacion'],
                            'nro_autorizacion' => $res['nro_autorizacion'],
                            'fecha_exp'=> date('d/m/Y',strtotime($res['fecha_exp'])),
                            'activo' =>  $res['activo'],
                            'regional' => array('id' => $res['id_regional'],
                                                   'nombre' => $res['nombre_regional'],
                                                   'codigo' => $res['codigo'],
                                                   'direccion' => $res['direccion'],
                                                   'telefono' => $res['telefono']));
            $resp = array('success'=>true,'message'=>'Exito','data_dosificacion'=>$result);
        } else {
            $resp = array('success'=>false,'message'=>'No se encontraron registros');
        }
        return $resp;
    }

    public function listDoficifacion($query): array {
        if(!(isset($query['filtro'])&&isset($query['estado'])&&isset($query['limite'])&&isset($query['indice']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        $filtro=$query['filtro'];
        $limite=$query['limite'];
        $indice=$query['indice'];
        $estado=$query['estado'];
        $limite=$limite+$indice;
        $filter="%".strtolower($filtro)."%";

        $sql = "SELECT fd.*
                FROM fac_dosificacion as fd LEFT JOIN regional as reg ON fd.id_regional = reg.id
                WHERE fd.activo = :estado AND (LOWER(fd.llave_dosificacion) LIKE LOWER(:filter) OR LOWER(fd.nro_autorizacion) LIKE LOWER(:filter)
                                         OR DATE_FORMAT(fd.fecha_exp, '%d/%m/%Y') LIKE :filter OR DATE_FORMAT(fd.f_crea,'%d/%m/%Y') LIKE :filter
                                         OR LOWER(reg.nombre) LIKE LOWER(:filter))";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':filter', $filter, PDO::PARAM_STR);
        $res->bindParam(':estado', $estado, PDO::PARAM_INT);
        $res->execute();
        $total=$res->rowCount();

        $sql = "SELECT fd.*, reg.id as id_regional, reg.nombre as nombre_regional, reg.codigo, reg.direccion, reg.telefono
                FROM fac_dosificacion as fd LEFT JOIN regional as reg ON fd.id_regional = reg.id
                WHERE fd.activo = :estado AND (LOWER(fd.llave_dosificacion) LIKE LOWER(:filter) OR LOWER(fd.nro_autorizacion) LIKE LOWER(:filter)
                                         OR DATE_FORMAT(fd.fecha_exp, '%d/%m/%Y') LIKE :filter OR DATE_FORMAT(fd.f_crea,'%d/%m/%Y') LIKE :filter
                                         OR LOWER(reg.nombre) LIKE LOWER(:filter))
                ORDER BY fd.f_crea DESC
                LIMIT :indice, :limite;";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':filter', $filter, PDO::PARAM_STR);
        $res->bindParam(':limite', $limite, PDO::PARAM_INT);
        $res->bindParam(':indice', $indice, PDO::PARAM_INT);
        $res->bindParam(':estado', $estado, PDO::PARAM_INT);
        $res->execute();

        if($res->rowCount()>0){
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $arrayres = array();
            foreach ($res as $item){
                $result = array('id'=>$item['id'],
                                'llave_dosificacion' => $item['llave_dosificacion'],
                                'nro_autorizacion' => $item['nro_autorizacion'],
                                'fecha_exp'=> date('d/m/Y',strtotime($item['fecha_exp'])),
                                'activo' =>  $item['activo'],
                                'regional' => array('id' => $item['id_regional'],
                                                    'nombre' => $item['nombre_regional'],
                                                    'codigo' => $item['codigo'],
                                                    'direccion' => $item['direccion'],
                                                    'telefono' => $item['telefono']));
                array_push($arrayres,$result);
            }
            $concat=array('resultados'=>$arrayres,'total'=>$total);
            $resp = array('success'=>true,'message'=>'Exito','data_dosificacion'=>$concat);
        } else {
            $resp = array('success'=>true,'message'=>'No se encontraron registros','data_dosificacion'=> [] );
        }
        return $resp;
    }

    public function createDocificacion($data_docificacion,$uuid): array {
        if(!(isset($data_docificacion['llave_dosificacion'])&&isset($data_docificacion['nro_autorizacion'])&&isset($data_docificacion['fecha_exp'])&&isset($data_docificacion['regional']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        // OR :fecha_exp > now() -> por si hay que valida la fecha de exp
        $sql = "SELECT *
                FROM fac_dosificacion
                WHERE llave_dosificacion = :llave_dosificacion";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':llave_dosificacion', $data_docificacion['llave_dosificacion'], PDO::PARAM_STR);
        $res->execute();
        if($res->rowCount() > 1){
            $resp = array('success'=>false,'message'=>'Error, la llave de dosificación ya existe');
        }else{
            $sql = "SELECT *
                    FROM fac_dosificacion
                    WHERE id_regional = :id_regional AND activo = 1";
            $res = ($this->db)->prepare($sql);
            $aux = $data_docificacion['regional']['id'];
            $res->bindParam(':id_regional', $aux, PDO::PARAM_STR);
            $res->execute();
            if($res->rowCount() == 1){
                $res = $res->fetchAll(PDO::FETCH_ASSOC);
                $res = $res[0];
                $sql = "UPDATE fac_dosificacion 
                        SET activo=0,
                        f_inac=now(), 
                        u_inac=:u_inac
                        WHERE id=:id_dosificacion;";
                $res1 = ($this->db)->prepare($sql);
                $res1->bindParam(':u_inac', $uuid, PDO::PARAM_STR);
                $aux = $res['id'];
                $res1->bindParam(':id_dosificacion', $aux, PDO::PARAM_STR);
                $res1->execute();
                if($res1->rowCount()==1){
                    $uuid_neo = Uuid::v4();
                    $sql = "INSERT INTO fac_dosificacion (
                        id,
                        llave_dosificacion,
                        nro_autorizacion,
                        fecha_exp,
                        id_regional,
                        activo,
                        f_crea,
                        u_crea
                        )VALUES(
                        :uuid,
                        :llave_dosificacion,
                        :nro_autorizacion,
                        STR_TO_DATE(:fecha_exp, '%d/%m/%Y'),
                        :id_regional,
                        1,
                        now(),
                        :u_crea
                        );";
                    $res2 = ($this->db)->prepare($sql);
        
                    $res2->bindParam(':uuid', $uuid_neo, PDO::PARAM_STR);
                    $res2->bindParam(':llave_dosificacion', $data_docificacion['llave_dosificacion'], PDO::PARAM_STR);
                    $res2->bindParam(':nro_autorizacion', $data_docificacion['nro_autorizacion'], PDO::PARAM_STR);
                    $res2->bindParam(':fecha_exp', $data_docificacion['fecha_exp'], PDO::PARAM_STR);
                    $aux = $data_docificacion['regional']['id'];
                    $res2->bindParam(':id_regional', $aux, PDO::PARAM_STR);
                    $res2->bindParam(':u_crea', $uuid, PDO::PARAM_STR);
                    $res2->execute();
                    $res2 = $res2->fetchAll(PDO::FETCH_ASSOC);
        
                    $sql = "SELECT fd.*, reg.id as id_regional2, reg.codigo as cod_reg, reg.nombre, reg.codigo, reg.direccion, reg.telefono
                            FROM fac_dosificacion fd, regional reg
                            WHERE fd.id=:uuid AND fd.activo=1 AND fd.id_regional=reg.id";
                    $res2 = ($this->db)->prepare($sql);
                    $res2->bindParam(':uuid', $uuid_neo, PDO::PARAM_STR);
                    $res2->execute();
                    $res2 = $res2->fetchAll(PDO::FETCH_ASSOC);
                    $res2 = $res2[0];
        
                    $result = array('id'=>$res2['id'],
                                    'llave_dosificacion'=>$res2['llave_dosificacion'],
                                    'nro_autorizacion'=>$res2['nro_autorizacion'],
                                    'fecha_exp'=>date('d/m/Y',strtotime($res2['fecha_exp'])),
                                    'activo'=>$res2['activo'],
                                    'regional' => array('id' => $res2['id_regional2'],
                                                            'nombre' => $res2['nombre'],
                                                            'codigo' => $res2['cod_reg'],
                                                            'direccion' => $res2['direccion'],
                                                            'telefono' => $res2['telefono']));
                    $resp = array('success'=>true,'message'=>'Docificación registrada exitosamente','data_dosificacion'=>$result);
                }else{
                    $resp = array('success'=>false,'message'=>'Error, ocurrio un error');
                }
                
            } else {
                $uuid_neo = Uuid::v4();
                $sql = "INSERT INTO fac_dosificacion (
                    id,
                    llave_dosificacion,
                    nro_autorizacion,
                    fecha_exp,
                    id_regional,
                    activo,
                    f_crea,
                    u_crea
                    )VALUES(
                    :uuid,
                    :llave_dosificacion,
                    :nro_autorizacion,
                    STR_TO_DATE(:fecha_exp, '%d/%m/%Y'),
                    :id_regional,
                    1,
                    now(),
                    :u_crea
                    );";
                $res = ($this->db)->prepare($sql);
    
                $res->bindParam(':uuid', $uuid_neo, PDO::PARAM_STR);
                $res->bindParam(':llave_dosificacion', $data_docificacion['llave_dosificacion'], PDO::PARAM_STR);
                $res->bindParam(':nro_autorizacion', $data_docificacion['nro_autorizacion'], PDO::PARAM_STR);
                $res->bindParam(':fecha_exp', $data_docificacion['fecha_exp'], PDO::PARAM_STR);
                $aux = $data_docificacion['regional']['id'];
                $res->bindParam(':id_regional', $aux, PDO::PARAM_STR);
                $res->bindParam(':u_crea', $uuid, PDO::PARAM_STR);
                $res->execute();
                $res = $res->fetchAll(PDO::FETCH_ASSOC);
    
                $sql = "SELECT fd.*, reg.id as id_regional2, reg.codigo as cod_reg, reg.nombre, reg.codigo, reg.direccion, reg.telefono
                        FROM fac_dosificacion fd, regional reg
                        WHERE fd.id=:uuid AND fd.activo=1 AND fd.id_regional=reg.id";
                $res = ($this->db)->prepare($sql);
                $res->bindParam(':uuid', $uuid_neo, PDO::PARAM_STR);
                $res->execute();
                $res = $res->fetchAll(PDO::FETCH_ASSOC);
                $res = $res[0];
    
                $result = array('id'=>$res['id'],
                                'llave_dosificacion'=>$res['llave_dosificacion'],
                                'nro_autorizacion'=>$res['nro_autorizacion'],
                                'fecha_exp'=>date('d/m/Y',strtotime($res['fecha_exp'])),
                                'activo'=>$res['activo'],
                                'regional' => array('id' => $res['id_regional2'],
                                                        'nombre' => $res['nombre'],
                                                        'codigo' => $res['cod_reg'],
                                                        'direccion' => $res['direccion'],
                                                        'telefono' => $res['telefono']));
                $resp = array('success'=>true,'message'=>'Docificación registrada exitosamente','data_dosificacion'=>$result);
            }

         }
        return $resp;
    }

    public function editDosificacion($id_dosificacion,$data_docificacion,$uuid): array {
        if(!(isset($data_docificacion['llave_dosificacion'])&&isset($data_docificacion['nro_autorizacion'])&&isset($data_docificacion['fecha_exp'])&&isset($data_docificacion['regional']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        $sql = "SELECT *
                FROM fac_dosificacion
                WHERE llave_dosificacion LIKE :llave_dosificacion AND id <> :id AND activo=1";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':llave_dosificacion', $data_docificacion['llave_dosificacion'], PDO::PARAM_STR);
        $res->bindParam('id', $id_dosificacion, PDO::PARAM_STR);
        $res->execute();

        if($res->rowCount()==1){
            $resp = array('success'=>false,'message'=>'Error, la llave de dosificación ya existe');
        }else{
            $sql = "UPDATE fac_dosificacion 
                    SET llave_dosificacion=:llave_dosificacion,
                    nro_autorizacion=:nro_autorizacion,
                    fecha_exp=STR_TO_DATE(:fecha_exp, '%d/%m/%Y'),
                    id_regional=:id_regional,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_dosificacion;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_dosificacion', $id_dosificacion, PDO::PARAM_STR);
            $res->bindParam(':llave_dosificacion', $data_docificacion['llave_dosificacion'], PDO::PARAM_STR);
            $res->bindParam(':nro_autorizacion', $data_docificacion['nro_autorizacion'], PDO::PARAM_STR);
            $res->bindParam(':fecha_exp', $data_docificacion['fecha_exp'], PDO::PARAM_STR);
            $aux = $data_docificacion['regional']['id'];
            $res->bindParam(':id_regional', $aux, PDO::PARAM_STR);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            $resp = array('success'=>true,'message'=>'Dosificación actualizada','data_dosificacion'=>$data_docificacion);
        }
        return $resp;
    }

    public function modifyDosificacion($id_dosificacion,$data_docificacion,$uuid): array {
        $success=true;
        $resp=array();
        
        if(isset($data_docificacion['llave_dosificacion'])){
            $sql = "UPDATE fac_dosificacion 
                        SET llave_dosificacion=:llave_dosificacion,
                        f_mod=now(), 
                        u_mod=:u_mod
                        WHERE id=:id_dosificacion;";
                $res = ($this->db)->prepare($sql);
                $res->bindParam(':id_dosificacion', $id_dosificacion, PDO::PARAM_STR);
                $res->bindParam(':llave_dosificacion', $data_docificacion['llave_dosificacion'], PDO::PARAM_STR);
                $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
                $res->execute();
                $resp += ['llave_dosificacion' => 'dato actualizado'];
        }

        if(isset($data_docificacion['nro_autorizacion'])){
            $sql = "UPDATE fac_dosificacion 
                        SET nro_autorizacion=:nro_autorizacion,
                        f_mod=now(), 
                        u_mod=:u_mod
                        WHERE id=:id_dosificacion;";
                $res = ($this->db)->prepare($sql);
                $res->bindParam(':id_dosificacion', $id_dosificacion, PDO::PARAM_STR);
                $res->bindParam(':nro_autorizacion', $data_docificacion['nro_autorizacion'], PDO::PARAM_STR);
                $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
                $res->execute();
                $resp += ['nro_autorizacion' => 'dato actualizado'];
        }

        if(isset($data_docificacion['fecha_exp'])){
            $sql = "UPDATE fac_dosificacion 
                        SET fecha_exp=STR_TO_DATE(:fecha_exp, '%d/%m/%Y'),
                        f_mod=now(), 
                        u_mod=:u_mod
                        WHERE id=:id_dosificacion;";
                $res = ($this->db)->prepare($sql);
                $res->bindParam(':id_dosificacion', $id_dosificacion, PDO::PARAM_STR);
                $res->bindParam(':fecha_exp', $data_docificacion['fecha_exp'], PDO::PARAM_STR);
                $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
                $res->execute();
                $resp += ['fecha_exp' => 'dato actualizado'];
        }

        if(isset($data_docificacion['regional'])){
            $sql = "UPDATE fac_dosificacion 
                        SET id_regional=:id_regional,
                        f_mod=now(), 
                        u_mod=:u_mod
                        WHERE id=:id_dosificacion;";
                $res = ($this->db)->prepare($sql);
                $res->bindParam(':id_dosificacion', $id_dosificacion, PDO::PARAM_STR);
                $aux = $data_docificacion['regional']['id'];
                $res->bindParam(':id_regional', $aux, PDO::PARAM_STR);
                $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
                $res->execute();
                $resp += ['id_regional' => 'dato actualizado'];
        }

        if(isset($data_docificacion['id_regional'])){
            $sql = "UPDATE fac_dosificacion 
                        SET id_regional=:id_regional,
                        f_mod=now(), 
                        u_mod=:u_mod
                        WHERE id=:id_dosificacion;";
                $res = ($this->db)->prepare($sql);
                $res->bindParam(':id_dosificacion', $id_dosificacion, PDO::PARAM_STR);
                $res->bindParam(':id_regional', $data_docificacion['id_regional'], PDO::PARAM_STR);
                $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
                $res->execute();
                $resp += ['id_regional' => 'dato actualizado'];
        }

        $resp = array('success'=>$success,'message'=>'datos actualizados','data_dosificacion'=>$resp);
        return $resp;
    }

    public function changestatusDosificacion($id_dosificacion,$uuid): array {
        $sql = "UPDATE fac_dosificacion 
                SET activo=0,
                f_inac=now(), 
                u_inac=:u_inac
                WHERE id=:id_dosificacion;";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':u_inac', $uuid, PDO::PARAM_STR);
        $res->bindParam(':id_dosificacion', $id_dosificacion, PDO::PARAM_STR);
        $res->execute();
        //$res = $res->fetchAll(PDO::FETCH_ASSOC);
        if($res->rowCount()==1){
            $resp = array('success'=>true,'message'=>'1 fila afectada');
        }else{
            $resp = array('success'=>false,'message'=>'0 fila afectada');
        }
        return ($resp);
    }

}
