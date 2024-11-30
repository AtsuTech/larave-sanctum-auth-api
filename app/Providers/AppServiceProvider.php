<?php

namespace App\Providers;

//use Illuminate\Support\ServiceProvider;//消す

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;//add
use App\Models\User;//add
use Illuminate\Auth\Notifications\ResetPassword;//add
use Illuminate\Support\Facades\Request;// 現在のドメイン部分を取得するためReque
use Illuminate\Support\Facades\Gate;//add

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        $this->registerPolicies();
        ResetPassword::createUrlUsing(function (User $user, string $token) {
        $currentUrl = Request::root(); // 現在のドメイン部分を取得
            return $currentUrl . '/password/reset?token=' . $token . '&email=' . $user->email;         
        });
    }
}
