<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

trait HandlesAsyncRequests
{
    protected function respondsWithJson(Request $request): bool
    {
        return $request->expectsJson() || $request->ajax() || $request->wantsJson() || $request->isJson();
    }

    protected function successResponse(Request $request, string $message, array $data = [], int $status = 200)
    {
        if ($this->respondsWithJson($request)) {
            return response()->json(array_merge([
                'success' => true,
                'message' => $message,
            ], $data), $status);
        }

        return back()->with('success', $message);
    }

    protected function errorResponse(Request $request, string $message, array $data = [], int $status = 422)
    {
        if ($this->respondsWithJson($request)) {
            return response()->json(array_merge([
                'success' => false,
                'message' => $message,
            ], $data), $status);
        }

        return back()->with('error', $message);
    }
}
