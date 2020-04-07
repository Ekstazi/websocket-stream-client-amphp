<?php

namespace ekstazi\websocket\stream\amphp\test;

use Amp\Websocket\Client\Connector as AmpConnector;
use Amp\Websocket\Options;
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
            ->expects(self::atLeastOnce())
            ->method('has')
            ->willReturn(true);

        $container
            ->expects(self::atLeastOnce())
            ->method('get')
            ->withConsecutive([AmpConnector::class], ['config'])
            ->willReturnOnConsecutiveCalls(connector(), [
                "websocket" => [
                    'clientOptions' => Options::createClientDefault(),
                ]
            ]);

        $factory = new ConnectorFactory();
        $factory->__invoke($container);
    }

    public function testInvokeDefault()
    {
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects(self::atLeastOnce())
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
