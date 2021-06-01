<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Actions\RepositoryConection\Conect;
use App\Domain\EntradaRepository;
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
        $this->dataRegionalRepository = new DataRegionalRepository;
        $this->dataAlmacenRepository = new DataAlmacenRepository;
        $this->dataProveedorRepository = new DataProveedorRepository;
        $this->dataCompraRepository = new DataCompraRepository;
        $this->dataParametricaRepository = new DataParametricaRepository;
        $this->dataItemRepository = new DataItemRepository;
        $this->dataKardexRepository = new DataKardexRepository;
    }

    public function getEntrada($id_entrada,$token): array {
        if(!($this->verificaPermisos($id_entrada,null,$token))){
            return array('success'=>false,'message'=>'usuario no autorizado','code'=>403,'data_entrada'=>array());
        }
        $sql = "SELECT en.*
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
            $data_regional = $this->dataRegionalRepository->getRegional($res['id_regional']);
            $data_almacen = $this->dataAlmacenRepository->getAlmacen($res['id_almacen'],$token);
            $data_proveedor = $this->dataProveedorRepository->getProveedor($res['id_proveedor']);
            $data_compra = $this->dataCompraRepository->getCompra($res['id_compra']);
            $data_tipo_entrada = $this->dataParametricaRepository->getCodParametrica('param_tipo_entrada',0,$res['tipo_entrada']);
            $data_tipo_adquisicion = $this->dataParametricaRepository->getCodParametrica('param_tipo_adquisicion',0,$res['tipo_adquisicion']);
            $data_tipo_financiamiento = $this->dataParametricaRepository->getCodParametrica('param_tipo_financiamiento',0,$res['tipo_financiamiento']);
            $data_tipo_contratacion = $this->dataParametricaRepository->getCodParametrica('param_modalidad_contr',0,$res['modalidad_contratacion']);
            $result = array('id'=>$res['id'],
                            'codigo'=>$res['codigo'],
                            'id_regional'=>$data_regional['data_regional'],
                            'id_almacen'=>$data_almacen['data_almacen'],
                            'tipo_entrada'=>$data_tipo_entrada,
                            'id_proveedor'=>$data_proveedor['data_proveedor'],
                            'id_compra'=>$data_compra['data_compra'],
                            'tipo_adquisicion'=>$data_tipo_adquisicion,
                            'tipo_financiamiento'=>$data_tipo_financiamiento,
                            'factura_comercial'=>$res['factura_comercial'],
                            'c_31'=>$res['c_31'],
                            'modalidad_contratacion'=>$data_tipo_contratacion,
                            'cite_contrato_compra'=>$res['cite_contrato_compra'],
                            'nota'=>$res['nota'],
                            'comision'=>json_decode($res['comision']),
                            'estado'=>$res['estado'],
                            'activo'=>$res['activo'],
                            'fecha'=>date('d/m/Y',strtotime($res['f_crea'])),
                            'total'=>$this->calculatotalEntrada($res['id']));

            if($result['id_regional']['id']==null){$result['id_regional']=json_decode("{}");}
            if($result['id_almacen']['id']==null){$result['id_almacen']=json_decode("{}");}
            if($result['tipo_entrada']['codigo']==null){$result['tipo_entrada']=json_decode("{}");}
            if($result['id_proveedor']['id']==null){$result['id_proveedor']=json_decode("{}");}  
            if($result['id_compra']['id']==null){$result['id_compra']=json_decode("{}");}
            if($result['tipo_adquisicion']['codigo']==null){$result['tipo_adquisicion']=json_decode("{}");} 
            if($result['tipo_financiamiento']['codigo']==null){$result['tipo_financiamiento']=json_decode("{}");}
            if($result['modalidad_contratacion']['codigo']==null){$result['modalidad_contratacion']=json_decode("{}");}  
            $resp = array('success'=>true,'message'=>'Exito','data_entrada'=>$result,'code'=>200);
        }else{
            $resp = array('success'=>true,'message'=>'No se encontraron registros','data_entrada'=>array(),'code'=>200);
        }
        return $resp;
    }

    public function listEntrada($query,$token): array {
        if(!(isset($query['filtro'])&&isset($query['limite'])&&isset($query['indice']))){
            return array('success'=>false,'message'=>'Datos invalidos','data_entrada'=>array(),'code'=>202);
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
                FROM (((((((entrada en LEFT JOIN regional reg ON en.id_regional=reg.id) 
                LEFT JOIN almacen alm ON en.id_almacen=alm.id)
                LEFT JOIN param_general te ON (en.tipo_entrada=te.codigo AND te.cod_grupo LIKE 'param_tipo_entrada'))
                LEFT JOIN proveedor pr ON en.id_proveedor=pr.id)
                LEFT JOIN compra co ON en.id_compra=co.id)
                LEFT JOIN param_general ta ON (en.tipo_adquisicion=ta.codigo AND ta.cod_grupo LIKE 'param_tipo_adquisicion'))
                LEFT JOIN param_general tf ON (en.tipo_financiamiento=tf.codigo AND tf.cod_grupo LIKE 'param_tipo_financiamiento'))
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
                FROM (((((((entrada en LEFT JOIN regional reg ON en.id_regional=reg.id) 
                LEFT JOIN almacen alm ON en.id_almacen=alm.id)
                LEFT JOIN param_general te ON (en.tipo_entrada=te.codigo AND te.cod_grupo LIKE 'param_tipo_entrada'))
                LEFT JOIN proveedor pr ON en.id_proveedor=pr.id)
                LEFT JOIN compra co ON en.id_compra=co.id)
                LEFT JOIN param_general ta ON (en.tipo_adquisicion=ta.codigo AND ta.cod_grupo LIKE 'param_tipo_adquisicion'))
                LEFT JOIN param_general tf ON (en.tipo_financiamiento=tf.codigo AND tf.cod_grupo LIKE 'param_tipo_financiamiento'))
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
                $data_proveedor = $this->dataProveedorRepository->getProveedor($res['id_proveedor']);
                $data_compra = $this->dataCompraRepository->getCompra($res['id_compra']);
                $data_tipo_entrada = $this->dataParametricaRepository->getCodParametrica('param_tipo_entrada',0,$res['tipo_entrada']);
                $data_tipo_adquisicion = $this->dataParametricaRepository->getCodParametrica('param_tipo_adquisicion',0,$res['tipo_adquisicion']);
                $data_tipo_financiamiento = $this->dataParametricaRepository->getCodParametrica('param_tipo_financiamiento',0,$res['tipo_financiamiento']);
                $data_tipo_contratacion = $this->dataParametricaRepository->getCodParametrica('param_modalidad_contr',0,$res['modalidad_contratacion']);
                $result = array('id'=>$res['id'],
                                'codigo'=>$res['codigo'],
                                'id_regional'=>$data_regional['data_regional'],
                                'id_almacen'=>$data_almacen['data_almacen'],
                                'tipo_entrada'=>$data_tipo_entrada,
                                'id_proveedor'=>$data_proveedor['data_proveedor'],
                                'id_compra'=>$data_compra['data_compra'],
                                'tipo_adquisicion'=>$data_tipo_adquisicion,
                                'tipo_financiamiento'=>$data_tipo_financiamiento,
                                'factura_comercial'=>$res['factura_comercial'],
                                'c_31'=>$res['c_31'],
                                'modalidad_contratacion'=>$data_tipo_contratacion,
                                'cite_contrato_compra'=>$res['cite_contrato_compra'],
                                'nota'=>$res['nota'],
                                'comision'=>json_decode($res['comision']),
                                'estado'=>$res['estado'],
                                'activo'=>$res['activo'],
                                'fecha'=>date('d/m/Y',strtotime($res['f_crea'])),
                                'total'=>$this->calculatotalEntrada($res['id']));
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
            $resp = array('success'=>true,'message'=>'Exito','data_entrada'=>$concat,'code'=>200);
        }else{
            $concat=array('resultados'=>array(),'total'=>0);
            $resp = array('success'=>true,'message'=>'No se encontraron registros', 'data_entrada'=>$concat,'code'=>200);
        }
        return $resp;
    }

    public function editEntrada($id_entrada,$data_entrada,$token): array {
        if(!(isset($id_entrada)&&isset($data_entrada['codigo'])&&isset($data_entrada['nombre_comercial'])&&isset($data_entrada['codigo_liname'])
        &&isset($data_entrada['codigo_linadime'])&&isset($data_entrada['referencia'])
        &&isset($data_entrada['medicamento'])&&isset($data_entrada['form_farm'])&&isset($data_entrada['concen'])
        &&isset($data_entrada['atq'])&&isset($data_entrada['precio_ref'])&&isset($data_entrada['aclara_parti'])
        &&isset($data_entrada['dispositivo'])&&isset($data_entrada['especificacion_tec'])&&isset($data_entrada['presentacion']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        if(!($this->verificaPermisos($id_entrada,$data_entrada['id_regional']['id'],$token))){
            return array('success'=>false,'message'=>'usuario no autorizado','code'=>403,'data_entrada'=>array());
        }
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
            $resp = array('success'=>false,'message'=>'Error, el nombre comercial o codigo del entrada ya existe en otro registro','code'=>202,'data_entrada'=>array());
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
            $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
            $res->execute();
            //$res = $res->fetchAll(PDO::FETCH_ASSOC);
            if($data_entrada['codigo_liname']['id_liname']==null){$data_entrada['codigo_liname']=json_decode ("{}");}
            if($data_entrada['codigo_linadime']['id_linadime']==null){$data_entrada['codigo_linadime']=json_decode ("{}");} 
            $resp = array('success'=>true,'message'=>'entrada actualizado','data_entrada'=>$data_entrada,'code'=>200);
        }
        return $resp;
    }

    public function changestatusEntrada($id_entrada,$token): array {
        if(!($this->verificaPermisos($id_entrada,null,$token))){
            return array('success'=>false,'message'=>'usuario no autorizado','code'=>403,'data_entrada'=>array());
        }
        $sql = "UPDATE entrada 
                SET activo=0,
                f_inac=now(), 
                u_inac=:u_inac
                WHERE id=:id_entrada;";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':u_inac', $token->sub, PDO::PARAM_STR);
        $res->bindParam(':id_entrada', $id_entrada, PDO::PARAM_STR);
        $res->execute();
        //$res = $res->fetchAll(PDO::FETCH_ASSOC);
        if($res->rowCount()==1){
            $resp = array('success'=>true,'message'=>'1 fila afectada','code'=>200,'data_entrada'=>array());
        }else{
            $resp = array('success'=>false,'message'=>'0 fila afectada','code'=>202,'data_entrada'=>array());
        }
        return ($resp);
    }

    public function createEntrada($data_entrada,$token): array {
        if(!(isset($data_entrada['id_regional'])&&isset($data_entrada['id_almacen'])
        &&isset($data_entrada['tipo_entrada'])&&isset($data_entrada['id_proveedor'])
        &&isset($data_entrada['id_compra'])&&isset($data_entrada['tipo_adquisicion'])&&isset($data_entrada['tipo_financiamiento'])
        &&isset($data_entrada['factura_comercial'])&&isset($data_entrada['c_31'])&&isset($data_entrada['modalidad_contratacion'])
        &&isset($data_entrada['cite_contrato_compra'])&&isset($data_entrada['nota'])&&isset($data_entrada['comision']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        if(!($this->verificaPermisos(null,$data_entrada['id_regional']['id'],$token))){
            return array('success'=>false,'message'=>'usuario no autorizado','code'=>403,'data_entrada'=>array());
        }
        $correlativo = 'Sin Asignar';
        $uuid_neo = Uuid::v4();
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
                :uuid,
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
        $res->bindParam(':uuid', $uuid_neo, PDO::PARAM_STR);
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
        $res->bindParam(':u_crea', $token->sub, PDO::PARAM_STR);
        $res->execute();
        $res = $res->fetchAll(PDO::FETCH_ASSOC);
        $sql = "SELECT *
                FROM entrada
                WHERE id LIKE :uuid AND activo=1";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':uuid', $uuid_neo, PDO::PARAM_STR);
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
        $resp = array('success'=>true,'message'=>'entrada registrada exitosamente','data_entrada'=>$result,'code'=>200);
        return $resp;
    }

    public function modifyEntrada($id_entrada,$data_entrada,$token): array {
        if(!($this->verificaPermisos($id_entrada,(isset($data_entrada['id_regional']['id']))?$data_entrada['id_regional']['id']:null,$token))){
            return array('success'=>false,'message'=>'usuario no autorizado','code'=>403,'data_entrada'=>array());
        }
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
            $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
            $res->execute();
            $resp += ['id_regional' => 'dato actualizado'];
        }

        if(isset($data_entrada['id_almacen'])){
            
            $sql = "UPDATE entrada 
                    SET id_almacen=:id_almacen,
                    codigo=:codigo,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_entrada;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_entrada', $id_entrada, PDO::PARAM_STR);
            $res->bindParam(':id_almacen', $data_entrada['id_almacen']['id'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
            $res->execute();
            $resp += ['id_almacen' => 'dato actualizado'];
            //al cambiar almacen, debemos eliminar los registros de items asociados a la entrada
            $query=array(
                'filtro'=>'',
                'limite'=>100000000000,
                'indice'=>0
            );
            $data_items = $this->dataItemRepository->listItem($query,$id_entrada);
            $data_items = $data_items['data_item']['resultados'];

            foreach($data_items as $item){
                $this->dataItemRepository->changestatusItem($item['id'],$token->sub);
            }
        }

        if(isset($data_entrada['tipo_entrada'])){
            $sql = "UPDATE entrada 
                    SET tipo_entrada=:tipo_entrada,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_entrada;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_entrada', $id_entrada, PDO::PARAM_STR);
            $res->bindParam(':tipo_entrada', $data_entrada['tipo_entrada']['codigo'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
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
            $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
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
            $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
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
            $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
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
            $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
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
            $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
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
            $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
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
            $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
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
            $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
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
            $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
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
            $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
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
            $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
            $res->execute();
            $resp += ['estado' => 'dato actualizado'];
            if($data_entrada['estado']=='COMPLETADO'){
                //aqui deberiamos generar el codigo de la entrada.
                $sql = "SELECT alm.codigo as cod_almacen, e.tipo_entrada
                        FROM entrada e, almacen alm
                        WHERE e.id=:id_entrada AND alm.id=e.id_almacen";
                $res = ($this->db)->prepare($sql);
                $res->bindParam(':id_entrada', $id_entrada, PDO::PARAM_STR);
                
                $res->execute();
                $res = $res->fetchAll(PDO::FETCH_ASSOC);
                $res = $res[0];

                $correlativo = $this->dataCorrelativoRepository->genCorrelativo($res['cod_almacen'].'-IN', $res['tipo_entrada'], $token->sub);
                $correlativo = $correlativo['correlativo'];
                $correlativo = $res['cod_almacen'] . '-IN-' . $correlativo .'-'. $res['tipo_entrada'];
                $sql = "UPDATE entrada 
                        SET codigo=:codigo,
                        f_mod=now(), 
                        u_mod=:u_mod
                        WHERE id=:id_entrada;";
                $res = ($this->db)->prepare($sql);
                $res->bindParam(':id_entrada', $id_entrada, PDO::PARAM_STR);
                $res->bindParam(':codigo', $correlativo, PDO::PARAM_STR);
                $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
                $res->execute();
                $resp += ['id_almacen' => 'dato actualizado'];
                $codigo=true;
                //concluye asignacion de codigo y empieza envio a kardex
                $query=array(
                    'filtro'=>'',
                    'limite'=>100000000000,
                    'indice'=>0
                );
                $data_items = $this->dataItemRepository->listItem($query,$id_entrada);

                $data_items = $data_items['data_item']['resultados'];
                $data_entrada = $this->getEntrada($id_entrada,$token);

                foreach($data_items as $item){
                    $data_kardex = array(
                                        'tipo_in_out'=>$item['tipo_in_out'],
                                        'id_item'=>$item,
                                        'id_producto'=>$item['id_producto'],
                                        'id_regional'=>$data_entrada['data_entrada']['id_regional'],
                                        'id_almacen'=>$data_entrada['data_entrada']['id_almacen'],
                                        'id_almacen_origen'=>array('id'=>null),
                                        'id_almacen_destino'=>array('id'=>null),
                                        'id_proveedor'=>$data_entrada['data_entrada']['id_proveedor'],
                                        'id_cliente'=>array('id'=>null),
                                        'id_entrada'=>$data_entrada['data_entrada'],
                                        'id_salida'=>array('id'=>null),
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
        if($codigo){
            $resp += ['codigo' => 'dato generado'];
        }
        $resp = array('success'=>true,'message'=>'datos actualizados','data_entrada'=>$resp,'code'=>200);
        return $resp;
    }

    public function calculatotalEntrada($id_entrada){
        $sql = "SELECT precio_total
                FROM item
                WHERE id_entrada_salida = :id_entrada";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':id_entrada', $id_entrada, PDO::PARAM_STR);
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
                        FROM entrada
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
