<?php

namespace App\Controllers\Website;

use App\Controllers\BaseController;
use App\Models\CartsModel;
use App\Models\LanguageModel;
use App\Models\UserModel;

class Language extends BaseController
{
    public function index()
    {
        $data['settings'] = $this->settings;
        $data['country'] = $this->country;
        $user = null;

        $cartsModel = new CartsModel();
        $userModel = new UserModel();
        if (session()->get('login_type') == 'email') {
            $user = $userModel->where('email', session()->get('email'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        if (session()->get('login_type') == 'mobile') {
            $user = $userModel->where('mobile', session()->get('mobile'))->where('is_active', 1)->where('is_delete', 0)->first();
        }
        if (!$user) {
            $data['cartItemCount'] = 0;
        } else {
            $cartItemCount = $cartsModel->where('user_id', $user['id'])->countAllResults();
            $data['cartItemCount'] = $cartItemCount;
            $data['user'] = $user;
        }

        $data['user_name'] = $user['name'];
        $data['user_mobile'] = $user['mobile'];
        $data['user_email'] = $user['email'];

        $languageModel = new LanguageModel();

        $data['languageList'] = $languageModel->where('is_active', 1)->findAll();


        return view('website/language/language', $data);
    }

    public function changeLanguage($language_id)
    {

        $data['settings'] = $this->settings;
        $data['country'] = $this->country;
        $languageModel = new LanguageModel();

        $cartsModel = new CartsModel();
        $userModel = new UserModel();
        $user = null;
        if (session()->get('login_type') == 'email') {
            $user = $userModel->where('email', session()->get('email'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        if (session()->get('login_type') == 'mobile') {
            $user = $userModel->where('mobile', session()->get('mobile'))->where('is_active', 1)->where('is_delete', 0)->first();
        }
        if (!$user) {
            $data['cartItemCount'] = 0;
        } else {
            $cartItemCount = $cartsModel->where('user_id', $user['id'])->countAllResults();
            $data['cartItemCount'] = $cartItemCount;
            $data['user'] = $user;
        }

        $data['user_name'] = $user['name'];
        $data['user_mobile'] = $user['mobile'];
        $data['user_email'] = $user['email'];

        $language = $languageModel->where('is_active', 1)->where('id', $language_id)->first();

        $updateData = [
            'language' => $language['lang_short']
        ];
        
        $userModel->update($user['id'], $updateData);

        session()->set('site_lang', $language['lang_short']);
        session()->set('is_rtl', $language['is_rtl']);


        $data['languageList'] = $languageModel->where('is_active', 1)->findAll();


        // return view('website/language/language', $data);
        return redirect()->to('/language');
    }
}
