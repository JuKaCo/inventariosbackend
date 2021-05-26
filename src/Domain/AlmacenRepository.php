<?php
declare(strict_types=1);

namespace App\Domain;

interface AlmacenRepository
{
    public function getAlmacen($id_almacen,$token): array;
    public function editAlmacen($id_almacen,$data_almacen,$token): array;
    public function createAlmacen($data_almacen,$token): array;
    public function changestatusAlmacen($id_almacen,$token): array;
    public function listAlmacen($query,$token): array;
}
