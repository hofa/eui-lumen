<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidatorException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            'username' => 'required',
            'password' => 'required',
        ]);

        if (!$token = $this->jwt->attempt($request->only('username', 'password'))) {
            // $validator->errors()->add('username', 'Something is wrong with this field!');
            // return response()->json(['user_not_found'], 404);
            // $error = \Illuminate\Validation\ValidationException::withMessages([
            //     'username' => ['用户不存在或者密码错误'],
            //     // 'field_name_2' => ['Validation Message #2'],
            // ]);
            // throw $error;
            // $validator = Validator::make([], []); // Empty data and rules fields
            // $validator->errors()->add('username', '用户不存在或者密码错误');
            // throw new \Illuminate\Validation\ValidationException($validator);
            // throw \Illuminate\Validation\ValidationException::withMessages([
            //     'username' => '用户不存在或者密码错误',
            // ]);
            // return response()->json(['username' => "用户不存在或者密码错误"], 422);
            ValidatorException::setError('username', '用户不存在或者密码错误');
        }

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

    // public function refreshToken(Request $request)
    // {
    //     return response()->json([
    //         'token' => $token,
    //         'tokenType' => 'bearer',
    //         'expiresIn' => Auth::factory()->getTTL() * 60,
    //     ]);
    // }

    public function postLogout(Request $request)
    {
        // Auth::logout();
        try {
            $this->jwt->parseToken()->invalidate();
        } catch (\Exception $e) {

        }
        // app('jwt.refresh');
        return ['meta' => ['message' => '退出成功']];
    }

    public function postRefresh(Request $request)
    {
        return [
            'data' => [
                'token' => $this->jwt->parseToken()->refreshToken(),
                'tokenType' => 'bearer',
                'expiresIn' => Auth::factory()->getTTL() * 60,
            ],
            'meta' => [
                'message' => '刷新TOKEN成功',
            ],
        ];
    }
}
