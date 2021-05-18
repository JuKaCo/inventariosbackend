<?php

declare(strict_types=1);

namespace App\Application\Actions\Action;

use App\Application\Actions\Action;
use App\Domain\ProgramaRepository;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProgramaAction extends Action {

    protected $programaRepository;

    /**
     * @param LoggerInterface $logger
     * @param ProgramaRepository  $programaRepository
     */
    public function __construct(LoggerInterface $logger, ProgramaRepository $programaRepository) {
        parent::__construct($logger);
        $this->programaRepository = $programaRepository;
    }

    public function action(): Response {
        $users = array();
        $this->logger->info("Programa list was viewed.");
        return $this->respondWithData($users);
    }

    public function obtiene_Programa(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_programa = $args['id_programa'];
        $res=$this->programaRepository->getPrograma($id_programa);
        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,true);
        }else{
            return $this->respondWithData($res['data_programa'],$res['message'],200,true);
        }
    }

    public function lista_Programa(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $query=$request->getQueryParams();

        $res=$this->programaRepository->listPrograma($query);

        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,true);
        }else{
            return $this->respondWithData($res['data_programa'],$res['message'],200,true);
        }
    }

    /* body servicio de edicion de programa
    {
        'id_programa':int,
        'codigo':string,
        'nombre':string,
        'pais':array,
        'direccion':string,
        'comentarios':string,
        'activo':int
    } 
    */
    public function edita_Programa(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_programa = $args['id_programa'];
        $data_programa =  $request->getParsedBody();

        $JWT = new \App\Application\Middleware\JWTdata($request);
        $token = $JWT->getToken();
        if (!$token['success']) {
            return $this->respondWithData(array(), 'Datos de token invalidos', 403,false);
        }
        $token = $token['data'];
        $uuid=$token->sub;

        $res=$this->programaRepository->editPrograma($id_programa,$data_programa,$uuid);

        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,false);
        }else{
            return $this->respondWithData($res['data_programa'],$res['message'],200,true);
        }
    }

    public function cambiaestado_Programa(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_programa = $args['id_programa'];
        $JWT = new \App\Application\Middleware\JWTdata($request);
        $token = $JWT->getToken();
        if (!$token['success']) {
            return $this->respondWithData(array(), 'Datos de token invalidos', 403,false);
        }
        $token = $token['data'];
        $uuid=$token->sub;
        $res=$this->programaRepository->changestatusPrograma($id_programa,$uuid);
        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,true);
        }else{
            return $this->respondWithData(null,$res['message'],200,true);
        }
        
    }

    /* body servicio de creacion programaes
    {
        'codigo':string,
        'nombre':string,
        'pais':array,
        'direccion':string,
        'comentarios':string
    } 
    */
    public function crea_Programa(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $data_programa =  $request->getParsedBody();

        $JWT = new \App\Application\Middleware\JWTdata($request);
        $token = $JWT->getToken();
        if (!$token['success']) {
            return $this->respondWithData(array(), 'Datos de token invalidos', 403,false);
        }
        $token = $token['data'];
        $uuid=$token->sub;
        
        $res=$this->programaRepository->createPrograma($data_programa,$uuid);

        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,false);
        }else{
            return $this->respondWithData($res['data_programa'],$res['message'],200,true);
        }
    }
}