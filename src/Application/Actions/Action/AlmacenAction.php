<?php

declare(strict_types=1);

namespace App\Application\Actions\Action;

use App\Application\Actions\Action;
use App\Domain\AlmacenRepository;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AlmacenAction extends Action {

    protected $almacenRepository;

    /**
     * @param LoggerInterface $logger
     * @param AlmacenRepository  $almacenRepository
     */
    public function __construct(LoggerInterface $logger, AlmacenRepository $almacenRepository) {
        parent::__construct($logger);
        $this->almacenRepository = $almacenRepository;
    }

    public function action(): Response {
        $users = array();
        $this->logger->info("Almacenes list was viewed.");
        return $this->respondWithData($users);
    }

    public function obtiene_Almacen(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_almacen = $args['id_almacen'];
        $res=$this->almacenRepository->getAlmacen($id_almacen);
        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,true);
        }else{
            return $this->respondWithData($res['data_almacen'],$res['message'],200,true);
        }
    }

    public function lista_Almacen(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $query=$request->getQueryParams();

        $res=$this->almacenRepository->listAlmacen($query);

        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,true);
        }else{
            return $this->respondWithData($res['data_almacen'],$res['message'],200,true);
        }
    }

    /* body servicio de edicion de almacen
    {
        'id_almacen':int,
        'codigo':string,
        'nombre':string,
        'pais':array,
        'direccion':string,
        'comentarios':string,
        'activo':int
    } 
    */
    public function edita_Almacen(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_almacen = $args['id_almacen'];
        $data_almacen =  $request->getParsedBody();

        $token=getenv('TOKEN_DATOS');
        $token=json_decode($token,true);
        $uuid=($token['sub']);

        $res=$this->almacenRepository->editAlmacen($id_almacen,$data_almacen,$uuid);

        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,false);
        }else{
            return $this->respondWithData($res['data_almacen'],$res['message'],200,true);
        }
    }

    public function cambiaestado_Almacen(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_almacen = $args['id_almacen'];
        $token=getenv('TOKEN_DATOS');
        $token=json_decode($token,true);
        $uuid=($token['sub']);
        $res=$this->almacenRepository->changestatusAlmacen($id_almacen,$uuid);
        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,true);
        }else{
            return $this->respondWithData(null,$res['message'],200,true);
        }
        
    }

    /* body servicio de creacion almacenes
    {
        'codigo':string,
        'nombre':string,
        'pais':array,
        'direccion':string,
        'comentarios':string
    } 
    */
    public function crea_Almacen(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $data_almacen =  $request->getParsedBody();

        $token=getenv('TOKEN_DATOS');
        $token=json_decode($token,true);
        $uuid=($token['sub']);
        
        $res=$this->almacenRepository->createAlmacen($data_almacen,$uuid);

        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,false);
        }else{
            return $this->respondWithData($res['data_almacen'],$res['message'],200,true);
        }
    }
}