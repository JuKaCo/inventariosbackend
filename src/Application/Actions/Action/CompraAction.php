<?php

declare(strict_types=1);

namespace App\Application\Actions\Action;

use App\Application\Actions\Action;
use App\Domain\CompraRepository;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CompraAction extends Action {

    protected $compraRepository;

    /**
     * @param LoggerInterface $logger
     * @param CompraRepository  $compraRepository
     */
    public function __construct(LoggerInterface $logger, CompraRepository $compraRepository) {
        parent::__construct($logger);
        $this->compraRepository = $compraRepository;
    }

    public function action(): Response {
        $users = array();
        $this->logger->info("Compraes list was viewed.");
        return $this->respondWithData($users);
    }

    public function obtiene_Compra(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_compra = $args['id_compra'];
        $res=$this->compraRepository->getCompra($id_compra);
        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,true);
        }else{
            return $this->respondWithData($res['data_compra'],$res['message'],200,true);
        }
    }

    public function lista_Compra(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $query=$request->getQueryParams();

        $res=$this->compraRepository->listCompra($query);

        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,true);
        }else{
            return $this->respondWithData($res['data_compra'],$res['message'],200,true);
        }
    }

    /* body servicio de edicion de compra
    {
        'id_compra':int,
        'codigo':string,
        'nombre':string,
        'pais':array,
        'direccion':string,
        'comentarios':string,
        'activo':int
    } 
    */
    public function edita_Compra(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_compra = $args['id_compra'];
        $data_compra =  $request->getParsedBody();

        $token=getenv('TOKEN_DATOS');
        $token=json_decode($token,true);
        $uuid=($token['sub']);

        $res=$this->compraRepository->editCompra($id_compra,$data_compra,$uuid);

        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,false);
        }else{
            return $this->respondWithData($res['data_compra'],$res['message'],200,true);
        }
    }

    public function modifica_Compra(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_compra = $args['id_compra'];
        $data_compra =  $request->getParsedBody();

        $token=getenv('TOKEN_DATOS');
        $token=json_decode($token,true);
        $uuid=($token['sub']);

        $res=$this->compraRepository->modifyCompra($id_compra,$data_compra,$uuid);

        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,false);
        }else{
            return $this->respondWithData($res['data_compra'],$res['message'],200,true);
        }
    }

    public function cambiaestado_Compra(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_compra = $args['id_compra'];
        $token=getenv('TOKEN_DATOS');
        $token=json_decode($token,true);
        $uuid=($token['sub']);
        $res=$this->compraRepository->changestatusCompra($id_compra,$uuid);
        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,true);
        }else{
            return $this->respondWithData(null,$res['message'],200,true);
        }
        
    }

    /* body servicio de creacion compraes
    {
        'codigo':string,
        'nombre':string,
        'pais':array,
        'direccion':string,
        'comentarios':string
    } 
    */
    public function crea_Compra(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $data_compra =  $request->getParsedBody();

        $token=getenv('TOKEN_DATOS');
        $token=json_decode($token,true);
        $uuid=($token['sub']);
        
        $res=$this->compraRepository->createCompra($data_compra,$uuid);

        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,false);
        }else{
            return $this->respondWithData($res['data_compra'],$res['message'],200,true);
        }
    }
}