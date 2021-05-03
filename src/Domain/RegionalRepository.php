<?php
declare(strict_types=1);

namespace App\Domain;

interface RegionalRepository
{
    public function getRegional($id_regional): array;
    public function editRegional($id_regional,$data_regional,$uuid): array;
    public function createRegional($data_regional,$uuid): array;
    public function changestatusRegional($id_regional,$uuid): array;
    public function listRegional($query): array;
}
