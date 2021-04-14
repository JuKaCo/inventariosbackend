<?php
declare(strict_types=1);

namespace App\Domain;

interface RrhhRepository
{
    public function getReporteGeneral($data): array;
}
