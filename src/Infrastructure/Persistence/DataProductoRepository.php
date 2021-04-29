<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Actions\RepositoryConection\Conect;
use App\Domain\ProductoRepository;
use \PDO;

class DataProductoRepository implements ProductoRepository {

    /**
     * @var data[]
     */
    private $data;

    /**
     * @var $db conection db
     */
    private $db;

    /**
     * DataProductoRepository constructor.
     *
     */
    public function __construct() {
        $con = new Conect();
        $this->db = $con->getConection();
    }

    public function getProducto($id_producto): array {

        $sql = "SELECT pr.*, pl.id as id_liname, pl.codigo as cod_liname, pld.id as id_linadime, pld.codigo as cod_linadime
        FROM (producto pr LEFT JOIN param_liname pl ON pr.codigo_liname=pl.id) 
        LEFT JOIN param_linadime pld ON pr.codigo_linadime=pld.id
        WHERE pr.id=:id_producto AND pr.activo=1";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':id_producto', $id_producto, PDO::PARAM_STR);
        $res->execute();
        if($res->rowCount()>0){
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $res = $res[0];

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
                            'nivel_uso_iii'=>$res['nivel_uso_iii'],
                            'activo'=>$res['activo']);
            if($result['codigo_liname']['id_liname']==null){ $result['codigo_liname']=json_decode ("{}");}
            if($result['codigo_linadime']['id_linadime']==null){$result['codigo_linadime']=json_decode ("{}");}  
            
            $resp = array('success'=>true,'message'=>'Exito','data_producto'=>$result);
        }else{
            $resp = array('success'=>false,'message'=>'No se encontraron registros');
        }
        return $resp;
    }

    public function listProducto($query): array {
        if(!(isset($query['filtro'])&&isset($query['limite'])&&isset($query['indice']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        $filtro=$query['filtro'];
        $limite=$query['limite'];
        $indice=$query['indice'];
        $limite=$limite+$indice;
        $filter="%".strtolower($filtro)."%";

        $sql = "SELECT pr.id
                FROM (producto pr LEFT JOIN param_liname pl ON pr.codigo_liname=pl.id) 
                LEFT JOIN param_linadime pld ON pr.codigo_linadime=pld.id
                WHERE pr.activo=1 AND (
            LOWER(pr.codigo) LIKE LOWER(:filtro) OR LOWER(pr.nombre_comercial) LIKE LOWER(:filtro) OR LOWER(pr.reg_san) LIKE LOWER(:filtro) OR
            LOWER(pr.referencia) LIKE LOWER(:filtro) OR LOWER(pr.medicamento) LIKE LOWER(:filtro) OR LOWER(pr.form_farm) LIKE LOWER(:filtro) OR
            LOWER(pr.concen) LIKE LOWER(:filtro) OR LOWER(pr.atq) LIKE LOWER(:filtro) OR LOWER(pr.precio_ref) LIKE LOWER(:filtro) OR
            LOWER(pr.aclara_parti) LIKE LOWER(:filtro) OR LOWER(pr.dispositivo) LIKE LOWER(:filtro) OR LOWER(pr.especificacion_tec) LIKE LOWER(:filtro) OR
            LOWER(pr.presentacion) LIKE LOWER(:filtro) OR LOWER(pl.codigo) LIKE LOWER(:filtro) OR LOWER(pld.codigo) LIKE LOWER(:filtro))";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':filtro', $filter, PDO::PARAM_STR);
        $res->execute();
        $total=$res->rowCount();
        $sql = "SELECT pr.*, pl.id as id_liname, pl.codigo as cod_liname, pld.id as id_linadime, pld.codigo as cod_linadime
                FROM (producto pr LEFT JOIN param_liname pl ON pr.codigo_liname=pl.id) 
                LEFT JOIN param_linadime pld ON pr.codigo_linadime=pld.id
                WHERE pr.activo=1 AND (
            LOWER(pr.codigo) LIKE LOWER(:filtro) OR LOWER(pr.nombre_comercial) LIKE LOWER(:filtro) OR LOWER(pr.reg_san) LIKE LOWER(:filtro) OR
            LOWER(pr.referencia) LIKE LOWER(:filtro) OR LOWER(pr.medicamento) LIKE LOWER(:filtro) OR LOWER(pr.form_farm) LIKE LOWER(:filtro) OR
            LOWER(pr.concen) LIKE LOWER(:filtro) OR LOWER(pr.atq) LIKE LOWER(:filtro) OR LOWER(pr.precio_ref) LIKE LOWER(:filtro) OR
            LOWER(pr.aclara_parti) LIKE LOWER(:filtro) OR LOWER(pr.dispositivo) LIKE LOWER(:filtro) OR LOWER(pr.especificacion_tec) LIKE LOWER(:filtro) OR
            LOWER(pr.presentacion) LIKE LOWER(:filtro) OR LOWER(pl.codigo) LIKE LOWER(:filtro) OR LOWER(pld.codigo) LIKE LOWER(:filtro))
                ORDER BY pr.f_crea DESC
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
                                'nivel_uso_iii'=>$res['nivel_uso_iii'],
                                'activo'=>$res['activo']);
                if($result['codigo_liname']['id_liname']==null){$result['codigo_liname']=json_decode ("{}");}
                if($result['codigo_linadime']['id_linadime']==null){$result['codigo_linadime']=json_decode ("{}");}  
                array_push($arrayres,$result);
            }
            $concat=array('resultados'=>$arrayres,'total'=>$total);
            $resp = array('success'=>true,'message'=>'Exito','data_producto'=>$concat);
        }else{
            $resp = array('success'=>false,'message'=>'No se encontraron registros');
        }
        return $resp;
    }

    public function editProducto($id_producto,$data_producto,$uuid): array {
        if(!(isset($id_producto)&&isset($data_producto['codigo'])&&isset($data_producto['nombre_comercial'])&&isset($data_producto['codigo_liname'])
        &&isset($data_producto['codigo_linadime'])&&isset($data_producto['reg_san'])&&isset($data_producto['referencia'])
        &&isset($data_producto['medicamento'])&&isset($data_producto['form_farm'])&&isset($data_producto['concen'])
        &&isset($data_producto['atq'])&&isset($data_producto['precio_ref'])&&isset($data_producto['aclara_parti'])
        &&isset($data_producto['dispositivo'])&&isset($data_producto['especificacion_tec'])&&isset($data_producto['presentacion']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        if($data_producto['reg_san']==""){
            $data_producto['reg_san']==null;
            $aux_query=" ";
        }else{
            $aux_query = "OR reg_san LIKE '".$data_producto['reg_san']."'";
        }
        $sql = "SELECT *
                FROM producto
                WHERE (codigo LIKE :codigo OR nombre_comercial LIKE :nombre_comercial ".$aux_query.") AND id!=:id_producto";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':codigo', $data_producto['codigo'], PDO::PARAM_STR);
            $res->bindParam(':id_producto', $id_producto, PDO::PARAM_STR);
            $res->bindParam(':nombre_comercial', $data_producto['codinombre_comercialgo'], PDO::PARAM_STR);
            //$res->bindParam(':reg_san', $data_producto['reg_san'], PDO::PARAM_STR);
            $res->execute();
        if($res->rowCount()>0){
            $resp = array('success'=>false,'message'=>'Error, el nombre comercial, codigo o registro sanitario del producto ya existe en otro registro');
        }else{
            $sql = "UPDATE producto 
                    SET codigo=:codigo,
                    nombre_comercial=:nombre_comercial,
                    codigo_liname=:codigo_liname,
                    codigo_linadime=:codigo_linadime,
                    reg_san=:reg_san,
                    referencia=:referencia,
                    medicamento=:medicamento,
                    form_farm=:form_farm,
                    concen=:concen,
                    atq=:atq,
                    precio_ref=:precio_ref,
                    aclara_parti=:aclara_parti,
                    dispositivo=:dispositivo,
                    especificacion_tec=:especificacion_tec,
                    presentacion=:presentacion,
                    nivel_uso_i=:nivel_uso_i,
                    nivel_uso_ii=:nivel_uso_ii,
                    nivel_uso_iii=:nivel_uso_iii,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_producto;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_producto', $id_producto, PDO::PARAM_STR);
            $res->bindParam(':codigo', $data_producto['codigo'], PDO::PARAM_STR);
            $res->bindParam(':nombre_comercial', $data_producto['nombre_comercial'], PDO::PARAM_STR);
            $res->bindParam(':codigo_liname', $data_producto['codigo_liname']['id_liname'], PDO::PARAM_INT);
            $res->bindParam(':codigo_linadime', $data_producto['codigo_linadime']['id_linadime'], PDO::PARAM_INT);
            $res->bindParam(':reg_san', $data_producto['reg_san'], PDO::PARAM_STR);
            $res->bindParam(':referencia', $data_producto['referencia'], PDO::PARAM_STR);
            $res->bindParam(':medicamento', $data_producto['medicamento'], PDO::PARAM_STR);
            $res->bindParam(':form_farm', $data_producto['form_farm'], PDO::PARAM_STR);
            $res->bindParam(':concen', $data_producto['concen'], PDO::PARAM_STR);
            $res->bindParam(':atq', $data_producto['atq'], PDO::PARAM_STR);
            $res->bindParam(':precio_ref', $data_producto['precio_ref'], PDO::PARAM_STR);
            $res->bindParam(':aclara_parti', $data_producto['aclara_parti'], PDO::PARAM_STR);
            $res->bindParam(':dispositivo', $data_producto['dispositivo'], PDO::PARAM_STR);
            $res->bindParam(':especificacion_tec', $data_producto['especificacion_tec'], PDO::PARAM_STR);
            $res->bindParam(':presentacion', $data_producto['presentacion'], PDO::PARAM_STR);
            $res->bindParam(':nivel_uso_i', $data_producto['nivel_uso_i'], PDO::PARAM_STR);
            $res->bindParam(':nivel_uso_ii', $data_producto['nivel_uso_ii'], PDO::PARAM_STR);
            $res->bindParam(':nivel_uso_iii', $data_producto['nivel_uso_iii'], PDO::PARAM_STR); 
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            //$res = $res->fetchAll(PDO::FETCH_ASSOC);
            if($data_producto['codigo_liname']['id_liname']==null){$data_producto['codigo_liname']=json_decode ("{}");}
            if($data_producto['codigo_linadime']['id_linadime']==null){$data_producto['codigo_linadime']=json_decode ("{}");} 
            if($data_producto['reg_san']==""){$data_producto['reg_san']=null;}
            $resp = array('success'=>true,'message'=>'producto actualizado','data_producto'=>$data_producto);
        }
        return $resp;
    }

    public function changestatusProducto($id_producto,$uuid): array {
        $sql = "UPDATE producto 
                SET activo=0,
                f_inac=now(), 
                u_inac=:u_inac
                WHERE id=:id_producto;";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':u_inac', $uuid, PDO::PARAM_STR);
        $res->bindParam(':id_producto', $id_producto, PDO::PARAM_STR);
        $res->execute();
        //$res = $res->fetchAll(PDO::FETCH_ASSOC);
        if($res->rowCount()==1){
            $resp = array('success'=>true,'message'=>'1 fila afectada');
        }else{
            $resp = array('success'=>false,'message'=>'0 fila afectada');
        }
        return ($resp);

    }

    public function createProducto($data_producto,$uuid): array {
        if(!(isset($data_producto['codigo'])&&isset($data_producto['nombre_comercial'])&&isset($data_producto['codigo_liname'])
        &&isset($data_producto['codigo_linadime'])&&isset($data_producto['reg_san'])&&isset($data_producto['referencia'])
        &&isset($data_producto['medicamento'])&&isset($data_producto['form_farm'])&&isset($data_producto['concen'])
        &&isset($data_producto['atq'])&&isset($data_producto['precio_ref'])&&isset($data_producto['aclara_parti'])
        &&isset($data_producto['dispositivo'])&&isset($data_producto['especificacion_tec'])&&isset($data_producto['presentacion']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        if($data_producto['reg_san']==""){
            $data_producto['reg_san']==null;
            $aux_query=" ";
        }else{
            $aux_query = "OR reg_san LIKE '".$data_producto['reg_san']."'";
        }
        $sql = "SELECT *
                FROM producto
                WHERE codigo LIKE :codigo OR nombre_comercial LIKE :nombre_comercial ".$aux_query.";";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':codigo', $data_producto['codigo'], PDO::PARAM_STR);
        $res->bindParam(':nombre_comercial', $data_producto['nombre_comercial'], PDO::PARAM_STR);
        //$res->bindParam(':reg_san', $data_producto['reg_san'], PDO::PARAM_STR);
        $res->execute();
        if($res->rowCount()==1){
            $resp = array('success'=>false,'message'=>'Error, ya existe un producto con el mismo codigo, nombre comercial o registro sanitario');
        }else{
            $sql = "INSERT INTO producto (
                    id,
                    codigo,
                    nombre_comercial,
                    codigo_liname,
                    codigo_linadime,
                    reg_san,
                    referencia,
                    medicamento,
                    form_farm,
                    concen,
                    atq,
                    precio_ref,
                    aclara_parti,
                    dispositivo,
                    especificacion_tec,
                    presentacion,
                    nivel_uso_i,
                    nivel_uso_ii,
                    nivel_uso_iii,
                    activo,
                    f_crea,
                    u_crea
                    )VALUES(
                    uuid(),
                    :codigo,
                    :nombre_comercial,
                    :codigo_liname,
                    :codigo_linadime,
                    :reg_san,
                    :referencia,
                    :medicamento,
                    :form_farm,
                    :concen,
                    :atq,
                    :precio_ref,
                    :aclara_parti,
                    :dispositivo,
                    :especificacion_tec,
                    :presentacion,
                    :nivel_uso_i,
                    :nivel_uso_ii,
                    :nivel_uso_iii,
                    1,
                    now(),
                    :u_crea
                    );";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':codigo', $data_producto['codigo'], PDO::PARAM_STR);
            $res->bindParam(':nombre_comercial', $data_producto['nombre_comercial'], PDO::PARAM_STR);
            $res->bindParam(':codigo_liname', $data_producto['codigo_liname']['id_liname'], PDO::PARAM_INT);
            $res->bindParam(':codigo_linadime', $data_producto['codigo_linadime']['id_linadime'], PDO::PARAM_INT);
            $res->bindParam(':reg_san', $data_producto['reg_san'], PDO::PARAM_STR);
            $res->bindParam(':referencia', $data_producto['referencia'], PDO::PARAM_STR);
            $res->bindParam(':medicamento', $data_producto['medicamento'], PDO::PARAM_STR);
            $res->bindParam(':form_farm', $data_producto['form_farm'], PDO::PARAM_STR);
            $res->bindParam(':concen', $data_producto['concen'], PDO::PARAM_STR);
            $res->bindParam(':atq', $data_producto['atq'], PDO::PARAM_STR);
            $res->bindParam(':precio_ref', $data_producto['precio_ref'], PDO::PARAM_STR);
            $res->bindParam(':aclara_parti', $data_producto['aclara_parti'], PDO::PARAM_STR);
            $res->bindParam(':dispositivo', $data_producto['dispositivo'], PDO::PARAM_STR);
            $res->bindParam(':especificacion_tec', $data_producto['especificacion_tec'], PDO::PARAM_STR);
            $res->bindParam(':presentacion', $data_producto['presentacion'], PDO::PARAM_STR);
            $res->bindParam(':nivel_uso_i', $data_producto['nivel_uso_i'], PDO::PARAM_STR);
            $res->bindParam(':nivel_uso_ii', $data_producto['nivel_uso_ii'], PDO::PARAM_STR);
            $res->bindParam(':nivel_uso_iii', $data_producto['nivel_uso_iii'], PDO::PARAM_STR);
            $res->bindParam(':u_crea', $uuid, PDO::PARAM_STR);
            $res->execute();
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $sql = "SELECT *
                    FROM producto pr
                    WHERE pr.codigo LIKE :codigo AND pr.activo=1";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':codigo', $data_producto['codigo'], PDO::PARAM_STR);
            $res->execute();
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $res = $res[0];
            $result = array('id'=>$res['id'],
                            'codigo'=>$res['codigo'],
                            'nombre_comercial'=>$res['nombre_comercial'],
                            'codigo_liname'=>$data_producto['codigo_liname'],
                            'codigo_linadime'=>$data_producto['codigo_linadime'],
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
                            'nivel_uso_iii'=>$res['nivel_uso_iii'],
                            'activo'=>$res['activo']);
            if($data_producto['codigo_liname']['id_liname']==null){$result['codigo_liname']=json_decode ("{}");}
            if($data_producto['codigo_linadime']['id_linadime']==null){$result['codigo_linadime']=json_decode ("{}");}
            if($data_producto['reg_san']==null){$result['reg_san']=null;}         
            $resp = array('success'=>true,'message'=>'producto registrado exitosamente','data_producto'=>$result);
        }
        return $resp;
    }
}
