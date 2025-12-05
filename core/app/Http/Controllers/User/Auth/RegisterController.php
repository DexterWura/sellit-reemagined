<?php

namespace App\Http\Controllers\User\Auth;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\Intended;
use App\Models\AdminNotification;
use App\Models\Escrow;
use App\Models\User;
use App\Models\UserLogin;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{

    use RegistersUsers;

    public function __construct()
    {
        parent::__construct();
    }

    public function showRegistrationForm()
    {
        $pageTitle = "Register";
        Intended::identifyRoute();
        return view('Template::user.auth.register', compact('pageTitle'));
    }


    protected function validator(array $data)
    {

        $passwordValidation = Password::min(6);

        if (gs('secure_password')) {
            $passwordValidation = $passwordValidation->mixedCase()->numbers()->symbols()->uncompromised();
        }

        $agree = 'nullable';
        if (gs('agree')) {
            $agree = 'required';
        }

        // Validate full name - must be reasonable (not just numbers or single letters)
        $fullnameRules = [
            'required',
            'string',
            'min:3',
            'max:100',
            function ($attribute, $value, $fail) {
                $value = trim($value);
                // Check if it's just numbers
                if (preg_match('/^[0-9]+$/', $value)) {
                    $fail('Full name cannot be just numbers.');
                }
                // Check if it's too short or just single characters
                if (strlen($value) < 3) {
                    $fail('Full name must be at least 3 characters long.');
                }
                // Check if it contains at least one letter
                if (!preg_match('/[a-zA-Z]/', $value)) {
                    $fail('Full name must contain at least one letter.');
                }
            }
        ];

        $validate = Validator::make($data, [
            'fullname'  => $fullnameRules,
            'email'     => 'required|string|email|unique:users',
            'mobile'     => ['required', 'regex:/^[0-9]+$/', 'min:6', 'max:15', 'unique:users,mobile'],
            'mobile_code' => 'required|string',
            'country'   => 'required|string',
            'country_code' => 'required|string',
            'password'  => ['required', 'confirmed', $passwordValidation],
            'captcha'   => 'sometimes|required',
            'agree'     => $agree
        ], [
            'fullname.required' => 'Full name is required',
            'fullname.min' => 'Full name must be at least 3 characters',
            'mobile.required' => 'Phone number is required',
            'mobile.regex' => 'Phone number must contain only numbers',
            'country.required' => 'Country is required'
        ]);

        return $validate;
    }

    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        $request->session()->regenerateToken();

        if (!verifyCaptcha()) {
            $notify[] = ['error', 'Invalid captcha provided'];
            return back()->withNotify($notify);
        }



        try {
            event(new Registered($user = $this->create($request->all())));
            $this->guard()->login($user);
        } catch (\Exception $e) {
            \Log::error('User registration failed', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip_address' => $request->ip()
            ]);
            throw $e;
        }

        // Log successful registration
        \Log::info('User registration successful', [
            'user_id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'mobile' => $user->mobile,
            'country' => $user->country_name,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer' => $referUser ? $referUser->username : null,
            'ref_by' => $user->ref_by,
            'timestamp' => now()->toIso8601String()
        ]);

        return $this->registered($request, $user)
            ?:  redirect($this->redirectPath());
    }



    protected function create(array $data)
    {
        $referBy = session()->get('reference');
        if ($referBy) {
            $referUser = User::where('username', $referBy)->first();
        } else {
            $referUser = null;
        }

        // Split fullname into firstname and lastname
        $fullname = trim($data['fullname']);
        $nameParts = explode(' ', $fullname, 2);
        $firstname = $nameParts[0];
        $lastname = isset($nameParts[1]) && !empty($nameParts[1]) ? $nameParts[1] : $nameParts[0];

        // Auto-generate username from email
        $email = strtolower($data['email']);
        $emailParts = explode('@', $email);
        $baseUsername = preg_replace('/[^a-z0-9]/', '', $emailParts[0]); // Remove special chars, keep only lowercase letters and numbers
        
        // Ensure username is not empty
        if (empty($baseUsername)) {
            $baseUsername = 'user';
        }
        
        // Check if username exists and generate unique one
        $username = $baseUsername;
        $counter = 0;
        while (User::where('username', $username)->exists()) {
            $counter++;
            $username = $baseUsername . str_pad($counter, 2, '0', STR_PAD_LEFT);
        }

        //User Create
        $user = new User();
        $user->email = $email;
        $user->firstname = $firstname;
        $user->lastname = $lastname;
        $user->username = $username;
        $user->password = Hash::make($data['password']);
        $user->mobile = $data['mobile'];
        $user->dial_code = $data['mobile_code'];
        $user->country_code = $data['country_code'];
        $user->country_name = $data['country'];
        $user->ref_by = $referUser ? $referUser->id : 0;
        $user->kv = gs('kv') ? Status::NO : Status::YES;
        $user->ev = gs('ev') ? Status::NO : Status::YES;
        $user->sv = gs('sv') ? Status::NO : Status::YES;
        $user->ts = Status::DISABLE;
        $user->tv = Status::ENABLE;
        $user->profile_complete = Status::YES; // Mark profile as complete since we collected all required info
        $user->save();

        $adminNotification            = new AdminNotification();
        $adminNotification->user_id   = $user->id;
        $adminNotification->title     = 'New member registered';
        $adminNotification->click_url = urlPath('admin.users.detail', $user->id);
        $adminNotification->save();


        //Login Log Create
        $ip        = getRealIP();
        $exist     = UserLogin::where('user_ip', $ip)->first();
        $userLogin = new UserLogin();

        if ($exist) {
            $userLogin->longitude    = $exist->longitude;
            $userLogin->latitude     = $exist->latitude;
            $userLogin->city         = $exist->city;
            $userLogin->country_code = $exist->country_code;
            $userLogin->country      = $exist->country;
        } else {
            $info                    = json_decode(json_encode(getIpInfo()), true);
            $userLogin->longitude    = @implode(',', $info['long']);
            $userLogin->latitude     = @implode(',', $info['lat']);
            $userLogin->city         = @implode(',', $info['city']);
            $userLogin->country_code = @implode(',', $info['code']);
            $userLogin->country      = @implode(',', $info['country']);
        }

        $userAgent          = osBrowser();
        $userLogin->user_id = $user->id;
        $userLogin->user_ip = $ip;

        $userLogin->browser = @$userAgent['browser'];
        $userLogin->os      = @$userAgent['os_platform'];
        $userLogin->save();


        $escrows = Escrow::where('invitation_mail', $user->email)->get();
        
        foreach ($escrows as $escrow) {
            $conversation = $escrow->conversation;
            if ($escrow->seller_id == 0) {
                $escrow->seller_id       = $user->id;
                $conversation->seller_id = $user->id;
            } else {
                $escrow->buyer_id       = $user->id;
                $conversation->buyer_id = $user->id;
            }
            $escrow->invitation_mail = null;
            $escrow->save();
            $conversation->save();
        }


        return $user;
    }

    public function checkUser(Request $request)
    {
        $exist['data'] = false;
        $exist['type'] = null;
        if ($request->email) {
            $exist['data']  = User::where('email', $request->email)->exists();
            $exist['type']  = 'email';
            $exist['field'] = 'Email';
        }
        if ($request->mobile) {
            $exist['data']  = User::where('mobile', $request->mobile)->where('dial_code', $request->mobile_code)->exists();
            $exist['type']  = 'mobile';
            $exist['field'] = 'Mobile';
        }
        if ($request->username) {
            $exist['data']  = User::where('username', $request->username)->exists();
            $exist['type']  = 'username';
            $exist['field'] = 'Username';
        }
        return response($exist);
    }

    public function registered()
    {
        return to_route('user.home');
    }
}
