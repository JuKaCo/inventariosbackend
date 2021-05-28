<?php
declare(strict_types=1);

namespace App\Domain;

interface EntradaRepository
{
    public function getEntrada($id_entrada,$token): array;
    public function editEntrada($id_entrada,$data_entrada,$token): array;
    public function createEntrada($data_entrada,$token): array;
    public function changestatusEntrada($id_entrada,$token): array;
    public function listEntrada($query,$token): array;
    public function modifyEntrada($id_entrada,$data_entrada,$token): array;
}
