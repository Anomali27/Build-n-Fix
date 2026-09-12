<?php

namespace App\Services;

use App\Repositories\ReviewRepositories;

class ReviewService
{
    public function getAllReviews(): array
    {
        return ReviewRepositories::getAll();
    }
}
