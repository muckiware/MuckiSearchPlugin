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

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Http\Promise\Promise;
use Psr\Log\LoggerInterface;
use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\ClientInterface;
use Elastic\Elasticsearch\Exception\AuthenticationException;

use MuckiSearchPlugin\Services\Settings as PluginSettings;
use MuckiSearchPlugin\Entities\CreateIndicesBody;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\AndFilter;

class ClientQuery
{
    public function createHighlightObject(array $mappings): array
    {
        $highlightObject = array();

        foreach ($mappings as $mapping) {

            if(array_key_exists('highlighting', $mapping) && $mapping['highlighting']) {
                $highlightObject['fields'][$mapping['key']] = new \stdClass();
            }
        }

        return $highlightObject;
    }

    public function createQueryObject(Criteria $criteria, array $mappings): array
    {
        $queryObject = array();
        /** @var AndFilter $filter */
        foreach($criteria->getFilters() as $filter) {

            foreach ($filter->getQueries() as $query) {

                if($query->getField() === 'product.searchKeywords.keyword') {

                    foreach ($query->getValue() as $term) {

                        foreach ($mappings as $mapping) {

                            $queryObject[] = array(
                                'match' => array(
                                    $mapping['key'] => array(
                                        'query' => $term,
                                        'operator' => 'or'
                                    )
                                )
                            );
                        }
                    }
                }
            }
        }

        return $queryObject;
    }
}
