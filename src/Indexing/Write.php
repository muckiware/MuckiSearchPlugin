<?php

namespace MuckiSearchPlugin\Indexing;

use Psr\Log\LoggerInterface;
use MuckiSearchPlugin\Services\Content\Products as Products;

class Write
{
    public function __construct(
        protected LoggerInterface  $logger,
        protected Products $products
    )
    {
    }

    public function doIndexing()
    {
        $allActiveProduct = $this->products->getAllActiveProduct();

        if ($allActiveProduct->count() >= 1) {

            $checker = true;
        }
    }
}