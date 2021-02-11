<?php

namespace ITColima\Siitec2\Api\Resources\Escolares;

use Francerz\Http\Utils\MessageHelper;
use ITColima\Siitec2\Api\AbstractResource;

class EstudiantesResource extends AbstractResource
{
    public function getAll(array $params = [])
    {
        $this->requiresClientAccessToken(true);
        $response = $this->_get('/escolares/estudiantes', $params);
        return MessageHelper::getContent($response);
    }

    public function getByNumControl(string $num_control, array $params = [])
    {
        $this->requiresClientAccessToken();
        $params['num_control'] = $num_control;
        $response = $this->_get('/escolares/estudiantes', $params);
        $output = MessageHelper::getContent($response);
        if (empty($output)) {
            return null;
        }
        return reset($output);
    }
}