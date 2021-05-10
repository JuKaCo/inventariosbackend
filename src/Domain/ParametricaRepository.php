<?php
declare(strict_types=1);

namespace App\Domain;

interface ParametricaRepository
{
    public function getParametrica($cod_grupo,$id_padre,$filtro): array;
    public function getTerminalBiometrico(): array ;
    public function getLiname($filtro): array;
    public function getLinadime($filtro): array;
    public function getProveedor($filtro): array;
    public function getRegional($filtro): array;
    public function getPrograma($filtro): array;
    public function getProducto($filtro): array;
    public function getAlmacen($query): array;
    public function getUsuario($filtro): array;
    public function getCompra($filtro0): array;

}
