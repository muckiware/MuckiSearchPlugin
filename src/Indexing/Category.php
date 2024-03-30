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
use Shopware\Core\Content\Category\CategoryEntity;

use MuckiSearchPlugin\Core\Defaults;
use MuckiSearchPlugin\Search\SearchClientFactory;
use MuckiSearchPlugin\Search\SearchClientInterface;
use MuckiSearchPlugin\Entities\CreateIndexBody;
use MuckiSearchPlugin\Services\CliOutput;
use MuckiSearchPlugin\Services\Settings as PluginSettings;
use MuckiSearchPlugin\Services\Helper as PluginHelper;
use MuckiSearchPlugin\Entities\IndexStructureInstance;
use MuckiSearchPlugin\Services\Content\SalesChannel as SalesChannelService;

class Category extends IndexData
{
    protected int $createCounter;

    protected int $updateCounter;

    protected string $currentIndicesName;

    public function __construct(
        protected LoggerInterface  $logger,
        protected CliOutput $cliOutput,
        protected SearchClientFactory $searchClientFactory,
        protected PluginSettings $pluginSettings,
        protected PluginHelper $pluginHelper
    ){
        $this->updateCounter = 0;
        $this->createCounter = 0;
    }

    public function indexingCategories(
        IndexStructureInstance $indexStructureInstance,
        SearchClientInterface $searchClient,
        ?OutputInterface $cliOutput = null,
    ): void
    {
        $this->createCounter = 0;
        $this->updateCounter = 0;

        if($cliOutput) {

            $progressCategory = $this->cliOutput->prepareCategoryProgress($indexStructureInstance->getItemTotals());
            $progressCategoryBar = $this->cliOutput->prepareCategoryProgressBar(
                $progressCategory,
                $indexStructureInstance->getLanguageName(),
                $indexStructureInstance->getItemTotals(),
                $cliOutput
            );
        }

        /** @var CategoryEntity $category */
        foreach ($indexStructureInstance->getItems() as $category) {

            if($cliOutput) {

                if ($progressCategory->getOffset() >= $progressCategory->getTotal()) {
                    $progressCategoryBar->setProgress($progressCategory->getTotal());
                } else {
                    $progressCategoryBar->advance();
                    $progressCategoryBar->display();
                }
            }

            $indexBody = new CreateIndexBody($this->pluginSettings);
            $indexBody->setIndexName($indexStructureInstance->getIndexName());

            $bodyItems = $this->getBodyItems(
                $indexStructureInstance->getIndexStructureTranslation()->get('mappings'),
                $category,
                $indexStructureInstance->getIndexStructureTranslation()->get('language')
            );

            $searchResult = $searchClient->searching(array(
                'index' => $indexStructureInstance->getIndexName(),
                'body' => array(
                    'query' => array (
                        'match' => array(
                            'id' => $category->getId()
                        )
                    )
                )
            ));

            $indexActionType = $this->getIndexActionType(md5(serialize($bodyItems)), $searchResult);
            if($indexActionType) {

                $bodyItems[] = array(
                    'propertyPath' => array(0 => 'hash'),
                    'propertyValue' => md5(serialize($bodyItems))
                );
                $indexBody->setBodyItems($this->pluginHelper->createIndexingBody($bodyItems));

                $this->indexingAction(
                    $indexActionType,
                    $searchClient,
                    $indexBody,
                    (($searchResult['hits'] === 0) ? '' : ($searchResult['items'][0]['id'])),
                    $category->getId()
                );
            }
        }

        if($cliOutput) {

            $cliOutput->write( $this->createCounter.' items has been created', true);
            $cliOutput->write( $this->updateCounter.' items has been updated', true);
            $cliOutput->writeln("\n");
        }
    }

    public function removeCategory(
        string $categoryId,
        IndexStructureInstance $indexStructureInstance,
        SearchClientInterface $searchClient,
    ): void
    {

        $searchResult = $searchClient->searching(array(
            'index' => $indexStructureInstance->getIndexName(),
            'body' => array(
                'query' => array (
                    'match' => array(
                        'id' => $categoryId
                    )
                )
            )
        ));

        if($this->pluginHelper->checkIndexSearchResults($searchResult)) {

            $indexBody = new CreateIndexBody($this->pluginSettings);
            $indexBody->setIndexName($indexStructureInstance->getIndexName());
            $this->indexingAction(
                'delete',
                $searchClient,
                $indexBody,
                $searchResult['items'][0]['id'],
                $categoryId
            );
        }
    }

    protected function indexingAction(
        string $indexActionType,
        SearchClientInterface $searchClient,
        CreateIndexBody $indexBody,
        ?string $indexItemId,
        string $categoryId
    ): void
    {
        switch ($indexActionType) {

            case 'create':

                $indexingResult = $searchClient->indexing($indexBody->getIndexBody());
                if($indexingResult) {

                    $this->logger->debug('Product successfully created in index');
                    $this->logger->debug(print_r($indexBody->getIndexBody(), true));
                    $this->createCounter++;
                }
                break;
            case 'update':

                $indexBody->setIndexId($indexItemId);
                $updateResult = $searchClient->updateIndex($indexBody->getIndexBody());
                if($updateResult) {

                    $this->logger->debug('Product successfully updated in index');
                    $this->logger->debug(print_r($indexBody->getIndexBody(), true));
                    $this->updateCounter++;
                }
                break;

            case 'delete':

                $deleteResult = $searchClient->deleteIndex(array(
                    'index' => $indexBody->getIndexName(),
                    'id' => $indexItemId
                ));
                if($deleteResult) {
                    $this->logger->debug('Product successfully deleted in index');
                }
                break;

            default:
                $this->logger->debug('Nothing todo for category id '.$categoryId);
        }
    }

    protected function getIndexActionType(string $dataHash, array $searchResult): ?string
    {
        if($searchResult['hits'] === 0) {
            return 'create';
        } else {

            if(!array_key_exists('hash', $searchResult['items'][0]['source'])) {

                $this->logger->warning('Missing hash item in search result item');
                $this->logger->warning(print_r($searchResult['items'][0]['source'], true));
                return null;
            }

            if($searchResult['items'][0]['source']['hash'] !== $dataHash) {
                return 'update';
            }
        }

        return null;
    }
}
