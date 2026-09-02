<?php

namespace App\Services;

use App\Data\CategoryData;

class CategoryService
{
    public function getAllCategories(): array
    {
        return CategoryData::getAll();
    }
}
