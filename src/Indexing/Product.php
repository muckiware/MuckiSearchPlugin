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

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Shopware\Core\Defaults as ShopwareDefaults;
use Shopware\Core\System\Language\LanguageEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Storefront\Framework\Routing\RequestTransformer;
use Shopware\Core\Content\Seo\SeoUrlPlaceholderHandlerInterface;
use Shopware\Core\Content\Seo\SeoUrl\SeoUrlEntity;

use MuckiSearchPlugin\Core\Defaults;
use MuckiSearchPlugin\Search\SearchClientFactory;
use MuckiSearchPlugin\Search\SearchClientInterface;
use MuckiSearchPlugin\Entities\CreateIndexBody;
use MuckiSearchPlugin\Services\CliOutput;
use MuckiSearchPlugin\Services\Settings as PluginSettings;
use MuckiSearchPlugin\Services\Helper as PluginHelper;
use MuckiSearchPlugin\Entities\IndexStructureInstance;
use MuckiSearchPlugin\Services\Content\SalesChannel as SalesChannelService;

class Product extends IndexData
{
    public function __construct(
        protected LoggerInterface  $logger,
        protected CliOutput $cliOutput,
        protected SearchClientFactory $searchClientFactory,
        protected PluginSettings $pluginSettings,
        protected PluginHelper $pluginHelper,
        protected SeoUrlPlaceholderHandlerInterface $seoUrlReplacer
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

            $bodyItems[] = array(
                'propertyPath' => array(0 => 'url'),
                'propertyValue' => $product->getSeoUrls()->first()->getSeoPathInfo()
            );

            $dataHash = md5(serialize($bodyItems));

            $searchResult = $searchClient->searching(array(
                'index' => $indexStructureInstance->getIndexName(),
                'body' => array(
                    'query' => array (
                        'match' => array(
                            'hash' => $dataHash
                        )
                    )
                )
            ));

            if($searchResult['hits']['total']['value'] === 0) {

                $bodyItems[] = array(
                    'propertyPath' => array(0 => 'hash'),
                    'propertyValue' => $dataHash
                );

                $indexBody->setBodyItems($this->pluginHelper->createIndexingBody($bodyItems));
                $searchClient->indexing($indexBody->getIndexBody());
            }
        }
    }
}
