<?php

/*
 店员接口路由
'prefix' => 'm','namespace' => 'Staff'

例 下面这条路由写法
$app->get('get.do',['as' => 'wechat.server', 'uses' => 'StaffController@server']);

访问地址 /s/get.do
namespace /App/Http/Controllers/Apps/Staff


*/
//增加自动同步代码
$app->get('/', function () use ($app) {
    return 'helo globals';
});
$app->post('upToken',['uses'=>'GlobalsController@upToken']);//获取七牛上传token
