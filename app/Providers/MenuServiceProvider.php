<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Request;
use App\Models\MenuItem;

class MenuServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // 🚧 Skip menu loading for SharpFleet routes
        if (Request::is('app/sharpfleet*')) {
            return;
        }

        $this->callAfterResolving('view', function () {
            View::composer('*', function ($view) {
                try {
                    $menuItems = MenuItem::where('is_active', 1)
                        ->orderBy('sort_order')
                        ->get();

                    $view->with('menuItems', $menuItems);
                } catch (\Throwable $e) {
                    // Fail silently so the site never dies
                    $view->with('menuItems', collect());
                }
            });
        });
    }
}
