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

namespace MuckiSearchPlugin\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1703322259 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1_703_322_259;
    }

    public function update(Connection $connection): void
    {
        $connection->executeUpdate('
            CREATE TABLE IF NOT EXISTS `muwa_index_structure` (
                `id` binary(16) NOT NULL,
                `active` tinyint(1) DEFAULT 0,
                `name` varchar(255) NOT NULL,
                `entity` varchar(255) NOT NULL,
                `sales_channel_id` binary(16) NOT NULL,
                `created_at` datetime(3) NOT NULL,
                `updated_at` datetime(3) DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS `muwa_index_structure_translation` (
                `muwa_index_structure_id` binary(16) NOT NULL,
                `language_id` binary(16) NOT NULL,
                `mappings` longtext DEFAULT NULL,
                `created_at` datetime(3) NOT NULL,
                `updated_at` datetime(3) DEFAULT NULL,
                PRIMARY KEY (`index_structure_id`,`language_id`),
                KEY `fk.muwa_index_structure_translation.language_id` (`language_id`),
                CONSTRAINT `fk.muwa_index_structure_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.muwa_index_structure_translation.muwa_index_structure_id` FOREIGN KEY (`index_structure_id`) REFERENCES `muwa_index_structure` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
