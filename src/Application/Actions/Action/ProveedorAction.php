<?php

declare(strict_types=1);

namespace App\Application\Actions\Action;

use App\Application\Actions\Action;
use App\Domain\ProveedorRepository;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProveedorAction extends Action {

    protected $proveedorRepository;

    /**
     * @param LoggerInterface $logger
     * @param ProveedorRepository  $proveedorRepository
     */
    public function __construct(LoggerInterface $logger, ProveedorRepository $proveedorRepository) {
        parent::__construct($logger);
        $this->proveedorRepository = $proveedorRepository;
    }

    public function action(): Response {
        $users = array();
        $this->logger->info("Proveedores list was viewed.");
        return $this->respondWithData($users);
    }

    public function obtiene_Proveedor(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_proveedor = $args['id_proveedor'];
        $res=$this->proveedorRepository->getProveedor($id_proveedor);
        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,true);
        }else{
            return $this->respondWithData($res['data_proveedor'],$res['message'],200,true);
        }
    }

    public function lista_Proveedor(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $query=$request->getQueryParams();
        
        $filtro = $query['filtro'];
        if($filtro==null){
            $filtro='';
        }
        $limite = $query['limite']; 
        $indice = $query['indice']; 
        $res=$this->proveedorRepository->listProveedor($filtro,$limite,$indice);

        return $this->respondWithData($res);
    }
    /* body servicio de edicion de proveedor
    {
        'id_proveedor':int,
        'codigo':string,
        'nombre':string,
        'pais':string,
        'direccion':string,
        'comentarios':string,
        'activo':int
    } 
    */
    public function edita_Proveedor(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_proveedor = $args['id_proveedor'];
        $data_proveedor =  $request->getParsedBody();

        $token=getenv('TOKEN_DATOS');
        $token=json_decode($token,true);
        $uuid=($token['sub']);

        $res=$this->proveedorRepository->editProveedor($id_proveedor,$data_proveedor,$uuid);

        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,false);
        }else{
            return $this->respondWithData(null,$res['message'],200,true);
        }
    }

    public function cambiaestado_Proveedor(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_proveedor = $args['id_proveedor'];
        $estado = $args['estado'];

        $token=getenv('TOKEN_DATOS');
        $token=json_decode($token,true);

        $uuid=($token['sub']);
        
        $res=$this->proveedorRepository->changestatusProveedor($id_proveedor,$estado,$uuid);

        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,true);
        }else{
            return $this->respondWithData(null,$res['message'],200,true);
        }
        
    }

    /* body servicio de creacion proveedores
    {
        'codigo':string,
        'nombre':string,
        'pais':string,
        'direccion':string,
        'comentarios':string,
        'activo':int
    } 
    */
    public function crea_Proveedor(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $data_proveedor =  $request->getParsedBody();

        $token=getenv('TOKEN_DATOS');
        $token=json_decode($token,true);
        $uuid=($token['sub']);
        
        $res=$this->proveedorRepository->createProveedor($data_proveedor,$uuid);

        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,false);
        }else{
            return $this->respondWithData($res['data_proveedor'],$res['message'],200,true);
        }
    }

}