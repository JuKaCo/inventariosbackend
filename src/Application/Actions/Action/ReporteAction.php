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

        //header('access-control-allow-origin: *');


        $res=$this->repository->reporteIngresoNotaIngreso($id_entrada);
        //return $this->respondWithData($res,'exito',200,true);
        //$this->repository->reporteIngresoNotaIngreso($data);
        $response->getBody()->write("");
    }

}
