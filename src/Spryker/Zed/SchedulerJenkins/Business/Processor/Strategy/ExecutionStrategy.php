<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SchedulerJenkins\Business\Processor\Strategy;

use Generated\Shared\Transfer\SchedulerJobTransfer;
use Spryker\Zed\SchedulerJenkins\Business\Executor\ExecutorInterface;

class ExecutionStrategy implements ExecutionStrategyInterface
{
    /**
     * @var \Spryker\Zed\SchedulerJenkins\Business\Executor\ExecutorInterface
     */
    protected $executorForExistingJob;

    /**
     * @var \Spryker\Zed\SchedulerJenkins\Business\Executor\ExecutorInterface
     */
    protected $executorForAbsentJob;

    /**
     * @var array<bool>
     */
    protected $jobNames = [];

    public function __construct(
        ExecutorInterface $executorForExistingJob,
        ExecutorInterface $executorForAbsentJob
    ) {
        $this->executorForExistingJob = $executorForExistingJob;
        $this->executorForAbsentJob = $executorForAbsentJob;
    }

    /**
     * @param string $jobName
     *
     * @return $this
     */
    public function addJobName(string $jobName)
    {
        $this->jobNames[$jobName] = true;

        return $this;
    }

    public function getExecutor(SchedulerJobTransfer $jobTransfer): ExecutorInterface
    {
        return $this->doesJobExist($jobTransfer)
            ? $this->executorForExistingJob
            : $this->executorForAbsentJob;
    }

    protected function doesJobExist(SchedulerJobTransfer $jobTransfer): bool
    {
        return $this->jobNames[$jobTransfer->getName()] ?? false;
    }
}
