<?php
declare(strict_types=1);

namespace App\Domain;

interface ClienteRepository
{
    public function getCliente($id_cliente,$token): array;
    public function editCliente($id_cliente,$data_cliente,$token): array;
    public function createCliente($data_cliente,$token): array;
    public function changestatusCliente($id_cliente,$token): array;
    public function listCliente($query,$token): array;
}
