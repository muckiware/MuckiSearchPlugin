<?php declare(strict_types=1);
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
namespace MuckiSearchPlugin\Controller;

use MuckiSearchPlugin\Search\SearchClientFactory;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Write\WriteException;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

use MuckiSearchPlugin\Services\Settings as PluginSettings;
use MuckiSearchPlugin\Services\Content\IndexStructure as IndexStructureService;
use MuckiSearchPlugin\Core\Content\ServerOptions\ServerOptionsFactory;


#[Route(defaults: ['_routeScope' => ['api']])]
#[Package('services-settings')]
class ServerController extends AbstractController
{
    /**
     * @internal
     */
    public function __construct(
        protected PluginSettings $pluginSettings,
        protected IndexStructureService $indexStructureService,
        protected SearchClientFactory $searchClientFactory
    ) {}

    #[Route(
        path: '/api/_action/muwa/search/server',
        name: 'api.action.muwa_search.server',
        methods: ['GET']
    )]
    public function getServerInfo(): JsonResponse
    {
        return new JsonResponse(
            $this->searchClientFactory->createSearchClient()->getServerInfoAsObject()
        );
    }
}
