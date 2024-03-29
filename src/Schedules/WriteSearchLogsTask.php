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

class WriteSearchLogsTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'muwa.write_search_logs_task';
    }

    public static function getDefaultInterval(): int
    {
        return 300; // 5 minutes
    }
}
