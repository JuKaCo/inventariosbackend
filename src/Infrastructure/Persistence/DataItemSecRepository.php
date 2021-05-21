<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Actions\RepositoryConection\Conect;
use App\Domain\ItemSecRepository;
use App\Infrastructure\Persistence\DataCorrelativoRepository;
use App\Infrastructure\Persistence\DataProductoRepository;
use App\Infrastructure\Persistence\DataParametricaRepository;
use \PDO;
use AbmmHasan\Uuid;

class DataItemSecRepository implements ItemSecRepository {

    /**
     * @$data[]
     */
    private $data;

    /**
     * @$$db conection db
     */
    private $db;
    private $dataCorrelativoRepository;
    private $dataProductoRepository;
    private $dataParametricaRepository;

    /**
     * DataItemSecRepository constructor.
     *
     */
    public function __construct() {
        $con = new Conect();
        $this->db = $con->getConection();
        $this->dataCorrelativoRepository = new DataCorrelativoRepository;
        $this->dataProductoRepository = new DataProductoRepository;
        $this->dataParametricaRepository = new DataParametricaRepository;
    }

    public function getItemSec($id_itemsec): array {
        $sql = "SELECT i.*
                FROM itemsec i
                WHERE i.id=:id_itemsec AND i.activo=1";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':id_itemsec', $id_itemsec, PDO::PARAM_STR);
        $res->execute();
        if($res->rowCount()>0){
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $res = $res[0];
            
            $data_producto = $this->dataProductoRepository->getProducto($res['id_producto']);
            $data_producto = $data_producto['data_producto'];
            $data_factor = $this->dataParametricaRepository->getCodParametrica("param_factor_precio",0,(float)$res['factor']);
            //$data_factor = $data_factor[0];
            $fecha = explode("-",$res['fecha_exp']);
            $result = array('id'=>$res['id'],
                        'id_producto'=>$data_producto,
                        'id_entrada_salida'=>$res['id_entrada_salida'],
                        'tipo_in_out'=>$res['tipo_in_out'],
                        'codigo_prod'=>$res['codigo_prod'],
                        'nombre_prod'=>$res['nombre_prod'],
                        'registro_sanitario'=>$res['registro_sanitario'],
                        'lote'=>$res['lote'],
                        'fecha_exp'=>$fecha[2]."/".$fecha[1]."/".$fecha[0],
                        'cantidad'=>$res['cantidad'],
                        'precio_factura'=>(float)$res['precio_factura'],
                        'precio_unidad_fob'=>(float)$res['precio_unidad_fob'],
                        'precio_total'=>(float)$res['precio_total'],
                        'factor'=>$data_factor,
                        'costo_almacen'=>(float)$res['costo_almacen'],
                        'costo_neto'=>(float)$res['costo_neto'],
                        'precio_venta'=>(float)$res['precio_venta'],
                        'activo'=>$res['activo']);
            $resp = array('success'=>true,'message'=>'Exito','data_itemsec'=>$result);
        }else{
            $resp = array('success'=>false,'message'=>'No se encontraron registros');
        }
        return $resp;
    }

    public function listItemSec($query,$id_entrada_salida): array {
        if(!(isset($query['filtro'])&&isset($query['limite'])&&isset($query['indice']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        $filtro=$query['filtro'];
        $limite=$query['limite'];
        $indice=$query['indice'];
        $limite=$limite+$indice;
        $filter="%".strtolower($filtro)."%";
        $sql = "SELECT i.id
                FROM itemsec i
                WHERE i.activo=1 AND i.id_entrada_salida=:id_entrada_salida AND
                (LOWER(i.id) LIKE LOWER(:filter) OR LOWER(i.id_producto) LIKE LOWER(:filter) OR LOWER(i.id_entrada_salida) LIKE LOWER(:filter) OR LOWER(i.tipo_in_out) LIKE LOWER(:filter) 
                OR LOWER(i.codigo_prod) LIKE LOWER(:filter) OR LOWER(i.nombre_prod) LIKE LOWER(:filter) OR LOWER(i.registro_sanitario) LIKE LOWER(:filter) OR LOWER(i.lote) LIKE LOWER(:filter) OR DATE_FORMAT(i.fecha_exp,'%d/%m/%Y') LIKE :filter)";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':filter', $filter, PDO::PARAM_STR);
        $res->bindParam(':id_entrada_salida', $id_entrada_salida, PDO::PARAM_STR);
        $res->execute();
        $total=$res->rowCount();
        $sql = "SELECT i.*
                FROM itemsec i
                WHERE i.activo=1 AND i.id_entrada_salida=:id_entrada_salida AND
                (LOWER(i.id) LIKE LOWER(:filter) OR LOWER(i.id_producto) LIKE LOWER(:filter) OR LOWER(i.id_entrada_salida) LIKE LOWER(:filter) OR LOWER(i.tipo_in_out) LIKE LOWER(:filter) 
                OR LOWER(i.codigo_prod) LIKE LOWER(:filter) OR LOWER(i.nombre_prod) LIKE LOWER(:filter) OR LOWER(i.registro_sanitario) LIKE LOWER(:filter) OR LOWER(i.lote) LIKE LOWER(:filter) OR DATE_FORMAT(i.fecha_exp,'%d/%m/%Y') LIKE :filter)
                ORDER BY i.f_crea DESC
                LIMIT :indice, :limite;";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':filter', $filter, PDO::PARAM_STR);
        $res->bindParam(':id_entrada_salida', $id_entrada_salida, PDO::PARAM_STR);
        $res->bindParam(':limite', $limite, PDO::PARAM_INT);
        $res->bindParam(':indice', $indice, PDO::PARAM_INT);
        $res->execute();
        if($res->rowCount()>0){
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $arrayres = array();
            foreach ($res as $itemsec){
                $data_producto = $this->dataProductoRepository->getProducto($itemsec['id_producto']);
                $data_producto = $data_producto['data_producto'];
                $data_factor = $this->dataParametricaRepository->getCodParametrica("param_factor_precio",0,(float)$itemsec['factor']);
                //$data_factor = $data_factor[0];
                $fecha = explode("-",$itemsec['fecha_exp']);
                $result = array('id'=>$itemsec['id'],
                            'id_producto'=>$data_producto,
                            'id_entrada_salida'=>$itemsec['id_entrada_salida'],
                            'tipo_in_out'=>$itemsec['tipo_in_out'],
                            'codigo_prod'=>$itemsec['codigo_prod'],
                            'nombre_prod'=>$itemsec['nombre_prod'],
                            'registro_sanitario'=>$itemsec['registro_sanitario'],
                            'lote'=>$itemsec['lote'],
                            'fecha_exp'=>$fecha[2]."/".$fecha[1]."/".$fecha[0],
                            'cantidad'=>$itemsec['cantidad'],
                            'precio_factura'=>(float)$itemsec['precio_factura'],
                            'precio_unidad_fob'=>(float)$itemsec['precio_unidad_fob'],
                            'precio_total'=>(float)$itemsec['precio_total'],
                            'factor'=>$data_factor,
                            'costo_almacen'=>(float)$itemsec['costo_almacen'],
                            'costo_neto'=>(float)$itemsec['costo_neto'],
                            'precio_venta'=>(float)$itemsec['precio_venta'],
                            'activo'=>$itemsec['activo']);
                array_push($arrayres,$result);
            }
            $concat=array('resultados'=>$arrayres,'total'=>$total);
            $resp = array('success'=>true,'message'=>'Exito','data_itemsec'=>$concat);
        }else{
            $concat=array('resultados'=>array(),'total'=>0);
            $resp = array('success'=>true,'message'=>'No se encontraron registros','data_itemsec'=>$concat);
        }
        return $resp;
    }

    public function editItemSec($id_itemsec,$data_itemsec,$uuid): array {
        return array();
    }

    public function modifyItemSec($id_itemsec,$data_itemsec,$uuid): array {
        
        $success=true;
        $resp=array();
        if(isset($data_itemsec['registro_sanitario'])){
            $sql = "UPDATE itemsec 
                    SET registro_sanitario=:registro_sanitario,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_itemsec;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_itemsec', $id_itemsec, PDO::PARAM_STR);
            $res->bindParam(':registro_sanitario', $data_itemsec['registro_sanitario'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            $resp += ['registro_sanitario' => 'dato actualizado'];
        }
        if(isset($data_itemsec['lote'])){
            $sql = "UPDATE itemsec 
                    SET lote=:lote,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_itemsec;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_itemsec', $id_itemsec, PDO::PARAM_STR);
            $res->bindParam(':lote', $data_itemsec['lote'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            $resp += ['lote' => 'dato actualizado'];
        }
        if(isset($data_itemsec['fecha_exp'])){
            $sql = "UPDATE itemsec 
                    SET fecha_exp=STR_TO_DATE(:fecha_exp, '%d/%m/%Y'),
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_itemsec;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_itemsec', $id_itemsec, PDO::PARAM_STR);
            $res->bindParam(':fecha_exp', $data_itemsec['fecha_exp'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            $resp += ['fecha_exp' => 'dato actualizado'];
        }
        if(isset($data_itemsec['cantidad'])){
            $sql = "SELECT i.cantidad, i.precio_unidad_fob, i.factor
                    FROM itemsec i
                    WHERE i.id=:id_itemsec AND i.activo=1";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_itemsec', $id_itemsec, PDO::PARAM_STR);
            $res->execute();
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $precios = $res[0];
            $data_precio=array();
            $data_precio+=['cantidad'=>$data_itemsec['cantidad']];
            $data_precio+=['precio_unidad_fob'=>$precios['precio_unidad_fob']];
            $data_precio+=['factor'=>($precios['factor'])];
            $data_calculo=$this->calculatePriceItemSec($data_precio);
            $data_calculo=$data_calculo['data_calculo'];
            $sql = "UPDATE itemsec 
                    SET cantidad=:cantidad,
                    precio_total=:precio_total,
                    costo_almacen=:costo_almacen,
                    costo_neto=:costo_neto,
                    precio_venta=:precio_venta,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_itemsec;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_itemsec', $id_itemsec, PDO::PARAM_STR);
            $res->bindParam(':cantidad', $data_itemsec['cantidad'], PDO::PARAM_STR);
            $res->bindParam(':precio_total', $data_calculo['total'], PDO::PARAM_STR);
            $res->bindParam(':costo_almacen', $data_calculo['costo_almacen'], PDO::PARAM_STR);
            $res->bindParam(':costo_neto', $data_calculo['costo_neto'], PDO::PARAM_STR);
            $res->bindParam(':precio_venta', $data_calculo['precio_venta'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            $resp += ['cantidad' => 'dato actualizado'];
        }
        if(isset($data_itemsec['precio_factura'])){
            $sql = "UPDATE itemsec 
                    SET precio_factura=:precio_factura,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_itemsec;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_itemsec', $id_itemsec, PDO::PARAM_STR);
            $res->bindParam(':precio_factura', $data_itemsec['precio_factura'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            $resp += ['precio_factura' => 'dato actualizado'];
        }
        if(isset($data_itemsec['precio_unidad_fob'])){
            $sql = "SELECT i.cantidad, i.precio_unidad_fob, i.factor
                    FROM itemsec i
                    WHERE i.id=:id_itemsec AND i.activo=1";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_itemsec', $id_itemsec, PDO::PARAM_STR);
            $res->execute();
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $precios = $res[0];
            $data_precio=array();
            $data_precio+=['cantidad'=>$precios['cantidad']];
            $data_precio+=['precio_unidad_fob'=>$data_itemsec['precio_unidad_fob']];
            $data_precio+=['factor'=>($precios['factor'])];
            $data_calculo=$this->calculatePriceItemSec($data_precio);
            $data_calculo=$data_calculo['data_calculo'];
            $sql = "UPDATE itemsec 
                    SET precio_unidad_fob=:precio_unidad_fob,
                    precio_total=:precio_total,
                    costo_almacen=:costo_almacen,
                    costo_neto=:costo_neto,
                    precio_venta=:precio_venta,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_itemsec;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_itemsec', $id_itemsec, PDO::PARAM_STR);
            $res->bindParam(':precio_unidad_fob', $data_itemsec['precio_unidad_fob'], PDO::PARAM_STR);
            $res->bindParam(':precio_total', $data_calculo['total'], PDO::PARAM_STR);
            $res->bindParam(':costo_almacen', $data_calculo['costo_almacen'], PDO::PARAM_STR);
            $res->bindParam(':costo_neto', $data_calculo['costo_neto'], PDO::PARAM_STR);
            $res->bindParam(':precio_venta', $data_calculo['precio_venta'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            $resp += ['precio_unidad_fob' => 'dato actualizado'];
        }
        if(isset($data_itemsec['factor'])){
            $sql = "SELECT i.cantidad, i.precio_unidad_fob, i.factor
                    FROM itemsec i
                    WHERE i.id=:id_itemsec AND i.activo=1";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_itemsec', $id_itemsec, PDO::PARAM_STR);
            $res->execute();
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $precios = $res[0];
            $data_precio=array();
            $data_precio+=['cantidad'=>$precios['cantidad']];
            $data_precio+=['precio_unidad_fob'=>$precios['precio_unidad_fob']];
            $data_precio+=['factor'=>($data_itemsec['factor']['codigo'])];
            $data_calculo=$this->calculatePriceItemSec($data_precio);
            $data_calculo=$data_calculo['data_calculo'];
            $sql = "UPDATE itemsec 
                    SET factor=:factor,
                    precio_total=:precio_total,
                    costo_almacen=:costo_almacen,
                    costo_neto=:costo_neto,
                    precio_venta=:precio_venta,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_itemsec;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_itemsec', $id_itemsec, PDO::PARAM_STR);
            $res->bindParam(':factor', $data_itemsec['factor']['codigo'], PDO::PARAM_STR);
            $res->bindParam(':precio_total', $data_calculo['total'], PDO::PARAM_STR);
            $res->bindParam(':costo_almacen', $data_calculo['costo_almacen'], PDO::PARAM_STR);
            $res->bindParam(':costo_neto', $data_calculo['costo_neto'], PDO::PARAM_STR);
            $res->bindParam(':precio_venta', $data_calculo['precio_venta'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            $resp += ['factor' => 'dato actualizado'];
        }
        $resp = array('success'=>$success,'message'=>'datos actualizados','data_itemsec'=>$resp);
        return $resp;
    }

    public function changestatusItemSec($id_itemsec,$uuid): array {
        $sql = "UPDATE itemsec 
                SET activo=0,
                f_inac=now(), 
                u_inac=:u_inac
                WHERE id=:id_itemsec;";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':u_inac', $uuid, PDO::PARAM_STR);
        $res->bindParam(':id_itemsec', $id_itemsec, PDO::PARAM_STR);
        $res->execute();
        //$res = $res->fetchAll(PDO::FETCH_ASSOC);
        if($res->rowCount()==1){
            $resp = array('success'=>true,'message'=>'1 fila afectada');
        }else{
            $resp = array('success'=>false,'message'=>'0 fila afectada');
        }
        return ($resp);

    }

    public function createItemSec($data_itemsec,$uuid): array {
        if(!(isset($data_itemsec['id_producto_inventario'])&&isset($data_itemsec['id_coti_vent'])&&isset($data_itemsec['tipo'])&&isset($data_itemsec['cantidad'])
        &&isset($data_itemsec['precio_factura'])&&isset($data_itemsec['lote'])&&isset($data_itemsec['cantidad']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        $data_precio=array();
        $data_precio+=['cantidad'=>$data_itemsec['cantidad']];
        $data_precio+=['precio_unidad_fob'=>$data_itemsec['precio_unidad_fob']];
        $data_precio+=['factor'=>($data_itemsec['factor']['codigo'])];
        $data_calculo=$this->calculatePriceItemSec($data_precio);
        $data_calculo=$data_calculo['data_calculo'];
        $uuid_neo=Uuid::v4();
        $sql = "INSERT INTO itemsec (
                id,
                id_producto_inventario,
                id_coti_vent,
                tipo,
                codigo_prod,
                nombre_prod,
                id_proveedor,
                lote,
                fecha_exp,
                cantidad,
                precio_venta,
                precio_total,            
                activo,
                f_crea,
                u_crea
                )VALUES(
                :uuid,
                :id_producto_inventario,
                :id_coti_vent,
                :tipo,
                :codigo_prod,
                :nombre_prod,
                :id_proveedor,
                :lote,
                STR_TO_DATE(:fecha_exp, '%d/%m/%Y'),
                :cantidad,
                :precio_venta,
                :precio_total,     
                1,
                now(),
                :u_crea
                );";
        $total=$data_itemsec['cantidad']*$data_itemsec['id_producto_inventario']['precio_venta'];
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':uuid', $uuid_neo, PDO::PARAM_STR);
        $res->bindParam(':id_producto_inventario', $data_itemsec['id_producto_inventario']['id'], PDO::PARAM_STR);
        $res->bindParam(':id_coti_vent', $data_itemsec['id_coti_vent']['id'], PDO::PARAM_STR);
        $res->bindParam(':tipo', $data_itemsec['tipo'], PDO::PARAM_STR);
        $res->bindParam(':codigo_prod', $data_itemsec['id_producto_inventario']['codigo'], PDO::PARAM_STR);
        $res->bindParam(':nombre_prod', $data_itemsec['id_producto_inventario']['nombre_comercial'], PDO::PARAM_STR);
        $res->bindParam(':id_proveedor', $data_itemsec['id_producto_inventario']['id_proveedor']['id'], PDO::PARAM_STR);
        $res->bindParam(':lote', $data_itemsec['id_producto_inventario']['lote'], PDO::PARAM_STR);
        $res->bindParam(':fecha_exp', $data_itemsec['id_producto_inventario']['fecha_exp'], PDO::PARAM_STR);
        $res->bindParam(':cantidad', $data_itemsec['cantidad'], PDO::PARAM_STR);
        $res->bindParam(':precio_venta', $data_itemsec['id_producto_inventario']['precio_venta'], PDO::PARAM_STR);
        $res->bindParam(':precio_total', $total, PDO::PARAM_STR);    
        $res->bindParam(':u_crea', $uuid, PDO::PARAM_STR);
        $res->execute();

        $sql = "SELECT *
                FROM itemsec
                WHERE id=:uuid AND activo=1";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':uuid', $uuid_neo, PDO::PARAM_STR);
        $res->execute();
        $res = $res->fetchAll(PDO::FETCH_ASSOC);
        $res = $res[0];
        //$data_producto = $this->dataProductoRepository->getProducto($res['id_producto']);
        //$data_factor = $this->dataParametricaRepository->getCodParametrica("param_factor_precio",0,(float)$res['factor']);
        $fecha = explode("-",$res['fecha_exp']);
        $result = array('id'=>$res['id'],
                        'id_producto_inventario'=>$data_itemsec['id_producto_inventario'],
                        'id_coti_vent'=>$res['id_coti_vent'],
                        'tipo'=>$res['tipo'],
                        'codigo_prod'=>$res['codigo_prod'],
                        'nombre_prod'=>$res['nombre_prod'],
                        'id_proveedor'=>$data_itemsec['id_producto_inventario']['id_proveedor'],
                        'lote'=>$res['lote'],
                        'fecha_exp'=>$fecha[2]."/".$fecha[1]."/".$fecha[0],
                        'cantidad'=>$res['cantidad'],
                        'precio_venta'=>(float)$res['precio_venta'],
                        'precio_total'=>(float)$res['precio_total'],
                        'activo'=>$res['activo']);
        $resp = array('success'=>true,'message'=>'item secundario registrado exitosamente','data_itemsec'=>$result);
        return $resp;
    }
}
