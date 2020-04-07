<?php

namespace ekstazi\websocket\stream\amphp;

use Amp\Promise;
use Amp\Websocket\Client\Connector as AmpConnector;
use Amp\Websocket\Client\Handshake;

use Amp\Websocket\Options;
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
    /**
     * @var Options
     */
    private $defaultOptions;

    public function __construct(AmpConnector $connector = null, Options $defaultOptions = null)
    {
        $this->connector = $connector ?? connector();
        $this->defaultOptions = $defaultOptions;
    }

    public function connect(RequestInterface $request, string $mode = self::MODE_BINARY, Options $options = null): Promise
    {
        return call(function () use ($request, $mode, $options) {
            $options = $options ?? $this->defaultOptions;
            $handshake = new Handshake($request->getUri(), $options);
            $connection = yield $this->connector->connect($handshake->withHeaders($request->getHeaders()));
            return new Connection($connection, $mode);
        });
    }
}
