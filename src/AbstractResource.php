<?php

namespace ITColima\Siitec2\Api;

use Francerz\Http\Utils\Constants\MediaTypes;
use Francerz\Http\Utils\Constants\Methods;
use Francerz\Http\Utils\Exceptions\ClientErrorException;
use Francerz\Http\Utils\Exceptions\ServerErrorException;
use Francerz\Http\Utils\MessageHelper;
use Francerz\Http\Utils\UriHelper;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractResource
{
    private $cliente;
    private $requiresAccessToken;
    private $requiresClientAccessToken;

    public function __construct(Cliente $cliente)
    {
        $this->cliente = $cliente;
        $this->requiresAccessToken = false;
        $this->requiresClientAccessToken = false;
    }

    protected function requiresAccessToken(bool $requires = true)
    {
        $this->requiresAccessToken = $requires;
    }

    protected function requiresClientAccessToken(bool $requires = true)
    {
        $this->requiresClientAccessToken = $requires;
    }

    protected function buildRequest(
        string $method, 
        string $path,
        array $params = [],
        $content = null,
        string $mediaType = MediaTypes::APPLICATION_X_WWW_FORM_URLENCODED) : RequestInterface
    {
        $uri = $this->cliente->getApiEndpoint();
        $uri = UriHelper::appendPath($uri, $path);
        if (!empty($params)) {
            $uri = UriHelper::withQueryParams($uri, $params);
        }
        if (isset($fragment)) {
            $uri = $uri->withFragment($fragment);
        }

        $requestFactory = $this->cliente->getHttpFactoryManager()->getRequestFactory();
        $request = $requestFactory->createRequest($method, $uri);
        if ($this->requiresAccessToken) {
            $request = $this->cliente->getOAuth2Client()->bindAccessToken($request);
        } elseif ($this->requiresClientAccessToken) {
            $request = $this->cliente->getOAuth2Client()->bindClientAccessToken($request);
        }

        if (isset($content)) {
            MessageHelper::setHttpFactoryManager($this->cliente->getHttpFactoryManager());
            $request = MessageHelper::withContent($request, $mediaType, $content);
        }

        return $request;
    }

    protected function sendRequest(RequestInterface $request) : ResponseInterface
    {
        $response = $this->cliente->getHttpClient()->sendRequest($request);
        if (MessageHelper::isClientError($response)) {
            throw new ClientErrorException($request, $response, "HTTP Client error: {$response->getStatusCode()}".print_r($response->getBody(), true));
        } elseif (MessageHelper::isServerError($response)) {
            throw new ServerErrorException($request, $response, "HTTP Server error: {$response->getStatusCode()}".print_r($response->getBody(), true));
        }
        return $response;
    }

    protected function _get(string $path, array $params = [])
    {
        $request = $this->buildRequest(Methods::GET, $path, $params);
        return $this->sendRequest($request);
    }

    protected function _post(string $path, $content, string $mediaType = MediaTypes::APPLICATION_X_WWW_FORM_URLENCODED)
    {
        $request = $this->buildRequest(Methods::POST, $path, [], $content, $mediaType);
        return $this->sendRequest($request);
    }

    protected function _put(string $path, $content, string $mediaType = MediaTypes::APPLICATION_X_WWW_FORM_URLENCODED)
    {
        $request = $this->buildRequest(Methods::PUT, $path, [], $content, $mediaType);
        return $this->sendRequest($request);
    }
    
    protected function _patch(string $path, $content, string $mediaType = MediaTypes::APPLICATION_X_WWW_FORM_URLENCODED)
    {
        $request = $this->buildRequest(Methods::PATCH, $path, [], $content, $mediaType);
        return $this->sendRequest($request);
    }

    protected function _delete(string $path)
    {
        $request = $this->buildRequest(Methods::DELETE, $path);
        return $this->sendRequest($request);
    }
}