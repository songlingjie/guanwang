<?php

namespace App\Http\Controllers\Apps\User;

use App\Library\Api_Sms;
use EasyWeChat\Foundation\Application;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User\User;

class AuthController extends Controller
{
    const PASSWORDLENGTH = 6;//密码最小位数
    /**
     * @apiUrl /u/login [post] 用户登录接口
     */
    public function login(Request $request)
    {
        if(!$request->has('username'))
            throw new \Exception('请填写用户名','-101');
        if(!$request->has('password'))
            throw new \Exception('请填写密码','-102');

        $user = app('db')->table('shop_member')->where('phone',trim($request->username))
            ->where('password',md5($request->password))->where('status',1)->first();

        if($user === null)
            throw new \Exception('用户名或密码错误','-13');

        $userId = $user->id;
        $groupId = $user->groupId;
        $rsa = app('rsaAuth');
        $token = $rsa->crtToken($userId);
        $createTime = time();

        return $this->respon(compact('groupId','token','createTime'));
    }

    /**
     * @param @apiUrl {post} /s/password/reset
     * @apiDesc 重置密码，
     * @throws
     * @return array
     */
    public function resetPsd(Request $request,Api_Sms $api_Sms)
    {
        if (!$this->userId)
            throw new \Exception('请先登录', 1);

        if (!$request->has('code'))
            throw new \Exception('请填写手机效验码', -10);

        if (!$request->has('password') || !$request->has('passwordConfirmation'))
            throw new \Exception('请填写新密码', -11);

        if ($request->password !== $request->passwordConfirmation)
            throw new \Exception('两次密码输入不一致', -12);

        if (self::PASSWORDLENGTH > mb_strlen($request->password))
            throw new \Exception(sprintf('密码最少%d位', self::PASSWORDLENGTH), -13);

        //验证手机效验码
        $phone = app('db')->table('shop_member')->where('id',$this->userId)->value('phone');
        if (false === $api_Sms->checkVerifyCode($phone, $request->code))
            throw new \Exception('验证码不正确', -14);

        $password =md5($request->password);

        $userRepository = new User;
        $userRepository->setUserId($this->userId);
        $userRepository->setUserValue(compact('password'));

        unset($userRepository, $request, $password, $authMd5);

        return $this->respon('修改成功');
    }

    /**
     * @param @apiUrl {post} /s/sensCode
     * @apiDesc 发送手机效验码，
     * @throws
     * @return array
     */
    public function _sendCode()
    {
        return $this->respon('发送成功');
    }

    /**
     * @apiUrl /api/sendCode
     * @throws
     * @apiDesc 发送手机验证码
     * @apiParam {string} username {必须} 手机号
     * @apiParam {int} type 可选 1为验证用户是否存在 2为验证用户是否不存在 为0则不验证
     * @return array
     */
    public function sendCode(Request $request, Api_Sms $api_Sms)
    {

        $type = $request->get('type', 2);

        if (!$request->has('phone'))
            throw new \Exception('手机号码为空', -11);

        if (!\App\Helpers::isMobile($request->phone))
            throw new \Exception('手机号码不正确', -12);

        if (1 == $type && $this->isReg($request->phone))
            throw new \Exception('手机号已注册', -13);

        if (2 == $type && !$this->isReg($request->phone))
            throw new \Exception('用户不存在', -15);


        $api_Sms->setMobile($request->phone);
        $api_Sms->sendVerifyCode();

        unset($api_Sms, $request, $type);

        return $this->respon('发送成功');

    }

    public function forgetPassword(Request $request,Api_Sms $api_Sms)
    {
        if(!\App\Helpers::isMobile($request->phone))
            throw new \Exception('手机号码不正确', -9);
        if (!$request->has('code'))
            throw new \Exception('请填写手机效验码', -10);

        if (!$request->has('password') || !$request->has('passwordConfirmation'))
            throw new \Exception('请填写新密码', -11);

        if ($request->password !== $request->passwordConfirmation)
            throw new \Exception('两次密码输入不一致', -12);

        if (self::PASSWORDLENGTH > mb_strlen($request->password))
            throw new \Exception(sprintf('密码最少%d位', self::PASSWORDLENGTH), -13);

        //验证手机效验码
        if (false === $api_Sms->checkVerifyCode($request->phone, $request->code))
            throw new \Exception('验证码不正确', -14);

        $userId  = app('db')->table('shop_member')->where('phone',$request->phone)->value('id');
        if($userId === null)
            throw new \Exception('用户不存在', -15);

        $password =md5($request->password);

        $userRepository = new User;
        $userRepository->setUserId($userId);
        $userRepository->setUserValue(compact('password'));

        unset($userRepository, $request, $password, $authMd5);

        return $this->respon('修改成功');
    }
}