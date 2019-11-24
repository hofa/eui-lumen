<?php
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
 */

$router->get('/', function () use ($router) {
    Cache::put('lumen', 'Hello, Lumen.', 5);
    app('redis')->setex('a', 30, 'Hello, Lumen.');

    // $results = app('db')->select("SELECT * FROM user");
    // $results = DB::select("SELECT * FROM users");
    return $router->app->version();
});

// $router->get('/test', 'ExampleController@getUser');
// 使用 auth:api 中间件 'auth:api',
$router->group(['middleware' => ['permission:api']], function () use ($router) {
    $router->get('/test', 'ExampleController@getUser');

    $router->get('/roleMenu', 'RoleMenuController@getMenu');
    $router->get('/roleSidebar', 'RoleMenuController@getRoleSidebar');
    $router->get('/roleMenu3', 'RoleMenuController@getMenu3');

    $router->get('/role', 'RoleMenuController@getRole');
    $router->post('/role', 'RoleMenuController@postRole');
    $router->put('/role/{id}', 'RoleMenuController@putRole');
    $router->delete('/role/{id}', 'RoleMenuController@deleteRole');
    $router->patch('/role/permission/{id}', 'RoleMenuController@patchPermission');

    $router->get('/menu', 'MenuController@getMenu');
    $router->post('/menu', 'MenuController@postMenu');
    $router->put('/menu/{id}', 'MenuController@putMenu');
    $router->delete('/menu/{id}', 'MenuController@deleteMenu');

    $router->get('/user', 'UserController@getUser');
    $router->post('/user', 'UserController@postUser');
    $router->put('/user/{id}', 'UserController@putUser');
    $router->get('/user/permission', 'UserController@getPermission');
    $router->patch('/user/password', 'UserController@patchPassword');
    $router->get('/user/info', 'UserController@getUserInfo');

    $router->get('/user/info/{id}', 'UserInfoController@getUserInfo');
    $router->put('/user/info/{id}', 'UserInfoController@putUserInfo');

    $router->get('/setting', 'SettingController@getSetting');
    $router->post('/setting', 'SettingController@postSetting');
    $router->put('/setting/{id}', 'SettingController@putSetting');
    $router->delete('/setting/{id}', 'SettingController@deleteSetting');
    $router->patch('/setting/batchVal', 'SettingController@patchBatchValSetting');
    $router->post('/setting/refresh', 'SettingController@postRefresh');

    $router->get('/option', 'OptionController@getOption');

    $router->get('/loginLog', 'LoginLogController@getLoginLog');
    $router->post('/loginLog/unlock', 'LoginLogController@postUnlock');

    $router->get('/actionLog', 'ActionLogController@getActionLog');

    $router->post('/upload', 'UploadController@postUpload');

    $router->get('/IPBlackWhiteList', 'IPBlackWhiteListController@getIPBlackWhiteList');
    $router->post('/IPBlackWhiteList', 'IPBlackWhiteListController@postIPBlackWhiteList');
    // $router->put('/IPBlackWhiteList/{id}', 'IPBlackWhiteListController@putIPBlackWhiteList');
    $router->delete('/IPBlackWhiteList/{id}', 'IPBlackWhiteListController@deleteIPBlackWhiteList');
    $router->post('/IPBlackWhiteList/refresh', 'IPBlackWhiteListController@refreshIPBlackWhiteList');

    $router->get('/level', 'LevelController@getLevel');
    $router->post('/level', 'LevelController@postLevel');
    $router->put('/level/{id}', 'LevelController@putLevel');
    $router->delete('/level/{id}', 'LevelController@deleteLevel');

    $router->get('/channel', 'ChannelController@getChannel');
    $router->post('/channel', 'ChannelController@postChannel');
    $router->put('/channel/{id}', 'ChannelController@putChannel');
    $router->delete('/channel/{id}', 'ChannelController@deleteChannel');

    $router->get('/userBank', 'UserBankController@getUserBank');
    $router->post('/userBank', 'UserBankController@postUserBank');
    $router->put('/userBank/{id}', 'UserBankController@putUserBank');
    $router->delete('/userBank/{id}', 'UserBankController@deleteUserBank');

    $router->get('/userAddress', 'UserAddressController@getUserAddress');
    $router->post('/userAddress', 'UserAddressController@postUserAddress');
    $router->put('/userAddress/{id}', 'UserAddressController@putUserAddress');
    $router->delete('/userAddress/{id}', 'UserAddressController@deleteUserAddress');

    $router->get('/article', 'ArticleController@getArticle');
    $router->post('/article', 'ArticleController@postArticle');
    $router->put('/article/{id}', 'ArticleController@putArticle');
    $router->delete('/article/{id}', 'ArticleController@deleteArticle');

    $router->get('/atlas', 'AtlasController@getAtlas');
    $router->post('/atlas', 'AtlasController@postAtlas');
    $router->put('/atlas/{id}', 'AtlasController@putAtlas');
    $router->delete('/atlas/{id}', 'AtlasController@deleteAtlas');

    $router->get('/product', 'ProductController@getProduct');
    $router->post('/product', 'ProductController@postProduct');
    $router->put('/product/{id}', 'ProductController@putProduct');
    $router->delete('/product/{id}', 'ProductController@deleteProduct');
});

$router->group(['middleware' => ['auth:api', 'ipWhite']], function () use ($router) {
    $router->get('/ipWhite', 'ExampleController@getUser');
});

$router->post('/auth/login', 'AuthController@postLogin');
$router->post('/auth/logout', 'AuthController@postLogout');
$router->get('/user/permission/refresh', 'UserController@refreshPermission');
$router->post('/auth/refresh', 'AuthController@postRefresh');

$router->get('/websocket/test', 'WebSocketController@test');
$router->post('/websocket/bind', 'WebSocketController@bind');
$router->post('/websocket/send/{touuid}', 'WebSocketController@send');
$router->get('/websocket/customer', 'WebSocketController@customer');
$router->post('/websocket/room/{touuid}', 'WebSocketController@room');
$router->get('/websocket/history/{touuid}', 'WebSocketController@history');
$router->get('/websocket/online', 'WebSocketController@online');

$router->post('/websocket/customer/bind', 'WebSocketController@customerBind');
$router->post('/websocket/customer/send', 'WebSocketController@customerSend');
$router->get('/websocket/customer/history', 'WebSocketController@customerHistory');
