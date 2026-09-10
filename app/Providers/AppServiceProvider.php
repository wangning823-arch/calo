<?php

namespace App\Providers;

use App\Http\Responses\LogoutResponse;
use Carbon\Carbon;
use Filament\Auth\Http\Responses\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Livewire\Mechanisms\PersistentMiddleware\PersistentMiddleware;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(LogoutResponseContract::class, LogoutResponse::class);
    }

    public function boot(): void
    {
        Carbon::setLocale(config('app.locale', 'zh_CN'));

        // Filament 的 originalRequest 依赖 Livewire 快照中的 path/method。
        // wire:navigate 整页导航时尚未写入，伪造请求会变成对 `/` 的非法方法匹配，
        // 进而抛出 MethodNotAllowedHttpException。这里做安全降级。
        $this->app->booted(function (): void {
            $this->app->scoped('originalRequest', function () {
                if (! Livewire::isLivewireRequest()) {
                    return request();
                }

                try {
                    $persistentMiddleware = app(PersistentMiddleware::class);
                    $path = invade($persistentMiddleware)->path ?? null;
                    $method = invade($persistentMiddleware)->method ?? null;

                    if (blank($path) || blank($method)) {
                        return request();
                    }

                    $request = invade($persistentMiddleware)->makeFakeRequest();
                    invade($persistentMiddleware)->getRouteFromRequest($request);

                    return $request;
                } catch (MethodNotAllowedHttpException | NotFoundHttpException) {
                    return request();
                }
            });
        });
    }
}
