<?php
namespace ITColima\Siitec2\Api\Resources;

use Francerz\Http\Helpers\UriHelper;
use Francerz\Http\Request;
use Psr\Http\Message\RequestInterface;
use ITColima\Siitec2\Api\Cliente;

abstract class ResourceBase
{
    private $cliente;
    
    public function __construct(Cliente $cliente)
    {
        $this->cliente = $cliente;
    }
    protected function request($path, array $params = null, string $fragment = null) : RequestInterface
    {
        $uri = $this->cliente->getApiUri();
        $uri = UriHelper::appendPath($uri, $path);
        if (!empty($params)) {
            $uri = UriHelper::withQueryParams($uri, $params);
        }
        if (isset($fragment)) {
            $uri = $uri->withFragment($fragment);
        }
        $access_token = $this->cliente->getAccessToken();
        $request = new Request($uri);
        $request = $request->withHeader('Authorization', $access_token);
        return $request;
    }
}