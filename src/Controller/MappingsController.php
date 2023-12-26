<?php declare(strict_types=1);

namespace MuckiSearchPlugin\Controller;

use Shopware\Core\Framework\Log\Package;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

use MuckiSearchPlugin\Services\Settings as PluginSettings;


#[Route(defaults: ['_routeScope' => ['api']])]
#[Package('services-settings')]
class MappingsController extends AbstractController
{
    /**
     * @internal
     */
    public function __construct(
        protected PluginSettings $pluginSettings
    ) {
    }

    #[Route(
        path: '/api/_action/muwa/search/default-product-mappings',
        name: 'api.action.import_export.features',
        methods: ['GET']
    )]
    public function defaultProductMappings(): JsonResponse
    {
        return new JsonResponse($this->pluginSettings->getDefaultProductMapping());
    }
}
