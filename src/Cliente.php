<?php
namespace ITColima\Siitec2\Api;

use Francerz\ApiClient\AbstractClient;
use Francerz\Http\Client as HttpClient;
use Francerz\Http\HttpFactory;
use Francerz\Http\Server;
use Francerz\Http\Utils\HttpFactoryManager;
use Francerz\Http\Utils\MessageHelper;
use Francerz\Http\Utils\ServerInterface;
use Francerz\Http\Utils\UriHelper;
use Francerz\OAuth2\ScopeHelper;
use InvalidArgumentException;
use ITColima\Siitec2\Api\Resources\Usuario\PerfilResource;
use Psr\Http\Client\ClientInterface as HttpClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

class Cliente extends AbstractClient
{
    private $perfil = null;

    private $loginHandlerUri = null;
    private $logoutUri = null;

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
        if (array_key_exists('SIITEC2_API_CLIENT_ID', $_ENV)) {
            $this->setClientId($_ENV['SIITEC2_API_CLIENT_ID']);
        }
        if (array_key_exists('SIITEC2_API_CLIENT_SECRET', $_ENV)) {
            $this->setClientSecret($_ENV['SIITEC2_API_CLIENT_SECRET']);
        }
        if (array_key_exists('SIITEC2_API_CLIENT_LOGOUT_URI', $_ENV)) {
            $this->logoutUri = $_ENV['SIITEC2_API_CLIENT_LOGOUT_URI'];
        } 
        if (array_key_exists('SIITEC2_API_AUTHORIZE_ENDPOINT', $_ENV)) {
            $this->getOAuth2Client()->setAuthorizationEndpoint($_ENV['SIITEC2_API_AUTHORIZE_ENDPOINT']);
        }
        if (array_key_exists('SIITEC2_API_TOKEN_ENDPOINT', $_ENV)) {
            $this->getOAuth2Client()->setTokenEndpoint($_ENV['SIITEC2_API_TOKEN_ENDPOINT']);
        }
        if (array_key_exists('SIITEC2_API_RESOURCE_ENDPOINT', $_ENV)) {
            $this->setApiEndpoint($_ENV['SIITEC2_API_RESOURCE_ENDPOINT']);
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

    public static function getLogoutUrl() : string
    {
        return Constants::PLATFORM_URL.'/index.php/usuarios/logout';
    }

    public function getOAuth2Client()
    {
        return parent::getOAuth2Client();
    }

    /**
     * Creates an URI for login and 'redir' query parameter to return after
     * login process completed.
     *
     * @param string|UriInterface $loginUri
     * @return UriInterface
     */
    public function redirectAuthUri($loginUri)
    {
        return parent::makeAuthorizeRedirUri($loginUri);
    }

    /**
     * @deprecated 0.1.17
     * Creates a Redirect response for redirectAuthUri()
     *
     * @param string|UriInterface $loginUri
     * @return ResponseInterface
     */
    public function redirectAuthRequest($loginUri)
    {
        $uri = $this->redirectAuthUri($loginUri);
        MessageHelper::setHttpFactoryManager($this->getHttpFactoryManager());
        return MessageHelper::makeRedirect($uri);
    }

    /**
     * Adds 'redir' parameter from login to login_handler.
     *
     * @param UriInterface $handlerUri
     * @return void
     */
    protected function addFollowParameters(UriInterface $handlerUri)
    {
        $uriFactory = $this->getHttpFactoryManager()->getUriFactory();
        $currentUri = UriHelper::getCurrent($uriFactory);
        return UriHelper::copyQueryParams($currentUri, $handlerUri, ['redir']);
    }

    /**
     * @deprecated 0.1.17
     * Retrieves OAuth 2.0 Authorization Code Uri adapted to given $scopes and $state
     *
     * @param array $scopes
     * @param string $state
     * @return UriInterface
     */
    public function getAuthCodeUri(array $scopes = [], string $state = '') : UriInterface
    {
        $loginHandlerUri = $this->addFollowParameters($this->loginHandlerUri);
        $scopes = ScopeHelper::merge($scopes, [Scopes::GET_USUARIO_PERFIL_OWN]);
        return parent::makeRequestAuthorizationCodeUri($loginHandlerUri, $scopes, $state);
    }

    public function getLoginRequest(array $scopes = [], string $state = '') : ResponseInterface
    {
        $authCodeUri = $this->getAuthCodeUri($scopes, $state);
        MessageHelper::setHttpFactoryManager($this->getHttpFactoryManager());
        return MessageHelper::makeRedirect($authCodeUri);
    }

    public function performLogin(array $scopes = [], string $state = '', ?ServerInterface $server = null)
    {
        if (is_null($server)) {
            $server = new Server();
        }
        $response = $this->getLoginRequest($scopes, $state);
        $server->emitResponse($response);
    }

    public function setLoginHandlerUri($uri)
    {
        $uriFactory = $this->getHttpFactoryManager()->getUriFactory();
        if (is_string($uri)) {
            $uri = $uriFactory->createUri($uri);
        }
        $this->loginHandlerUri = $uri;
    }

    public function login($handlerUri, $logoutUri, array $scopes = [], string $state = '')
    {
        $uriFactory = $this->getHttpFactoryManager()->getUriFactory();

        if (is_string($handlerUri)) {
            $handlerUri = $uriFactory->createUri($handlerUri);
        }
        if (!$handlerUri instanceof UriInterface) {
            throw new InvalidArgumentException('Invalid $handlerUri.');
        }

        if (is_string($logoutUri)) {
            $logoutUri = $uriFactory->createUri($logoutUri);
        }
        if (!$logoutUri instanceof UriInterface) {
            throw new InvalidArgumentException('Invalid $logoutUri.');
        }

        $handlerUri = $this->addFollowParameters($handlerUri);
        $scopes = ScopeHelper::merge($scopes, [Scopes::GET_USUARIO_PERFIL_OWN]);
        $uri = parent::makeRequestAuthorizationCodeUri($handlerUri, $scopes, $state);

        $uri = UriHelper::withQueryParam($uri, 'logout', $logoutUri);
        
        MessageHelper::setHttpFactoryManager($this->getHttpFactoryManager());
        return MessageHelper::makeRedirect($uri);
    }

    public function handleLogin(?ServerRequestInterface $request = null)
    {
        $at = parent::handleAuthorizeResponse($request);

        if (isset($at)) {
            $this->retrievePerfil();
        }

        return $at;
    }

    #region Perfil (ResourceOwner)
    private function retrievePerfil()
    {
        $perfilResource = new PerfilResource($this);
        $this->perfil = $_SESSION['siitec2.perfil'] = $perfilResource->getOwn();
    }

    private function loadPerfilFromSession()
    {
        $s2pk = 'siitec2.perfil';
        if (array_key_exists($s2pk, $_SESSION) && is_object($_SESSION[$s2pk])) {
            $this->perfil = $_SESSION[$s2pk];
        }
    }

    private function unsetPerfil()
    {
        unset($this->perfil);
        unset($_SESSION['siitec2.perfil']);
    }

    public function getPerfil()
    {
        if (is_null($this->perfil)) {
            $this->loadPerfilFromSession();
        }
        return $this->perfil;
    }
    #endregion

    /**
     * Checks that User (Resource Owner) granted access.
     *
     * @return boolean
     */
    public function isLoggedIn()
    {
        return !is_null($this->getPerfil());
    }

    /**
     * Revokes all tokens and user data.
     *
     * @return void
     */
    public function revoke()
    {
        $this->unsetPerfil();
        $this->revokeAcccessToken();
    }

    /**
     * Handles direct logout.
     *
     * @return ResponseInterface
     */
    public function handleLogout() : ResponseInterface
    {
        $this->revoke();

        MessageHelper::setHttpFactoryManager($this->getHttpFactoryManager());
        $currentUri = UriHelper::getCurrent($this->getHttpFactoryManager()->getUriFactory());
        $continue = UriHelper::getQueryParam($currentUri, 'continue');

        if (!empty($continue)) {
            return MessageHelper::makeRedirect($continue);
        }
        return MessageHelper::makeRedirect(static::getPlatformUrl());
    }
}