<?php

namespace App\Libraries;

class UserService
{
    private static $currentUser = null;

    public static function setCurrentUser($userData)
    {
        self::$currentUser = $userData;
    }

    public static function getCurrentUser()
    {
        return self::$currentUser;
    }

    public static function getUserId()
    {
        return self::$currentUser['user_id'] ?? null;
    }

    public static function clear()
    {
        self::$currentUser = null;
    }
}
