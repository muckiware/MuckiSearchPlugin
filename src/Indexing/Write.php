<?php

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
                $this->cliOutput->printCliOutput($cliOutput, 'Search for products...');
                $allActiveProduct = $this->products->getAllActiveProduct($indexStructure->getSalesChannelId());
                $totalProductCounter = $allActiveProduct->count();

                $this->cliOutput->printCliOutput(
                    $cliOutput,
                    'Found '.$totalProductCounter.' products'
                );

                /** @var IndexStructureTranslationEntity $translation */
                foreach ($indexStructure->get('translations') as $translation) {

                    $translationLanguageLabel = $translation->get('language')->getName();

                    $progressProduct = $this->cliOutput->prepareProductProgress($totalProductCounter);
                    $progressProductBar = $this->cliOutput->prepareProductProgressBar(
                        $progressProduct,
                        $translationLanguageLabel,
                        $totalProductCounter,
                        $cliOutput
                    );

                    $this->indicesSettings->setTemplateVariable('languageId', $translation->getLanguageId());
                    $indexName = $this->indicesSettings->getIndexNameByTemplate();
                    if($this->searchClientFactory->createSearchClient()->checkIndicesExists($indexName)) {

                        /** @var ProductEntity $product */
                        foreach ($allActiveProduct->getEntities() as $product) {

                            if ($progressProduct->getOffset() >= $progressProduct->getTotal()) {
                                $progressProductBar->setProgress($progressProduct->getTotal());
                            } else {
                                $progressProductBar->advance();
                                $progressProductBar->display();
                            }

                            $indexBody = new CreateIndexBody($this->pluginSettings);
                            $indexBody->setIndexName($indexName);

                            $this->setBodyItems(
                                $indexBody,
                                $translation->get('mappings'),
                                $product,
                                $translation->get('language')
                            );

                            $this->searchClientFactory->createSearchClient()->indexing(
                                $indexBody->getIndexBody()
                            );
                        }
                    }
                }

                $cliOutput->write('',true);
            }
        }
    }

    protected function setBodyItems(
        CreateIndexBody $indexBody,
        array $mappings,
        ProductEntity $product,
        LanguageEntity $language
    ): void
    {
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
                $indexBody->setBodyItem(implode('_',$bodyKey), $propertyContent);
            }
        }
    }
}
