<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {


        //o_b0a1cah1F3PSyS_YoPNNVgn92M 小宋的openId
        //o_b0a1ZbZdJrurFOnUB9PbGujD4M 浩栋的openId

        //parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        //o_b0a1cah1F3PSyS_YoPNNVgn92M小宋的openId
        //o_b0a1ZbZdJrurFOnUB9PbGujD4M 浩栋的openId
        /*
        if(0 == $e->getCode()){
            try{
                //这块是给小宋发送异常提醒。
                foreach (['o_b0a1cah1F3PSyS_YoPNNVgn92M','o_b0a1ZbZdJrurFOnUB9PbGujD4M'] as $openId){
                    $cnf = \App\Conf::getGJWxConf();
                    $app = new \EasyWeChat\Foundation\Application($cnf);
                    $staff = $app->staff; // 客服管理
                    $text = new \EasyWeChat\Message\Text;
                    $text->content = sprintf('焕熊管家：系统出现异常，文件:%s 第 %s 行 , 异常内容 : %s  , path: %s  , params : %s , clientIp : %s , method: %s ',$e->getFile(),$e->getLine(),$e->getMessage(),$request->path(),json_encode($request->all()),$request->getClientIp(),$request->method());
                    $staff->message($text)->to($openId)->send();
                }
            }catch (\Exception $e){

            }
        }
        */
        $code = 0 == $e->getCode() ? -1 : $e->getCode();
        $errMsg = !empty($e->getMessage())? $e->getMessage():'no page';
        \App\Helpers::errLog($e,$request);
        return compact('code','errMsg');
//        return parent::render($request, $e);
    }
}
