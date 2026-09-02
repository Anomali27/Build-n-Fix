<?php

namespace App\Services;

use App\Data\BranchData;

class BranchService
{
    public function getAllBranches(): array
    {
        return BranchData::getAll();
    }
}
