<?php
declare(strict_types=1);

namespace App\Domain;

interface ProductoRepository
{
    public function getProducto($id_producto): array;
    public function editProducto($id_producto,$data_producto,$uuid): array;
    public function createProducto($data_producto,$uuid): array;
    public function changestatusProducto($id_producto,$uuid): array;
    public function listProducto($query): array;
}
