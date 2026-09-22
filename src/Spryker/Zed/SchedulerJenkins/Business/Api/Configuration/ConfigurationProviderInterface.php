<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SchedulerJenkins\Business\Api\Configuration;

interface ConfigurationProviderInterface
{
    /**
     * @return array<string>
     */
    public function getJenkinsAuthCredentials(): array;

    /**
     * @param string $urlPath
     *
     * @throws \Spryker\Zed\SchedulerJenkins\Business\Api\Exception\InvalidJenkinsConfiguration
     *
     * @return string
     */
    public function buildJenkinsApiUrl(string $urlPath): string;

    public function isJenkinsCsrfProtectionEnabled(): bool;

    /**
     * Returns the timeout in seconds for a complete Jenkins API request.
     */
    public function getJenkinsRequestTimeout(): int;

    /**
     * Returns the timeout in seconds for establishing a connection to Jenkins.
     */
    public function getJenkinsConnectTimeout(): int;
}
