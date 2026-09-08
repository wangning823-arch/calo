@extends('layouts.app')

@section('title', '登录 - Calo')

@section('content')
<div class="flex flex-col items-center justify-center min-h-screen px-6 py-12">
    <div class="w-full max-w-sm">
        <h1 class="text-2xl font-bold text-center mb-8 dark:text-white">Calo</h1>
        <p class="text-center text-gray-500 dark:text-gray-400 mb-8">热量管理，轻松减重</p>

        @if (session('success'))
            <div class="mb-4 p-3 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-4">
                <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">手机号</label>
                <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition bg-white dark:bg-gray-700 dark:text-white"
                    placeholder="请输入手机号" required maxlength="11" pattern="1[3-9]\d{9}">
                @error('phone')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">密码</label>
                <input type="password" id="password" name="password"
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition bg-white dark:bg-gray-700 dark:text-white"
                    placeholder="请输入密码（至少8位）" required minlength="8">
                @error('password')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                class="w-full py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 active:bg-blue-800 transition">
                登录 / 注册
            </button>
        </form>

        <div class="mt-4 text-center">
            <a href="{{ route('password.reset') }}" class="text-sm text-green-600 hover:text-green-700">
                忘记密码？
            </a>
        </div>

        <p class="mt-8 text-xs text-gray-400 dark:text-gray-500 text-center">
            首次使用手机号登录将自动创建账号
        </p>
    </div>
</div>
@endsection
