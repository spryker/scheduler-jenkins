<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SchedulerJenkins\Business\Api\Executor;

use Generated\Shared\Transfer\SchedulerJenkinsResponseTransfer;
use Psr\Http\Message\RequestInterface;
use Spryker\Zed\SchedulerJenkins\Business\Api\Configuration\ConfigurationProviderInterface;

interface RequestExecutorInterface
{
    public function execute(
        RequestInterface $request,
        ConfigurationProviderInterface $configurationProvider
    ): SchedulerJenkinsResponseTransfer;
}
