<?php declare(strict_types=1);

namespace MuckiSearchPlugin\Controller;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Write\WriteException;
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

    /**
     * @throws WriteException|\Exception
     */
    #[Route(
        path: '/api/_action/muwa/search/save-mappings',
        name: 'api.action.muwa_search.save-mappings',
        methods: ['POST']
    )]
    public function saveMappings(RequestDataBag $requestDataBag, Context $context): JsonResponse
    {
        $mappings = $this->getMappings($requestDataBag);
        $languageId = $this->getLanguageId($requestDataBag);
        $indexStructureId = $requestDataBag->get('id');

        $saveMappingsResults = $this->indexStructureService->saveMappingsByLanguageId(
            $mappings,
            $indexStructureId,
            $languageId,
            $context
        );

        return new JsonResponse($saveMappingsResults->getContext());
    }

    protected function getMappings(RequestDataBag $requestDataBag): array
    {
        /** @var RequestDataBag $mappings */
        $mappings = $requestDataBag->get('translated')->get('mappings');
        return $mappings->all();
    }

    protected function getLanguageId(RequestDataBag $requestDataBag): string
    {
        /** @var RequestDataBag $mappings */
        $translations = $requestDataBag->get('translations')->all();
        return $translations[0]['languageId'];
    }
}
