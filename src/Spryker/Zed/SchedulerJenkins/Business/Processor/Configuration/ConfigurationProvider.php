<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SchedulerJenkins\Business\Processor\Configuration;

use Spryker\Shared\SchedulerJenkins\SchedulerJenkinsConfig as SharedSchedulerJenkinsConfig;
use Spryker\Zed\SchedulerJenkins\Business\Api\Configuration\ConfigurationProviderInterface;
use Spryker\Zed\SchedulerJenkins\Business\Api\Exception\InvalidJenkinsConfiguration;
use Spryker\Zed\SchedulerJenkins\SchedulerJenkinsConfig;

class ConfigurationProvider implements ConfigurationProviderInterface
{
    /**
     * @var string
     */
    protected $idScheduler;

    /**
     * @var \Spryker\Zed\SchedulerJenkins\SchedulerJenkinsConfig
     */
    protected $schedulerJenkinsConfig;

    public function __construct(string $idScheduler, SchedulerJenkinsConfig $schedulerJenkinsConfig)
    {
        $this->idScheduler = $idScheduler;
        $this->schedulerJenkinsConfig = $schedulerJenkinsConfig;
    }

    /**
     * @return array<string>
     */
    public function getJenkinsAuthCredentials(): array
    {
        $schedulerJenkinsConfiguration = $this->getJenkinsConfigurationBySchedulerId($this->idScheduler);

        if (
            !array_key_exists(
                SharedSchedulerJenkinsConfig::SCHEDULER_JENKINS_CREDENTIALS,
                $schedulerJenkinsConfiguration,
            )
        ) {
            return [];
        }

        return $schedulerJenkinsConfiguration[SharedSchedulerJenkinsConfig::SCHEDULER_JENKINS_CREDENTIALS];
    }

    /**
     * @param string $urlPath
     *
     * @throws \Spryker\Zed\SchedulerJenkins\Business\Api\Exception\InvalidJenkinsConfiguration
     *
     * @return string
     */
    public function buildJenkinsApiUrl(string $urlPath): string
    {
        $schedulerJenkinsConfiguration = $this->getJenkinsConfigurationBySchedulerId($this->idScheduler);

        if (
            !array_key_exists(
                SharedSchedulerJenkinsConfig::SCHEDULER_JENKINS_BASE_URL,
                $schedulerJenkinsConfiguration,
            )
        ) {
            throw new InvalidJenkinsConfiguration('Base URL has not been configured for Jenkins server.');
        }

        return rtrim($schedulerJenkinsConfiguration[SharedSchedulerJenkinsConfig::SCHEDULER_JENKINS_BASE_URL], '/') . '/' . $urlPath;
    }

    public function isJenkinsCsrfProtectionEnabled(): bool
    {
        $schedulerJenkinsConfiguration = $this->getJenkinsConfigurationBySchedulerId($this->idScheduler);

        if (
            array_key_exists(
                SharedSchedulerJenkinsConfig::SCHEDULER_JENKINS_CSRF_ENABLED,
                $schedulerJenkinsConfiguration,
            )
        ) {
            return $schedulerJenkinsConfiguration[SharedSchedulerJenkinsConfig::SCHEDULER_JENKINS_CSRF_ENABLED];
        }

        return false;
    }

    public function getJenkinsRequestTimeout(): int
    {
        return $this->resolveTimeout(
            SharedSchedulerJenkinsConfig::SCHEDULER_JENKINS_TIMEOUT,
            $this->schedulerJenkinsConfig->getDefaultJenkinsRequestTimeout(),
        );
    }

    public function getJenkinsConnectTimeout(): int
    {
        return $this->resolveTimeout(
            SharedSchedulerJenkinsConfig::SCHEDULER_JENKINS_CONNECT_TIMEOUT,
            $this->schedulerJenkinsConfig->getDefaultJenkinsConnectTimeout(),
        );
    }

    /**
     * Guzzle reads a timeout of 0 as "wait forever", which is the unbounded behaviour these
     * timeouts exist to remove. A missing, non-numeric or non-positive configuration value
     * therefore falls back to the module default rather than being passed through.
     */
    protected function resolveTimeout(string $timeoutKey, int $defaultTimeout): int
    {
        $schedulerJenkinsConfiguration = $this->getJenkinsConfigurationBySchedulerId($this->idScheduler);

        if (!array_key_exists($timeoutKey, $schedulerJenkinsConfiguration)) {
            return $defaultTimeout;
        }

        $timeout = $schedulerJenkinsConfiguration[$timeoutKey];

        if (!is_numeric($timeout) || (int)$timeout <= 0) {
            return $defaultTimeout;
        }

        return (int)$timeout;
    }

    protected function getJenkinsConfigurationBySchedulerId(string $schedulerId): array
    {
        $schedulerJenkinsConfiguration = $this->schedulerJenkinsConfig->getJenkinsConfiguration();

        // @phpstan-ignore function.alreadyNarrowedType (defensive programming for type safety)
        if (!is_array($schedulerJenkinsConfiguration)) {
            return [];
        }

        return $schedulerJenkinsConfiguration[$schedulerId];
    }
}
