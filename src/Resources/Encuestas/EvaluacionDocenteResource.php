<?php

namespace ITColima\Siitec2\Api\Resources\Encuestas;

use Francerz\Http\Utils\MessageHelper;
use ITColima\Siitec2\Api\AbstractResource;

class EvaluacionDocenteResource extends AbstractResource
{
    public function getRealizada($encuesta_id, $usuario_id, array $params = [])
    {
        $this->requiresClientAccessToken(false);
        $response = $this->get("/encuestas/edocente/{$encuesta_id}/usuarios/{$usuario_id}", $params);
        return MessageHelper::getContent($response);
    }
}