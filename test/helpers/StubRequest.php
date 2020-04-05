<?php

namespace ekstazi\websocket\stream\amphp\test\helpers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

trait StubRequest
{
    private function stubRequest(): RequestInterface
    {
        $request = $this->createStub(RequestInterface::class);
        $uri = $this->createStub(UriInterface::class);
        $uri->method('getScheme')
            ->willReturn('ws');
        $request->method('getUri')
            ->willReturn($uri);

        $request->method('getHeaders')
            ->willReturn([
                'test-header' => ['test'],
            ]);

        return $request;
    }
}
