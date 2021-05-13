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
                mc.id_param as mc_id_param, mc.cod_grupo as mc_cod_grupo, mc.codigo as mc_codigo, mc.valor as mc_valor
                FROM (((((((entrada en LEFT JOIN regional reg ON en.id_regional=reg.id) 
                LEFT JOIN almacen alm ON en.id_almacen=alm.id)
                LEFT JOIN param_general te ON (en.tipo_entrada=te.codigo AND te.cod_grupo LIKE 'param_tipo_entrada'))
                LEFT JOIN proveedor pr ON en.id_proveedor=pr.id)
                LEFT JOIN compra co ON en.id_compra=co.id)
                LEFT JOIN param_general ta ON (en.tipo_adquisicion=ta.codigo AND ta.cod_grupo LIKE 'param_tipo_adquisicion'))
                LEFT JOIN param_general tf ON (en.tipo_financiamiento=tf.codigo AND tf.cod_grupo LIKE 'param_tipo_financiamiento'))
                LEFT JOIN param_general mc ON (en.modalidad_contratacion=mc.codigo AND mc.cod_grupo LIKE 'param_modalidad_contr')
                WHERE en.id=:id_entrada AND en.activo=1";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':id_entrada', $id_entrada, PDO::PARAM_STR);
        $res->execute();
        if($res->rowCount()>0){
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $res = $res[0];
            $result = array('id'=>$res['id'],
                            'codigo'=>$res['codigo'],
                            'id_regional'=>array(
                                'id'=>$res['reg_id'],
                                'codigo'=>$res['reg_codigo'],
                                'nombre'=>$res['reg_nombre'],
                                'direccion'=>$res['reg_direccion'],
                                'telefono'=>$res['reg_telefono']
                            ),
                            'id_almacen'=>array(
                                'id'=>$res['alm_id'],
                                'codigo'=>$res['alm_codigo'],
                                'nombre'=>$res['alm_nombre'],
                                'direccion'=>$res['alm_direccion'],
                                'telefono'=>$res['alm_telefono']
                            ),
                            'tipo_entrada'=>array(
                                'id_param'=>$res['te_id_param'],
                                'cod_grupo'=>$res['te_cod_grupo'],
                                'codigo'=>$res['te_codigo'],
                                'valor'=>$res['te_valor']
                            ),
                            'id_proveedor'=>array(
                                'id'=>$res['pr_id'],
                                'codigo'=>$res['pr_codigo'],
                                'nombre'=>$res['pr_nombre'],
                                'direccion'=>$res['pr_direccion']
                            ),
                            'id_compra'=>array(
                                'id'=>$res['co_id'],
                                'codigo'=>$res['co_codigo'],
                                'nombre'=>$res['co_nombre'],
                                'gestion'=>$res['co_gestion'],
                                'descripcion'=>$res['co_descripcion'],
                                'estado'=>$res['co_estado']
                            ),
                            'tipo_adquisicion'=>array(
                                'id_param'=>$res['ta_id_param'],
                                'cod_grupo'=>$res['ta_cod_grupo'],
                                'codigo'=>$res['ta_codigo'],
                                'valor'=>$res['ta_valor']
                            ),
                            'tipo_financiamiento'=>array(
                                'id_param'=>$res['tf_id_param'],
                                'cod_grupo'=>$res['tf_cod_grupo'],
                                'codigo'=>$res['tf_codigo'],
                                'valor'=>$res['tf_valor']
                            ),
                            'factura_comercial'=>$res['factura_comercial'],
                            'c_31'=>$res['c_31'],
                            'modalidad_contratacion'=>array(
                                'id_param'=>$res['mc_id_param'],
                                'cod_grupo'=>$res['mc_cod_grupo'],
                                'codigo'=>$res['mc_codigo'],
                                'valor'=>$res['mc_valor']
                            ),
                            'cite_contrato_compra'=>$res['cite_contrato_compra'],
                            'nota'=>$res['nota'],
                            'comision'=>json_decode($res['comision']),
                            'estado'=>$res['estado'],
                            'activo'=>$res['activo']);
            if($result['id_regional']['id']==null){$result['id_regional']=json_decode("{}");}
            if($result['id_almacen']['id']==null){$result['id_almacen']=json_decode("{}");}
            if($result['tipo_entrada']['codigo']==null){$result['tipo_entrada']=json_decode("{}");}
            if($result['id_proveedor']['id']==null){$result['id_proveedor']=json_decode("{}");}  
            if($result['id_compra']['id']==null){$result['id_compra']=json_decode("{}");}
            if($result['tipo_adquisicion']['codigo']==null){$result['tipo_adquisicion']=json_decode("{}");} 
            if($result['tipo_financiamiento']['codigo']==null){$result['tipo_financiamiento']=json_decode("{}");}
            if($result['modalidad_contratacion']['codigo']==null){$result['modalidad_contratacion']=json_decode("{}");}  
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

        $sql = "SELECT en.*
                FROM (((((((entrada en LEFT JOIN regional reg ON en.id_regional=reg.id) 
                LEFT JOIN almacen alm ON en.id_almacen=alm.id)
                LEFT JOIN param_general te ON (en.tipo_entrada=te.codigo AND te.cod_grupo LIKE 'param_tipo_entrada'))
                LEFT JOIN proveedor pr ON en.id_proveedor=pr.id)
                LEFT JOIN compra co ON en.id_compra=co.id)
                LEFT JOIN param_general ta ON (en.tipo_adquisicion=ta.codigo AND ta.cod_grupo LIKE 'param_tipo_adquisicion'))
                LEFT JOIN param_general tf ON (en.tipo_financiamiento=tf.codigo AND tf.cod_grupo LIKE 'param_tipo_financiamiento'))
                LEFT JOIN param_general mc ON (en.modalidad_contratacion=mc.codigo AND mc.cod_grupo LIKE 'param_modalidad_contr')
                WHERE en.activo=1 AND (
                LOWER(en.codigo) LIKE LOWER(:filtro) OR LOWER(en.factura_comercial) LIKE LOWER(:filtro) OR
                LOWER(en.c_31) LIKE LOWER(:filtro) OR LOWER(en.cite_contrato_compra) LIKE LOWER(:filtro) OR LOWER(en.nota) LIKE LOWER(:filtro) OR
                LOWER(en.comision) LIKE LOWER(:filtro) OR LOWER(en.estado) LIKE LOWER(:filtro) OR
                LOWER(reg.nombre) LIKE LOWER(:filtro) OR LOWER(alm.nombre) LIKE LOWER(:filtro) OR LOWER(te.valor) LIKE LOWER(:filtro) OR
                LOWER(pr.nombre) LIKE LOWER(:filtro) OR LOWER(co.codigo) LIKE LOWER(:filtro) OR LOWER(co.nombre) LIKE LOWER(:filtro)OR
                LOWER(ta.valor) LIKE LOWER(:filtro) OR LOWER(tf.valor) LIKE LOWER(:filtro) OR LOWER(mc.valor) LIKE LOWER(:filtro) )";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':filtro', $filter, PDO::PARAM_STR);
        $res->execute();
        $total=$res->rowCount();
        $sql = "SELECT en.*, 
                reg.id as reg_id, reg.codigo as reg_codigo, reg.nombre as reg_nombre, reg.direccion as reg_direccion, reg.telefono as reg_telefono,
                alm.id as alm_id, alm.codigo as alm_codigo, alm.nombre as alm_nombre, alm.direccion as alm_direccion, alm.telefono as alm_telefono,
                te.id_param as te_id_param, te.cod_grupo as te_cod_grupo, te.codigo as te_codigo, te.valor as te_valor,
                pr.id as pr_id, pr.codigo as pr_codigo, pr.nombre as pr_nombre, pr.direccion as pr_direccion,
                co.id as co_id, co.codigo as co_codigo, co.nombre as co_nombre, co.gestion as co_gestion, co.descripcion as co_descripcion, co.estado as co_estado,
                ta.id_param as ta_id_param, ta.cod_grupo as ta_cod_grupo, ta.codigo as ta_codigo, ta.valor as ta_valor,
                tf.id_param as tf_id_param, tf.cod_grupo as tf_cod_grupo, tf.codigo as tf_codigo, tf.valor as tf_valor,
                mc.id_param as mc_id_param, mc.cod_grupo as mc_cod_grupo, mc.codigo as mc_codigo, mc.valor as mc_valor
                FROM (((((((entrada en LEFT JOIN regional reg ON en.id_regional=reg.id) 
                LEFT JOIN almacen alm ON en.id_almacen=alm.id)
                LEFT JOIN param_general te ON (en.tipo_entrada=te.codigo AND te.cod_grupo LIKE 'param_tipo_entrada'))
                LEFT JOIN proveedor pr ON en.id_proveedor=pr.id)
                LEFT JOIN compra co ON en.id_compra=co.id)
                LEFT JOIN param_general ta ON (en.tipo_adquisicion=ta.codigo AND ta.cod_grupo LIKE 'param_tipo_adquisicion'))
                LEFT JOIN param_general tf ON (en.tipo_financiamiento=tf.codigo AND tf.cod_grupo LIKE 'param_tipo_financiamiento'))
                LEFT JOIN param_general mc ON (en.modalidad_contratacion=mc.codigo AND mc.cod_grupo LIKE 'param_modalidad_contr')
                WHERE en.activo=1 AND (
                LOWER(en.codigo) LIKE LOWER(:filtro) OR LOWER(en.factura_comercial) LIKE LOWER(:filtro) OR
                LOWER(en.c_31) LIKE LOWER(:filtro) OR LOWER(en.cite_contrato_compra) LIKE LOWER(:filtro) OR LOWER(en.nota) LIKE LOWER(:filtro) OR
                LOWER(en.comision) LIKE LOWER(:filtro) OR LOWER(en.estado) LIKE LOWER(:filtro) OR
                LOWER(reg.nombre) LIKE LOWER(:filtro) OR LOWER(alm.nombre) LIKE LOWER(:filtro) OR LOWER(te.valor) LIKE LOWER(:filtro) OR
                LOWER(pr.nombre) LIKE LOWER(:filtro) OR LOWER(co.codigo) LIKE LOWER(:filtro) OR LOWER(co.nombre) LIKE LOWER(:filtro)OR
                LOWER(ta.valor) LIKE LOWER(:filtro) OR LOWER(tf.valor) LIKE LOWER(:filtro) OR LOWER(mc.valor) LIKE LOWER(:filtro) )
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
                                'id_regional'=>array(
                                    'id'=>$res['reg_id'],
                                    'codigo'=>$res['reg_codigo'],
                                    'nombre'=>$res['reg_nombre'],
                                    'direccion'=>$res['reg_direccion'],
                                    'telefono'=>$res['reg_telefono']
                                ),
                                'id_almacen'=>array(
                                    'id'=>$res['alm_id'],
                                    'codigo'=>$res['alm_codigo'],
                                    'nombre'=>$res['alm_nombre'],
                                    'direccion'=>$res['alm_direccion'],
                                    'telefono'=>$res['alm_telefono']
                                ),
                                'tipo_entrada'=>array(
                                    'id_param'=>$res['te_id_param'],
                                    'cod_grupo'=>$res['te_cod_grupo'],
                                    'codigo'=>$res['te_codigo'],
                                    'valor'=>$res['te_valor']
                                ),
                                'id_proveedor'=>array(
                                    'id'=>$res['pr_id'],
                                    'codigo'=>$res['pr_codigo'],
                                    'nombre'=>$res['pr_nombre'],
                                    'direccion'=>$res['pr_direccion']
                                ),
                                'id_compra'=>array(
                                    'id'=>$res['co_id'],
                                    'codigo'=>$res['co_codigo'],
                                    'nombre'=>$res['co_nombre'],
                                    'gestion'=>$res['co_gestion'],
                                    'descripcion'=>$res['co_descripcion'],
                                    'estado'=>$res['co_estado']
                                ),
                                'tipo_adquisicion'=>array(
                                    'id_param'=>$res['ta_id_param'],
                                    'cod_grupo'=>$res['ta_cod_grupo'],
                                    'codigo'=>$res['ta_codigo'],
                                    'valor'=>$res['ta_valor']
                                ),
                                'tipo_financiamiento'=>array(
                                    'id_param'=>$res['tf_id_param'],
                                    'cod_grupo'=>$res['tf_cod_grupo'],
                                    'codigo'=>$res['tf_codigo'],
                                    'valor'=>$res['tf_valor']
                                ),
                                'factura_comercial'=>$res['factura_comercial'],
                                'c_31'=>$res['c_31'],
                                'modalidad_contratacion'=>array(
                                    'id_param'=>$res['mc_id_param'],
                                    'cod_grupo'=>$res['mc_cod_grupo'],
                                    'codigo'=>$res['mc_codigo'],
                                    'valor'=>$res['mc_valor']
                                ),
                                'cite_contrato_compra'=>$res['cite_contrato_compra'],
                                'nota'=>$res['nota'],
                                'comision'=>json_decode($res['comision']),
                                'estado'=>$res['estado'],
                                'activo'=>$res['activo']);
                if($result['id_regional']['id']==null){$result['id_regional']=json_decode("{}");}
                if($result['id_almacen']['id']==null){$result['id_almacen']=json_decode("{}");}
                if($result['tipo_entrada']['codigo']==null){$result['tipo_entrada']=json_decode("{}");}
                if($result['id_proveedor']['id']==null){$result['id_proveedor']=json_decode("{}");}  
                if($result['id_compra']['id']==null){$result['id_compra']=json_decode("{}");}
                if($result['tipo_adquisicion']['codigo']==null){$result['tipo_adquisicion']=json_decode("{}");} 
                if($result['tipo_financiamiento']['codigo']==null){$result['tipo_financiamiento']=json_decode("{}");}
                if($result['modalidad_contratacion']['codigo']==null){$result['modalidad_contratacion']=json_decode("{}");}  
                array_push($arrayres,$result);
            }
            $concat=array('resultados'=>$arrayres,'total'=>$total);
            $resp = array('success'=>true,'message'=>'Exito','data_entrada'=>$concat);
        }else{
            $concat=array('resultados'=>array(),'total'=>0);
            $resp = array('success'=>true,'message'=>'No se encontraron registros', 'data_entrada'=>$concat);
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
        $correlativo = $data_entrada['id_almacen']['codigo'] . '-IN-' . $correlativo .'-'. $data_entrada['tipo_entrada']['codigo'];
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
        $codigo=false;
        $resp=array();

        if(isset($data_entrada['id_regional'])){
            $sql = "UPDATE entrada 
                    SET id_regional=:id_regional,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_entrada;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_entrada', $id_entrada, PDO::PARAM_STR);
            $res->bindParam(':id_regional', $data_entrada['id_regional']['id'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            $resp += ['id_regional' => 'dato actualizado'];
        }

        if(isset($data_entrada['id_almacen'])){
            
            $sql = "SELECT alm.codigo as cod_almacen, e.tipo_entrada
                    FROM entrada e, almacen alm
                    WHERE e.id=:id_entrada AND alm.id=:id_almacen";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_entrada', $id_entrada, PDO::PARAM_STR);
            $res->bindParam(':id_almacen', $data_entrada['id_almacen']['id'], PDO::PARAM_STR);
            $res->execute();
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $res = $res[0];

            $correlativo = $this->dataCorrelativoRepository->genCorrelativo($res['cod_almacen'].'-IN', $res['tipo_entrada'], $uuid);
            $correlativo = $correlativo['correlativo'];
            $correlativo = $res['cod_almacen'] . '-IN-' . $correlativo .'-'. $res['tipo_entrada'];
            $sql = "UPDATE entrada 
                    SET id_almacen=:id_almacen,
                    codigo=:codigo,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_entrada;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_entrada', $id_entrada, PDO::PARAM_STR);
            $res->bindParam(':id_almacen', $data_entrada['id_almacen']['id'], PDO::PARAM_STR);
            $res->bindParam(':codigo', $correlativo, PDO::PARAM_STR);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            $resp += ['id_almacen' => 'dato actualizado'];
            $codigo=true;
        }

        if(isset($data_entrada['tipo_entrada'])){
            $sql = "SELECT alm.codigo as cod_almacen, e.tipo_entrada
                    FROM entrada e, almacen alm
                    WHERE e.id=:id_entrada AND e.id_almacen=alm.id;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_entrada', $id_entrada, PDO::PARAM_STR);
            $res->execute();
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $res = $res[0];
            $correlativo = $this->dataCorrelativoRepository->genCorrelativo($res['cod_almacen'].'-IN', $data_entrada['tipo_entrada']['codigo'], $uuid);
            $correlativo = $correlativo['correlativo'];
            $correlativo = $res['cod_almacen'] . '-IN-' . $correlativo .'-'. $data_entrada['tipo_entrada']['codigo'];
            $sql = "UPDATE entrada 
                    SET tipo_entrada=:tipo_entrada,
                    codigo=:codigo,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_entrada;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_entrada', $id_entrada, PDO::PARAM_STR);
            $res->bindParam(':tipo_entrada', $data_entrada['tipo_entrada']['codigo'], PDO::PARAM_STR);
            $res->bindParam(':codigo', $correlativo, PDO::PARAM_STR);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            $resp += ['tipo_entrada' => 'dato actualizado'];
            $codigo=true;
        }

        if(isset($data_entrada['id_proveedor'])){
            $sql = "UPDATE entrada 
                    SET id_proveedor=:id_proveedor,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_entrada;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_entrada', $id_entrada, PDO::PARAM_STR);
            $res->bindParam(':id_proveedor', $data_entrada['id_proveedor']['id'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            $resp += ['id_proveedor' => 'dato actualizado'];
        }

        if(isset($data_entrada['id_compra'])){
            $sql = "UPDATE entrada 
                    SET id_compra=:id_compra,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_entrada;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_entrada', $id_entrada, PDO::PARAM_STR);
            $res->bindParam(':id_compra', $data_entrada['id_compra']['id'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            $resp += ['id_compra' => 'dato actualizado'];
        }

        if(isset($data_entrada['tipo_adquisicion'])){
            $sql = "UPDATE entrada 
                    SET tipo_adquisicion=:tipo_adquisicion,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_entrada;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_entrada', $id_entrada, PDO::PARAM_STR);
            $res->bindParam(':tipo_adquisicion', $data_entrada['tipo_adquisicion']['codigo'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            $resp += ['tipo_adquisicion' => 'dato actualizado'];
        }

        if(isset($data_entrada['tipo_financiamiento'])){
            $sql = "UPDATE entrada 
                    SET tipo_financiamiento=:tipo_financiamiento,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_entrada;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_entrada', $id_entrada, PDO::PARAM_STR);
            $res->bindParam(':tipo_financiamiento', $data_entrada['tipo_financiamiento']['id'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            $resp += ['tipo_financiamiento' => 'dato actualizado'];
        }

        if(isset($data_entrada['factura_comercial'])){
            $sql = "UPDATE entrada 
                    SET factura_comercial=:factura_comercial,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_entrada;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_entrada', $id_entrada, PDO::PARAM_STR);
            $res->bindParam(':factura_comercial', $data_entrada['factura_comercial'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            $resp += ['factura_comercial' => 'dato actualizado'];
        }

        if(isset($data_entrada['c_31'])){
            $sql = "UPDATE entrada 
                    SET c_31=:c_31,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_entrada;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_entrada', $id_entrada, PDO::PARAM_STR);
            $res->bindParam(':c_31', $data_entrada['c_31'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            $resp += ['c_31' => 'dato actualizado'];
        }

        if(isset($data_entrada['modalidad_contratacion'])){
            $sql = "UPDATE entrada 
                    SET modalidad_contratacion=:modalidad_contratacion,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_entrada;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_entrada', $id_entrada, PDO::PARAM_STR);
            $res->bindParam(':modalidad_contratacion', $data_entrada['modalidad_contratacion']['codigo'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            $resp += ['modalidad_contratacion' => 'dato actualizado'];
        }

        if(isset($data_entrada['cite_contrato_compra'])){
            $sql = "UPDATE entrada 
                    SET cite_contrato_compra=:cite_contrato_compra,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_entrada;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_entrada', $id_entrada, PDO::PARAM_STR);
            $res->bindParam(':cite_contrato_compra', $data_entrada['cite_contrato_compra'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            $resp += ['cite_contrato_compra' => 'dato actualizado'];
        }

        if(isset($data_entrada['nota'])){
            $sql = "UPDATE entrada 
                    SET nota=:nota,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_entrada;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_entrada', $id_entrada, PDO::PARAM_STR);
            $res->bindParam(':nota', $data_entrada['nota'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            $resp += ['nota' => 'dato actualizado'];
        }

        if(isset($data_entrada['comision'])){
            $data_entrada['comision']=json_encode($data_entrada['comision']);
            $sql = "UPDATE entrada 
                    SET comision=:comision,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_entrada;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_entrada', $id_entrada, PDO::PARAM_STR);
            $res->bindParam(':comision', $data_entrada['comision'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            $resp += ['comision' => 'dato actualizado'];
        }

        if(isset($data_entrada['estado'])){
            $data_entrada['estado']=$data_entrada['estado'];
            $sql = "UPDATE entrada 
                    SET estado=:estado,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_entrada;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_entrada', $id_entrada, PDO::PARAM_STR);
            $res->bindParam(':estado', $data_entrada['estado'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            $resp += ['estado' => 'dato actualizado'];
        }
        if($codigo){
            $resp += ['codigo' => 'dato actualizado'];
        }
        $resp = array('success'=>'true','message'=>'datos actualizados','data_entrada'=>$resp);
        return $resp;
    }
}
