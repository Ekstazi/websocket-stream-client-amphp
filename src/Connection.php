<?php

namespace ekstazi\websocket\client\amphp;

use Amp\Promise;
use ekstazi\websocket\client\Connection as ConnectionInterface;
use ekstazi\websocket\common\Connection as BaseConnection;

final class Connection implements ConnectionInterface
{
    /**
     * @var BaseConnection
     */
    private $connection;

    public function __construct(BaseConnection $connection)
    {
        $this->connection = $connection;
    }

    public function getId(): int
    {
        return $this->connection->getId();
    }

    public function getRemoteAddress(): string
    {
        return $this->connection->getRemoteAddress();
    }

    public function read(): Promise
    {
        return $this->connection->read();
    }

    public function setDefaultMode(string $defaultMode): void
    {
        $this->connection->setDefaultMode($defaultMode);
    }

    public function getDefaultMode(): string
    {
        return $this->connection->getDefaultMode();
    }

    public function write(string $data, string $mode = null): Promise
    {
        return $this->connection->write($data, $mode);
    }

    public function end(string $finalData = "", string $mode = null): Promise
    {
        return $this->connection->end($finalData, $mode);
    }
}
