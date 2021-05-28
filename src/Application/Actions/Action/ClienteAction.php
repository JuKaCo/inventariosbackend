<?php

declare(strict_types=1);

namespace App\Application\Actions\Action;

use App\Application\Actions\Action;
use App\Domain\ClienteRepository;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ClienteAction extends Action {

    protected $clienteRepository;

    /**
     * @param LoggerInterface $logger
     * @param ClienteRepository  $clienteRepository
     */
    public function __construct(LoggerInterface $logger, ClienteRepository $clienteRepository) {
        parent::__construct($logger);
        $this->clienteRepository = $clienteRepository;
    }

    public function action(): Response {
        $users = array();
        $this->logger->info("Clientes list was viewed.");
        return $this->respondWithData($users);
    }

    public function obtiene_Cliente(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_cliente = $args['id_cliente'];
        $JWT = new \App\Application\Middleware\JWTdata($request);
        $token = $JWT->getToken();
        if (!$token['success']) {
            return $this->respondWithData(array(), 'Datos de token invalidos', 403,false);
        }
        $token = $token['data'];
        $res=$this->clienteRepository->getCliente($id_cliente,$token);
        return $this->respondWithData($res['data_cliente'],$res['message'],$res['code'],$res['success']);
    }

    public function lista_Cliente(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $query=$request->getQueryParams();
        $JWT = new \App\Application\Middleware\JWTdata($request);
        $token = $JWT->getToken();
        if (!$token['success']) {
            return $this->respondWithData(array(), 'Datos de token invalidos', 403,false);
        }
        $token = $token['data'];
        $res=$this->clienteRepository->listCliente($query,$token);

        return $this->respondWithData($res['data_cliente'],$res['message'],$res['code'],$res['success']);
    }

    /* body servicio de edicion de cliente
    {
        'id_cliente':int,
        'codigo':string,
        'nombre':string,
        'pais':array,
        'direccion':string,
        'comentarios':string,
        'activo':int
    } 
    */
    public function edita_Cliente(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_cliente = $args['id_cliente'];
        $data_cliente =  $request->getParsedBody();

        $JWT = new \App\Application\Middleware\JWTdata($request);
        $token = $JWT->getToken();
        if (!$token['success']) {
            return $this->respondWithData(array(), 'Datos de token invalidos', 403,false);
        }
        $token = $token['data'];
        $uuid=$token->sub;

        $res=$this->clienteRepository->editCliente($id_cliente,$data_cliente,$token);

        return $this->respondWithData($res['data_cliente'],$res['message'],$res['code'],$res['success']);
    }

    public function cambiaestado_Cliente(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_cliente = $args['id_cliente'];
        $JWT = new \App\Application\Middleware\JWTdata($request);
        $token = $JWT->getToken();
        if (!$token['success']) {
            return $this->respondWithData(array(), 'Datos de token invalidos', 403,false);
        }
        $token = $token['data'];

        $res=$this->clienteRepository->changestatusCliente($id_cliente,$token);
        return $this->respondWithData($res['data_cliente'],$res['message'],$res['code'],$res['success']);
        
    }

    /* body servicio de creacion clientes
    {
        "nombre": "cliente_test",
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
    public function crea_Cliente(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $data_cliente =  $request->getParsedBody();

        $JWT = new \App\Application\Middleware\JWTdata($request);
        $token = $JWT->getToken();
        if (!$token['success']) {
            return $this->respondWithData(array(), 'Datos de token invalidos', 403,false);
        }
        $token = $token['data'];        
        $res=$this->clienteRepository->createCliente($data_cliente,$token);

        return $this->respondWithData($res['data_cliente'],$res['message'],$res['code'],$res['success']);
    }
}