<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Actions\RepositoryConection\Conect;
use App\Domain\EntradaRepository;
use App\Infrastructure\Persistence\DataCorrelativoRepository;
use \PDO;

class DataEntradaRepository implements EntradaRepository {

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
     * DataEntradaRepository constructor.
     *
     */
    public function __construct() {
        $con = new Conect();
        $this->db = $con->getConection();
        $this->dataCorrelativoRepository = new DataCorrelativoRepository;
    }

    public function getEntrada($id_entrada): array {

        $sql = "SELECT en.*, 
                reg.id as reg_id, reg.codigo as reg_codigo, reg.nombre as reg_nombre, reg.direccion as reg_direccion, reg.telefono as reg_telefono,
                alm.id as alm_id, alm.codigo as alm_codigo, alm.nombre as alm_nombre, alm.direccion as alm_direccion, alm.telefono as alm_telefono,
                te.id_param as te_id_param, te.cod_grupo as te_cod_grupo, te.codigo as te_codigo, te.valor as te_valor,
                pr.id as pr_id, pr.codigo as pr_codigo, pr.nombre as pr_nombre, pr.direccion as pr_direccion,
                co.id as co_id, co.codigo as co_codigo, co.nombre as co_nombre, co.gestion as co_gestion, co.descripcion as co_descripcion, co.estado as co_estado,
                ta.id_param as ta_id_param, ta.cod_grupo as ta_cod_grupo, ta.codigo as ta_codigo, ta.valor as ta_valor,
                tf.id_param as tf_id_param, tf.cod_grupo as tf_cod_grupo, tf.codigo as tf_codigo, tf.valor as tf_valor,
                mc.id_param as mc_id_param, mc.cod_grupo as mc_cod_grupo, mc.codigo as mc_codigo, mc.valor as mc_valor,
                FROM (((((((entrada en LEFT JOIN regional reg ON en.id_regional=reg.id) 
                LEFT JOIN almacen alm ON en.id_almacen=alm.id)
                LEFT JOIN param_general te ON en.tipo_entrada=te.codigo AND te.cod_grupo='param_tipo_entrada')
                LEFT JOIN proveedor pr ON en.id_proveedor=pr.id)
                LEFT JOIN compra co ON en.id_compra=co.id)
                LEFT JOIN param_general ta ON en.tipo_adquisicion=ta.codigo AND ta.cod_grupo='param_tipo_adquisicion')
                LEFT JOIN param_general tf ON en.tipo_financiamiento=tf.codigo AND tf.cod_grupo='param_tipo_financiamiento')
                LEFT JOIN param_general mc ON en.modalidad_contratacion=mc.codigo AND mc.cod:grupo='param_modalidad_contr'
                WHERE en.id=:id_entrada AND en.activo=1";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':id_entrada', $id_entrada, PDO::PARAM_STR);
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
            
            $resp = array('success'=>true,'message'=>'Exito','data_entrada'=>$result);
        }else{
            $resp = array('success'=>false,'message'=>'No se encontraron registros');
        }
        return $resp;
    }

    public function listEntrada($query): array {
        if(!(isset($query['filtro'])&&isset($query['limite'])&&isset($query['indice']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        $filtro=$query['filtro'];
        $limite=$query['limite'];
        $indice=$query['indice'];
        $limite=$limite+$indice;
        $filter="%".strtolower($filtro)."%";

        $sql = "SELECT en.id
                FROM (entrada en LEFT JOIN param_liname pl ON en.codigo_liname=pl.id) 
                LEFT JOIN param_linadime pld ON en.codigo_linadime=pld.id
                WHERE en.activo=1 AND (
            LOWER(en.codigo) LIKE LOWER(:filtro) OR LOWER(en.nombre_comercial) LIKE LOWER(:filtro) OR
            LOWER(en.referencia) LIKE LOWER(:filtro) OR LOWER(en.medicamento) LIKE LOWER(:filtro) OR LOWER(en.form_farm) LIKE LOWER(:filtro) OR
            LOWER(en.concen) LIKE LOWER(:filtro) OR LOWER(en.atq) LIKE LOWER(:filtro) OR LOWER(en.precio_ref) LIKE LOWER(:filtro) OR
            LOWER(en.aclara_parti) LIKE LOWER(:filtro) OR LOWER(en.dispositivo) LIKE LOWER(:filtro) OR LOWER(en.especificacion_tec) LIKE LOWER(:filtro) OR
            LOWER(en.presentacion) LIKE LOWER(:filtro) OR LOWER(pl.codigo) LIKE LOWER(:filtro) OR LOWER(pld.codigo) LIKE LOWER(:filtro))";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':filtro', $filter, PDO::PARAM_STR);
        $res->execute();
        $total=$res->rowCount();
        $sql = "SELECT en.*, pl.id as id_liname, pl.codigo as cod_liname, pld.id as id_linadime, pld.codigo as cod_linadime
                FROM (entrada en LEFT JOIN param_liname pl ON en.codigo_liname=pl.id) 
                LEFT JOIN param_linadime pld ON en.codigo_linadime=pld.id
                WHERE en.activo=1 AND (
            LOWER(en.codigo) LIKE LOWER(:filtro) OR LOWER(en.nombre_comercial) LIKE LOWER(:filtro) OR
            LOWER(en.referencia) LIKE LOWER(:filtro) OR LOWER(en.medicamento) LIKE LOWER(:filtro) OR LOWER(en.form_farm) LIKE LOWER(:filtro) OR
            LOWER(en.concen) LIKE LOWER(:filtro) OR LOWER(en.atq) LIKE LOWER(:filtro) OR LOWER(en.precio_ref) LIKE LOWER(:filtro) OR
            LOWER(en.aclara_parti) LIKE LOWER(:filtro) OR LOWER(en.dispositivo) LIKE LOWER(:filtro) OR LOWER(en.especificacion_tec) LIKE LOWER(:filtro) OR
            LOWER(en.presentacion) LIKE LOWER(:filtro) OR LOWER(pl.codigo) LIKE LOWER(:filtro) OR LOWER(pld.codigo) LIKE LOWER(:filtro))
                ORDER BY en.f_crea DESC
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
            $resp = array('success'=>true,'message'=>'Exito','data_entrada'=>$concat);
        }else{
            $resp = array('success'=>false,'message'=>'No se encontraron registros');
        }
        return $resp;
    }

    public function editEntrada($id_entrada,$data_entrada,$uuid): array {
        if(!(isset($id_entrada)&&isset($data_entrada['codigo'])&&isset($data_entrada['nombre_comercial'])&&isset($data_entrada['codigo_liname'])
        &&isset($data_entrada['codigo_linadime'])&&isset($data_entrada['referencia'])
        &&isset($data_entrada['medicamento'])&&isset($data_entrada['form_farm'])&&isset($data_entrada['concen'])
        &&isset($data_entrada['atq'])&&isset($data_entrada['precio_ref'])&&isset($data_entrada['aclara_parti'])
        &&isset($data_entrada['dispositivo'])&&isset($data_entrada['especificacion_tec'])&&isset($data_entrada['presentacion']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        /*if($data_entrada['reg_san']==""){
            $data_entrada['reg_san']==null;
            $aux_query=" ";
        }else{
            $aux_query = "OR reg_san LIKE '".$data_entrada['reg_san']."'";
        }*/
        $sql = "SELECT *
                FROM entrada
                WHERE (codigo LIKE :codigo OR nombre_comercial LIKE :nombre_comercial ) AND id!=:id_entrada";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':codigo', $data_entrada['codigo'], PDO::PARAM_STR);
            $res->bindParam(':id_entrada', $id_entrada, PDO::PARAM_STR);
            $res->bindParam(':nombre_comercial', $data_entrada['nombre_comercial'], PDO::PARAM_STR);
            //$res->bindParam(':reg_san', $data_entrada['reg_san'], PDO::PARAM_STR);
            $res->execute();
        if($res->rowCount()>0){
            $resp = array('success'=>false,'message'=>'Error, el nombre comercial o codigo del entrada ya existe en otro registro');
        }else{
            $sql = "UPDATE entrada 
                    SET codigo=:codigo,
                    nombre_comercial=:nombre_comercial,
                    codigo_liname=:codigo_liname,
                    codigo_linadime=:codigo_linadime,
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
                    WHERE id=:id_entrada;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_entrada', $id_entrada, PDO::PARAM_STR);
            $res->bindParam(':codigo', $data_entrada['codigo'], PDO::PARAM_STR);
            $res->bindParam(':nombre_comercial', $data_entrada['nombre_comercial'], PDO::PARAM_STR);
            $res->bindParam(':codigo_liname', $data_entrada['codigo_liname']['id_liname'], PDO::PARAM_INT);
            $res->bindParam(':codigo_linadime', $data_entrada['codigo_linadime']['id_linadime'], PDO::PARAM_INT);
            $res->bindParam(':referencia', $data_entrada['referencia'], PDO::PARAM_STR);
            $res->bindParam(':medicamento', $data_entrada['medicamento'], PDO::PARAM_STR);
            $res->bindParam(':form_farm', $data_entrada['form_farm'], PDO::PARAM_STR);
            $res->bindParam(':concen', $data_entrada['concen'], PDO::PARAM_STR);
            $res->bindParam(':atq', $data_entrada['atq'], PDO::PARAM_STR);
            $res->bindParam(':precio_ref', $data_entrada['precio_ref'], PDO::PARAM_STR);
            $res->bindParam(':aclara_parti', $data_entrada['aclara_parti'], PDO::PARAM_STR);
            $res->bindParam(':dispositivo', $data_entrada['dispositivo'], PDO::PARAM_STR);
            $res->bindParam(':especificacion_tec', $data_entrada['especificacion_tec'], PDO::PARAM_STR);
            $res->bindParam(':presentacion', $data_entrada['presentacion'], PDO::PARAM_STR);
            $res->bindParam(':nivel_uso_i', $data_entrada['nivel_uso_i'], PDO::PARAM_STR);
            $res->bindParam(':nivel_uso_ii', $data_entrada['nivel_uso_ii'], PDO::PARAM_STR);
            $res->bindParam(':nivel_uso_iii', $data_entrada['nivel_uso_iii'], PDO::PARAM_STR); 
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            //$res = $res->fetchAll(PDO::FETCH_ASSOC);
            if($data_entrada['codigo_liname']['id_liname']==null){$data_entrada['codigo_liname']=json_decode ("{}");}
            if($data_entrada['codigo_linadime']['id_linadime']==null){$data_entrada['codigo_linadime']=json_decode ("{}");} 
            $resp = array('success'=>true,'message'=>'entrada actualizado','data_entrada'=>$data_entrada);
        }
        return $resp;
    }

    public function changestatusEntrada($id_entrada,$uuid): array {
        $sql = "UPDATE entrada 
                SET activo=0,
                f_inac=now(), 
                u_inac=:u_inac
                WHERE id=:id_entrada;";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':u_inac', $uuid, PDO::PARAM_STR);
        $res->bindParam(':id_entrada', $id_entrada, PDO::PARAM_STR);
        $res->execute();
        //$res = $res->fetchAll(PDO::FETCH_ASSOC);
        if($res->rowCount()==1){
            $resp = array('success'=>true,'message'=>'1 fila afectada');
        }else{
            $resp = array('success'=>false,'message'=>'0 fila afectada');
        }
        return ($resp);
    }

    public function createEntrada($data_entrada,$uuid): array {
        if(!(isset($data_entrada['id_regional'])&&isset($data_entrada['id_almacen'])
        &&isset($data_entrada['tipo_entrada'])&&isset($data_entrada['id_proveedor'])
        &&isset($data_entrada['id_compra'])&&isset($data_entrada['tipo_adquisicion'])&&isset($data_entrada['tipo_financiamiento'])
        &&isset($data_entrada['factura_comercial'])&&isset($data_entrada['c_31'])&&isset($data_entrada['modalidad_contratacion'])
        &&isset($data_entrada['cite_contrato_compra'])&&isset($data_entrada['nota'])&&isset($data_entrada['comision']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        $correlativo = $this->dataCorrelativoRepository->genCorrelativo($data_entrada['id_almacen']['codigo'].'-IN', $data_entrada['tipo_entrada']['codigo'], $uuid);
        $correlativo = $correlativo['correlativo'];
        $correlativo = $data_entrada['id_almacen']['codigo'] . '-IN-' . $correlativo . $data_entrada['tipo_entrada']['codigo'];
        $sql = "INSERT INTO entrada (
                id,
                codigo,
                id_regional,
                id_almacen,
                tipo_entrada,
                id_proveedor,
                id_compra,
                tipo_adquisicion,
                tipo_financiamiento,
                factura_comercial,
                c_31,
                modalidad_contratacion,
                cite_contrato_compra,
                nota,
                comision,
                estado,
                activo,
                f_crea,
                u_crea
                )VALUES(
                uuid(),
                :codigo,
                :id_regional,
                :id_almacen,
                :tipo_entrada,
                :id_proveedor,
                :id_compra,
                :tipo_adquisicion,
                :tipo_financiamiento,
                :factura_comercial,
                :c_31,
                :modalidad_contratacion,
                :cite_contrato_compra,
                :nota,
                :comision,
                'PENDIENTE',
                1,
                now(),
                :u_crea
                );";
        $data_entrada['comision']=json_encode($data_entrada['comision']);
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':codigo', $correlativo, PDO::PARAM_STR);
        $res->bindParam(':id_regional', $data_entrada['id_regional']['id'], PDO::PARAM_STR);
        $res->bindParam(':id_almacen', $data_entrada['id_almacen']['id'], PDO::PARAM_STR);
        $res->bindParam(':tipo_entrada', $data_entrada['tipo_entrada']['codigo'], PDO::PARAM_STR);
        $res->bindParam(':id_proveedor', $data_entrada['id_proveedor']['id'], PDO::PARAM_STR);
        $res->bindParam(':id_compra', $data_entrada['id_compra']['id'], PDO::PARAM_STR);
        $res->bindParam(':tipo_adquisicion', $data_entrada['tipo_adquisicion']['codigo'], PDO::PARAM_STR);
        $res->bindParam(':tipo_financiamiento', $data_entrada['tipo_financiamiento']['codigo'], PDO::PARAM_STR);
        $res->bindParam(':factura_comercial', $data_entrada['factura_comercial'], PDO::PARAM_STR);
        $res->bindParam(':c_31', $data_entrada['c_31'], PDO::PARAM_STR);
        $res->bindParam(':modalidad_contratacion', $data_entrada['modalidad_contratacion']['codigo'], PDO::PARAM_STR);
        $res->bindParam(':cite_contrato_compra', $data_entrada['cite_contrato_compra'], PDO::PARAM_STR);
        $res->bindParam(':nota', $data_entrada['nota'], PDO::PARAM_STR);
        $res->bindParam(':comision',$data_entrada['comision'], PDO::PARAM_STR);
        $res->bindParam(':u_crea', $uuid, PDO::PARAM_STR);
        $res->execute();
        $res = $res->fetchAll(PDO::FETCH_ASSOC);
        $sql = "SELECT *
                FROM entrada
                WHERE codigo LIKE :codigo AND activo=1";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':codigo', $correlativo, PDO::PARAM_STR);
        $res->execute();
        $res = $res->fetchAll(PDO::FETCH_ASSOC);
        $res = $res[0];
        $result = array('id'=>$res['id'],
                        'codigo'=>$res['codigo'],
                        'id_regional'=>$data_entrada['id_regional'],
                        'id_almacen'=>$data_entrada['id_almacen'],
                        'tipo_entrada'=>$data_entrada['tipo_entrada'],
                        'id_proveedor'=>$data_entrada['id_proveedor'],
                        'id_compra'=>$data_entrada['id_compra'],
                        'tipo_adquisicion'=>$data_entrada['tipo_adquisicion'],
                        'tipo_financiamiento'=>$data_entrada['tipo_financiamiento'],
                        'factura_comercial'=>$res['factura_comercial'],
                        'c_31'=>$res['c_31'],
                        'modalidad_contratacion'=>$data_entrada['modalidad_contratacion'],
                        'cite_contrato_compra'=>$res['cite_contrato_compra'],
                        'nota'=>$res['nota'],
                        'comision'=>json_decode($res['comision']),
                        'activo'=>$res['activo']);
        if($data_entrada['id_regional']['id']==null){$result['id_regional']=json_decode("{}");}
        if($data_entrada['id_almacen']['id']==null){$result['id_almacen']=json_decode("{}");}
        if($data_entrada['tipo_entrada']['codigo']==null){$result['tipo_entrada']=json_decode("{}");}
        if($data_entrada['id_proveedor']['id']==null){$result['id_proveedor']=json_decode("{}");}  
        if($data_entrada['id_compra']['id']==null){$result['id_compra']=json_decode("{}");}
        if($data_entrada['tipo_adquisicion']['codigo']==null){$result['tipo_adquisicion']=json_decode("{}");} 
        if($data_entrada['tipo_financiamiento']['codigo']==null){$result['tipo_financiamiento']=json_decode("{}");}
        if($data_entrada['modalidad_contratacion']['codigo']==null){$result['modalidad_contratacion']=json_decode("{}");}  
        $resp = array('success'=>true,'message'=>'entrada registrada exitosamente','data_entrada'=>$result);
        return $resp;
    }

    public function modifyEntrada($id_entrada,$data_entrada,$uuid): array {
        $success=true;
        $resp=array();

        if(isset($data_entrada['codigo'])){
            $sql = "SELECT *
                    FROM entrada
                    WHERE codigo=:codigo AND id!=:id_pro$id_entrada";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':codigo', $data_entrada['codigo'], PDO::PARAM_STR);
            $res->bindParam(':id_entrada', $id_entrada, PDO::PARAM_STR);
            $res->execute();
            if($res->rowCount()>0){
                $success=false;
                $resp += ['codigo' => 'error, ya existe registro'];
            }else{
                $sql = "UPDATE entrada 
                        SET codigo=:codigo,
                        f_mod=now(), 
                        u_mod=:u_mod
                        WHERE id=:id_pro$id_entrada;";
                $res = ($this->db)->prepare($sql);
                $res->bindParam(':id_pro$id_entrada', $id_entrada, PDO::PARAM_STR);
                $res->bindParam(':codigo', $data_entrada['codigo'], PDO::PARAM_STR);
                $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
                $res->execute();
                $resp += ['codigo' => 'dato actualizado'];
            }

            if(isset($data_entrada['nombre_comercial'])){
                $sql = "UPDATE entrada 
                            SET nombre_comercial=:nombre_comercial,
                            f_mod=now(), 
                            u_mod=:u_mod
                            WHERE id=:id_entrada;";
                    $res = ($this->db)->prepare($sql);
                    $res->bindParam(':id_entrada', $id_entrada, PDO::PARAM_STR);
                    $res->bindParam(':nombre_comercial', $data_entrada['nombre_comercial'], PDO::PARAM_STR);
                    $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
                    $res->execute();
                    $resp += ['nombre_comercial' => 'dato actualizado'];
            }

            if(isset($data_entrada['codigo_liname'])){
                $sql = "UPDATE entrada 
                            SET codigo_liname=:codigo_liname,
                            f_mod=now(), 
                            u_mod=:u_mod
                            WHERE id=:id_entrada;";
                    $res = ($this->db)->prepare($sql);
                    $res->bindParam(':id_entrada', $id_entrada, PDO::PARAM_STR);
                    $res->bindParam(':codigo_liname', $data_entrada['codigo_liname'], PDO::PARAM_STR);
                    $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
                    $res->execute();
                    $resp += ['codigo_liname' => 'dato actualizado'];
            }

            if(isset($data_entrada['codigo_linadime'])){
                $sql = "UPDATE entrada 
                            SET codigo_linadime=:codigo_linadime,
                            f_mod=now(), 
                            u_mod=:u_mod
                            WHERE id=:id_entrada;";
                    $res = ($this->db)->prepare($sql);
                    $res->bindParam(':id_entrada', $id_entrada, PDO::PARAM_STR);
                    $res->bindParam(':codigo_linadime', $data_entrada['codigo_linadime'], PDO::PARAM_STR);
                    $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
                    $res->execute();
                    $resp += ['codigo_linadime' => 'dato actualizado'];
            }

            if(isset($data_entrada['referencia'])){
                $sql = "UPDATE entrada 
                            SET referencia=:referencia,
                            f_mod=now(), 
                            u_mod=:u_mod
                            WHERE id=:id_entrada;";
                    $res = ($this->db)->prepare($sql);
                    $res->bindParam(':id_entrada', $id_entrada, PDO::PARAM_STR);
                    $res->bindParam(':referencia', $data_entrada['referencia'], PDO::PARAM_STR);
                    $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
                    $res->execute();
                    $resp += ['referencia' => 'dato actualizado'];
            }

            if(isset($data_entrada['medicamento'])){
                $sql = "UPDATE entrada 
                            SET medicamento=:medicamento,
                            f_mod=now(), 
                            u_mod=:u_mod
                            WHERE id=:id_entrada;";
                    $res = ($this->db)->prepare($sql);
                    $res->bindParam(':id_entrada', $id_entrada, PDO::PARAM_STR);
                    $res->bindParam(':medicamento', $data_entrada['medicamento'], PDO::PARAM_STR);
                    $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
                    $res->execute();
                    $resp += ['medicamento' => 'dato actualizado'];
            }

            if(isset($data_entrada['form_farm'])){
                $sql = "UPDATE entrada 
                            SET form_farm=:form_farm,
                            f_mod=now(), 
                            u_mod=:u_mod
                            WHERE id=:id_entrada;";
                    $res = ($this->db)->prepare($sql);
                    $res->bindParam(':id_entrada', $id_entrada, PDO::PARAM_STR);
                    $res->bindParam(':form_farm', $data_entrada['form_farm'], PDO::PARAM_STR);
                    $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
                    $res->execute();
                    $resp += ['form_farm' => 'dato actualizado'];
            }

            if(isset($data_entrada['concen'])){
                $sql = "UPDATE entrada 
                            SET concen=:concen,
                            f_mod=now(), 
                            u_mod=:u_mod
                            WHERE id=:id_entrada;";
                    $res = ($this->db)->prepare($sql);
                    $res->bindParam(':id_entrada', $id_entrada, PDO::PARAM_STR);
                    $res->bindParam(':concen', $data_entrada['concen'], PDO::PARAM_STR);
                    $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
                    $res->execute();
                    $resp += ['concen' => 'dato actualizado'];
            }

            if(isset($data_entrada['atq'])){
                $sql = "UPDATE entrada 
                            SET atq=:atq,
                            f_mod=now(), 
                            u_mod=:u_mod
                            WHERE id=:id_entrada;";
                    $res = ($this->db)->prepare($sql);
                    $res->bindParam(':id_entrada', $id_entrada, PDO::PARAM_STR);
                    $res->bindParam(':atq', $data_entrada['atq'], PDO::PARAM_STR);
                    $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
                    $res->execute();
                    $resp += ['atq' => 'dato actualizado'];
            }

            if(isset($data_entrada['precio_ref'])){
                $sql = "UPDATE entrada 
                            SET precio_ref=:precio_ref,
                            f_mod=now(), 
                            u_mod=:u_mod
                            WHERE id=:id_entrada;";
                    $res = ($this->db)->prepare($sql);
                    $res->bindParam(':id_entrada', $id_entrada, PDO::PARAM_STR);
                    $res->bindParam(':precio_ref', $data_entrada['precio_ref'], PDO::PARAM_STR);
                    $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
                    $res->execute();
                    $resp += ['precio_ref' => 'dato actualizado'];
            }

            if(isset($data_entrada['aclara_parti'])){
                $sql = "UPDATE entrada 
                            SET aclara_parti=:aclara_parti,
                            f_mod=now(), 
                            u_mod=:u_mod
                            WHERE id=:id_entrada;";
                    $res = ($this->db)->prepare($sql);
                    $res->bindParam(':id_entrada', $id_entrada, PDO::PARAM_STR);
                    $res->bindParam(':aclara_parti', $data_entrada['aclara_parti'], PDO::PARAM_STR);
                    $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
                    $res->execute();
                    $resp += ['aclara_parti' => 'dato actualizado'];
            }

            if(isset($data_entrada['dispositivo'])){
                $sql = "UPDATE entrada 
                            SET dispositivo=:dispositivo,
                            f_mod=now(), 
                            u_mod=:u_mod
                            WHERE id=:id_entrada;";
                    $res = ($this->db)->prepare($sql);
                    $res->bindParam(':id_entrada', $id_entrada, PDO::PARAM_STR);
                    $res->bindParam(':dispositivo', $data_entrada['dispositivo'], PDO::PARAM_STR);
                    $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
                    $res->execute();
                    $resp += ['dispositivo' => 'dato actualizado'];
            }

            if(isset($data_entrada['especificacion_tec'])){
                $sql = "UPDATE entrada 
                            SET especificacion_tec=:especificacion_tec,
                            f_mod=now(), 
                            u_mod=:u_mod
                            WHERE id=:id_entrada;";
                    $res = ($this->db)->prepare($sql);
                    $res->bindParam(':id_entrada', $id_entrada, PDO::PARAM_STR);
                    $res->bindParam(':especificacion_tec', $data_entrada['especificacion_tec'], PDO::PARAM_STR);
                    $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
                    $res->execute();
                    $resp += ['especificacion_tec' => 'dato actualizado'];
            }

            if(isset($data_entrada['presentacion'])){
                $sql = "UPDATE entrada 
                            SET presentacion=:presentacion,
                            f_mod=now(), 
                            u_mod=:u_mod
                            WHERE id=:id_entrada;";
                    $res = ($this->db)->prepare($sql);
                    $res->bindParam(':id_entrada', $id_entrada, PDO::PARAM_STR);
                    $res->bindParam(':presentacion', $data_entrada['presentacion'], PDO::PARAM_STR);
                    $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
                    $res->execute();
                    $resp += ['presentacion' => 'dato actualizado'];
            }

            if(isset($data_entrada['nivel_uso_i'])){
                $sql = "UPDATE entrada 
                            SET nivel_uso_i=:nivel_uso_i,
                            f_mod=now(), 
                            u_mod=:u_mod
                            WHERE id=:id_entrada;";
                    $res = ($this->db)->prepare($sql);
                    $res->bindParam(':id_entrada', $id_entrada, PDO::PARAM_STR);
                    $res->bindParam(':nivel_uso_i', $data_entrada['nivel_uso_i'], PDO::PARAM_STR);
                    $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
                    $res->execute();
                    $resp += ['nivel_uso_i' => 'dato actualizado'];
            }
        }

        if(isset($data_entrada['nivel_uso_ii'])){
            $sql = "UPDATE entrada 
                        SET nivel_uso_ii=:nivel_uso_ii,
                        f_mod=now(), 
                        u_mod=:u_mod
                        WHERE id=:id_entrada;";
                $res = ($this->db)->prepare($sql);
                $res->bindParam(':id_entrada', $id_entrada, PDO::PARAM_STR);
                $res->bindParam(':nivel_uso_ii', $data_entrada['nivel_uso_ii'], PDO::PARAM_STR);
                $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
                $res->execute();
                $resp += ['nivel_uso_ii' => 'dato actualizado'];
        }

        if(isset($data_entrada['nivel_uso_iii'])){
            $sql = "UPDATE entrada 
                        SET nivel_uso_iii=:nivel_uso_iii,
                        f_mod=now(), 
                        u_mod=:u_mod
                        WHERE id=:id_entrada;";
                $res = ($this->db)->prepare($sql);
                $res->bindParam(':id_entrada', $id_entrada, PDO::PARAM_STR);
                $res->bindParam(':nivel_uso_iii', $data_entrada['nivel_uso_iii'], PDO::PARAM_STR);
                $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
                $res->execute();
                $resp += ['nivel_uso_iii' => 'dato actualizado'];
        }

        $resp = array('success'=>$success,'message'=>'datos actualizados','data_entrada'=>$resp);
        return $resp;
    }
}
