<?php declare(strict_types=1);

namespace MuckiSearchPlugin\Subscriber;

use Shopware\Core\Content\Product\ProductEvents;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityDeletedEvent;
use Shopware\Core\Framework\DataAbstractionLayer\EntityWriteResult;

use MuckiSearchPlugin\Indexing\Product as IndexingProduct;
use MuckiSearchPlugin\Indexing\Write as IndexingWrite;
use MuckiSearchPlugin\Search\SearchClientFactory;
use MuckiSearchPlugin\Services\Content\IndexStructure;
use MuckiSearchPlugin\Core\Content\IndexStructure\IndexStructureEntity;
use MuckiSearchPlugin\Entities\IndexStructureInstance;
use MuckiSearchPlugin\Services\IndicesSettings;
use MuckiSearchPlugin\Services\Content\Products as ContentProducts;
use MuckiSearchPlugin\Services\Searching as ServicesSearching;

class ProductSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected IndexingProduct $indexingProduct,
        protected IndexingWrite $indexingWrite,
        protected SearchClientFactory $searchClientFactory,
        protected IndexStructure $indexStructure,
        protected IndicesSettings $indicesSettings,
        protected ContentProducts $contentProducts,
        protected ServicesSearching $servicesSearching,
        protected RequestStack $requestStack
    )
    {}

    public static function getSubscribedEvents(): array
    {
        return [
            ProductEvents::PRODUCT_WRITTEN_EVENT => 'onProductWritten',
            ProductEvents::PRODUCT_DELETED_EVENT => 'onProductDeleted'
        ];
    }

    public function onProductDeleted(EntityDeletedEvent $event): void
    {
        if($event->getEntityName() === 'product') {

            $searchClient = $this->searchClientFactory->createSearchClient();

            foreach ($event->getWriteResults() as $writeResult) {

                if($writeResult->getOperation() === 'delete') {

                    $payload = $writeResult->getPayload();
                    $indexStructureInstances = $this->indexingWrite->getIndexStructureInstances($payload['id']);
                    /** @var IndexStructureInstance $indexStructureInstance */
                    foreach ($indexStructureInstances as $indexStructureInstance) {

                        if(!$searchClient->checkIndicesExists($indexStructureInstance->getIndexName())) {
                            continue;
                        }

                        if($indexStructureInstance->getEntity() === 'product') {

                            $this->indexingProduct->removeProduct(
                                $payload['id'],
                                $indexStructureInstance,
                                $searchClient
                            );
                        }
                    }
                }
            }
        }
    }

    public function onProductWritten(EntityWrittenEvent $event): void
    {
        if($event->getEntityName() === 'product') {

            $searchClient = $this->searchClientFactory->createSearchClient();

            foreach ($event->getWriteResults() as $writeResult) {

                if($writeResult->getOperation() === 'update' || $writeResult->getOperation() === 'create') {

                    $payload = $writeResult->getPayload();
                    if(array_key_exists('id', $payload) && Uuid::isValid($payload['id'])) {
                        $productId = $payload['id'];
                    } else {
                        continue;
                    }

                    $indexStructureInstances = $this->indexingWrite->getIndexStructureInstances($productId);
                    /** @var IndexStructureInstance $indexStructureInstance */
                    foreach ($indexStructureInstances as $indexStructureInstance) {

                        if(!$searchClient->checkIndicesExists($indexStructureInstance->getIndexName())) {
                            continue;
                        }

                        if($indexStructureInstance->getEntity() === 'product') {

                            $requestPayload = $this->searchPayloadInRequestParams($productId);
                            if($this->servicesSearching->checkProductNeedToRemove($requestPayload)) {

                                $this->indexingProduct->removeProduct(
                                    $productId,
                                    $indexStructureInstance,
                                    $searchClient
                                );
                            } else {
                                $this->indexingProduct->indexingProducts($indexStructureInstance, $searchClient);
                            }
                        }
                    }
                }
            }
        }
    }

    protected function searchPayloadInRequestParams(string $productId): array
    {
        $getCurrentRequest = $this->requestStack->getCurrentRequest();
        if($getCurrentRequest) {

            foreach ($getCurrentRequest->request->all() as $requestParameters) {

                foreach ($requestParameters as $requestParameter) {

                    if(is_array($requestParameter)) {

                        foreach ($requestParameter as $payload) {

                            if(
                                array_key_exists('id', $payload) &&
                                Uuid::isValid($payload['id']) &&
                                in_array($productId, $payload)
                            ) {
                                return $payload;
                            }
                        }
                    }
                }
            }
        }
        return array();
    }
}
