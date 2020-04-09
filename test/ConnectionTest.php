<?php

namespace ekstazi\websocket\client\amphp\test;

use Amp\PHPUnit\AsyncTestCase;
use Amp\Websocket\Client;
use ekstazi\websocket\client\amphp\Connection;
use ekstazi\websocket\common\Writer;

class ConnectionTest extends AsyncTestCase
{
    public function testCreate()
    {
        $client = $this->createClient();
        $stream = Connection::create($client, Writer::MODE_BINARY);
        self::assertInstanceOf(Connection::class, $stream);
        self::assertEquals(Writer::MODE_BINARY, $stream->getDefaultMode());
    }

    /**
     * @return Client
     */
    private function createClient(): Client
    {
        return $this->createStub(Client::class);
    }

}
