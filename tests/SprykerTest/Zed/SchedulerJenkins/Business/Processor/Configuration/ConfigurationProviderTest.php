<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\SchedulerJenkins\Business\Processor\Configuration;

use Codeception\Test\Unit;
use Spryker\Shared\SchedulerJenkins\SchedulerJenkinsConfig as SharedSchedulerJenkinsConfig;
use Spryker\Zed\SchedulerJenkins\Business\Processor\Configuration\ConfigurationProvider;
use Spryker\Zed\SchedulerJenkins\SchedulerJenkinsConfig;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group SchedulerJenkins
 * @group Business
 * @group Processor
 * @group Configuration
 * @group ConfigurationProviderTest
 * Add your own group annotations below this line
 */
class ConfigurationProviderTest extends Unit
{
    protected const string ID_SCHEDULER = 'test';

    protected const int EXPECTED_DEFAULT_REQUEST_TIMEOUT = 30;

    protected const int EXPECTED_DEFAULT_CONNECT_TIMEOUT = 10;

    protected const int SCHEDULER_SPECIFIC_REQUEST_TIMEOUT = 45;

    protected const int SCHEDULER_SPECIFIC_CONNECT_TIMEOUT = 3;

    public function testGetJenkinsRequestTimeoutReturnsModuleDefaultWhenSchedulerDefinesNone(): void
    {
        // Arrange
        $configurationProvider = $this->createConfigurationProvider([
            SharedSchedulerJenkinsConfig::SCHEDULER_JENKINS_BASE_URL => 'http://jenkins/',
        ]);

        // Act
        $requestTimeout = $configurationProvider->getJenkinsRequestTimeout();

        // Assert
        $this->assertSame(static::EXPECTED_DEFAULT_REQUEST_TIMEOUT, $requestTimeout);
    }

    public function testGetJenkinsConnectTimeoutReturnsModuleDefaultWhenSchedulerDefinesNone(): void
    {
        // Arrange
        $configurationProvider = $this->createConfigurationProvider([
            SharedSchedulerJenkinsConfig::SCHEDULER_JENKINS_BASE_URL => 'http://jenkins/',
        ]);

        // Act
        $connectTimeout = $configurationProvider->getJenkinsConnectTimeout();

        // Assert
        $this->assertSame(static::EXPECTED_DEFAULT_CONNECT_TIMEOUT, $connectTimeout);
    }

    public function testGetJenkinsRequestTimeoutReturnsSchedulerSpecificValueWhenConfigured(): void
    {
        // Arrange
        $configurationProvider = $this->createConfigurationProvider([
            SharedSchedulerJenkinsConfig::SCHEDULER_JENKINS_TIMEOUT => static::SCHEDULER_SPECIFIC_REQUEST_TIMEOUT,
        ]);

        // Act
        $requestTimeout = $configurationProvider->getJenkinsRequestTimeout();

        // Assert
        $this->assertSame(static::SCHEDULER_SPECIFIC_REQUEST_TIMEOUT, $requestTimeout);
    }

    public function testGetJenkinsConnectTimeoutReturnsSchedulerSpecificValueWhenConfigured(): void
    {
        // Arrange
        $configurationProvider = $this->createConfigurationProvider([
            SharedSchedulerJenkinsConfig::SCHEDULER_JENKINS_CONNECT_TIMEOUT => static::SCHEDULER_SPECIFIC_CONNECT_TIMEOUT,
        ]);

        // Act
        $connectTimeout = $configurationProvider->getJenkinsConnectTimeout();

        // Assert
        $this->assertSame(static::SCHEDULER_SPECIFIC_CONNECT_TIMEOUT, $connectTimeout);
    }

    /**
     * A timeout of 0 means "wait forever" to Guzzle, so an unusable configured value must fall
     * back to the module default rather than silently restoring the unbounded wait.
     */
    public function testGetJenkinsTimeoutsFallBackToModuleDefaultWhenConfiguredValueIsUnusable(): void
    {
        // Arrange
        $unusableValues = ['zero' => 0, 'negative' => -5, 'non-numeric' => 'abc', 'empty' => '', 'null' => null];

        foreach ($unusableValues as $label => $unusableValue) {
            $configurationProvider = $this->createConfigurationProvider([
                SharedSchedulerJenkinsConfig::SCHEDULER_JENKINS_TIMEOUT => $unusableValue,
                SharedSchedulerJenkinsConfig::SCHEDULER_JENKINS_CONNECT_TIMEOUT => $unusableValue,
            ]);

            // Act
            $requestTimeout = $configurationProvider->getJenkinsRequestTimeout();
            $connectTimeout = $configurationProvider->getJenkinsConnectTimeout();

            // Assert
            $this->assertSame(static::EXPECTED_DEFAULT_REQUEST_TIMEOUT, $requestTimeout, $label);
            $this->assertSame(static::EXPECTED_DEFAULT_CONNECT_TIMEOUT, $connectTimeout, $label);
        }
    }

    /**
     * @param array<string, mixed> $schedulerConfiguration
     *
     * @return \Spryker\Zed\SchedulerJenkins\Business\Processor\Configuration\ConfigurationProvider
     */
    protected function createConfigurationProvider(array $schedulerConfiguration): ConfigurationProvider
    {
        $schedulerJenkinsConfigMock = $this->getMockBuilder(SchedulerJenkinsConfig::class)
            ->onlyMethods(['getJenkinsConfiguration'])
            ->getMock();

        $schedulerJenkinsConfigMock
            ->method('getJenkinsConfiguration')
            ->willReturn([static::ID_SCHEDULER => $schedulerConfiguration]);

        return new ConfigurationProvider(static::ID_SCHEDULER, $schedulerJenkinsConfigMock);
    }
}
