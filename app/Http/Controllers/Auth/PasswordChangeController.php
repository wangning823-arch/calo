<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PasswordChangeController extends Controller
{
    public function showChangeForm()
    {
        return view('auth.password-change');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required'],
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => '当前密码不正确。',
            ]);
        }

        $user->update([
            'password' => $request->password,
        ]);

        // Invalidate all sessions except current
        $currentSessionId = $request->session()->getId();
        DB::table('sessions')->where('user_id', $user->id)->where('id', '!=', $currentSessionId)->delete();

        AuditLog::create([
            'user_id' => $user->id,
            'action_type' => 'password_changed',
            'action_details' => ['phone' => $user->phone],
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);

        return back()->with('success', '密码已修改。');
    }
}
