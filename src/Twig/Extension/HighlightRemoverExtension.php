<?php declare(strict_types=1);

namespace MuckiSearchPlugin\Twig\Extension;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

use MuckiSearchPlugin\Services\Settings as PluginSettings;

#[Package('core')]
class HighlightRemoverExtension extends AbstractExtension
{
    public function __construct(
        protected PluginSettings $pluginSettings
    )
    {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('muwa_removeHighlightMarkers', $this->removeHighlightMarkers(...)),
        ];
    }

    public function removeHighlightMarkers(string $input): ?string
    {
        $replaceItems[] = $this->pluginSettings->getSearchRequestSettingsPreTags();
        $replaceItems[] = $this->pluginSettings->getSearchRequestSettingsPostTags();

        return str_replace($replaceItems, '', $input);
    }
}
