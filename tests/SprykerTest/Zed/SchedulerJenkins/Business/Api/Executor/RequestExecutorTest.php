<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\SchedulerJenkins\Business\Api\Executor;

use Codeception\Test\Unit;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Spryker\Shared\SchedulerJenkins\SchedulerJenkinsConfig as SharedSchedulerJenkinsConfig;
use Spryker\Zed\SchedulerJenkins\Business\Api\Executor\RequestExecutor;
use Spryker\Zed\SchedulerJenkins\Business\Processor\Configuration\ConfigurationProvider;
use Spryker\Zed\SchedulerJenkins\Dependency\Guzzle\SchedulerJenkinsToGuzzleInterface;
use Spryker\Zed\SchedulerJenkins\SchedulerJenkinsConfig;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group SchedulerJenkins
 * @group Business
 * @group Api
 * @group Executor
 * @group RequestExecutorTest
 * Add your own group annotations below this line
 */
class RequestExecutorTest extends Unit
{
    protected const string ID_SCHEDULER = 'test';

    protected const string JENKINS_URL = 'http://jenkins/job/DE__test/disable';

    protected const string TIMEOUT_KEY = 'timeout';

    protected const string CONNECT_TIMEOUT_KEY = 'connect_timeout';

    protected const string AUTH_KEY = 'auth';

    protected const int CONFIGURED_REQUEST_TIMEOUT = 45;

    protected const int CONFIGURED_CONNECT_TIMEOUT = 3;

    protected const int EXPECTED_DEFAULT_REQUEST_TIMEOUT = 30;

    protected const int EXPECTED_DEFAULT_CONNECT_TIMEOUT = 10;

    protected const string TIMEOUT_MESSAGE = 'cURL error 28: Operation timed out after 30001 milliseconds';

    public function testExecutePassesConfiguredTimeoutsToJenkinsRequest(): void
    {
        // Arrange
        $capturedRequestOptions = [];
        $requestExecutor = new RequestExecutor(
            $this->createGuzzleBridgeMock($capturedRequestOptions),
        );
        $configurationProvider = $this->createConfigurationProvider([
            SharedSchedulerJenkinsConfig::SCHEDULER_JENKINS_CREDENTIALS => ['user', 'password'],
            SharedSchedulerJenkinsConfig::SCHEDULER_JENKINS_TIMEOUT => static::CONFIGURED_REQUEST_TIMEOUT,
            SharedSchedulerJenkinsConfig::SCHEDULER_JENKINS_CONNECT_TIMEOUT => static::CONFIGURED_CONNECT_TIMEOUT,
        ]);

        // Act
        $requestExecutor->execute($this->createRequest(), $configurationProvider);

        // Assert
        $this->assertSame(static::CONFIGURED_REQUEST_TIMEOUT, $capturedRequestOptions[static::TIMEOUT_KEY]);
        $this->assertSame(static::CONFIGURED_CONNECT_TIMEOUT, $capturedRequestOptions[static::CONNECT_TIMEOUT_KEY]);
        $this->assertSame(['user', 'password'], $capturedRequestOptions[static::AUTH_KEY]);
    }

    /**
     * The credentials lookup returns early when a scheduler defines none. Timeouts must be
     * applied before that early return, otherwise an unauthenticated scheduler keeps waiting
     * forever on an unresponsive Jenkins.
     */
    public function testExecutePassesDefaultTimeoutsWhenSchedulerHasNoCredentials(): void
    {
        // Arrange
        $capturedRequestOptions = [];
        $requestExecutor = new RequestExecutor(
            $this->createGuzzleBridgeMock($capturedRequestOptions),
        );
        $configurationProvider = $this->createConfigurationProvider([
            SharedSchedulerJenkinsConfig::SCHEDULER_JENKINS_BASE_URL => 'http://jenkins/',
        ]);

        // Act
        $requestExecutor->execute($this->createRequest(), $configurationProvider);

        // Assert
        $this->assertArrayNotHasKey(static::AUTH_KEY, $capturedRequestOptions);
        $this->assertSame(static::EXPECTED_DEFAULT_REQUEST_TIMEOUT, $capturedRequestOptions[static::TIMEOUT_KEY]);
        $this->assertSame(static::EXPECTED_DEFAULT_CONNECT_TIMEOUT, $capturedRequestOptions[static::CONNECT_TIMEOUT_KEY]);
    }

    public function testExecuteReturnsFailedResponseNamingJenkinsWhenRequestTimesOut(): void
    {
        // Arrange
        $guzzleBridgeMock = $this->createMock(SchedulerJenkinsToGuzzleInterface::class);
        $guzzleBridgeMock
            ->method('send')
            ->willThrowException(new ConnectException(static::TIMEOUT_MESSAGE, $this->createRequest()));
        $requestExecutor = new RequestExecutor($guzzleBridgeMock);

        // Act
        $responseTransfer = $requestExecutor->execute(
            $this->createRequest(),
            $this->createConfigurationProvider([]),
        );

        // Assert
        $this->assertFalse($responseTransfer->getStatus());
        $this->assertSame(static::TIMEOUT_MESSAGE, $responseTransfer->getMessage());
    }

    protected function createRequest(): RequestInterface
    {
        return new Request('POST', static::JENKINS_URL);
    }

    /**
     * @param array<string, mixed> $capturedRequestOptions
     *
     * @return \Spryker\Zed\SchedulerJenkins\Dependency\Guzzle\SchedulerJenkinsToGuzzleInterface
     */
    protected function createGuzzleBridgeMock(array &$capturedRequestOptions): SchedulerJenkinsToGuzzleInterface
    {
        $guzzleBridgeMock = $this->createMock(SchedulerJenkinsToGuzzleInterface::class);

        $guzzleBridgeMock
            ->method('send')
            ->willReturnCallback(
                function (RequestInterface $request, array $options) use (&$capturedRequestOptions): Response {
                    $capturedRequestOptions = $options;

                    return new Response(200, [], '{}');
                },
            );

        return $guzzleBridgeMock;
    }

    /**
     * @param array<string, mixed> $schedulerConfiguration
     *
     * @return \Spryker\Zed\SchedulerJenkins\Business\Processor\Configuration\ConfigurationProvider
     */
    protected function createConfigurationProvider(array $schedulerConfiguration): ConfigurationProvider
    {
        $schedulerJenkinsConfigMock = $this->getMockBuilder(SchedulerJenkinsConfig::class)
            ->onlyMethods(['getJenkinsConfiguration'])
            ->getMock();

        $schedulerJenkinsConfigMock
            ->method('getJenkinsConfiguration')
            ->willReturn([static::ID_SCHEDULER => $schedulerConfiguration]);

        return new ConfigurationProvider(static::ID_SCHEDULER, $schedulerJenkinsConfigMock);
    }
}
