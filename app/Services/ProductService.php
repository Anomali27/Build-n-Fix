<?php

namespace App\Services;

use App\Data\ProductData;

class ProductService
{
    public function getFeaturedProducts(): array
    {
        return ProductData::getFeatured();
    }
}
