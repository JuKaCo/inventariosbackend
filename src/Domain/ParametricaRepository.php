<?php
declare(strict_types=1);

namespace App\Domain;

interface ParametricaRepository
{
    public function getParametrica($cod_grupo,$id_padre): array;
}
