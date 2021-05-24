<?php
declare(strict_types=1);

namespace App\Domain;

interface DosificacionRepository
{
    public function getDoficifacion($id_dosificacio): array;
    public function listDoficifacion($query): array;
    public function createDocificacion($data_docificacion,$uuid): array;
    public function editDosificacion($id_dosificacion,$data_docificacion,$uuid): array;
    public function modifyDosificacion($id_dosificacion,$data_docificacion,$uuid): array;
    public function changestatusDosificacion($id_dosificacion, $uuid): array;
}
