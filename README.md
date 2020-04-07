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
use \ekstazi\websocket\stream\amphp\ConnectorFactory;
use \ekstazi\websocket\stream\ConnectionFactory;

// ....

return [
    ConnectionFactory::class => new ConnectorFactory(),
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
use \ekstazi\websocket\stream\ConnectionFactory;
use \Psr\Http\Message\RequestInterface;
use \ekstazi\websocket\stream\Stream;

/** @var ContainerInterface $container */
/** @var ConnectionFactory $connector */
/** @var RequestInterface $request */

$connector = $container->get(ConnectionFactory::class);

/** @var Stream $stream */
$stream = yield $connector->connect($request, ConnectionFactory::MODE_BINARY);

```

## Without container
You can use functions to do the same:
```php
use \ekstazi\websocket\stream\ConnectionFactory;
use \Psr\Http\Message\RequestInterface;
use \ekstazi\websocket\stream\Stream;

use function \ekstazi\websocket\stream\connect;

/** @var RequestInterface $request */
/** @var Stream $stream */
$stream = yield connect($request, ConnectionFactory::MODE_BINARY);
```
or
```php
use \ekstazi\websocket\stream\ConnectionFactory;
use \Psr\Http\Message\RequestInterface;
use \ekstazi\websocket\stream\Stream;

use function \ekstazi\websocket\stream\connector;

/** @var RequestInterface $request */
/** @var ConnectionFactory $connector */
$connector = connector();

/** @var Stream $stream */
$stream = yield $connector->connect($request, ConnectionFactory::MODE_BINARY);
```

## Passing additional options to connection
```php
use Amp\Websocket\Options;
use \ekstazi\websocket\stream\ConnectionFactory;
use \Psr\Http\Message\RequestInterface;
use \ekstazi\websocket\stream\Stream;

use function \ekstazi\websocket\stream\connector;

/** @var RequestInterface $request */
/** @var ConnectionFactory $connector */
$connector = connector();

/** @var Stream $stream */
$stream = yield $connector->connect($request, ConnectionFactory::MODE_BINARY, Options::createClientDefault()->withoutHeartbeat());
```
## Set default options to all connections
The default options can be overridden as shown in above example
```php
use Amp\Websocket\Options;
use ekstazi\websocket\stream\amphp\Connector;
use \ekstazi\websocket\stream\ConnectionFactory;
use \Psr\Http\Message\RequestInterface;
use \ekstazi\websocket\stream\Stream;

/** @var RequestInterface $request */
/** @var ConnectionFactory $connector */
$connector = new Connector(null, Options::createClientDefault());

/** @var Stream $stream */
$stream = yield $connector->connect($request, ConnectionFactory::MODE_BINARY);
```
