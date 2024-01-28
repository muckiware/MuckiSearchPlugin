<?php declare(strict_types=1);

namespace MuckiSearchPlugin\Core\System\SalesChannel\Entity;

use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Product\SalesChannel\Listing\Processor\CompositeListingProcessor;
use Shopware\Core\Content\Product\SearchKeyword\ProductSearchBuilderInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityAggregationResultLoadedEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityLoadedEventFactory;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntitySearchResultLoadedEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Field\AssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Read\EntityReaderInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntityAggregatorInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearcherInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\Event\SalesChannelProcessCriteriaEvent;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelEntityIdSearchResultLoadedEvent;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelDefinitionInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

use MuckiSearchPlugin\Search\SearchClientFactory;
use MuckiSearchPlugin\Services\IndicesSettings;
use MuckiSearchPlugin\Services\Content\IndexStructure;
use MuckiSearchPlugin\Search\SearchClientInterface;
use MuckiSearchPlugin\Services\Searching as ServicesSearching;
use MuckiSearchPlugin\Services\Settings as PluginSettings;

/**
 *
 * @template TEntityCollection of EntityCollection
 */
#[Package('buyers-experience')]
class SalesChannelRepositoryDecorator extends SalesChannelRepository
{
    private SalesChannelRepository $originalSalesChannelRepository;
    /**
     * @internal
     */
    public function __construct(
        protected SalesChannelRepository $salesChannelRepository,
        private readonly EntityDefinition $definition,
        private readonly EntityReaderInterface $reader,
        private readonly EntitySearcherInterface $searcher,
        protected EntityAggregatorInterface $aggregator,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly EntityLoadedEventFactory $eventFactory,
        protected ServicesSearching $servicesSearching,
        protected SearchClientFactory $searchClientFactory,
        protected IndexStructure $indexStructure,
        protected RequestStack $requestStack,
        protected CompositeListingProcessor $processor,
        protected ProductSearchBuilderInterface $searchBuilder,
        protected IndicesSettings $indicesSettings,
        protected PluginSettings $pluginSettings
    ) {
        $this->originalSalesChannelRepository = $salesChannelRepository;

        parent::__construct(
            $definition,
            $reader,
            $searcher,
            $aggregator,
            $eventDispatcher,
            $eventFactory
        );
    }

    public function search(Criteria $criteria, SalesChannelContext $salesChannelContext): EntitySearchResult
    {
        $request = $this->requestStack->getCurrentRequest();
        $searchEngineAvailable = $this->servicesSearching->checkSearchEngineAvailable(
            $request,
            $salesChannelContext,
            'product'
        );

        if($searchEngineAvailable) {
            $searchResults = $this->pluginSearch($request, $criteria, $salesChannelContext);
            if($searchResults) {
                return $searchResults;
            }
        }

        return $this->regularSearch($criteria, $salesChannelContext);
    }

    public function pluginSearch(
        Request $request,
        Criteria $criteria,
        SalesChannelContext $salesChannelContext
    ): ?EntitySearchResult
    {
        $searchClient = $this->searchClientFactory->createSearchClient();

        $this->processor->prepare($request, $criteria, $salesChannelContext);
        $this->searchBuilder->build($request, $criteria, $salesChannelContext);
        $resultsByServer = $this->getResultsByServer($searchClient, $criteria, $salesChannelContext);

        if($resultsByServer && $resultsByServer['hits'] >= 1) {

            $salesChannelProductCollection = $searchClient->createSalesChannelProductCollection(
                $resultsByServer,
                $salesChannelContext->getSalesChannelId(),
                $this->salesChannelRepository,
                $salesChannelContext
            );

            return new EntitySearchResult(
                $this->definition->getEntityName(),
                0,
                $salesChannelProductCollection,
                null,
                $criteria,
                $salesChannelContext->getContext()
            );
        }

        return null;
    }

    protected function getResultsByServer(
        SearchClientInterface $searchClient,
        Criteria $criteria,
        SalesChannelContext $salesChannelContext
    ): ?array
    {
        $currentIndexStructure = $this->indexStructure->getCurrentIndexStructure(
            'product',
            $salesChannelContext->getLanguageId(),
            $salesChannelContext->getSalesChannelId(),
            $salesChannelContext->getContext()
        );

        $this->indicesSettings->setTemplateVariable('entity', 'product');
        $this->indicesSettings->setTemplateVariable('salesChannelId', $salesChannelContext->getSalesChannelId());
        $this->indicesSettings->setTemplateVariable('languageId', $salesChannelContext->getLanguageId());

        $searchQueryRequestBody = array(
            'query' => array (
                'bool' => array(
                    'should' => $searchClient->createQueryObject(
                        $criteria,
                        $currentIndexStructure->get('mappings')
                    )
                )
            )
        );

        $highlightObject = $searchClient->createHighlightObject(
            $this->pluginSettings,
            $currentIndexStructure->get('mappings')
        );
        if(!empty($highlightObject)) {
            $searchQueryRequestBody['highlight'] = $highlightObject;
        }

        return $searchClient->searching(array(
            'index' => $this->indicesSettings->getIndexNameByTemplate(),
            'body' => $searchQueryRequestBody
        ));
    }

    /**
     * @throws InconsistentCriteriaIdsException
     *
     * @return EntitySearchResult<TEntityCollection>
     */
    public function regularSearch(Criteria $criteria, SalesChannelContext $salesChannelContext): EntitySearchResult
    {
        return parent::search($criteria, $salesChannelContext);
    }

    /**
     * @return TEntityCollection
     */
    private function read(Criteria $criteria, SalesChannelContext $salesChannelContext, bool $skipReading=false): EntityCollection
    {
        $criteria = clone $criteria;

        /** @var TEntityCollection $entities */
        $entities = $this->reader->read($this->definition, $criteria, $salesChannelContext->getContext());

        if ($criteria->getFields() === []) {
            $events = $this->eventFactory->createForSalesChannel($entities->getElements(), $salesChannelContext);
        } else {
            $events = $this->eventFactory->createPartialForSalesChannel($entities->getElements(), $salesChannelContext);
        }

        foreach ($events as $event) {
            $this->eventDispatcher->dispatch($event);
        }

        return $entities;
    }

    private function doSearch(Criteria $criteria, SalesChannelContext $salesChannelContext): IdSearchResult
    {
        $result = $this->searcher->search($this->definition, $criteria, $salesChannelContext->getContext());

        $event = new SalesChannelEntityIdSearchResultLoadedEvent($this->definition, $result, $salesChannelContext);
        $this->eventDispatcher->dispatch($event, $event->getName());

        return $result;
    }

    private function processCriteria(Criteria $topCriteria, SalesChannelContext $salesChannelContext): void
    {
        if (!$this->definition instanceof SalesChannelDefinitionInterface) {
            return;
        }

        $queue = [
            ['definition' => $this->definition, 'criteria' => $topCriteria],
        ];

        $maxCount = 100;

        $processed = [];

        // process all associations breadth-first
        while (!empty($queue) && --$maxCount > 0) {
            $cur = array_shift($queue);

            $definition = $cur['definition'];
            $criteria = $cur['criteria'];

            if (isset($processed[$definition::class])) {
                continue;
            }

            if ($definition instanceof SalesChannelDefinitionInterface) {
                $definition->processCriteria($criteria, $salesChannelContext);

                $eventName = \sprintf('sales_channel.%s.process.criteria', $definition->getEntityName());
                $event = new SalesChannelProcessCriteriaEvent($criteria, $salesChannelContext);

                $this->eventDispatcher->dispatch($event, $eventName);
            }

            $processed[$definition::class] = true;

            foreach ($criteria->getAssociations() as $associationName => $associationCriteria) {
                // find definition
                $field = $definition->getField($associationName);
                if (!$field instanceof AssociationField) {
                    continue;
                }

                $referenceDefinition = $field->getReferenceDefinition();
                $queue[] = ['definition' => $referenceDefinition, 'criteria' => $associationCriteria];
            }
        }
    }
}
