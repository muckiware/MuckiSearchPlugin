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
use OpenSearch\Client as OpenSearchClient;

interface SearchClientInterface
{
    public function getClient(): ElasticsearchClient | OpenSearchClient | null;

    public function searching(array $params): ?array;

    public function deleting(array $params): bool;

    public function getServerInfoAsString(): ?string;

    public function getServerInfoAsObject(): ?object;

    public function indexing(array $params): ?array;
}
