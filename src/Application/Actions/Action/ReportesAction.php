<?php

declare(strict_types=1);

namespace App\Application\Actions\Action;

use App\Application\Actions\Action;
use App\Domain\LinadimeRepository;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;
use App\Domain\ReportesRepository;

class ReportesAction extends Action {

    protected $repository;

    /**
     * @param LoggerInterface $logger
     * @param $repository  ?
     */
    public function __construct(LoggerInterface $logger, ReportesRepository $rep) {
        parent::__construct($logger);
        $this->repository = $rep;
    }

    public function action(): Response {
        $users = array();
        $this->logger->info("ReportesAction");
        return $this->respondWithData($users);
    }

    public function getEntradaNotaIngreso(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        //$params=$args;
        $query = $request->getQueryParams();
        $data = array();
        header('access-control-allow-origin: *');
        $this->repository->reporteIngresoNotaIngreso($data);
        $response->getBody()->write("");
        return $response;
    }

}
