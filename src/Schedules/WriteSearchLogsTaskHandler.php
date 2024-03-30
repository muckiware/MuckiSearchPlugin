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

use League\Flysystem\FilesystemException;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;

use MuckiSearchPlugin\Services\SearchTermLog;

#[AsMessageHandler(handles: WriteSearchLogsTask::class)]
class WriteSearchLogsTaskHandler extends ScheduledTaskHandler
{
    public function __construct(
        EntityRepository $scheduledTaskRepository,
        protected LoggerInterface $logger,
        protected SearchTermLog $searchTermLog
    )
    {
        parent::__construct($scheduledTaskRepository, $logger);
    }
    public static function getHandledMessages(): iterable
    {
        return [ WriteSearchLogsTask::class ];
    }

    /**
     * @throws FilesystemException
     */
    public function run(): void
    {
        $this->logger->debug('Run WriteSearchLogsTask', array('mucki','search'));

        $serverInfoAsString = $this->searchTermLog->saveSearchLogsIntoDb();
        if($serverInfoAsString) {
            $this->logger->debug('Run WriteSearchLogsTask is done', array('mucki','search'));
        }
    }
}
