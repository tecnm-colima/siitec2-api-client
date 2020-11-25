<?php

namespace ITColima\Siitec2\Api\Resources\Docencia;

use Francerz\Http\Utils\MessageHelper;
use ITColima\Siitec2\Api\AbstractResource;

class GruposResource extends AbstractResource
{
    public function all()
    {
        $response = $this->get('/docencia/grupos');
        return MessageHelper::getContent($response);
    }
    public function own()
    {
        $response = $this->get('/docencia/grupos/own');
        return MessageHelper::getContent($response);
    }
    public function byId($grupo_id)
    {
        $response = $this->get("/docencia/grupos/{$grupo_id}");
        return MessageHelper::getContent($response);
    }
}