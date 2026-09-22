# SchedulerJenkins Module
[![Latest Stable Version](https://poser.pugx.org/spryker/scheduler-jenkins/v/stable.svg)](https://packagist.org/packages/spryker/scheduler-jenkins)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.3-8892BF.svg)](https://php.net/)

Provides Jenkins scheduler functionality.

## Installation

```
composer require spryker/scheduler-jenkins
```

## Configuration

Each scheduler is configured under `SchedulerJenkinsConstants::JENKINS_CONFIGURATION`, keyed by scheduler id:

```php
use Spryker\Shared\SchedulerJenkins\SchedulerJenkinsConfig;
use Spryker\Shared\SchedulerJenkins\SchedulerJenkinsConstants;

$config[SchedulerJenkinsConstants::JENKINS_CONFIGURATION] = [
    'default' => [
        SchedulerJenkinsConfig::SCHEDULER_JENKINS_BASE_URL => 'http://scheduler:8080/',
        SchedulerJenkinsConfig::SCHEDULER_JENKINS_CREDENTIALS => ['username', 'password'],
        SchedulerJenkinsConfig::SCHEDULER_JENKINS_CSRF_ENABLED => true,
        SchedulerJenkinsConfig::SCHEDULER_JENKINS_TIMEOUT => 30,
        SchedulerJenkinsConfig::SCHEDULER_JENKINS_CONNECT_TIMEOUT => 10,
    ],
];
```

| Key | Default | Description |
|---|---|---|
| `SCHEDULER_JENKINS_BASE_URL` | none, required | Base URL of the Jenkins instance |
| `SCHEDULER_JENKINS_CREDENTIALS` | none | `[username, password]` for Jenkins basic auth |
| `SCHEDULER_JENKINS_CSRF_ENABLED` | `false` | Whether to fetch a CSRF crumb before each write |
| `SCHEDULER_JENKINS_TIMEOUT` | `30` | Seconds allowed for a complete Jenkins API request |
| `SCHEDULER_JENKINS_CONNECT_TIMEOUT` | `10` | Seconds allowed to establish the connection to Jenkins |

### Timeouts

Both timeouts are per Jenkins API call, not per console command, and apply to every scheduler operation (`setup`, `clean`, `suspend`, `resume`).

They exist because Guzzle's own default is `0`, meaning wait forever. Without them, a Jenkins instance that completes the TCP handshake but never answers makes `scheduler:suspend` block until an outer process timeout kills it, which in a deploy pre-deploy hook surfaces as an opaque `ProcessTimedOutException` naming Symfony Process rather than Jenkins. With them, the call fails in seconds and the console prints `Scheduler Error:` followed by the underlying Jenkins error, and exits non-zero.

For the same reason a configured value that is missing, non-numeric or not positive falls back to the default rather than being passed through: `0` would restore the unbounded wait these timeouts exist to remove. Raise `SCHEDULER_JENKINS_TIMEOUT` for a Jenkins instance that is legitimately slow to respond; there is no supported way to disable the bound.

Defaults can be changed per project by overriding `SchedulerJenkinsConfig::getDefaultJenkinsRequestTimeout()` or `getDefaultJenkinsConnectTimeout()`.

## Scripts

### scripts/clean_jobs.sh

Deletes all jobs from a Jenkins instance via the Jenkins REST API. Useful for cleaning up stale jobs before re-running the scheduler setup.

**Required environment variables:**

| Variable | Description |
|---|---|
| `SPRYKER_SCHEDULER_HOST` | Jenkins hostname |
| `SPRYKER_SCHEDULER_PORT` | Jenkins port |

**Usage:**

```bash
export SPRYKER_SCHEDULER_HOST=jenkins.example.com
export SPRYKER_SCHEDULER_PORT=8080
bash scripts/clean_jobs.sh
```

The script fetches a CSRF crumb and its session cookie before issuing delete requests.

## Documentation

[Spryker Documentation](https://docs.spryker.com)
