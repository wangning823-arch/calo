<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function showResetForm()
    {
        return view('auth.password-reset');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'string', 'regex:/^1[3-9]\d{9}$/'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required'],
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (! $user) {
            return back()->withErrors([
                'phone' => '该手机号未注册。',
            ])->onlyInput('phone');
        }

        $user->update([
            'password' => $request->password,
            'password_reset_token' => null,
            'password_reset_expires_at' => null,
        ]);

        // Invalidate all sessions for this user
        DB::table('sessions')->where('user_id', $user->id)->delete();

        AuditLog::create([
            'user_id' => $user->id,
            'action_type' => 'password_reset',
            'action_details' => ['phone' => $user->phone],
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);

        return redirect()->route('login')->with('success', '密码已重置，请重新登录。');
    }
}
