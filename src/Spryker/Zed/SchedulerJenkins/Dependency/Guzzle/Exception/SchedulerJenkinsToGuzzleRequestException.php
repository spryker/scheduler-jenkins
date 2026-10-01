<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SchedulerJenkins\Dependency\Guzzle\Exception;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Throwable;

class SchedulerJenkinsToGuzzleRequestException extends RuntimeException
{
    protected ?ResponseInterface $response;

    public function __construct(string $message, ?ResponseInterface $response = null, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);

        $this->response = $response;
    }

    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }
}
