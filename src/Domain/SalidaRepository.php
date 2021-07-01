<?php
declare(strict_types=1);

namespace App\Domain;

interface SalidaRepository
{
    public function getSalida($id_salida,$token): array;
    public function editSalida($id_salida,$data_salida,$token): array;
    public function createSalida($data_salida,$token): array;
    public function changestatusSalida($id_salida,$token): array;
    public function listSalida($query,$token): array;
    public function modifySalida($id_salida,$data_salida,$token): array;
}
