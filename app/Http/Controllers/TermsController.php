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
        $user = auth()->user();

        // Save the agreement in the current browser session
        session(['agreed_to_terms' => true]);

        switch ($user->role) {
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'guest':
                return redirect()->route('guest.dashboard');
            case 'fs_team':
                return redirect()->route('fs.dashboard');
            case 'rpwsis_team':
                return redirect('/rpwsis_team/dashboard');
            case 'cm_team':
                return redirect('/cm_team/dashboard');
            case 'row_team':
                return redirect('/row_team/dashboard');
            case 'pcr_team':
                return redirect('/pcr_team/dashboard');
            case 'pao_team':
                return redirect('/pao_team/dashboard');
            default:
                return redirect('/');
        }
    }

    private function redirectUser()
    {
        $role = auth()->user()->role;

        switch ($role) {
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'guest':
                return redirect()->route('guest.dashboard');
            case 'fs_team':
                return redirect()->route('fs.dashboard');
            case 'rpwsis_team':
                return redirect()->route('rpwsis.dashboard'); // <-- The fix for RP-WSIS!

            // Placeholders for when you build the rest of the teams:
            case 'cm_team':
                return redirect('/cm_team/dashboard');
            case 'row_team':
                return redirect('/row_team/dashboard');
            case 'pcr_team':
                return redirect('/pcr_team/dashboard');
            case 'pao_team':
                return redirect('/pao_team/dashboard');

            // Fallback just in case
            default:
                return redirect('/');
        }
    }
}
