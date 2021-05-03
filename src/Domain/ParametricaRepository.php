<?php
declare(strict_types=1);

namespace App\Domain;

interface ParametricaRepository
{
    public function getParametrica($cod_grupo,$id_padre,$filtro): array;
    public function getTerminalBiometrico(): array ;
    public function getLiname($filtro): array;
    public function getLinadime($filtro): array;
}
