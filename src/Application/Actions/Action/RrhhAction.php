<?php

declare(strict_types=1);

namespace App\Application\Actions\Action;

use App\Application\Actions\Action;
use App\Domain\RrhhRepository;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RrhhAction extends Action {

    protected $rrhhRepository;

    public function __construct(LoggerInterface $logger, RrhhRepository $rrhhRepository) {
        parent::__construct($logger);
        $this->rrhhRepository = $rrhhRepository;
    }

    public function action(): Response {
        $data = array();
        return $this->respondWithData($data);
    }

    public function genReporteGeneral(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $data = $request->getParsedBody();
        $data = $this->rrhhRepository->getReporteGeneral($data);

        if (isset($data['error'])) {
            return $this->respondWithData(array(), 'Error', 500, false);
        }
        if ($data == null) {
            return $this->respondWithData(array(), 'No se encontro datos', 202, false);
        }
        ob_clean();
        $this->response->getBody()->write($data['data']);
        //$this->response->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        //$this->response->withHeader('Content-Disposition', 'attachment; filename="reporte.xlsx"');
        //$this->response->withHeader('Access-Control-Allow-Origin', '*');
        
        //$response->getBody()->write('Hello world!');
        return $response;
        
        //return $this->respondWithData($data);
    }

}
