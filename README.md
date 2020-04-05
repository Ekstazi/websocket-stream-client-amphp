# websocket-stream-client-amphp
Websocket stream client implementation based on amphp websocket-client
# Usage
## With container
If you have container then add this to your `container.php`
```php
use \ekstazi\websocket\stream\amphp\ConnectorFactory;
use \ekstazi\websocket\stream\ConnectionFactory;

// ....

return [
    ConnectionFactory::class => new ConnectorFactory(),
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

### Without container
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
