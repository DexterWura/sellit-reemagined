<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\Controller;
use App\Lib\SocialLogin;

class SocialiteController extends Controller
{

    public function socialLogin($provider)
    {
        $socialLogin = new SocialLogin($provider);
        return $socialLogin->redirectDriver();
    }


    public function callback($provider)
    {
        // Check if this OAuth is for ownership validation
        if (session()->has('oauth_for_ownership_validation')) {
            // Redirect to ownership validation callback
            return app(\App\Http\Controllers\User\OwnershipValidationController::class)->oauthCallback($provider);
        }
        
        // Normal social login
        $socialLogin = new SocialLogin($provider);
        try {
            return $socialLogin->login();
        } catch (\Exception $e) {
            $notify[] = ['error', $e->getMessage()];
            return to_route('home')->withNotify($notify);
        }
    }
}
