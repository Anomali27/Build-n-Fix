<?php

namespace App\Http\Controllers;

use App\Models\Branch;

class LocationController extends Controller
{
    public function index()
    {
        $locations = Branch::all();

        return view('locations.index', compact('locations'));
    }
}
