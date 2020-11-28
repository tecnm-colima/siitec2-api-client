<?php

namespace ITColima\Siitec2\Api\Resources\Docencia;

use Francerz\Http\Utils\MessageHelper;
use ITColima\Siitec2\Api\AbstractResource;

class GruposResource extends AbstractResource
{
    public function getAll($params)
    {
        $response = $this->get('/docencia/grupos');
        return MessageHelper::getContent($response);
    }
    public function getOwn()
    {
        $response = $this->get('/docencia/grupos/own');
        return MessageHelper::getContent($response);
    }
    public function getById($grupo_id)
    {
        if (is_array($grupo_id)) {
            $grupo_id = join('+', $grupo_id);
        }
        $response = $this->get("/docencia/grupos/{$grupo_id}");
        return MessageHelper::getContent($response);
    }
}