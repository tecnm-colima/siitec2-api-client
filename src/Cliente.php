<?php
namespace ITColima\Siitec2\Api;

use Francerz\Http\Helpers\UriHelper;
use Francerz\Http\Uri;
use Francerz\OAuth2\AccessToken;
use Francerz\OAuth2\Flow\AuthorizationCodeRequest;
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
            ->withAuthorizationEndpoint(UriHelper::appendPath($this->getAuthUrlBase(), '/oauth2/request'))
            ->withTokenEndpoint(UriHelper::appendPath($this->getAuthUrlBase(), '/oauth2/access_token'));
    }
    public function loadConfigFile($config)
    {
        $config = json_decode(file_get_contents($config));
        $this->oauth2 = $this->oauth2
            ->withClientId($config->client_id)
            ->withClientSecret($config->client_secret);
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

    private function getAuthUrlBase() : UriInterface
    {
        $uri = new Uri();
        $uri = $uri
            ->withScheme(Constants::AUTH_PROTOCOL)
            ->withHost(Constants::AUTH_HOST)
            ->withPath(Constants::AUTH_PATH);
        return $uri;
    }
    public function getAuthCodeUri(UriInterface $redirect_uri = null, string $state = null) : UriInterface
    {
        $request = new AuthorizationCodeRequest($this->oauth2);
        if (isset($redirect_uri)) {
            $request = $request->withRedirectUri($redirect_uri);
        }
        if (isset($state)) {
            $request = $request->withState($state);
        }
        return $request->getRequestUri();
    }

    public function redeemAuthCode(string $code, UriInterface $redirect_uri = null) : ?AccessToken
    {
        return $this->oauth2->redeemAuthCode(
            $this->oauth2->getAuthorizationEndpoint(),
            $code,
            $redirect_uri
        );
    }

    public function handleAuthCodeRequest(RequestInterface $request)
    {
        $this->oauth2->handleAuthCodeRequest($request);
    }
}