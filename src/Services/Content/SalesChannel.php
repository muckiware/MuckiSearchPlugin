<?php

namespace MuckiSearchPlugin\Services\Content;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

class SalesChannel
{
    final const ENTITY_NAME = 'salesChannel';

    public function __construct(
        protected LoggerInterface $logger,
        protected EntityRepository $salesChannelRepository
    ){}

    public function getSalesChannelById(string $salesChannelId): ?SalesChannelEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('id', [$salesChannelId]));
        $criteria->addAssociation('domains');

        $salesChannelRepository = $this->salesChannelRepository->search($criteria, Context::createDefaultContext());
        if ($salesChannelRepository->count() >= 1) {
            return $salesChannelRepository->first();
        } else {
            return null;
        }
    }
}

