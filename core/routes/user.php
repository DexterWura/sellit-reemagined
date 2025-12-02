<?php

use Illuminate\Support\Facades\Route;

Route::namespace('User\Auth')->name('user.')->group(function () {

    Route::middleware('guest')->group(function () {
        Route::controller('LoginController')->group(function () {
            Route::get('/login', 'showLoginForm')->name('login');
            Route::post('/login', 'login');
            Route::get('logout', 'logout')->middleware('auth')->withoutMiddleware('guest')->name('logout');
        });

        Route::controller('RegisterController')->middleware(['guest'])->group(function () {
            Route::get('register', 'showRegistrationForm')->name('register');
            Route::post('register', 'register');
            Route::post('check-user', 'checkUser')->name('checkUser')->withoutMiddleware('guest');
        });

        Route::controller('ForgotPasswordController')->prefix('password')->name('password.')->group(function () {
            Route::get('reset', 'showLinkRequestForm')->name('request');
            Route::post('email', 'sendResetCodeEmail')->name('email');
            Route::get('code-verify', 'codeVerify')->name('code.verify');
            Route::post('verify-code', 'verifyCode')->name('verify.code');
        });

        Route::controller('ResetPasswordController')->group(function () {
            Route::post('password/reset', 'reset')->name('password.update');
            Route::get('password/reset/{token}', 'showResetForm')->name('password.reset');
        });

        Route::controller('SocialiteController')->group(function () {
            Route::get('social-login/{provider}', 'socialLogin')->name('social.login');
            Route::get('social-login/callback/{provider}', 'callback')->name('social.login.callback');
        });
    });
});

Route::middleware('auth')->name('user.')->group(function () {

    Route::get('user-data', 'User\UserController@userData')->name('data');
    Route::post('user-data-submit', 'User\UserController@userDataSubmit')->name('data.submit');

    //authorization
    Route::middleware('registration.complete')->namespace('User')->controller('AuthorizationController')->group(function () {
        Route::get('authorization', 'authorizeForm')->name('authorization');
        Route::get('resend-verify/{type}', 'sendVerifyCode')->name('send.verify.code');
        Route::post('verify-email', 'emailVerification')->name('verify.email');
        Route::post('verify-mobile', 'mobileVerification')->name('verify.mobile');
        Route::post('verify-g2fa', 'g2faVerification')->name('2fa.verify');
    });

    Route::middleware(['check.status', 'registration.complete'])->group(function () {

        Route::namespace('User')->group(function () {

            Route::controller('UserController')->group(function () {
                Route::get('dashboard', 'home')->name('home');
                Route::get('download-attachments/{file_hash}', 'downloadAttachment')->name('download.attachment');

                //2FA
                Route::get('twofactor', 'show2faForm')->name('twofactor');
                Route::post('twofactor/enable', 'create2fa')->name('twofactor.enable');
                Route::post('twofactor/disable', 'disable2fa')->name('twofactor.disable');

                //KYC
                Route::get('kyc-form', 'kycForm')->name('kyc.form');
                Route::get('kyc-data', 'kycData')->name('kyc.data');
                Route::post('kyc-submit', 'kycSubmit')->name('kyc.submit');

                //Report
                Route::any('deposit/history', 'depositHistory')->name('deposit.history');
                Route::get('transactions', 'transactions')->name('transactions');

                Route::post('add-device-token', 'addDeviceToken')->name('add.device.token');
            });

            //Profile setting
            Route::controller('ProfileController')->group(function () {
                Route::get('profile-setting', 'profile')->name('profile.setting');
                Route::post('profile-setting', 'submitProfile');
                Route::get('change-password', 'changePassword')->name('change.password');
                Route::post('change-password', 'submitPassword');
            });


            Route::controller('EscrowController')->name('escrow.')->prefix('escrow')->group(function () {
                Route::get('step-one', 'stepOne')->name('step.one');
                Route::post('step-one', 'submitStepOne')->name('step.one.submit');
                Route::get('step-two', 'stepTwo')->name('step.two');
                Route::post('step-two', 'submitStepTwo')->name('step.two.submit');
                Route::get('details/{id}', 'details')->name('details');

                Route::post('cancel/{id}', 'cancel')->name('cancel');
                Route::post('accept/{id}', 'accept')->name('accept');
                Route::post('dispute/{id}', 'dispute')->name('dispute');
                Route::post('dispatch/{id}', 'dispatchEscrow')->name('dispatch');

                Route::post('message-reply', 'replyMessage')->name('message.reply');
                Route::get('get-messages', 'getMessages')->name('message.get');
                Route::get('{type?}', 'index')->name('index');
            });

            Route::controller('MilestoneController')->name('escrow.milestone.')->prefix('escrow/milestone')->group(function () {
                Route::get('/{id}', 'milestones')->name('index');
                Route::post('/{id}', 'createMilestone')->name('create');
                Route::post('/pay/{id}', 'payMilestone')->name('pay');
            });

            // Marketplace - My Listings
            Route::controller('ListingController')->name('listing.')->prefix('listing')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('create', 'create')->name('create');
                Route::post('store', 'store')->name('store');
                Route::get('edit/{id}', 'edit')->name('edit');
                Route::post('update/{id}', 'update')->name('update');
                Route::get('show/{id}', 'show')->name('show');
                Route::post('cancel/{id}', 'cancel')->name('cancel');
                Route::delete('image/{id}', 'deleteImage')->name('image.delete');
                Route::post('image/primary/{id}', 'setPrimaryImage')->name('image.primary');
                Route::post('metrics/{id}', 'addMetrics')->name('metrics.add');
                Route::post('question/answer/{id}', 'answerQuestion')->name('question.answer');
            });

            // Bids
            Route::controller('BidController')->name('bid.')->prefix('bid')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('place/{listingId}', 'place')->name('place');
                Route::post('buy-now/{listingId}', 'buyNow')->name('buy.now');
                Route::post('cancel/{id}', 'cancel')->name('cancel');
                Route::get('won', 'wonAuctions')->name('won');
            });

            // Offers
            Route::controller('OfferController')->name('offer.')->prefix('offer')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('received', 'received')->name('received');
                Route::post('make/{listingId}', 'make')->name('make');
                Route::post('accept/{id}', 'accept')->name('accept');
                Route::post('reject/{id}', 'reject')->name('reject');
                Route::post('counter/{id}', 'counter')->name('counter');
                Route::post('accept-counter/{id}', 'acceptCounter')->name('accept.counter');
                Route::post('cancel/{id}', 'cancel')->name('cancel');
            });

            // Watchlist
            Route::controller('WatchlistController')->name('watchlist.')->prefix('watchlist')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('toggle/{listingId}', 'toggle')->name('toggle');
                Route::post('settings/{id}', 'updateSettings')->name('settings');
                Route::delete('remove/{id}', 'remove')->name('remove');
            });

            // Saved Searches
            Route::controller('SavedSearchController')->name('saved_search.')->prefix('saved-search')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('store', 'store')->name('store');
                Route::get('apply/{id}', 'apply')->name('apply');
                Route::post('update/{id}', 'update')->name('update');
                Route::delete('delete/{id}', 'destroy')->name('delete');
                Route::post('toggle-alerts/{id}', 'toggleAlerts')->name('toggle.alerts');
            });

            // Domain Verification
            Route::controller('DomainVerificationController')->name('verification.')->prefix('verification')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('show/{id}', 'show')->name('show');
                Route::post('initiate/{listingId}', 'initiate')->name('initiate');
                Route::post('verify/{id}', 'verify')->name('verify');
                Route::post('change-method/{id}', 'changeMethod')->name('change.method');
                Route::get('download/{id}', 'downloadFile')->name('download');
            });

            // Withdraw
            Route::controller('WithdrawController')->prefix('withdraw')->name('withdraw')->group(function () {
                Route::middleware('kyc')->group(function () {
                    Route::get('/', 'withdrawMoney');
                    Route::post('/', 'withdrawStore')->name('.money');
                    Route::get('preview', 'withdrawPreview')->name('.preview');
                    Route::post('preview', 'withdrawSubmit')->name('.submit');
                });
                Route::get('history', 'withdrawLog')->name('.history');
            });
        });

        // Payment
        Route::prefix('deposit')->name('deposit.')->controller('Gateway\PaymentController')->group(function () {
            Route::post('insert', 'depositInsert')->name('insert');
            Route::get('confirm', 'depositConfirm')->name('confirm');
            Route::get('manual', 'manualDepositConfirm')->name('manual.confirm');
            Route::post('manual', 'manualDepositUpdate')->name('manual.update');
            Route::any('/{type?}', 'deposit')->name('index');
        });
    });
});
