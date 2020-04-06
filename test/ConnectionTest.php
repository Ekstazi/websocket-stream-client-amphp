<?php

namespace ekstazi\websocket\stream\amphp\test;

use Amp\ByteStream\InMemoryStream;
use Amp\ByteStream\Payload;
use Amp\PHPUnit\AsyncTestCase;
use Amp\Promise;
use Amp\Success;
use Amp\Websocket\Client\Connection as AmpConnection;
use ekstazi\websocket\stream\amphp\Connection;
use ekstazi\websocket\stream\ConnectionFactory;

class ConnectionTest extends AsyncTestCase
{

    /**
     * @param Payload $data
     * @return AmpConnection
     */
    private function stubRead(Payload $data = null): AmpConnection
    {
        $connection = $this->createMock(AmpConnection::class);
        $connection
            ->expects(self::once())
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

        $connection = new Connection($connection);
        $data = yield $connection->read();
        self::assertEquals('test', $data);
    }

    /**
     * Test that null returned when websocket client was closed.
     * @return \Generator
     */
    public function testReadClose()
    {
        $connection = $this->stubRead(null);

        $connection = new Connection($connection);
        $data = yield $connection->read();
        self::assertNull($data);
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
            ->expects(self::once())
            ->method($mainMethod)
            ->with($this->equalTo($data))
            ->willReturn(new Success());

        $connection
            ->expects(self::never())
            ->method($unusedMethod);

        return $connection;
    }

    /**
     * Test write method with data and different modes.
     * @param string $mode
     * @dataProvider writeProvider
     * @return \Generator
     * @throws
     */
    public function testWrite(string $mode)
    {
        $connection = $this->stubWriteConnection('test', $mode);
        $connection = new Connection($connection, $mode);
        $promise = $connection->write('test');
        self::assertInstanceOf(Success::class, $promise);
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
     * @throws
     */
    public function testEndWithData(string $mode)
    {
        $connection = $this->stubWriteConnection('test', $mode);
        $connection->expects(self::once())
            ->method('close')
            ->willReturn(new Success());

        $connection = new Connection($connection, $mode);
        $promise = $connection->end('test');
        self::assertInstanceOf(Promise::class, $promise);
    }

    /**
     * @return \Generator
     * @throws
     */
    public function testEndWithoutData()
    {
        $connection = $this->createMock(AmpConnection::class);

        $connection->expects(self::never())
            ->method('send');

        $connection->expects(self::never())
            ->method('sendBinary');

        $connection->expects(self::once())
            ->method('close')
            ->willReturn(new Success());

        $connection = new Connection($connection);
        $promise = yield $connection->end();
    }
}
