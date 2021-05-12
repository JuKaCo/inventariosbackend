<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Actions\RepositoryConection\Conect;
use App\Domain\ItemRepository;
use App\Infrastructure\Persistence\DataCorrelativoRepository;
use App\Infrastructure\Persistence\DataProductoRepository;
use App\Infrastructure\Persistence\DataParametricaRepository;
use \PDO;
use AbmmHasan\Uuid;

class DataItemRepository implements ItemRepository {

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
     * DataItemRepository constructor.
     *
     */
    public function __construct() {
        $con = new Conect();
        $this->db = $con->getConection();
        $this->dataCorrelativoRepository = new DataCorrelativoRepository;
        $this->dataProductoRepository = new DataProductoRepository;
        $this->dataParametricaRepository = new DataParametricaRepository;
    }

    public function getItem($id_item): array {
        $sql = "SELECT i.*, pg.id_param, pg.cod_grupo, pg.codigo, pg.valor
                FROM item i LEFT JOIN param_general pg ON (i.factor=pg.codigo AND pg.cod_grupo LIKE 'param_factor_precio')
                WHERE i.id=:id_item AND i.activo=1";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':id_item', $id_item, PDO::PARAM_STR);
        $res->execute();
        if($res->rowCount()>0){
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $res = $res[0];
            
            $data_producto = $this->dataProductoRepository->getProducto($res['id_producto']);
            $data_producto = $data_producto['data_producto'];
            //$data_factor = $this->dataParametricaRepository->getParametrica("param_factor_precio",0,"DON");
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
                        'precio_factura'=>$res['precio_factura'],
                        'precio_unidad_fob'=>$res['precio_unidad_fob'],
                        'precio_total'=>$res['precio_total'],
                        'factor'=>array(
                            'id_param'=>$res['id_param'],
                            'cod_grupo'=>$res['cod_grupo'],
                            'codigo'=>$res['codigo'],
                            'valor'=>$res['valor'],
                        ),
                        'costo_almacen'=>$res['costo_almacen'],
                        'costo_neto'=>$res['costo_neto'],
                        'precio_venta'=>$res['precio_venta'],
                        'activo'=>$res['activo']);
            $resp = array('success'=>true,'message'=>'Exito','data_item'=>$result);
        }else{
            $resp = array('success'=>false,'message'=>'No se encontraron registros');
        }
        return $resp;
    }

    public function listItem($query): array {
        if(!(isset($query['filtro'])&&isset($query['limite'])&&isset($query['indice']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        $filtro=$query['filtro'];
        $limite=$query['limite'];
        $indice=$query['indice'];
        $limite=$limite+$indice;
        $filter="%".strtolower($filtro)."%";
        $sql = "SELECT i.id
                FROM item i LEFT JOIN param_general pg ON (i.factor=pg.codigo AND pg.cod_grupo LIKE 'param_factor_precio')
                WHERE i.activo=1 AND 
                (LOWER(i.id) LIKE LOWER(:filter) OR LOWER(i.id_producto) LIKE LOWER(:filter) OR LOWER(i.id_entrada_salida) LIKE LOWER(:filter) OR LOWER(i.tipo_in_out) LIKE LOWER(:filter) 
                OR LOWER(i.codigo_prod) LIKE LOWER(:filter) OR LOWER(i.nombre_prod) LIKE LOWER(:filter) OR LOWER(i.registro_sanitario) LIKE LOWER(:filter) OR LOWER(i.lote) LIKE LOWER(:filter) OR DATE_FORMAT(i.fecha_exp,'%d/%m/%Y') LIKE :filter)";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':filter', $filter, PDO::PARAM_STR);
        $res->execute();
        $total=$res->rowCount();
        $sql = "SELECT i.*, pg.id_param, pg.cod_grupo, pg.codigo, pg.valor
                FROM item i LEFT JOIN param_general pg ON (i.factor=pg.codigo AND pg.cod_grupo LIKE 'param_factor_precio')
                WHERE i.activo=1 AND 
                (LOWER(i.id) LIKE LOWER(:filter) OR LOWER(i.id_producto) LIKE LOWER(:filter) OR LOWER(i.id_entrada_salida) LIKE LOWER(:filter) OR LOWER(i.tipo_in_out) LIKE LOWER(:filter) 
                OR LOWER(i.codigo_prod) LIKE LOWER(:filter) OR LOWER(i.nombre_prod) LIKE LOWER(:filter) OR LOWER(i.registro_sanitario) LIKE LOWER(:filter) OR LOWER(i.lote) LIKE LOWER(:filter) OR DATE_FORMAT(i.fecha_exp,'%d/%m/%Y') LIKE :filter)
                ORDER BY i.f_crea DESC
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
                $data_producto = $this->dataProductoRepository->getProducto($item['id_producto']);
                $data_producto = $data_producto['data_producto'];
                //$data_factor = $this->dataParametricaRepository->getParametrica("param_factor_precio",0,"DON");
                //$data_factor = $data_factor[0];
                $fecha = explode("-",$item['fecha_exp']);
                $result = array('id'=>$item['id'],
                            'id_producto'=>$data_producto,
                            'id_entrada_salida'=>$item['id_entrada_salida'],
                            'tipo_in_out'=>$item['tipo_in_out'],
                            'codigo_prod'=>$item['codigo_prod'],
                            'nombre_prod'=>$item['nombre_prod'],
                            'registro_sanitario'=>$item['registro_sanitario'],
                            'lote'=>$item['lote'],
                            'fecha_exp'=>$fecha[2]."/".$fecha[1]."/".$fecha[0],
                            'cantidad'=>$item['cantidad'],
                            'precio_factura'=>$item['precio_factura'],
                            'precio_unidad_fob'=>$item['precio_unidad_fob'],
                            'precio_total'=>$item['precio_total'],
                            'factor'=>array(
                                'id_param'=>$item['id_param'],
                                'cod_grupo'=>$item['cod_grupo'],
                                'codigo'=>$item['codigo'],
                                'valor'=>$item['valor'],
                            ),
                            'costo_almacen'=>$item['costo_almacen'],
                            'costo_neto'=>$item['costo_neto'],
                            'precio_venta'=>$item['precio_venta'],
                            'activo'=>$item['activo']);
                array_push($arrayres,$result);
            }
            $concat=array('resultados'=>$arrayres,'total'=>$total);
            $resp = array('success'=>true,'message'=>'Exito','data_item'=>$concat);
        }else{
            $resp = array('success'=>false,'message'=>'No se encontraron registros');
        }
        return $resp;
    }

    public function editItem($id_item,$data_item,$uuid): array {
        if(!(isset($data_item['nombre'])&&isset($data_item['descripcion'])&&isset($data_item['gestion'])&&isset($data_item['codigo']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        $sql = "SELECT *
                FROM item
                WHERE codigo=:codigo AND id!=:id_item";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':codigo', $data_item['codigo'], PDO::PARAM_STR);
        $res->bindParam(':id_item', $id_item, PDO::PARAM_STR);
        $res->execute();
        if($res->rowCount()>0){
            $resp = array('success'=>false,'message'=>'Error, el codigo de la item ya existe en otro registro');
        }else{
            $sql = "UPDATE item 
                    SET codigo=:codigo,
                    nombre=:nombre,
                    descripcion=:descripcion,
                    gestion=:gestion,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_item;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_item', $id_item, PDO::PARAM_STR);
            $res->bindParam(':codigo', $data_item['codigo'], PDO::PARAM_STR);
            $res->bindParam(':nombre', $data_item['nombre'], PDO::PARAM_STR);
            $res->bindParam(':descripcion', $data_item['descripcion'], PDO::PARAM_STR);
            $res->bindParam(':gestion', $data_item['gestion'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            //$res = $res->fetchAll(PDO::FETCH_ASSOC);
            $resp = array('success'=>true,'message'=>'item actualizada','data_item'=>$data_item);
        }
        return $resp;
    }

    public function modifyItem($id_item,$data_item,$uuid): array {
        
        $success=true;
        $resp=array();
        if(isset($data_item['registro_sanitario'])){
            $sql = "UPDATE item 
                    SET registro_sanitario=:registro_sanitario,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_item;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_item', $id_item, PDO::PARAM_STR);
            $res->bindParam(':registro_sanitario', $data_item['registro_sanitario'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            $resp += ['registro_sanitario' => 'dato actualizado'];
        }
        if(isset($data_item['lote'])){
            $sql = "UPDATE item 
                    SET lote=:lote,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_item;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_item', $id_item, PDO::PARAM_STR);
            $res->bindParam(':lote', $data_item['lote'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            $resp += ['lote' => 'dato actualizado'];
        }
        if(isset($data_item['fecha_exp'])){
            $sql = "UPDATE item 
                    SET fecha_exp=STR_TO_DATE(:fecha_exp, '%d/%m/%Y'),
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_item;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_item', $id_item, PDO::PARAM_STR);
            $res->bindParam(':fecha_exp', $data_item['fecha_exp'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            $resp += ['fecha_exp' => 'dato actualizado'];
        }
        if(isset($data_item['cantidad'])){
            $sql = "SELECT i.cantidad, i.precio_unidad_fob, i.factor
                    FROM item i
                    WHERE i.id=:id_item AND i.activo=1";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_item', $id_item, PDO::PARAM_STR);
            $res->execute();
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $precios = $res[0];
            $data_precio=array();
            $data_precio+=['cantidad'=>$data_item['cantidad']];
            $data_precio+=['precio_unidad_fob'=>$precios['precio_unidad_fob']];
            $data_precio+=['factor'=>($precios['factor'])];
            $data_calculo=$this->calculatePriceItem($data_precio);
            $data_calculo=$data_calculo['data_calculo'];
            $sql = "UPDATE item 
                    SET cantidad=:cantidad,
                    precio_total=:precio_total,
                    costo_almacen=:costo_almacen,
                    costo_neto=:costo_neto,
                    precio_venta=:precio_venta,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_item;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_item', $id_item, PDO::PARAM_STR);
            $res->bindParam(':cantidad', $data_item['cantidad'], PDO::PARAM_STR);
            $res->bindParam(':precio_total', $data_calculo['total'], PDO::PARAM_STR);
            $res->bindParam(':costo_almacen', $data_calculo['costo_almacen'], PDO::PARAM_STR);
            $res->bindParam(':costo_neto', $data_calculo['costo_neto'], PDO::PARAM_STR);
            $res->bindParam(':precio_venta', $data_calculo['precio_venta'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            $resp += ['cantidad' => 'dato actualizado'];
        }
        if(isset($data_item['precio_factura'])){
            $sql = "UPDATE item 
                    SET precio_factura=:precio_factura,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_item;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_item', $id_item, PDO::PARAM_STR);
            $res->bindParam(':precio_factura', $data_item['precio_factura'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            $resp += ['precio_factura' => 'dato actualizado'];
        }
        if(isset($data_item['precio_unidad_fob'])){
            $sql = "SELECT i.cantidad, i.precio_unidad_fob, i.factor
                    FROM item i
                    WHERE i.id=:id_item AND i.activo=1";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_item', $id_item, PDO::PARAM_STR);
            $res->execute();
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $precios = $res[0];
            $data_precio=array();
            $data_precio+=['cantidad'=>$precios['cantidad']];
            $data_precio+=['precio_unidad_fob'=>$data_item['precio_unidad_fob']];
            $data_precio+=['factor'=>($precios['factor'])];
            $data_calculo=$this->calculatePriceItem($data_precio);
            $data_calculo=$data_calculo['data_calculo'];
            $sql = "UPDATE item 
                    SET precio_unidad_fob=:precio_unidad_fob,
                    precio_total=:precio_total,
                    costo_almacen=:costo_almacen,
                    costo_neto=:costo_neto,
                    precio_venta=:precio_venta,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_item;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_item', $id_item, PDO::PARAM_STR);
            $res->bindParam(':precio_unidad_fob', $data_item['precio_unidad_fob'], PDO::PARAM_STR);
            $res->bindParam(':precio_total', $data_calculo['total'], PDO::PARAM_STR);
            $res->bindParam(':costo_almacen', $data_calculo['costo_almacen'], PDO::PARAM_STR);
            $res->bindParam(':costo_neto', $data_calculo['costo_neto'], PDO::PARAM_STR);
            $res->bindParam(':precio_venta', $data_calculo['precio_venta'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            $resp += ['precio_unidad_fob' => 'dato actualizado'];
        }
        if(isset($data_item['factor'])){
            $sql = "SELECT i.cantidad, i.precio_unidad_fob, i.factor
                    FROM item i
                    WHERE i.id=:id_item AND i.activo=1";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_item', $id_item, PDO::PARAM_STR);
            $res->execute();
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $precios = $res[0];
            $data_precio=array();
            $data_precio+=['cantidad'=>$precios['cantidad']];
            $data_precio+=['precio_unidad_fob'=>$precios['precio_unidad_fob']];
            $data_precio+=['factor'=>($data_item['factor']['codigo'])];
            $data_calculo=$this->calculatePriceItem($data_precio);
            $data_calculo=$data_calculo['data_calculo'];
            $sql = "UPDATE item 
                    SET factor=:factor,
                    precio_total=:precio_total,
                    costo_almacen=:costo_almacen,
                    costo_neto=:costo_neto,
                    precio_venta=:precio_venta,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_item;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_item', $id_item, PDO::PARAM_STR);
            $res->bindParam(':factor', $data_item['factor']['codigo'], PDO::PARAM_STR);
            $res->bindParam(':precio_total', $data_calculo['total'], PDO::PARAM_STR);
            $res->bindParam(':costo_almacen', $data_calculo['costo_almacen'], PDO::PARAM_STR);
            $res->bindParam(':costo_neto', $data_calculo['costo_neto'], PDO::PARAM_STR);
            $res->bindParam(':precio_venta', $data_calculo['precio_venta'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            $resp += ['factor' => 'dato actualizado'];
        }
        /*if(isset($data_item['estado'])){
            $sql = "UPDATE item 
                    SET estado=:estado,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_item;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_item', $id_item, PDO::PARAM_STR);
            $res->bindParam(':estado', $data_item['estado'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            $resp += ['estado' => 'dato actualizado'];
        }*/
        $resp = array('success'=>$success,'message'=>'datos actualizados','data_item'=>$resp);
        return $resp;
    }

    public function changestatusItem($id_item,$uuid): array {
        $sql = "UPDATE item 
                SET activo=0,
                f_inac=now(), 
                u_inac=:u_inac
                WHERE id=:id_item;";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':u_inac', $uuid, PDO::PARAM_STR);
        $res->bindParam(':id_item', $id_item, PDO::PARAM_STR);
        $res->execute();
        //$res = $res->fetchAll(PDO::FETCH_ASSOC);
        if($res->rowCount()==1){
            $resp = array('success'=>true,'message'=>'1 fila afectada');
        }else{
            $resp = array('success'=>false,'message'=>'0 fila afectada');
        }
        return ($resp);

    }

    public function createItem($data_item,$uuid): array {
        if(!(isset($data_item['id_producto'])&&isset($data_item['id_entrada_salida'])&&isset($data_item['tipo_in_out'])&&isset($data_item['cantidad'])
        &&isset($data_item['precio_factura'])&&isset($data_item['lote'])&&isset($data_item['cantidad']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        $data_precio=array();
        $data_precio+=['cantidad'=>$data_item['cantidad']];
        $data_precio+=['precio_unidad_fob'=>$data_item['precio_unidad_fob']];
        $data_precio+=['factor'=>($data_item['factor']['codigo'])];
        $data_calculo=$this->calculatePriceItem($data_precio);
        $data_calculo=$data_calculo['data_calculo'];
        $sql = "INSERT INTO item (
                id,
                id_producto,
                id_entrada_salida,
                tipo_in_out,
                codigo_prod,
                nombre_prod,
                registro_sanitario,
                lote,
                fecha_exp,
                cantidad,
                precio_factura,
                precio_unidad_fob,
                precio_total,
                factor,
                costo_almacen,
                costo_neto,
                precio_venta,
                activo,
                f_crea,
                u_crea
                )VALUES(
                uuid(),
                :id_producto,
                :id_entrada_salida,
                :tipo_in_out,
                :codigo_prod,
                :nombre_prod,
                :registro_sanitario,
                :lote,
                STR_TO_DATE(:fecha_exp, '%d/%m/%Y'),
                :cantidad,
                :precio_factura,
                :precio_unidad_fob,
                :precio_total,
                :factor,
                :costo_almacen,
                :costo_neto,
                :precio_venta,
                1,
                now(),
                :u_crea
                );";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':id_producto', $data_item['id_producto']['id'], PDO::PARAM_STR);
        $res->bindParam(':id_entrada_salida', $data_item['id_entrada_salida'], PDO::PARAM_STR);
        $res->bindParam(':tipo_in_out', $data_item['tipo_in_out'], PDO::PARAM_STR);
        $res->bindParam(':codigo_prod', $data_item['id_producto']['codigo'], PDO::PARAM_STR);
        $res->bindParam(':nombre_prod', $data_item['id_producto']['nombre_comercial'], PDO::PARAM_STR);
        $res->bindParam(':registro_sanitario', $data_item['registro_sanitario'], PDO::PARAM_STR);
        $res->bindParam(':lote', $data_item['lote'], PDO::PARAM_STR);
        $res->bindParam(':fecha_exp', $data_item['fecha_exp'], PDO::PARAM_STR);
        $res->bindParam(':cantidad', $data_item['cantidad'], PDO::PARAM_STR);
        $res->bindParam(':precio_factura', $data_item['precio_factura'], PDO::PARAM_STR);
        $res->bindParam(':precio_unidad_fob', $data_item['precio_unidad_fob'], PDO::PARAM_STR);
        $res->bindParam(':factor', $data_item['factor']['codigo'], PDO::PARAM_STR);
        $res->bindParam(':precio_total', $data_calculo['total'], PDO::PARAM_STR);
        $res->bindParam(':costo_almacen', $data_calculo['costo_almacen'], PDO::PARAM_STR);
        $res->bindParam(':costo_neto', $data_calculo['costo_neto'], PDO::PARAM_STR);
        $res->bindParam(':precio_venta', $data_calculo['precio_venta'], PDO::PARAM_STR);
        $res->bindParam(':u_crea', $uuid, PDO::PARAM_STR);
        $res->execute();
        $res = $res->fetchAll(PDO::FETCH_ASSOC);
        $query=("SELECT id
                FROM item
                ORDER BY f_crea DESC
                LIMIT 1");
        $res = ($this->db)->prepare($query);
        $res->execute();
        $row = $res->fetchAll(PDO::FETCH_ASSOC);
        $id = $row[0]['id'];
        $sql = "SELECT *
                FROM item
                WHERE id=:id AND activo=1";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':id', $id, PDO::PARAM_STR);
        $res->execute();
        $res = $res->fetchAll(PDO::FETCH_ASSOC);
        $res = $res[0];
        //$data_producto = $this->dataProductoRepository->getProducto($res['id_producto']);
        $fecha = explode("-",$res['fecha_exp']);
        $result = array('id'=>$res['id'],
                        'id_producto'=>$data_item['id_producto'],
                        'id_entrada_salida'=>$res['id_entrada_salida'],
                        'tipo_in_out'=>$res['tipo_in_out'],
                        'codigo_prod'=>$res['codigo_prod'],
                        'nombre_prod'=>$res['nombre_prod'],
                        'registro_sanitario'=>$res['registro_sanitario'],
                        'lote'=>$res['lote'],
                        'fecha_exp'=>$fecha[2]."/".$fecha[1]."/".$fecha[0],
                        'cantidad'=>$res['cantidad'],
                        'precio_factura'=>$res['precio_factura'],
                        'precio_unidad_fob'=>$res['precio_unidad_fob'],
                        'precio_total'=>$res['precio_total'],
                        'factor'=>$data_item['factor'],
                        'costo_almacen'=>$res['costo_almacen'],
                        'costo_neto'=>$res['costo_neto'],
                        'precio_venta'=>$res['precio_venta'],
                        'activo'=>$res['activo']);
        $resp = array('success'=>true,'message'=>'item registrado exitosamente','data_item'=>$result);
        return $resp;
    }

    public function calculatePriceItem($data_item):array{

        $price = $data_item['precio_unidad_fob'];
        $factor = $data_item['factor'];	
        $qty = $data_item['cantidad'];
        
        $iva=14.94;	
        $costo_alma = 0;	
        $cf_cv = 0;	
        $cf_cv_porce = 50;	
        $fondo_rota = 0;	
        $fondo_rota_por = 17;	
        $costo_neto = 0;
            
        if($factor == 3.9909)	
        {	
            $costo_alma = ($price * $factor) / 100;
            $costo_almacen = $price + $costo_alma;
            $costo_alma_neto = ((($price * $factor) / 100) + $price) * $qty;
            
            $cf_cv = ($price + $costo_alma) * $cf_cv_porce/100;
            
            $fondo_rota = ($price + $costo_alma) * $fondo_rota_por/100;
            $costo_neto = $fondo_rota + $cf_cv + $costo_alma + $price;
            
        }	
        if($factor == 6.9761)	
        {	
            $costo_alma = ($price*$factor) / 100;
            $costo_almacen = $price + $costo_alma;
            $costo_alma_neto = ((($price * $factor) / 100) + $price) * $qty;
            $cf_cv = ($price + $costo_alma) * $cf_cv_porce/100;
            $fondo_rota = ($price + $costo_alma) * $fondo_rota_por/100;
            $costo_neto = $fondo_rota + $cf_cv + $costo_alma + $price;
        }	
            
        if ($factor == 13)	
        {	
            $costo_alma = $price - (($price * $factor) / 100);
            $costo_almacen = $costo_alma;
            $costo_alma_neto = ($price - (($price * $factor) / 100)) * $qty;
            $cf_cv = $costo_alma * $cf_cv_porce/100;
            $fondo_rota = $costo_alma * $fondo_rota_por/100;
            $costo_neto = $fondo_rota + $cf_cv + $costo_alma;
        }	
            
        if ($factor == 1)	
        {	
            $iva=0;
            $costo_alma = $price;
            $costo_almacen = $costo_alma;
            $costo_alma_neto = $price  * $qty;
            $costo_neto = $costo_alma;
        }

        $total = $costo_alma_neto;
        $precio_venta = (($costo_neto*$iva)/100) + $costo_neto;	
        $result=array();
        $result+=['total'=>round($total,5)];
        $result+=['precio_venta'=>round($precio_venta,5)];
        $result+=['costo_almacen'=>round($costo_almacen,5)];
        $result+=['costo_neto'=>round($costo_neto,5)];
        return array('success'=>true,'message'=>'calculo realizado exitosamente','data_calculo'=>$result);
    }
}
