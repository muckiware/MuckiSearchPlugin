<?php
/**
 * MuckiSearchPlugin plugin
 *
 *
 * @category   Muckiware
 * @package    MuckiSearch
 * @copyright  Copyright (c) 2023 by Muckiware
 *
 * @author     Muckiware
 *
 */

declare(strict_types=1);

namespace MuckiSearchPlugin;

/**
 * Add dependencies from composer
 */
if(file_exists(dirname(__DIR__) . "/vendor/autoload.php")) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;

class MuckiSearchPlugin extends Plugin
{
    /**
     * @throws Exception
     */
    public function uninstall(UninstallContext $uninstallContext): void
    {
        if ($uninstallContext->keepUserData()) {
            return;
        } else {

            /** @var Connection $connection */
            $connection = $this->container->get(Connection::class);
            $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
            $connection->executeStatement('DROP TABLE `muwa_index_structure`');
            $connection->executeStatement('DROP TABLE `muwa_index_structure_translation`');
            $connection->executeStatement('DROP TABLE `muwa_search_request_logs`');
            $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
        }
    }
}
