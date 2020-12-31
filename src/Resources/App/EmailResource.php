<?php

namespace ITColima\Siitec2\Api\Resources\App;

use Francerz\Http\Utils\Constants\MediaTypes;
use Francerz\Http\Utils\MessageHelper;
use ITColima\Siitec2\Api\AbstractResource;
use ITColima\Siitec2\Api\Model\App\Email;

class EmailResource extends AbstractResource
{
    public function send(Email $email)
    {
        $this->requiresClientAccessToken(true);
        $response = $this->post('/app/email', $email, MediaTypes::APPLICATION_JSON);
        return MessageHelper::getContent($response);
    }
}