<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Actions\RepositoryConection\Conect;
use App\Domain\ItemSecRepository;
use App\Infrastructure\Persistence\DataCorrelativoRepository;
use App\Infrastructure\Persistence\DataProductoRepository;
use App\Infrastructure\Persistence\DataParametricaRepository;
use App\Infrastructure\Persistence\DataKardexRepository;
use App\Infrastructure\Persistence\DataProveedorRepository;
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
        $this->dataKardexRepository = new DataKardexRepository;
        $this->dataProveedorRepository = new DataProveedorRepository;
    }

    public function getItemSec($id_itemsec): array {
        $sql = "SELECT i.*
                FROM item_secundario i
                WHERE i.id=:id_itemsec AND i.activo=1";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':id_itemsec', $id_itemsec, PDO::PARAM_STR);
        $res->execute();
        if($res->rowCount()>0){
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $res = $res[0];
            
            $data_producto = $this->dataProductoRepository->getProducto($res['id_producto_inventario']);
            $data_producto = $data_producto['data_producto'];
            $data_proveedor = $this->dataProveedorRepository->getProveedor($res['id_proveedor']);

            //$data_factor = $data_factor[0];
            $fecha = explode("-",$res['fecha_exp']);
            $result = array('id'=>$res['id'],
                        'id_producto'=>$data_producto,
                        'id_coti_vent'=>$res['id_coti_vent'],
                        'tipo'=>$res['tipo'],
                        'codigo_prod'=>$res['codigo_prod'],
                        'nombre_prod'=>$res['nombre_prod'],
                        'id_proveedor'=>$data_proveedor['data_proveedor'],
                        'lote'=>$res['lote'],
                        'fecha_exp'=>$fecha[2]."/".$fecha[1]."/".$fecha[0],
                        'cantidad'=>$res['cantidad'],
                        'precio_venta'=>(float)$res['precio_venta'],
                        'precio_total'=>(float)$res['precio_total'],
                        'activo'=>$res['activo']);
            $resp = array('success'=>true,'message'=>'Exito','data_itemsec'=>$result);
        }else{
            $resp = array('success'=>false,'message'=>'No se encontraron registros');
        }
        return $resp;
    }

    public function listItemSec($query,$id_coti_vent): array {

        if(!(isset($query['filtro'])&&isset($query['limite'])&&isset($query['indice']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        $filtro=$query['filtro'];
        $limite=$query['limite'];
        $indice=$query['indice'];
        $limite=$limite+$indice;
        $filter="%".strtolower($filtro)."%";
        $sql = "SELECT i.id
                FROM item_secundario i
                WHERE i.activo=1 AND i.id_coti_vent=:id_coti_vent AND
                (LOWER(i.nombre_prod) LIKE LOWER(:filter) OR LOWER(i.codigo_prod) LIKE LOWER(:filter) OR LOWER(i.lote) LIKE LOWER(:filter) )";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':filter', $filter, PDO::PARAM_STR);
        $res->bindParam(':id_coti_vent', $id_coti_vent, PDO::PARAM_STR);
        $res->execute();
        $total=$res->rowCount();
        $sql = "SELECT i.*
                FROM item_secundario i
                WHERE i.activo=1 AND i.id_coti_vent=:id_coti_vent AND
                (LOWER(i.nombre_prod) LIKE LOWER(:filter) OR LOWER(i.codigo_prod) LIKE LOWER(:filter) OR LOWER(i.lote) LIKE LOWER(:filter) )
                ORDER BY i.f_crea DESC
                LIMIT :indice, :limite;";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':filter', $filter, PDO::PARAM_STR);
        $res->bindParam(':id_coti_vent', $id_coti_vent, PDO::PARAM_STR);
        $res->bindParam(':limite', $limite, PDO::PARAM_INT);
        $res->bindParam(':indice', $indice, PDO::PARAM_INT);
        $res->execute();
        if($res->rowCount()>0){
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $arrayres = array();
            foreach ($res as $itemsec){
                $data_producto = $this->dataProductoRepository->getProducto($itemsec['id_producto_inventario']);
                $data_proveedor = $this->dataProveedorRepository->getProveedor($itemsec['id_proveedor']);
                $data_producto = $data_producto['data_producto'];
                $fecha = explode("-",$itemsec['fecha_exp']);
                $result = array('id'=>$itemsec['id'],
                            'id_producto_inventario'=>$data_producto,
                            'id_coti_vent'=>$itemsec['id_coti_vent'],
                            'tipo'=>$itemsec['tipo'],
                            'codigo_prod'=>$itemsec['codigo_prod'],
                            'nombre_prod'=>$itemsec['nombre_prod'],
                            'id_proveedor'=>$data_proveedor['data_proveedor'],
                            'lote'=>$itemsec['lote'],
                            'fecha_exp'=>$fecha[2]."/".$fecha[1]."/".$fecha[0],
                            'cantidad'=>$itemsec['cantidad'],
                            'precio_venta'=>(float)$itemsec['precio_venta'],
                            'precio_total'=>(float)$itemsec['precio_total'],
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
        if(isset($data_itemsec['cantidad'])){
            $sql = "UPDATE item_secundario 
                    SET cantidad=:cantidad,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_itemsec;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_itemsec', $id_itemsec, PDO::PARAM_STR);
            $res->bindParam(':cantidad', $data_itemsec['cantidad'], PDO::PARAM_STR);
            $res->bindParam(':u_mod', $uuid, PDO::PARAM_STR);
            $res->execute();
            $resp += ['cantidad' => 'dato actualizado'];
        }

        $resp = array('success'=>$success,'message'=>'datos actualizados','data_itemsec'=>$resp);
        return $resp;
    }

    public function changestatusItemSec($id_itemsec,$uuid): array {
        $sql = "UPDATE item_secundario 
                SET activo=0,
                f_inac=now(), 
                u_inac=:u_inac
                WHERE id=:id_itemsec;";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':u_inac', $uuid, PDO::PARAM_STR);
        $res->bindParam(':id_itemsec', $id_itemsec, PDO::PARAM_STR);
        $res->execute();
        if($res->rowCount()==1){
            $resp = array('success'=>true,'message'=>'1 fila afectada');
        }else{
            $resp = array('success'=>false,'message'=>'0 fila afectada');
        }
        return ($resp);

    }

    public function createItemSec($data_itemsec,$uuid): array {
        if(!(isset($data_itemsec['id_producto_inventario'])&&isset($data_itemsec['id_coti_vent'])&&isset($data_itemsec['tipo'])&&isset($data_itemsec['cantidad']))){
            return array('success'=>false,'message'=>'Datos invalidos');
        }
        $uuid_neo=Uuid::v4();
        $sql = "INSERT INTO item_secundario (
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
        $total=((integer)$data_itemsec['cantidad'])*((float)$data_itemsec['id_producto_inventario']['precio_venta']);
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':uuid', $uuid_neo, PDO::PARAM_STR);
        $res->bindParam(':id_producto_inventario', $data_itemsec['id_producto_inventario']['id_producto']['id'], PDO::PARAM_STR);
        $res->bindParam(':id_coti_vent', $data_itemsec['id_coti_vent'], PDO::PARAM_STR);
        $res->bindParam(':tipo', $data_itemsec['tipo'], PDO::PARAM_STR);
        $res->bindParam(':codigo_prod', $data_itemsec['id_producto_inventario']['id_producto']['codigo'], PDO::PARAM_STR);
        $res->bindParam(':nombre_prod', $data_itemsec['id_producto_inventario']['id_producto']['nombre_comercial'], PDO::PARAM_STR);
        $res->bindParam(':id_proveedor', $data_itemsec['id_producto_inventario']['id_proveedor']['id'], PDO::PARAM_STR);
        $res->bindParam(':lote', $data_itemsec['id_producto_inventario']['lote'], PDO::PARAM_STR);
        $res->bindParam(':fecha_exp', $data_itemsec['id_producto_inventario']['fecha_exp'], PDO::PARAM_STR);
        $res->bindParam(':cantidad', $data_itemsec['cantidad'], PDO::PARAM_STR);
        $res->bindParam(':precio_venta', $data_itemsec['id_producto_inventario']['precio_venta'], PDO::PARAM_STR);
        $res->bindParam(':precio_total', $total, PDO::PARAM_STR);    
        $res->bindParam(':u_crea', $uuid, PDO::PARAM_STR);
        $res->execute();

        $sql = "SELECT *
                FROM item_secundario
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
