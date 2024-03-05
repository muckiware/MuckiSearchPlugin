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

use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Session\SessionFactory;

use MuckiSearchPlugin\Core\Defaults as PluginDefaults;
use MuckiSearchPlugin\Entities\SessionSearchRequest;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Session
{
    public function __construct(
        private readonly SessionFactory $sessionFactory
    ){}

    public function getLastSearchRequestDateTime(int $requestTimeStamp): \DateTime
    {
        $returnDateTime = new \DateTime();
        if($requestTimeStamp >= 1) {

            $session = $this->sessionFactory->createSession();
            if($session->has(PluginDefaults::DEFAULT_SESSION_FIELD_LAST_SEARCH_REQUEST)) {
                $lastSearchRequestTimestamp = $session->get(PluginDefaults::DEFAULT_SESSION_FIELD_LAST_SEARCH_REQUEST);
                $session->set(
                    PluginDefaults::DEFAULT_SESSION_FIELD_LAST_SEARCH_REQUEST,
                    $requestTimeStamp
                );

                $returnDateTime->setTimestamp($lastSearchRequestTimestamp);
            }

            $returnDateTime->setTimestamp($requestTimeStamp);
        }

        return $returnDateTime;
    }

    public function setSearchTerm(string $searchTerm, string $salesChannelId, int $searchTotals): void
    {
        if($searchTerm === '') {
            return;
        }

        $session = $this->sessionFactory->createSession();
        if($session->has(PluginDefaults::DEFAULT_SESSION_FIELD_SEARCH_REQUESTS)) {

            $currentUserRequests = $this->getCurrentSearchRequests($session);
            $similarItemIndex = -1;

            /** @var SessionSearchRequest $currentUserRequest */
            foreach ($currentUserRequests as $currentUserRequestKey => $currentUserRequest) {

                \similar_text($currentUserRequest->getSearchTerm(), $searchTerm, $percent);
                if($percent >= 98.5 && (strlen($searchTerm) > strlen($currentUserRequest->getSearchTerm()))) {
                    $similarItemIndex = $currentUserRequestKey;
                }
            }

            if($similarItemIndex >= 0) {
                $currentUserRequests[$similarItemIndex]->setSearchTerm($searchTerm);
            } else {

                $currentUserRequests[] = $this->createNewLogSessionItem(
                    $searchTerm,
                    $session,
                    $salesChannelId,
                    $searchTotals
                );
            }

            $session->set(
                PluginDefaults::DEFAULT_SESSION_FIELD_SEARCH_REQUESTS,
                serialize($currentUserRequests)
            );

        } else {

            $session->set(
                PluginDefaults::DEFAULT_SESSION_FIELD_SEARCH_REQUESTS,
                serialize(array($this->createNewLogSessionItem($searchTerm, $session, $salesChannelId, $searchTotals)))
            );
        }
    }

    public function createNewLogSessionItem(
        string $searchTerm,
        SessionInterface $session,
        string $salesChannelId,
        int $searchTotals
    ): SessionSearchRequest
    {
        $sessionSearchRequests = new SessionSearchRequest();
        $sessionSearchRequests->setId(Uuid::randomHex());
        $sessionSearchRequests->setSearchTerm($searchTerm);
        $sessionSearchRequests->setSessionId($session->getId());
        $sessionSearchRequests->setSalesChannelId($salesChannelId);
        $sessionSearchRequests->setHits($searchTotals);

        return $sessionSearchRequests;
    }

    public function getCurrentSearchRequests(SessionInterface $session = null): array
    {
        if(!$session) {
            $session = $this->sessionFactory->createSession();
        }

        $currentUserRequests = unserialize(
            $session->get(PluginDefaults::DEFAULT_SESSION_FIELD_SEARCH_REQUESTS)
        );
        if(is_array($currentUserRequests) && !empty($currentUserRequests)) {
            return $currentUserRequests;
        }

        return array();
    }

    public function getCurrentSerializedSearchRequests(SessionInterface $session = null): string
    {
        if(!$session) {
            $session = $this->sessionFactory->createSession();
        }
        return $session->get(PluginDefaults::DEFAULT_SESSION_FIELD_SEARCH_REQUESTS);
    }

    public function getSessionId(SessionInterface $session = null): string
    {
        if(!$session) {
            $session = $this->sessionFactory->createSession();
        }
        return $session->getId();
    }
}
