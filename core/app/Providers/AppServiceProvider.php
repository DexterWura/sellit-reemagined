<?php

namespace App\Providers;

use App\Constants\Status;
use App\Lib\Searchable;
use App\Models\AdminNotification;
use App\Models\Deposit;
use App\Models\Escrow;
use App\Models\Frontend;
use App\Models\Listing;
use App\Models\Offer;
use App\Models\Review;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Builder::mixin(new Searchable);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Don't redirect if we're already on the install page
        try {
            $request = request();
            if ($request && !$request->is('install*')) {
                $isInstalled = false;
                
                // Try Laravel cache first
                try {
                    $isInstalled = cache()->get('SystemInstalled');
                } catch (\Exception $e) {
                    // Cache might not be working, try alternative method
                }
                
                // If not found in cache, check file-based alternative
                if (!$isInstalled) {
                    // Check both possible locations (relative to core and absolute)
                    $cacheFile1 = base_path('storage/framework/cache/data/SystemInstalled');
                    $cacheFile2 = dirname(base_path()) . '/core/storage/framework/cache/data/SystemInstalled';
                    
                    $cacheFile = file_exists($cacheFile1) ? $cacheFile1 : (file_exists($cacheFile2) ? $cacheFile2 : null);
                    
                    if ($cacheFile && file_exists($cacheFile)) {
                        $cacheData = @unserialize(file_get_contents($cacheFile));
                        if (is_array($cacheData) && isset($cacheData['installed']) && $cacheData['installed']) {
                            $isInstalled = true;
                            // Try to set it in Laravel cache for future use
                            try {
                                cache()->put('SystemInstalled', true, now()->addYears(10));
                            } catch (\Exception $e) {
                                // Ignore if cache still doesn't work
                            }
                        }
                    }
                }
                
                if (!$isInstalled) {
                    // .env is in the parent directory of core
                    $envFilePath = dirname(base_path()) . '/.env';
                    if (!file_exists($envFilePath)) {
                        header('Location: /install');
                        exit;
                    }
                    $envContents = @file_get_contents($envFilePath);
                    if (empty(trim($envContents))) {
                        header('Location: /install');
                        exit;
                    } else {
                        // .env exists and has content, assume installed
                        // Don't try to set cache here to avoid errors
                        // Just let it through
                    }
                }
            }
        } catch (\Exception $e) {
            // If anything fails in the installation check, just continue
            // Better to show the site with potential errors than redirect loop
        }

        // Only proceed with view composers if view service is available
        if (!$this->app->bound('view')) {
            return; // View service not ready yet, skip view operations
        }
        
        // Only proceed with view composers if database is ready
        try {
            // Check if database connection is available
            try {
                DB::connection()->getPdo();
            } catch (\Exception $e) {
                // Database not ready, use fallbacks
                $viewShare['activeTemplate'] = 'templates.basic.';
                $viewShare['activeTemplateTrue'] = 'assets/templates/basic/';
                $viewShare['emptyMessage'] = 'Data not found';
                $this->app->make('view')->share($viewShare);
                return;
            }
            
            // Try to get active template, with fallback
            try {
                $activeTemplate = activeTemplate();
                $activeTemplateTrue = activeTemplate(true);
            } catch (\Exception $e) {
                // Fallback if activeTemplate fails
                $activeTemplate = 'templates.basic.';
                $activeTemplateTrue = 'assets/templates/basic/';
            }
            
            $viewShare['activeTemplate'] = $activeTemplate;
            $viewShare['activeTemplateTrue'] = $activeTemplateTrue;
            $viewShare['emptyMessage'] = 'Data not found';
            $this->app->make('view')->share($viewShare);


            $this->app->make('view')->composer('admin.partials.sidenav', function ($view) {
                try {
                    $view->with([
                        'bannedUsersCount'           => User::banned()->count(),
                        'emailUnverifiedUsersCount' => User::emailUnverified()->count(),
                        'mobileUnverifiedUsersCount'   => User::mobileUnverified()->count(),
                        'kycUnverifiedUsersCount'   => User::kycUnverified()->count(),
                        'kycPendingUsersCount'   => User::kycPending()->count(),
                        'pendingTicketCount'         => SupportTicket::whereIN('status', [Status::TICKET_OPEN, Status::TICKET_REPLY])->count(),
                        'pendingDepositsCount'    => Deposit::pending()->count(),
                        'pendingWithdrawCount'    => Withdrawal::pending()->count(),
                        'disputedEscrowCount'        => Escrow::disputed()->count(),
                        'pendingListingsCount'    => Listing::where('status', Status::LISTING_PENDING)->count(),
                        'pendingOffersCount'      => Offer::where('status', Status::OFFER_PENDING)->count(),
                        'pendingReviewsCount'     => Review::where('status', Status::REVIEW_PENDING)->count(),
                        'updateAvailable'    => false, // Update functionality disabled
                    ]);
                } catch (\Exception $e) {
                    // Database might not be ready, use defaults
                    $view->with([
                        'bannedUsersCount' => 0,
                        'emailUnverifiedUsersCount' => 0,
                        'mobileUnverifiedUsersCount' => 0,
                        'kycUnverifiedUsersCount' => 0,
                        'kycPendingUsersCount' => 0,
                        'pendingTicketCount' => 0,
                        'pendingDepositsCount' => 0,
                        'pendingWithdrawCount' => 0,
                        'disputedEscrowCount' => 0,
                        'pendingListingsCount' => 0,
                        'pendingOffersCount' => 0,
                        'pendingReviewsCount' => 0,
                        'updateAvailable' => false,
                    ]);
                }
            });

            $this->app->make('view')->composer('admin.partials.topnav', function ($view) {
                try {
                    $view->with([
                        'adminNotifications' => AdminNotification::where('is_read', Status::NO)->with('user')->orderBy('id', 'desc')->take(10)->get(),
                        'adminNotificationCount' => AdminNotification::where('is_read', Status::NO)->count(),
                    ]);
                } catch (\Exception $e) {
                    $view->with([
                        'adminNotifications' => collect(),
                        'adminNotificationCount' => 0,
                    ]);
                }
            });

            $this->app->make('view')->composer('partials.seo', function ($view) {
                try {
                    $seo = Frontend::where('data_keys', 'seo.data')->first();
                    $view->with([
                        'seo' => $seo ? $seo->data_values : $seo,
                    ]);
                } catch (\Exception $e) {
                    $view->with(['seo' => null]);
                }
            });

            try {
                if (gs('force_ssl')) {
                    \URL::forceScheme('https');
                }
            } catch (\Exception $e) {
                // Ignore if gs() fails
            }
        } catch (\Exception $e) {
            // Database not ready, set minimal view shares if view service is available
            if ($this->app->bound('view')) {
                try {
                    $viewShare['activeTemplate'] = 'templates.basic.';
                    $viewShare['activeTemplateTrue'] = 'assets/templates/basic/';
                    $viewShare['emptyMessage'] = 'Data not found';
                    $this->app->make('view')->share($viewShare);
                } catch (\Exception $e2) {
                    // Ignore if view sharing fails
                }
            }
        }


        Paginator::useBootstrapFive();
    }
}
