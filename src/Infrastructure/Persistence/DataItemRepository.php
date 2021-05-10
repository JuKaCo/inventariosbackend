<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Actions\RepositoryConection\Conect;
use App\Domain\ItemRepository;
use App\Infrastructure\Persistence\DataCorrelativoRepository;
use \PDO;

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

    /**
     * DataItemRepository constructor.
     *
     */
    public function __construct() {
        $con = new Conect();
        $this->db = $con->getConection();
        $this->dataCorrelativoRepository = new DataCorrelativoRepository;
    }

    public function getItem($id_item): array {
        $sql = "SELECT com.*
                FROM item com
                WHERE com.id=:id_item AND com.activo=1";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':id_item', $id_item, PDO::PARAM_STR);
        $res->execute();
        if($res->rowCount()>0){
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $res = $res[0];
            $result = array('id'=>$res['id'],
                            'codigo'=>$res['codigo'],
                            'nombre'=>$res['nombre'],
                            'gestion'=>$res['gestion'],
                            'descripcion'=>$res['descripcion'],
                            'estado'=>$res['estado'],
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
        $sql = "SELECT com.*
                FROM item com 
                WHERE com.activo=1 AND 
                (LOWER(com.codigo) LIKE LOWER(:filter) OR LOWER(com.nombre) LIKE LOWER(:filter) OR LOWER(com.descripcion) LIKE LOWER(:filter) OR LOWER(com.gestion) LIKE LOWER(:filter) OR DATE_FORMAT(com.f_crea,'%d/%m/%Y') LIKE :filter)";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':filter', $filter, PDO::PARAM_STR);
        $res->execute();
        $total=$res->rowCount();
        $sql = "SELECT com.*
                FROM item com 
                WHERE com.activo=1 AND (LOWER(com.codigo) LIKE LOWER(:filter) OR LOWER(com.nombre) LIKE LOWER(:filter) OR LOWER(com.descripcion) LIKE LOWER(:filter) OR LOWER(com.gestion) LIKE LOWER(:filter) OR DATE_FORMAT(com.f_crea,'%d/%m/%Y') LIKE :filter)
                ORDER BY com.f_crea DESC
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
                $result = array('id'=>$item['id'],
                                'codigo'=>$item['codigo'],
                                'nombre'=>$item['nombre'],
                                'gestion'=>$item['gestion'],
                                'descripcion'=>$item['descripcion'],
                                'estado'=>$item['estado'],
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
        if(isset($data_item['codigo'])){
            $sql = "SELECT *
                    FROM item
                    WHERE codigo=:codigo AND id!=:id_item";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':codigo', $data_item['codigo'], PDO::PARAM_STR);
            $res->bindParam(':id_item', $id_item, PDO::PARAM_STR);
            $res->execute();
            if($res->rowCount()>0){
                //$resp = array('success'=>false,'message'=>'Error, el codigo de la item ya existe en otro registro');
                $success=false;
                $resp += ['codigo' => 'error, ya existe registro'];
            }else{
                $sql = "UPDATE item 
                        SET codigo=:codigo,
                        f_mod=now(), 
                        u_mod=:u_mod
                        WHERE id=:id_item;";
                $res = ($this->db)->prepare($sql);
                $res->bindParam(':id_item', $id_item, PDO::PARAM_STR);
                $res->bindParam(':codigo', $data_item['codigo'], PDO::PARAM_STR);
                $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
                $res->execute();
                $resp += ['codigo' => 'dato actualizado'];
            }
        }
        if(isset($data_item['nombre'])){
            $sql = "UPDATE item 
                        SET nombre=:nombre,
                        f_mod=now(), 
                        u_mod=:u_mod
                        WHERE id=:id_item;";
                $res = ($this->db)->prepare($sql);
                $res->bindParam(':id_item', $id_item, PDO::PARAM_STR);
                $res->bindParam(':nombre', $data_item['nombre'], PDO::PARAM_STR);
                $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
                $res->execute();
                $resp += ['nombre' => 'dato actualizado'];
        }
        if(isset($data_item['gestion'])){
            $sql = "UPDATE item 
                        SET gestion=:gestion,
                        f_mod=now(), 
                        u_mod=:u_mod
                        WHERE id=:id_item;";
                $res = ($this->db)->prepare($sql);
                $res->bindParam(':id_item', $id_item, PDO::PARAM_STR);
                $res->bindParam(':gestion', $data_item['gestion'], PDO::PARAM_STR);
                $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
                $res->execute();
                $resp += ['gestion' => 'dato actualizado'];
        }
        if(isset($data_item['descripcion'])){
            $sql = "UPDATE item 
                        SET descripcion=:descripcion,
                        f_mod=now(), 
                        u_mod=:u_mod
                        WHERE id=:id_item;";
                $res = ($this->db)->prepare($sql);
                $res->bindParam(':id_item', $id_item, PDO::PARAM_STR);
                $res->bindParam(':descripcion', $data_item['descripcion'], PDO::PARAM_STR);
                $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
                $res->execute();
                $resp += ['descripcion' => 'dato actualizado'];
        }
        if(isset($data_item['estado'])){
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
        }
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
        $res->bindParam(':id_producto', $data_item['id_producto'], PDO::PARAM_STR);
        $res->bindParam(':id_entrada_salida', $data_item['id_entrada_salida'], PDO::PARAM_STR);
        $res->bindParam(':tipo_in_out', $data_item['tipo_in_out'], PDO::PARAM_STR);
        $res->bindParam(':codigo_prod', $data_item['codigo_prod'], PDO::PARAM_STR);
        $res->bindParam(':nombre_prod', $data_item['nombre_prod'], PDO::PARAM_STR);
        $res->bindParam(':registro_sanitario', $data_item['registro_sanitario'], PDO::PARAM_STR);
        $res->bindParam(':lote', $data_item['lote'], PDO::PARAM_STR);
        $res->bindParam(':fecha_exp', $data_item['fecha_exp'], PDO::PARAM_STR);
        $res->bindParam(':cantidad', $data_item['cantidad'], PDO::PARAM_STR);
        $res->bindParam(':precio_factura', $data_item['precio_factura'], PDO::PARAM_STR);
        $res->bindParam(':precio_unidad_fob', $data_item['precio_unidad_fob'], PDO::PARAM_STR);
        $res->bindParam(':precio_total', $data_item['precio_total'], PDO::PARAM_STR);
        $res->bindParam(':factor', $data_item['factor'], PDO::PARAM_STR);
        $res->bindParam(':costo_almacen', $data_item['costo_almacen'], PDO::PARAM_STR);
        $res->bindParam(':costo_neto', $data_item['costo_neto'], PDO::PARAM_STR);
        $res->bindParam(':precio_venta', $data_item['precio_venta'], PDO::PARAM_STR);
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
        $fecha = explode("-",$res['fecha_exp']);
        $result = array('id'=>$res['id'],
                        'id_producto'=>$res['id_producto'],
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
                        'factor'=>$res['factor'],
                        'costo_almacen'=>$res['costo_almacen'],
                        'costo_neto'=>$res['costo_neto'],
                        'precio_venta'=>$res['precio_venta'],
                        'activo'=>$res['activo']);
        $resp = array('success'=>true,'message'=>'item registrado exitosamente','data_item'=>$result);
        return $resp;
    }

    public function calculatePriceItem($data_precio){

        $price = $data_precio['precio_unidad_fob'];
        $factor = $data_precio['factor'];	
        $qty = $data_precio['cantidad'];
        
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
        $result+=['total'=>$total];
        $result+=['precio_venta'=>$precio_venta];
        $result+=['costo_almacen'=>$costo_almacen];
        $result+=['costo_neto'=>$costo_neto];
        return array('success'=>true,'message'=>'calculo realizado exitosamente','data_calculo'=>$result);
    }
}
