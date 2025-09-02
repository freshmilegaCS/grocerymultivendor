<?php

namespace App\Controllers;

class Test extends BaseController
{
    public function index(): string
    {
        return view('welcome_message');
    }

    public function testSession()
    {
        print_r(session()->get());
    }
}
