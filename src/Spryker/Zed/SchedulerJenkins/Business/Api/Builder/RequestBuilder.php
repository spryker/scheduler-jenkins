<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SchedulerJenkins\Business\Api\Builder;

use GuzzleHttp\Psr7\Request as Psr7Request;
use Psr\Http\Message\RequestInterface;
use Spryker\Zed\SchedulerJenkins\Business\Api\Configuration\ConfigurationProviderInterface;

class RequestBuilder implements RequestBuilderInterface
{
    public function buildPsrRequest(
        string $requestMethod,
        ConfigurationProviderInterface $configurationProvider,
        string $urlPath,
        string $body = ''
    ): RequestInterface {
        $baseUrl = $configurationProvider->buildJenkinsApiUrl($urlPath);
        $headers = $this->getHeaders();

        $request = new Psr7Request(
            $requestMethod,
            $baseUrl,
            $headers,
            $body,
        );

        return $request;
    }

    protected function getHeaders(): array
    {
        $httpHeader = [
            'Content-Type' => 'text/xml; charset=UTF8',
        ];

        return $httpHeader;
    }
}
