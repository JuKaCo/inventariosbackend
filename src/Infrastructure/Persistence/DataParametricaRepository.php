<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Actions\RepositoryConection\ConectBiometrico;
use App\Application\Actions\RepositoryConection\Conect;
use App\Domain\ParametricaRepository;
use \PDO;

class DataParametricaRepository implements ParametricaRepository {

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

    public function getParametrica($cod_grupo,$id_padre): array {
        try {
            $sql = "SELECT 
                id_param,
                cod_grupo,
                codigo,
                valor

                FROM param_general
                WHERE cod_grupo=:cod_grupo and id_padre=:id_padre";
            $res = ($this->db)->prepare($sql);
            $res->bindParam(':cod_grupo', $cod_grupo, PDO::PARAM_STR);
            $res->bindParam(':id_padre', $id_padre, PDO::PARAM_INT);
            $res->execute();
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            return $res;
        } catch (Exception $e) {
            return array('error'=>true);
        }
    }
    public function getTerminalBiometrico(): array {
        $con = new ConectBiometrico();
        $this->db = $con->getConection();
        try {
            $sql = "SELECT id as id_param,
                terminal_name as valor, 
                terminal_type as codigo,
                'terminal_biometrico' as cod_grupo 
                    FROM att_terminal";
            $res = ($this->db)->prepare($sql);
            $res->execute();
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            return $res;
        } catch (Exception $e) {
            return array('error'=>true);
        }
    }
}
