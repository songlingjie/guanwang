<?php

namespace App\Library;

use Illuminate\Support\Facades\Config;

final class Api_Sms {
    const EXPIRE_SEC = 300;    // 过期时间间隔
    const RESEND_SEC = 60;     // 重发时间间隔
    const ONE_DAY_FREQ = 5;    // 每日向同一个手机号发短信的次数
    const ONE_DAY_IMEI_COUNT = 3; // 每日向同一个手机号发送短信的IMEI个数

    public $error = array();
    public $code = 0;
    public $result;
    private $mobile;

    public function __construct()
    {
        app()->configure('sms');//加载配置文件
    }


    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
    }

    /**
     * 向指定手机号发送验证码
     * @param $mobile
     * @param $imei
     * @return bool
     */
    public function sendVerifyCode() {

        if(!$this->mobile)
            throw new \Exception('未填写手机号',-19);

        $mobile = $this->mobile;
        $redis = app('rdb');

        $vcKey = 'VC_'.$mobile;

        // 验证码重发限制
        $data = json_decode($redis->get($vcKey), true);

        if($data && time() < $data['resend_expire'])
            throw new \Exception('短信已在1分钟内发出，请耐心等待',-9);

        // 获取验证码
        $vc = $vc = !$data ? strval(rand(1000, 9999)) : $data['vc'];

        $data = array('vc' => $vc, 'resend_expire' => 0);

        $redis->set($vcKey, json_encode($data));
        $redis->expire($vcKey, self::EXPIRE_SEC); // 设置验证码过期时间

        $content = sprintf("您的验证码是：%s，如不是您本人操作，请忽略",$vc);
        //添加一个params参数
        $this->send($mobile,[$vc],'verifyCode');

        // 重设重发时限
        $data['resend_expire'] = time() + self::RESEND_SEC;
        $ttl = $redis->ttl($vcKey);
        $redis->set($vcKey, json_encode($data));
        $redis->expire($vcKey, $ttl);

    }

    /**
     * 向指定手机号发送短信
     * @param $mobile
     * @param $content
     * @return bool
     */
    function send($phone,$params,$smsType){
        $sms = new SMS\Sms($phone, $smsType, $params);
        $r = $sms->sendMessage();
//        dd($r);
        if(!$r){
            throw new \Exception('发送失败',-6);
        };
//        $mobiles = array($phone);
//        //Web Service接口地址
//        $client = new \SoapClient(app('config')->get('sms.api.url'));
//
//        $serialNumber = app('config')->get('sms.api.ac');
//        $password = app('config')->get('sms.api.key');
//        $sessionKey = '';
//        $paras=new SMSClient($serialNumber,$password,$sessionKey,$mobiles,$content,'',0,5,5);
//        $result=$client->sendSMS($paras);
//        if(0 != $result->return)
//            throw new \Exception('发送失败',-6);
    }

    /**
     * 验证短信验证码
     * @param $mobile
     * @param $vc
     * @return bool
     */
    public function checkVerifyCode($mobile, $vc) {
        $vcKey = 'VC_'.$mobile;
        $vcData = json_decode(app('rdb')->get($vcKey), true);
        if($vcData && $vcData['vc'] === $vc)
            return true;
        return false;
    }

    /**
     * 清除验证码
     * @param $mobile
     */
    public function cleanVerifyCode($mobile) {
        $redis = app('rdb');
        $vcKey = 'VC_'.$mobile;
        $limitKey = 'VC_LIMIT_'.$mobile;
        $redis->del($vcKey);
        $redis->del($limitKey);
    }
}


/*
 *发送短信参数的实体类
 */
class SMSClient{
    var $arg0 = "";
    var $arg1 = "";
    var $arg2 = "";
    var $arg3 = "";
    var $arg4 = "";
    var $arg5 = "";
    var $arg6 = "";
    var $arg7 = "";
    var $arg8 = "";
    function __construct($serialNumber,$password,$sessionKey='',$mobiles,$content,$arg5,$arg6=0,$arg7=5,$arg8=5){
        $this->arg0=$serialNumber;
        $this->arg1=$password;
        $this->arg2=$sessionKey;
        $this->arg3=$mobiles;
        $this->arg4=$content;
        $this->arg5=$arg5;
        $this->arg6=$arg6;
        $this->arg7=$arg7;
        $this->arg8=$arg8;
    }
}