<?php

declare(strict_types=1);

namespace App\Application\Actions\Action;

use App\Application\Actions\Action;
use App\Domain\SalidaRepository;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class SalidaAction extends Action {

    protected $salidaRepository;

    /**
     * @param LoggerInterface $logger
     * @param SalidaRepository  $salidaRepository
     */
    public function __construct(LoggerInterface $logger, SalidaRepository $salidaRepository) {
        parent::__construct($logger);
        $this->salidaRepository = $salidaRepository;
    }

    public function action(): Response {
        $users = array();
        $this->logger->info("Salidas list was viewed.");
        return $this->respondWithData($users);
    }

    public function obtiene_Salida(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_salida = $args['id_salida'];
        $JWT = new \App\Application\Middleware\JWTdata($request);
        $token = $JWT->getToken();
        if (!$token['success']) {
            return $this->respondWithData(array(), 'Datos de token invalidos', 403,false);
        }
        $token = $token['data'];
        $res=$this->salidaRepository->getSalida($id_salida,$token);
        return $this->respondWithData($res['data_salida'],$res['message'],$res['code'],$res['success']);
    }

    public function lista_Salida(Request $request, Response $response, $args): Response {
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
        $res=$this->salidaRepository->listSalida($query,$token);

        return $this->respondWithData($res['data_salida'],$res['message'],$res['code'],$res['success']);
    }

    /* body servicio de edicion de salida
    {
        'id_salida':int,
        'codigo':string,
        'nombre':string,
        'pais':array,
        'direccion':string,
        'comentarios':string,
        'activo':int
    } 
    */
    public function edita_Salida(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_salida = $args['id_salida'];
        $data_salida =  $request->getParsedBody();

        $JWT = new \App\Application\Middleware\JWTdata($request);
        $token = $JWT->getToken();
        if (!$token['success']) {
            return $this->respondWithData(array(), 'Datos de token invalidos', 403,false);
        }
        $token = $token['data'];

        $res=$this->salidaRepository->editSalida($id_salida,$data_salida,$token);

        return $this->respondWithData($res['data_salida'],$res['message'],$res['code'],$res['success']);
    }

    public function cambiaestado_Salida(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_salida = $args['id_salida'];
        $JWT = new \App\Application\Middleware\JWTdata($request);
        $token = $JWT->getToken();
        if (!$token['success']) {
            return $this->respondWithData(array(), 'Datos de token invalidos', 403,false);
        }
        $token = $token['data'];

        $res=$this->salidaRepository->changestatusSalida($id_salida,$token);
        return $this->respondWithData($res['data_salida'],$res['message'],$res['code'],$res['success']);
    }

    /* body servicio de creacion salidas
    {
        "nombre": "salida_test",
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
    public function crea_Salida(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $data_salida =  $request->getParsedBody();

        $JWT = new \App\Application\Middleware\JWTdata($request);
        $token = $JWT->getToken();
        if (!$token['success']) {
            return $this->respondWithData(array(), 'Datos de token invalidos', 403,false);
        }
        $token = $token['data'];
        
        $res=$this->salidaRepository->createSalida($data_salida,$token);

        return $this->respondWithData($res['data_salida'],$res['message'],$res['code'],$res['success']);
    }

    public function modifica_Salida(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_salida = $args['id_salida'];
        $data_salida =  $request->getParsedBody();

        $JWT = new \App\Application\Middleware\JWTdata($request);
        $token = $JWT->getToken();
        if (!$token['success']) {
            return $this->respondWithData(array(), 'Datos de token invalidos', 403,false);
        }
        $token = $token['data'];

        $res=$this->salidaRepository->modifySalida($id_salida,$data_salida,$token);

        return $this->respondWithData($res['data_salida'],$res['message'],$res['code'],$res['success']);
    }
}