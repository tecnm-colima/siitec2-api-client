<?php
namespace ITColima\Siitec2\Api\Resources\Usuario;

use Francerz\Http\Tools\MessageHelper;
use ITColima\Siitec2\Api\Cliente;
use ITColima\Siitec2\Api\AbstractResource;

class PerfilResource extends AbstractResource
{
    public function __construct(Cliente $cliente) {
        parent::__construct($cliente);
    }
    public function own()
    {
        $response = $this->get('/usuarios/perfil/own');
        return MessageHelper::getContent($response);
    }
}