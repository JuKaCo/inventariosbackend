<?php

declare(strict_types=1);

namespace App\Application\Actions\Action;

use App\Application\Actions\Action;
use App\Domain\LinadimeRepository;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;

class LinadimeAction extends Action {

    protected $linadimeRepository;

    /**
     * @param LoggerInterface $logger
     * @param MenuRepository  $menuRepository
     */
    
    public function __construct(LoggerInterface $logger, LinadimeRepository $linadimeRepository) {
        parent::__construct($logger);
        $this->linadimeRepository = $linadimeRepository;
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

        $res = $this->linadimeRepository->setValidUpload($archivo, $body);
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
        $token = getenv('TOKEN_DATOS');
        $token = json_decode($token, true);
        $id_usuario = $token['sub'];

        $res = $this->linadimeRepository->setCargarUpload($archivo, $body, $id_usuario);
        if (isset($res['error'])) {
            return $this->respondWithData(array(), $res['error'], 202, false);
        } else {
            return $this->respondWithData($res);
        }
    }

    public function getListLinadime(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        //$params=$args;
        $query = $request->getQueryParams();
        $res = $this->linadimeRepository->getListLinadime($query);
        return $this->respondWithData($res);
    }

    public function setInhabilitaHabilita(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        //$params=$args;
        //get token id usuario
        $token = getenv('TOKEN_DATOS');
        $token = json_decode($token, true);
        $id_usuario = $token['sub'];

        $estado = $args['estado'];
        $uuid = $args['uuid'];

        $res = $this->linadimeRepository->setActivaInactiva($uuid, $estado, $id_usuario);
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

        $res = $this->linadimeRepository->gerArchive($uuid);
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
