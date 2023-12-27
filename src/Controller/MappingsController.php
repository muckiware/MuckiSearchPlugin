<?php declare(strict_types=1);

namespace MuckiSearchPlugin\Controller;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

use MuckiSearchPlugin\Services\Settings as PluginSettings;
use MuckiSearchPlugin\Services\Content\IndexStructure as IndexStructureService;


#[Route(defaults: ['_routeScope' => ['api']])]
#[Package('services-settings')]
class MappingsController extends AbstractController
{
    /**
     * @internal
     */
    public function __construct(
        protected PluginSettings $pluginSettings,
        protected IndexStructureService $indexStructureService
    ) {
    }

    #[Route(
        path: '/api/_action/muwa/search/default-product-mappings',
        name: 'api.action.muwa_search.default-product-mappings',
        methods: ['GET']
    )]
    public function defaultProductMappings(): JsonResponse
    {
        return new JsonResponse($this->pluginSettings->getDefaultProductMapping());
    }

    #[Route(
        path: '/api/_action/muwa/search/save-mappings',
        name: 'api.action.muwa_search.save-mappings',
        methods: ['POST']
    )]
    public function saveMappings(RequestDataBag $requestDataBag, Context $context): JsonResponse
    {

//        $this->indexStructureService->saveMappingsByLanguageId(
//            $context->getLanguageId()
//        );
        return new JsonResponse($this->pluginSettings->getDefaultProductMapping());
    }
}
