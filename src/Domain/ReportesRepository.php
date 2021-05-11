<?php
declare(strict_types=1);

namespace App\Domain;

interface ReportesRepository
{
    public function reporteIngresoNotaIngreso($data): void;
    
}
