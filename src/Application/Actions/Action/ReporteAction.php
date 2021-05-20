<?php

declare(strict_types=1);

namespace App\Application\Actions\Action;

use App\Application\Actions\Action;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;
use App\Domain\ReporteRepository;

class ReporteAction extends Action {

    protected $repository;

    /**
     * @param LoggerInterface $logger
     * @param $repository  ?
     */
    public function __construct(LoggerInterface $logger, ReporteRepository $rep) {
        parent::__construct($logger);
        $this->repository = $rep;
    }

    public function action(): Response {
        $users = array();
        $this->logger->info("ReporteAction");
        return $this->respondWithData($users);
    }

    public function getEntradaNotaIngreso(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        //$params=$args;
        $id_entrada = $args['id'];

        header('access-control-allow-origin: *');


        $res=$this->repository->reporteIngresoNotaIngreso($id_entrada);
        if (isset($res['error'])) {
            return $this->respondWithData(array(), 'Error', 500, false);
        }
        if (isset($res['sin_datos'])) {
            return $this->respondWithData(array(), 'No se encontro', 404, false);
        }
        $response->getBody()->write("");
        return $response;
    }

    public function getEntradaActaRecepcion(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        //$params=$args;
        $id_entrada = $args['id'];

        header('access-control-allow-origin: *');

        $JWT = new \App\Application\Middleware\JWTdata($request);
        $token = $JWT->getToken();
        if (!$token['success']) {
            return $this->respondWithData(array(), 'Datos de token invalidos', 403);
        }
        $token = $token['data'];
        $res=$this->repository->reporteIngresoActaRecepcion($id_entrada, $token);
        if (isset($res['error'])) {
            return $this->respondWithData(array(), 'Error', 500, false);
        }
        if (isset($res['sin_datos'])) {
            return $this->respondWithData(array(), 'No se encontro', 404, false);
        }
        $response->getBody()->write("");
        return $response;
    }

}
