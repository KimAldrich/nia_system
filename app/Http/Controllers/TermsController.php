<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TermsController extends Controller
{
    public function show()
    {
        // If they already agreed during this session, send them to their dashboard
        if (session('agreed_to_terms')) {
            return $this->redirectUser();
        }

        return view('terms.show');
    }

    public function agree(Request $request)
    {
        // Save the agreement in the current browser session
        session(['agreed_to_terms' => true]);

        return $this->redirectUser();
    }

    private function redirectUser()
    {
        $role = auth()->user()->role;

        if ($role === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($role === 'fs_team') {
            return redirect()->route('fs.dashboard');
        }

        return redirect('/');
    }
}