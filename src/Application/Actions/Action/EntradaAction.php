<?php

declare(strict_types=1);

namespace App\Application\Actions\Action;

use App\Application\Actions\Action;
use App\Domain\EntradaRepository;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class EntradaAction extends Action {

    protected $entradaRepository;

    /**
     * @param LoggerInterface $logger
     * @param EntradaRepository  $entradaRepository
     */
    public function __construct(LoggerInterface $logger, EntradaRepository $entradaRepository) {
        parent::__construct($logger);
        $this->entradaRepository = $entradaRepository;
    }

    public function action(): Response {
        $users = array();
        $this->logger->info("Entradas list was viewed.");
        return $this->respondWithData($users);
    }

    public function obtiene_Entrada(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_entrada = $args['id_entrada'];
        $res=$this->entradaRepository->getEntrada($id_entrada);
        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,true);
        }else{
            return $this->respondWithData($res['data_entrada'],$res['message'],200,true);
        }
    }

    public function lista_Entrada(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $query=$request->getQueryParams();

        $res=$this->entradaRepository->listEntrada($query);

        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,true);
        }else{
            return $this->respondWithData($res['data_entrada'],$res['message'],200,true);
        }
    }

    /* body servicio de edicion de entrada
    {
        'id_entrada':int,
        'codigo':string,
        'nombre':string,
        'pais':array,
        'direccion':string,
        'comentarios':string,
        'activo':int
    } 
    */
    public function edita_Entrada(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_entrada = $args['id_entrada'];
        $data_entrada =  $request->getParsedBody();

        $token=getenv('TOKEN_DATOS');
        $token=json_decode($token,true);
        $uuid=($token['sub']);

        $res=$this->entradaRepository->editEntrada($id_entrada,$data_entrada,$uuid);

        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,false);
        }else{
            return $this->respondWithData($res['data_entrada'],$res['message'],200,true);
        }
    }

    public function cambiaestado_Entrada(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_entrada = $args['id_entrada'];
        $token=getenv('TOKEN_DATOS');
        $token=json_decode($token,true);
        $uuid=($token['sub']);
        $res=$this->entradaRepository->changestatusEntrada($id_entrada,$uuid);
        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,true);
        }else{
            return $this->respondWithData(null,$res['message'],200,true);
        }
        
    }

    /* body servicio de creacion entradas
    {
        "nombre": "entrada_test",
        "telefono": 22416539,
        "correo": "asd@asd.com",
        "nit": 123456789,
        "dependencia": {
            "id_param": "273",
            "cod_grupo": "param_dependencia",
            "codigo": "NING",
            "valor": "Ninguno"
        },
        "nivel": {
            "id_param": "263",
            "cod_grupo": "param_nivel_hosp",
            "codigo": "NING",
            "valor": "Ninguno"
        },
        "departamento": {
            "id_param": "253",
            "cod_grupo": "param_departamentos_bol",
            "codigo": "LPZ",
            "valor": "La Paz"
        },
        "provincia": {
            "id_param": "274",
            "cod_grupo": "param_provincia",
            "codigo": "NING",
            "valor": "Ninguno"
        },
        "municipio": {
            "id_param": "275",
            "cod_grupo": "param_municipio",
            "codigo": "NING",
            "valor": "Ninguno"
        },
        "ciudad": "La Paz",
        "direccion": "sin comentarios2",
        "subsector": {
            "id_param": "269",
            "cod_grupo": "param_subsec_hosp",
            "codigo": "PUBLIC",
            "valor": "Público"
        },
        "tipo": {
            "id_param": "272",
            "cod_grupo": "param_tipo_hosp",
            "codigo": "NING",
            "valor": "Ninguno"
        }
    }
    */
    public function crea_Entrada(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $data_entrada =  $request->getParsedBody();

        $token=getenv('TOKEN_DATOS');
        $token=json_decode($token,true);
        $uuid=($token['sub']);
        
        $res=$this->entradaRepository->createEntrada($data_entrada,$uuid);

        if($res['success']==false){
            return $this->respondWithData(null,$res['message'],202,false);
        }else{
            return $this->respondWithData($res['data_entrada'],$res['message'],200,true);
        }
    }
}