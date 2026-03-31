<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Downloadable;
use App\Models\IaResolution;

class GuestController extends Controller
{
    public function index()
    {
        // Pull EVERYTHING from the database
        $downloadables = Downloadable::latest()->get();
        $resolutions = IaResolution::latest()->get();

        return view('guest.dashboard', compact('downloadables', 'resolutions'));
    }
}