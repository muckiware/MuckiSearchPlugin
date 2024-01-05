<?php declare(strict_types=1);

namespace MuckiSearchPlugin\Entities;

use MuckiSearchPlugin\Services\Settings as PluginSettings;
use Shopware\Core\Framework\Uuid\Uuid;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;

class Indices
{
    /**
     * UUID for a search mapping object
     * @var string
     */
    protected string $indexId;

    protected string $indexName;

    protected string $salesChannelId;

    protected string $languageId;

    /**
     * @param PluginSettings $pluginSettings
     */
    public function __construct(
        protected PluginSettings $pluginSettings,
        protected Environment $twig,
        string $salesChannelId,
        string $languageId
    )
    {
        $this->salesChannelId = $salesChannelId;
        $this->languageId = $languageId;
    }

    /**
     * @return string
     */
    public function getIndexId(): string
    {
        return $this->indexId;
    }

    /**
     * @param string $indexId
     */
    public function setIndexId(string $indexId): void
    {
        $this->indexId = $indexId;
    }

    /**
     * @return string
     */
    public function getIndexName(): string
    {
//        $pattern = $this->pluginSettings->getIndexNamePattern();
        try {
            $patternTemplate = $this->twig->createTemplate('{% {{salesChannelId}} %}-', 'patternTemplate');
            $pattern = $patternTemplate->render(array('salesChannelId' => $this->salesChannelId));
        } catch (LoaderError $e) {
        } catch (SyntaxError $e) {
        }

        return sprintf($pattern, $this->salesChannelId, $this->languageId);
    }

    /**
     * @param string $indexName
     */
    public function setIndexName(string $indexName): void
    {
        $this->indexName = $indexName;
    }
}
