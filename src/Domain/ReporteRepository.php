<?php
declare(strict_types=1);

namespace App\Domain;

interface ReporteRepository
{
    public function reporteIngresoNotaIngreso($data, $token): array;
    public function reporteIngresoActaRecepcion($data, $token): array;
    public function reporteCotizacion($data, $token): array;
    
}
