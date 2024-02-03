<?php
/**
 * MuckiSearchPlugin plugin
 *
 *
 * @category   Muckiware
 * @package    MuckiSearch
 * @copyright  Copyright (c) 2023-2024 by Muckiware
 *
 * @author     Muckiware
 *
 */

namespace MuckiSearchPlugin\Search;

use Elastic\Elasticsearch\ClientInterface as ElasticsearchClient;
use Elastic\Elasticsearch\Response\Elasticsearch;
use OpenSearch\Client as OpenSearchClient;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

use MuckiSearchPlugin\Services\Settings as PluginSettings;

interface SearchClientInterface
{
    public function getClient(): ElasticsearchClient | OpenSearchClient | null;

    public function searching(array $params): ?array;

    public function getServerInfoAsString(): ?string;

    public function getServerInfoAsObject(): ?object;

    public function indexing(array $params): ?array;

    public function updateIndex(array $params): ?array;

    public function deleteIndex(array $params): ?array;

    public function getIndices(): ?array;

    public function saveIndicesByIndexStructureId(string $indexStructureId, string $languageId, Context $context);

    public function removeIndicesByIndexStructureId(string $indexStructureId, string $languageId, Context $context);

    public function removeIndicesByIndexName(string $indexName);

    public function checkIndicesExists(string $indexName): bool;

    public function createQueryObject(Criteria $criteria, array $mappings): array;

    public function createHighlightObject(PluginSettings $pluginSettings, array $mappings): array;

    public function getClusterHealth(string $indexName);
}
