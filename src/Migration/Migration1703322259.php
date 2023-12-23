<?php declare(strict_types=1);

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
              `id` BINARY(16) NOT NULL,
              `active` TINYINT(1) NULL,
              `name` VARCHAR(255) NULL,
              `entity` VARCHAR(255) NOT NULL,
              `sales_channel_id` BINARY(16) NOT NULL,
              `language_id` BINARY(16) NOT NULL,
              `mappings` LONGTEXT NULL,
              `created_at` DATETIME(3) NOT NULL,
              `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
