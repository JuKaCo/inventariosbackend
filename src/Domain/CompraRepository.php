<?php
declare(strict_types=1);

namespace App\Domain;

interface CompraRepository
{
    public function getCompra($id_compra): array;
    public function editCompra($id_compra,$data_compra,$uuid): array;
    public function createCompra($data_compra,$uuid): array;
    public function changestatusCompra($id_compra,$uuid): array;
    public function listCompra($query): array;
}
