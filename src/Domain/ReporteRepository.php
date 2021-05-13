<?php
declare(strict_types=1);

namespace App\Domain;

interface ReporteRepository
{
    public function reporteIngresoNotaIngreso($data): void;
    
}
