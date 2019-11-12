<?php
use Illuminate\Http\Request;
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

    // $router->get('/user/info', function () use ($router) {
    //     $user = [
    //         "roles" => ['admin'],
    //         "introduction" => 'I am a super administrator',
    //         "avatar" => 'https://wpimg.wallstcn.com/f778738c-e4f8-4870-b634-56703b4acafe.gif',
    //         "name" => 'Super Admin',
    //     ];
    //     return $user;
    // });

    $router->get('/option111', function (Request $request) use ($router) {
        $ins = $request->input('ins');
        $ins = explode(',', $ins);
        $output = [];
        $defines = [
            'status' => [
                'Normal' => '正常',
                'Close' => '关闭',
            ],
        ];
        foreach ($ins as $in) {
            isset($defines[$in]) && $output[$in] = $defines[$in];
        }
        return $output;
    });

    $router->get('/option', 'OptionController@getOption');
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
