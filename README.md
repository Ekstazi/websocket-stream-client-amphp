# websocket-stream-client-amphp
`ekstazi/websocket-stream-client-amphp` is `ekstazi/websocket-stream-client` implementation based on `amphp/websocket-client`
# Installation
This package can be installed as a Composer dependency.

`composer require ekstazi/websocket-stream-client-amphp`
# Requirements
PHP 7.2+
# Usage
## With container
If you have container then add this to your `container.php`
```php
use Amp\Websocket\Options;
use \ekstazi\websocket\client\amphp\ConnectorFactory;
use \ekstazi\websocket\client\ConnectionFactory;

// ....

return [
    ConnectionFactory::class => new ConnectorFactory(),
    // this is optional config for default options to connections
    "config" => [
        "websocket" => [
            'clientOptions' => Options::createClientDefault(),
        ]
    ]
];
```
Then in your code:
```php
use \Psr\Container\ContainerInterface;
use \ekstazi\websocket\client\ConnectionFactory;
use \Psr\Http\Message\RequestInterface;
use \ekstazi\websocket\client\Connection;

/** @var ContainerInterface $container */
/** @var ConnectionFactory $connector */
/** @var RequestInterface $request */

$connector = $container->get(ConnectionFactory::class);

/** @var Connection $stream */
$stream = yield $connector->connect($request, Connection::MODE_BINARY);

```

## Without container
You can use functions to do the same:
```php
use \Psr\Http\Message\RequestInterface;
use \ekstazi\websocket\client\Connection;

use function \ekstazi\websocket\client\connect;

/** @var RequestInterface $request */
/** @var Connection $stream */
$stream = yield connect($request, Connection::MODE_BINARY);
```
or
```php
use \ekstazi\websocket\client\ConnectionFactory;
use \Psr\Http\Message\RequestInterface;
use \ekstazi\websocket\client\Connection;

use function \ekstazi\websocket\client\connector;

/** @var RequestInterface $request */
/** @var ConnectionFactory $connector */
$connector = connector();

/** @var Connection $stream */
$stream = yield $connector->connect($request, Connection::MODE_BINARY);
```

## Passing additional options to connection
```php
use Amp\Websocket\Options;
use \ekstazi\websocket\client\ConnectionFactory;
use \Psr\Http\Message\RequestInterface;
use \ekstazi\websocket\client\Connection;

use function \ekstazi\websocket\client\connector;

/** @var RequestInterface $request */
/** @var ConnectionFactory $connector */
$connector = connector();

/** @var Connection $stream */
$stream = yield $connector->connect($request, Connection::MODE_BINARY, Options::createClientDefault()->withoutHeartbeat());
```
## Set default options to all connections
The default options can be overridden as shown in above example
```php
use Amp\Websocket\Options;
use ekstazi\websocket\client\amphp\Connector;
use \ekstazi\websocket\client\ConnectionFactory;
use \Psr\Http\Message\RequestInterface;
use \ekstazi\websocket\client\Connection;

/** @var RequestInterface $request */
/** @var ConnectionFactory $connector */
$connector = new Connector(null, Options::createClientDefault());

/** @var Connection $stream */
$stream = yield $connector->connect($request, Connection::MODE_BINARY);
```
