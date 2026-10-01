<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SchedulerJenkins\Business\Api\Executor;

use Generated\Shared\Transfer\SchedulerJenkinsResponseTransfer;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Spryker\Zed\SchedulerJenkins\Business\Api\Configuration\ConfigurationProviderInterface;
use Spryker\Zed\SchedulerJenkins\Dependency\Guzzle\Exception\SchedulerJenkinsToGuzzleRequestException;
use Spryker\Zed\SchedulerJenkins\Dependency\Guzzle\SchedulerJenkinsToGuzzleInterface;
use Spryker\Zed\SchedulerJenkins\SchedulerJenkinsConfig;

class RequestExecutor implements RequestExecutorInterface
{
    protected const string AUTH_KEY = 'auth';

    protected const string TIMEOUT_KEY = 'timeout';

    protected const string CONNECT_TIMEOUT_KEY = 'connect_timeout';

    protected const string HEADER_SET_COOKIE = 'set-cookie';

    protected const string HEADER_JENKINS_ERROR = 'X-Error';

    protected const string ERROR_MESSAGE_TRANSFER_FAILED = 'Jenkins request %s failed: %s';

    protected const string ERROR_MESSAGE_RESPONSE_FAILED = 'Jenkins request %s failed with HTTP %d %s.';

    protected const string ERROR_MESSAGE_JENKINS_ERROR = ' Jenkins error: %s.';

    protected const string ERROR_MESSAGE_RESPONSE_TEXT = ' Response: %s';

    protected const string REQUEST_DESCRIPTION = '%s %s';

    protected const string TRUNCATION_MARKER = ' [...]';

    protected const string PATTERN_NON_TEXT_HTML_ELEMENTS = '#<(script|style)\b[^>]*>.*?</\1>#is';

    protected const string PATTERN_HTML_TAG = '#<[^>]*>#';

    protected const string PATTERN_WHITESPACE = '#\s+#';

    public function __construct(
        protected SchedulerJenkinsToGuzzleInterface $client,
        protected SchedulerJenkinsConfig $config
    ) {
    }

    public function execute(
        RequestInterface $request,
        ConfigurationProviderInterface $configurationProvider
    ): SchedulerJenkinsResponseTransfer {
        $requestOptions = $this->getRequestOptions($configurationProvider);

        try {
            $response = $this->client->send($request, $requestOptions);
        } catch (SchedulerJenkinsToGuzzleRequestException $schedulerJenkinsToGuzzleRequestException) {
            return $this->createSchedulerJenkinsErrorResponseTransfer(
                $this->buildRequestExceptionMessage($request, $schedulerJenkinsToGuzzleRequestException),
            );
        }

        $payload = $response->getBody()->getContents();
        $cookie = $response->hasHeader(static::HEADER_SET_COOKIE) ? $response->getHeaderLine(static::HEADER_SET_COOKIE) : null;

        return $this->createSchedulerJenkinsSuccessResponseTransfer($payload, $cookie);
    }

    protected function getRequestOptions(ConfigurationProviderInterface $configurationProvider): array
    {
        $requestOptions = [
            static::CONNECT_TIMEOUT_KEY => $configurationProvider->getJenkinsConnectTimeout(),
            static::TIMEOUT_KEY => $configurationProvider->getJenkinsRequestTimeout(),
        ];

        $jenkinsAuthCredentials = $configurationProvider->getJenkinsAuthCredentials();

        if (count($jenkinsAuthCredentials) === 0) {
            return $requestOptions;
        }

        $requestOptions[static::AUTH_KEY] = $jenkinsAuthCredentials;

        return $requestOptions;
    }

    protected function buildRequestExceptionMessage(
        RequestInterface $request,
        SchedulerJenkinsToGuzzleRequestException $schedulerJenkinsToGuzzleRequestException
    ): string {
        $response = $schedulerJenkinsToGuzzleRequestException->getResponse();

        if ($response === null) {
            return $this->buildTransferFailedMessage($request, $schedulerJenkinsToGuzzleRequestException->getMessage());
        }

        return $this->buildResponseFailedMessage($request, $response);
    }

    protected function buildTransferFailedMessage(RequestInterface $request, string $reason): string
    {
        return sprintf(static::ERROR_MESSAGE_TRANSFER_FAILED, $this->getRequestDescription($request), $reason);
    }

    protected function buildResponseFailedMessage(RequestInterface $request, ResponseInterface $response): string
    {
        $message = sprintf(
            static::ERROR_MESSAGE_RESPONSE_FAILED,
            $this->getRequestDescription($request),
            $response->getStatusCode(),
            $response->getReasonPhrase(),
        );

        if ($response->hasHeader(static::HEADER_JENKINS_ERROR)) {
            $message .= sprintf(static::ERROR_MESSAGE_JENKINS_ERROR, $response->getHeaderLine(static::HEADER_JENKINS_ERROR));
        }

        $responseText = $this->extractResponseText($response);

        if ($responseText === '') {
            return $message;
        }

        return $message . sprintf(static::ERROR_MESSAGE_RESPONSE_TEXT, $responseText);
    }

    protected function getRequestDescription(RequestInterface $request): string
    {
        return sprintf(static::REQUEST_DESCRIPTION, $request->getMethod(), (string)$request->getUri()->withUserInfo(''));
    }

    protected function extractResponseText(ResponseInterface $response): string
    {
        $responseBody = $this->truncateResponseText((string)$response->getBody());

        $responseBody = preg_replace(static::PATTERN_NON_TEXT_HTML_ELEMENTS, ' ', $responseBody) ?? $responseBody;
        $responseText = preg_replace(static::PATTERN_HTML_TAG, ' ', $responseBody) ?? $responseBody;
        $responseText = html_entity_decode($responseText, ENT_QUOTES | ENT_HTML5);
        $responseText = trim(preg_replace(static::PATTERN_WHITESPACE, ' ', $responseText) ?? $responseText);

        return $responseText;
    }

    protected function truncateResponseText(string $responseText): string
    {
        $maxLength = $this->config->getJenkinsErrorResponseMaxLength();

        if (mb_strlen($responseText) <= $maxLength) {
            return $responseText;
        }

        return mb_substr($responseText, 0, $maxLength) . static::TRUNCATION_MARKER;
    }

    protected function createSchedulerJenkinsSuccessResponseTransfer(string $payload, ?string $cookie = null): SchedulerJenkinsResponseTransfer
    {
        return (new SchedulerJenkinsResponseTransfer())
            ->setPayload($payload)
            ->setCookie($cookie)
            ->setStatus(true);
    }

    protected function createSchedulerJenkinsErrorResponseTransfer(string $message): SchedulerJenkinsResponseTransfer
    {
        return (new SchedulerJenkinsResponseTransfer())
            ->setMessage($message)
            ->setStatus(false);
    }
}
