<?php
declare(strict_types=1);

namespace App\Domain;

interface LinameRepository
{
    public function setValidUpload($archivo,$body): array;
    
    public function setCargarUpload($archivo,$body,$id_usuario): array;
}
