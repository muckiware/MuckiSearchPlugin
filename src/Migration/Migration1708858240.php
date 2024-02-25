<?php declare(strict_types=1);

namespace MuckiSearchPlugin\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1708858240 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1708858240;
    }

    public function update(Connection $connection): void
    {
        $connection->executeUpdate('
            CREATE TABLE IF NOT EXISTS `muwa_search_request_logs` (
                `id` binary(16) NOT NULL,
                `sales_channel_id` binary(16) NOT NULL,
                `created_at` datetime(3) NOT NULL,
                `updated_at` datetime(3) DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `fk.muwa_search_request_logs.sales_channel_id` (`sales_channel_id`),
                CONSTRAINT `fk.muwa_search_request_logs.sales_channel_id` FOREIGN KEY (`sales_channel_id`) REFERENCES `sales_channel` (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS `muwa_search_request_logs_translation` (
                `muwa_search_request_logs_id` binary(16) NOT NULL,
                `language_id` binary(16) NOT NULL,
                `search_term` varchar(255) NOT NULL,
                `hits` int(7) NULL,
                `created_at` datetime(3) NOT NULL,
                `updated_at` datetime(3) DEFAULT NULL,
                PRIMARY KEY (`muwa_search_request_logs_id`,`language_id`),
                KEY `fk.muwa_search_request_logs_translation.language_id` (`language_id`),
                CONSTRAINT `fk.muwa_search_request_logs_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.muwa_search_request_logs_translation.muwa_index_structure_id` FOREIGN KEY (`muwa_search_request_logs_id`) REFERENCES `muwa_search_request_logs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
