<?php

namespace App\Http\Controllers;

use App\Helpers\Tools;
use App\Http\Resources\UserInfo as UserInfoResource;
use App\Models\ActionLog;
use App\Models\UserInfo;
use App\Rules\Chinese;
use App\Rules\Idcard;
use App\Rules\Mobile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserInfoController extends Controller
{
    public function getUserInfo(Request $request, $id)
    {
        $id = Tools::getIdByHash($id);
        $userInfo = UserInfo::where('user_id', $id)->firstOrFail();
        return (new UserInfoResource($userInfo))->additional(['meta' => ['message' => '查询成功']]);
    }

    public function putUserInfo(Request $request, $id)
    {
        $id = Tools::getIdByHash($id);
        $sex = array_keys(config('options.sex'));
        $userInfo = UserInfo::where('user_id', $id)->firstOrFail();
        $this->validate($request, [
            'qq' => 'nullable|integer',
            'weixin' => 'nullable|alpha_dash',
            'mobile' => ['nullable', new Mobile, Rule::unique('user_info')->ignore($userInfo->id)],
            'mobile_area' => 'nullable',
            'email' => ['nullable', Rule::unique('user_info')->ignore($userInfo->id)],
            'avatar' => 'nullable|url',
            'sex' => [Rule::in($sex)],
            'nickname' => 'required|alpha_dash',
            'real_name' => ['nullable', new Chinese, 'min:2', 'max:10'],
            'idcard' => ['nullable', new Idcard, Rule::unique('user_info')->ignore($userInfo->id)],
        ]);

        $oldResource = new UserInfoResource($userInfo);
        $collection = collect($oldResource->toArray($request));
        $data = $userInfo->encrypt($request->only(['qq',
            'idcard', 'weixin', 'email', 'real_name', 'idcard',
            'mobile', 'mobile_area', 'sex', 'avatar']));
        $userInfo->fill($data)->save();

        $resource = new UserInfoResource($userInfo);
        $diff = $collection->diff($resource->toArray($request));
        if (!empty($diff)) {
            ActionLog::create([
                'user_id' => $id,
                'action_user_id' => Auth::user()->id,
                'module_id' => $request['menu']['id'],
                'diff' => json_encode($diff, JSON_UNESCAPED_UNICODE),
                'mark' => '修改个人信息',
                'ip' => $request->ip(),
            ]);
        }
        return $resource->additional(['meta' => [
            'message' => '修改成功',
        ]]);
    }
}
