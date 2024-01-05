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
use MuckiSearchPlugin\Core\Content\ServerOptions\ServerOptionsFactory;

#[Route(defaults: ['_routeScope' => ['api']])]
#[Package('services-settings')]
class SettingsMappingsController extends AbstractController
{
    /**
     * @internal
     */
    public function __construct(
        protected PluginSettings $pluginSettings,
        protected IndexStructureService $indexStructureService,
        protected ServerOptionsFactory $serverOptionsFactory
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
        path: '/api/_action/muwa/search/default-indices-settings',
        name: 'api.action.muwa_search.default-indices-settings',
        methods: ['GET']
    )]
    public function defaultIndicesSettings(): JsonResponse
    {
        return new JsonResponse($this->pluginSettings->getDefaultIndicesSettings());
    }

    #[Route(
        path: '/api/_action/muwa/server/mapping-input-data-types',
        name: 'api.action.muwa_server.mapping-input-data-types',
        methods: ['GET']
    )]
    public function serverMappingInputDataTypes(): JsonResponse
    {
        return new JsonResponse($this->serverOptionsFactory->createServerOptions()->getDataTypes());
    }

    /**
     * @throws WriteException|\Exception
     */
    #[Route(
        path: '/api/_action/muwa/search/save-mappings-settings',
        name: 'api.action.muwa_search.save-mappings-settings',
        methods: ['POST']
    )]
    public function saveMappingsSettingsSettings(RequestDataBag $requestDataBag, Context $context): JsonResponse
    {
        $saveMappingsResults = $this->indexStructureService->saveMappingsSettingsByLanguageId(
            $this->getMappings($requestDataBag),
            $this->getSettings($requestDataBag),
            $requestDataBag->get('id'),
            $requestDataBag->get('languageId'),
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

    protected function getSettings(RequestDataBag $requestDataBag): array
    {
        /** @var RequestDataBag $settings */
        $settings = $requestDataBag->get('translated')->get('settings');
        return $settings->all();
    }
}
