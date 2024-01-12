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

use Shopware\Core\Framework\Plugin;

class MuckiSearchPlugin extends Plugin
{
}
