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
use MuckiSearchPlugin\Indexing\Product as IndexingProduct;

class Write
{
    public function __construct(
        protected LoggerInterface  $logger,
        protected Products $products,
        protected CliOutput $cliOutput,
        protected IndexStructure $indexStructure,
        protected IndicesSettings $indicesSettings,
        protected SearchClientFactory $searchClientFactory,
        protected PluginSettings $pluginSettings,
        protected PluginHelper $pluginHelper,
        protected IndexingProduct $indexingProduct
    ){}

    public function doIndexing(OutputInterface $cliOutput = null): void
    {
        $searchClient = $this->searchClientFactory->createSearchClient();

        /** @var IndexStructureInstance $indexStructureInstance */
        foreach ($this->getIndexStructureInstances() as $indexStructureInstance) {

            if(!$searchClient->checkIndicesExists($indexStructureInstance->getIndexName())) {
                continue;
            }

            switch ($indexStructureInstance->getEntity()) {

                case 'product':
                    $this->indexingProduct->indexingProducts($indexStructureInstance, $cliOutput, $searchClient);
                    break;

                default:
                    $this->logger->warning('Missing valid entity index structure instance');
            }
        }
    }

    public function getIndexStructureInstances(): array
    {
        $indexStructures = array();
        /** @var IndexStructureEntity $indexStructure */
        foreach ($this->indexStructure->getAllActiveIndexStructure()->getEntities() as $indexStructure) {

            $this->indicesSettings->setTemplateVariable('entity', $indexStructure->getEntity());
            $this->indicesSettings->setTemplateVariable('salesChannelId', $indexStructure->getSalesChannelId());

            $allActiveProduct = $this->products->getAllActiveProduct(
                $indexStructure->getSalesChannelId()
            )->getElements();

            /** @var IndexStructureTranslationEntity $translation */
            foreach ($indexStructure->get('translations') as $translation) {

                //Set structure instance globals
                $indexStructureInstance = new IndexStructureInstance();
                $indexStructureInstance->setEntity($indexStructure->getEntity());
                $indexStructureInstance->setSalesChannelId($indexStructure->getSalesChannelId());

                //Set structure instance items
                $indexStructureInstance->setItems($allActiveProduct);
                $indexStructureInstance->setItemTotals(count($allActiveProduct));

                $this->indicesSettings->setTemplateVariable('languageId', $translation->getLanguageId());
                $indexStructureInstance->setIndexName($this->indicesSettings->getIndexNameByTemplate());
                $indexStructureInstance->setLanguageId($translation->getLanguageId());
                $indexStructureInstance->setLanguageName($translation->get('language')->getName());
                $indexStructureInstance->setIndexStructureTranslation($translation);

                $indexStructures[] = $indexStructureInstance;
            }
        }

        return $indexStructures;
    }
}
