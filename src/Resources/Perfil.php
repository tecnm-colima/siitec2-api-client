<?php
namespace ITColima\Siitec2\Api\Resources;

use ITColima\Siitec2\Api\Cliente;
use Francerz\Http\Client AS HttpClient;
use Francerz\Http\Helpers\MessageHelper;

class Perfil extends ResourceBase
{
    public function __construct(Cliente $cliente) {
        parent::__construct($cliente);
    }
    public function own()
    {
        $request = $this->request('/perfil/own');
        $httpClient = new HttpClient();
        $response = $httpClient->send($request);
        if ($response->getStatusCode() >= 400) {
            throw new \Exception("HTTP/1.1 {$response->getStatusCode()} on {$request->getUri()}");
        }
        return MessageHelper::getContent($response);
    }
}