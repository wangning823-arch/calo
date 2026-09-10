<?php

namespace App\Http\Responses;

use Filament\Auth\Http\Responses\Contracts\LogoutResponse as Contract;
use Illuminate\Http\RedirectResponse;

class LogoutResponse implements Contract
{
    public function toResponse($request): RedirectResponse
    {
        return redirect()->route('login');
    }
}
