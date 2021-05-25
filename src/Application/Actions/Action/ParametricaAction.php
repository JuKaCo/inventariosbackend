<?php

declare(strict_types=1);

namespace App\Application\Actions\Action;

use App\Application\Actions\Action;
use App\Domain\ParametricaRepository;
use App\Domain\LinameRepository;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use AbmmHasan\Uuid;

class ParametricaAction  extends Action {
    
     protected $parametricaRepository;
     protected $linameRepository;

    public function __construct(LoggerInterface $logger, ParametricaRepository $parametricaRepository,LinameRepository $linameRepository) {
        parent::__construct($logger);
        $this->parametricaRepository = $parametricaRepository;
        $this->linameRepository = $parametricaRepository;
    }

    public function action(): Response {
        $data = array();
        return $this->respondWithData($data);
    }

    public function genParam(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $cod_grupo = $args['cod_grupo'];
        $query=$request->getQueryParams();
        $filtro=$query['filtro'];
        $data = $this->parametricaRepository->getParametrica($cod_grupo, 0,$filtro);
        if (isset($data['error'])) {
            return $this->respondWithData(array(), 'Error', 500, false);
        }
        if ($data == null) {
            return $this->respondWithData(array(), 'No se encontro datos', 202, false);
        }

        return $this->respondWithData($data);
    }

    public function genParamPadre(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $cod_grupo = $args['cod_grupo'];
        $id_padre = $args['id_padre'];
        $query=$request->getQueryParams();
        $filtro=$query['filtro'];
        $data = $this->parametricaRepository->getParametrica($cod_grupo, $id_padre,$filtro);
        if (isset($data['error'])) {
            return $this->respondWithData(array(), 'Error', 500, false);
        }
        if ($data == null) {
            return $this->respondWithData(array(), 'No se encontro datos', 202, false);
        }
        return $this->respondWithData($data);
    }

    public function genParamBiometrico(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $data = $this->parametricaRepository->getTerminalBiometrico();
        if (isset($data['error'])) {
            return $this->respondWithData(array(), 'Error', 500, false);
        }
        if ($data == null) {
            return $this->respondWithData(array(), 'No se encontro datos', 202, false);
        }
        return $this->respondWithData($data);
    }
    
     public function genParamLiname(Request $request, Response $response, $args): Response {
        $this->response = $response;
        $this->args = $args;
        $query=$request->getQueryParams();
        $filtro=$query['filtro'];
        $data = $this->parametricaRepository->getLiname($filtro);
        if (isset($data['error'])) {
            return $this->respondWithData(array(), 'Error', 500, false);
        }
        if ($data == null) {
            return $this->respondWithData(array(), 'No se encontro datos', 202, false);
        }
        return $this->respondWithData($data);
    }
    
    public function genParamLinadime(Request $request, Response $response, $args): Response {
        $this->response = $response;
        $this->args = $args;
        $query=$request->getQueryParams();
        $filtro=$query['filtro'];
        $data = $this->parametricaRepository->getLinadime($filtro);
        if (isset($data['error'])) {
            return $this->respondWithData(array(), 'Error', 500, false);
        }
        if ($data == null) {
            return $this->respondWithData(array(), 'No se encontro datos', 202, false);
        }
        return $this->respondWithData($data);
    }

    public function genParamProveedor(Request $request, Response $response, $args): Response {
        $this->response = $response;
        $this->args = $args;
        $query=$request->getQueryParams();
        $filtro=$query['filtro'];
        $data = $this->parametricaRepository->getProveedor($filtro);
        if (isset($data['error'])) {
            return $this->respondWithData(array(), 'Error', 500, false);
        }
        if ($data == null) {
            return $this->respondWithData(array(), 'No se encontro datos', 202, false);
        }
        return $this->respondWithData($data);
    }

    public function genParamRegional(Request $request, Response $response, $args): Response {
        $this->response = $response;
        $this->args = $args;
        $query=$request->getQueryParams();
        $filtro=$query['filtro'];
        $data = $this->parametricaRepository->getRegional($filtro);
        if (isset($data['error'])) {
            return $this->respondWithData(array(), 'Error', 500, false);
        }
        if ($data == null) {
            return $this->respondWithData(array(), 'No se encontro datos', 202, false);
        }
        return $this->respondWithData($data);
    }

    public function genParamPrograma(Request $request, Response $response, $args): Response {
        $this->response = $response;
        $this->args = $args;
        $query=$request->getQueryParams();
        $filtro=$query['filtro'];
        $data = $this->parametricaRepository->getPrograma($filtro);
        if (isset($data['error'])) {
            return $this->respondWithData(array(), 'Error', 500, false);
        }
        if ($data == null) {
            return $this->respondWithData(array(), 'No se encontro datos', 202, false);
        }
        return $this->respondWithData($data);
    }

    public function genParamProducto(Request $request, Response $response, $args): Response {
        $this->response = $response;
        $this->args = $args;
        $query=$request->getQueryParams();
        $filtro=$query['filtro'];
        $data = $this->parametricaRepository->getProducto($filtro);
        if (isset($data['error'])) {
            return $this->respondWithData(array(), 'Error', 500, false);
        }
        if ($data == null) {
            return $this->respondWithData(array(), 'No se encontro datos', 202, false);
        }
        return $this->respondWithData($data);
    }

    public function genParamAlmacen(Request $request, Response $response, $args): Response {
        $this->response = $response;
        $this->args = $args;
        $query=$request->getQueryParams();
        
        $data = $this->parametricaRepository->getAlmacen($query);
        if (isset($data['error'])) {
            return $this->respondWithData(array(), 'Error', 500, false);
        }
        if ($data == null) {
            return $this->respondWithData(array(), 'No se encontro datos', 202, false);
        }
        if (isset($data['success'])) {
            return $this->respondWithData(array(), 'No se encontro datos', 202, false);
        }
        return $this->respondWithData($data);
    }

    public function genParamUsuario(Request $request, Response $response, $args): Response {
        $this->response = $response;
        $this->args = $args;
        $query=$request->getQueryParams();
        $filtro=$query['filtro'];
        $data = $this->parametricaRepository->getUsuario($filtro);
        if (isset($data['error'])) {
            return $this->respondWithData(array(), 'Error', 500, false);
        }
        if ($data == null) {
            return $this->respondWithData(array(), 'No se encontro datos', 202, false);
        }
        return $this->respondWithData($data);
    }

    public function genParamCompra(Request $request, Response $response, $args): Response {
        $this->response = $response;
        $this->args = $args;
        $query=$request->getQueryParams();
        $filtro=$query['filtro'];
        $data = $this->parametricaRepository->getCompra($filtro);
        if (isset($data['error'])) {
            return $this->respondWithData(array(), 'Error', 500, false);
        }
        if ($data == null) {
            return $this->respondWithData(array(), 'No se encontro datos', 202, false);
        }
        return $this->respondWithData($data);
    }


    public function genParamConfiguracion(Request $request, Response $response, $args): Response {
        $this->response = $response;
        $this->args = $args;
        $codigo = $args['codigo'];
        $data = $this->parametricaRepository->getConfiguracion($codigo);
        if (isset($data['error'])) {
            return $this->respondWithData(array(), 'Error', 500, false);
        }
        if ($data == null) {
            return $this->respondWithData(array(), 'No se encontro datos', 202, false);
        }
        return $this->respondWithData($data[0], 'Exitoso', 200, true);
    }

    public function genParamCliente(Request $request, Response $response, $args): Response {
        $this->response = $response;
        $this->args = $args;
        $query=$request->getQueryParams();
        $filtro=$query['filtro'];
        $data = $this->parametricaRepository->getCliente($filtro);
        if (isset($data['error'])) {
            return $this->respondWithData(array(), 'Error', 500, false);
        }
        if ($data == null) {
            return $this->respondWithData(array(), 'No se encontro datos', 202, false);
        }
        return $this->respondWithData($data);
    }
}
