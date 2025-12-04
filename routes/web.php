<?php

        use Illuminate\Support\Facades\Route;
        use App\Http\Controllers\PageController;
        use App\Http\Controllers\Auth\VerifyController;
        use App\Http\Controllers\Admin\LogViewerController;
        

        Route::get('/', fn() => view('/home')); // Home Page
        // Route::get('/', fn() => view('coming_soon')); // Coming Soon
        Route::get('/welcome', fn() => view('welcome')); //Sydney
        Route::get('/contact', fn() => view('contact'));
        Route::get('/style-preview', fn() => view('style-preview'));
        Route::get('/mobile-preview', fn() => view('mobile-preview'));
        Route::get('/components', fn() => view('components'));
        Route::get('/home', fn() => view('home'));
        Route::get('/test-threatpulse', fn() => view('test-threatpulse'));
        
        // Policy Pages        
        Route::get('/policies/terms', fn() => view('policies.terms'));
        Route::get('/policies/privacy', fn() => view('policies.privacy'));

        

        Route::get('/about', [PageController::class, 'about'])->name('about');
        Route::get('/testimonials', [PageController::class, 'testimonials'])->name('testimonials');

        // Always bind verify to VerifyController
        Route::get('/verify/{token}', [VerifyController::class, 'verify'])->name('verify.email');

        // Log Test - Remove in Prod
        Route::get('/admin/registration-log', [LogViewerController::class, 'index'])->name('admin.registration.log');
        Route::post('/admin/registration-log/clear', [LogViewerController::class, 'clear'])->name('admin.registration.log.clear');

        // Trend Micro
        Route::view('/trend-micro', 'trend-micro')->name('trend-micro');        

        // DB Content Testing Routes
        use App\Models\CMS\Service;
        Route::get('/test-services', function () {
            return Service::all();    
        });

        use App\Models\CMS\MenuItem;
        Route::get('/test-menu', function () {
            return MenuItem::all();
        });

        use App\Models\CMS\Page;
        Route::get('/test-page', function () {
            return Page::all();
        });

        use App\Models\CMS\FooterLink;
        Route::get('/test-footer', function () {
            return FooterLink::all();
        });

        use App\Models\CMS\AboutSection;
        Route::get('/test-about-section', function () {
            return AboutSection::all();
        });

        use App\Models\CMS\AboutTimelineItem;
        Route::get('/test-timeline', function () {
            return AboutTimelineItem::all();
        });

        use App\Models\CMS\AboutValue;
        Route::get('/test-about-values', function () {
            return AboutValue::all();
        });

        use App\Models\CMS\ContactInfo;
        Route::get('/test-contact', function () {
            return ContactInfo::all();
        });

        use App\Models\CMS\SeoMeta;
        Route::get('/test-seo', function () {
            return SeoMeta::all();
        });

        use App\Models\CMS\Post;
        use App\Models\CMS\PostCategory;
        Route::get('/test-posts', function () {
            return Post::all();
        });

        Route::get('/test-post-categories', function () {
            return PostCategory::all();
        });

        use App\Models\CMS\KnowledgeBaseCategory;
        use App\Models\CMS\KnowledgeBaseArticle;
        Route::get('/test-kb-categories', fn() => KnowledgeBaseCategory::all());
        Route::get('/test-kb-articles', fn() => KnowledgeBaseArticle::all());

        Route::get('/email-preview', function () {
            return view('emails.preview');
        });

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
