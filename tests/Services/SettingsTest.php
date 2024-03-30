<?php

declare(strict_types=1);

namespace MuckiSearchPlugin\Services;

use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Psr\Log\LoggerInterface;
use Shopware\Core\System\SystemConfig\SystemConfigService;

use MuckiSearchPlugin\Services\Settings as PluginSettings;

class SettingsTest extends TestCase
{
    public function testGetMappingProductFieldsDefaultInput()
    {
        $systemConfigService = $this->createMock(SystemConfigService::class);
        $systemConfigService->method('getString')->willReturnCallback(
            fn () => ''
        );

        $instance = $this->getInstance($systemConfigService);
        $mappingProductFieldsResults = $instance->getMappingProductFields();
        $this->assertIsArray($mappingProductFieldsResults, 'GetMappingProductFields is not an array');
        $this->assertArrayHasKey('field', $mappingProductFieldsResults[0], 'Missing field key of GetMappingProductFields method');
        $this->assertArrayHasKey('type', $mappingProductFieldsResults[0], 'Missing type key of GetMappingProductFields method');
        $this->assertSame('id', $mappingProductFieldsResults[0]['field'], 'Missing correct [0] field value');
        $this->assertSame('keyword', $mappingProductFieldsResults[0]['type'], 'Missing correct [0] type value');
        $this->assertSame('productNumber', $mappingProductFieldsResults[1]['field'], 'Missing correct [1] field value');
        $this->assertSame('keyword', $mappingProductFieldsResults[1]['type'], 'Missing correct [1] type value');
        $this->assertSame('translations.DEFAULT.name', $mappingProductFieldsResults[2]['field'], 'Missing correct [2] field value');
        $this->assertSame('text', $mappingProductFieldsResults[2]['type'], 'Missing correct [2] type value');
        $this->assertSame('translations.DEFAULT.description', $mappingProductFieldsResults[3]['field'], 'Missing correct [3] field value');
        $this->assertSame('text', $mappingProductFieldsResults[3]['type'], 'Missing correct [3] type value');
        $this->assertSame('cover.media.url', $mappingProductFieldsResults[4]['field'], 'Missing correct [4] field value');
        $this->assertSame('text', $mappingProductFieldsResults[4]['type'], 'Missing correct [4] type value');
    }

    private function getInstance($systemConfigService): PluginSettings
    {
        return new PluginSettings($systemConfigService);
    }
}
