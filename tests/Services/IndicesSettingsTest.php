<?php

declare(strict_types=1);

namespace MuckiSearchPlugin\Services;

use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Psr\Log\LoggerInterface;
use MuckiSearchPlugin\Services\IndicesSettings;
use MuckiSearchPlugin\Services\Settings as PluginSettings;

class IndicesSettingsTest extends TestCase
{
    public function testGetIndexNameByTemplate()
    {
        $indicesSettingsMock = $this->getInstance();
        $indicesSettingsMock->setTemplateVariable('testKey','testValue');
        $templateVariable = $indicesSettingsMock->getTemplateVariable();
        static::assertIsArray($templateVariable, 'TemplateVariable output should be of type string');
    }
    private function getInstance(): IndicesSettings
    {
        $pluginSettings = $this->createMock(PluginSettings::class);
        $twig = $this->createMock(Environment::class);
        $logger = $this->createMock(LoggerInterface::class);

        return new IndicesSettings(
            $pluginSettings,
            $twig,
            $logger
        );
    }
}
