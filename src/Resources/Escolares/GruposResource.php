<?php

namespace ITColima\Siitec2\Api\Resources\Escolares;

use Francerz\Http\Utils\MessageHelper;
use ITColima\Siitec2\Api\AbstractResource;

class GruposResource extends AbstractResource
{
    public function getAll(array $params = [])
    {
        $this->requiresAccessToken(false);
        $response = $this->_get('/escolares/grupos', $params);
        return MessageHelper::getContent($response);
    }

    public function getById($grupo_id, array $params = [])
    {
        if (is_array($grupo_id)) {
            $grupo_id = join('+', $grupo_id);
        }
        $this->requiresAccessToken(false);
        $response = $this->_get("/escolares/grupos/{$grupo_id}", $params);
        return MessageHelper::getContent($response);
    }

    public function getAsDocente(array $params = [])
    {
        $this->requiresAccessToken(true);
        $response = $this->_get('/escolares/grupos/@docente', $params);
        return MessageHelper::getContent($response);
    }

    public function getAsEstudiante(array $params = [])
    {
        $this->requiresAccessToken(true);
        $response = $this->_get('/escolares/grupos/@estudiante', $params);
        return MessageHelper::getContent($response);
    } 
}