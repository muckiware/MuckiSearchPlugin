<?php

namespace MuckiSearchPlugin\Services;

use Psr\Log\LoggerInterface;

use Twig\Environment;

use MuckiSearchPlugin\Services\Settings as PluginSettings;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;

class IndicesSettings
{

    protected array $templateVariables;

    public function __construct(
        protected PluginSettings $pluginSettings,
        protected Environment $twig,
        protected LoggerInterface $logger
    ){}

    public function setTemplateVariable(string $key, string $value): void
    {
        $this->templateVariables[$key] = $value;
    }

    public function getTemplateVariable(): array
    {
        return $this->templateVariables;
    }

    public function getIndexNameByTemplate(): ?string
    {
        if(!empty($this->templateVariables)) {

            try {
                $patternTemplate = $this->twig->createTemplate(
                    $this->pluginSettings->getIndexNameTemplate(),
                    'patternTemplate'
                );
                return $patternTemplate->render($this->templateVariables);
            } catch (LoaderError $e) {
                $this->logger->error('Problems with Twig LoaderError');
                $this->logger->error($e->getMessage());
            } catch (SyntaxError $e) {
                $this->logger->error('Twig syntax problems');
                $this->logger->error($e->getMessage());
            }
        }

        return null;
    }

    public function getIndexId(): string
    {
        return md5($this->getIndexNameByTemplate());
    }
}

