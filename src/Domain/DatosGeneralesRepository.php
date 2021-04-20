<?php
declare(strict_types=1);

namespace App\Domain;

interface DatosGeneralesRepository
{
    public function getDatos(): array;
    
    public function getDatosCodigo($codigo): array;
}
