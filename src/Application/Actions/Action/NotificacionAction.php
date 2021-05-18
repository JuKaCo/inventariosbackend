<?php

declare(strict_types=1);

namespace App\Application\Actions\Action;

use App\Application\Actions\Action;
use App\Domain\NotificacionRepository;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class NotificacionAction extends Action {

    protected $notificacionRepository;

    /**
     * @param LoggerInterface $logger
     * @param NotificacionRepository  $notificacionRepository
     */
    public function __construct(LoggerInterface $logger, NotificacionRepository $notificacionRepository) {
        parent::__construct($logger);
        $this->notificacionRepository = $notificacionRepository;
    }

    public function action(): Response {
        $users = array();
        $this->logger->info("Users list was viewed.");
        return $this->respondWithData($users);
    }

    public function lista_notificacion_simple(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        //get token  -> id_usuario
        $JWT = new \App\Application\Middleware\JWTdata($request);
        $token = $JWT->getToken();
        if (!$token['success']) {
            return $this->respondWithData(array(), 'Datos de token invalidos', 403,false);
        }
        $token = $token['data'];
        $id_usuario=$token->sub;

        $res=$this->notificacionRepository->getNotificacionSimple($id_usuario);

        if($res['success']==false){
            return $this->respondWithData([],$res['message'],202,true);
        }else{
            return $this->respondWithData($res['data_notificacion'],$res['message'],200,true);
        }
    }


    public function lista_notificacion(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        //get token  -> id_usuario
        $JWT = new \App\Application\Middleware\JWTdata($request);
        $token = $JWT->getToken();
        if (!$token['success']) {
            return $this->respondWithData(array(), 'Datos de token invalidos', 403,false);
        }
        $token = $token['data'];
        $id_usuario=$token->sub;
        $query=$request->getQueryParams();
        $res=$this->notificacionRepository->getNotificacion($id_usuario, $query);

        if($res['success']==false){
            return $this->respondWithData([],$res['message'],202,true);
        }else{
            return $this->respondWithData($res['data_notificacion'],$res['message'],200,true);
        }
    }

    public function lista_id_notificacion(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_notificacion = $args['id_notificacion'];
        //get token  -> id_usuario
        $JWT = new \App\Application\Middleware\JWTdata($request);
        $token = $JWT->getToken();
        if (!$token['success']) {
            return $this->respondWithData(array(), 'Datos de token invalidos', 403,false);
        }
        $token = $token['data'];
        $id_usuario=$token->sub;

        $res=$this->notificacionRepository->getNotificacionId($id_usuario, $id_notificacion);

        return $this->respondWithData($res);
    }

    public function crea_notificacion(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $data_notificacion =  $request->getParsedBody();
        //get token  -> id_usuario
        $JWT = new \App\Application\Middleware\JWTdata($request);
        $token = $JWT->getToken();
        if (!$token['success']) {
            return $this->respondWithData(array(), 'Datos de token invalidos', 403,false);
        }
        $token = $token['data'];
        $id_usuario=$token->sub;

        $res=$this->notificacionRepository->createNotificacion($id_usuario, $data_notificacion);

        // return $this->respondWithData($res);

        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,true);
        }else{
            return $this->respondWithData($res['data_notificacion'],$res['message'],200,true);
        }
    }

    public function inactiva_notificacion(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_notificacion = $args['id_notificacion'];
        //get token  -> id_usuario
        $JWT = new \App\Application\Middleware\JWTdata($request);
        $token = $JWT->getToken();
        if (!$token['success']) {
            return $this->respondWithData(array(), 'Datos de token invalidos', 403,false);
        }
        $token = $token['data'];
        $id_usuario=$token->sub;

        $res=$this->notificacionRepository->inactivaNotificacion($id_usuario, $id_notificacion);

        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,true);
        }else{
            return $this->respondWithData($res['data_notificacion'],$res['message'],200,true);
        }
    }

    public function confirma_notificacion(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_notificacion = $args['id_notificacion'];
        //get token  -> id_usuario
        $JWT = new \App\Application\Middleware\JWTdata($request);
        $token = $JWT->getToken();
        if (!$token['success']) {
            return $this->respondWithData(array(), 'Datos de token invalidos', 403,false);
        }
        $token = $token['data'];
        $id_usuario=$token->sub;

        $res=$this->notificacionRepository->confirmaNotificacion($id_usuario, $id_notificacion);

        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,true);
        }else{
            return $this->respondWithData($res['data_notificacion'],$res['message'],200,true);
        }
    }

}
