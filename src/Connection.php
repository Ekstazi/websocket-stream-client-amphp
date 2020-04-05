<?php

namespace ekstazi\websocket\stream\amphp;

use Amp\Promise;
use Amp\Websocket\Client\Connection as AmpConnection;
use Amp\Websocket\Message;
use ekstazi\websocket\stream\ConnectionFactory;
use ekstazi\websocket\stream\Stream;
use function Amp\call;

class Connection implements Stream
{
    /**
     * @var AmpConnection
     */
    private $connection;
    /**
     * @var string
     */
    private $mode;

    public function __construct(AmpConnection $connection, string $mode = ConnectionFactory::MODE_BINARY)
    {
        $this->connection = $connection;
        $this->mode = $mode;
    }

    /**
     * @inheritDoc
     */
    public function read(): Promise
    {
        return call(function () {
            /** @var Message $frame */
            $frame = yield $this->connection->receive();
            if (!$frame) {
                return null;
            }
            return $frame->buffer();
        });
    }

    /**
     * @inheritDoc
     */
    public function write(string $data): Promise
    {
        switch ($this->mode) {
            case ConnectionFactory::MODE_BINARY:
                return $this->connection->sendBinary($data);
            case ConnectionFactory::MODE_TEXT:
            default:
                return $this->connection->send($data);
        }
    }

    /**
     * @inheritDoc
     */
    public function end(string $finalData = ""): Promise
    {
        return call(function () use ($finalData) {
            if ($finalData) {
                yield $this->write($finalData);
            }
            return $this->connection->close();
        });
    }
}
