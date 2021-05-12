<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Actions\RepositoryConection\Conect;
use App\Domain\RutaRepository;
use \PDO;
use AbmmHasan\Uuid;

class DataRutaRepository /*implements RutaRepository */{

    /**
     * @var data[]
     */
    private $data;

    /**
     * @var $db conection db
     */
    private $db;

    /**
     * DataMenuRepository constructor.
     *
     */
    public function __construct() {
        $con = new Conect();
        $this->db = $con->getConection();
    }

    public function getRuta($roles, $method): array {

        $role = '"' . implode('","', $roles) . '"';
        $sql = "SELECT r.id, r.label
                FROM auth_ruta r,
                     auth_rol_ruta rr
                where rr.id_rol in (" . $role . ") 
                      and r.id = rr.id_ruta
                      and r.method = :method
                      and r.activo = 1"; 
        $res = ($this->db)->prepare($sql);
        $res->bindParam(':method', $method, PDO::PARAM_STR);
        $res->execute();
        $res = $res->fetchAll(PDO::FETCH_ASSOC);

        return $res;
    }

}
