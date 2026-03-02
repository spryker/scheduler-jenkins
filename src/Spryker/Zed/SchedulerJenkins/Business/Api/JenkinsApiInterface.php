<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SchedulerJenkins\Business\Api;

use Generated\Shared\Transfer\SchedulerJenkinsResponseTransfer;
use Spryker\Zed\SchedulerJenkins\Business\Api\Configuration\ConfigurationProviderInterface;

interface JenkinsApiInterface
{
    public function getJobs(ConfigurationProviderInterface $configurationProvider): SchedulerJenkinsResponseTransfer;

    public function updateJob(ConfigurationProviderInterface $configurationProvider, string $jobName, string $jobXmlTemplate): SchedulerJenkinsResponseTransfer;

    public function createJob(ConfigurationProviderInterface $configurationProvider, string $jobName, string $jobXmlTemplate): SchedulerJenkinsResponseTransfer;

    public function deleteJob(ConfigurationProviderInterface $configurationProvider, string $jobName): SchedulerJenkinsResponseTransfer;

    public function enableJob(ConfigurationProviderInterface $configurationProvider, string $jobName): SchedulerJenkinsResponseTransfer;

    public function disableJob(ConfigurationProviderInterface $configurationProvider, string $jobName): SchedulerJenkinsResponseTransfer;
}
