<?php
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
namespace MuckiSearchPlugin\Schedules;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

use MuckiSearchPlugin\Core\Defaults as PluginDefaults;

class WriteSearchLogsTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'muwa.write_search_logs_task';
    }

    public static function getDefaultInterval(): int
    {
        return PluginDefaults::DEFAULT_TASK_INTERVAL_IN_SECONDS;
    }
}
