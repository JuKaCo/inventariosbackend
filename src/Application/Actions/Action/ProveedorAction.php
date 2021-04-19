<?php

declare(strict_types=1);

namespace App\Application\Actions\Action;

use App\Application\Actions\Action;
use App\Domain\ProveedorRepository;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProveedorAction extends Action {

    protected $proveedorRepository;

    /**
     * @param LoggerInterface $logger
     * @param ProveedorRepository  $proveedorRepository
     */
    public function __construct(LoggerInterface $logger, ProveedorRepository $proveedorRepository) {
        parent::__construct($logger);
        $this->proveedorRepository = $proveedorRepository;
    }

    public function action(): Response {
        $users = array();
        $this->logger->info("Proveedores list was viewed.");
        return $this->respondWithData($users);
    }

    public function obtiene_Proveedor(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_proveedor = $args['id_proveedor'];
        $res=$this->proveedorRepository->getProveedor($id_proveedor);

        return $this->respondWithData($res);
    }

    public function lista_Proveedor(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $filtro = $args['filtro'];
        $items_pagina = $args['items_pagina'];
        $pagina = $args['pagina'];
        $res=$this->proveedorRepository->listProveedor($filtro,$items_pagina,$pagina);

        return $this->respondWithData($res);
    }

    public function edita_Proveedor(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_proveedor = $args['id_proveedor'];
        $data_proveedor =  $request->getParsedBody();

        $token=getenv('TOKEN_DATOS');
        $token=json_decode($token,true);
        $uuid=($token['data'])['sub'];
        
        $res=$this->proveedorRepository->listProveedor($id_proveedor,$data_proveedor,$uuid);

        return $this->respondWithData($res);
    }

    public function elimina_Proveedor(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $id_proveedor = $args['id_proveedor'];
        $data_proveedor =  $request->getParsedBody();

        $token=getenv('TOKEN_DATOS');
        $token=json_decode($token,true);
        $uuid=($token['data'])['sub'];
        
        $res=$this->proveedorRepository->deleteProveedor($id_proveedor,$uuid);

        return $this->respondWithData($res);
    }

    public function crea_Proveedor(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $data_proveedor =  $request->getParsedBody();

        $token=getenv('TOKEN_DATOS');
        $token=json_decode($token,true);
        $uuid=($token['data'])['sub'];
        
        $res=$this->proveedorRepository->createProveedor($data_proveedor,$uuid);

        return $this->respondWithData($res);
    }

}