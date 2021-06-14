<?php

declare(strict_types=1);

namespace App\Application\Actions\Action;

use App\Application\Actions\Action;
use App\Domain\VentaRepository;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class VentaAction extends Action {

    protected $ventaRepository;

    /**
     * @param LoggerInterface $logger
     * @param VentaRepository  $ventaRepository
     */
    public function __construct(LoggerInterface $logger, VentaRepository $ventaRepository) {
        parent::__construct($logger);
        $this->ventaRepository = $ventaRepository;
    }

    public function action(): Response {
        $users = array();
        $this->logger->info("Ventas list was viewed.");
        return $this->respondWithData($users);
    }

    public function obtiene_Venta(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_venta = $args['id_venta'];

        $JWT = new \App\Application\Middleware\JWTdata($request);
        $token = $JWT->getToken();
        if (!$token['success']) {
            return $this->respondWithData(array(), 'Datos de token invalidos', 403,false);
        }
        $token = $token['data'];

        $res=$this->ventaRepository->getVenta($id_venta,$token);
        
        return $this->respondWithData($res['data_venta'],$res['message'],$res['code'],$res['success']);
    }

    public function lista_Venta(Request $request, Response $response, $args): Response {
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

        $res=$this->ventaRepository->listVenta($query,$token);

        return $this->respondWithData($res['data_venta'],$res['message'],$res['code'],$res['success']);
    }

    /* body servicio de edicion de venta
    {
        'id_venta':int,
        'codigo':string,
        'nombre':string,
        'pais':array,
        'direccion':string,
        'comentarios':string,
        'activo':int
    } 
    */
    public function edita_Venta(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_venta = $args['id_venta'];
        $data_venta =  $request->getParsedBody();

        $JWT = new \App\Application\Middleware\JWTdata($request);
        $token = $JWT->getToken();
        if (!$token['success']) {
            return $this->respondWithData(array(), 'Datos de token invalidos', 403,false);
        }
        $token = $token['data'];

        $res=$this->ventaRepository->editVenta($id_venta,$data_venta,$token);

        return $this->respondWithData($res['data_venta'],$res['message'],$res['code'],$res['success']);
    }

    public function cambiaestado_Venta(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_venta = $args['id_venta'];
        $JWT = new \App\Application\Middleware\JWTdata($request);
        $token = $JWT->getToken();
        if (!$token['success']) {
            return $this->respondWithData(array(), 'Datos de token invalidos', 403,false);
        }
        $token = $token['data'];

        $res=$this->ventaRepository->changestatusVenta($id_venta,$token);
        return $this->respondWithData($res['data_venta'],$res['message'],$res['code'],$res['success']);
        
    }

    /* body servicio de creacion ventas
    {
        "nombre": "venta_test",
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
    public function crea_Venta(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $data_venta =  $request->getParsedBody();

        $JWT = new \App\Application\Middleware\JWTdata($request);
        $token = $JWT->getToken();
        if (!$token['success']) {
            return $this->respondWithData(array(), 'Datos de token invalidos', 403,false);
        }
        $token = $token['data'];
        
        $res=$this->ventaRepository->createVenta($data_venta,$token);

        return $this->respondWithData($res['data_venta'],$res['message'],$res['code'],$res['success']);
    }

    public function modifica_Venta(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_venta = $args['id_venta'];
        $data_venta =  $request->getParsedBody();

        $JWT = new \App\Application\Middleware\JWTdata($request);
        $token = $JWT->getToken();
        if (!$token['success']) {
            return $this->respondWithData(array(), 'Datos de token invalidos', 403,false);
        }
        $token = $token['data'];

        $res=$this->ventaRepository->modifyVenta($id_venta,$data_venta,$token);

        return $this->respondWithData($res['data_venta'],$res['message'],$res['code'],$res['success']);
    }
}