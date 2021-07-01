<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Actions\RepositoryConection\Conect;
use App\Domain\SalidaRepository;
use App\Infrastructure\Persistence\DataCorrelativoRepository;
use App\Infrastructure\Persistence\DataRegionalRepository;
use App\Infrastructure\Persistence\DataAlmacenRepository;
use App\Infrastructure\Persistence\DataProveedorRepository;
use App\Infrastructure\Persistence\DataCompraRepository;
use App\Infrastructure\Persistence\DataParametricaRepository;
use App\Infrastructure\Persistence\DataItemRepository;
use App\Infrastructure\Persistence\DataKardexRepository;
use \PDO;
use AbmmHasan\Uuid;

class DataSalidaRepository implements SalidaRepository {

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
     * DataSalidaRepository constructor.
     *
     */
    public function __construct() {
        $con = new Conect();
        $this->db = $con->getConection();
        $this->dataCorrelativoRepository = new DataCorrelativoRepository;
        $this->dataRegionalRepository = new DataRegionalRepository;
        $this->dataAlmacenRepository = new DataAlmacenRepository;
        $this->dataProveedorRepository = new DataProveedorRepository;
        $this->dataCompraRepository = new DataCompraRepository;
        $this->dataParametricaRepository = new DataParametricaRepository;
        $this->dataItemRepository = new DataItemRepository;
        $this->dataKardexRepository = new DataKardexRepository;
    }

    public function getSalida($id_salida,$token): array {
        if(!($this->verificaPermisos($id_salida,null,$token))){
            return array('success'=>false,'message'=>'usuario no autorizado','code'=>403,'data_salida'=>array());
        }
        $sql = "SELECT en.*
                FROM (((((((salida en LEFT JOIN regional reg ON en.id_regional=reg.id) 
                LEFT JOIN almacen alm ON en.id_almacen=alm.id)
                LEFT JOIN param_general te ON (en.tipo_salida=te.codigo AND te.cod_grupo LIKE 'param_tipo_salida'))
                LEFT JOIN cliente pr ON en.id_cliente=pr.id)
                LEFT JOIN compra co ON en.id_transaccion=co.id)
                LEFT JOIN param_general ta ON (en.tipo_adquisicion=ta.codigo AND ta.cod_grupo LIKE 'param_tipo_adquisicion'))
                LEFT JOIN param_general tf ON (en.fecha_entrega=tf.codigo AND tf.cod_grupo LIKE 'param_fecha_entrega'))
                LEFT JOIN param_general mc ON (en.modalidad_contratacion=mc.codigo AND mc.cod_grupo LIKE 'param_modalidad_contr')
                WHERE en.id=:id_salida AND en.activo=1";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':id_salida', $id_salida, PDO::PARAM_STR);
        $res->execute();
        if($res->rowCount()>0){
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $res = $res[0];
            $data_regional = $this->dataRegionalRepository->getRegional($res['id_regional']);
            $data_almacen = $this->dataAlmacenRepository->getAlmacen($res['id_almacen'],$token);
            $data_cliente = $this->dataProveedorRepository->getProveedor($res['id_cliente']);
            $data_compra = $this->dataCompraRepository->getCompra($res['id_transaccion']);
            $data_tipo_salida = $this->dataParametricaRepository->getCodParametrica('param_tipo_salida',0,$res['tipo_salida']);
            $data_tipo_adquisicion = $this->dataParametricaRepository->getCodParametrica('param_tipo_adquisicion',0,$res['tipo_adquisicion']);
            $data_fecha_entrega = $this->dataParametricaRepository->getCodParametrica('param_fecha_entrega',0,$res['fecha_entrega']);
            $data_tipo_contratacion = $this->dataParametricaRepository->getCodParametrica('param_modalidad_contr',0,$res['modalidad_contratacion']);
            $result = array('id'=>$res['id'],
     
            'codigo'=>$res['codigo'],
                            'id_regional'=>$data_regional['data_regional'],
                            'id_almacen'=>$data_almacen['data_almacen'],
                            'tipo_salida'=>$data_tipo_salida,
                            'id_cliente'=>$data_cliente['data_cliente'],
                            'id_transaccion'=>$data_compra['data_compra'],
                            'fecha_entrega'=>$data_fecha_entrega,
                            'nota'=>$res['nota'],
                            'estado'=>$res['estado'],
                            'activo'=>$res['activo'],
                            'fecha'=>date('d/m/Y',strtotime($res['f_crea'])),
                            'total'=>$this->calculatotalSalida($res['id']));
            if($result['id_regional']['id']==null){$result['id_regional']=json_decode("{}");}
            if($result['id_almacen']['id']==null){$result['id_almacen']=json_decode("{}");}
            if($result['tipo_salida']['codigo']==null){$result['tipo_salida']=json_decode("{}");}
            if($result['id_cliente']['id']==null){$result['id_cliente']=json_decode("{}");}  
            if($result['id_transaccion']['id']==null){$result['id_transaccion']=json_decode("{}");}
            $resp = array('success'=>true,'message'=>'Exito','data_salida'=>$result,'code'=>200);
        }else{
            $resp = array('success'=>true,'message'=>'No se encontraron registros','data_salida'=>array(),'code'=>200);
        }
        return $resp;
    }

    public function listSalida($query,$token): array {
        if(!(isset($query['filtro'])&&isset($query['limite'])&&isset($query['indice']))){
            return array('success'=>false,'message'=>'Datos invalidos','data_salida'=>array(),'code'=>202);
        }
        if($token->privilegio=='limitado'){
            $filtro_regional="id_regional='".$token->regional."' AND ";
        }else{
            $filtro_regional="";
        }
        $filtro=$query['filtro'];
        $limite=$query['limite'];
        $indice=$query['indice'];
        $limite=$limite+$indice;
        $filter="%".strtolower($filtro)."%";

        $sql = "SELECT en.*
                FROM (((((((salida en LEFT JOIN regional reg ON en.id_regional=reg.id) 
                LEFT JOIN almacen alm ON en.id_almacen=alm.id)
                LEFT JOIN param_general te ON (en.tipo_salida=te.codigo AND te.cod_grupo LIKE 'param_tipo_salida'))
                LEFT JOIN cliente pr ON en.id_cliente=pr.id)
                LEFT JOIN compra co ON en.id_transaccion=co.id)
                LEFT JOIN param_general ta ON (en.tipo_adquisicion=ta.codigo AND ta.cod_grupo LIKE 'param_tipo_adquisicion'))
                LEFT JOIN param_general tf ON (en.fecha_entrega=tf.codigo AND tf.cod_grupo LIKE 'param_fecha_entrega'))
                LEFT JOIN param_general mc ON (en.modalidad_contratacion=mc.codigo AND mc.cod_grupo LIKE 'param_modalidad_contr')
                WHERE en.activo=1 AND  ".$filtro_regional."(
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
        $sql = "SELECT en.*
                FROM (((((((salida en LEFT JOIN regional reg ON en.id_regional=reg.id) 
                LEFT JOIN almacen alm ON en.id_almacen=alm.id)
                LEFT JOIN param_general te ON (en.tipo_salida=te.codigo AND te.cod_grupo LIKE 'param_tipo_salida'))
                LEFT JOIN cliente pr ON en.id_cliente=pr.id)
                LEFT JOIN compra co ON en.id_transaccion=co.id)
                LEFT JOIN param_general ta ON (en.tipo_adquisicion=ta.codigo AND ta.cod_grupo LIKE 'param_tipo_adquisicion'))
                LEFT JOIN param_general tf ON (en.fecha_entrega=tf.codigo AND tf.cod_grupo LIKE 'param_fecha_entrega'))
                LEFT JOIN param_general mc ON (en.modalidad_contratacion=mc.codigo AND mc.cod_grupo LIKE 'param_modalidad_contr')
                WHERE en.activo=1 AND  ".$filtro_regional."(
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
                $data_regional = $this->dataRegionalRepository->getRegional($res['id_regional']);
                $data_almacen = $this->dataAlmacenRepository->getAlmacen($res['id_almacen'],$token);
                $data_cliente = $this->dataProveedorRepository->getProveedor($res['id_cliente']);
                $data_compra = $this->dataCompraRepository->getCompra($res['id_transaccion']);
                $data_tipo_salida = $this->dataParametricaRepository->getCodParametrica('param_tipo_salida',0,$res['tipo_salida']);
                $data_tipo_adquisicion = $this->dataParametricaRepository->getCodParametrica('param_tipo_adquisicion',0,$res['tipo_adquisicion']);
                $data_fecha_entrega = $this->dataParametricaRepository->getCodParametrica('param_fecha_entrega',0,$res['fecha_entrega']);
                $data_tipo_contratacion = $this->dataParametricaRepository->getCodParametrica('param_modalidad_contr',0,$res['modalidad_contratacion']);
                $result = array('id'=>$res['id'],
                                'codigo'=>$res['codigo'],
                                'id_regional'=>$data_regional['data_regional'],
                                'id_almacen'=>$data_almacen['data_almacen'],
                                'tipo_salida'=>$data_tipo_salida,
                                'id_cliente'=>$data_cliente['data_cliente'],
                                'id_transaccion'=>$data_compra['data_compra'],
                                'fecha_entrega'=>$data_fecha_entrega,
                                'nota'=>$res['nota'],
                                'estado'=>$res['estado'],
                                'activo'=>$res['activo'],
                                'fecha'=>date('d/m/Y',strtotime($res['f_crea'])),
                                'total'=>$this->calculatotalSalida($res['id']));
                if($result['id_regional']['id']==null){$result['id_regional']=json_decode("{}");}
                if($result['id_almacen']['id']==null){$result['id_almacen']=json_decode("{}");}
                if($result['tipo_salida']['codigo']==null){$result['tipo_salida']=json_decode("{}");}
                if($result['id_cliente']['id']==null){$result['id_cliente']=json_decode("{}");}  
                if($result['id_transaccion']['id']==null){$result['id_transaccion']=json_decode("{}");}
                if($result['fecha_entrega']['codigo']==null){$result['fecha_entrega']=json_decode("{}");}
                array_push($arrayres,$result);
            }
            $concat=array('resultados'=>$arrayres,'total'=>$total);
            $resp = array('success'=>true,'message'=>'Exito','data_salida'=>$concat,'code'=>200);
        }else{
            $concat=array('resultados'=>array(),'total'=>0);
            $resp = array('success'=>true,'message'=>'No se encontraron registros', 'data_salida'=>$concat,'code'=>200);
        }
        return $resp;
    }

    public function editSalida($id_salida,$data_salida,$token): array {
        /*if(!(isset($id_salida)&&isset($data_salida['codigo'])&&isset($data_salida['nombre_comercial'])&&isset($data_salida['codigo_liname'])
        &&isset($data_salida['codigo_linadime'])&&isset($data_salida['referencia'])
        &&isset($data_salida['medicamento'])&&isset($data_salida['form_farm'])&&isset($data_salida['concen'])
        &&isset($data_salida['atq'])&&isset($data_salida['precio_ref'])&&isset($data_salida['aclara_parti'])
        &&isset($data_salida['dispositivo'])&&isset($data_salida['especificacion_tec'])&&isset($data_salida['presentacion']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        if(!($this->verificaPermisos($id_salida,$data_salida['id_regional']['id'],$token))){
            return array('success'=>false,'message'=>'usuario no autorizado','code'=>403,'data_salida'=>array());
        }
        $sql = "SELECT *
                FROM salida
                WHERE (codigo LIKE :codigo OR nombre_comercial LIKE :nombre_comercial ) AND id!=:id_salida";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':codigo', $data_salida['codigo'], PDO::PARAM_STR);
            $res->bindParam(':id_salida', $id_salida, PDO::PARAM_STR);
            $res->bindParam(':nombre_comercial', $data_salida['nombre_comercial'], PDO::PARAM_STR);
            //$res->bindParam(':reg_san', $data_salida['reg_san'], PDO::PARAM_STR);
            $res->execute();
        if($res->rowCount()>0){
            $resp = array('success'=>false,'message'=>'Error, el nombre comercial o codigo del salida ya existe en otro registro','code'=>202,'data_salida'=>array());
        }else{
            $sql = "UPDATE salida 
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
                    WHERE id=:id_salida;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_salida', $id_salida, PDO::PARAM_STR);
            $res->bindParam(':codigo', $data_salida['codigo'], PDO::PARAM_STR);
            $res->bindParam(':nombre_comercial', $data_salida['nombre_comercial'], PDO::PARAM_STR);
            $res->bindParam(':codigo_liname', $data_salida['codigo_liname']['id_liname'], PDO::PARAM_INT);
            $res->bindParam(':codigo_linadime', $data_salida['codigo_linadime']['id_linadime'], PDO::PARAM_INT);
            $res->bindParam(':referencia', $data_salida['referencia'], PDO::PARAM_STR);
            $res->bindParam(':medicamento', $data_salida['medicamento'], PDO::PARAM_STR);
            $res->bindParam(':form_farm', $data_salida['form_farm'], PDO::PARAM_STR);
            $res->bindParam(':concen', $data_salida['concen'], PDO::PARAM_STR);
            $res->bindParam(':atq', $data_salida['atq'], PDO::PARAM_STR);
            $res->bindParam(':precio_ref', $data_salida['precio_ref'], PDO::PARAM_STR);
            $res->bindParam(':aclara_parti', $data_salida['aclara_parti'], PDO::PARAM_STR);
            $res->bindParam(':dispositivo', $data_salida['dispositivo'], PDO::PARAM_STR);
            $res->bindParam(':especificacion_tec', $data_salida['especificacion_tec'], PDO::PARAM_STR);
            $res->bindParam(':presentacion', $data_salida['presentacion'], PDO::PARAM_STR);
            $res->bindParam(':nivel_uso_i', $data_salida['nivel_uso_i'], PDO::PARAM_STR);
            $res->bindParam(':nivel_uso_ii', $data_salida['nivel_uso_ii'], PDO::PARAM_STR);
            $res->bindParam(':nivel_uso_iii', $data_salida['nivel_uso_iii'], PDO::PARAM_STR); 
            $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
            $res->execute();
            //$res = $res->fetchAll(PDO::FETCH_ASSOC);
            if($data_salida['codigo_liname']['id_liname']==null){$data_salida['codigo_liname']=json_decode ("{}");}
            if($data_salida['codigo_linadime']['id_linadime']==null){$data_salida['codigo_linadime']=json_decode ("{}");} 
            $resp = array('success'=>true,'message'=>'salida actualizado','data_salida'=>$data_salida,'code'=>200);
        }*/
        return array();
    }

    public function changestatusSalida($id_salida,$token): array {
        if(!($this->verificaPermisos($id_salida,null,$token))){
            return array('success'=>false,'message'=>'usuario no autorizado','code'=>403,'data_salida'=>array());
        }
        $sql = "UPDATE salida 
                SET activo=0,
                f_inac=now(), 
                u_inac=:u_inac
                WHERE id=:id_salida;";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':u_inac', $token->sub, PDO::PARAM_STR);
        $res->bindParam(':id_salida', $id_salida, PDO::PARAM_STR);
        $res->execute();
        //$res = $res->fetchAll(PDO::FETCH_ASSOC);
        if($res->rowCount()==1){
            $resp = array('success'=>true,'message'=>'1 fila afectada','code'=>200,'data_salida'=>array());
        }else{
            $resp = array('success'=>false,'message'=>'0 fila afectada','code'=>202,'data_salida'=>array());
        }
        return ($resp);
    }

    public function createSalida($data_salida,$token): array {
        if(!(isset($data_salida['id_regional'])&&isset($data_salida['id_almacen'])&&isset($data_salida['tipo_salida']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        if(!($this->verificaPermisos(null,$data_salida['id_regional']['id'],$token))){
            return array('success'=>false,'message'=>'usuario no autorizado','code'=>403,'data_salida'=>array());
        }
        $correlativo = 'Sin Asignar';
        $uuid_neo = Uuid::v4();
        $sql = "INSERT INTO salida (
                id,
                codigo,
                id_regional,
                id_almacen,
                tipo_salida,
                id_cliente,
                id_transaccion,
                fecha_entrega,
                nota,
                estado,
                activo,
                f_crea,
                u_crea
                )VALUES(
                :uuid,
                :codigo,
                :id_regional,
                :id_almacen,
                :tipo_salida,
                :id_cliente,
                :id_transaccion,
                STR_TO_DATE(:fecha_entrega, '%d/%m/%Y'),
                :nota,
                'PENDIENTE',
                1,
                now(),
                :u_crea
                );";

        $res = ($this->db)->prepare($sql);
        $res->bindParam(':uuid', $uuid_neo, PDO::PARAM_STR);
        $res->bindParam(':codigo', $correlativo, PDO::PARAM_STR);
        $res->bindParam(':id_regional', $data_salida['id_regional']['id'], PDO::PARAM_STR);
        $res->bindParam(':id_almacen', $data_salida['id_almacen']['id'], PDO::PARAM_STR);
        $res->bindParam(':tipo_salida', $data_salida['tipo_salida']['codigo'], PDO::PARAM_STR);
        $res->bindParam(':id_cliente', $data_salida['id_cliente']['id'], PDO::PARAM_STR);
        $res->bindParam(':id_transaccion', $data_salida['id_transaccion']['id'], PDO::PARAM_STR);
        $res->bindParam(':fecha_entrega', $data_salida['fecha_entrega'], PDO::PARAM_STR);
        $res->bindParam(':nota', $data_salida['nota'], PDO::PARAM_STR);
        $res->bindParam(':u_crea', $token->sub, PDO::PARAM_STR);
        $res->execute();
        $res = $res->fetchAll(PDO::FETCH_ASSOC);
        $sql = "SELECT *
                FROM salida
                WHERE id LIKE :uuid AND activo=1";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':uuid', $uuid_neo, PDO::PARAM_STR);
        $res->execute();
        $res = $res->fetchAll(PDO::FETCH_ASSOC);
        $res = $res[0];
        $fecha = explode("-",$res['fecha_exp']);
        $result = array('id'=>$res['id'],
                        'codigo'=>$res['codigo'],
                        'id_regional'=>$data_salida['id_regional'],
                        'id_almacen'=>$data_salida['id_almacen'],
                        'tipo_salida'=>$data_salida['tipo_salida'],
                        'id_cliente'=>$data_salida['id_cliente'],
                        'id_transaccion'=>$data_salida['id_transaccion'],
                        'fecha_entrega'=>$fecha[2]."/".$fecha[1]."/".$fecha[0],
                        'nota'=>$res['nota'],
                        'activo'=>$res['activo']);
        if($data_salida['id_regional']['id']==null){$result['id_regional']=json_decode("{}");}
        if($data_salida['id_almacen']['id']==null){$result['id_almacen']=json_decode("{}");}
        if($data_salida['tipo_salida']['codigo']==null){$result['tipo_salida']=json_decode("{}");}
        if($data_salida['id_cliente']['id']==null){$result['id_cliente']=json_decode("{}");}  
        if($data_salida['id_transaccion']['id']==null){$result['id_transaccion']=json_decode("{}");}
        $resp = array('success'=>true,'message'=>'salida registrada exitosamente','data_salida'=>$result,'code'=>200);
        return $resp;
    }

    public function modifySalida($id_salida,$data_salida,$token): array {
        if(!($this->verificaPermisos($id_salida,(isset($data_salida['id_regional']['id']))?$data_salida['id_regional']['id']:null,$token))){
            return array('success'=>false,'message'=>'usuario no autorizado','code'=>403,'data_salida'=>array());
        }
        $codigo=false;
        $resp=array();

        $sql = "SELECT *
                FROM salida
                WHERE id=:id_salida";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':id_salida', $id_salida, PDO::PARAM_STR);
        $res->execute();
        $res = $res->fetchAll(PDO::FETCH_ASSOC);
        $dato_ant=$res[0];

        if(isset($data_salida['id_regional'])){
            $sql = "UPDATE salida 
                    SET id_regional=:id_regional,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_salida;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_salida', $id_salida, PDO::PARAM_STR);
            $res->bindParam(':id_regional', $data_salida['id_regional']['id'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
            $res->execute();
            $resp += ['id_regional' => 'dato actualizado'];
        }

        if(isset($data_salida['id_almacen'])){
            if($dato_ant['id_almacen']!=$data_salida['id_almacen']['id']){
                $sql = "UPDATE salida 
                        SET id_almacen=:id_almacen,
                        codigo=:codigo,
                        f_mod=now(), 
                        u_mod=:u_mod
                        WHERE id=:id_salida;";
                $res = ($this->db)->prepare($sql);
                $res->bindParam(':id_salida', $id_salida, PDO::PARAM_STR);
                $res->bindParam(':id_almacen', $data_salida['id_almacen']['id'], PDO::PARAM_STR);
                $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
                $res->execute();
                $resp += ['id_almacen' => 'dato actualizado'];
                //al cambiar almacen, debemos eliminar los registros de items asociados a la salida
                $query=array(
                    'filtro'=>'',
                    'limite'=>100000000000,
                    'indice'=>0
                );
                $data_items = $this->dataItemRepository->listItem($query,$id_salida);
                $data_items = $data_items['data_item']['resultados'];

                foreach($data_items as $item){
                    $this->dataItemRepository->changestatusItem($item['id'],$token->sub);
                }
            }
            
        }

        if(isset($data_salida['tipo_salida'])){
            $sql = "UPDATE salida 
                    SET tipo_salida=:tipo_salida,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_salida;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_salida', $id_salida, PDO::PARAM_STR);
            $res->bindParam(':tipo_salida', $data_salida['tipo_salida']['codigo'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
            $res->execute();
            $resp += ['tipo_salida' => 'dato actualizado'];
            $codigo=true;
        }

        if(isset($data_salida['id_cliente'])){
            if($dato_ant['id_cliente']!=$data_salida['id_cliente']['id']){
                $sql = "UPDATE salida 
                        SET id_cliente=:id_cliente,
                        f_mod=now(), 
                        u_mod=:u_mod
                        WHERE id=:id_salida;";
                $res = ($this->db)->prepare($sql);
                $res->bindParam(':id_salida', $id_salida, PDO::PARAM_STR);
                $res->bindParam(':id_cliente', $data_salida['id_cliente']['id'], PDO::PARAM_STR);
                $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
                $res->execute();
                $resp += ['id_cliente' => 'dato actualizado'];
                
            }
            
        }

        if(isset($data_salida['id_transaccion'])){
            $sql = "UPDATE salida 
                    SET id_transaccion=:id_transaccion,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_salida;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_salida', $id_salida, PDO::PARAM_STR);
            $res->bindParam(':id_transaccion', $data_salida['id_transaccion']['id'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
            $res->execute();
            $resp += ['id_transaccion' => 'dato actualizado'];
        }

        if(isset($data_salida['fecha_entrega'])){
            $sql = "UPDATE salida 
                    SET fecha_entrega=STR_TO_DATE(:fecha_entrega, '%d/%m/%Y'),
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_salida;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_salida', $id_salida, PDO::PARAM_STR);
            $res->bindParam(':fecha_entrega', $data_salida['fecha_entrega'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
            $res->execute();
            $resp += ['fecha_entrega' => 'dato actualizado'];
        }

        if(isset($data_salida['nota'])){
            $sql = "UPDATE salida 
                    SET nota=:nota,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_salida;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_salida', $id_salida, PDO::PARAM_STR);
            $res->bindParam(':nota', $data_salida['nota'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
            $res->execute();
            $resp += ['nota' => 'dato actualizado'];
        }

        if(isset($data_salida['estado'])){
            if($dato_ant['estado']!=$data_salida['estado']){
                $sql = "UPDATE salida 
                        SET estado=:estado,
                        f_mod=now(), 
                        u_mod=:u_mod
                        WHERE id=:id_salida;";
                $res = ($this->db)->prepare($sql);
                $res->bindParam(':id_salida', $id_salida, PDO::PARAM_STR);
                $res->bindParam(':estado', $data_salida['estado'], PDO::PARAM_STR);
                $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
                $res->execute();
                $resp += ['estado' => 'dato actualizado'];
                if($data_salida['estado']=='COMPLETADO'){
                    //aqui deberiamos generar el codigo de la salida.
                    $sql = "SELECT alm.codigo as cod_almacen, e.tipo_salida
                            FROM salida e, almacen alm
                            WHERE e.id=:id_salida AND alm.id=e.id_almacen";
                    $res = ($this->db)->prepare($sql);
                    $res->bindParam(':id_salida', $id_salida, PDO::PARAM_STR);
                    
                    $res->execute();
                    $res = $res->fetchAll(PDO::FETCH_ASSOC);
                    $res = $res[0];

                    $correlativo = $this->dataCorrelativoRepository->genCorrelativo($res['cod_almacen'].'-OUT', $res['tipo_salida'], $token->sub);
                    $correlativo = $correlativo['correlativo'];
                    $correlativo = $res['cod_almacen'] . '-OUT-' . $correlativo .'-'. $res['tipo_salida'];
                    $sql = "UPDATE salida 
                            SET codigo=:codigo,
                            f_mod=now(), 
                            u_mod=:u_mod
                            WHERE id=:id_salida;";
                    $res = ($this->db)->prepare($sql);
                    $res->bindParam(':id_salida', $id_salida, PDO::PARAM_STR);
                    $res->bindParam(':codigo', $correlativo, PDO::PARAM_STR);
                    $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
                    $res->execute();
                    $resp += ['estado' => 'dato actualizado'];
                    $codigo=true;
                    //concluye asignacion de codigo y empieza envio a kardex
                    $query=array(
                        'filtro'=>'',
                        'limite'=>100000000000,
                        'indice'=>0
                    );
                    $data_items = $this->dataItemRepository->listItem($query,$id_salida);

                    $data_items = $data_items['data_item']['resultados'];
                    $data_salida = $this->getSalida($id_salida,$token);
                    $data_item_cli = array('id_cliente'=>$data_salida['data_salida']['id_cliente']);
                    foreach($data_items as $item){
                        if(($data_salida['data_salida']['id_cliente'])!=json_decode("{}")){
                            $respuesta_cambio_prov=$this->dataItemRepository->modifyItem($item['id'],$data_item_cli,$token->sub);
                        }
                        $data_kardex = array(
                                            'tipo_in_out'=>$item['tipo_in_out'],
                                            'id_item'=>$item,
                                            'id_producto'=>$item['id_producto'],
                                            'id_regional'=>$data_salida['data_salida']['id_regional'],
                                            'id_almacen'=>$data_salida['data_salida']['id_almacen'],
                                            'id_almacen_origen'=>array('id'=>null),
                                            'id_almacen_destino'=>array('id'=>null),
                                            'id_cliente'=>$data_salida['data_salida']['id_cliente'],
                                            'id_proveedor'=>array('id'=>null),
                                            'id_entrada'=>array('id'=>null),
                                            'id_salida'=>$data_salida['data_salida'],
                                            'lote'=>$item['lote'],
                                            'cantidad_diferencia'=>$item['cantidad'],
                                            'precio_compra'=>$item['precio_unidad_fob'],
                                            'precio_actual'=>$item['costo_neto'],
                                            'precio_venta'=>$item['precio_venta']
                                        );
                        $this->dataKardexRepository->createKardex($data_kardex,$token->sub);
                    }
                }
            }
            
        }
        if($codigo){
            $resp += ['codigo' => 'dato generado'];
        }
        $resp = array('success'=>true,'message'=>'datos actualizados','data_salida'=>$resp,'code'=>200);
        return $resp;
    }

    public function calculatotalSalida($id_salida){
        $sql = "SELECT precio_total
                FROM item
                WHERE id_entrada_salida = :id_salida";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':id_salida', $id_salida, PDO::PARAM_STR);
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

    private function verificaPermisos($uuid_registro_a_modificar,$id_regional_registro_nuevo,$token){
        //sacamos los datos del token
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
                        FROM salida
                        WHERE id=:uuid;";
                $res = ($this->db)->prepare($sql);
                $res->bindParam(':uuid', $uuid_registro_a_modificar, PDO::PARAM_STR);
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
