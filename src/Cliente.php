<?php
namespace ITColima\Siitec2\Api;

use Francerz\Http\Utils\Constants\StatusCodes;
use Francerz\Http\Utils\HttpFactoryManager;
use Francerz\Http\Utils\ServerInterface;
use Francerz\OAuth2\AccessToken;
use Francerz\OAuth2\Client\AuthClient;
use Francerz\PowerData\Functions;
use InvalidArgumentException;
use Psr\Http\Client\ClientInterface as HttpClient;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

class Cliente
{
    private $oauth2;
    private $httpFactory;
    private $httpClient;

    private $apiEndpoint;

    private $handlerAccessTokenChanged;
    private $handlerAccessTokenLoad;

    public function __construct(HttpFactoryManager $httpFactory, HttpClient $httpClient)
    {
        $this->httpFactory = $httpFactory;
        $this->httpClient = $httpClient;
        $this->oauth2 = new AuthClient($httpFactory, $httpClient);
        $this->setAuthorizeEndpoint(Constants::AUTHORIZE_ENDPOINT);
        $this->setTokenEndpoint(Constants::TOKEN_ENDPOINT);
        $this->setApiEndpoint(Constants::API_ENDPOINT);

        $this->handlerAccessTokenChanged = function(AccessToken $accessToken) {
            $_SESSION['access_token'] = $accessToken;
        };
        $this->handlerAccessTokenLoad = function() {
            if (isset($_SESSION['access_token']) && $_SESSION['access_token'] instanceof AccessToken) {
                $this->setAccessToken($_SESSION['access_token']);
            }
        };

        $self = $this;
        $this->oauth2->setAccessTokenChangedHandler(function(AccessToken $accessToken) use ($self) {
            call_user_func($self->handlerAccessTokenChanged, $accessToken);
        });
        $this->loadAccessToken();
    }

    public function setClientId(string $client_id)
    {
        $this->oauth2->setClientId($client_id);
    }

    public function setClientSecret(string $client_secret)
    {
        $this->oauth2->setClientSecret($client_secret);
    }

    public function loadConfigFile($config)
    {
        $config = json_decode(file_get_contents($config));
        $this->oauth2->setClientId($config->client_id);
        $this->oauth2->setClientSecret($config->client_secret);
        if (isset($config->callback_endpoint)) {
            $this->oauth2->setCallbackEndpoint($config->callback_endpoint);
        }
    }

    public function setAuthorizeEndpoint($uri)
    {
        if (is_string($uri)) {
            $uri = $this->httpFactory->getUriFactory()->createUri($uri);
        }
        if (!$uri instanceof UriInterface) {
            throw new InvalidArgumentException(__METHOD__.' $uri argument must be string or UriInterface object');
        }
        $this->oauth2->setAuthorizationEndpoint($uri);
    }

    public function setTokenEndpoint($uri)
    {
        if (is_string($uri)) {
            $uri = $this->httpFactory->getUriFactory()->createUri($uri);
        }
        if (!$uri instanceof UriInterface) {
            throw new InvalidArgumentException(__METHOD__.' $uri argument must be string or UriInterface object');
        }
        $this->oauth2->setTokenEndpoint($uri);
    }

    public function setApiEndpoint($uri)
    {
        if (is_string($uri)) {
            $uri = $this->httpFactory->getUriFactory()->createUri($uri);
        }
        if (!$uri instanceof UriInterface) {
            throw new InvalidArgumentException(__METHOD__.' $uri argument must be string or UriInterface object');
        }
        $this->apiEndpoint = $uri;
    }

    public function setAccessToken(AccessToken $accessToken)
    {
        $this->oauth2->setAccessToken($accessToken);
    }

    public function getAccessToken()
    {
        return $this->oauth2->getAccessToken();
    }

    public function loadAccessToken()
    {
        call_user_func($this->handlerAccessTokenLoad);
    }

    public function setAccessTokenLoadHandler(callable $handler)
    {
        $this->handlerAccessTokenLoad = $handler;
    }

    public function setAccessTokenChangedHandler(callable $handler)
    {
        if (!Functions::testSignature($handler, [AccessToken::class])) {
            throw new InvalidArgumentException('AccessTokenChanged handler signature must be: func(AccessToken)');
        }
        $this->handlerAccessTokenChanged = $handler;
    }

    public function getHttpFactory() : HttpFactoryManager
    {
        return $this->httpFactory;
    }

    public function getHttpClient() : HttpClient
    {
        return $this->httpClient;
    }

    public function getUserAuth() : AuthClient
    {
        return $this->oauth2;
    }

    public function getApiUri() : UriInterface
    {
        return $this->apiEndpoint;
    }

    public function getAuthCodeUri(array $scopes = [], string $state = '') : UriInterface
    {
        return $this->oauth2->getAuthorizationCodeRequestUri($scopes, $state);
    }

    public function getLoginRequest(array $scopes = [], string $state = '') : ResponseInterface
    {
        $responseFactory = $this->httpFactory->getResponseFactory();
        $authUri = $this->getAuthCodeUri($scopes, $state);
        $response = $responseFactory
            ->createResponse(StatusCodes::REDIRECT_TEMPORARY_REDIRECT)
            ->withHeader('Location', $authUri);
        return $response;
    }

    public function performLogin(ServerInterface $server, array $scopes = [], string $state = '')
    {
        $response = $this->getLoginRequest($scopes, $state);
        $server->emitResponse($response);
    }

    public function setLoginHandlerUri(UriInterface $uri)
    {
        $this->oauth2->setCallbackEndpoint($uri);
    }

    private function getCurrentUri() : UriInterface
    {
        $uri = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
        $uri.= '://';
        $uri.= $_SERVER['HTTP_HOST'];
        $uri.= $_SERVER['REQUEST_URI'];
        return $this->httpFactory->getUriFactory()->createUri($uri);   
    }

    private function getCurrentBodyStream()
    {
        return $this->httpFactory->getStreamFactory()
            ->createStream(file_get_contents('php://input'));
    }

    private function getCurrentServerRequest()
    {
        $sp = $_SERVER['SERVER_PROTOCOL'];
        $sp = substr($sp, strpos($sp, '/') + 1);

        $srf = $this->httpFactory->getServerRequestFactory();
        $req = $srf->createServerRequest($_SERVER['REQUEST_METHOD'], $this->getCurrentUri(), $_SERVER)
            ->withProtocolVersion($sp)
            ->withBody($this->getCurrentBodyStream());
        
        $headers = getallheaders();
        foreach ($headers as $hname => $hcontent) {
            $req->withHeader($hname, preg_split('/(,\\s*)/', $hcontent));
        }

        return $req;
    }

    public function handleLogin(?ServerRequestInterface $request)
    {
        if (is_null($request)) {
            $request = $this->getCurrentServerRequest();
        }
        $this->oauth2->handleCallbackRequest($request);
    }
}