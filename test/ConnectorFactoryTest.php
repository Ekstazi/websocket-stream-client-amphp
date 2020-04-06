<?php

namespace ekstazi\websocket\stream\amphp\test;

use ekstazi\websocket\stream\amphp\ConnectorFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use function Amp\Websocket\Client\connector;

class ConnectorFactoryTest extends TestCase
{
    public function testInvokeInstance()
    {
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects(self::once())
            ->method('has')
            ->willReturn(true);

        $container
            ->expects(self::once())
            ->method('get')
            ->willReturn(connector());

        $factory = new ConnectorFactory();
        $factory->__invoke($container);
    }

    public function testInvokeDefault()
    {
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects(self::once())
            ->method('has')
            ->willReturn(false);

        $container
            ->expects(self::never())
            ->method('get')
            ->willReturn(connector());

        $factory = new ConnectorFactory();
        $factory->__invoke($container);
    }
}
