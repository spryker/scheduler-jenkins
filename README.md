# SchedulerJenkins Module
[![Latest Stable Version](https://poser.pugx.org/spryker/scheduler-jenkins/v/stable.svg)](https://packagist.org/packages/spryker/scheduler-jenkins)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.3-8892BF.svg)](https://php.net/)

Provides Jenkins scheduler functionality.

## Installation

```
composer require spryker/scheduler-jenkins
```

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
