<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;

class AuthController
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            Response::redirect('/hos/dashboard');
        }
        View::render('auth/login', [
            'pageTitle' => 'เข้าสู่ระบบ — PCU Smart Pharmacy',
            'error' => Session::flash('error'),
            'success' => Session::flash('success')
        ], null); // login has its own clean standalone layout
    }

    public function login(): void
    {
        $username = Request::post('username', '');
        $password = Request::post('password', '');

        if (empty($username) || empty($password)) {
            Session::flash('error', 'กรุณาระบุชื่อผู้ใช้งานและรหัสผ่าน');
            Response::redirect('/hos/login');
        }

        if (Auth::attempt($username, $password)) {
            Session::flash('success', 'ยินดีต้อนรับเข้าสู่ระบบ PCU Smart Pharmacy');
            Response::redirect('/hos/dashboard');
        } else {
            Session::flash('error', 'ชื่อผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง');
            Response::redirect('/hos/login');
        }
    }

    public function logout(): void
    {
        Auth::logout();
        Session::flash('success', 'ออกจากระบบเรียบร้อยแล้ว');
        Response::redirect('/hos/login');
    }
}
