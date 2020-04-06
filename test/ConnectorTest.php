<?php

namespace ekstazi\websocket\stream\amphp\test;

use Amp\CancellationToken;
use Amp\PHPUnit\AsyncTestCase;
use Amp\Promise;
use Amp\Success;
use Amp\Websocket\Client\Connection as AmpConnection;
use Amp\Websocket\Client\Connector as AmpConnector;
use Amp\Websocket\Client\Handshake;
use ekstazi\websocket\stream\amphp\Connector;
use ekstazi\websocket\stream\ConnectionFactory;
use ekstazi\websocket\stream\Stream;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class ConnectorTest extends AsyncTestCase
{
    private function stubRequest(): RequestInterface
    {
        $request = $this->createStub(RequestInterface::class);
        $uri = $this->createStub(UriInterface::class);
        $uri->method('getScheme')
            ->willReturn('ws');
        $request->method('getUri')
            ->willReturn($uri);

        $request->method('getHeaders')
            ->willReturn([
                'test-header' => ['test'],
            ]);

        return $request;
    }

    private function stubAmpConnection(): AmpConnection
    {
        return $this->createStub(AmpConnection::class);
    }


    private function stubAmpConnector($registerAsDefault = true)
    {
        $connector = new class($this->stubAmpConnection()) implements AmpConnector {
            /**
             * @var AmpConnection
             */
            private $connection;

            private $handshake;

            public function __construct(AmpConnection $connection)
            {
                $this->connection = $connection;
            }

            public function connect(Handshake $handshake, ?CancellationToken $cancellationToken = null): Promise
            {
                $this->handshake = $handshake;
                return new Success($this->connection);
            }

            /**
             * @return mixed
             */
            public function getHandshake(): ?Handshake
            {
                return $this->handshake;
            }
        };
        if ($registerAsDefault) {
            \Amp\Websocket\Client\connector($connector);
        }
        return $connector;
    }

    /**
     * Test that connector is instance of ConnectionFactory.
     */
    public function testInstanceOf()
    {
        $connector = new Connector();
        self::assertInstanceOf(ConnectionFactory::class, $connector);
    }

    /**
     * Test that request parameters used to create connection.
     * @return \Generator
     */
    public function testConnect()
    {
        $request = $this->stubRequest();
        $client = $this->stubAmpConnector(true);
        $connector = new Connector();
        $connection = yield $connector->connect($request);

        /** @var Handshake $handshake */
        $handshake = $client->getHandshake();
        self::assertEquals($handshake->getUri(), $request->getUri());
        self::assertEquals($handshake->getHeaders(), $request->getHeaders());
        self::assertInstanceOf(Stream::class, $connection);
    }

    /**
     * Test that default rfc 6455 connector is used.
     * @return \Generator
     */
    public function testConstructDefault()
    {
        $request = $this->stubRequest();
        $client = $this->stubAmpConnector(true);
        $connector = new Connector();
        $connection = yield $connector->connect($request);

        self::assertInstanceOf(Handshake::class, $client->getHandshake());
    }

    /**
     * Test that passed as argument rfc6455 connector is used.
     * @return \Generator
     */
    public function testConstructInstance()
    {
        $request = $this->stubRequest();

        $clientDefault = $this->stubAmpConnector(true);

        $client = $this->stubAmpConnector(false);
        $connector = new Connector($client);
        $connection = yield $connector->connect($request);

        self::assertNull($clientDefault->getHandshake());
        self::assertInstanceOf(Handshake::class, $client->getHandshake());
    }
}
