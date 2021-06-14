<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Actions\RepositoryConection\Conect;
use App\Domain\VentaRepository;
use App\Infrastructure\Persistence\DataCorrelativoRepository;
use App\Infrastructure\Persistence\DataRegionalRepository;
use App\Infrastructure\Persistence\DataAlmacenRepository;
use App\Infrastructure\Persistence\DataClienteRepository;
use App\Infrastructure\Persistence\DataCompraRepository;
use App\Infrastructure\Persistence\DataParametricaRepository;
use App\Infrastructure\Persistence\DataItemSecRepository;
use App\Infrastructure\Persistence\DataKardexRepository;
use \PDO;
use AbmmHasan\Uuid;

class DataVentaRepository implements VentaRepository {

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
     * DataVentaRepository constructor.
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
        $this->DataItemSecRepository = new DataItemSecRepository;
        $this->dataKardexRepository = new DataKardexRepository;
    }

    public function getVenta($id_venta,$token): array {
        if(!($this->verificaPermisos($id_venta,null,$token))){
            return array('success'=>false,'message'=>'usuario no autorizado','code'=>403,'data_venta'=>array());
        }
        $sql = "SELECT co.*
                FROM venta co
                WHERE co.id=:id_venta AND co.activo=1";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':id_venta', $id_venta, PDO::PARAM_STR);
        $res->execute();
        if($res->rowCount()>0){
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $res = $res[0];
            $data_regional = $this->dataRegionalRepository->getRegional($res['id_regional']);
            $data_almacen = $this->dataAlmacenRepository->getAlmacen($res['id_almacen'],$token);
            $data_cliente = $this->dataClienteRepository->getCliente($res['id_cliente'],$token);
            if($res['id_cotizacion']!=''||$res['id_cotizacion']!=null){
                $data_cotizacion = $this->dataCotizacionRepository->getCotizacion($res['id_cotizacion'],$token);
            }else{
                $data_cotizacion['data_cotizacion']=json_decode("{}");
            }
            if($res['id_factura']!=''||$res['id_factura']!=null){
                $data_factura = $this->dataFacturaRepository->getFactura($res['id_factura'],$token);
            }else{
                $data_factura['data_factura']=json_decode("{}");
            }
            $result = array('id'=>$res['id'],
                            'codigo'=>$res['codigo'],
                            'id_regional'=>$data_regional['data_regional'],
                            'id_almacen'=>$data_almacen['data_almacen'],
                            'id_cliente'=>$data_cliente['data_cliente'],
                            'id_cotizacion'=>$data_cotizacion['data_cotizacion'],
                            'id_factura'=>$data_factura['data_factura'],
                            'tipo_venta'=>$res['tipo_venta'],
                            'referencia'=>$res['referencia'],
                            'nombre_factura'=>$res['nombre_factura'],
                            'nit'=>$res['nit'],
                            'estado'=>$res['estado'],
                            'activo'=>$res['activo'],
                            'fecha'=>date('d/m/Y',strtotime($res['f_crea'])),
                            'total'=>$this->calculatotalVenta($res['id']));

            $resp = array('success'=>true,'message'=>'Exito','data_venta'=>$result,'code'=>200);
        }else{
            $resp = array('success'=>false,'message'=>'No se encontraron registros','data_venta'=>array(),'code'=>202);
        }
        return $resp;
    }

    public function listVenta($query,$token): array {
        if(!(isset($query['filtro'])&&isset($query['limite'])&&isset($query['indice']))){
            return array('success'=>false,'message'=>'Datos invalidos','data_venta'=>array(),'code'=>202);
        }
        if($token->privilegio=='limitado'){
            $filtro_regional="co.id_regional='".$token->regional."' AND ";
        }else{
            $filtro_regional="";
        }
        $filtro=$query['filtro'];
        $limite=$query['limite'];
        $indice=$query['indice'];
        $limite=$limite+$indice;
        $filter="%".strtolower($filtro)."%";

        $sql = "SELECT co.*
                FROM venta co, cliente cl, param_general pg
                WHERE co.id_cliente=cl.id AND co.activo=1 AND pg.id_param=co.tipo_venta AND ".$filtro_regional."(
                LOWER(co.codigo) LIKE LOWER(:filtro) OR LOWER(co.nombre_factura) LIKE LOWER(:filtro) OR LOWER(co.referencia) LIKE LOWER(:filtro) OR LOWER(cl.nombre) LIKE LOWER(:filtro) OR LOWER(co.nit) LIKE LOWER(:filtro) OR LOWER(pg.valor) LIKE LOWER(:filtro))";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':filtro', $filter, PDO::PARAM_STR);
        $res->execute();
        $total=$res->rowCount();
        $sql = "SELECT co.*
                FROM venta co, cliente cl, param_general pg
                WHERE co.id_cliente=cl.id AND co.activo=1 AND pg.id_param=co.tipo_venta AND ".$filtro_regional."(
                LOWER(co.codigo) LIKE LOWER(:filtro) OR LOWER(co.nombre_factura) LIKE LOWER(:filtro) OR LOWER(co.referencia) LIKE LOWER(:filtro) OR LOWER(cl.nombre) LIKE LOWER(:filtro) OR LOWER(co.nit) LIKE LOWER(:filtro) OR LOWER(pg.valor) LIKE LOWER(:filtro))
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
                $data_almacen = $this->dataAlmacenRepository->getAlmacen($res['id_almacen'],$token);
                $data_cliente = $this->dataClienteRepository->getCliente($res['id_cliente'],$token);
                if($res['id_cotizacion']!=''||$res['id_cotizacion']!=null){
                    $data_cotizacion = $this->dataCotizacionRepository->getCotizacion($res['id_cotizacion'],$token);
                }else{
                    $data_cotizacion['data_cotizacion']=json_decode("{}");
                }
                if($res['id_factura']!=''||$res['id_factura']!=null){
                    $data_factura = $this->dataFacturaRepository->getFactura($res['id_factura'],$token);
                }else{
                    $data_factura['data_factura']=json_decode("{}");
                }
                
                $result = array('id'=>$res['id'],
                                'codigo'=>$res['codigo'],
                                'id_regional'=>$data_regional['data_regional'],
                                'id_almacen'=>$data_almacen['data_almacen'],
                                'id_cliente'=>$data_cliente['data_cliente'],
                                'id_cotizacion'=>$data_cotizacion['data_cotizacion'],
                                'id_factura'=>$data_factura['data_factura'],
                                'tipo_venta'=>$res['tipo_venta'],
                                'referencia'=>$res['referencia'],
                                'nombre_factura'=>$res['nombre_factura'],
                                'nit'=>$res['nit'],
                                'estado'=>$res['estado'],
                                'activo'=>$res['activo'],
                                'fecha'=>date('d/m/Y',strtotime($res['f_crea'])),
                                'total'=>$this->calculatotalVenta($res['id']));
                array_push($arrayres,$result);
            }
            $concat=array('resultados'=>$arrayres,'total'=>$total);
            $resp = array('success'=>true,'message'=>'Exito','data_venta'=>$concat,'code'=>200);
        }else{
            $concat=array('resultados'=>array(),'total'=>0);
            $resp = array('success'=>true,'message'=>'No se encontraron registros','data_venta'=>$concat,'code'=>200);
        }
        return $resp;
    }

    public function editVenta($id_venta,$data_venta,$token): array {
        return array();
    }

    public function changestatusVenta($id_venta,$token): array {
        if(!($this->verificaPermisos($id_venta,null,$token))){
            return array('success'=>false,'message'=>'usuario no autorizado','code'=>403,'data_venta'=>array());
        }
        $sql = "UPDATE venta 
                SET activo=0,
                f_inac=now(), 
                u_inac=:u_inac
                WHERE id=:id_venta;";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':u_inac', $token->sub, PDO::PARAM_STR);
        $res->bindParam(':id_venta', $id_venta, PDO::PARAM_STR);
        $res->execute();
        //$res = $res->fetchAll(PDO::FETCH_ASSOC);
        if($res->rowCount()==1){
            $resp = array('success'=>true,'message'=>'1 fila afectada','code'=>200,'data_venta'=>array());
        }else{
            $resp = array('success'=>false,'message'=>'0 fila afectada','code'=>202,'data_venta'=>array());
        }
        return ($resp);
    }

    public function createVenta($data_venta,$token): array {
        if(!(isset($data_venta['id_regional'])&&isset($data_venta['id_almacen'])
        &&isset($data_venta['tipo_venta'])&&isset($data_venta['id_cliente'])
        &&isset($data_venta['referencia'])&&isset($data_venta['nit'])&&isset($data_venta['nombre_factura']))){
            return array('success'=>false,'message'=>'Datos invalidos','data_venta'=>array(),'code'=>202);
        }
        if(!($this->verificaPermisos(null,$data_venta['id_regional']['id'],$token))){
            return array('success'=>false,'message'=>'usuario no autorizado','code'=>403,'data_venta'=>array());
        }
        
        $uuid_neo = Uuid::v4();
        $sql = "INSERT INTO venta (
                id,
                codigo,
                id_regional,
                id_almacen,
                id_cliente,
                id_cotizacion,
                id_factura,
                tipo_venta,
                referencia,
                nombre_factura,
                nit,
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
                :id_cotizacion,
                :id_factura,
                :tipo_venta,
                :referencia,
                :nombre_factura,
                :nit,
                'PENDIENTE',
                1,
                now(),
                :u_crea
                );";
        $codigo="Sin Asignar";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':uuid', $uuid_neo, PDO::PARAM_STR);
        $res->bindParam(':codigo', $codigo, PDO::PARAM_STR);
        $res->bindParam(':id_regional', $data_venta['id_regional']['id'], PDO::PARAM_STR);
        $res->bindParam(':id_almacen', $data_venta['id_almacen']['id'], PDO::PARAM_STR);
        $res->bindParam(':id_cliente', $data_venta['id_cliente']['id'], PDO::PARAM_STR);
        $res->bindParam(':id_cotizacion', $data_venta['id_cotizacion']['id'], PDO::PARAM_STR);
        $res->bindParam(':id_factura', $data_venta['id_factura']['id'], PDO::PARAM_STR);
        $res->bindParam(':tipo_venta', $data_venta['tipo_venta']['id_param'], PDO::PARAM_STR);
        $res->bindParam(':referencia', $data_venta['referencia'], PDO::PARAM_STR);
        $res->bindParam(':nombre_factura', $data_venta['nombre_factura'], PDO::PARAM_STR);
        $res->bindParam(':nit', $data_venta['nit'], PDO::PARAM_INT);
        $res->bindParam(':u_crea', $token->sub, PDO::PARAM_STR);
        $res->execute();
        $res = $res->fetchAll(PDO::FETCH_ASSOC);
        $sql = "SELECT *
                FROM venta
                WHERE id LIKE :uuid AND activo=1";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':uuid', $uuid_neo, PDO::PARAM_STR);
        $res->execute();
        $res = $res->fetchAll(PDO::FETCH_ASSOC);
        $res = $res[0];
        $result = array('id'=>$res['id'],
                        'id_regional'=>$data_venta['id_regional'],
                        'id_almacen'=>$data_venta['id_almacen'],
                        'id_cliente'=>$data_venta['id_cliente'],
                        'id_cotizacion'=>$data_venta['id_cotizacion'],
                        'id_factura'=>$data_venta['id_factura'],
                        'tipo_venta'=>$res['tipo_venta'],
                        'referencia'=>$res['referencia'],
                        'nombre_factura'=>$res['nombre_factura'],
                        'nit'=>$res['nit'],
                        'estado'=>$res['estado'],
                        'activo'=>$res['activo']); 
        $resp = array('success'=>true,'message'=>'venta registrada exitosamente','data_venta'=>$result,'code'=>200);
        return $resp;
    }

    public function modifyVenta($id_venta,$data_venta,$token): array {
        
        if(!($this->verificaPermisos($id_venta,(isset($data_venta['id_regional']['id']))?$data_venta['id_regional']['id']:null,$token))){
            return array('success'=>false,'message'=>'usuario no autorizado','code'=>403,'data_venta'=>array());
        }
        $codigo=false;
        $resp=array();

        $sql = "SELECT *
                FROM venta
                WHERE id=:id_venta";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':id_venta', $id_venta, PDO::PARAM_STR);
        $res->execute();
        $res = $res->fetchAll(PDO::FETCH_ASSOC);
        $dato_ant=$res[0];

        if(isset($data_venta['id_regional'])){
            if($dato_ant['id_regional']!=$data_venta['id_regional']['id']){
                $sql = "UPDATE venta 
                        SET id_regional=:id_regional,
                        f_mod=now(), 
                        u_mod=:u_mod
                        WHERE id=:id_venta;";
                $res = ($this->db)->prepare($sql);
                $res->bindParam(':id_venta', $id_venta, PDO::PARAM_STR);
                $res->bindParam(':id_regional', $data_venta['id_regional']['id'], PDO::PARAM_STR);
                //$res->bindParam(':codigo', $correlativo, PDO::PARAM_STR);
                $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
                $res->execute();
                $resp += ['id_regional' => 'dato actualizado'];
                // se borre en cascada los items secundarios asociados a la venta
                $query=array(
                    'filtro'=>'',
                    'limite'=>100000000000,
                    'indice'=>0
                );
                $data_items = $this->DataItemSecRepository->listItemSec($query,$id_venta);
                $data_items = $data_items['data_itemsec']['resultados'];

                foreach($data_items as $item){
                    $this->DataItemSecRepository->changestatusItemSec($item['id'],$token->sub);
                }
            }
        }

        if(isset($data_venta['id_almacen'])){
            if($dato_ant['id_almacen']!=$data_venta['id_almacen']['id']){
                $sql = "UPDATE venta 
                        SET id_almacen=:id_almacen,
                        f_mod=now(), 
                        u_mod=:u_mod
                        WHERE id=:id_venta;";
                $res = ($this->db)->prepare($sql);
                $res->bindParam(':id_venta', $id_venta, PDO::PARAM_STR);
                $res->bindParam(':id_almacen', $data_venta['id_almacen']['id'], PDO::PARAM_STR);
                $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
                $res->execute();
                
                // se borre en cascada los items secundarios asociados a la venta
                $query=array(
                    'filtro'=>'',
                    'limite'=>100000000000,
                    'indice'=>0
                );
                $data_items = $this->DataItemSecRepository->listItemSec($query,$id_venta);
                $data_items = $data_items['data_itemsec']['resultados'];

                foreach($data_items as $item){
                    $this->DataItemSecRepository->changestatusItemSec($item['id'],$token->sub);
                }
                $resp += ['id_almacen' => 'dato actualizado, items asociados eliminados'];
            }
        }

        if(isset($data_venta['id_cliente'])){
            $sql = "UPDATE venta 
                    SET id_cliente=:id_cliente,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_venta;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_venta', $id_venta, PDO::PARAM_STR);
            $res->bindParam(':id_cliente', $data_venta['id_cliente']['id'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
            $res->execute();
            $resp += ['id_cliente' => 'dato actualizado'];
        }

        if(isset($data_venta['id_cotizacion'])){
            $sql = "UPDATE venta 
                    SET id_cotizacion=:id_cotizacion,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_venta;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_venta', $id_venta, PDO::PARAM_STR);
            $res->bindParam(':id_cotizacion', $data_venta['id_cotizacion']['id'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
            $res->execute();
            $resp += ['id_cotizacion' => 'dato actualizado'];
        }

        if(isset($data_venta['id_factura'])){
            if($dato_ant['id_factura']!=$data_venta['id_factura']['id']){
                $sql = "UPDATE venta 
                        SET id_factura=:id_factura,
                        f_mod=now(), 
                        u_mod=:u_mod
                        WHERE id=:id_venta;";
                $res = ($this->db)->prepare($sql);
                $res->bindParam(':id_venta', $id_venta, PDO::PARAM_STR);
                $res->bindParam(':id_factura', $data_venta['id_factura']['id'], PDO::PARAM_STR);
                $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
                $res->execute();
                $resp += ['id_factura' => 'dato actualizado'];
            }
        }

        if(isset($data_venta['nombre_factura'])){
            $sql = "UPDATE venta 
                    SET nombre_factura=:nombre_factura,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_venta;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_venta', $id_venta, PDO::PARAM_STR);
            $res->bindParam(':nombre_factura', $data_venta['nombre_factura'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
            $res->execute();
            $resp += ['nombre_factura' => 'dato actualizado'];
        }

        if(isset($data_venta['nit'])){
            $sql = "UPDATE venta 
                    SET nit=:nit,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_venta;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_venta', $id_venta, PDO::PARAM_STR);
            $res->bindParam(':nit', $data_venta['nit'], PDO::PARAM_INT);
            $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
            $res->execute();
            $resp += ['nit' => 'dato actualizado'];
        }

        if(isset($data_venta['tipo_venta'])){
            $sql = "UPDATE venta 
                    SET tipo_venta=:tipo_venta,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_venta;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_venta', $id_venta, PDO::PARAM_STR);
            $res->bindParam(':tipo_venta', $data_venta['tipo_venta']['id_param'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
            $res->execute();
            $resp += ['tipo_venta' => 'dato actualizado'];
        }

        if(isset($data_venta['referencia'])){
            $sql = "UPDATE venta 
                    SET referencia=:referencia,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_venta;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_venta', $id_venta, PDO::PARAM_STR);
            $res->bindParam(':referencia', $data_venta['referencia'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
            $res->execute();
            $resp += ['referencia' => 'dato actualizado'];
        }

        if(isset($data_venta['estado'])){
            if($dato_ant['estado']!=$data_venta['estado']){
                $sql = "UPDATE venta 
                        SET estado=:estado,
                        f_mod=now(), 
                        u_mod=:u_mod
                        WHERE id=:id_venta;";
                $res = ($this->db)->prepare($sql);
                $res->bindParam(':id_venta', $id_venta, PDO::PARAM_STR);
                $res->bindParam(':estado', $data_venta['estado'], PDO::PARAM_STR);
                $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
                $res->execute();
                $resp += ['estado' => 'dato actualizado'];
                if($data_venta['estado']=='COMPLETADO'){
                    //tomamos en cuenta esta data para los productos comprometidos, ademas asignamos un codigo a la venta, codigo que generaremos al generar la salida
                    //servicio de creacion de salida
                    $correlativo = "";// es el correlativo de la salida generada
                    $sql = "UPDATE venta 
                            SET codigo=:codigo,
                            f_mod=now(), 
                            u_mod=:u_mod
                            WHERE id=:id_venta;";
                    $res = ($this->db)->prepare($sql);
                    $res->bindParam(':id_venta', $id_venta, PDO::PARAM_STR);
                    $res->bindParam(':codigo', $correlativo, PDO::PARAM_STR);
                    $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
                    $res->execute();
                    //$resp += ['id_almacen' => 'dato actualizado'];
                    $codigo=true;
                }
                if($data_venta['estado']=='ANULADA'){
                    //servicio de anulacion de salida, se deben de devolver los items vendidos a kardex

                }
            }
            
        }

        if($codigo){
            $resp += ['codigo' => 'dato actualizado'];
        }
        $resp = array('success'=>true,'message'=>'datos actualizados','data_venta'=>$resp,'code'=>200);
        return $resp;
    }

    public function calculatotalVenta($id_venta){
        $sql = "SELECT precio_total
                FROM item_secundario
                WHERE id_coti_vent = :id_venta";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':id_venta', $id_venta, PDO::PARAM_STR);
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
                        FROM venta
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
