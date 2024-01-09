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
namespace MuckiSearchPlugin\Indexing;

use Shopware\Core\Defaults as ShopwareDefaults;
use MuckiSearchPlugin\Core\Defaults;
use Psr\Log\LoggerInterface;
use Shopware\Core\System\Language\LanguageEntity;
use Symfony\Component\Console\Output\OutputInterface;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Product\Aggregate\ProductTranslation\ProductTranslationEntity;

use MuckiSearchPlugin\Services\CliOutput;
use MuckiSearchPlugin\Services\Content\Products as Products;
use MuckiSearchPlugin\Services\Content\IndexStructure;
use MuckiSearchPlugin\Services\IndicesSettings;
use MuckiSearchPlugin\Core\Content\IndexStructure\IndexStructureEntity;
use MuckiSearchPlugin\Core\Content\IndexStructure\IndexStructureTranslation\IndexStructureTranslationEntity;
use MuckiSearchPlugin\Search\SearchClientFactory;
use MuckiSearchPlugin\Entities\CreateIndexBody;
use MuckiSearchPlugin\Services\Settings as PluginSettings;
use MuckiSearchPlugin\Services\Helper as PluginHelper;
use MuckiSearchPlugin\Entities\IndexStructureInstance;

class IndexData
{
    public function __construct(
        protected LoggerInterface  $logger,
        protected Products $products,
        protected CliOutput $cliOutput,
        protected IndexStructure $indexStructure,
        protected IndicesSettings $indicesSettings,
        protected SearchClientFactory $searchClientFactory,
        protected PluginSettings $pluginSettings,
        protected PluginHelper $pluginHelper
    ){}

    protected function getBodyItems(
        array $mappings,
        ProductEntity $product,
        LanguageEntity $language
    ): array
    {
        $bodyContentItem = array();
        $mappedKeys = array_column($mappings, 'key');
        $propertyPaths = array_map(fn (string $key): array => explode('.', $key), $mappedKeys);

        foreach ($propertyPaths as $properties) {

            $propertyContent = null;
            $bodyKey = array();

            foreach ($properties as $propertyKey) {

                $originPropertyKey = $propertyKey;
                if($propertyKey === 'DEFAULT') {
                    $propertyKey = $product->getId().'-'.ShopwareDefaults::LANGUAGE_SYSTEM;
                }
                if($propertyKey === $language->getTranslationCode()->getCode()) {
                    $propertyKey = $product->getId().'-'.$language->getId();
                }
                if(
                    !$propertyContent &&
                    $product->has($propertyKey) &&
                    $product->get($propertyKey)
                ) {
                    if($originPropertyKey !== $propertyKey) {
                        $bodyKey[] = $originPropertyKey;
                    } else {
                        $bodyKey[] = $propertyKey;
                    }
                    $propertyContent = $product->get($propertyKey);
                } else {

                    if(
                        $propertyContent &&
                        $propertyContent->has($propertyKey) &&
                        $propertyContent->get($propertyKey)
                    ) {
                        if($originPropertyKey !== $propertyKey) {
                            $bodyKey[] = $originPropertyKey;
                        } else {
                            $bodyKey[] = $propertyKey;
                        }
                        $propertyContent = $propertyContent->get($propertyKey);
                    } else {
                        $propertyContent = null;
                    }
                }
            }

            if(!empty($bodyKey) && $propertyContent) {

                $bodyContentItem[] = array(
                    'propertyPath' => $bodyKey,
                    'propertyValue' => $propertyContent
                );
            }
        }

        return $bodyContentItem;
    }
}
