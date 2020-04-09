<?php

namespace ekstazi\websocket\client\amphp\test;

use Amp\ByteStream\Payload;
use Amp\PHPUnit\AsyncTestCase;
use Amp\Success;
use ekstazi\websocket\client\amphp\Connection;
use ekstazi\websocket\common\Connection as BaseConnection;

class ConnectionTest extends AsyncTestCase
{

    /**
     * @param Payload $data
     * @return BaseConnection
     */
    private function stubRead(Payload $data = null): BaseConnection
    {
        $connection = $this->createMock(BaseConnection::class);
        $connection
            ->expects(self::once())
            ->method('read')
            ->willReturn(new Success($data));
        return $connection;
    }

    /**
     * Test that data readed from websocket client.
     * @return \Generator
     * @throws
     */
    public function testRead()
    {
        $client = $this->createMock(BaseConnection::class);
        $client->expects(self::once())
            ->method('read')
            ->willReturn(new Success('test'));

        $connection = new Connection($client);
        $data = yield $connection->read();
        self::assertEquals('test', $data);
    }

    /**
     * Test write method with data and different modes.
     * @return \Generator
     * @throws
     */
    public function testWrite()
    {
        $client = $this->createMock(BaseConnection::class);
        $client->expects(self::once())
            ->method('write')
            ->with('test', Connection::MODE_BINARY)
            ->willReturn(new Success());

        $connection = new Connection($client);
        yield $connection->write('test', Connection::MODE_BINARY);
    }

    /**
     * @return \Generator
     * @throws
     */
    public function testEnd()
    {
        $client = $this->createMock(BaseConnection::class);
        $client->expects(self::once())
            ->method('end')
            ->with('test', Connection::MODE_BINARY)
            ->willReturn(new Success());

        $connection = new Connection($client);
        yield $connection->end('test', Connection::MODE_BINARY);
    }

    public function testSetDefaultMode()
    {
        $client = $this->createMock(BaseConnection::class);
        $client->expects(self::once())
            ->method('setDefaultMode')
            ->with(Connection::MODE_BINARY);

        $connection = new Connection($client);
        $connection->setDefaultMode(Connection::MODE_BINARY);
    }

    public function testGetDefaultMode()
    {
        $client = $this->createMock(BaseConnection::class);
        $client->expects(self::once())
            ->method('getDefaultMode')
            ->willReturn(Connection::MODE_BINARY);

        $connection = new Connection($client);
        self::assertEquals(Connection::MODE_BINARY, $connection->getDefaultMode());
    }

    public function testGetRemoteAddress()
    {
        $client = $this->createMock(BaseConnection::class);
        $client->expects(self::once())
            ->method('getRemoteAddress')
            ->willReturn('127.0.0.2');

        $connection = new Connection($client);
        self::assertEquals('127.0.0.2', $connection->getRemoteAddress());
    }

    public function testGetId()
    {
        $client = $this->createMock(BaseConnection::class);
        $client->expects(self::once())
            ->method('getId')
            ->willReturn(1);

        $connection = new Connection($client);
        self::assertEquals(1, $connection->getId());
    }
}
