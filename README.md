Cliente PHP de la API de SIITEC 2
=======================================

Instalación
---------------------------------------

La instalación del paquete se puede hacer mediante **composer** utilizando el
siguiente comando:

```
composer require itcolima/siitec2-api-client
```

Inicialización
---------------------------------------

La forma de inicializar la API de SIITEC 2 es mediante la clase
`ITColima\Siitec2\Api\Siitec2Api`.

```php
use ITColima\Siitec2\Api\Siitec2Api;

// Carga manual de las variables de entorno
$_ENV['SIITEC2_API_CLIENT_ID'] = '<client_id>';
$_ENV['SIITEC2_API_CLIENT_SECRET'] = '<client_secret>';

$api = new Siitec2Api();
```

> **Carga automática de las variables de entorno `$_ENV`**
> La API de SIITEC 2 puede cargar automáticamente las variables de entorno si
> se utiliza un framework o librería que las cargue desde un archivo `.env`.
> En el archivo deberían incluirse de la siguiente manera:
> ```bash
> # Credenciales de API de SIITEC 2
> SIITEC2_API_CLIENT_ID = '<client_id>'
> SIITEC2_API_CLIENT_SECRET = '<client_secret>'
> ```

> **NOTA**  
> Los valores de los parámetros `<client_id>` y `<client_secret>` son proporcionados
> por el Departamento de Centro de Cómputo del Instituto Tecnológico de Colima.

Inicio de sesión
---------------------------------------

Una de las funcionalidades especiales de la libería API de SIITEC 2 es permitir
acceso a recursos propios de cada usuario, identificándolo mediante su inicio
de sesión.

El inicio de sesión en la API de SIITEC 2 se realiza utilizando el Framework
de Autorización OAuth 2.0, el cual permite obtener acceso a recursos protegidos
utilizando claves temporales de acceso, llamadas Access Token, y permitiendo una
operación continua.

Estos procesos de inicio de sesión requieren de una compleja red de interacciones
e intercambio de peticiones HTTP entre el Cliente (aplicación) y el
servidor de SIITEC 2. Ese complejo mecanismo se simplifica utilizando funciones
de la librería, que permiten centrarse menos en la estructura y más en la
funcionalidad.

### Ejemplos de uso

#### LLamado al inicio de sesión.

Para iniciar sesión se requiere tener un archivo o función disparadora de la
acción. En el siguiente código se muestra cómo hacer una petición para inicio
de sesión a SIITEC 2.

```php
<?php
/**
 * Archivo: login.php
 * Establecer la URI como manejadora del inicio de sesión.
 */
// Cargar liberías
use ITColima\Siitec2\Api\Siitec2Api;

// Cargar autoloader de composer.
require_once __DIR__.'/vendor/autoload.php';

// Para un correcto funcionamiento de la API se requiere contar con sesiones.
session_start();

// Inicializar instancia de API
$api = new Siitec2Api();

// Verificar si hay sesión iniciada, si es así redirigir a donde señale el
// parámetro `redir` de la URL o a "principal.php"
if ($api->getPerfil()) {
    http_response_code(307);
    header('Location', $api->getRedir('https://www.ejemplo.com/principal.php'));
    return;
}

// Estableceer URL donde se recibirá el inicio de sesión de SIITEC 2
$api->setLoginHandlerUri('https://www.ejemplo.com/login_handler.php');

// Realizar inicio de sesión con $scopes y $csrfKey opcionales.
$api->performLogin();
```

> **Variaciones**
> El código anterior está planteado para una aplicación que utilice PHP puro,
> sin un framework o libería adicional que soporte funcionalidades básicas.
> A continuación se describen algunas de las variaciones comunes para el código:
> * Es posible que el framework haga la carga automática del
>   **autoloader de composer**.
> * La redirección puede cambiar dependiendo del framework, a continuación se
>   incluyen algunos ejemplos con un framework distinto:
>   * **CodeIgniter 3**
>     ```php
>     if ($api->getPerfil()) {
>         redirect($api->getRedir(site_url('principal')));
>     }
>     $api->setLoginHandlerUri(site_url('login_handler'));
>     $api->performLogin();
>     ```
>  * **CodeIgniter 4**
>    ```php
>    if ($api->getPerfil()) {
>         return redirect()->to($api->getRedir(site_url('principal')));
>    }
>    $api->setLoginHandlerUri(site_url('login_handler'));
>    return CodeIgniter4::outputResponsePsr7($this->response, $api->getLoginRequest());
>    ```
>    > Se requiere instalar el paquete `francerz/utils` para utilizar
>    > `Francerz\Utils\Frameworks\CodeIgniter4`.

#### Manejo de respuesta del inicio de sesión

Una vez iniciada la acción el servidor solicitará la autorización de acceso al
usuario y cuando se obtenga un resultado, este será devuelto a la URI manejadora
del inicio de sesión.

```php
<?php
/**
 * Archivo: login_handler.php
 * Manejar la respuesta del servidor al iniciar sesión.
 */
// Cargar liberías
use ITColima\Siitec2\Api\Siitec2Api;

// Cargar autoloader de composer.
require_once __DIR__.'/vendor/autoload.php';

// Para un correcto funcionamiento de la API se requiere contar con sesiones.
session_start();

// Inicializar instancia de API
$api = new Siitec2Api();

// Capturar la petición entrante y permitir a la librería gestionar el proceso.
$api->handleLogin();

// Una vez concluído el inicio de sesión, redirigir a principal.php
http_response_code(307);
header('Location', $api->getRedir('https://www.ejemplo.com/principal.php'));
```

#### Acceso a los datos del usuario identificado

Uno de los comportamientos básicos esperados al iniciar sesión es identificar
al usuario que haya ingresado al sistema. Para acceder a estos datos, la
API de manera automática hace la recuperación desde el servidor y los almacena
temporalmente durante la sesión.

```php
<?php
/**
 * Archivo: perfil.php
 */
// Cargar librerías
use ITColima\Siitec2\Api\Siitec2Api;


```

