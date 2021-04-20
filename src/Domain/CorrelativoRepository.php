<?php
declare(strict_types=1);

namespace App\Domain;

interface CorrelativoRepository
{
    public function genCorrelativo($codigo,$parametro,$user_uuid): array;
}
