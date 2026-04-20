<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RpwsisAccomplishment;

class RpwsisAccomplishmentController extends Controller
{
        public function store(Request $request)
    {
        $record = RpwsisAccomplishment::create($request->all());
        return response()->json($record);
    }

    public function index()
    {
        $records = RpwsisAccomplishment::latest()->get();
        return view('your-view-name', compact('records'));
    }
}
