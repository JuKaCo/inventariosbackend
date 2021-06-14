<?php
declare(strict_types=1);

namespace App\Domain;

interface FacturaRepository
{
    public function getFactura($id,$request): array;

}
