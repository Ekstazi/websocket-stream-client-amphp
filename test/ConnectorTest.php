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
use ekstazi\websocket\stream\amphp\test\helpers\StubRequest;

use ekstazi\websocket\stream\ConnectionFactory;
use ekstazi\websocket\stream\Stream;

class ConnectorTest extends AsyncTestCase
{
    use StubRequest;

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
        $this->assertInstanceOf(ConnectionFactory::class, $connector);
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
        $this->assertEquals($handshake->getUri(), $request->getUri());
        $this->assertEquals($handshake->getHeaders(), $request->getHeaders());
        $this->assertInstanceOf(Stream::class, $connection);
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

        $this->assertInstanceOf(Handshake::class, $client->getHandshake());
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

        $this->assertNull($clientDefault->getHandshake());
        $this->assertInstanceOf(Handshake::class, $client->getHandshake());
    }
}
