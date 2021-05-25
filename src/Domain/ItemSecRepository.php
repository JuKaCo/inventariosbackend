<?php
declare(strict_types=1);

namespace App\Domain;

interface ItemSecRepository
{
    public function getItemSec($id_itemsec): array;
    public function editItemSec($id_itemsec,$data_itemsec,$uuid): array;
    public function modifyItemSec($id_itemsec,$data_itemsec,$uuid): array;
    public function createItemSec($data_itemsec,$uuid): array;
    public function changestatusItemSec($id_itemsec,$uuid): array;
    public function listItemSec($query,$id_coti_ven): array;
}
