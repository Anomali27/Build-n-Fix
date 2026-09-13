<?php

namespace App\Services;

use App\Repositories\ReviewRepository;

class ReviewService
{
    public function getAllReviews(): array
    {
        return ReviewRepository::getAll();
    }
}
