<?php
declare(strict_types=1);

namespace App\Domain;

interface NotificacionRepository
{
    public function getNotificacion($id_usuario): array;
    public function getNotificacionId($id_usuario, $id_notificacion): array;
    public function createNotificacion($id_usuario, $data_notificacion): array;
}
