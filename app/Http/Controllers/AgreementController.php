<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AgreementController extends Controller
{
    public function show()
    {
        return view('auth.agreement');
    }

    public function accept(Request $request)
    {
        $request->user()->update(['agreed_at' => now()]);

        return redirect()->intended(route('dashboard'));
    }
}
