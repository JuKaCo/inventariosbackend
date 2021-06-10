<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Actions\RepositoryConection\Conect;
use App\Domain\KardexRepository;
use App\Infrastructure\Persistence\DataCorrelativoRepository;
use App\Infrastructure\Persistence\DataProductoRepository;
use App\Infrastructure\Persistence\DataRepository;
use \PDO;
use AbmmHasan\Uuid;

class DataKardexRepository implements KardexRepository {

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
     * DataKardexRepository constructor.
     *
     */
    public function __construct() {
        $con = new Conect();
        $this->db = $con->getConection();
        $this->dataCorrelativoRepository = new DataCorrelativoRepository;
        $this->dataProductoRepository = new DataProductoRepository;
        $this->dataProveedorRepository = new DataProveedorRepository;
    }

    public function getKardex($id_kardex): array {

        $sql = "SELECT en.*, 
                reg.id as reg_id, reg.codigo as reg_codigo, reg.nombre as reg_nombre, reg.direccion as reg_direccion, reg.telefono as reg_telefono,
                alm.id as alm_id, alm.codigo as alm_codigo, alm.nombre as alm_nombre, alm.direccion as alm_direccion, alm.telefono as alm_telefono,
                te.id_param as te_id_param, te.cod_grupo as te_cod_grupo, te.codigo as te_codigo, te.valor as te_valor,
                pr.id as pr_id, pr.codigo as pr_codigo, pr.nombre as pr_nombre, pr.direccion as pr_direccion,
                co.id as co_id, co.codigo as co_codigo, co.nombre as co_nombre, co.gestion as co_gestion, co.descripcion as co_descripcion, co.estado as co_estado,
                ta.id_param as ta_id_param, ta.cod_grupo as ta_cod_grupo, ta.codigo as ta_codigo, ta.valor as ta_valor,
                tf.id_param as tf_id_param, tf.cod_grupo as tf_cod_grupo, tf.codigo as tf_codigo, tf.valor as tf_valor,
                mc.id_param as mc_id_param, mc.cod_grupo as mc_cod_grupo, mc.codigo as mc_codigo, mc.valor as mc_valor
                FROM (((((((kardex en LEFT JOIN regional reg ON en.id_regional=reg.id) 
                LEFT JOIN almacen alm ON en.id_almacen=alm.id)
                LEFT JOIN param_general te ON (en.tipo_kardex=te.codigo AND te.cod_grupo LIKE 'param_tipo_kardex'))
                LEFT JOIN proveedor pr ON en.id_proveedor=pr.id)
                LEFT JOIN compra co ON en.id_compra=co.id)
                LEFT JOIN param_general ta ON (en.tipo_adquisicion=ta.codigo AND ta.cod_grupo LIKE 'param_tipo_adquisicion'))
                LEFT JOIN param_general tf ON (en.tipo_financiamiento=tf.codigo AND tf.cod_grupo LIKE 'param_tipo_financiamiento'))
                LEFT JOIN param_general mc ON (en.modalidad_contratacion=mc.codigo AND mc.cod_grupo LIKE 'param_modalidad_contr')
                WHERE en.id=:id_kardex AND en.activo=1";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':id_kardex', $id_kardex, PDO::PARAM_STR);
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
                            'tipo_kardex'=>array(
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
                            'activo'=>$res['activo'],
                            'fecha'=>date('d/m/Y',strtotime($res['f_crea'])),
                            'total'=>$this->calculatotalKardex($res['id']));
            if($result['id_regional']['id']==null){$result['id_regional']=json_decode("{}");}
            if($result['id_almacen']['id']==null){$result['id_almacen']=json_decode("{}");}
            if($result['tipo_kardex']['codigo']==null){$result['tipo_kardex']=json_decode("{}");}
            if($result['id_proveedor']['id']==null){$result['id_proveedor']=json_decode("{}");}  
            if($result['id_compra']['id']==null){$result['id_compra']=json_decode("{}");}
            if($result['tipo_adquisicion']['codigo']==null){$result['tipo_adquisicion']=json_decode("{}");} 
            if($result['tipo_financiamiento']['codigo']==null){$result['tipo_financiamiento']=json_decode("{}");}
            if($result['modalidad_contratacion']['codigo']==null){$result['modalidad_contratacion']=json_decode("{}");}  
            $resp = array('success'=>true,'message'=>'Exito','data_kardex'=>$result);
        }else{
            $resp = array('success'=>false,'message'=>'No se encontraron registros');
        }
        return $resp;
    }

    public function listKardex($query): array {
        if(!(isset($query['filtro'])&&isset($query['limite'])&&isset($query['indice']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        $filtro=$query['filtro'];
        $limite=$query['limite'];
        $indice=$query['indice'];
        $limite=$limite+$indice;
        $filter="%".strtolower($filtro)."%";

        $sql = "SELECT en.*
                FROM (((((((kardex en LEFT JOIN regional reg ON en.id_regional=reg.id) 
                LEFT JOIN almacen alm ON en.id_almacen=alm.id)
                LEFT JOIN param_general te ON (en.tipo_kardex=te.codigo AND te.cod_grupo LIKE 'param_tipo_kardex'))
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
                FROM (((((((kardex en LEFT JOIN regional reg ON en.id_regional=reg.id) 
                LEFT JOIN almacen alm ON en.id_almacen=alm.id)
                LEFT JOIN param_general te ON (en.tipo_kardex=te.codigo AND te.cod_grupo LIKE 'param_tipo_kardex'))
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
                                'tipo_kardex'=>array(
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
                                'activo'=>$res['activo'],
                                'fecha'=>date('d/m/Y',strtotime($res['f_crea'])),
                                'total'=>$this->calculatotalKardex($res['id']));
                if($result['id_regional']['id']==null){$result['id_regional']=json_decode("{}");}
                if($result['id_almacen']['id']==null){$result['id_almacen']=json_decode("{}");}
                if($result['tipo_kardex']['codigo']==null){$result['tipo_kardex']=json_decode("{}");}
                if($result['id_proveedor']['id']==null){$result['id_proveedor']=json_decode("{}");}  
                if($result['id_compra']['id']==null){$result['id_compra']=json_decode("{}");}
                if($result['tipo_adquisicion']['codigo']==null){$result['tipo_adquisicion']=json_decode("{}");} 
                if($result['tipo_financiamiento']['codigo']==null){$result['tipo_financiamiento']=json_decode("{}");}
                if($result['modalidad_contratacion']['codigo']==null){$result['modalidad_contratacion']=json_decode("{}");}  
                array_push($arrayres,$result);
            }
            $concat=array('resultados'=>$arrayres,'total'=>$total);
            $resp = array('success'=>true,'message'=>'Exito','data_kardex'=>$concat);
        }else{
            $concat=array('resultados'=>array(),'total'=>0);
            $resp = array('success'=>true,'message'=>'No se encontraron registros', 'data_kardex'=>$concat);
        }
        return $resp;
    }

    public function editKardex($id_kardex,$data_kardex,$uuid): array {
        if(!(isset($id_kardex)&&isset($data_kardex['codigo'])&&isset($data_kardex['nombre_comercial'])&&isset($data_kardex['codigo_liname'])
        &&isset($data_kardex['codigo_linadime'])&&isset($data_kardex['referencia'])
        &&isset($data_kardex['medicamento'])&&isset($data_kardex['form_farm'])&&isset($data_kardex['concen'])
        &&isset($data_kardex['atq'])&&isset($data_kardex['precio_ref'])&&isset($data_kardex['aclara_parti'])
        &&isset($data_kardex['dispositivo'])&&isset($data_kardex['especificacion_tec'])&&isset($data_kardex['presentacion']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        /*if($data_kardex['reg_san']==""){
            $data_kardex['reg_san']==null;
            $aux_query=" ";
        }else{
            $aux_query = "OR reg_san LIKE '".$data_kardex['reg_san']."'";
        }*/
        $sql = "SELECT *
                FROM kardex
                WHERE (codigo LIKE :codigo OR nombre_comercial LIKE :nombre_comercial ) AND id!=:id_kardex";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':codigo', $data_kardex['codigo'], PDO::PARAM_STR);
            $res->bindParam(':id_kardex', $id_kardex, PDO::PARAM_STR);
            $res->bindParam(':nombre_comercial', $data_kardex['nombre_comercial'], PDO::PARAM_STR);
            //$res->bindParam(':reg_san', $data_kardex['reg_san'], PDO::PARAM_STR);
            $res->execute();
        if($res->rowCount()>0){
            $resp = array('success'=>false,'message'=>'Error, el nombre comercial o codigo del kardex ya existe en otro registro');
        }else{
            $sql = "UPDATE kardex 
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
                    WHERE id=:id_kardex;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_kardex', $id_kardex, PDO::PARAM_STR);
            $res->bindParam(':codigo', $data_kardex['codigo'], PDO::PARAM_STR);
            $res->bindParam(':nombre_comercial', $data_kardex['nombre_comercial'], PDO::PARAM_STR);
            $res->bindParam(':codigo_liname', $data_kardex['codigo_liname']['id_liname'], PDO::PARAM_INT);
            $res->bindParam(':codigo_linadime', $data_kardex['codigo_linadime']['id_linadime'], PDO::PARAM_INT);
            $res->bindParam(':referencia', $data_kardex['referencia'], PDO::PARAM_STR);
            $res->bindParam(':medicamento', $data_kardex['medicamento'], PDO::PARAM_STR);
            $res->bindParam(':form_farm', $data_kardex['form_farm'], PDO::PARAM_STR);
            $res->bindParam(':concen', $data_kardex['concen'], PDO::PARAM_STR);
            $res->bindParam(':atq', $data_kardex['atq'], PDO::PARAM_STR);
            $res->bindParam(':precio_ref', $data_kardex['precio_ref'], PDO::PARAM_STR);
            $res->bindParam(':aclara_parti', $data_kardex['aclara_parti'], PDO::PARAM_STR);
            $res->bindParam(':dispositivo', $data_kardex['dispositivo'], PDO::PARAM_STR);
            $res->bindParam(':especificacion_tec', $data_kardex['especificacion_tec'], PDO::PARAM_STR);
            $res->bindParam(':presentacion', $data_kardex['presentacion'], PDO::PARAM_STR);
            $res->bindParam(':nivel_uso_i', $data_kardex['nivel_uso_i'], PDO::PARAM_STR);
            $res->bindParam(':nivel_uso_ii', $data_kardex['nivel_uso_ii'], PDO::PARAM_STR);
            $res->bindParam(':nivel_uso_iii', $data_kardex['nivel_uso_iii'], PDO::PARAM_STR); 
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            //$res = $res->fetchAll(PDO::FETCH_ASSOC);
            if($data_kardex['codigo_liname']['id_liname']==null){$data_kardex['codigo_liname']=json_decode ("{}");}
            if($data_kardex['codigo_linadime']['id_linadime']==null){$data_kardex['codigo_linadime']=json_decode ("{}");} 
            $resp = array('success'=>true,'message'=>'kardex actualizado','data_kardex'=>$data_kardex);
        }
        return $resp;
    }

    public function changestatusKardex($id_kardex,$uuid): array {
        $sql = "UPDATE kardex 
                SET activo=0,
                f_inac=now(), 
                u_inac=:u_inac
                WHERE id=:id_kardex;";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':u_inac', $uuid, PDO::PARAM_STR);
        $res->bindParam(':id_kardex', $id_kardex, PDO::PARAM_STR);
        $res->execute();
        //$res = $res->fetchAll(PDO::FETCH_ASSOC);
        if($res->rowCount()==1){
            $resp = array('success'=>true,'message'=>'1 fila afectada');
        }else{
            $resp = array('success'=>false,'message'=>'0 fila afectada');
        }
        return ($resp);
    }

    public function listproducts($id_almacen){
        $sql = "SELECT *
                FROM kardex
                WHERE id_almacen=:id_almacen AND estado LIKE 'VIGENTE'";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':id_almacen', $id_almacen, PDO::PARAM_STR);
    }

    public function createKardex($data_kardex,$uuid): array {
        if(!(isset($data_kardex['id_item'])&&isset($data_kardex['id_producto'])
        &&isset($data_kardex['id_regional'])&&isset($data_kardex['id_almacen'])
        &&isset($data_kardex['tipo_in_out'])&&isset($data_kardex['lote'])
        &&isset($data_kardex['cantidad_diferencia'])&&isset($data_kardex['precio_compra'])&&isset($data_kardex['precio_actual'])
        &&isset($data_kardex['precio_venta']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        //obtenemos el ultimo registro asociado al id_producto y lote
        $sql = "SELECT *
                FROM kardex
                WHERE LOWER(lote) LIKE LOWER(:lote) AND id_producto = :id_producto AND id_proveedor = :id_proveedor
                ORDER BY f_crea DESC
                LIMIT 1;";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':lote', $data_kardex['lote'], PDO::PARAM_STR);
        $res->bindParam(':id_producto', $data_kardex['id_producto']['id'], PDO::PARAM_STR);
        $res->bindParam(':id_proveedor', $data_kardex['id_proveedor']['id'], PDO::PARAM_STR);

        $res->execute();

        if($res->rowCount()>0){
            
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $res = $res[0];
            $id_kardex_padre = $res['id'];
            $cantidad_anterior = (integer)$res['cantidad_actual'];
            $cantidad_actual = 0;
            $estado='VIGENTE';
            if($data_kardex['tipo_in_out']=="IN"){
                $cantidad_actual = (integer)$res['cantidad_actual'] + (integer)$data_kardex['cantidad_diferencia'];//si es ingreso sumamos
            }else{
                if(((integer)$res['cantidad_actual'])<((integer)$data_kardex['cantidad_diferencia'])){
                    $resp = array('success'=>false,'message'=>'cantidad actual es inferior a la cantidad solicitada','data_kardex'=>array());
                }else{
                    $cantidad_actual = (integer)$res['cantidad_actual'] - (integer)$data_kardex['cantidad_diferencia'];//si es salida restamos
                    if($cantidad_actual==0){
                        $estado='COMPENSADO';
                    }
                }
            }
            //CAMBIAMOS ESTADO A ANTERIOR REGISTRO
            $sql = "UPDATE kardex
                    SET estado='ARCHIVADO'
                    WHERE id LIKE :id AND activo=1";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id', $id_kardex_padre, PDO::PARAM_STR);
            $res->execute();
        }else{
            $cantidad_anterior = 0;
            $cantidad_actual = (integer)$data_kardex['cantidad_diferencia'];
            $id_kardex_padre = null;
            
        }
        //$estado='VIGENTE';
        $uuid_neo=Uuid::v4();
        $sql = "INSERT INTO kardex (
                id,
                tipo_in_out,
                id_item,
                id_producto,
                id_regional,
                id_almacen,
                id_almacen_origen,
                id_almacen_destino,
                id_proveedor,
                id_cliente,
                id_entrada,
                id_salida,
                tipo_entrada,
                tipo_salida,
                lote,
                cantidad_anterior,
                cantidad_diferencia,
                cantidad_actual,
                precio_compra,
                precio_actual,
                precio_venta,
                estado,
                id_kardex_padre,
                activo,
                f_crea,
                u_crea
                )VALUES(
                :uuid,
                :tipo_in_out,
                :id_item,
                :id_producto,
                :id_regional,
                :id_almacen,
                :id_almacen_origen,
                :id_almacen_destino,
                :id_proveedor,
                :id_cliente,
                :id_entrada,
                :id_salida,
                :tipo_entrada,
                :tipo_salida,
                :lote,
                :cantidad_anterior,
                :cantidad_diferencia,
                :cantidad_actual,
                :precio_compra,
                :precio_actual,
                :precio_venta,
                :estado,
                :id_kardex_padre,
                1,
                now(),
                :u_crea
                );";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':uuid', $uuid_neo, PDO::PARAM_STR);
        $res->bindParam(':tipo_in_out', $data_kardex['tipo_in_out'], PDO::PARAM_STR);
        $res->bindParam(':id_item', $data_kardex['id_item']['id'], PDO::PARAM_STR);
        $res->bindParam(':id_producto', $data_kardex['id_producto']['id'], PDO::PARAM_STR);
        $res->bindParam(':id_regional', $data_kardex['id_regional']['id'], PDO::PARAM_STR);
        $res->bindParam(':id_almacen', $data_kardex['id_almacen']['id'], PDO::PARAM_STR);
        $res->bindParam(':id_almacen_origen', $data_kardex['id_almacen_origen']['id'], PDO::PARAM_STR);
        $res->bindParam(':id_almacen_destino', $data_kardex['id_almacen_destino']['id'], PDO::PARAM_STR);
        $res->bindParam(':id_proveedor', $data_kardex['id_proveedor']['id'], PDO::PARAM_STR);
        $res->bindParam(':id_cliente', $data_kardex['id_cliente']['id'], PDO::PARAM_STR);
        $res->bindParam(':id_entrada', $data_kardex['id_entrada']['id'], PDO::PARAM_STR);
        $res->bindParam(':id_salida', $data_kardex['id_salida']['id'], PDO::PARAM_STR);
        $res->bindParam(':tipo_entrada', $data_kardex['id_entrada']['tipo_entrada']['id_param'], PDO::PARAM_STR);
        $res->bindParam(':tipo_salida', $data_kardex['id_salida']['tipo_salida']['id_param'], PDO::PARAM_STR);
        $res->bindParam(':lote', $data_kardex['lote'], PDO::PARAM_STR);
        $res->bindParam(':cantidad_anterior', $cantidad_anterior, PDO::PARAM_STR);
        $res->bindParam(':cantidad_diferencia', $data_kardex['cantidad_diferencia'], PDO::PARAM_STR);
        $res->bindParam(':cantidad_actual', $cantidad_actual, PDO::PARAM_STR);
        $res->bindParam(':precio_compra', $data_kardex['precio_compra'], PDO::PARAM_STR);
        $res->bindParam(':precio_actual', $data_kardex['precio_actual'], PDO::PARAM_STR);
        $res->bindParam(':precio_venta', $data_kardex['precio_venta'], PDO::PARAM_STR);
        $res->bindParam(':estado',$estado, PDO::PARAM_STR);
        $res->bindParam(':id_kardex_padre',$id_kardex_padre, PDO::PARAM_STR);
        $res->bindParam(':u_crea', $uuid, PDO::PARAM_STR);
        $res->execute();
        $res = $res->fetchAll(PDO::FETCH_ASSOC);
        $sql = "SELECT *
                FROM kardex
                WHERE id LIKE :id AND activo=1";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':id', $uuid_neo, PDO::PARAM_STR);
        $res->execute();
        $res = $res->fetchAll(PDO::FETCH_ASSOC);
        $res = $res[0];
        $result = array('id'=>$uuid_neo,
                        'tipo_in_out'=>$res['tipo_in_out'],
                        'id_item'=>$res['id_item'],
                        'id_producto'=>$res['id_producto'],
                        'id_regional'=>$res['id_regional'],
                        'id_almacen'=>$res['id_almacen'],
                        'id_almacen_origen'=>$res['id_almacen_origen'],
                        'id_almacen_destino'=>$res['id_almacen_destino'],
                        'id_proveedor'=>$res['id_proveedor'],
                        'id_cliente'=>$res['id_cliente'],
                        'id_entrada'=>$res['id_entrada'],
                        'id_salida'=>$res['id_salida'],
                        'tipo_entrada'=>$res['tipo_entrada'],
                        'tipo_salida'=>$res['tipo_salida'],
                        'lote'=>$res['lote'],
                        'cantidad_anterior'=>$res['cantidad_anterior'],
                        'cantidad_diferencia'=>$res['cantidad_diferencia'],
                        'cantidad_actual'=>$res['cantidad_actual'],
                        'precio_compra'=>$res['precio_compra'],
                        'precio_actual'=>$res['precio_actual'],
                        'precio_venta'=>$res['precio_venta'],
                        'id_kardex_padre'=>$res['id_kardex_padre'],
                        'estado'=>$res['estado'],
                        'activo'=>$res['activo']);
        /*if($data_kardex['id_regional']['id']==null){$result['id_regional']=json_decode("{}");}
        if($data_kardex['id_almacen']['id']==null){$result['id_almacen']=json_decode("{}");}
        if($data_kardex['tipo_kardex']['codigo']==null){$result['tipo_kardex']=json_decode("{}");}
        if($data_kardex['id_proveedor']['id']==null){$result['id_proveedor']=json_decode("{}");}  
        if($data_kardex['id_compra']['id']==null){$result['id_compra']=json_decode("{}");}
        if($data_kardex['tipo_adquisicion']['codigo']==null){$result['tipo_adquisicion']=json_decode("{}");} 
        if($data_kardex['tipo_financiamiento']['codigo']==null){$result['tipo_financiamiento']=json_decode("{}");}
        if($data_kardex['modalidad_contratacion']['codigo']==null){$result['modalidad_contratacion']=json_decode("{}");}  */
        $resp = array('success'=>true,'message'=>'kardex registrado exitosamente','data_kardex'=>$result);
        return $resp;
    }

    public function getProdsKardex($id_almacen, $query): array {
        
        $limite = $query['limite'];
        $indice = $query['indice'];
        $filtro = $query['filtro'];
        $filtro = '%'.$filtro.'%';

        $sql = "SELECT k.id
                FROM kardex k, producto p, item i, proveedor pr
                WHERE k.id_producto=p.id AND k.id_item=i.id AND k.id_proveedor=pr.id AND
                k.id_almacen LIKE :id_almacen AND k.estado like 'VIGENTE' AND 
                (LOWER(k.lote) LIKE LOWER(:filtro) OR LOWER(p.codigo) LIKE LOWER(:filtro) OR 
                LOWER(p.nombre_comercial) LIKE LOWER(:filtro) OR LOWER(p.medicamento) LIKE LOWER(:filtro) OR
                LOWER(p.dispositivo) LIKE LOWER(:filtro) OR LOWER(pr.nombre) LIKE LOWER(:filtro))";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':id_almacen', $id_almacen, PDO::PARAM_STR);
        $res->bindParam(':filtro', $filtro, PDO::PARAM_STR);
        $res->execute();
        $total=$res->rowCount();
        $sql = "SELECT k.id_producto, k.id_proveedor, k.lote, i.fecha_exp, k.cantidad_actual, k.precio_venta
                FROM kardex k, producto p, item i, proveedor pr
                WHERE k.id_producto=p.id AND k.id_item=i.id AND k.id_proveedor=pr.id AND
                k.id_almacen LIKE :id_almacen AND k.estado like 'VIGENTE' AND 
                (LOWER(k.lote) LIKE LOWER(:filtro) OR LOWER(p.codigo) LIKE LOWER(:filtro) OR 
                LOWER(p.nombre_comercial) LIKE LOWER(:filtro) OR LOWER(p.medicamento) LIKE LOWER(:filtro) OR
                LOWER(p.dispositivo) LIKE LOWER(:filtro) OR LOWER(pr.nombre) LIKE LOWER(:filtro))
                ORDER BY p.nombre_comercial
                LIMIT :indice, :limite;";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':id_almacen', $id_almacen, PDO::PARAM_STR);
        $res->bindParam(':filtro', $filtro, PDO::PARAM_STR);
        $res->bindParam(':limite', $limite, PDO::PARAM_INT);
        $res->bindParam(':indice', $indice, PDO::PARAM_INT);
        $res->execute();
        if($res->rowCount()>0){
            $restodo = $res->fetchAll(PDO::FETCH_ASSOC);
            $arrayres = array();
            foreach ($restodo as $res){
                $data_proveedor = $this->dataProveedorRepository->getProveedor($res['id_proveedor']);
                $data_producto = $this->dataProductoRepository->getProducto($res['id_producto']);
                $fecha = explode("-",$res['fecha_exp']);
                $result = array(
                                'id_producto'=>$data_producto['data_producto'],
                                'id_proveedor'=>$data_proveedor['data_proveedor'],
                                'lote'=>$res['lote'],
                                'cantidad_actual'=>(integer)$res['cantidad_actual'],
                                'comprometido'=>(integer)0,
                                'fecha_exp'=>$fecha[2]."/".$fecha[1]."/".$fecha[0],
                                'precio_venta'=>(double)$res['precio_venta']
                                );
                array_push($arrayres,$result);
            }
            $concat=array('resultados'=>$arrayres,'total'=>$total);
            $resp = array('success'=>true,'message'=>'Exito','data_kardex'=>$concat);   
        }else{
            $concat=array('resultados'=>array(),'total'=>0);
            $resp = array('success'=>true,'message'=>'No se encontraron registros','data_kardex'=>$concat);
        }
        return $resp;
    }

    public function modifyKardex($id_kardex,$data_kardex,$uuid): array {
        
        return array();
    }

    public function calculatotalKardex($id_kardex){
        $sql = "SELECT precio_total
                FROM item
                WHERE id_kardex_salida = :id_kardex";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':id_kardex', $id_kardex, PDO::PARAM_STR);
        $res->execute();
        if($res->rowCount()>0){
            $restodo = $res->fetchAll(PDO::FETCH_ASSOC);
            $total = 0;
            foreach ($restodo as $res){
                $total = $total + $res['precio_total'];
            }
            return round($total,2);
        }else{
            return 0;
        }
    }
}
