<?php
namespace ITColima\Siitec2\Api\Resources\Usuario;

use Francerz\Http\Utils\MessageHelper;
use ITColima\Siitec2\Api\AbstractResource;

class PerfilResource extends AbstractResource
{
    /**
     * Obtiene el perfil del usuario actual.
     *
     * @return void
     */
    public function getOwn()
    {
        $this->requiresAccessToken(true);
        $response = $this->_get('/usuarios/perfil/own');
        return MessageHelper::getContent($response);
    }
}