<?php

namespace App\Services;

use App\Repositories\BranchRepository;

class BranchService
{
    public function getAllBranches(): array
    {
        return BranchRepository::getAll();
    }
}
