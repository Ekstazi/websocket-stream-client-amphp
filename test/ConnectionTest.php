<?php

namespace ekstazi\websocket\stream\amphp\test;

use Amp\ByteStream\InMemoryStream;
use Amp\ByteStream\Payload;
use Amp\PHPUnit\AsyncTestCase;
use Amp\Promise;
use Amp\Success;
use Amp\Websocket\Client\Connection as AmpConnection;
use Amp\Websocket\Client\Connector as Client;
use ekstazi\websocket\stream\amphp\Connector;
use ekstazi\websocket\stream\amphp\test\helpers\StubRequest;
use ekstazi\websocket\stream\ConnectionFactory;

class ConnectionTest extends AsyncTestCase
{
    use StubRequest;

    private function stubClient(AmpConnection $connection)
    {
        $connector = $this->createMock(Client::class);
        $connector
            ->expects($this->once())
            ->method('connect')
            ->willReturn(new Success($connection));
        return $connector;
    }

    /**
     * @param Payload $data
     * @return AmpConnection
     */
    private function stubRead(Payload $data = null): AmpConnection
    {
        $connection = $this->createMock(AmpConnection::class);
        $connection
            ->expects($this->once())
            ->method('receive')
            ->willReturn(new Success($data));
        return $connection;
    }

    /**
     * Test that data readed from websocket client.
     * @return \Generator
     */
    public function testReadSuccess()
    {
        $connection = $this->stubRead(new Payload(new InMemoryStream('test')));

        $client = $this->stubClient($connection);
        $connector = new Connector($client);
        $connection = yield $connector->connect($this->stubRequest());
        $data = yield $connection->read();
        $this->assertEquals('test', $data);
    }

    /**
     * Test that null returned when websocket client was closed.
     * @return \Generator
     */
    public function testReadClose()
    {
        $connection = $this->stubRead(null);

        $client = $this->stubClient($connection);
        $connector = new Connector($client);
        $connection = yield $connector->connect($this->stubRequest());
        $data = yield $connection->read();
        $this->assertNull($data);
    }

    private function stubWriteConnection(string $data, string $mode = ConnectionFactory::MODE_BINARY): AmpConnection
    {
        switch ($mode) {
            case ConnectionFactory::MODE_BINARY:
                $mainMethod = 'sendBinary';
                $unusedMethod = 'send';
                break;
            case ConnectionFactory::MODE_TEXT:
            default:
                $mainMethod = 'send';
                $unusedMethod = 'sendBinary';

        }
        $connection = $this->createMock(AmpConnection::class);

        $connection
            ->expects($this->once())
            ->method($mainMethod)
            ->with($this->equalTo($data))
            ->willReturn(new Success());

        $connection
            ->expects($this->never())
            ->method($unusedMethod);

        return $connection;
    }

    /**
     * Test write method with data and different modes.
     * @param string $mode
     * @dataProvider writeProvider
     * @return \Generator
     */
    public function testWrite(string $mode)
    {
        $connection = $this->stubWriteConnection('test', $mode);
        $client = $this->stubClient($connection);
        $connector = new Connector($client);
        $connection = yield $connector->connect($this->stubRequest(), $mode);
        $promise = $connection->write('test');
        $this->assertInstanceOf(Success::class, $promise);
    }

    public function writeProvider()
    {
        return [
            'binary mode' => [ConnectionFactory::MODE_BINARY],
            'text mode' => [ConnectionFactory::MODE_TEXT],
        ];
    }

    /**
     * @param string $mode
     * @dataProvider writeProvider
     * @return \Generator
     */
    public function testEndWithData(string $mode)
    {
        $connection = $this->stubWriteConnection('test', $mode);
        $connection->expects($this->once())
            ->method('close')
            ->willReturn(new Success());

        $client = $this->stubClient($connection);
        $connector = new Connector($client);
        $connection = yield $connector->connect($this->stubRequest(), $mode);
        $promise = $connection->end('test');
        $this->assertInstanceOf(Promise::class, $promise);
    }

    /**
     * @return \Generator
     */
    public function testEndWithoutData()
    {
        $connection = $this->createMock(AmpConnection::class);

        $connection->expects($this->never())
            ->method('send');

        $connection->expects($this->never())
            ->method('sendBinary');

        $connection->expects($this->once())
            ->method('close')
            ->willReturn(new Success());

        $client = $this->stubClient($connection);
        $connector = new Connector($client);
        $connection = yield $connector->connect($this->stubRequest());
        $promise = $connection->end();
    }
}
