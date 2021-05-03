<?php
declare(strict_types=1);

namespace App\Domain;

interface ProgramaRepository
{
    public function getPrograma($id_programa): array;
    public function editPrograma($id_programa,$data_programa,$uuid): array;
    public function createPrograma($data_programa,$uuid): array;
    public function changestatusPrograma($id_programa,$uuid): array;
    public function listPrograma($query): array;
}
