<?php

declare(strict_types=1);

namespace App\Application\Actions\Action;

use App\Application\Actions\Action;
use App\Domain\MenuRepository;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class MenuAction extends Action {

    protected $menuRepository;

    /**
     * @param LoggerInterface $logger
     * @param MenuRepository  $menuRepository
     */
    public function __construct(LoggerInterface $logger, MenuRepository $menuRepository) {
        parent::__construct($logger);
        $this->menuRepository = $menuRepository;
    }

    public function action(): Response {
        $users = array();
        $this->logger->info("Users list was viewed.");
        return $this->respondWithData($users);
    }

    public function menu(Request $request, Response $response, $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;

        
        $res=$this->menuRepository->getMenu($token);

        return $this->respondWithData($res);
    }

}
