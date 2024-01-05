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

namespace MuckiSearchPlugin\Search\Opensearch;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use OpenSearch\Client as OpenSearchClient;

use MuckiSearchPlugin\Search\SearchClientInterface;
use MuckiSearchPlugin\Services\Settings as PluginSettings;

class Client implements SearchClientInterface
{
    public function __construct(
        protected PluginSettings $settings,
        protected LoggerInterface $logger
    )
    {}

    public function getClient(): ?OpenSearchClient
    {
        return null;
    }

    public function searching(array $params): ?array
    {
        return null;
    }

    public function deleting(array $params): bool
    {
        return false;
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

    public function getIndices(): ?array
    {
        return null;
    }

    public function saveIndicesByIndexStructureId(string $indexStructureId, string $languageId, Context $context)
    {
        return null;
    }

    public function removeIndicesByIndexStructureId(string $indexStructureId, string $languageId, Context $context)
    {
        return null;
    }

    public function removeIndicesByIndexName(string $indexName)
    {
        return null;
    }
}
