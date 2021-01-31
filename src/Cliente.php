<?php
namespace ITColima\Siitec2\Api;

use Francerz\ApiClient\AbstractClient;
use Francerz\Http\Client as HttpClient;
use Francerz\Http\HttpFactory;
use Francerz\Http\Server;
use Francerz\Http\Utils\HttpFactoryManager;
use Francerz\Http\Utils\ServerInterface;
use ITColima\Siitec2\Api\Resources\Usuario\PerfilResource;
use Psr\Http\Client\ClientInterface as HttpClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

class Cliente extends AbstractClient
{
    private $perfil = null;

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
        return parent::makeRequestAuthorizationCodeUri($scopes, $state);
    }

    public function redirectAuthUri($loginUri)
    {
        return parent::makeAuthorizeRedirUri($loginUri);
    }

    public function redirectAuthRequest($loginUri)
    {
        return parent::makeAuthorizeRedirResponse($loginUri);
    }

    public function getLoginRequest(array $scopes = [], string $state = '') : ResponseInterface
    {
        $scopes = array_merge($scopes, [Scopes::GET_USUARIO_PERFIL_OWN]);
        return parent::makeRequestAuthorizationCodeRedirect($scopes, $state);
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
        parent::setCallbackEndpoint($uri);
    }

    public function handleLogin(?ServerRequestInterface $request = null)
    {
        $at = parent::handleAuthorizeResponse($request);

        if (isset($at)) {
            $this->retrievePerfil();
        }

        return $at;
    }

    public function revoke()
    {
        $this->unsetPerfil();
        $this->revokeAcccessToken();
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
}