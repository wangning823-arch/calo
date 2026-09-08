<?php

namespace App\Http\Controllers;

use App\Services\AccountService;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function __construct(
        private AccountService $accountService,
    ) {}

    public function showCancellationForm(Request $request)
    {
        $user = $request->user();
        $isPending = $user->isCancellationPending();

        return view('settings.cancellation', compact('user', 'isPending'));
    }

    public function requestCancellation(Request $request)
    {
        $this->accountService->requestCancellation($request->user());

        return redirect()->route('dashboard')
            ->with('success', '账号注销已申请，7天冷静期后将执行注销。您可在冷静期内撤销注销。');
    }

    public function cancelCancellation(Request $request)
    {
        $this->accountService->cancelCancellation($request->user());

        return redirect()->route('dashboard')
            ->with('success', '注销已撤销，您的账号已恢复正常。');
    }

    public function executeCancellation(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (! $this->accountService->validatePassword($user, $request->password)) {
            return back()->withErrors(['password' => '密码不正确。']);
        }

        $this->accountService->executeCancellation($user);

        auth()->logout();

        return redirect()->route('login')
            ->with('success', '账号已注销，您的数据已全部删除。');
    }
}
