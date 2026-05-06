<?php

namespace App\Controllers;

class Pages extends BaseController
{
    public function index(): string
    {
        return view('index');
    }

    public function dashboard(): string
    {
        return view('dashboard');
    }

    public function login(): string
    {
        return view('login');
    }

    public function verify(): string
    {
        return view('verify');
    }

    public function myCertificates(): string
    {
        return view('my_certificates');
    }

    public function admin(): string
    {
        return view('admin');
    }

    public function superAdmin(): string
    {
        return view('super_admin/dashboard');
    }

    public function resetPassword(): string
    {
        return view('reset_password');
    }

    public function userLogin(): string
    {
        return view('user_login');
    }

    public function tempUserLogin(): string
    {
        return view('temp_user_login');
    }

    public function adminLogin(): string
    {
        return view('admin_login');
    }
}
