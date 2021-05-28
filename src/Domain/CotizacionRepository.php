<?php
declare(strict_types=1);

namespace App\Domain;

interface CotizacionRepository
{
    public function getCotizacion($id_cotizacion,$token): array;
    public function editCotizacion($id_cotizacion,$data_cotizacion,$token): array;
    public function createCotizacion($data_cotizacion,$token): array;
    public function changestatusCotizacion($id_cotizacion,$token): array;
    public function listCotizacion($query,$token): array;
    public function modifyCotizacion($id_cotizacion,$data_cotizacion,$token): array;
}
