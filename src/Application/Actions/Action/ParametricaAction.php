<?php

declare(strict_types=1);

namespace App\Application\Actions\Action;

use App\Application\Actions\Action;
use App\Domain\ParametricaRepository;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ParametricaAction extends Action {

    protected $parametricaRepository;

    public function __construct(LoggerInterface $logger, ParametricaRepository $parametricaRepository) {
        parent::__construct($logger);
        $this->parametricaRepository = $parametricaRepository;
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
        $data = $this->parametricaRepository->getParametrica($cod_grupo, 0);
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
        $data = $this->parametricaRepository->getParametrica($cod_grupo, $id_padre);
        if (isset($data['error'])) {
            return $this->respondWithData(array(), 'Error', 500, false);
        }
        if ($data == null) {
            return $this->respondWithData(array(), 'No se encontro datos', 202, false);
        }
        return $this->respondWithData($data);
    }

}
