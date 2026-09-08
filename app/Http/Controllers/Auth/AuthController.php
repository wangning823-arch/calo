<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'string', 'regex:/^1[3-9]\d{9}$/'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $phone = $request->phone;
        $lockoutKey = "login_lockout:{$phone}";

        if (Cache::has($lockoutKey)) {
            return back()->withErrors([
                'phone' => '账号已锁定，请10分钟后重试。',
            ])->onlyInput('phone');
        }

        $user = User::where('phone', $phone)->first();

        // Auto-register if phone not found (FR-1.1: 未注册手机号首次登录自动创建账号)
        if (! $user) {
            $user = User::create([
                'name' => $phone,
                'phone' => $phone,
                'password' => $request->password,
            ]);

            UserPreference::create(['user_id' => $user->id]);

            AuditLog::create([
                'user_id' => $user->id,
                'action_type' => 'register',
                'action_details' => ['phone' => $phone],
                'ip_address' => $request->ip(),
                'created_at' => now(),
            ]);
        }

        if (! $user || ! Hash::check($request->password, $user->password)) {
            $attemptsKey = "login_attempts:{$phone}";
            $attempts = (int) Cache::get($attemptsKey, 0) + 1;

            if ($attempts >= 5) {
                Cache::put($lockoutKey, true, now()->addMinutes(10));
                Cache::forget($attemptsKey);

                AuditLog::create([
                    'user_id' => $user?->id,
                    'action_type' => 'login_locked',
                    'action_details' => ['phone' => $phone, 'attempts' => $attempts],
                    'ip_address' => $request->ip(),
                    'created_at' => now(),
                ]);

                return back()->withErrors([
                    'phone' => '连续输错5次，账号已锁定10分钟。',
                ])->onlyInput('phone');
            }

            Cache::put($attemptsKey, $attempts, now()->addMinutes(15));

            return back()->withErrors([
                'password' => '手机号或密码错误。',
            ])->onlyInput('phone');
        }

        if ($user->isCancelled()) {
            return back()->withErrors([
                'phone' => '该账号已注销。',
            ])->onlyInput('phone');
        }

        Cache::forget("login_attempts:{$phone}");

        $request->session()->regenerate();
        auth()->login($user, true);

        AuditLog::create([
            'user_id' => $user->id,
            'action_type' => 'login',
            'action_details' => ['phone' => $phone],
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        AuditLog::create([
            'user_id' => $user->id,
            'action_type' => 'logout',
            'action_details' => ['phone' => $user->phone],
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);

        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
