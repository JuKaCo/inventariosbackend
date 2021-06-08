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
use App\Infrastructure\Persistence\DataItemSecRepository;
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
        $this->DataItemSecRepository = new DataItemSecRepository;
        $this->dataKardexRepository = new DataKardexRepository;
    }

    public function getCotizacion($id_cotizacion,$token): array {
        if(!($this->verificaPermisos($id_cotizacion,null,$token))){
            return array('success'=>false,'message'=>'usuario no autorizado','code'=>403,'data_cotizacion'=>array());
        }
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
            $data_almacen = $this->dataAlmacenRepository->getAlmacen($res['id_almacen'],$token);
            $data_cliente = $this->dataClienteRepository->getCliente($res['id_cliente'],$token);
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

            $resp = array('success'=>true,'message'=>'Exito','data_cotizacion'=>$result,'code'=>200);
        }else{
            $resp = array('success'=>false,'message'=>'No se encontraron registros','data_cotizacion'=>array(),'code'=>202);
        }
        return $resp;
    }

    public function listCotizacion($query,$token): array {
        if(!(isset($query['filtro'])&&isset($query['limite'])&&isset($query['indice']))){
            return array('success'=>false,'message'=>'Datos invalidos','data_cotizacion'=>array(),'code'=>202);
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
                FROM cotizacion co, cliente cl
                WHERE co.id_cliente=cl.id AND co.activo=1 AND ".$filtro_regional."(
                LOWER(co.codigo) LIKE LOWER(:filtro) OR LOWER(co.dias_validez) LIKE LOWER(:filtro) OR LOWER(co.comentarios) LIKE LOWER(:filtro) OR LOWER(cl.nombre) LIKE LOWER(:filtro))";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':filtro', $filter, PDO::PARAM_STR);
        $res->execute();
        $total=$res->rowCount();
        $sql = "SELECT co.*
                FROM cotizacion co, cliente cl
                WHERE co.id_cliente=cl.id AND co.activo=1 AND ".$filtro_regional."(
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
                $data_almacen = $this->dataAlmacenRepository->getAlmacen($res['id_almacen'],$token);
                $data_cliente = $this->dataClienteRepository->getCliente($res['id_cliente'],$token);
                
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
            $resp = array('success'=>true,'message'=>'Exito','data_cotizacion'=>$concat,'code'=>200);
        }else{
            $concat=array('resultados'=>array(),'total'=>0);
            $resp = array('success'=>true,'message'=>'No se encontraron registros','data_cotizacion'=>$concat,'code'=>200);
        }
        return $resp;
    }

    public function editCotizacion($id_cotizacion,$data_cotizacion,$token): array {
        return array();
    }

    public function changestatusCotizacion($id_cotizacion,$token): array {
        if(!($this->verificaPermisos($id_cotizacion,null,$token))){
            return array('success'=>false,'message'=>'usuario no autorizado','code'=>403,'data_cotizacion'=>array());
        }
        $sql = "UPDATE cotizacion 
                SET activo=0,
                f_inac=now(), 
                u_inac=:u_inac
                WHERE id=:id_cotizacion;";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':u_inac', $token->sub, PDO::PARAM_STR);
        $res->bindParam(':id_cotizacion', $id_cotizacion, PDO::PARAM_STR);
        $res->execute();
        //$res = $res->fetchAll(PDO::FETCH_ASSOC);
        if($res->rowCount()==1){
            $resp = array('success'=>true,'message'=>'1 fila afectada','code'=>200,'data_cotizacion'=>array());
        }else{
            $resp = array('success'=>false,'message'=>'0 fila afectada','code'=>202,'data_cotizacion'=>array());
        }
        return ($resp);
    }

    public function createCotizacion($data_cotizacion,$token): array {
        if(!(isset($data_cotizacion['id_regional'])&&isset($data_cotizacion['id_almacen'])
        &&isset($data_cotizacion['dias_validez'])&&isset($data_cotizacion['id_cliente'])
        &&isset($data_cotizacion['comentarios']))){
            return array('success'=>false,'message'=>'Datos invalidos','data_cotizacion'=>array(),'code'=>202);
        }
        if(!($this->verificaPermisos(null,$data_cotizacion['id_regional']['id'],$token))){
            return array('success'=>false,'message'=>'usuario no autorizado','code'=>403,'data_cotizacion'=>array());
        }
        
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
        $correlativo = "sin codigo";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':uuid', $uuid_neo, PDO::PARAM_STR);
        $res->bindParam(':codigo', $correlativo, PDO::PARAM_STR);
        $res->bindParam(':id_regional', $data_cotizacion['id_regional']['id'], PDO::PARAM_STR);
        $res->bindParam(':id_almacen', $data_cotizacion['id_almacen']['id'], PDO::PARAM_STR);
        $res->bindParam(':id_cliente', $data_cotizacion['id_cliente']['id'], PDO::PARAM_STR);
        $res->bindParam(':dias_validez', $data_cotizacion['dias_validez'], PDO::PARAM_INT);
        $res->bindParam(':comentarios', $data_cotizacion['comentarios'], PDO::PARAM_STR);
        $res->bindParam(':u_crea', $token->sub, PDO::PARAM_STR);
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
        $resp = array('success'=>true,'message'=>'cotizacion registrada exitosamente','data_cotizacion'=>$result,'code'=>200);
        return $resp;
    }

    public function modifyCotizacion($id_cotizacion,$data_cotizacion,$token): array {
        
        if(!($this->verificaPermisos($id_cotizacion,(isset($data_cotizacion['id_regional']['id']))?$data_cotizacion['id_regional']['id']:null,$token))){
            return array('success'=>false,'message'=>'usuario no autorizado','code'=>403,'data_cotizacion'=>array());
        }
        $codigo=false;
        $resp=array();

        $sql = "SELECT *
                FROM cotizacion
                WHERE id=:id_cotizacion";
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':id_cotizacion', $id_cotizacion, PDO::PARAM_STR);
        $res->execute();
        $res = $res->fetchAll(PDO::FETCH_ASSOC);
        $dato_ant=$res[0];

        if(isset($data_cotizacion['id_regional'])){

            /*$correlativo = $this->dataCorrelativoRepository->genCorrelativo($data_cotizacion['id_regional']['codigo'], 'COT', $token->sub);
            $correlativo = $correlativo['correlativo'];
            $correlativo = $data_cotizacion['id_regional']['codigo'] . '-COT-' . $correlativo;*/

            $sql = "UPDATE cotizacion 
                    SET id_regional=:id_regional,
                    f_mod=now(), 
                    u_mod=:u_mod
                    WHERE id=:id_cotizacion;";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':id_cotizacion', $id_cotizacion, PDO::PARAM_STR);
            $res->bindParam(':id_regional', $data_cotizacion['id_regional']['id'], PDO::PARAM_STR);
            //$res->bindParam(':codigo', $correlativo, PDO::PARAM_STR);
            $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
            $res->execute();
            $resp += ['id_regional' => 'dato actualizado'];
        }

        if(isset($data_cotizacion['id_almacen'])){
            if($dato_ant['id_regional']!=$data_cotizacion['id_almacen']['id']){
                $sql = "UPDATE cotizacion 
                        SET id_almacen=:id_almacen,
                        f_mod=now(), 
                        u_mod=:u_mod
                        WHERE id=:id_cotizacion;";
                $res = ($this->db)->prepare($sql);
                $res->bindParam(':id_cotizacion', $id_cotizacion, PDO::PARAM_STR);
                $res->bindParam(':id_almacen', $data_cotizacion['id_almacen']['id'], PDO::PARAM_STR);
                $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
                $res->execute();
                
                // se borre en cascada los items secundarios asociados a la cotizacion
                $query=array(
                    'filtro'=>'',
                    'limite'=>100000000000,
                    'indice'=>0
                );
                $data_items = $this->DataItemSecRepository->listItemSec($query,$id_cotizacion);
                $data_items = $data_items['data_itemsec']['resultados'];

                foreach($data_items as $item){
                    $this->DataItemSecRepository->changestatusItemSec($item['id'],$token->sub);
                }
                $resp += ['id_almacen' => 'dato actualizado, items asociados eliminados'];
            }
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
            $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
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
            $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
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
            $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
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
            $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
            $res->execute();
            $resp += ['estado' => 'dato actualizado'];
            if($data_cotizacion['estado']=='VIGENTE'){
                //tomamos en cuenta esta data para los productos comprometidos, ademas asignamos un codigo a la cotizacion.
                $sql = "SELECT reg.codigo as cod_regional
                        FROM cotizacion c, regional reg
                        WHERE c.id=:id_cotizacion AND reg.id=c.id_almacen";
                $res = ($this->db)->prepare($sql);
                $res->bindParam(':id_cotizacion', $id_cotizacion, PDO::PARAM_STR);
                
                $res->execute();
                $res = $res->fetchAll(PDO::FETCH_ASSOC);
                $res = $res[0];

                $correlativo = $this->dataCorrelativoRepository->genCorrelativo($res['cod_regional'],'-COT', $token->sub);
                $correlativo = $correlativo['correlativo'];
                $correlativo = $res['cod_regional'] . '-COT-' . $correlativo;
                $sql = "UPDATE entrada 
                        SET codigo=:codigo,
                        f_mod=now(), 
                        u_mod=:u_mod
                        WHERE id=:id_cotizacion;";
                $res = ($this->db)->prepare($sql);
                $res->bindParam(':id_cotizacion', $id_cotizacion, PDO::PARAM_STR);
                $res->bindParam(':codigo', $correlativo, PDO::PARAM_STR);
                $res->bindParam(':u_mod', $token->sub, PDO::PARAM_STR);
                $res->execute();
                //$resp += ['id_almacen' => 'dato actualizado'];
                $codigo=true;
            }
        }

        if($codigo){
            $resp += ['codigo' => 'dato actualizado'];
        }
        $resp = array('success'=>true,'message'=>'datos actualizados','data_cotizacion'=>$resp,'code'=>200);
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
                        FROM cotizacion
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
