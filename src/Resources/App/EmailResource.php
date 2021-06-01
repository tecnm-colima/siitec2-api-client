<?php

namespace ITColima\Siitec2\Api\Resources\App;

use Francerz\Http\Utils\Constants\MediaTypes;
use Francerz\Http\Utils\HttpHelper;
use ITColima\Siitec2\Api\AbstractResource;
use ITColima\Siitec2\Api\Model\App\Email;

class EmailResource extends AbstractResource
{
    /**
     * Envía un correo electrónico utilizando la misma dirección de correo de SIITEC.
     *
     * @param Email $email
     * @return void
     */
    public function send(Email $email)
    {
        $this->requiresClientAccessToken(true);
        $response = $this->_post('/app/email', $email, MediaTypes::APPLICATION_JSON);
        return HttpHelper::getContent($response);
    }
}