<?php
declare(strict_types=1);

namespace App\Domain;

interface CotizacionRepository
{
    public function getCotizacion($id_cotizacion): array;
    public function editCotizacion($id_cotizacion,$data_cotizacion,$uuid): array;
    public function createCotizacion($data_cotizacion,$uuid): array;
    public function changestatusCotizacion($id_cotizacion,$uuid): array;
    public function listCotizacion($query): array;
    public function modifyCotizacion($id_cotizacion,$data_cotizacion,$uuid): array;
}
