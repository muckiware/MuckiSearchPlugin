<?php
/**
 * MuckiSearchPlugin plugin
 *
 *
 * @category   Muckiware
 * @package    MuckiSearch
 * @copyright  Copyright (c) 2023 by Muckiware
 *
 * @author     Muckiware
 *
 */

namespace MuckiSearchPlugin\Search\Elasticsearch;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Uuid\Uuid;
use Psr\Log\LoggerInterface;

use MuckiSearchPlugin\Core\Defaults;
use MuckiSearchPlugin\Search\SearchClientInterface;
use MuckiSearchPlugin\Services\Settings as PluginSettings;
use MuckiSearchPlugin\Services\Content\IndexStructure;
use MuckiSearchPlugin\Core\Content\IndexStructure\IndexStructureTranslation\IndexStructureTranslationEntity;
use MuckiSearchPlugin\Entities\CreateIndicesBody;
use MuckiSearchPlugin\Entities\IndicesMappingProperty;
use MuckiSearchPlugin\Services\IndicesSettings;
use MuckiSearchPlugin\Services\Helper as PluginHelper;

class Client extends ClientActions implements SearchClientInterface
{
    public function __construct(
        protected PluginSettings $settings,
        protected LoggerInterface $logger,
        protected IndexStructure $indexStructure,
        protected IndicesSettings $indicesSettings,
        protected PluginHelper $pluginHelper
    )
    {
        parent::__construct(
            $settings,
            $logger
        );
    }

    public function saveIndicesByIndexStructureId(string $indexStructureId, string $languageId, Context $context)
    {
        $indexStructure = $this->indexStructure->getIndexStructureById($indexStructureId, $languageId, $context);
        $this->indicesSettings->setTemplateVariable('entity', $indexStructure->getEntity());
        $this->indicesSettings->setTemplateVariable('salesChannelId', $indexStructure->getSalesChannelId());
        /** @var IndexStructureTranslationEntity $indexStructureTranslation */
        foreach ($indexStructure->get('translations') as $indexStructureTranslation) {

            $this->indicesSettings->setTemplateVariable('languageId', $indexStructureTranslation->getLanguageId());
            $indexName = $this->indicesSettings->getIndexNameByTemplate();
            $indexId = $this->indicesSettings->getIndexId();

            $createBody = new CreateIndicesBody($this->settings);
            $createBody->setIndexName($indexName);
            $createBody->setIndexId($indexId);
            $this->setIndicesSettings($indexStructureTranslation->get('settings'), $createBody);
            $this->setIndicesMappings($indexStructureTranslation->get('mappings'), $createBody);

            if(!$this->checkIndicesExists($indexName)) {
                $this->createNewIndices($createBody);
            } else {
                $this->updateIndices($createBody);
            }
        }

        return null;
    }

    public function removeIndicesByIndexStructureId(string $indexStructureId, string $languageId, Context $context)
    {
        if(Uuid::isValid($indexStructureId)) {

            $indexStructure = $this->indexStructure->getIndexStructureById($indexStructureId, $languageId, $context);
            $this->indicesSettings->setTemplateVariable('salesChannelId', $indexStructure->getSalesChannelId());
            $this->indicesSettings->setTemplateVariable('entity', $indexStructure->getEntity());

            /** @var IndexStructureTranslationEntity $indexStructureTranslation */
            foreach ($indexStructure->get('translations') as $indexStructureTranslation) {

                $this->indicesSettings->setTemplateVariable('languageId', $indexStructureTranslation->getLanguageId());
                $indexName = $this->indicesSettings->getIndexNameByTemplate();

                if($this->checkIndicesExists($indexName)) {
                    $this->removeIndices($indexName);
                }
            }

            $this->indexStructure->removeIndexStructureById($indexStructureId, $context);
        }
    }

    public function removeIndicesByIndexName(string $indexName)
    {
        if($indexName !== '' && $this->checkIndicesExists($indexName)) {
            $this->removeIndices($indexName);
        }
    }

    protected function setIndicesSettings(array $settings, CreateIndicesBody $createBody): void
    {
        foreach ($settings as $setting) {

            if (array_key_exists('settingKey', $setting) && array_key_exists('settingValue', $setting)) {

                switch ($setting['settingKey']) {

                    case Defaults::INDICES_SETTINGS_NUMBER_SHARDS:
                        $createBody->setNumberOfShards($setting['settingValue']);
                        break;
                    case Defaults::INDICES_SETTINGS_NUMBER_REPLICAS:
                        $createBody->setNumberOfReplicas($setting['settingValue']);
                        break;
                }
            }
        }
    }

    protected function setIndicesMappings(array $mappings, CreateIndicesBody $createBody): void
    {
        $mappedKeys = array_column($mappings, 'key');
        $propertyPaths = array_map(fn (string $key): array => explode('.', $key), $mappedKeys);

        $createBody->setMappings(
            $this->pluginHelper->convertBodyArray($propertyPaths, $mappings)
        );
    }
}
