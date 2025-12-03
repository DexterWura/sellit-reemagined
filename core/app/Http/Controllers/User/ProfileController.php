<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function profile()
    {
        $pageTitle = "Profile Setting";
        $user = auth()->user();
        return view('Template::user.profile_setting', compact('pageTitle','user'));
    }

    public function submitProfile(Request $request)
    {
        $request->validate([
            'firstname' => 'required|string|min:2|max:50|regex:/^[a-zA-Z\s\-\'\.]+$/',
            'lastname' => 'required|string|min:2|max:50|regex:/^[a-zA-Z\s\-\'\.]+$/',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip' => 'nullable|string|max:20',
        ],[
            'firstname.required' => 'First name is required',
            'firstname.min' => 'First name must be at least 2 characters',
            'firstname.max' => 'First name cannot exceed 50 characters',
            'firstname.regex' => 'First name can only contain letters, spaces, hyphens, apostrophes, and periods',
            'lastname.required' => 'Last name is required',
            'lastname.min' => 'Last name must be at least 2 characters',
            'lastname.max' => 'Last name cannot exceed 50 characters',
            'lastname.regex' => 'Last name can only contain letters, spaces, hyphens, apostrophes, and periods',
        ]);

        $user = auth()->user();

        // Trim and capitalize names properly
        $user->firstname = ucwords(strtolower(trim($request->firstname)));
        $user->lastname = ucwords(strtolower(trim($request->lastname)));

        // Validate name doesn't contain only numbers or special characters
        if (preg_match('/^[0-9\s\-\.]+$/', $user->firstname) || preg_match('/^[0-9\s\-\.]+$/', $user->lastname)) {
            $notify[] = ['error', 'Names cannot contain only numbers or special characters'];
            return back()->withInput()->withNotify($notify);
        }

        $user->address = $request->address ? trim($request->address) : null;
        $user->city = $request->city ? ucwords(strtolower(trim($request->city))) : null;
        $user->state = $request->state ? ucwords(strtolower(trim($request->state))) : null;
        $user->zip = $request->zip ? strtoupper(trim($request->zip)) : null;

        // Validate ZIP code format if provided
        if ($user->zip && !preg_match('/^[A-Z0-9\s\-]{3,10}$/i', $user->zip)) {
            $notify[] = ['error', 'Invalid ZIP/postal code format'];
            return back()->withInput()->withNotify($notify);
        }

        $user->save();
        $notify[] = ['success', 'Profile updated successfully'];
        return back()->withNotify($notify);
    }

    public function changePassword()
    {
        $pageTitle = 'Change Password';
        return view('Template::user.password', compact('pageTitle'));
    }

    public function submitPassword(Request $request)
    {
        $passwordValidation = Password::min(6);
        if (gs('secure_password')) {
            $passwordValidation = $passwordValidation->mixedCase()->numbers()->symbols()->uncompromised();
        }

        $request->validate([
            'current_password' => 'required',
            'password' => ['required','confirmed',$passwordValidation]
        ], [
            'current_password.required' => 'Current password is required',
            'password.required' => 'New password is required',
            'password.confirmed' => 'Password confirmation does not match',
        ]);

        $user = auth()->user();
        
        // Check if current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            $notify[] = ['error', 'Current password is incorrect'];
            return back()->withInput()->withNotify($notify);
        }

        // Check if new password is same as current password
        if (Hash::check($request->password, $user->password)) {
            $notify[] = ['error', 'New password must be different from your current password'];
            return back()->withInput()->withNotify($notify);
        }

        // Check if password is too common (basic check)
        $commonPasswords = ['password', '123456', 'password123', 'qwerty', 'abc123', 'letmein', 'welcome'];
        if (in_array(strtolower($request->password), $commonPasswords)) {
            $notify[] = ['warning', 'This password is too common. Please choose a stronger password.'];
            // Don't block, just warn
        }

        $password = Hash::make($request->password);
        $user->password = $password;
        $user->save();
        
        $notify[] = ['success', 'Password changed successfully. Please log in again with your new password.'];
        return back()->withNotify($notify);
    }
}
