<?php
declare(strict_types=1);

namespace App\Domain;

interface EntradaRepository
{
    public function getEntrada($id_entrada): array;
    public function editEntrada($id_entrada,$data_entrada,$uuid): array;
    public function createEntrada($data_entrada,$uuid): array;
    public function changestatusEntrada($id_entrada,$uuid): array;
    public function listEntrada($query): array;
    public function modifyEntrada($id_entrada,$data_entrada,$uuid): array;
}
