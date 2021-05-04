<?php
declare(strict_types=1);

namespace App\Domain;

interface AlmacenRepository
{
    public function getAlmacen($id_almacen): array;
    public function editAlmacen($id_almacen,$data_almacen,$uuid): array;
    public function createAlmacen($data_almacen,$uuid): array;
    public function changestatusAlmacen($id_almacen,$uuid): array;
    public function listAlmacen($query): array;
}
