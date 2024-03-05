<?php declare(strict_types=1);
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
namespace MuckiSearchPlugin\Entities;

class SessionSearchRequest
{
    /**
     * UUID for a search log
     * @var string
     */
    protected string $id;

    protected string $searchTerm;

    protected string $sessionId;

    protected string $languageId;

    protected string $salesChannelId;

    protected int $hits;

    protected \DateTime $requestDateTime;

    protected string $userAgent;
    protected string $device;
    protected string $platform;
    protected string $platformVersion;
    protected string $browser;
    protected string $browserVersion;
    protected bool $isMobile;
    protected bool $isDesktop;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getSearchTerm(): string
    {
        return $this->searchTerm;
    }

    public function setSearchTerm(string $searchTerm): void
    {
        $this->searchTerm = $searchTerm;
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function setSessionId(string $sessionId): void
    {
        $this->sessionId = $sessionId;
    }

    public function getLanguageId(): string
    {
        return $this->languageId;
    }

    public function setLanguageId(string $languageId): void
    {
        $this->languageId = $languageId;
    }

    public function getSalesChannelId(): string
    {
        return $this->salesChannelId;
    }

    public function setSalesChannelId(string $salesChannelId): void
    {
        $this->salesChannelId = $salesChannelId;
    }

    public function getHits(): int
    {
        return $this->hits;
    }

    public function setHits(int $hits): void
    {
        $this->hits = $hits;
    }

    public function getRequestDateTime(): \DateTime
    {
        return $this->requestDateTime;
    }

    public function setRequestDateTime(\DateTime $requestDateTime): void
    {
        $this->requestDateTime = $requestDateTime;
    }

    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    public function setUserAgent(string $userAgent): void
    {
        $this->userAgent = $userAgent;
    }

    public function getDevice(): string
    {
        return $this->device;
    }

    public function setDevice(string $device): void
    {
        $this->device = $device;
    }

    public function getPlatform(): string
    {
        return $this->platform;
    }

    public function setPlatform(string $platform): void
    {
        $this->platform = $platform;
    }

    public function getPlatformVersion(): string
    {
        return $this->platformVersion;
    }

    public function setPlatformVersion(string $platformVersion): void
    {
        $this->platformVersion = $platformVersion;
    }

    public function getBrowser(): string
    {
        return $this->browser;
    }

    public function setBrowser(string $browser): void
    {
        $this->browser = $browser;
    }

    public function getBrowserVersion(): string
    {
        return $this->browserVersion;
    }

    public function setBrowserVersion(string $browserVersion): void
    {
        $this->browserVersion = $browserVersion;
    }

    public function isMobile(): bool
    {
        return $this->isMobile;
    }

    public function setIsMobile(bool $isMobile): void
    {
        $this->isMobile = $isMobile;
    }

    public function isDesktop(): bool
    {
        return $this->isDesktop;
    }

    public function setIsDesktop(bool $isDesktop): void
    {
        $this->isDesktop = $isDesktop;
    }

    public function toSerialize(): string
    {
        return serialize(get_object_vars($this));
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
