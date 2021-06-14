<?php
declare(strict_types=1);

namespace App\Domain;

interface VentaRepository
{
    public function getVenta($id_venta,$token): array;
    public function editVenta($id_venta,$data_venta,$token): array;
    public function createVenta($data_venta,$token): array;
    public function changestatusVenta($id_venta,$token): array;
    public function listVenta($query,$token): array;
    public function modifyVenta($id_venta,$data_venta,$token): array;
}
