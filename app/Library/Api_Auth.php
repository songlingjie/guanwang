<?php

namespace App\Library;

class Api_Auth
{

    const AuthorizationMethod = 'bearer';

    //返回userid
    public function userId($token)
    {
        if (!$token)
            return null;
        $token = trim(str_ireplace(self::AuthorizationMethod, '', $token));
        $rsa = app('rsa');
        $str = $rsa->decrypt($token);
        if (empty($str))
            return false;
        list(, $user_id) = explode('_', $str);
        //if ($time < (time() - 7230))
        //    return false;
        return $user_id;
    }

    //创建token
    public function crtToken($user_id)
    {
        $rsa = app('rsa');
        $arr = [time(), $user_id];
        $str = join('_', $arr);
        unset($arr);
        return $rsa->encrypt($str);
    }

    //刷新token
    public function refreshToken($user_id)
    {
        return $this->crtToken($user_id);
    }
}
