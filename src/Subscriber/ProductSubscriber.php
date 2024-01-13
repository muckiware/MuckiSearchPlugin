<?php declare(strict_types=1);

namespace MuckiSearchPlugin\Subscriber;

use MuckiSearchPlugin\Entities\IndexStructureInstance;
use Shopware\Core\Content\Product\ProductEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityDeletedEvent;
use Shopware\Core\Framework\DataAbstractionLayer\EntityWriteResult;

use MuckiSearchPlugin\Indexing\Product as IndexingProduct;
use MuckiSearchPlugin\Indexing\Write as IndexingWrite;
use MuckiSearchPlugin\Search\SearchClientFactory;

class ProductSubscriber implements EventSubscriberInterface
{
    protected array $request;

    public function __construct(
        protected IndexingProduct $indexingProduct,
        protected IndexingWrite $indexingWrite,
        protected SearchClientFactory $searchClientFactory
    )
    {
        $this->request = $_REQUEST;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductEvents::PRODUCT_WRITTEN_EVENT => 'onProductWritten',
            ProductEvents::PRODUCT_DELETED_EVENT => 'onProductDeleted'
        ];
    }

    public function onProductDeleted(EntityDeletedEvent $event): void
    {
        $checker = true;
    }

    public function onProductWritten(EntityWrittenEvent $event): void
    {
        if($event->getEntityName() === 'product') {

            $searchClient = $this->searchClientFactory->createSearchClient();

            foreach ($event->getWriteResults() as $writeResult) {

                if($writeResult->getOperation() === 'update' || $writeResult->getOperation() === 'create') {

                    $payload = $writeResult->getPayload();

                    /** @var IndexStructureInstance $indexStructureInstance */
                    foreach ($this->indexingWrite->getIndexStructureInstances() as $indexStructureInstance) {

                        if(!$searchClient->checkIndicesExists($indexStructureInstance->getIndexName())) {
                            continue;
                        }

                        $this->indexingProduct->indexingProduct(
                            $indexStructureInstance,
                            $searchClient,
                            $payload['id']
                        );
                    }
                }
            }
        }
    }
}
