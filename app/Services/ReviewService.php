<?php

namespace App\Services;

use App\Data\ReviewData;

class ReviewService
{
    public function getAllReviews(): array
    {
        return ReviewData::getAll();
    }
}
