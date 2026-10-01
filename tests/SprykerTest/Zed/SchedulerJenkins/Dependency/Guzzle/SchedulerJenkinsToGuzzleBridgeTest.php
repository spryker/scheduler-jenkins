<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\SchedulerJenkins\Dependency\Guzzle;

use Codeception\Test\Unit;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Spryker\Zed\SchedulerJenkins\Dependency\Guzzle\Exception\SchedulerJenkinsToGuzzleRequestException;
use Spryker\Zed\SchedulerJenkins\Dependency\Guzzle\SchedulerJenkinsToGuzzleBridge;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group SchedulerJenkins
 * @group Dependency
 * @group Guzzle
 * @group SchedulerJenkinsToGuzzleBridgeTest
 * Add your own group annotations below this line
 */
class SchedulerJenkinsToGuzzleBridgeTest extends Unit
{
    protected const string REQUEST_URL = 'http://jenkins:8080/createItem?name=DE__queue-worker-start';

    protected const string CONNECT_ERROR_MESSAGE = 'cURL error 7: Failed to connect to jenkins port 8080';

    public function testSendReturnsResponseOnSuccess(): void
    {
        // Arrange
        $schedulerJenkinsToGuzzleBridge = $this->createBridge(new Response(200, [], '{}'));

        // Act
        $response = $schedulerJenkinsToGuzzleBridge->send(new Request('POST', static::REQUEST_URL));

        // Assert
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testSendRethrowsErrorResponseAsProxyExceptionCarryingResponse(): void
    {
        // Arrange
        $schedulerJenkinsToGuzzleBridge = $this->createBridge(new Response(400, ['X-Error' => 'A job already exists']));

        // Act
        try {
            $schedulerJenkinsToGuzzleBridge->send(new Request('POST', static::REQUEST_URL));
            $this->fail('Expected exception was not thrown.');
        } catch (SchedulerJenkinsToGuzzleRequestException $schedulerJenkinsToGuzzleRequestException) {
            // Assert
            $this->assertSame(400, $schedulerJenkinsToGuzzleRequestException->getResponse()->getStatusCode());
            $this->assertSame('A job already exists', $schedulerJenkinsToGuzzleRequestException->getResponse()->getHeaderLine('X-Error'));
            $this->assertInstanceOf(RequestException::class, $schedulerJenkinsToGuzzleRequestException->getPrevious());
        }
    }

    public function testSendRethrowsConnectionFailureAsProxyExceptionWithoutResponse(): void
    {
        // Arrange
        $request = new Request('POST', static::REQUEST_URL);
        $schedulerJenkinsToGuzzleBridge = $this->createBridge(new ConnectException(static::CONNECT_ERROR_MESSAGE, $request));

        // Act
        try {
            $schedulerJenkinsToGuzzleBridge->send($request);
            $this->fail('Expected exception was not thrown.');
        } catch (SchedulerJenkinsToGuzzleRequestException $schedulerJenkinsToGuzzleRequestException) {
            // Assert
            $this->assertNull($schedulerJenkinsToGuzzleRequestException->getResponse());
            $this->assertSame(static::CONNECT_ERROR_MESSAGE, $schedulerJenkinsToGuzzleRequestException->getMessage());
            $this->assertInstanceOf(ConnectException::class, $schedulerJenkinsToGuzzleRequestException->getPrevious());
        }
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface|\Throwable $queuedResult
     *
     * @return \Spryker\Zed\SchedulerJenkins\Dependency\Guzzle\SchedulerJenkinsToGuzzleBridge
     */
    protected function createBridge(mixed $queuedResult): SchedulerJenkinsToGuzzleBridge
    {
        return new SchedulerJenkinsToGuzzleBridge(
            new Client(['handler' => HandlerStack::create(new MockHandler([$queuedResult]))]),
        );
    }
}
