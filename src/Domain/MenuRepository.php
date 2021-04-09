<?php
declare(strict_types=1);

namespace App\Domain;

interface MenuRepository
{
    public function getMenu($roles): array;
}
