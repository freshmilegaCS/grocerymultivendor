<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Config\Services;
use App\Models\LanguageModel;
use App\Models\UserModel;

class LangFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
{
    // Check if language is already set in session to avoid repeated execution
    // if (session()->get('site_lang')) {
    //     return;
    // }
    
    $selectedLanguage = null;
    
    // First check if user is logged in and has language preference
    if (session()->get('login_type')) {
        $userModel = new UserModel();
        $user = null;
        
        if (session()->get('login_type') == 'email') {
            $user = $userModel->where('email', session()->get('email'))
                             ->where('is_active', 1)
                             ->where('is_delete', 0)
                             ->first();
        }
        
        if (session()->get('login_type') == 'mobile') {
            $user = $userModel->where('mobile', session()->get('mobile'))
                             ->where('is_active', 1)
                             ->where('is_delete', 0)
                             ->first();
        }
        
        // If user found and has language preference, use it
        if ($user && !empty($user['language'])) {
            $languageModel = new LanguageModel();
            $selectedLanguage = $languageModel->where('lang_short', $user['language'])->first();
        }
    }
    
    // If no user language preference found, get default language from database
    if (!$selectedLanguage) {
        $languageModel = new LanguageModel();
        $selectedLanguage = $languageModel->where('is_default', 1)->first();
    }
    
    // Set language in session
    if ($selectedLanguage) {
        session()->set('site_lang', $selectedLanguage['lang_short']);
        session()->set('is_rtl', $selectedLanguage['is_rtl']);
        Services::language()->setLocale($selectedLanguage['lang_short']);
    } else {
        // Final fallback to English if nothing found
        session()->set('site_lang', 'en');
        session()->set('is_rtl', 0);
        Services::language()->setLocale('en');
    }
}

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) 
    {
        // Optional: Add any post-processing logic here
    }
}