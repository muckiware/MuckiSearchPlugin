<?php declare(strict_types=1);

namespace MuckiSearchPlugin\Subscriber;

use Shopware\Core\Content\Product\ProductEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntitySearchResultLoadedEvent;

use MuckiSearchPlugin\Services\Settings as PluginSettings;
use MuckiSearchPlugin\Indexing\Product as IndexingProduct;
use MuckiSearchPlugin\Indexing\Write as IndexingWrite;
use MuckiSearchPlugin\Search\SearchClientFactory;
use MuckiSearchPlugin\Services\Content\IndexStructure;
use MuckiSearchPlugin\Core\Content\IndexStructure\IndexStructureEntity;
use MuckiSearchPlugin\Entities\IndexStructureInstance;
use MuckiSearchPlugin\Services\IndicesSettings;
use MuckiSearchPlugin\Services\Content\Products as ContentProducts;

class SearchSubscriber implements EventSubscriberInterface
{
    protected array $request;

    public function __construct(
        protected PluginSettings $pluginSettings,
        protected SearchClientFactory $searchClientFactory
    )
    {
        $this->request = $_REQUEST;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductEvents::PRODUCT_SEARCH_RESULT_LOADED_EVENT => 'onProductSearchResult',
        ];
    }

    public function onProductSearchResult(EntitySearchResultLoadedEvent $event): void
    {
        if($this->checkSearchEngineAvailable($event)) {

            $searchResults = $event->getResult();
            $searchResults->clear();
        }
    }

    public function checkSearchEngineAvailable(EntitySearchResultLoadedEvent $event): bool
    {
        if($event->getContext()->getScope() === 'user') {

            if($this->pluginSettings->isEnabled()) {

                if($this->searchClientFactory->createSearchClient()->getServerInfoAsObject()) {
                    return true;
                }
            }
        }
        return false;
    }
}
