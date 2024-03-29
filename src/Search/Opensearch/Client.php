<?php
/**
 * MuckiSearchPlugin plugin
 *
 *
 * @category   Muckiware
 * @package    MuckiSearch
 * @copyright  Copyright (c) 2023-2024 by Muckiware
 * @license    MIT
 * @author     Muckiware
 *
 */

namespace MuckiSearchPlugin\Search\Opensearch;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use OpenSearch\Client as OpenSearchClient;

use MuckiSearchPlugin\Search\SearchClientInterface;
use MuckiSearchPlugin\Services\Settings as PluginSettings;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;

class Client implements SearchClientInterface
{
    public function __construct(
        protected PluginSettings $settings,
        protected LoggerInterface $logger
    )
    {}

    public function getClient(): OpenSearchClient
    {
        return OpenSearchClient::class;
    }

    public function searching(array $params): ?array
    {
        return null;
    }

    public function getServerInfoAsString(): ?string
    {
        return null;
    }

    public function getServerInfoAsObject(): ?object
    {
        return null;
    }

    public function indexing(array $params): ?array
    {
        return null;
    }

    public function updateIndex(array $params): ?array
    {
        return null;
    }
    public function getIndices(): ?array
    {
        return null;
    }

    public function deleteIndex(array $params): ?array
    {
        return null;
    }

    public function saveIndicesByIndexStructureId(string $indexStructureId, string $languageId, Context $context): void
    {
        //TODO saveIndicesByIndexStructureId
    }

    public function removeIndicesByIndexStructureId(string $indexStructureId, string $languageId, Context $context): void
    {
        //TODO removeIndicesByIndexStructureId
    }

    public function removeIndicesByIndexName(string $indexName): void
    {
        //TODO removeIndicesByIndexName
    }

    public function checkIndicesExists(string $indexName): bool
    {
        return false;
    }

    public function createQueryObject(Criteria $criteria, array $mappings): array
    {
        return array();
    }

    public function createHighlightObject(PluginSettings $pluginSettings, array $mappings): array
    {
        return array();
    }

    public function getClusterHealth(string $indexName): ?object
    {
        return null;
    }
}
