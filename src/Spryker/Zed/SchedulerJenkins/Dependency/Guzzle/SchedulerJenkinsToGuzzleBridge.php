<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SchedulerJenkins\Dependency\Guzzle;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Spryker\Zed\SchedulerJenkins\Dependency\Guzzle\Exception\SchedulerJenkinsToGuzzleRequestException;

class SchedulerJenkinsToGuzzleBridge implements SchedulerJenkinsToGuzzleInterface
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * @param \GuzzleHttp\Client $client
     */
    public function __construct($client)
    {
        $this->client = $client;
    }

    /**
     * @param \Psr\Http\Message\RequestInterface $request
     * @param array<string, mixed> $options
     *
     * @throws \Spryker\Zed\SchedulerJenkins\Dependency\Guzzle\Exception\SchedulerJenkinsToGuzzleRequestException
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function send(RequestInterface $request, array $options = []): ResponseInterface
    {
        try {
            return $this->client->send($request, $options);
        } catch (RequestException $requestException) {
            throw new SchedulerJenkinsToGuzzleRequestException(
                $requestException->getMessage(),
                $requestException->getResponse(),
                $requestException,
            );
        } catch (TransferException $transferException) {
            throw new SchedulerJenkinsToGuzzleRequestException($transferException->getMessage(), null, $transferException);
        }
    }
}
