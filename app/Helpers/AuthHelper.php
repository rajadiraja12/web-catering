<?php
// app/Helpers/AuthHelper.php

namespace App\Helpers;

use App\Models\User;

class AuthHelper
{
    public static function check()
    {
        return session()->has('user_id');
    }
    
    public static function user()
    {
        if (!session()->has('user_id')) {
            return null;
        }
        
        return User::find(session('user_id'));
    }
    
    public static function id()
    {
        return session('user_id');
    }
    
    public static function role()
    {
        return session('user_role');
    }
}