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

use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\Filter as SearchFilter;

use MuckiSearchPlugin\Services\Settings as PluginSettings;
use MuckiSearchPlugin\Entities\CreateIndicesBody;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\AndFilter;

class ClientQuery
{
    public function createHighlightObject(PluginSettings $settings, array $mappings): array
    {
        $highlightObject = array();

        foreach ($mappings as $mapping) {

            if(array_key_exists('highlighting', $mapping) && $mapping['highlighting']) {
                $highlightObject['fields'][$mapping['key']] = new \stdClass();
            }
        }

        $highlightObject['pre_tags'] = $settings->getSearchRequestSettingsPreTags();
        $highlightObject['post_tags'] = $settings->getSearchRequestSettingsPostTags();

        return $highlightObject;
    }

    public function createQueryObject(Criteria $criteria, array $mappings): array
    {
        $queryObject = array();
        /** @var AndFilter $filter */
        foreach($criteria->getFilters() as $filter) {

            /** @var SearchFilter $query */
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
