<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Actions\RepositoryConection\Conect;
use App\Domain\FacturacionRepository;
use \PDO;
use AbmmHasan\Uuid;

class DataFacturacionRepository implements FacturacionRepository {

    /**
     * @var data[]
     */
    private $data;

    /**
     * @var $db conection db
     */
    private $db;

    /**
     * DataFacturacionRepository constructor.
     *
     */
    public function __construct() {
        $con = new Conect();
        $this->db = $con->getConection();
    }

    public function getDoficifacion($id_dosificacion): array {
        $sql = "SELECT fd.*, reg.id as id_regional, reg.nombre as nombre_regional
                FROM fac_dosificacion fd LEFT JOIN 
                     regional as reg ON fd.id_regional = reg.id
                WHERE fd.id=:id_dosificacion AND fd.activo=1";
                
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
                            'id_regional' => array('id' => $res['id_regional'],
                                                   'nombre' => $res['nombre_regional']));
            $resp = array('success'=>true,'message'=>'Exito','data_dosificacion'=>$result);
        } else {
            $resp = array('success'=>false,'message'=>'No se encontraron registros');
        }
        return $resp;
    }

    public function listDoficifacion($query): array {
        if(!(isset($query['filtro'])&&isset($query['limite'])&&isset($query['indice']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        $filtro=$query['filtro'];
        $limite=$query['limite'];
        $indice=$query['indice'];
        $limite=$limite+$indice;
        $filter="%".strtolower($filtro)."%";

        $sql = "SELECT fd.*
                FROM fac_dosificacion as fd LEFT JOIN regional as reg ON fd.id_regional = reg.id
                WHERE fd.activo = 1 AND (LOWER(fd.llave_dosificacion) LIKE LOWER(:filter) OR LOWER(fd.nro_autorizacion) LIKE LOWER(:filter)
                                         OR DATE_FORMAT(fd.fecha_exp, '%d/%m/%Y') LIKE :filter OR DATE_FORMAT(fd.f_crea,'%d/%m/%Y') LIKE :filter
                                         OR LOWER(reg.nombre) LIKE LOWER(:filter))";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':filter', $filter, PDO::PARAM_STR);
        $res->execute();
        $total=$res->rowCount();

        $sql = "SELECT fd.*, reg.id as id_regional, reg.nombre as nombre_regional
                FROM fac_dosificacion as fd LEFT JOIN regional as reg ON fd.id_regional = reg.id
                WHERE fd.activo = 1 AND (LOWER(fd.llave_dosificacion) LIKE LOWER(:filter) OR LOWER(fd.nro_autorizacion) LIKE LOWER(:filter)
                                         OR DATE_FORMAT(fd.fecha_exp, '%d/%m/%Y') LIKE :filter OR DATE_FORMAT(fd.f_crea,'%d/%m/%Y') LIKE :filter
                                         OR LOWER(reg.nombre) LIKE LOWER(:filter))
                ORDER BY fd.f_crea DESC
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
                                'llave_dosificacion' => $item['llave_dosificacion'],
                                'nro_autorizacion' => $item['nro_autorizacion'],
                                'fecha_exp'=> date('d/m/Y',strtotime($item['fecha_exp'])),
                                'activo' =>  $item['activo'],
                                'id_regional' => array('id' => $item['id_regional'],
                                                   'nombre' => $item['nombre_regional']));
                array_push($arrayres,$result);
            }
            $concat=array('resultados'=>$arrayres,'total'=>$total);
            $resp = array('success'=>true,'message'=>'Exito','data_dosificacion'=>$concat);
        } else {
            $resp = array('success'=>false,'message'=>'No se encontraron registros');
        }
        return $resp;
    }

    public function createDocificacion($data_docificacion,$uuid): array {
        if(!(isset($data_docificacion['llave_dosificacion'])&&isset($data_docificacion['nro_autorizacion'])&&isset($data_docificacion['fecha_exp'])&&isset($data_docificacion['id_regional']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        // OR :fecha_exp > now() -> por si hay que valida la fecha de exp
        $sql = "SELECT *
                FROM fac_dosificacion
                WHERE llave_dosificacion LIKE :llave_dosificacion";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':llave_dosificacion', $data_docificacion['llave_dosificacion'], PDO::PARAM_STR);
        $res->execute();
        if($res->rowCount()==1){
            $resp = array('success'=>false,'message'=>'Error, la llave de dosificación ya existe');
        }else{
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
            $res->bindParam(':id_regional', $data_docificacion['id_regional'], PDO::PARAM_STR);
            $res->bindParam(':u_crea', $uuid, PDO::PARAM_STR);
            $res->execute();
            $res = $res->fetchAll(PDO::FETCH_ASSOC);

            $sql = "SELECT fd.*, reg.id, reg.codigo as cod_reg, reg.nombre
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
                            'id_regional'=>$res['id_regional']);
            $resp = array('success'=>true,'message'=>'Docificación registrada exitosamente','data_dosificacion'=>$result);
         }
        return $resp;
    }

    public function editDosificacion($id_dosificacion,$data_docificacion,$uuid): array {
        if(!(isset($data_docificacion['llave_dosificacion'])&&isset($data_docificacion['nro_autorizacion'])&&isset($data_docificacion['fecha_exp'])&&isset($data_docificacion['id_regional']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        $sql = "SELECT *
                FROM fac_dosificacion
                WHERE llave_dosificacion LIKE :llave_dosificacion";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':llave_dosificacion', $data_docificacion['llave_dosificacion'], PDO::PARAM_STR);
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
            $res->bindParam(':id_regional', $data_docificacion['id_regional'], PDO::PARAM_STR);
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
