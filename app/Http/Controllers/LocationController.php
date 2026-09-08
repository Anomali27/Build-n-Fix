<?php

namespace App\Http\Controllers;

use App\Services\LocationService;

class LocationController extends Controller
{
    public function __construct(
        protected LocationService $locationService
    ) {}

    public function index()
    {
        $locations = $this->locationService->getLocations();

        return view('locations.index', compact('locations'));
    }
}
