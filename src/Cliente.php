<?php
namespace ITColima\Siitec2\Api;

use Francerz\Http\Helpers\UriHelper;
use Francerz\Http\Response;
use Francerz\Http\Server;
use Francerz\Http\Uri;
use Francerz\OAuth2\Roles\AuthClient;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class Cliente
{
    private $oauth2;

    public function __construct()
    {
        $this->oauth2 = new AuthClient();
        $this->oauth2 = $this->oauth2
            ->withAuthorizationEndpoint(UriHelper::appendPath($this->getAuthUriBase(), '/oauth2/request'))
            ->withTokenEndpoint(UriHelper::appendPath($this->getAuthUriBase(), '/oauth2/access_token'));
    }
    public function loadConfigFile($config)
    {
        $config = json_decode(file_get_contents($config));
        $this->oauth2 = $this->oauth2
            ->withClientId($config->client_id)
            ->withClientSecret($config->client_secret);
        if (isset($config->callback_endpoint)) {
            $this->oauth2 = $this->oauth2->withCallbackEndpoint(new Uri($config->callback_endpoint));
        }
    }
    public function getAccessToken()
    {
        return $this->oauth2->getAccessToken();
    }

    public function getApiUri() : UriInterface
    {
        $uri = new Uri();
        $uri = $uri
            ->withScheme(Constants::API_PROTOCOL)
            ->withHost(Constants::API_HOST)
            ->withPath(Constants::API_PATH);
        return $uri;
    }

    private function getAuthUriBase() : UriInterface
    {
        $uri = new Uri();
        $uri = $uri
            ->withScheme(Constants::AUTH_PROTOCOL)
            ->withHost(Constants::AUTH_HOST)
            ->withPath(Constants::AUTH_PATH);
        return $uri;
    }

    public function getAuthCodeUri(array $scopes = [], string $state = '') : UriInterface
    {
        return $this->oauth2->getAuthorizationCodeRequestUri($scopes, $state);
    }

    public function getLoginRequest(array $scopes = [], string $state = '') : Response
    {
        $authUri = $this->getAuthCodeUri($scopes, $state);
        $response = new Response();
        $response = $response->withHeader('Location', $authUri);
        return $response;
    }

    public function performLogin(array $scopes = [], string $state = '')
    {
        $response = $this->getLoginRequest($scopes, $state);
        Server::output($response);
    }

    public function setLoginHandlerUri(UriInterface $uri)
    {
        $this->oauth2 = $this->oauth2->withCallbackEndpoint($uri);
    }

    public function handleLogin(RequestInterface $request)
    {
        $this->oauth2->handleAuthCodeRequest($request);
    }
}