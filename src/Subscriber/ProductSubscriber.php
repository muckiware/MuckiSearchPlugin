<?php declare(strict_types=1);

namespace MuckiSearchPlugin\Subscriber;

use Shopware\Core\Content\Product\ProductEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityDeletedEvent;
use Symfony\Component\HttpFoundation\Request;

class ProductSubscriber implements EventSubscriberInterface
{
    protected array $request;

    public function __construct(
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
        $checker = true;
    }
}
