<?php

declare(strict_types=1);

namespace App\Application\Actions\Action;

use App\Application\Actions\Action;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;
use App\Domain\DosificacionRepository;

class DosificacionAction extends Action {

    protected $dosificacionRepository;

    /**
     * @param LoggerInterface $logger
     * @param $repository  ?
     */
    public function __construct(LoggerInterface $logger, DosificacionRepository $rep) {
        parent::__construct($logger);
        $this->dosificacionRepository = $rep;
    }

    public function action(): Response {
        $users = array();
        $this->logger->info("ReporteAction");
        return $this->respondWithData($users);
    }

    public function obtiene_dosificacion(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_docificacion = $args['id_dosificacion'];
        
        $res=$this->dosificacionRepository->getDoficifacion($id_docificacion);

        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,false);
        }else{
            return $this->respondWithData($res['data_dosificacion'],$res['message'],200,true);
        }
    }

    public function lista_dosificacion(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $query=$request->getQueryParams();

        $res=$this->dosificacionRepository->listDoficifacion($query);

        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,true);
        }else{
            return $this->respondWithData($res['data_dosificacion'],$res['message'],200,true);
        }
    }


    public function crea_docificacion(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $data_docificacion =  $request->getParsedBody();

        $JWT = new \App\Application\Middleware\JWTdata($request);
        $token = $JWT->getToken();
        if (!$token['success']) {
            return $this->respondWithData(array(), 'Datos de token invalidos', 403,false);
        }
        $token = $token['data'];
        $uuid=$token->sub;
        
        $res=$this->dosificacionRepository->createDocificacion($data_docificacion,$uuid);

        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,false);
        }else{
            return $this->respondWithData($res['data_dosificacion'],$res['message'],200,true);
        }
    }

    public function edita_docificacion(Request $request, Response $response, $args): Response { 
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_docificacion = $args['id_dosificacion'];
        $data_docificacion =  $request->getParsedBody();

        $JWT = new \App\Application\Middleware\JWTdata($request);
        $token = $JWT->getToken();
        if (!$token['success']) {
            return $this->respondWithData(array(), 'Datos de token invalidos', 403,false);
        }
        $token = $token['data'];
        $uuid=$token->sub;
        
        $res=$this->dosificacionRepository->editDosificacion($id_docificacion, $data_docificacion, $uuid);

        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,false);
        }else{
            return $this->respondWithData($res['data_dosificacion'],$res['message'],200,true);
        }
    }

    public function modifica_docificacion(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_docificacion = $args['id_dosificacion'];
        $data_docificacion =  $request->getParsedBody();

        $JWT = new \App\Application\Middleware\JWTdata($request);
        $token = $JWT->getToken();
        if (!$token['success']) {
            return $this->respondWithData(array(), 'Datos de token invalidos', 403,false);
        }
        $token = $token['data'];
        $uuid=$token->sub;
        
        $res=$this->dosificacionRepository->modifyDosificacion($id_docificacion, $data_docificacion, $uuid);

        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,false);
        }else{
            return $this->respondWithData($res['data_dosificacion'],$res['message'],200,true);
        }
    }

    public function cambiaestado_dosificacion(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_docificacion = $args['id_dosificacion'];

        $JWT = new \App\Application\Middleware\JWTdata($request);
        $token = $JWT->getToken();
        if (!$token['success']) {
            return $this->respondWithData(array(), 'Datos de token invalidos', 403,false);
        }
        $token = $token['data'];
        $uuid=$token->sub;
        
        $res=$this->dosificacionRepository->changestatusDosificacion($id_docificacion, $uuid);

        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,false);
        }else{
            return $this->respondWithData(null,$res['message'],200,true);
        }
    }

    

}
