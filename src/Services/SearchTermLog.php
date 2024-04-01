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
use Symfony\Component\Console\Output\OutputInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\WriteTypeIntendException;
use Shopware\Core\Framework\Uuid\Uuid;

use MuckiSearchPlugin\Entities\SessionSearchRequest;
use MuckiSearchPlugin\Services\CliOutput as ServicesCliOutput;
use MuckiSearchPlugin\Services\Session as ServicesSession;

class SearchTermLog
{
    public function __construct(
        protected LoggerInterface $logger,
        private readonly FilesystemOperator $fileSystemPrivate,
        protected EntityRepository $searchRequestLogsRepository,
        protected ServicesCliOutput $servicesCliOutput,
        protected ServicesSession $servicesSession
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
        $this->servicesCliOutput->printCliOutputNewline($cliOutput, 'Found '.count($listContents).' log files');

        $writeData = array();

        foreach ($listContents as $listContent) {

            /** @var SessionSearchRequest $searchLog */
            foreach ($this->getSearchLogsFromFiles($listContent->path()) as $searchLog) {
                $writeData[] = $searchLog->toArray();
            }

            $this->servicesCliOutput->printCliOutputNewline($cliOutput,'Remove search logs session file');
            $this->removeSearchLogsSessionFile($listContent->path());
            $this->servicesSession->cleanSearchRequestsSession();
        }

        $this->servicesCliOutput->printCliOutputNewline($cliOutput,'Remove search logs session file');

        try {
            $this->searchRequestLogsRepository->create($writeData, Context::createDefaultContext());
        } catch (WriteTypeIntendException $exception) {

            $this->logger->error('Problems to write new search log item', array('mucki','search'));
            $this->logger->error($exception->getMessage(), array('mucki','search'));
            $this->logger->error($exception->getTraceAsString(), array('mucki','search'));
        }
        return true;
    }

    public function getSearchLogsFromFiles(string $location): array
    {
        if($location !== '' && Uuid::isValid($location)) {

            try {
                return unserialize($this->fileSystemPrivate->read($location));
            } catch (FilesystemException $e) {
                $this->logger->error('Not possible to read session log file '.$location, array('mucki','search'));
                $this->logger->error($e->getMessage(), array('mucki','search'));
            }
        }
        return array();
    }

    public function removeSearchLogsSessionFile(string $location): array
    {
        if($location !== '' && Uuid::isValid($location)) {

            try {
                $this->fileSystemPrivate->delete($location);
            } catch (FilesystemException $e) {
                $this->logger->error('Not possible to remove session log file '.$location, array('mucki','search'));
                $this->logger->error($e->getMessage(), array('mucki','search'));
            }
        }
        return array();
    }
}
