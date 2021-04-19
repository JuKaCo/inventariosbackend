<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Actions\RepositoryConection\Conect;
use App\Domain\LinameRepository;
use \PDO;

class DataLinameRepository implements LinameRepository {

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

    public function getMenu($roles): array {
        $role = '"' . implode('","', $roles) . '"';
        $sql = "SELECT 
            m.id_menu,
            m.label,
            m.descripcion,
            m.orden,
            m.ruta_url as routerLink,
            m.icon,
            m.id_menu_padre
                FROM auth_menu m, auth_rol_menu rm
                WHERE m.id_menu=rm.fid_menu and rm.fid_rol in (" . $role . ")
                and m.activo=true and rm.activo=true and m.id_menu_padre is null
                ORDER BY m.orden";
        $res = ($this->db)->prepare($sql);
        //$res->bindParam(':role', $role, PDO::PARAM_STR);
        $res->execute();
        $res = $res->fetchAll(PDO::FETCH_ASSOC);
        $sql = "SELECT id_menu,
            label,
            descripcion,
            orden,
            ruta_url as routerLink,
            icon,
            id_menu_padre 
                FROM auth_menu WHERE activo=true and id_menu_padre=:id_menu_padre
                ORDER BY orden";
        $menu = array();
        foreach ($res as $value) {
            if ($value['routerLink'] == null) {
                unset($value['routerLink']);
            } else {
                $value['routerLink'] = array($value['routerLink']);
            }
            $resSM = ($this->db)->prepare($sql);
            $id_menu_padre = $value['id_menu'];
            $resSM->bindParam(':id_menu_padre', $id_menu_padre, PDO::PARAM_INT);
            $resSM->execute();
            $resSM = $resSM->fetchAll(PDO::FETCH_ASSOC);
            if (count($resSM) != 0) {
                $menuSub = array();
                foreach ($resSM as $valueSM) {
                    if ($valueSM['routerLink'] == null) {
                        unset($valueSM['routerLink']);
                    } else {
                        $valueSM['routerLink'] = array($valueSM['routerLink']);
                        array_push($menuSub, $valueSM);
                    }
                }
                $value += ["items" => $menuSub];
            }
            array_push($menu, $value);
        }
        return $menu;
    }

}
