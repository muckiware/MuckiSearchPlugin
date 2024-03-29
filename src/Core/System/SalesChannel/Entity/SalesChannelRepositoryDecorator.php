<?php declare(strict_types=1);

namespace MuckiSearchPlugin\Core\System\SalesChannel\Entity;

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
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\System\SalesChannel\Event\SalesChannelProcessCriteriaEvent;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelEntityIdSearchResultLoadedEvent;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelDefinitionInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

use MuckiSearchPlugin\Search\SearchClientFactory;
use MuckiSearchPlugin\Services\Settings as PluginSettings;
use MuckiSearchPlugin\Search\Content\Product as ContentProduct;
use MuckiSearchPlugin\Search\Content\Category as ContentCategory;

/**
 *
 * @template TEntityCollection of EntityCollection
 */
#[Package('buyers-experience')]
class SalesChannelRepositoryDecorator extends SalesChannelRepository
{
    protected ?EntitySearchResult $entitySearchResult;
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
        protected SearchClientFactory $searchClientFactory,
        protected RequestStack $requestStack,
        protected CompositeListingProcessor $processor,
        protected ProductSearchBuilderInterface $searchBuilder,
        protected PluginSettings $pluginSettings,
        protected ContentProduct $contentProduct,
        protected ContentCategory $contentCategory
    ) {
        $this->originalSalesChannelRepository = $salesChannelRepository;
        $this->entitySearchResult = null;

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
        if($this->pluginSettings->isEnabled()) {

            $this->pluginSearch($criteria, $salesChannelContext);
            if(!$this->entitySearchResult || $this->entitySearchResult->getEntities()->count() <= 0) {
                $this->entitySearchResult = $this->pluginSearch($criteria, $salesChannelContext);
            }

            return $this->entitySearchResult;

        }

        return $this->regularSearch($criteria, $salesChannelContext);
    }

    public function pluginSearch(Criteria $criteria, SalesChannelContext $salesChannelContext): ?EntitySearchResult
    {
        $searchClient = $this->searchClientFactory->createSearchClient();
        $request = $this->requestStack->getCurrentRequest();

        if($request->get('search')) {

            $this->processor->prepare($request, $criteria, $salesChannelContext);
            $this->searchBuilder->build($request, $criteria, $salesChannelContext);
        }

        $productSearchCollection = $this->contentProduct->productSearch(
            $searchClient,
            $criteria,
            $this->originalSalesChannelRepository,
            $salesChannelContext
        );

        return new EntitySearchResult(
            $this->definition->getEntityName(),
            $productSearchCollection->count(),
            $productSearchCollection,
            null,
            $criteria,
            $salesChannelContext->getContext()
        );
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

    public function searchIds(Criteria $criteria, SalesChannelContext $salesChannelContext): IdSearchResult
    {
        $criteria = clone $criteria;

        $this->processCriteria($criteria, $salesChannelContext);

        return $this->doSearch($criteria, $salesChannelContext);
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
        if($this->pluginSettings->isEnabled() && !empty($criteria->getQueries())) {
            $this->entitySearchResult = $this->pluginSearch($criteria, $salesChannelContext);
        }

        if($this->entitySearchResult && $this->entitySearchResult->getEntities()->count() >= 1) {

            $data = array();
            foreach ($this->entitySearchResult->getElements() as $elementKey => $elementItems) {
                $data[$elementKey] = array(
                    'primaryKey' => $elementKey,
                    'data' => array(
                        'id' => $elementKey
                    )
                );
            }

            $result = new IdSearchResult(
                $this->entitySearchResult->getEntities()->count(),
                $data,
                $criteria,
                $salesChannelContext->getContext()
            );

        } else {
            $result = $this->searcher->search($this->definition, $criteria, $salesChannelContext->getContext());
        }

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
