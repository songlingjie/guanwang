<?php

require_once __DIR__.'/../vendor/autoload.php';

try {
    (new Dotenv\Dotenv(__DIR__.'/../'))->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    //
}

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    realpath(__DIR__.'/../')
);

// $app->withFacades();

// $app->withEloquent();

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

// $app->middleware([
//    App\Http\Middleware\ExampleMiddleware::class
// ]);

 $app->routeMiddleware([
     'auth' => App\Http\Middleware\Authenticate::class,
     'Browse' => App\Http\Middleware\Browse::class,
 ]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

// $app->register(App\Providers\AppServiceProvider::class);
$app->register(App\Providers\AuthServiceProvider::class);
// $app->register(App\Providers\EventServiceProvider::class);

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/



//注册rdb为redis
$app->singleton('rdb', function(){
    $redis = new Redis;
    $redis->pconnect('127.0.0.1',6379); //建立连接
    $redis->select(1); //选择库
    // $redis->auth('xxxx'); //认证
    return $redis;
});

$app->singleton('rsa', function () {
    return new \App\Library\Api_Rsa;
});
$app->singleton('rsaAuth', function () {
    return new \App\Library\Api_Auth;
});

$app->group(['namespace' => 'App\Http\Controllers\Apps','middleware' => 'Browse'], function ($app) {
    $app->group(['prefix' => 'wx','namespace' => 'WeChat'], function ($app) {
        require __DIR__ . '/../app/Http/Routes/routes_wechat.php';
    });
    $app->group(['prefix' => 'g','namespace' => 'Globals'], function ($app) {
        require __DIR__ . '/../app/Http/Routes/routes_global.php';
    });
    $app->group(['prefix' => 'i','namespace' => 'Ident'], function ($app) {
        require __DIR__ . '/../app/Http/Routes/routes_ident.php';
    });
    $app->group(['prefix' => 'u','namespace' => 'User'], function ($app) {
        require __DIR__ . '/../app/Http/Routes/routes_user.php';
    });
    $app->group(['prefix' => 'm','namespace' => 'Manager'], function ($app) {
        require __DIR__ . '/../app/Http/Routes/routes_manager.php';
    });
    $app->group(['prefix' => 's','namespace' => 'Staff'], function ($app) {
        require __DIR__ . '/../app/Http/Routes/routes_staff.php';
    });
    $app->group(['prefix' => '/','namespace' => 'Normal'], function ($app) {
        require __DIR__ . '/../app/Http/Routes/routes_normal.php';
    });
});

return $app;
