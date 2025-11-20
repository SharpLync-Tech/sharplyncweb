<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\CMS\MenuItem;
use Illuminate\Support\Facades\View;

class MenuServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Share menu items with ALL frontend views
        View::composer('*', function ($view) {
            $view->with('menuItems',
                MenuItem::where('is_active', true)
                    ->orderBy('sort_order', 'asc')
                    ->get()
            );
        });
    }
}
