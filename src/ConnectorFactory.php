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

        $config = $container->has('config') ? $container->get('config') : [];
        $config = $config['websocket'] ?? [];
        $options = $config['clientOptions'] ?? null;

        return new Connector($client, $options);
    }
}
