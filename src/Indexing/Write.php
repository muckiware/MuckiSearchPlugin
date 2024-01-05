<?php

namespace MuckiSearchPlugin\Indexing;

use Psr\Log\LoggerInterface;
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

class Write
{
    public function __construct(
        protected LoggerInterface  $logger,
        protected Products $products,
        protected CliOutput $cliOutput,
        protected IndexStructure $indexStructure,
        protected IndicesSettings $indicesSettings,
        protected SearchClientFactory $searchClientFactory,
        protected PluginSettings $pluginSettings
    ){}

    public function doIndexing(OutputInterface $cliOutput = null): void
    {
        $allActiveIndexStructure = $this->indexStructure->getAllActiveIndexStructure();
        $indexStructureCounter = $allActiveIndexStructure->count();
        $progressIndexStructure = $this->cliOutput->prepareIndexStructureProgress($indexStructureCounter);
        $progressIndexStructureBar = $this->cliOutput->prepareIndexStructureProgressBar(
            $progressIndexStructure,
            $indexStructureCounter,
            $cliOutput
        );

        if ($indexStructureCounter >= 1) {

            $this->cliOutput->printCliOutput(
                $cliOutput,
                'Found '.$indexStructureCounter.' index Structures'
            );

            /** @var IndexStructureEntity $indexStructure */
            foreach ($allActiveIndexStructure->getEntities() as $indexStructure) {

                if ($progressIndexStructure->getOffset() >= $progressIndexStructure->getTotal()) {
                    $progressIndexStructureBar->setProgress($progressIndexStructure->getTotal());
                } else {
                    $progressIndexStructureBar->advance();
                    $progressIndexStructureBar->display();
                }

                $this->indicesSettings->setTemplateVariable('entity', $indexStructure->getEntity());
                $this->indicesSettings->setTemplateVariable('salesChannelId', $indexStructure->getSalesChannelId());
                $allActiveProduct = $this->products->getAllActiveProduct($indexStructure->getSalesChannelId());
                $totalProductCounter = $allActiveProduct->count();

                $this->cliOutput->printCliOutput(
                    $cliOutput,
                    'Found '.$totalProductCounter.' products'
                );

                $progress = $this->cliOutput->prepareProductProgress($totalProductCounter);
                $progressBar = $this->cliOutput->prepareProductProgressBar($progress, $totalProductCounter, $cliOutput);

                /** @var IndexStructureTranslationEntity $translation */
                foreach ($indexStructure->get('translations') as $translation) {

                    $this->indicesSettings->setTemplateVariable('languageId', $translation->getLanguageId());
                    $indexName = $this->indicesSettings->getIndexNameByTemplate();
                    if($this->searchClientFactory->createSearchClient()->checkIndicesExists($indexName)) {

                        /** @var ProductEntity $product */
                        foreach ($allActiveProduct->getEntities() as $product) {

                            if ($progress->getOffset() >= $progress->getTotal()) {
                                $progressBar->setProgress($progress->getTotal());
                            } else {
                                $progressBar->advance();
                                $progressBar->display();
                            }

                            $indexBody = new CreateIndexBody($this->pluginSettings);
                            $indexBody->setIndexName($indexName);

                            foreach ($translation->get('mappings') as $mapping) {

                                if(str_contains($mapping['key'], 'translations.DEFAULT')) {

                                    $translationsElements = $product->get('translations')->getVars()['elements'];

                                    /**
                                     * @var string $translationsKey
                                     * @var ProductTranslationEntity $translationsValue
                                     */
                                    foreach ($translationsElements as $translationsKey => $translationsValue) {

                                        if(explode('-', $translationsKey)[1]) {

                                            $fieldName = str_replace('translations.DEFAULT.', '', $mapping['key']);
                                            $fieldValue = $translationsValue->get($fieldName);
                                            if($fieldValue) {
                                                $indexBody->setBodyItem(
                                                    $fieldName,
                                                    $translationsValue->get($fieldName)
                                                );
                                            }
                                        }
                                    }
                                } else {
                                    $indexBody->setBodyItem($mapping['key'], $product->get($mapping['key']));
                                }
                            }

                            $this->searchClientFactory->createSearchClient()->indexing(
                                $indexBody->getIndexBody()
                            );
                        }
                    }
                }
            }
        }
    }
}
