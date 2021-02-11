<?php

namespace ITColima\Siitec2\Api\Resources\App;

use ITColima\Siitec2\Api\AbstractResource;
use Francerz\Http\Utils\MessageHelper;

class UsuariosResource extends AbstractResource
{
    public function getById($id, array $params = [])
    {
        $this->requiresClientAccessToken(true);
        $response = $this->_get("/app/usuarios/{$id}", $params);
        return MessageHelper::getContent($response);
    }

    public function findTerm($term, array $params = [])
    {
        $this->requiresClientAccessToken(true);
        $params['q'] = $term;
        $response = $this->_get('/app/usuarios', $params);
        return MessageHelper::getContent($response);
    }

    public function findMatricula($matricula, array $params = [])
    {
        $this->requiresClientAccessToken(true);
        $params['matricula'] = $matricula;
        $response = $this->_get('/app/usuarios', $params);
        return MessageHelper::getContent($response);
    }
}