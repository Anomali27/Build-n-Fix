<?php

namespace App\Services;

use App\Repositories\BranchRepositories;

class BranchService
{
    public function getAllBranches(): array
    {
        return BranchRepositories::getAll();
    }
}
