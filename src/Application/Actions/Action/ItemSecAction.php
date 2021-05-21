<?php

declare(strict_types=1);

namespace App\Application\Actions\Action;

use App\Application\Actions\Action;
use App\Domain\ItemSecRepository;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ItemSecAction extends Action {

    protected $itemsecRepository;

    /**
     * @param LoggerInterface $logger
     * @param ItemSecRepository  $itemsecRepository
     */
    public function __construct(LoggerInterface $logger, ItemSecRepository $itemsecRepository) {
        parent::__construct($logger);
        $this->itemsecRepository = $itemsecRepository;
    }

    public function action(): Response {
        $users = array();
        $this->logger->info("ItemSeces list was viewed.");
        return $this->respondWithData($users);
    }

    public function obtiene_ItemSec(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_itemsec = $args['id_itemsec'];
        $res=$this->itemsecRepository->getItemSec($id_itemsec);
        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,true);
        }else{
            return $this->respondWithData($res['data_itemsec'],$res['message'],200,true);
        }
    }

    public function lista_ItemSec(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $query=$request->getQueryParams();
        $id_entrada_salida = $args['id_entrada_salida'];
        $res=$this->itemsecRepository->listItemSec($query, $id_entrada_salida);

        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,true);
        }else{
            return $this->respondWithData($res['data_itemsec'],$res['message'],200,true);
        }
    }

    /* body servicio de edicion de itemsec
    {
        'id_itemsec':int,
        'codigo':string,
        'nombre':string,
        'pais':array,
        'direccion':string,
        'comentarios':string,
        'activo':int
    } 
    */
    public function edita_ItemSec(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_itemsec = $args['id_itemsec'];
        $data_itemsec =  $request->getParsedBody();

        $JWT = new \App\Application\Middleware\JWTdata($request);
        $token = $JWT->getToken();
        if (!$token['success']) {
            return $this->respondWithData(array(), 'Datos de token invalidos', 403,false);
        }
        $token = $token['data'];
        $uuid=$token->sub;

        $res=$this->itemsecRepository->editItemSec($id_itemsec,$data_itemsec,$uuid);

        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,false);
        }else{
            return $this->respondWithData($res['data_itemsec'],$res['message'],200,true);
        }
    }

    public function modifica_ItemSec(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_itemsec = $args['id_itemsec'];
        $data_itemsec =  $request->getParsedBody();

        $JWT = new \App\Application\Middleware\JWTdata($request);
        $token = $JWT->getToken();
        if (!$token['success']) {
            return $this->respondWithData(array(), 'Datos de token invalidos', 403,false);
        }
        $token = $token['data'];
        $uuid=$token->sub;

        $res=$this->itemsecRepository->modifyItemSec($id_itemsec,$data_itemsec,$uuid);

        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,false);
        }else{
            return $this->respondWithData($res['data_itemsec'],$res['message'],200,true);
        }
    }

    public function cambiaestado_ItemSec(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_itemsec = $args['id_itemsec'];
        $JWT = new \App\Application\Middleware\JWTdata($request);
        $token = $JWT->getToken();
        if (!$token['success']) {
            return $this->respondWithData(array(), 'Datos de token invalidos', 403,false);
        }
        $token = $token['data'];
        $uuid=$token->sub;
        $res=$this->itemsecRepository->changestatusItemSec($id_itemsec,$uuid);
        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,true);
        }else{
            return $this->respondWithData(null,$res['message'],200,true);
        }
        
    }

    /* body servicio de creacion itemseces
    {
        'codigo':string,
        'nombre':string,
        'pais':array,
        'direccion':string,
        'comentarios':string
    } 
    */
    public function crea_ItemSec(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $data_itemsec =  $request->getParsedBody();

        $JWT = new \App\Application\Middleware\JWTdata($request);
        $token = $JWT->getToken();
        if (!$token['success']) {
            return $this->respondWithData(array(), 'Datos de token invalidos', 403,false);
        }
        $token = $token['data'];
        $uuid=$token->sub;
        
        $res=$this->itemsecRepository->createItemSec($data_itemsec,$uuid);

        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,false);
        }else{
            return $this->respondWithData($res['data_itemsec'],$res['message'],200,true);
        }
    }

    public function calcula_Precio_ItemSec(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $data_itemsec =  $request->getParsedBody();
        
        $res=$this->itemsecRepository->calculatePriceItemSec($data_itemsec);

        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,false);
        }else{
            return $this->respondWithData($res['data_calculo'],$res['message'],200,true);
        }
    }
}