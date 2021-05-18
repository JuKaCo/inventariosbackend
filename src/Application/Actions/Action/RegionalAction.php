<?php

declare(strict_types=1);

namespace App\Application\Actions\Action;

use App\Application\Actions\Action;
use App\Domain\RegionalRepository;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RegionalAction extends Action {

    protected $regionalRepository;

    /**
     * @param LoggerInterface $logger
     * @param RegionalRepository  $regionalRepository
     */
    public function __construct(LoggerInterface $logger, RegionalRepository $regionalRepository) {
        parent::__construct($logger);
        $this->regionalRepository = $regionalRepository;
    }

    public function action(): Response {
        $users = array();
        $this->logger->info("Regionales list was viewed.");
        return $this->respondWithData($users);
    }

    public function obtiene_Regional(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_regional = $args['id_regional'];
        $res=$this->regionalRepository->getRegional($id_regional);
        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,true);
        }else{
            return $this->respondWithData($res['data_regional'],$res['message'],200,true);
        }
    }

    public function lista_Regional(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $query=$request->getQueryParams();

        $res=$this->regionalRepository->listRegional($query);

        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,true);
        }else{
            return $this->respondWithData($res['data_regional'],$res['message'],200,true);
        }
    }

    /* body servicio de edicion de regional
    {
        'id_regional':int,
        'codigo':string,
        'nombre':string,
        'pais':array,
        'direccion':string,
        'comentarios':string,
        'activo':int
    } 
    */
    public function edita_Regional(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_regional = $args['id_regional'];
        $data_regional =  $request->getParsedBody();

        $JWT = new \App\Application\Middleware\JWTdata($request);
        $token = $JWT->getToken();
        if (!$token['success']) {
            return $this->respondWithData(array(), 'Datos de token invalidos', 403,false);
        }
        $token = $token['data'];
        $uuid=$token->sub;

        $res=$this->regionalRepository->editRegional($id_regional,$data_regional,$uuid);

        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,false);
        }else{
            return $this->respondWithData($res['data_regional'],$res['message'],200,true);
        }
    }

    public function cambiaestado_Regional(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_regional = $args['id_regional'];
        $JWT = new \App\Application\Middleware\JWTdata($request);
        $token = $JWT->getToken();
        if (!$token['success']) {
            return $this->respondWithData(array(), 'Datos de token invalidos', 403,false);
        }
        $token = $token['data'];
        $uuid=$token->sub;
        $res=$this->regionalRepository->changestatusRegional($id_regional,$uuid);
        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,true);
        }else{
            return $this->respondWithData(null,$res['message'],200,true);
        }
        
    }

    /* body servicio de creacion regionales
    {
        'codigo':string,
        'nombre':string,
        'pais':array,
        'direccion':string,
        'comentarios':string
    } 
    */
    public function crea_Regional(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $data_regional =  $request->getParsedBody();

        $JWT = new \App\Application\Middleware\JWTdata($request);
        $token = $JWT->getToken();
        if (!$token['success']) {
            return $this->respondWithData(array(), 'Datos de token invalidos', 403,false);
        }
        $token = $token['data'];
        $uuid=$token->sub;
        
        $res=$this->regionalRepository->createRegional($data_regional,$uuid);

        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,false);
        }else{
            return $this->respondWithData($res['data_regional'],$res['message'],200,true);
        }
    }
}