@extends('layouts.app')

@section('title', '登录 - Calo')

@section('content')
<div class="flex min-h-screen items-center justify-center px-6 py-12">
    <div class="w-full max-w-md">
        <div class="mb-10 text-center">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl text-white text-xl font-bold shadow-lift" style="background: linear-gradient(135deg, #10b981, #047857);">C</div>
            <h1 class="text-3xl font-bold tracking-tight">Calo</h1>
            <p class="mt-2 text-sm text-[var(--calo-muted)]">热量管理，轻松减重</p>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-xl border border-brand-200 dark:border-brand-800/60 bg-brand-50 dark:bg-brand-900/20 px-4 py-3 text-sm text-brand-800 dark:text-brand-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="card p-6">
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-4">
                    <label for="phone" class="mb-1.5 block text-sm font-medium">手机号</label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                        class="input-field"
                        autocomplete="tel"
                        inputmode="numeric"
                        placeholder="请输入手机号" required maxlength="11" pattern="1[3-9]\d{9}">
                    @error('phone')
                        <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="password" class="mb-1.5 block text-sm font-medium">密码</label>
                    <input type="password" id="password" name="password"
                        class="input-field"
                        autocomplete="current-password"
                        placeholder="请输入密码（至少 8 位）" required minlength="8">
                    @error('password')
                        <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn-primary w-full py-3.5 text-[15px]">
                    登录 / 注册
                </button>
            </form>

            <div class="mt-4 text-center">
                <a href="{{ route('password.reset') }}" class="text-sm font-medium text-brand-700 dark:text-brand-400 hover:underline">
                    忘记密码？
                </a>
            </div>
        </div>

        <p class="mt-6 text-center text-xs text-[var(--calo-muted)]">
            首次使用手机号登录将自动创建账号
        </p>
    </div>
</div>
@endsection
