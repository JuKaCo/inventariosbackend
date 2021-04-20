<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Actions\RepositoryConection\Conect;
use App\Domain\DatosGeneralesRepository;
use \PDO;

class DataDatosGeneralesRepository implements DatosGeneralesRepository {

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

    public function getDatos(): array {
        try {
            $sql = "SELECT 
                id,
                codigo,
                descripcion,
                recurso
                FROM configuracion_general
                ";
            $res = $this->db->prepare($sql);
            $res->execute();
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            return $res;
        } catch (Exception $e) {
            return array('error' => true);
        }
    }

    public function getDatosCodigo($codigo): array {
         try {
            $sql = "SELECT 
                    id,
                    codigo,
                    descripcion,
                    recurso
                    FROM configuracion_general
                    WHERE codigo=:codigo";
            $res = $this->db->prepare($sql);
            $res->bindParam(':codigo', $codigo, PDO::PARAM_STR);
            $res->execute();
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            return $res[0];
        } catch (Exception $e) {
            return array('error' => true);
        }
    }

  

}
