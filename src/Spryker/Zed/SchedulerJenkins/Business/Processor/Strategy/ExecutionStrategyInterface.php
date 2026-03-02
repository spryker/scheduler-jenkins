<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SchedulerJenkins\Business\Processor\Strategy;

use Generated\Shared\Transfer\SchedulerJobTransfer;
use Spryker\Zed\SchedulerJenkins\Business\Executor\ExecutorInterface;

interface ExecutionStrategyInterface
{
    public function getExecutor(SchedulerJobTransfer $jobTransfer): ExecutorInterface;
}
