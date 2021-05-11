<?php

declare(strict_types=1);

namespace App\Application\Actions\Action;

use App\Application\Actions\Action;
use App\Domain\ItemRepository;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ItemAction extends Action {

    protected $itemRepository;

    /**
     * @param LoggerInterface $logger
     * @param ItemRepository  $itemRepository
     */
    public function __construct(LoggerInterface $logger, ItemRepository $itemRepository) {
        parent::__construct($logger);
        $this->itemRepository = $itemRepository;
    }

    public function action(): Response {
        $users = array();
        $this->logger->info("Itemes list was viewed.");
        return $this->respondWithData($users);
    }

    public function obtiene_Item(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_item = $args['id_item'];
        $res=$this->itemRepository->getItem($id_item);
        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,true);
        }else{
            return $this->respondWithData($res['data_item'],$res['message'],200,true);
        }
    }

    public function lista_Item(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $query=$request->getQueryParams();

        $res=$this->itemRepository->listItem($query);

        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,true);
        }else{
            return $this->respondWithData($res['data_item'],$res['message'],200,true);
        }
    }

    /* body servicio de edicion de item
    {
        'id_item':int,
        'codigo':string,
        'nombre':string,
        'pais':array,
        'direccion':string,
        'comentarios':string,
        'activo':int
    } 
    */
    public function edita_Item(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_item = $args['id_item'];
        $data_item =  $request->getParsedBody();

        $token=getenv('TOKEN_DATOS');
        $token=json_decode($token,true);
        $uuid=($token['sub']);

        $res=$this->itemRepository->editItem($id_item,$data_item,$uuid);

        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,false);
        }else{
            return $this->respondWithData($res['data_item'],$res['message'],200,true);
        }
    }

    public function modifica_Item(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_item = $args['id_item'];
        $data_item =  $request->getParsedBody();

        $token=getenv('TOKEN_DATOS');
        $token=json_decode($token,true);
        $uuid=($token['sub']);

        $res=$this->itemRepository->modifyItem($id_item,$data_item,$uuid);

        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,false);
        }else{
            return $this->respondWithData($res['data_item'],$res['message'],200,true);
        }
    }

    public function cambiaestado_Item(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_item = $args['id_item'];
        $token=getenv('TOKEN_DATOS');
        $token=json_decode($token,true);
        $uuid=($token['sub']);
        $res=$this->itemRepository->changestatusItem($id_item,$uuid);
        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,true);
        }else{
            return $this->respondWithData(null,$res['message'],200,true);
        }
        
    }

    /* body servicio de creacion itemes
    {
        'codigo':string,
        'nombre':string,
        'pais':array,
        'direccion':string,
        'comentarios':string
    } 
    */
    public function crea_Item(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $data_item =  $request->getParsedBody();

        $token=getenv('TOKEN_DATOS');
        $token=json_decode($token,true);
        $uuid=($token['sub']);
        
        $res=$this->itemRepository->createItem($data_item,$uuid);

        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,false);
        }else{
            return $this->respondWithData($res['data_item'],$res['message'],200,true);
        }
    }

    public function calcula_Precio_Item(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $data_item =  $request->getParsedBody();
        
        $res=$this->itemRepository->calculatePriceItem($data_item);

        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,false);
        }else{
            return $this->respondWithData($res['data_calculo'],$res['message'],200,true);
        }
    }
}