<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RpwsisAccomplishment;

class RpwsisAccomplishmentController extends Controller
{
        public function store(Request $request)
    {
        $validated = $request->validate([
            'region' => ['required', 'string', 'max:100'],
            'batch' => ['nullable', 'string', 'max:100'],
            'allocation' => ['nullable', 'string', 'max:255'],
            'nis' => ['nullable', 'string', 'max:255'],
            'activity' => ['required', 'string', 'max:255'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'phy' => ['nullable', 'numeric', 'between:0,100'],
            'fin' => ['nullable', 'numeric', 'between:0,100'],
            'exp' => ['nullable', 'numeric', 'min:0'],
        ] + collect(range(1, 12))->mapWithKeys(fn ($index) => [
            'c' . $index => ['nullable', 'string', 'max:255'],
        ])->toArray());

        $record = RpwsisAccomplishment::create($validated);
        return response()->json([
            'success' => true,
            'message' => 'Accomplishment record saved successfully.',
            'record' => $record,
        ]);
    }

    public function index()
    {
        $records = RpwsisAccomplishment::latest()->get();
        return view('your-view-name', compact('records'));
    }
}
