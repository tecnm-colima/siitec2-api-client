Cliente PHP de la API de SIITEC 2
=======================================

Instalación
---------------------------------------

La instalación del paquete se puede hacer mediante **composer** utilizando el
siguiente comando:

```
composer install itcolima/siitec2-api-lib
```

Inicialización
---------------------------------------

La forma de inicializar la clase cliente de SIITEC 2, es mediante la carga
de la clase Cliente.

```php
use ITColima\Siitec2\Api\Cliente;

$cliente = new Cliente();
```

Para utilizar la conectividad se requiere utilizar un client_id y client_secret
válidos, los cuales pueden asignarse mediante los *accesores* `setClientId` y
`setClientSecret`.

```php
// Utilizando accesores
$cliente->setClientId('<client_id>');
$cliente->setClientSecret('<client_secret>');
```

También pueden asignarse estos archivos utilizando un archivo json con la
configuración requerida en el siguiente formato.

```json
{
    "client_id": "<client_id>",
    "client_secret": "<client_secret>"
}
```

Posteriormente utilizar el método `loadConfigFile` para establecer la ruta de
consulta de la configuración.

```php
// Utilizando archivo de configuración
$cliente->loadConfigFile('/ruta/al/archivo.json');
```

Inicio de sesión
---------------------------------------

Una de las funcionalidades especiales de la libería API de SIITEC 2 es permitir
acceso a recursos propios de cada usuario, identificándolo mediante su inicio
de sesión.

El inicio de sesión en la API de SIITEC 2 se realiza utilizando el framework
de autorización OAuth 2.0, el cual permite obtener acceso a recursos protegidos
utilizando claves temporales de acceso y permitiendo una continua operación.

Estos procesos de inicio de sesión requieren de una compleja red de interacciones
e intercambio de peticiones HTTP. Ese complejo mecanismo se simplifica
utilizando funciones de la librería, que permiten centrarse menos en la
estructura y más en la funcionalidad.

Para iniciar sesión se requiere tener un archivo o función disparadora de la
acción.

```php
// Archivo: login.php
// Establecer la URI como manejadora del inicio de sesión.
$cliente->setLoginHandlerUri('https://www.ejemplo.com/login_handler.php');

// Inicia la acción de inicio de sesión.
$cliente->performLogin();
```

Una vez iniciada la acción el servidor solicitará la autorización de acceso al
usuario y cuando se obtenga un resultado, este será devuelto a la URI manejadora
del inicio de sesión.

```php
// Archivo: login_handler.php

// Capturar la petición entrante y permitir a la librería gestionar el proceso.
$cliente->handleLogin();
```

> **NOTA**  
> Es probable que el código descrito anteriormente varíe dependiendo de la
> organización que se tenga, por ejemplo, utilizando un framework MVC.
