<?php
declare(strict_types=1);

namespace App\Domain;

interface ClienteRepository
{
    public function getCliente($id_cliente): array;
    public function editCliente($id_cliente,$data_cliente,$uuid): array;
    public function createCliente($data_cliente,$uuid): array;
    public function changestatusCliente($id_cliente,$uuid): array;
    public function listCliente($query): array;
}
