<?php

namespace ekstazi\websocket\client\amphp\test;

use Amp\CancellationToken;
use Amp\PHPUnit\AsyncTestCase;
use Amp\Promise;
use Amp\Socket\SocketAddress;
use Amp\Success;

use Amp\Websocket\Client;
use Amp\Websocket\Client\Connector as AmpConnector;
use Amp\Websocket\Client\Handshake;
use Amp\Websocket\Options;
use ekstazi\websocket\client\amphp\Connector;
use ekstazi\websocket\client\ConnectionFactory;
use ekstazi\websocket\common\Connection;
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

    private function stubClient($asDefault = true): Client
    {
        $client = $this->createMock(Client::class);
        $client->expects($asDefault ? self::once() : self::never())
            ->method('getRemoteAddress')
            ->willReturn(new SocketAddress('127.0.0.2', 8000));
        $client->expects($asDefault ? self::once() : self::never())
            ->method('getId')
            ->willReturn(1);
        return $client;
    }


    private function stubAmpConnector($registerAsDefault = true, Client $client = null)
    {
        $client = $client ?? $this->stubClient();
        $connector = new class($client) implements AmpConnector {
            /**
             * @var Client
             */
            private $connection;

            private $handshake;

            public function __construct(Client $connection)
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
        $options = Options::createClientDefault();

        $connector = new Connector();
        $connection = yield $connector->connect($request, Connection::MODE_BINARY, $options);

        /** @var Handshake $handshake */
        $handshake = $client->getHandshake();

        self::assertEquals($handshake->getUri(), $request->getUri());
        self::assertEquals($handshake->getHeaders(), $request->getHeaders());
        self::assertEquals($handshake->getOptions(), $options);

        self::assertInstanceOf(Connection::class, $connection);
    }

    /**
     * Test that default rfc 6455 connector is used.
     * @return \Generator
     */
    public function testConstructDefaultClient()
    {
        $request = $this->stubRequest();
        $client = $this->stubAmpConnector(true);
        $connector = new Connector();
        $connection = yield $connector->connect($request);

        self::assertInstanceOf(Handshake::class, $client->getHandshake());
    }

    /**
     * Test that default rfc 6455 connector is used.
     * @return \Generator
     */
    public function testConstructDefaultOptions()
    {
        $request = $this->stubRequest();
        $client = $this->stubAmpConnector(true);
        $options = Options::createClientDefault();
        $connector = new Connector(null, $options);
        $connection = yield $connector->connect($request);

        self::assertInstanceOf(Handshake::class, $client->getHandshake());
        self::assertEquals($options, $client->getHandshake()->getOptions());
    }

    /**
     * Test that passed as argument rfc6455 connector is used.
     * @return \Generator
     */
    public function testConstructInstance()
    {
        $request = $this->stubRequest();
        $rfcClient = $this->stubClient();
        $clientDefault = $this->stubAmpConnector(true, $rfcClient);

        $client = $this->stubAmpConnector(false, $rfcClient);
        $connector = new Connector($client);
        $connection = yield $connector->connect($request);

        self::assertNull($clientDefault->getHandshake());
        self::assertInstanceOf(Handshake::class, $client->getHandshake());
    }
}
