<?php

namespace ekstazi\websocket\stream\amphp;

use Amp\Promise;
use Amp\Websocket\Client\Connector as AmpConnector;
use Amp\Websocket\Client\Handshake;

use ekstazi\websocket\stream\ConnectionFactory;
use Psr\Http\Message\RequestInterface;
use function Amp\call;
use function Amp\Websocket\Client\connector;

class Connector implements ConnectionFactory
{
    /**
     * @var AmpConnector
     */
    private $connector;

    public function __construct(AmpConnector $connector = null)
    {
        $this->connector = $connector ?? connector();
    }

    public function connect(RequestInterface $request, string $mode = self::MODE_BINARY): Promise
    {
        return call(function () use ($request, $mode) {
            $handshake = new Handshake($request->getUri());
            $connection = yield $this->connector->connect($handshake->withHeaders($request->getHeaders()));
            return new Connection($connection, $mode);
        });
    }
}
