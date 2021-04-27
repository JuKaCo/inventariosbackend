<?php
declare(strict_types=1);

namespace App\Domain;

interface ProveedorRepository
{
    public function getProveedor($id_proveedor): array;
    public function editProveedor($id_proveedor,$data_proveedor,$uuid): array;
    public function createProveedor($data_proveedor,$uuid): array;
    public function changestatusProveedor($id_proveedor,$uuid): array;
    public function listProveedor($query): array;
}
