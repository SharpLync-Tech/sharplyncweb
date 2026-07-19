<?php

        use Illuminate\Support\Facades\Route;
        use App\Http\Controllers\PageController;
        use App\Http\Controllers\Auth\VerifyController;
        use App\Http\Controllers\SharpFleet\StripeWebhookController;
        

        /*
        |--------------------------------------------------------------------------
        | SharpFleet – Domain Routing
        |--------------------------------------------------------------------------
        */

        // ===============================
        // Country / Marketing Domains
        // ===============================
        Route::domain('sharpfleet.com.au')->group(function () {
            Route::get('/', fn () => redirect('/sharpfleet'));
        });

        Route::domain('sharpfleet.au')->group(function () {
            Route::get('/', fn () => redirect('/sharpfleet'));
        });

        Route::domain('sharpfleet.co.nz')->group(function () {
            Route::get('/', fn () => redirect('/sharpfleet'));
        });

        Route::domain('sharpfleet.co.za')->group(function () {
            Route::get('/', fn () => redirect('/sharpfleet'));
        });

        // ===============================
        // App / Driver Domain
        // ===============================
        Route::domain('sharpfleet.app')->group(function () {
            Route::get('/', fn () => redirect('/app/sharpfleet/mobile'));
        });

        

        Route::get('/', fn() => view('/home'))->name('home'); // Home Page
        // Route::get('/', fn() => view('coming_soon')); // Coming Soon
        // Route::get('/welcome', fn() => view('welcome')); //Sydney
        Route::get('/contact', fn() => view('contact'));
        // Route::get('/style-preview', fn() => view('style-preview'));
        // Route::get('/mobile-preview', fn() => view('mobile-preview'));
        // Route::get('/components', fn() => view('components'));
        Route::redirect('/home', '/', 301);
        // Route::get('/test-threatpulse', fn() => view('test-threatpulse'));
        
        // Policy Pages         
        Route::get('/policies/hub', fn() => view('policies.hub'))->name('policies.hub');  
        Route::get('/policies/terms', fn() => view('policies.terms'));
        Route::get('/policies/sharpfleet-terms', fn() => view('policies.sharpfleet-terms'));
        Route::get('/policies/privacy', fn() => view('policies.privacy')); 
        Route::get('/policies/support', fn() => view('policies.support'));
        Route::get('/policies/security', fn() => view('policies.security'));
        Route::redirect('/policies/remote-support', '/policies/support', 301);

        
        // Vendors
        Route::get('/vendors', function () {
            return view('vendors.vendors');
            })->name('vendors');

          

     
        Route::get('/about', [PageController::class, 'about'])->name('about');
        Route::get('/testimonials', [PageController::class, 'testimonials'])->name('testimonials');

        // Always bind verify to VerifyController
        Route::get('/verify/{token}', [VerifyController::class, 'verify'])->name('verify.email');

        // Trend Micro
        Route::view('/trend-micro', 'trend-micro')->name('trend-micro');        

        Route::post('/app/sharpfleet/stripe/webhook', [StripeWebhookController::class, 'handle']);

        require __DIR__.'/facilities.php';
        require __DIR__.'/admin.php';
        require __DIR__.'/customers.php';
        require __DIR__.'/customer_security.php';
        require __DIR__.'/services.php';
        require __DIR__.'/admin_cms.php';
        require __DIR__.'/sms.php';
        require __DIR__ . '/contact.php';
        require __DIR__ . '/support.php';
        require __DIR__ . '/admintickets.php';
        require __DIR__ . '/ads.php';
        require __DIR__ . '/azureai.php';
        require __DIR__ . '/tools.php';
        require __DIR__ . '/sharpfleet.php';
        require __DIR__.'/marketing.php';
