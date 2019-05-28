<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SchedulerJenkins\Business\Executor;

use Generated\Shared\Transfer\SchedulerJobTransfer;
use Generated\Shared\Transfer\SchedulerResponseTransfer;
use Spryker\Zed\SchedulerJenkins\Business\Api\JenkinsApiInterface;
use Spryker\Zed\SchedulerJenkins\Business\TemplateGenerator\JenkinsJobTemplateGeneratorInterface;

class CreateExecutor implements ExecutorInterface
{
    protected const CREATE_JOB_URL_TEMPLATE = 'createItem?name=%s';

    /**
     * @var \Spryker\Zed\SchedulerJenkins\Business\Api\JenkinsApiInterface
     */
    protected $jenkinsApi;

    /**
     * @var \Spryker\Zed\SchedulerJenkins\Business\TemplateGenerator\JenkinsJobTemplateGeneratorInterface
     */
    protected $jobTemplateGenerator;

    /**
     * @param \Spryker\Zed\SchedulerJenkins\Business\Api\JenkinsApiInterface $jenkinsApi
     * @param \Spryker\Zed\SchedulerJenkins\Business\TemplateGenerator\JenkinsJobTemplateGeneratorInterface $jobTemplateGenerator
     */
    public function __construct(
        JenkinsApiInterface $jenkinsApi,
        JenkinsJobTemplateGeneratorInterface $jobTemplateGenerator
    ) {
        $this->jenkinsApi = $jenkinsApi;
        $this->jobTemplateGenerator = $jobTemplateGenerator;
    }

    /**
     * @param string $idScheduler
     * @param \Generated\Shared\Transfer\SchedulerJobTransfer $schedulerJobTransfer
     *
     * @return \Generated\Shared\Transfer\SchedulerResponseTransfer
     */
    public function execute(string $idScheduler, SchedulerJobTransfer $schedulerJobTransfer): SchedulerResponseTransfer
    {
        $jobXmlTemplate = $this->jobTemplateGenerator->getJobTemplate($schedulerJobTransfer);

        $schedulerResponseTransfer = $this->jenkinsApi->executePostRequest(
            $idScheduler,
            sprintf(static::CREATE_JOB_URL_TEMPLATE, $schedulerJobTransfer->getName()),
            $jobXmlTemplate
        );

        return $schedulerResponseTransfer;
    }
}