<?php

use Illuminate\Support\Facades\Route;

Route::get('/clear', function () {
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
});

// Cron job trigger route (web-based)
Route::get('/cron', function () {
    // Set cache indicator for cron detection
    \Illuminate\Support\Facades\Cache::put('schedule:run:last', now()->toIso8601String(), now()->addMinutes(10));
    
    // Run the scheduler
    \Illuminate\Support\Facades\Artisan::call('schedule:run');
    
    return response()->json([
        'success' => true,
        'message' => 'Scheduler executed successfully',
        'timestamp' => now()->toIso8601String()
    ]);
})->name('cron.trigger');


// User Support Ticket
Route::controller('TicketController')->prefix('ticket')->name('ticket.')->group(function () {
    Route::get('/', 'supportTicket')->name('index');
    Route::get('new', 'openSupportTicket')->name('open');
    Route::post('create', 'storeSupportTicket')->name('store');
    Route::get('view/{ticket}', 'viewTicket')->name('view');
    Route::post('reply/{id}', 'replyTicket')->name('reply');
    Route::post('close/{id}', 'closeTicket')->name('close');
    Route::get('download/{attachment_id}', 'ticketDownload')->name('download');
});


// Marketplace Public Routes
Route::controller('MarketplaceController')->name('marketplace.')->prefix('marketplace')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('browse', 'browse')->name('browse');
    Route::get('auctions', 'auctions')->name('auctions');
    Route::get('listing/{slug}', 'show')->name('listing.show');
    Route::post('listing/{id}/question', 'askQuestion')->name('listing.question');
    Route::get('seller/{username}', 'sellerProfile')->name('seller');
    Route::get('category/{slug}', 'category')->name('category');
    Route::get('type/{type}', 'businessType')->name('type');
});

// NDA Routes
Route::controller('NdaController')->name('marketplace.nda.')->prefix('marketplace/nda')->group(function () {
    Route::get('listing/{listingId}', 'show')->name('show');
    Route::post('sign/{listingId}', 'sign')->name('sign')->middleware('auth');
    Route::get('download/{id}', 'download')->name('download')->middleware('auth');
});

Route::controller('SiteController')->group(function () {
    Route::get('/contact', 'contact')->name('contact');
    Route::post('/contact', 'contactSubmit');
    Route::get('/change/{lang?}', 'changeLanguage')->name('lang');

    Route::get('cookie-policy', 'cookiePolicy')->name('cookie.policy');

    Route::get('/cookie/accept', 'cookieAccept')->name('cookie.accept');

    Route::get('blogs', 'blogs')->name('blogs');

    Route::get('blog/{slug}', 'blogDetails')->name('blog.details');

    Route::get('policy/{slug}', 'policyPages')->name('policy.pages');

    Route::post('subscribe', 'subscribe')->name('subscribe');

    Route::get('placeholder-image/{size}', 'placeholderImage')->withoutMiddleware('maintenance')->name('placeholder.image');
    Route::get('maintenance-mode', 'maintenance')->withoutMiddleware('maintenance')->name('maintenance');

    Route::get('/{slug}', 'pages')->name('pages');
    Route::get('/', 'index')->name('home');
});
