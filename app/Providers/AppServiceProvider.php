<?php

namespace App\Providers;

use App\Models\Task;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

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
        // Tugas hanya boleh diakses oleh pemiliknya (mahasiswa yang login).
        Route::bind('task', function (string $value) {
            return Task::query()
                ->whereKey($value)
                ->where('user_id', auth()->id())
                ->firstOrFail();
        });
    }
}
