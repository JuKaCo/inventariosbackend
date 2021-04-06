<?php
declare(strict_types=1);

namespace App\Domain\Menu;

interface MenuRepository
{
    /**
     * @param array $roles
     * @return array
     * @throws UserNotFoundException
     */
    public function getMenu($roles): array;
}
