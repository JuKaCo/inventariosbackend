<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Actions\RepositoryConection\Conect;
use App\Domain\CotizacionRepository;
use App\Infrastructure\Persistence\DataCorrelativoRepository;
use App\Infrastructure\Persistence\DataRegionalRepository;
use App\Infrastructure\Persistence\DataAlmacenRepository;
use App\Infrastructure\Persistence\DataClienteRepository;
use App\Infrastructure\Persistence\DataCompraRepository;
use App\Infrastructure\Persistence\DataParametricaRepository;
use App\Infrastructure\Persistence\DataItemRepository;
use App\Infrastructure\Persistence\DataKardexRepository;
use \PDO;
use AbmmHasan\Uuid;

class DataCotizacionRepository implements CotizacionRepository {

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
     * DataCotizacionRepository constructor.
     *
     */
    public function __construct() {
        $con = new Conect();
        $this->db = $con->getConection();
        $this->dataCorrelativoRepository = new DataCorrelativoRepository;
        $this->dataRegionalRepository = new DataRegionalRepository;
        $this->dataAlmacenRepository = new DataAlmacenRepository;
        $this->dataClienteRepository = new DataClienteRepository;
        $this->dataCompraRepository = new DataCompraRepository;
        $this->dataParametricaRepository = new DataParametricaRepository;
        $this->dataItemRepository = new DataItemRepository;
        $this->dataKardexRepository = new DataKardexRepository;
    }

    public function getCotizacion($id_cotizacion): array {

        $sql = "SELECT co.*
                FROM cotizacion co
                WHERE co.id=:id_cotizacion AND co.activo=1";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':id_cotizacion', $id_cotizacion, PDO::PARAM_STR);
        $res->execute();
        if($res->rowCount()>0){
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $res = $res[0];
            $data_regional = $this->dataRegionalRepository->getRegional($res['id_regional']);
            $data_almacen = $this->dataAlmacenRepository->getAlmacen($res['id_almacen']);
            $data_cliente = $this->dataClienteRepository->getCliente($res['id_cliente']);
            $result = array('id'=>$res['id'],
                            'codigo'=>$res['codigo'],
                            'id_regional'=>$data_regional['data_regional'],
                            'id_almacen'=>$data_almacen['data_almacen'],
                            'id_cliente'=>$data_cliente['data_cliente'],
                            'dias_validez'=>$res['dias_validez'],
                            'comentarios'=>$res['comentarios'],
                            'estado'=>$res['estado'],
                            'activo'=>$res['activo'],
                            'fecha'=>date('d/m/Y',strtotime($res['f_crea'])),
                            'total'=>$this->calculatotalCotizacion($res['id']));

            $resp = array('success'=>true,'message'=>'Exito','data_cotizacion'=>$result);
        }else{
            $resp = array('success'=>false,'message'=>'No se encontraron registros');
        }
        return $resp;
    }

    public function listCotizacion($query): array {
        if(!(isset($query['filtro'])&&isset($query['limite'])&&isset($query['indice']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        $filtro=$query['filtro'];
        $limite=$query['limite'];
        $indice=$query['indice'];
        $limite=$limite+$indice;
        $filter="%".strtolower($filtro)."%";

        $sql = "SELECT co.*
                FROM cotizacion co, cliente cl
                WHERE co.id_cliente=cl.id AND co.activo=1 AND (
                LOWER(co.codigo) LIKE LOWER(:filtro) OR LOWER(co.dias_validez) LIKE LOWER(:filtro) OR LOWER(co.comentarios) LIKE LOWER(:filtro) OR LOWER(cl.nombre) LIKE LOWER(:filtro))";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':filtro', $filter, PDO::PARAM_STR);
        $res->execute();
        $total=$res->rowCount();
        $sql = "SELECT co.*
                FROM cotizacion co, cliente cl
                WHERE co.id_cliente=cl.id AND co.activo=1 AND (
                LOWER(co.codigo) LIKE LOWER(:filtro) OR LOWER(co.dias_validez) LIKE LOWER(:filtro) OR LOWER(co.comentarios) LIKE LOWER(:filtro) OR LOWER(cl.nombre) LIKE LOWER(:filtro))
                ORDER BY co.f_crea DESC
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
                $data_almacen = $this->dataAlmacenRepository->getAlmacen($res['id_almacen']);
                $data_cliente = $this->dataClienteRepository->getCliente($res['id_cliente']);
                
                $result = array('id'=>$res['id'],
                                'codigo'=>$res['codigo'],
                                'id_regional'=>$data_regional['data_regional'],
                                'id_almacen'=>$data_almacen['data_almacen'],
                                'id_cliente'=>$data_cliente['data_cliente'],
                                'dias_validez'=>$res['dias_validez'],
                                'comentarios'=>$res['comentarios'],
                                'estado'=>$res['estado'],
                                'activo'=>$res['activo'],
                                'fecha'=>date('d/m/Y',strtotime($res['f_crea'])),
                                'total'=>$this->calculatotalCotizacion($res['id']));
                array_push($arrayres,$result);
            }
            $concat=array('resultados'=>$arrayres,'total'=>$total);
            $resp = array('success'=>true,'message'=>'Exito','data_cotizacion'=>$concat);
        }else{
            $concat=array('resultados'=>array(),'total'=>0);
            $resp = array('success'=>true,'message'=>'No se encontraron registros', 'data_cotizacion'=>$concat);
        }
        return $resp;
    }

    public function editCotizacion($id_cotizacion,$data_cotizacion,$uuid): array {
        if(!(isset($id_cotizacion)&&isset($data_cotizacion['codigo'])&&isset($data_cotizacion['nombre_comercial'])&&isset($data_cotizacion['codigo_liname'])
        &&isset($data_cotizacion['codigo_linadime'])&&isset($data_cotizacion['referencia'])
        &&isset($data_cotizacion['medicamento'])&&isset($data_cotizacion['form_farm'])&&isset($data_cotizacion['concen'])
        &&isset($data_cotizacion['atq'])&&isset($data_cotizacion['precio_ref'])&&isset($data_cotizacion['aclara_parti'])
        &&isset($data_cotizacion['dispositivo'])&&isset($data_cotizacion['especificacion_tec'])&&isset($data_cotizacion['presentacion']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }

        $sql = "SELECT *
                FROM cotizacion
                WHERE (codigo LIKE :codigo OR nombre_comercial LIKE :nombre_comercial ) AND id!=:id_cotizacion";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':codigo', $data_cotizacion['codigo'], PDO::PARAM_STR);
            $res->bindParam(':id_cotizacion', $id_cotizacion, PDO::PARAM_STR);
            $res->bindParam(':nombre_comercial', $data_cotizacion['nombre_comercial'], PDO::PARAM_STR);
            //$res->bindParam(':reg_san', $data_cotizacion['reg_san'], PDO::PARAM_STR);
            $res->execute();
        if($res->rowCount()>0){
            $resp = array('success'=>false,'message'=>'Error, el nombre comercial o codigo del cotizacion ya existe co otro registro');
        }else{
            $sql = "UPDATE cotizacion 
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
                    WHERE id=:id_cotizacion;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_cotizacion', $id_cotizacion, PDO::PARAM_STR);
            $res->bindParam(':codigo', $data_cotizacion['codigo'], PDO::PARAM_STR);
            $res->bindParam(':nombre_comercial', $data_cotizacion['nombre_comercial'], PDO::PARAM_STR);
            $res->bindParam(':codigo_liname', $data_cotizacion['codigo_liname']['id_liname'], PDO::PARAM_INT);
            $res->bindParam(':codigo_linadime', $data_cotizacion['codigo_linadime']['id_linadime'], PDO::PARAM_INT);
            $res->bindParam(':referencia', $data_cotizacion['referencia'], PDO::PARAM_STR);
            $res->bindParam(':medicamento', $data_cotizacion['medicamento'], PDO::PARAM_STR);
            $res->bindParam(':form_farm', $data_cotizacion['form_farm'], PDO::PARAM_STR);
            $res->bindParam(':concen', $data_cotizacion['concen'], PDO::PARAM_STR);
            $res->bindParam(':atq', $data_cotizacion['atq'], PDO::PARAM_STR);
            $res->bindParam(':precio_ref', $data_cotizacion['precio_ref'], PDO::PARAM_STR);
            $res->bindParam(':aclara_parti', $data_cotizacion['aclara_parti'], PDO::PARAM_STR);
            $res->bindParam(':dispositivo', $data_cotizacion['dispositivo'], PDO::PARAM_STR);
            $res->bindParam(':especificacion_tec', $data_cotizacion['especificacion_tec'], PDO::PARAM_STR);
            $res->bindParam(':presentacion', $data_cotizacion['presentacion'], PDO::PARAM_STR);
            $res->bindParam(':nivel_uso_i', $data_cotizacion['nivel_uso_i'], PDO::PARAM_STR);
            $res->bindParam(':nivel_uso_ii', $data_cotizacion['nivel_uso_ii'], PDO::PARAM_STR);
            $res->bindParam(':nivel_uso_iii', $data_cotizacion['nivel_uso_iii'], PDO::PARAM_STR); 
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            //$res = $res->fetchAll(PDO::FETCH_ASSOC);
            if($data_cotizacion['codigo_liname']['id_liname']==null){$data_cotizacion['codigo_liname']=json_decode ("{}");}
            if($data_cotizacion['codigo_linadime']['id_linadime']==null){$data_cotizacion['codigo_linadime']=json_decode ("{}");} 
            $resp = array('success'=>true,'message'=>'cotizacion actualizado','data_cotizacion'=>$data_cotizacion);
        }
        return $resp;
    }

    public function changestatusCotizacion($id_cotizacion,$uuid): array {
        $sql = "UPDATE cotizacion 
                SET activo=0,
                f_inac=now(), 
                u_inac=:u_inac
                WHERE id=:id_cotizacion;";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':u_inac', $uuid, PDO::PARAM_STR);
        $res->bindParam(':id_cotizacion', $id_cotizacion, PDO::PARAM_STR);
        $res->execute();
        //$res = $res->fetchAll(PDO::FETCH_ASSOC);
        if($res->rowCount()==1){
            $resp = array('success'=>true,'message'=>'1 fila afectada');
        }else{
            $resp = array('success'=>false,'message'=>'0 fila afectada');
        }
        return ($resp);
    }

    public function createCotizacion($data_cotizacion,$uuid): array {
        if(!(isset($data_cotizacion['id_regional'])&&isset($data_cotizacion['id_almacen'])
        &&isset($data_cotizacion['dias_validez'])&&isset($data_cotizacion['id_cliente'])
        &&isset($data_cotizacion['comentarios']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        $correlativo = $this->dataCorrelativoRepository->genCorrelativo($data_cotizacion['id_regional']['codigo'], 'COT', $uuid);
        $correlativo = $correlativo['correlativo'];
        $correlativo = $data_cotizacion['id_regional']['codigo'] . '-COT-' . $correlativo ;
        $uuid_neo = Uuid::v4();
        $sql = "INSERT INTO cotizacion (
                id,
                codigo,
                id_regional,
                id_almacen,
                id_cliente,
                dias_validez,
                comentarios,
                estado,
                activo,
                f_crea,
                u_crea
                )VALUES(
                :uuid,
                :codigo,
                :id_regional,
                :id_almacen,
                :id_cliente,
                :dias_validez,
                :comentarios,
                'VIGENTE',
                1,
                now(),
                :u_crea
                );";

        $res = ($this->db)->prepare($sql);
        $res->bindParam(':uuid', $uuid_neo, PDO::PARAM_STR);
        $res->bindParam(':codigo', $correlativo, PDO::PARAM_STR);
        $res->bindParam(':id_regional', $data_cotizacion['id_regional']['id'], PDO::PARAM_STR);
        $res->bindParam(':id_almacen', $data_cotizacion['id_almacen']['id'], PDO::PARAM_STR);
        $res->bindParam(':id_cliente', $data_cotizacion['id_cliente']['id'], PDO::PARAM_STR);
        $res->bindParam(':dias_validez', $data_cotizacion['dias_validez'], PDO::PARAM_INT);
        $res->bindParam(':comentarios', $data_cotizacion['comentarios'], PDO::PARAM_STR);
        $res->bindParam(':u_crea', $uuid, PDO::PARAM_STR);
        $res->execute();
        $res = $res->fetchAll(PDO::FETCH_ASSOC);
        $sql = "SELECT *
                FROM cotizacion
                WHERE id LIKE :uuid AND activo=1";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':uuid', $uuid_neo, PDO::PARAM_STR);
        $res->execute();
        $res = $res->fetchAll(PDO::FETCH_ASSOC);
        $res = $res[0];
        $result = array('id'=>$res['id'],
                        'codigo'=>$res['codigo'],
                        'id_regional'=>$data_cotizacion['id_regional'],
                        'id_almacen'=>$data_cotizacion['id_almacen'],
                        'id_cliente'=>$data_cotizacion['id_cliente'],
                        'dias_validez'=>$res['dias_validez'],
                        'comentarios'=>$res['comentarios'],
                        'estado'=>$res['estado'],
                        'activo'=>$res['activo']); 
        $resp = array('success'=>true,'message'=>'cotizacion registrada exitosamente','data_cotizacion'=>$result);
        return $resp;
    }

    public function modifyCotizacion($id_cotizacion,$data_cotizacion,$uuid): array {
        $codigo=false;
        $resp=array();

        if(isset($data_cotizacion['id_regional'])){

            $correlativo = $this->dataCorrelativoRepository->genCorrelativo($data_cotizacion['id_regional']['codigo'], 'COT', $uuid);
            $correlativo = $correlativo['correlativo'];
            $correlativo = $data_cotizacion['id_regional']['codigo'] . '-COT-' . $correlativo;

            $sql = "UPDATE cotizacion 
                    SET id_regional=:id_regional,
                    codigo=:codigo,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_cotizacion;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_cotizacion', $id_cotizacion, PDO::PARAM_STR);
            $res->bindParam(':id_regional', $data_cotizacion['id_regional']['id'], PDO::PARAM_STR);
            $res->bindParam(':codigo', $correlativo, PDO::PARAM_STR);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            $resp += ['id_regional' => 'dato actualizado'];
            $codigo=true;
        }

        if(isset($data_cotizacion['id_almacen'])){
            
            $sql = "UPDATE cotizacion 
                    SET id_almacen=:id_almacen,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_cotizacion;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_cotizacion', $id_cotizacion, PDO::PARAM_STR);
            $res->bindParam(':id_almacen', $data_cotizacion['id_almacen']['id'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            $resp += ['id_almacen' => 'dato actualizado'];
            
        }

        if(isset($data_cotizacion['id_cliente'])){
            $sql = "UPDATE cotizacion 
                    SET id_cliente=:id_cliente,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_cotizacion;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_cotizacion', $id_cotizacion, PDO::PARAM_STR);
            $res->bindParam(':id_cliente', $data_cotizacion['id_cliente']['id'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            $resp += ['id_cliente' => 'dato actualizado'];
        }

        if(isset($data_cotizacion['dias_validez'])){
            $sql = "UPDATE cotizacion 
                    SET dias_validez=:dias_validez,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_cotizacion;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_cotizacion', $id_cotizacion, PDO::PARAM_STR);
            $res->bindParam(':dias_validez', $data_cotizacion['dias_validez'], PDO::PARAM_INT);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            $resp += ['dias_validez' => 'dato actualizado'];
        }

        if(isset($data_cotizacion['comentarios'])){
            $sql = "UPDATE cotizacion 
                    SET comentarios=:comentarios,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_cotizacion;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_cotizacion', $id_cotizacion, PDO::PARAM_STR);
            $res->bindParam(':comentarios', $data_cotizacion['comentarios'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            $resp += ['comentarios' => 'dato actualizado'];
        }

        if(isset($data_cotizacion['estado'])){
            $sql = "UPDATE cotizacion 
                    SET estado=:estado,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_cotizacion;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_cotizacion', $id_cotizacion, PDO::PARAM_STR);
            $res->bindParam(':estado', $data_cotizacion['estado'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            $resp += ['estado' => 'dato actualizado'];
        }

        /*if(isset($data_cotizacion['estado'])){
            $data_cotizacion['estado']=$data_cotizacion['estado'];
            $sql = "UPDATE cotizacion 
                    SET estado=:estado,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_cotizacion;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_cotizacion', $id_cotizacion, PDO::PARAM_STR);
            $res->bindParam(':estado', $data_cotizacion['estado'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            $resp += ['estado' => 'dato actualizado'];
            if($data_cotizacion['estado']=='COMPLETADO'){
                $query=array(
                    'filtro'=>'',
                    'limite'=>100000000000,
                    'indice'=>0
                );
                $data_items = $this->dataItemRepository->listItem($query,$id_cotizacion);

                $data_items = $data_items['data_item']['resultados'];
                $data_cotizacion = $this->getCotizacion($id_cotizacion);

                foreach($data_items as $item){
                    $data_kardex = array(
                                        'tipo_in_out'=>$item['tipo_in_out'],
                                        'id_item'=>$item,
                                        'id_producto'=>$item['id_producto'],
                                        'id_regional'=>$data_cotizacion['data_cotizacion']['id_regional'],
                                        'id_almacen'=>$data_cotizacion['data_cotizacion']['id_almacen'],
                                        'id_almacen_origen'=>array('id'=>null),
                                        'id_almacen_destino'=>array('id'=>null),
                                        'id_cliente'=>$data_cotizacion['data_cotizacion']['id_cliente'],
                                        'id_cliente'=>array('id'=>null),
                                        'id_cotizacion'=>$data_cotizacion['data_cotizacion'],
                                        'id_salida'=>array('id'=>null),
                                        'lote'=>$item['lote'],
                                        'cantidad_diferencia'=>$item['cantidad'],
                                        'precio_compra'=>$item['precio_unidad_fob'],
                                        'precio_actual'=>$item['costo_neto'],
                                        'precio_venta'=>$item['precio_venta']
                                    );
                    $this->dataKardexRepository->createKardex($data_kardex,$uuid);
                }
            }
        }*/
        if($codigo){
            $resp += ['codigo' => 'dato actualizado'];
        }
        $resp = array('success'=>'true','message'=>'datos actualizados','data_cotizacion'=>$resp);
        return $resp;
    }

    public function calculatotalCotizacion($id_cotizacion){
        $sql = "SELECT precio_total
                FROM item_secundario
                WHERE id_coti_vent = :id_cotizacion";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':id_cotizacion', $id_cotizacion, PDO::PARAM_STR);
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
