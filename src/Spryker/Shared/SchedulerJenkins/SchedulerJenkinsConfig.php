<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Shared\SchedulerJenkins;

use Spryker\Shared\Kernel\AbstractSharedConfig;

class SchedulerJenkinsConfig extends AbstractSharedConfig
{
    /**
     * @api
     *
     * @var string
     */
    public const SCHEDULER_JENKINS_BASE_URL = 'base_url';

    /**
     * @api
     *
     * Defines credentials for Jenkins Api, e.g ['username', 'password']
     *
     * @var string
     */
    public const SCHEDULER_JENKINS_CREDENTIALS = 'credentials';

    /**
     * @api
     *
     * Defines if CSRF protection is enabled for Jenkins API calls.
     *
     * @var string
     */
    public const SCHEDULER_JENKINS_CSRF_ENABLED = 'csrf';

    /**
     * @api
     *
     * Defines the timeout in seconds for a complete Jenkins API request, e.g 30
     */
    public const string SCHEDULER_JENKINS_TIMEOUT = 'timeout';

    /**
     * @api
     *
     * Defines the timeout in seconds for establishing a connection to Jenkins, e.g 10
     */
    public const string SCHEDULER_JENKINS_CONNECT_TIMEOUT = 'connect_timeout';
}
