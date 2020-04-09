<?php

namespace ekstazi\websocket\client\amphp;

use Amp\Promise;
use Amp\Websocket\Client\Connector as AmpConnector;
use Amp\Websocket\Client\Handshake;

use Amp\Websocket\Options;
use ekstazi\websocket\client\ConnectionFactory;
use ekstazi\websocket\common\amphp\Connection as BaseConnection;
use Psr\Http\Message\RequestInterface;

use function Amp\call;
use function Amp\Websocket\Client\connector;

final class Connector implements ConnectionFactory
{
    /**
     * @var AmpConnector
     */
    private $connector;
    /**
     * @var Options
     */
    private $defaultOptions;

    public function __construct(AmpConnector $connector = null, Options $defaultOptions = null)
    {
        $this->connector = $connector ?? connector();
        $this->defaultOptions = $defaultOptions;
    }

    public function connect(RequestInterface $request, string $defaultMode = Connection::MODE_BINARY, Options $options = null): Promise
    {
        return call(function () use ($request, $defaultMode, $options) {
            $options = $options ?? $this->defaultOptions;
            $handshake = new Handshake($request->getUri(), $options);
            $client = yield $this->connector->connect($handshake->withHeaders($request->getHeaders()));
            $adapter = BaseConnection::create($client, $defaultMode);
            return new Connection($adapter);
        });
    }
}
