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

use MuckiSearchPlugin\Search\SearchClientInterface;
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

class Product extends IndexData
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

    public function indexingProducts(
        IndexStructureInstance $indexStructureInstance,
        OutputInterface $cliOutput,
        SearchClientInterface $searchClient
    ): void
    {
        $progressProduct = $this->cliOutput->prepareProductProgress($indexStructureInstance->getItemTotals());
        $progressProductBar = $this->cliOutput->prepareProductProgressBar(
            $progressProduct,
            $indexStructureInstance->getLanguageName(),
            $indexStructureInstance->getItemTotals(),
            $cliOutput
        );

        /** @var ProductEntity $product */
        foreach ($indexStructureInstance->getItems() as $product) {

            if ($progressProduct->getOffset() >= $progressProduct->getTotal()) {
                $progressProductBar->setProgress($progressProduct->getTotal());
            } else {
                $progressProductBar->advance();
                $progressProductBar->display();
            }

            $indexBody = new CreateIndexBody($this->pluginSettings);
            $indexBody->setIndexName($indexStructureInstance->getIndexName());

            $bodyItems = $this->getBodyItems(
                $indexStructureInstance->getIndexStructureTranslation()->get('mappings'),
                $product,
                $indexStructureInstance->getIndexStructureTranslation()->get('language')
            );

            $indexBody->setBodyItems($this->pluginHelper->createIndexingBody($bodyItems));
            $searchClient->indexing($indexBody->getIndexBody());
        }
    }
}
