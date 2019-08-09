<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('admin.home');
    $router->resource('users', UsersController::class);
    $router->resource('auth/users', UserController::class);
    $router->resource('stage', StageController::class);
    $router->resource('tools', ToolsController::class);
    $router->resource('broadcast', BroadcastController::class);
    $router->resource('device', DeviceController::class);
    $router->resource('config', ConfigController::class);
    $router->resource('signIn', SignInController::class);
    $router->resource('constellation', ConstellationController::class);
    $router->resource('poetry', PoetryController::class);
    $router->resource('giftBag', GiftBagController::class);
    $router->resource('giftBagTools', GiftBagToolsController::class);
    $router->resource('signInSetting', SignInSettingController::class);
    $router->resource('account', UserAccountController::class);
    $router->resource('backPack', BackPackController::class);
    $router->resource('giftLog', UserGiftController::class);
    $router->resource('passLog', StagePassLogController::class);
    $router->resource('toolsLog', UserToolsController::class);
    $router->resource('randName', RandNameController::class);
    $router->resource('sensitive_word', SensitiveWordController::class);
    $router->resource('installApk', InstallApkController::class);
    $router->resource('bbs', BbsController::class);
    $router->resource('idiom', IdiomController::class);
    $router->resource('idioms', IdiomsController::class);
    $router->resource('exchange', ExchangeController::class);
    $router->resource('userExchangeLog', UserExchangeController::class);

});

