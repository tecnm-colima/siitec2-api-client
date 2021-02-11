<?php

namespace ITColima\Siitec2\Api\Resources\Escolares;

use Francerz\Http\Utils\MessageHelper;
use ITColima\Siitec2\Api\AbstractResource;

class CarrerasResource extends AbstractResource
{
    public function getAll(array $params = [])
    {
        $this->requiresAccessToken(false);
        $response = $this->_get('/escolares/carreras', $params);
        return MessageHelper::getContent($response);
    }

    public function getById($carrera_id, array $params = [])
    {
        if (is_array($carrera_id)) {
            $carrera_id = join('+', $carrera_id);
        }
        $this->requiresAccessToken(false);
        $response = $this->_get("/escolares/carreras/{$carrera_id}", $params);
        return MessageHelper::getContent($response);
    }
}