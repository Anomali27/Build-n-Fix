<?php

namespace App\Services;

use App\Repositories\BranchRepositories;

class LocationService
{
    /**
     * Retrieve all branch locations.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getLocations(): array
    {
        return BranchRepositories::getAll();
    }
}
