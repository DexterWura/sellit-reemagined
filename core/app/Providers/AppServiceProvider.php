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
                $envFilePath = base_path('.env');
                if (!file_exists($envFilePath)) {
                    header('Location: /install');
                    exit;
                }
                $envContents = file_get_contents($envFilePath);
                if (empty(trim($envContents))) {
                    header('Location: /install');
                    exit;
                } else {
                    // .env exists and has content, set installation flag
                    try {
                        cache()->put('SystemInstalled', true, now()->addYears(10));
                    } catch (\Exception $e) {
                        // If cache fails, use file-based method
                        $cacheFile = base_path('storage/framework/cache/data/SystemInstalled');
                        $cacheDir = dirname($cacheFile);
                        if (!is_dir($cacheDir)) {
                            @mkdir($cacheDir, 0755, true);
                        }
                        @file_put_contents($cacheFile, serialize(['installed' => true, 'timestamp' => time()]));
                    }
                }
            }
        }


        $activeTemplate = activeTemplate();
        $viewShare['activeTemplate'] = $activeTemplate;
        $viewShare['activeTemplateTrue'] = activeTemplate(true);
        $viewShare['emptyMessage'] = 'Data not found';
        view()->share($viewShare);


        view()->composer('admin.partials.sidenav', function ($view) {
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
                'updateAvailable'    => version_compare(gs('available_version'),systemDetails()['version'],'>') ? 'v'.gs('available_version') : false,
            ]);
        });

        view()->composer('admin.partials.topnav', function ($view) {
            $view->with([
                'adminNotifications' => AdminNotification::where('is_read', Status::NO)->with('user')->orderBy('id', 'desc')->take(10)->get(),
                'adminNotificationCount' => AdminNotification::where('is_read', Status::NO)->count(),
            ]);
        });

        view()->composer('partials.seo', function ($view) {
            $seo = Frontend::where('data_keys', 'seo.data')->first();
            $view->with([
                'seo' => $seo ? $seo->data_values : $seo,
            ]);
        });

        if (gs('force_ssl')) {
            \URL::forceScheme('https');
        }


        Paginator::useBootstrapFive();

    }
}
