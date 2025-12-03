<?php

use App\Constants\Status;
use App\Lib\GoogleAuthenticator;
use App\Models\Extension;
use App\Models\Frontend;
use App\Models\GeneralSetting;
use Carbon\Carbon;
use App\Lib\Captcha;
use App\Lib\ClientInfo;
use App\Lib\CurlRequest;
use App\Lib\FileManager;
use App\Notify\Notify;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Laramin\Utility\VugiChugi;

function systemDetails()
{
    // Get system name from general settings, fallback to default
    $systemName = gs('site_name') ?? 'sellit';
    $system['name']          = $systemName;
    $system['version']       = '1.0';
    $system['build_version'] = '1.0.0';
    return $system;
}

function slug($string)
{
    return Str::slug($string);
}

/**
 * Normalize a URL - add protocol if missing, remove trailing slashes, etc.
 */
function normalizeUrl($url)
{
    if (empty($url)) {
        return null;
    }
    
    $url = trim($url);
    
    // Remove trailing slashes (except after protocol)
    $url = rtrim($url, '/');
    
    // Add protocol if missing
    if (!preg_match('/^https?:\/\//i', $url)) {
        // Check if it looks like a domain
        if (preg_match('/^[a-zA-Z0-9][a-zA-Z0-9-]{0,61}[a-zA-Z0-9]?\.[a-zA-Z]{2,}/', $url)) {
            $url = 'https://' . $url;
        }
    }
    
    return $url;
}

/**
 * Extract clean domain name from URL
 */
function extractDomain($url)
{
    if (empty($url)) {
        return null;
    }
    
    $url = normalizeUrl($url);
    
    try {
        $parsed = parse_url($url);
        if (!isset($parsed['host'])) {
            return null;
        }
        
        $domain = $parsed['host'];
        
        // Remove www. prefix
        $domain = preg_replace('/^www\./i', '', $domain);
        
        // Remove port if present
        $domain = explode(':', $domain)[0];
        
        return strtolower($domain);
    } catch (\Exception $e) {
        return null;
    }
}

/**
 * Check if a domain/URL is accessible
 */
function checkDomainAccessibility($url, $timeout = 5)
{
    if (empty($url)) {
        return ['accessible' => false, 'error' => 'URL is empty'];
    }
    
    $url = normalizeUrl($url);
    
    try {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => $timeout,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; DomainChecker/1.0)',
            CURLOPT_NOBODY => true, // HEAD request only
        ]);
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 400) {
            return ['accessible' => true, 'http_code' => $httpCode];
        } else {
            return [
                'accessible' => false,
                'error' => $curlError ?: "HTTP $httpCode",
                'http_code' => $httpCode
            ];
        }
    } catch (\Exception $e) {
        return ['accessible' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Format amount with helpful context
 */
function formatAmountWithContext($amount, $currency = null)
{
    $currency = $currency ?? gs('cur_text') ?? 'USD';
    $formatted = showAmount($amount);
    
    // Add helpful context for large amounts
    if ($amount >= 1000000) {
        return $formatted . ' (' . number_format($amount / 1000000, 2) . 'M)';
    } elseif ($amount >= 1000) {
        return $formatted . ' (' . number_format($amount / 1000, 2) . 'K)';
    }
    
    return $formatted;
}

/**
 * Get helpful tip based on context
 */
function getHelpfulTip($context, $data = [])
{
    $tips = [
        'low_balance' => 'Consider depositing funds to avoid payment delays',
        'high_bid' => 'Make sure you have sufficient funds if you win this auction',
        'first_listing' => 'Complete your profile and verify your account for better visibility',
        'expired_offer' => 'Offers expire after 7 days. Make a new offer if still interested',
        'pending_verification' => 'Domain verification usually takes a few minutes after uploading the file',
    ];
    
    return $tips[$context] ?? null;
}

/**
 * Validate and suggest better input
 */
function suggestBetterInput($field, $value, $type = 'text')
{
    $suggestions = [];
    
    switch ($type) {
        case 'email':
            $value = strtolower(trim($value));
            if (strpos($value, '@gmail.com') !== false) {
                // Remove dots before @ for Gmail
                $value = str_replace('.', '', substr($value, 0, strpos($value, '@'))) . '@gmail.com';
            }
            break;
            
        case 'phone':
            // Remove common formatting
            $value = preg_replace('/[^0-9+]/', '', $value);
            break;
            
        case 'url':
            $value = normalizeUrl($value);
            break;
            
        case 'name':
            $value = ucwords(strtolower(trim($value)));
            break;
    }
    
    return $value;
}

function verificationCode($length)
{
    if ($length == 0) return 0;
    $min = pow(10, $length - 1);
    $max = (int) ($min - 1).'9';
    return random_int($min,$max);
}

function getNumber($length = 8)
{
    $characters = '1234567890';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}


function activeTemplate($asset = false) {
    try {
        $template = session('template') ?? gs('active_template');
        // Fallback if gs() returns null
        if (empty($template)) {
            $template = 'basic';
        }
        if ($asset) return 'assets/templates/' . $template . '/';
        return 'templates.' . $template . '.';
    } catch (\Exception $e) {
        // Fallback template
        if ($asset) return 'assets/templates/basic/';
        return 'templates.basic.';
    }
}

function activeTemplateName() {
    try {
        $template = session('template') ?? gs('active_template');
        // Fallback if gs() returns null
        if (empty($template)) {
            $template = 'basic';
        }
        return $template;
    } catch (\Exception $e) {
        return 'basic';
    }
}

function siteLogo($type = null) {
    $name = $type ? "/logo_$type.png" : '/logo.png';
    return getImage(getFilePath('logo_icon') . $name);
}
function siteFavicon() {
    return getImage(getFilePath('logo_icon'). '/favicon.png');
}

function loadReCaptcha()
{
    return Captcha::reCaptcha();
}

function loadCustomCaptcha($width = '100%', $height = 46, $bgColor = '#003')
{
    return Captcha::customCaptcha($width, $height, $bgColor);
}

function verifyCaptcha()
{
    return Captcha::verify();
}

function loadExtension($key)
{
    $extension = Extension::where('act', $key)->where('status', Status::ENABLE)->first();
    return $extension ? $extension->generateScript() : '';
}

function getTrx($length = 12)
{
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function getAmount($amount, $length = 2)
{
    $amount = round($amount ?? 0, $length);
    return $amount + 0;
}

function showAmount($amount, $decimal = 2, $separate = true, $exceptZeros = false, $currencyFormat = true)
{
    $separator = '';
    if ($separate) {
        $separator = ',';
    }
    $printAmount = number_format($amount, $decimal, '.', $separator);
    if ($exceptZeros) {
        $exp = explode('.', $printAmount);
        if ($exp[1] * 1 == 0) {
            $printAmount = $exp[0];
        } else {
            $printAmount = rtrim($printAmount, '0');
        }
    }
    if ($currencyFormat) {
        if (gs('currency_format') == Status::CUR_BOTH) {
            return gs('cur_sym').$printAmount.' '.__(gs('cur_text'));
        }elseif(gs('currency_format') == Status::CUR_TEXT){
            return $printAmount.' '.__(gs('cur_text'));
        }else{
            return gs('cur_sym').$printAmount;
        }
    }
    return $printAmount;
}


function removeElement($array, $value)
{
    return array_diff($array, (is_array($value) ? $value : array($value)));
}

function cryptoQR($wallet)
{
    return "https://api.qrserver.com/v1/create-qr-code/?data=$wallet&size=300x300&ecc=m";
}

function keyToTitle($text)
{
    return ucfirst(preg_replace("/[^A-Za-z0-9 ]/", ' ', $text));
}


function titleToKey($text)
{
    return strtolower(str_replace(' ', '_', $text));
}


function strLimit($title = null, $length = 10)
{
    return Str::limit($title, $length);
}


function getIpInfo()
{
    $ipInfo = ClientInfo::ipInfo();
    return $ipInfo;
}


function osBrowser()
{
    $osBrowser = ClientInfo::osBrowser();
    return $osBrowser;
}


function getTemplates()
{
    $param['purchasecode'] = env("PURCHASECODE");
    $param['website'] = @$_SERVER['HTTP_HOST'] . @$_SERVER['REQUEST_URI'] . ' - ' . env("APP_URL");
    $url = VugiChugi::gttmp() . systemDetails()['name'];
    $response = CurlRequest::curlPostContent($url, $param);
    if ($response) {
        return $response;
    } else {
        return null;
    }
}


function getPageSections($arr = false)
{
    $jsonUrl = resource_path('views/') . str_replace('.', '/', activeTemplate()) . 'sections.json';
    $sections = json_decode(file_get_contents($jsonUrl));
    if ($arr) {
        $sections = json_decode(file_get_contents($jsonUrl), true);
        ksort($sections);
    }
    return $sections;
}


function getImage($image, $size = null)
{
    $clean = '';
    if (file_exists($image) && is_file($image)) {
        return asset($image) . $clean;
    }
    if ($size) {
        return route('placeholder.image', $size);
    }
    return asset('assets/images/default.png');
}


function notify($user, $templateName, $shortCodes = null, $sendVia = null, $createLog = true,$pushImage = null)
{
    $globalShortCodes = [
        'site_name' => gs('site_name'),
        'site_currency' => gs('cur_text'),
        'currency_symbol' => gs('cur_sym'),
    ];

    if (gettype($user) == 'array') {
        $user = (object) $user;
    }

    $shortCodes = array_merge($shortCodes ?? [], $globalShortCodes);

    $notify = new Notify($sendVia);
    $notify->templateName = $templateName;
    $notify->shortCodes = $shortCodes;
    $notify->user = $user;
    $notify->createLog = $createLog;
    $notify->pushImage = $pushImage;
    $notify->userColumn = isset($user->id) ? $user->getForeignKey() : 'user_id';
    $notify->send();
}

function getPaginate($paginate = null)
{
    if (!$paginate) {
        $paginate = gs('paginate_number');
    }
    return $paginate;
}

function paginateLinks($data)
{
    return $data->appends(request()->all())->links();
}


function menuActive($routeName, $type = null, $param = null)
{
    if ($type == 3) $class = 'side-menu--open';
    elseif ($type == 2) $class = 'sidebar-submenu__open';
    else $class = 'active';

    if (is_array($routeName)) {
        foreach ($routeName as $key => $value) {
            if (request()->routeIs($value)) return $class;
        }
    } elseif (request()->routeIs($routeName)) {
        if ($param) {
            $routeParam = array_values(@request()->route()->parameters ?? []);
            if (strtolower(@$routeParam[0]) == strtolower($param)) return $class;
            else return;
        }
        return $class;
    }
}


function fileUploader($file, $location, $size = null, $old = null, $thumb = null,$filename = null)
{
    $fileManager = new FileManager($file);
    $fileManager->path = $location;
    $fileManager->size = $size;
    $fileManager->old = $old;
    $fileManager->thumb = $thumb;
    $fileManager->filename = $filename;
    $fileManager->upload();
    return $fileManager->filename;
}

function fileManager()
{
    return new FileManager();
}

function getFilePath($key)
{
    return fileManager()->$key()->path;
}

function getFileSize($key)
{
    return fileManager()->$key()->size;
}

function getFileExt($key)
{
    return fileManager()->$key()->extensions;
}

function diffForHumans($date)
{
    $lang = session()->get('lang');
    Carbon::setlocale($lang);
    return Carbon::parse($date)->diffForHumans();
}


function showDateTime($date, $format = 'Y-m-d h:i A')
{
    if (!$date) {
        return '-';
    }
    $lang = session()->get('lang');
    Carbon::setlocale($lang);
    return Carbon::parse($date)->translatedFormat($format);
}


function getContent($dataKeys, $singleQuery = false, $limit = null, $orderById = false) {

    $templateName = activeTemplateName();
    if ($singleQuery) {
        $content = Frontend::where('tempname', $templateName)->where('data_keys', $dataKeys)->orderBy('id', 'desc')->first();
    } else {
        $article = Frontend::where('tempname', $templateName);
        $article->when($limit != null, function ($q) use ($limit) {
            return $q->limit($limit);
        });
        if ($orderById) {
            $content = $article->where('data_keys', $dataKeys)->orderBy('id')->get();
        } else {
            $content = $article->where('data_keys', $dataKeys)->orderBy('id', 'desc')->get();
        }
    }
    return $content;
}

function verifyG2fa($user, $code, $secret = null)
{
    $authenticator = new GoogleAuthenticator();
    if (!$secret) {
        $secret = $user->tsc;
    }
    $oneCode = $authenticator->getCode($secret);
    $userCode = $code;
    if ($oneCode == $userCode) {
        $user->tv = Status::YES;
        $user->save();
        return true;
    } else {
        return false;
    }
}


function urlPath($routeName, $routeParam = null)
{
    if ($routeParam == null) {
        $url = route($routeName);
    } else {
        $url = route($routeName, $routeParam);
    }
    $basePath = route('home');
    $path = str_replace($basePath, '', $url);
    return $path;
}


function showMobileNumber($number)
{
    $length = strlen($number);
    return substr_replace($number, '***', 2, $length - 4);
}

function showEmailAddress($email)
{
    $endPosition = strpos($email, '@') - 1;
    return substr_replace($email, '***', 1, $endPosition);
}


function getRealIP()
{
    $ip = $_SERVER["REMOTE_ADDR"];
    //Deep detect ip
    if (filter_var(@$_SERVER['HTTP_FORWARDED'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_FORWARDED'];
    }
    if (filter_var(@$_SERVER['HTTP_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_FORWARDED_FOR'];
    }
    if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    if (filter_var(@$_SERVER['HTTP_X_REAL_IP'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_X_REAL_IP'];
    }
    if (filter_var(@$_SERVER['HTTP_CF_CONNECTING_IP'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    }
    if ($ip == '::1') {
        $ip = '127.0.0.1';
    }

    return $ip;
}


function appendQuery($key, $value)
{
    return request()->fullUrlWithQuery([$key => $value]);
}

function dateSort($a, $b)
{
    return strtotime($a) - strtotime($b);
}

function dateSorting($arr)
{
    usort($arr, "dateSort");
    return $arr;
}

function gs($key = null)
{
    try {
        $general = Cache::get('GeneralSetting');
        if (!$general) {
            // Check if database is available
            try {
                \DB::connection()->getPdo();
                $general = GeneralSetting::first();
                if ($general) {
                    Cache::put('GeneralSetting', $general);
                }
            } catch (\Exception $e) {
                // Database not ready, return null
                return null;
            }
        }
        if ($key) return @$general->$key;
        return $general;
    } catch (\Exception $e) {
        // Return null if anything fails
        return null;
    }
}
function isImage($string){
    $allowedExtensions = array('jpg', 'jpeg', 'png', 'gif');
    $fileExtension = pathinfo($string, PATHINFO_EXTENSION);
    if (in_array($fileExtension, $allowedExtensions)) {
        return true;
    } else {
        return false;
    }
}

function isHtml($string)
{
    if (preg_match('/<.*?>/', $string)) {
        return true;
    } else {
        return false;
    }
}


function convertToReadableSize($size) {
    preg_match('/^(\d+)([KMG])$/', $size, $matches);
    $size = (int)$matches[1];
    $unit = $matches[2];

    if ($unit == 'G') {
        return $size.'GB';
    }

    if ($unit == 'M') {
        return $size.'MB';
    }

    if ($unit == 'K') {
        return $size.'KB';
    }

    return $size.$unit;
}


function frontendImage($sectionName, $image, $size = null,$seo = false)
{
    if ($seo) {
        return getImage('assets/images/frontend/' . $sectionName . '/seo/' . $image, $size);
    }
    return getImage('assets/images/frontend/' . $sectionName . '/' . $image, $size);
}

/**
 * Convert number to short format (K, M, B)
 * @param int|float $number
 * @param int $precision
 * @return string
 */
function shortNumber($number, $precision = 1)
{
    if ($number < 1000) {
        return number_format($number, 0);
    }

    $suffixes = ['', 'K', 'M', 'B', 'T'];
    $suffixIndex = 0;

    while ($number >= 1000 && $suffixIndex < count($suffixes) - 1) {
        $number /= 1000;
        $suffixIndex++;
    }

    $formattedNumber = number_format($number, $precision);
    // Remove trailing zeros after decimal point
    $formattedNumber = rtrim(rtrim($formattedNumber, '0'), '.');

    return $formattedNumber . $suffixes[$suffixIndex];
}

/**
 * Generate unique listing number
 * @return string
 */
function generateListingNumber()
{
    return 'LST' . date('Ymd') . strtoupper(Str::random(6));
}

/**
 * Generate unique bid number
 * @return string
 */
function generateBidNumber()
{
    return 'BID' . date('Ymd') . strtoupper(Str::random(6));
}

/**
 * Generate unique offer number
 * @return string
 */
function generateOfferNumber()
{
    return 'OFR' . date('Ymd') . strtoupper(Str::random(6));
}
