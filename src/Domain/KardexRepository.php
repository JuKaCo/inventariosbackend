<?php
declare(strict_types=1);

namespace App\Domain;

interface KardexRepository
{
    public function getKardex($id_kardex): array;
    public function editKardex($id_kardex,$data_kardex,$uuid): array;
    public function createKardex($data_kardex,$uuid): array;
    public function changestatusKardex($id_kardex,$uuid): array;
    public function listKardex($query): array;
    public function modifyKardex($id_kardex,$data_kardex,$uuid): array;
    public function getProdsKardex($id_almacen,$query): array;
}
