<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\SchedulerJenkins\Business\Api\Executor;

use Codeception\Test\Unit;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Spryker\Shared\SchedulerJenkins\SchedulerJenkinsConfig as SharedSchedulerJenkinsConfig;
use Spryker\Zed\SchedulerJenkins\Business\Api\Configuration\ConfigurationProviderInterface;
use Spryker\Zed\SchedulerJenkins\Business\Api\Executor\RequestExecutor;
use Spryker\Zed\SchedulerJenkins\Business\Processor\Configuration\ConfigurationProvider;
use Spryker\Zed\SchedulerJenkins\Dependency\Guzzle\Exception\SchedulerJenkinsToGuzzleRequestException;
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

    protected const string REQUEST_URL = 'http://jenkins:8080/createItem?name=DE__queue-worker-start';

    protected const string REQUEST_URL_WITH_CREDENTIALS = 'http://jenkins-user:jenkins-secret@jenkins:8080/createItem?name=DE__queue-worker-start';

    protected const string JENKINS_ERROR_HEADER_VALUE = 'A job already exists with the name ‘DE__queue-worker-start’';

    public function testExecutePassesConfiguredTimeoutsToJenkinsRequest(): void
    {
        // Arrange
        $capturedRequestOptions = [];
        $requestExecutor = new RequestExecutor(
            $this->createGuzzleBridgeMock($capturedRequestOptions),
            new SchedulerJenkinsConfig(),
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
            new SchedulerJenkinsConfig(),
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
            ->willThrowException(
                new SchedulerJenkinsToGuzzleRequestException(
                    static::TIMEOUT_MESSAGE,
                    null,
                    new ConnectException(static::TIMEOUT_MESSAGE, $this->createRequest()),
                ),
            );
        $requestExecutor = new RequestExecutor($guzzleBridgeMock, new SchedulerJenkinsConfig());

        // Act
        $responseTransfer = $requestExecutor->execute(
            $this->createRequest(),
            $this->createConfigurationProvider([]),
        );

        // Assert
        $this->assertFalse($responseTransfer->getStatus());
        $this->assertSame(
            sprintf('Jenkins request POST %s failed: %s', static::JENKINS_URL, static::TIMEOUT_MESSAGE),
            $responseTransfer->getMessage(),
        );
    }

    public function testExecuteReturnsStatusCodeJenkinsErrorHeaderAndResponseTextWhenJenkinsRespondsWithClientError(): void
    {
        // Arrange
        $request = new Request('POST', static::REQUEST_URL);
        $response = new Response(
            400,
            ['X-Error' => static::JENKINS_ERROR_HEADER_VALUE],
            '<html><head><title>Error</title><style>body { color: red; }</style><script>var crumb = 1;</script></head>'
                . '<body><h1>Error</h1><p>A job already exists with the name &lsquo;DE__queue-worker-start&rsquo;</p></body></html>',
        );
        $requestExecutor = $this->createRequestExecutor(new ClientException('Client error', $request, $response));

        // Act
        $schedulerJenkinsResponseTransfer = $requestExecutor->execute($request, $this->createConfigurationProviderMock());

        // Assert
        $this->assertFalse($schedulerJenkinsResponseTransfer->getStatus());
        $this->assertSame(
            'Jenkins request POST http://jenkins:8080/createItem?name=DE__queue-worker-start failed with HTTP 400 Bad Request.'
                . ' Jenkins error: A job already exists with the name ‘DE__queue-worker-start’.'
                . ' Response: Error Error A job already exists with the name ‘DE__queue-worker-start’',
            $schedulerJenkinsResponseTransfer->getMessage(),
        );
    }

    public function testExecuteReturnsStatusCodeAndResponseTextWhenJenkinsRespondsWithServerErrorWithoutJenkinsErrorHeader(): void
    {
        // Arrange
        $request = new Request('POST', static::REQUEST_URL);
        $response = new Response(503, [], '<html><body><h2>Jenkins is getting ready to work</h2></body></html>');
        $requestExecutor = $this->createRequestExecutor(new ServerException('Server error', $request, $response));

        // Act
        $schedulerJenkinsResponseTransfer = $requestExecutor->execute($request, $this->createConfigurationProviderMock());

        // Assert
        $this->assertFalse($schedulerJenkinsResponseTransfer->getStatus());
        $this->assertSame(
            'Jenkins request POST http://jenkins:8080/createItem?name=DE__queue-worker-start failed with HTTP 503 Service Unavailable.'
                . ' Response: Jenkins is getting ready to work',
            $schedulerJenkinsResponseTransfer->getMessage(),
        );
    }

    public function testExecuteOmitsResponsePartWhenJenkinsRespondsWithEmptyBody(): void
    {
        // Arrange
        $request = new Request('POST', static::REQUEST_URL);
        $response = new Response(403);
        $requestExecutor = $this->createRequestExecutor(new ClientException('Client error', $request, $response));

        // Act
        $schedulerJenkinsResponseTransfer = $requestExecutor->execute($request, $this->createConfigurationProviderMock());

        // Assert
        $this->assertSame(
            'Jenkins request POST http://jenkins:8080/createItem?name=DE__queue-worker-start failed with HTTP 403 Forbidden.',
            $schedulerJenkinsResponseTransfer->getMessage(),
        );
    }

    public function testExecuteTruncatesResponseTextToConfiguredMaxLength(): void
    {
        // Arrange
        $request = new Request('POST', static::REQUEST_URL);
        $response = new Response(500, [], sprintf('<pre>%s</pre>', str_repeat('a', 50)));
        $requestExecutor = $this->createRequestExecutor(
            new ServerException('Server error', $request, $response),
            $this->createSchedulerJenkinsConfigMock(10),
        );

        // Act
        $schedulerJenkinsResponseTransfer = $requestExecutor->execute($request, $this->createConfigurationProviderMock());

        // Assert
        $this->assertStringEndsWith(
            ' Response: aaaaa [...]',
            $schedulerJenkinsResponseTransfer->getMessage(),
        );
    }

    public function testExecuteDoesNotExposeUrlCredentialsInMessage(): void
    {
        // Arrange
        $request = new Request('POST', static::REQUEST_URL_WITH_CREDENTIALS);
        $response = new Response(400);
        $requestExecutor = $this->createRequestExecutor(new ClientException('Client error', $request, $response));

        // Act
        $schedulerJenkinsResponseTransfer = $requestExecutor->execute($request, $this->createConfigurationProviderMock());

        // Assert
        $this->assertStringNotContainsString('jenkins-secret', $schedulerJenkinsResponseTransfer->getMessage());
        $this->assertStringContainsString('POST http://jenkins:8080/createItem', $schedulerJenkinsResponseTransfer->getMessage());
    }

    public function testExecuteReturnsPayloadAndCookieOnSuccess(): void
    {
        // Arrange
        $request = new Request('GET', static::REQUEST_URL);
        $guzzleClientMock = $this->createMock(SchedulerJenkinsToGuzzleInterface::class);
        $guzzleClientMock
            ->method('send')
            ->willReturn(new Response(200, ['Set-Cookie' => 'JSESSIONID=1'], '{"crumb":"abc"}'));
        $requestExecutor = new RequestExecutor($guzzleClientMock, new SchedulerJenkinsConfig());

        // Act
        $schedulerJenkinsResponseTransfer = $requestExecutor->execute($request, $this->createConfigurationProviderMock());

        // Assert
        $this->assertTrue($schedulerJenkinsResponseTransfer->getStatus());
        $this->assertSame('{"crumb":"abc"}', $schedulerJenkinsResponseTransfer->getPayload());
        $this->assertSame('JSESSIONID=1', $schedulerJenkinsResponseTransfer->getCookie());
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

    protected function createRequestExecutor(RequestException $exception, ?SchedulerJenkinsConfig $schedulerJenkinsConfig = null): RequestExecutor
    {
        $guzzleClientMock = $this->createMock(SchedulerJenkinsToGuzzleInterface::class);
        $guzzleClientMock
            ->method('send')
            ->willThrowException(
                new SchedulerJenkinsToGuzzleRequestException(
                    $exception->getMessage(),
                    $exception->getResponse(),
                    $exception,
                ),
            );

        return new RequestExecutor($guzzleClientMock, $schedulerJenkinsConfig ?? new SchedulerJenkinsConfig());
    }

    protected function createConfigurationProviderMock(): ConfigurationProviderInterface
    {
        $configurationProviderMock = $this->createMock(ConfigurationProviderInterface::class);
        $configurationProviderMock
            ->method('getJenkinsAuthCredentials')
            ->willReturn([]);
        $configurationProviderMock
            ->method('getJenkinsConnectTimeout')
            ->willReturn(10);
        $configurationProviderMock
            ->method('getJenkinsRequestTimeout')
            ->willReturn(30);

        return $configurationProviderMock;
    }

    protected function createSchedulerJenkinsConfigMock(int $errorResponseMaxLength): SchedulerJenkinsConfig
    {
        $schedulerJenkinsConfigMock = $this->getMockBuilder(SchedulerJenkinsConfig::class)
            ->onlyMethods(['getJenkinsErrorResponseMaxLength'])
            ->getMock();
        $schedulerJenkinsConfigMock
            ->method('getJenkinsErrorResponseMaxLength')
            ->willReturn($errorResponseMaxLength);

        return $schedulerJenkinsConfigMock;
    }
}
