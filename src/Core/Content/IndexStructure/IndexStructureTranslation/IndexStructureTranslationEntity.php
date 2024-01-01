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

namespace MuckiSearchPlugin\Core\Content\IndexStructure\IndexStructureTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class IndexStructureTranslationEntity extends Entity
{
    use EntityIdTrait;

    protected string $languageId;

    /**
     * @return string
     */
    public function getLanguageId(): string
    {
        return $this->languageId;
    }

    /**
     * @param string $languageId
     */
    public function setLanguageId(string $languageId): void
    {
        $this->languageId = $languageId;
    }
}

