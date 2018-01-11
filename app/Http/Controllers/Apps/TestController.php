<?php

namespace App\Http\Controllers\Apps;
use EasyWeChat\Foundation\Application;
use App\Http\Controllers\Controller;
class TestController extends Controller
{
    //


    /**
     * 测试加密
     */
    public function test()
    {
        $rsa = app('rsa');
        $str = '123_'.time();
        echo "当前字符串",$str;
        echo "<br>";
        echo '字符串加密后:',$str = $rsa->encrypt($str),"<br>";
        echo '字符串解密后:',$rsa->decrypt($str),"<br>";

    }
}
