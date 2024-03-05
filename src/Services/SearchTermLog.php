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

namespace MuckiSearchPlugin\Services;

use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\FileAttributes;
use Psr\Log\LoggerInterface;

use MuckiSearchPlugin\Entities\SessionSearchRequest;
use Symfony\Component\Console\Output\OutputInterface;

class SearchTermLog
{
    public function __construct(
        protected LoggerInterface $logger,
        private readonly FilesystemOperator $fileSystemPrivate
    ){}

    /**
     * @throws FilesystemException
     */
    public function saveSearchLogSessionToFile(string $sessionId, string $currentSearchRequests): void
    {
        try {

            $this->fileSystemPrivate->delete($sessionId);
            $this->fileSystemPrivate->write($sessionId, $currentSearchRequests);
        } catch (FilesystemException $e) {

            $this->logger->error('Not possible to write search log file', array('mucki','search'));
            $this->logger->error($e->getMessage(), array('mucki','search'));
        }
    }

    /**
     * @throws FilesystemException
     */
    public function saveSearchLogsIntoDb(OutputInterface $cliOutput = null): bool
    {
        $listContents = $this->fileSystemPrivate->listContents('', true)->toArray();
        $cliOutput->write( 'Found '.count($listContents).' log files', true);
        foreach ($listContents as $listContent) {

            /** @var SessionSearchRequest $searchLogs */
            $searchLogs = $this->getSearchLogsFromFiles($listContent->path());

            /** @var SessionSearchRequest $searchLog */
            foreach ($searchLogs as $searchLog) {

                $check = $searchLog;
            }
//            $this->writeSearchLogs($searchLogs);
        }
        return true;
    }

    public function writeSearchLogs(SessionSearchRequest $searchLogs): void
    {
        $logData = array(
            'id' => $searchLogs->getId(),
            'salesChannelId' => $searchLogs->getSalesChannelId(),
            'searchTerm' => $searchLogs->getSearchTerm(),
            'hits' => $searchLogs->getHits()
        );
//        $this->searchRequestLogsRepository->create([$logData], $event->getContext());
//
//        $versionLogId = $this->searchRequestLogsRepository->createVersion($logId, $event->getContext());
//        $versionContext = $event->getContext()->createWithVersionId($versionLogId);
    }

    /**
     * @throws FilesystemException
     */
    public function getSearchLogsFromFiles(string $location): array
    {
        return unserialize($this->fileSystemPrivate->read($location));
    }
}
