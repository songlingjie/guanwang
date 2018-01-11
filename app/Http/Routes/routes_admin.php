<?php

/*
用户接口路由
默认配置 'prefix' => 'u','namespace' => 'User'


例 下面这条路由写法
$app->get('get.do',['as' => 'wechat.server', 'uses' => 'UserController@server']);

访问地址 /u/get.do
namespace /App/Http/Controllers/Apps/User


*/

$app->get('/', function () use ($app) {
    return 'helo user';
});

$app->post('login',['as' => 'tweet.create', 'uses' => 'AuthController@login']);

$app->post('/sendUserPay',function(){
    $uid = 8;
    $orderId = 197;
    if(!is_null($clientId = \App\Helpers::uidToClientId($uid))) {
        $msg = [
            'type' => 'confimOrder',
            'data' => [
                'postId' => $orderId,
            ]
        ];

        \App\Helpers::sendMsg($clientId, $msg);
        dd('success');
    }else{
        dd('error');
    }

});

$app->post('/sendUserPayStateOk',function(){
    $uid = 1;
    $orderId = 197;
    if(!is_null($clientId = \App\Helpers::uidToClientId($uid))) {
        $msg = [
            'type' => 'OrderPayStateOk',
            'data' => [
                'orderId' => $orderId,
            ]
        ];

        \App\Helpers::sendMsg($clientId, $msg);
        dd('success');
    }else{
        dd('error');
    }
});
$app->post('/sendUserPayStateFail',function(){
    $uid = 8;
    $orderId = 197;
    if(!is_null($clientId = \App\Helpers::uidToClientId($uid))) {
        $msg = [
            'type' => 'OrderPayStateFail',
            'data' => [
                'orderId' => $orderId,
            ]
        ];

        \App\Helpers::sendMsg($clientId, $msg);
        dd('success');
    }else{
        dd('error');
    }
});

//验证登录的接口
$app->group(['middleware' => 'auth'],function() use ($app){
    $app->post('/password/reset',['as=>password.reset','uses'=>'AuthController@resetPsd']);//重置密码

    $app->post('/user/center',['as'=>'u.user.first','uses'=>'UserController@center']);//个人中心数据
    $app->post('/user/readMsg',['as'=>'u.message.read','uses'=>'UserController@readMsg']);//更新消息状态
    $app->post('/user/first',['as'=>'u.user.first','uses'=>'UserController@userInfo']);//获取个人资料

    $app->post('/money/first',['as'=>'u.money.first','uses'=>'UserController@userMoney']);//获取钱包信息
    $app->post('/money/get',['as'=>'u.money.get','uses'=>'UserController@moneyList']);//获取钱包记录
    $app->post('/money/cash',['as'=>'u.money.get','uses'=>'UserController@cash']);//提现

    $app->post('/message/first',['as'=>'u.message.get','uses'=>'UserController@messageInfo']);//消息详情
    $app->post('/message/get',['as'=>'u.money.get','uses'=>'UserController@messageList']);//获取消息记录

    $app->post('/order/first',['as=>u.order.first','uses'=>'OrderController@getOrderInfo']);//订单详情
    $app->post('/order/get',['as=>u.order.first','uses'=>'OrderController@getOrderList']);//订单列表
    $app->post('/orderLog/get',['as=>u.order.first','uses'=>'OrderController@getOrderLog']);//订单日志

    $app->post('/wechat/first',['as=>u.wechat.first','uses'=>'UserController@getWechatInfo']);//获取微信绑定数据
    $app->post('/wechat/bind',['as=>u.wechat.bind','uses'=>'UserController@bindWechat']);//绑定微信
});

$app->post('/sensCode',['as=>u.send.code','uses'=>'AuthController@sendCode']);//发送手机效验码
$app->post('/order/invalid',['as=>u.order.invalid','uses'=>'OrderController@invalid']);//发送手机效验码
$app->post('/password/forget',['as=>u.password.forget','uses'=>'AuthController@forgetPassword']);//忘记密码
$app->get('/test',function(){
    $db = app('db');
    $logs = $db->table('order_log')->where('desc','like','%付款成功%')->select()->get();
    foreach ($logs as $v)
    {
        $db->table('order')->where('order_id',$v->order_id)->update([
            'payment_time'=>strtotime($v->created_at),
        ]);
    }
});

//用户端操作接口
//$app->post('ident/init',['as' => 'tweet.create', 'uses' => 'Apps\IdentController@index']);
//$app->post('ident/first',['as' => 'tweet.create', 'uses' => 'Apps\IdentController@first']);

