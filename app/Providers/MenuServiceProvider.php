<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\MenuItem;

class MenuServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Delay view composer registration until view subsystem is ready
        $this->callAfterResolving('view', function () {
            View::composer('*', function ($view) {
                $menuItems = MenuItem::where('is_active', 1)
                    ->orderBy('sort_order')
                    ->get();

                $view->with('menuItems', $menuItems);
            });
        });
    }
}
