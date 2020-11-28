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

    public function __construct(Cliente $cliente)
    {
        $this->cliente = $cliente;
        $this->requiresAccessToken = false;
    }

    protected function requiresAccessToken(bool $requires = true)
    {
        $this->requiresAccessToken = $requires;
    }

    protected function buildRequest(
        string $method, 
        string $path,
        array $params = [],
        $content = null,
        string $mediaType = MediaTypes::APPLICATION_X_WWW_FORM_URLENCODED) : RequestInterface
    {
        $uri = $this->cliente->getApiUri();
        $uri = UriHelper::appendPath($uri, $path);
        if (!empty($params)) {
            $uri = UriHelper::withQueryParams($uri, $params);
        }
        if (isset($fragment)) {
            $uri = $uri->withFragment($fragment);
        }

        $requestFactory = $this->cliente->getHttpFactory()->getRequestFactory();
        $request = $requestFactory->createRequest($method, $uri);
        if ($this->requiresAccessToken) {
            $request = $this->cliente->getUserAuth()->bindAccessToken($request);
        }

        if (isset($content)) {
            $request = MessageHelper::withContent($request, $mediaType, $content);
        }

        return $request;
    }

    protected function sendRequest(RequestInterface $request) : ResponseInterface
    {
        $response = $this->cliente->getHttpClient()->sendRequest($request);
        if (MessageHelper::isClientError($response)) {
            throw new ClientErrorException($request, $response, "HTTP Client error: {$response->getStatusCode()}");
        } elseif (MessageHelper::isServerError($response)) {
            throw new ServerErrorException($request, $response, "HTTP Server error: {$response->getStatusCode()}");
        }
        return $response;
    }

    protected function get(string $path, array $params = [])
    {
        $request = $this->buildRequest(Methods::GET, $path, $params);
        return $this->sendRequest($request);
    }

    protected function post(string $path, $content, string $mediaType = MediaTypes::APPLICATION_X_WWW_FORM_URLENCODED)
    {
        $request = $this->buildRequest(Methods::POST, $path, [], $content, $mediaType);
        return $this->sendRequest($request);
    }

    protected function put(string $path, $content, string $mediaType = MediaTypes::APPLICATION_X_WWW_FORM_URLENCODED)
    {
        $request = $this->buildRequest(Methods::PUT, $path, [], $content, $mediaType);
        return $this->sendRequest($request);
    }
    
    protected function patch(string $path, $content, string $mediaType = MediaTypes::APPLICATION_X_WWW_FORM_URLENCODED)
    {
        $request = $this->buildRequest(Methods::PATCH, $path, [], $content, $mediaType);
        return $this->sendRequest($request);
    }

    protected function delete(string $path)
    {
        $request = $this->buildRequest(Methods::DELETE, $path);
        return $this->sendRequest($request);
    }
}