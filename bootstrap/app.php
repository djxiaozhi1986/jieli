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

 $app->withFacades();

 $app->withEloquent();

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
$app->middleware([
    \Illuminate\Session\Middleware\StartSession::class,
    \App\Http\Middleware\CorsMiddleware::class,
]);
$app->routeMiddleware([
    'wechat.oauth'=>\App\Http\Middleware\WechatMiddleware::class,
]);
// $App->middleware([
//    App\Http\Middleware\ExampleMiddleware::class
// ]);

// $App->routeMiddleware([
//     'auth' => App\Http\Middleware\Authenticate::class,
// ]);

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
$app->configure('C');
$app->configure('database');
//增加邮件发送
//$App->configure('mail');
$app->configure('wechat');
$app->configure('session');
$app->register(Illuminate\Session\SessionServiceProvider::class);
// $App->register(Maatwebsite\Excel\ExcelServiceProvider::class);
// $App->register(Illuminate\Mail\MailServiceProvider::class);
$app->register(Illuminate\Redis\RedisServiceProvider::class);
// class_alias('Illuminate\Support\Facades\Mail', 'Mail');
$app->alias('session', 'Illuminate\Session\SessionManager');

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

//$App->configureMonologUsing(function(Monolog\Logger $monoLog) use ($App){
//    return $monoLog->pushHandler(
//        new \Monolog\Handler\RotatingFileHandler($App->storagePath().'/logs/'.env('LOG_PREFIX').'.log',20)
//    );
//});
$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__.'/../routes/auth.php';
    require __DIR__.'/../routes/web.php';
    require __DIR__.'/../routes/wechat.php';
});

return $app;
