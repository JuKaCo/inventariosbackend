<?php
declare(strict_types=1);

namespace App\Domain;

interface ItemRepository
{
    public function getItem($id_item): array;
    public function editItem($id_item,$data_item,$uuid): array;
    public function modifyItem($id_item,$data_item,$uuid): array;
    public function createItem($data_item,$uuid): array;
    public function changestatusItem($id_item,$uuid): array;
    public function listItem($query,$id_entrada_salida): array;
    public function calculatePriceItem($data_item): array;
}
