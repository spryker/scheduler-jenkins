<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SchedulerJenkins;

use Spryker\Shared\SchedulerJenkins\SchedulerJenkinsConstants;
use Spryker\Zed\Kernel\AbstractBundleConfig;

class SchedulerJenkinsConfig extends AbstractBundleConfig
{
    /**
     * @var int
     */
    protected const DEFAULT_AMOUNT_OF_DAYS_FOR_LOGFILE_ROTATION = 7;

    /**
     * @var int
     */
    protected const DEFAULT_AMOUNT_OF_BUILDS_FOR_LOGFILE_ROTATION = 10;

    protected const int DEFAULT_JENKINS_REQUEST_TIMEOUT = 30;

    protected const int DEFAULT_JENKINS_CONNECT_TIMEOUT = 10;

    protected const int JENKINS_ERROR_RESPONSE_MAX_LENGTH = 1000;

    /**
     * @var string
     */
    protected const DEFAULT_JENKINS_TEMPLATE_PATH = __DIR__ . '/Business/TemplateGenerator/Template/jenkins-job.default.xml.twig';

    /**
     * @api
     *
     * @return array<string, mixed>
     */
    public function getJenkinsConfiguration(): array
    {
        return $this->get(SchedulerJenkinsConstants::JENKINS_CONFIGURATION);
    }

    /**
     * @api
     *
     * @param string $schedulerId
     *
     * @return string
     */
    public function getJenkinsBaseUrlBySchedulerId(string $schedulerId): string
    {
        return $this->getJenkinsConfiguration()[$schedulerId];
    }

    /**
     * @api
     *
     * @return int
     */
    public function getAmountOfDaysForLogFileRotation(): int
    {
        return $this->get(SchedulerJenkinsConstants::JENKINS_DEFAULT_AMOUNT_OF_DAYS_FOR_LOGFILE_ROTATION, static::DEFAULT_AMOUNT_OF_DAYS_FOR_LOGFILE_ROTATION);
    }

    /**
     * @api
     *
     * @return int
     */
    public function getAmountOfBuildsForLogFileRotation(): int
    {
        return $this->get(SchedulerJenkinsConstants::JENKINS_DEFAULT_AMOUNT_OF_BUILDS_FOR_LOGFILE_ROTATION, static::DEFAULT_AMOUNT_OF_BUILDS_FOR_LOGFILE_ROTATION);
    }

    /**
     * @api
     */
    public function getDefaultJenkinsRequestTimeout(): int
    {
        return static::DEFAULT_JENKINS_REQUEST_TIMEOUT;
    }

    /**
     * @api
     */
    public function getDefaultJenkinsConnectTimeout(): int
    {
        return static::DEFAULT_JENKINS_CONNECT_TIMEOUT;
    }

    /**
     * Specification:
     * - Returns the maximum number of characters of a failed Jenkins response text included in the error message.
     * - Longer response text is truncated.
     *
     * @api
     */
    public function getJenkinsErrorResponseMaxLength(): int
    {
        return static::JENKINS_ERROR_RESPONSE_MAX_LENGTH;
    }

    /**
     * @api
     *
     * @return array<string>
     */
    public function getJenkinsTemplateFolders(): array
    {
        return [
            dirname($this->getJenkinsTemplatePath()),
            dirname(static::DEFAULT_JENKINS_TEMPLATE_PATH),
        ];
    }

    /**
     * @api
     *
     * @return string
     */
    public function getJenkinsTemplateName(): string
    {
        return basename($this->getJenkinsTemplatePath());
    }

    protected function getJenkinsTemplatePath(): string
    {
        return $this->get(SchedulerJenkinsConstants::JENKINS_TEMPLATE_PATH, static::DEFAULT_JENKINS_TEMPLATE_PATH);
    }
}
