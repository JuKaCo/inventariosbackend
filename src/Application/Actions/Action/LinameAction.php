<?php

declare(strict_types=1);

namespace App\Application\Actions\Action;

use App\Application\Actions\Action;
use App\Domain\LinameRepository;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;

class LinameAction extends Action {

    protected $linameRepository;

    /**
     * @param LoggerInterface $logger
     * @param MenuRepository  $menuRepository
     */
    public function __construct(LoggerInterface $logger, LinameRepository $linameRepository) {
        parent::__construct($logger);
        $this->linameRepository = $linameRepository;
    }

    public function action(): Response {
        $users = array();
        $this->logger->info("Users list was viewed.");
        return $this->respondWithData($users);
    }

    public function cargarValidUpload(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;

        $archivo = $this->request->getUploadedFiles();
        $body = $this->request->getParsedBody();

        $res = $this->linameRepository->setValidUpload($archivo, $body);
        if (isset($res['error'])) {
            return $this->respondWithData(array(), $res['error'], 202, false);
        } else {
            return $this->respondWithData($res);
        }
    }

    public function cargarConsolida(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        //datos form
        $archivo = $this->request->getUploadedFiles();
        $body = $this->request->getParsedBody();

        //get token id usuario
        $JWT = new \App\Application\Middleware\JWTdata($request);
        $token = $JWT->getToken();
        if (!$token['success']) {
            return $this->respondWithData(array(), 'Datos de token invalidos', 403,false);
        }
        $token = $token['data'];
        $id_usuario=$token->sub;

        $res = $this->linameRepository->setCargarUpload($archivo, $body, $id_usuario);
        if (isset($res['error'])) {
            return $this->respondWithData(array(), $res['error'], 202, false);
        } else {
            return $this->respondWithData($res);
        }
    }

    public function getListLiname(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        //$params=$args;
        $query = $request->getQueryParams();
        $res = $this->linameRepository->getListLiname($query);
        return $this->respondWithData($res);
    }

    public function setInhabilitaHabilita(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        //$params=$args;
        //get token id usuario
        $JWT = new \App\Application\Middleware\JWTdata($request);
        $token = $JWT->getToken();
        if (!$token['success']) {
            return $this->respondWithData(array(), 'Datos de token invalidos', 403,false);
        }
        $token = $token['data'];
        $id_usuario=$token->sub;

        $estado = $args['estado'];
        $uuid = $args['uuid'];

        $res = $this->linameRepository->setActivaInactiva($uuid, $estado, $id_usuario);
        if (isset($res['error'])) {
            return $this->respondWithData(array(), $res['error'], 202, false);
        }
        return $this->respondWithData($res);
    }

    public function getArchive(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;

        $uuid = $args['uuid'];

        $res = $this->linameRepository->gerArchive($uuid);
        if (isset($res['error'])) {
            return $this->respondWithData(array(), $res['error'], 404, false);
        }
        $pathArch = $res['ruta'] . $res['nombre_archivo'];
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment;filename="' . basename($res['nombre_archivo']) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('access-control-allow-origin: *');
        header('Content-Length: ' . filesize($pathArch));
        readfile($pathArch);
        exit;
    }

}
