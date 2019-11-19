<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidatorException;
use App\Models\LoginLog;
use App\Models\User;
use App\Models\UserInfo;
use App\Rules\Username;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Tymon\JWTAuth\JWTAuth;

class AuthController extends Controller
{
    protected $jwt;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
    }

    public function postLogin(Request $request)
    {
        $this->validate($request, [
            'username' => ['required', new Username],
            'password' => 'required',
        ]);
        $random = Str::random(10);
        $user = User::where('username', $request->input('username'))->first();
        $loginLog = new LoginLog;
        if ($loginLog->getIPError($request->ip())) {
            LoginLog::create([
                'username' => $request->input('username'),
                'ip' => $request->ip(),
                'user_id' => 0,
                'status' => 'Fail',
                'type' => 'IPLimit',
            ]);
            ValidatorException::setError('username', '你的IP登录异常,48小时内被限制登录');
        }

        if (empty($user)) {
            LoginLog::create([
                'username' => $request->input('username'),
                'ip' => $request->ip(),
                'user_id' => 0,
                'status' => 'Fail',
                'type' => 'Username',
            ]);
            ValidatorException::setError('username', '账号不存在');
        }

        if ($user->status == 'Close') {
            LoginLog::create([
                'username' => $request->input('username'),
                'ip' => $request->ip(),
                'user_id' => 0,
                'status' => 'Fail',
                'type' => 'Close',
            ]);
            ValidatorException::setError('username', '账号已被停用');
        }

        if ($loginLog->getPasswordError($user->username)) {
            LoginLog::create([
                'username' => $request->input('username'),
                'ip' => $request->ip(),
                'user_id' => 0,
                'status' => 'Fail',
                'type' => 'PasswordLimit',
            ]);
            ValidatorException::setError('username', '账号密码错误次数过多,48小时内无法登录');
        }

        if (!$token = $this->jwt->customClaims(['rand' => $random])->attempt($request->only('username', 'password'))) {
            LoginLog::create([
                'username' => $request->input('username'),
                'ip' => $request->ip(),
                'user_id' => 0,
                'status' => 'Fail',
                'type' => 'Password',
            ]);
            ValidatorException::setError('username', '用户不存在或者密码错误');
        }

        LoginLog::create([
            'username' => $user->username,
            'ip' => $request->ip(),
            'user_id' => $user->id,
            'status' => 'Succ',
            'type' => 'Normal',
        ]);
        Redis::setex('device:' . $user->id, Auth::factory()->getTTL() * 60, $random);
        // Event::fire(new LoginEvent($this->jwt->user()));
        $loginLog->decrError($request->ip(), $user->username);
        UserInfo::where('user_id', $user->id)->update([
            'last_login_time' => date('Y-m-d H:i:s'),
            'last_login_ip' => $request->ip(),
        ]);
        return [
            'data' => [
                'token' => $token,
                'tokenType' => 'bearer',
                'expiresIn' => Auth::factory()->getTTL() * 60,
            ],
            'meta' => [
                'message' => '登录成功',
            ],
        ];
    }

    public function postLogout(Request $request)
    {
        try {
            $this->jwt->parseToken()->invalidate();
        } catch (\Exception $e) {

        }
        return ['meta' => ['message' => '退出成功']];
    }

    public function postRefresh(Request $request)
    {
        Redis::expire('device:' . $this->jwt->user()->id, Auth::factory()->getTTL() * 60);
        return [
            'data' => [
                'token' => $this->jwt->parseToken()->refresh(),
                'tokenType' => 'bearer',
                'expiresIn' => Auth::factory()->getTTL() * 60,
            ],
            'meta' => [
                'message' => '刷新TOKEN成功',
            ],
        ];
    }
}
