<?php

namespace ekstazi\websocket\stream\amphp;

use Amp\Websocket\Client\Connector as AmpConnector;
use ekstazi\websocket\stream\ConnectionFactory;
use Psr\Container\ContainerInterface;

class ConnectorFactory
{
    public function __invoke(ContainerInterface $container): ConnectionFactory
    {
        $client = $container->has(AmpConnector::class)
            ? $container->get(AmpConnector::class)
            : null;

        return new Connector($client);
    }
}
