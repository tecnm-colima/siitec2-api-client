<?php
namespace ITColima\Siitec2\Api;

use Francerz\ApiClient\AbstractClient;
use Francerz\Http\Client as HttpClient;
use Francerz\Http\HttpFactory;
use Francerz\Http\Server;
use Francerz\Http\Utils\Constants\StatusCodes;
use Francerz\Http\Utils\HttpFactoryManager;
use Francerz\Http\Utils\MessageHelper;
use Francerz\Http\Utils\ServerInterface;
use Francerz\Http\Utils\UriHelper;
use InvalidArgumentException;
use Psr\Http\Client\ClientInterface as HttpClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

class Cliente extends AbstractClient
{  
    private $redirUri = null;
    public function __construct(
        ?string $configFile = null,
        ?HttpFactoryManager $httpFactory = null,
        ?HttpClientInterface $httpClient = null
    ) {
        $httpFactory = isset($httpFactory) ? $httpFactory : new HttpFactoryManager(new HttpFactory());
        $httpClient = isset($httpClient) ? $httpClient : new HttpClient();
        parent::__construct($httpFactory, $httpClient);

        $this->getOAuth2Client()->setAuthorizationEndpoint(Constants::AUTHORIZE_ENDPOINT);
        $this->getOAuth2Client()->setTokenEndpoint(Constants::TOKEN_ENDPOINT);
        $this->setApiEndpoint(Constants::API_ENDPOINT);

        $this->setAccessTokenSessionKey('siitec2.access_token');
        $this->setClientAccessTokenSessionKey('siitec2.client_access_token');

        $this->loadDefaultAccessTokenHandlers();
        $this->loadDefaultClientAccessTokenHandlers();

        $this->loadAccessToken();
        $this->loadClientAccessToken();

        if (isset($configFile)) {
            $this->loadConfigFile($configFile);
        }
        $this->loadConfigEnv();   
    }

    private function loadConfigEnv()
    {   
        if (array_key_exists('siitec2.api.client_id', $_ENV)) {
            $this->setClientId($_ENV['siitec2.api.client_id']);
        }
        if (array_key_exists('siitec2.api.client_secret', $_ENV)) {
            $this->setClientSecret($_ENV['siitec2.api.client_secret']);
        }
        if (array_key_exists('SIITEC2_API_CLIENT_ID', $_ENV)) {
            $this->setClientId($_ENV['SIITEC2_API_CLIENT_ID']);
        }
        if (array_key_exists('SIITEC2_API_CLIENT_SECRET', $_ENV)) {
            $this->setClientSecret($_ENV['SIITEC2_API_CLIENT_SECRET']);
        }
    }

    public function loadConfigFile(string $config)
    {
        $config = json_decode(file_get_contents($config));
        $this->setClientId($config->client_id);
        $this->setClientSecret($config->client_secret);
        if (isset($config->callback_endpoint)) {
            $this->getOauth2Client()->setCallbackEndpoint($config->callback_endpoint);
        }
    }

    public static function getPlatformUrl() : string
    {
        return Constants::PLATFORM_URL;
    }

    public function getOAuth2Client()
    {
        return parent::getOAuth2Client();
    }

    public function getAuthCodeUri(array $scopes = [], string $state = '') : UriInterface
    {
        return $this->getOAuth2Client()->getAuthorizationCodeRequestUri($scopes, $state);
    }

    public function requireLogin($loginUri)
    {
        $uriFactory = $this->getHttpFactoryManager()->getUriFactory();
        $responseFactory = $this->getHttpFactoryManager()->getResponseFactory();

        if (is_string($loginUri)) {
            $loginUri = $uriFactory->createUri($loginUri);
        }

        $url = UriHelper::getCurrent($uriFactory);
        $loginUri = UriHelper::withQueryParam($loginUri, 'redir', $url);

        $response = $responseFactory
            ->createResponse(StatusCodes::REDIRECT_TEMPORARY_REDIRECT)
            ->withHeader('Location', $loginUri);
        return $response;
    }

    public function getLoginRequest(array $scopes = [], string $state = '') : ResponseInterface
    {
        $responseFactory = $this->getHttpFactoryManager()->getResponseFactory();
        $uriFactory = $this->getHttpFactoryManager()->getUriFactory();


        $authUri = $this->getAuthCodeUri($scopes, $state);

        # Adds redirection to invoked source
        $currentUri = UriHelper::getCurrent($uriFactory);
        $redir = UriHelper::getQueryParam($currentUri, 'redir');
        if (!empty($redir)) {
            $redirectUri = $uriFactory->createUri(
                UriHelper::getQueryParam($authUri, 'redirect_uri')
            );
            $redirectUri = UriHelper::withQueryParam($redirectUri, 'redir', $redir);
            $authUri = UriHelper::withQueryParam($authUri, 'redirect_uri', $redirectUri);
        }

        $response = $responseFactory
            ->createResponse(StatusCodes::REDIRECT_TEMPORARY_REDIRECT)
            ->withHeader('Location', $authUri);
        return $response;
    }

    public function performLogin( array $scopes = [], string $state = '', ?ServerInterface $server = null)
    {
        if (is_null($server)) {
            $server = new Server();
        }
        $response = $this->getLoginRequest($scopes, $state);
        $server->emitResponse($response);
    }

    public function setLoginHandlerUri($uri)
    {
        if (is_string($uri)) {
            $uri = $this->getHttpFactoryManager()->getUriFactory()->createUri($uri);
        }
        if (!$uri instanceof UriInterface) {
            throw new InvalidArgumentException(__METHOD__.' $uri argument must be string or UriInterface object');
        }
        $this->getOAuth2Client()->setCallbackEndpoint($uri);
    }

    public function handleLogin(?ServerRequestInterface $request = null)
    {
        if (is_null($request)) {
            MessageHelper::setHttpFactoryManager($this->getHttpFactoryManager());
            $request = MessageHelper::getCurrentRequest();
        }
        $requestUri = $request->getUri();
        $this->redirUri = UriHelper::getQueryParam($requestUri, 'redir');
        $this->getOAuth2Client()->handleCallbackRequest($request);
    }

    public function getRedir($defaultUri)
    {
        if (isset($this->redirUri)) {
            return $this->redirUri;
        }
        return $defaultUri;
    }

    public function revoke()
    {
        $this->revokeAcccessToken();
    }
}