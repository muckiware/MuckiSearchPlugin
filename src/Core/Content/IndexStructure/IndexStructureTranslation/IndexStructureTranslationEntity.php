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

    protected string $mappings;

    /**
     * @return string
     */
    public function getMappings(): string
    {
        return $this->mappings;
    }

    /**
     * @param string $mappings
     */
    public function setMappings(string $mappings): void
    {
        $this->mappings = $mappings;
    }
}

