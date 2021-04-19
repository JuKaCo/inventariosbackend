<?php
declare(strict_types=1);

namespace App\Domain;

interface LinameRepository
{
    public function getMenu($roles): array;
}
