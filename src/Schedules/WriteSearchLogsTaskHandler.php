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

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;

class WriteSearchLogsTaskHandler extends ScheduledTaskHandler
{
    public function __construct(
        EntityRepository $scheduledTaskRepository,
        protected LoggerInterface $logger
    )
    {
        parent::__construct($scheduledTaskRepository, $logger);
    }
    public static function getHandledMessages(): iterable
    {
        return [ WriteSearchLogsTask::class ];
    }

    public function run(): void
    {
        $this->logger->debug('Run WriteSearchLogsTask', array('mucki','search'));
    }
}
