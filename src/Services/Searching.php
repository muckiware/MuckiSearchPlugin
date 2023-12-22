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

use MuckiSearchPlugin\Services\Settings;
use MuckiSearchPlugin\Search\SearchClientInterface;

class Searching
{
    public function __construct(
        protected Settings $settings,
        protected SearchClientInterface $searchClient
    ){}
    
    public function getServerInfo()
    {
        $this->searchClient->getInfoAsString();
    }

    protected function getTypeOfServer()
    {

    }
}
