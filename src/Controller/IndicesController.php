<?php declare(strict_types=1);

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
class IndicesController extends AbstractController
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
        path: '/api/_action/muwa/search/indices',
        name: 'api.action.muwa_search.indices',
        methods: ['GET']
    )]
    public function getIndices(): JsonResponse
    {
        return new JsonResponse(
            $this->searchClientFactory->createSearchClient()->getIndices()
        );
    }

    #[Route(
        path: '/api/_action/muwa/search/create-indices',
        name: 'api.action.muwa_search.create.indices',
        methods: ['POST']
    )]
    public function saveIndices(RequestDataBag $requestDataBag, Context $context): JsonResponse
    {

//        $this->searchClientFactory->createSearchClient()->getClient()->indices()->exists()
        return new JsonResponse(
            $this->searchClientFactory
                ->createSearchClient()
                ->saveIndicesByIndexStructureId(
                    $requestDataBag->get('id'),
                    $requestDataBag->get('languageId'),
                    $context
                )
        );
    }

    #[Route(
        path: '/api/_action/muwa/search/remove-indices',
        name: 'api.action.muwa_search.remove.indices',
        methods: ['POST']
    )]
    public function removeIndices(RequestDataBag $requestDataBag, Context $context): JsonResponse
    {
        $indexStructureId = $requestDataBag->get('id', '');
        $indexStructureIndexName = $requestDataBag->get('index');
        $languageId = $requestDataBag->get('languageId', Defaults::LANGUAGE_SYSTEM);

        if($indexStructureId !== '' && Uuid::isValid($indexStructureId) && !$indexStructureIndexName) {

            $responseContent = $this->searchClientFactory
                ->createSearchClient()
                ->removeIndicesByIndexStructureId($indexStructureId, $languageId, $context);
        } else {

            $responseContent = $this->searchClientFactory
                ->createSearchClient()
                ->removeIndicesByIndexName($indexStructureIndexName);
        }

        return new JsonResponse($responseContent);
    }
}
