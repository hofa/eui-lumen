<?php

namespace App\Http\Controllers;

use App\Helpers\Tools;
use App\Http\Resources\UserAddress as UserAddressResource;
use App\Models\ActionLog;
use App\Models\User;
use App\Models\UserAddress;
use App\Rules\Mobile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserAddressController extends Controller
{
    public function deleteUserAddress(Request $request, $id)
    {
        $id = Tools::getIdByHash($id);
        $userAddress = UserAddress::findOrFail($id);
        $userAddress->delete();
        $resource = new UserAddressResource($userAddress);
        ActionLog::create([
            'user_id' => $userAddress->user_id,
            'action_user_id' => Auth::user()->id,
            'module_id' => $request['menu']['id'],
            'diff' => json_encode($resource->toArray($request), JSON_UNESCAPED_UNICODE),
            'mark' => '删除地址',
            'ip' => $request->ip(),
        ]);
        return $resource->additional(['meta' => ['message' => '删除成功']]);
    }

    public function postUserAddress(Request $request)
    {
        $yn = array_keys(config('options.yn'));
        $this->validate($request, [
            'province' => 'required',
            'city' => 'required',
            'area' => 'required',
            'street' => 'required',
            'mobile' => ['required', new Mobile],
            'recipient' => 'required',
            'address' => 'required',
            'username' => 'required',
            'is_default' => ['required', Rule::in($yn)],
        ]);
        $user = User::where('username', $request->input('username'))->first();
        $data = $request->only([
            'province',
            'city',
            'area',
            'street',
            'mobile',
            'recipient',
            'address',
            'is_default',
        ]);
        $data['user_id'] = $user->id;
        $data['mobile'] = Tools::encrypt($data['mobile']);
        $isDefault = $request->input('is_default');
        if ($isDefault == 'Yes') {
            UserAddress::where('user_id', $user->id)->update(['is_default' => 'No']);
        }
        $userAddress = UserAddress::create($data);
        $resource = new UserAddressResource($userAddress);
        ActionLog::create([
            'user_id' => $user->id,
            'action_user_id' => Auth::user()->id,
            'module_id' => $request['menu']['id'],
            'diff' => json_encode($resource->toArray($request), JSON_UNESCAPED_UNICODE),
            'mark' => '新增地址',
            'ip' => $request->ip(),
        ]);
        return $resource->additional(['meta' => ['message' => '创建成功']]);
    }

    public function putUserAddress(Request $request, $id)
    {
        $id = Tools::getIdByHash($id);
        $userAddress = UserAddress::findOrFail($id);
        $yn = array_keys(config('options.yn'));
        $this->validate($request, [
            'province' => 'required',
            'city' => 'required',
            'area' => 'required',
            'street' => 'required',
            'mobile' => ['required', new Mobile],
            'recipient' => 'required',
            'address' => 'required',
            'username' => 'required',
            'is_default' => ['required', Rule::in($yn)],
        ]);
        $user = User::where('username', $request->input('username'))->first();
        $data = $request->only([
            'province',
            'city',
            'area',
            'street',
            'mobile',
            'recipient',
            'address',
            'is_default',
        ]);
        $data['user_id'] = $user->id;
        $data['mobile'] = Tools::encrypt($data['mobile']);
        $isDefault = $request->input('is_default');
        if ($isDefault == 'Yes') {
            UserAddress::where('user_id', $user->id)->update(['is_default' => 'No']);
        }
        $oldResource = new UserAddressResource($userAddress);
        $collection = collect($oldResource->toArray($request));
        $userAddress->fill($data)->save();
        $resource = new UserAddressResource($userAddress);
        $diff = $collection->diff($resource->toArray($request));
        if (!empty($diff)) {
            ActionLog::create([
                'user_id' => $user->id,
                'action_user_id' => Auth::user()->id,
                'module_id' => $request['menu']['id'],
                'diff' => json_encode($diff, JSON_UNESCAPED_UNICODE),
                'mark' => '修改地址',
                'ip' => $request->ip(),
            ]);
        }
        return (new UserAddressResource($userAddress))->additional(['meta' => ['message' => '修改成功']]);
    }

    public function getUserAddress(Request $request)
    {
        $userAddress = new UserAddress;
        $username = $request->input('username');
        $recipient = $request->input('recipient');
        $mobile = $request->input('mobile');

        $request->input('recipient') && $userAddress = $userAddress->where('recipient', $recipient);
        if (!empty($username)) {
            $userAddress = $userAddress->wherehas('user', function ($r) use ($username) {
                return $r->where('username', $username);
            });
        }
        $sort = $request->input('sort');
        if (!empty($sort)) {
            $sortField = trim(str_replace(['*', '-'], '', $sort));
            $sortBy = $sort[0] == '*' ? 'asc' : 'desc';
            $userAddress = $userAddress->orderBy($sortField, $sortBy);
        }
        $pageSize = $request->input('psize', 20);
        $data = $userAddress->paginate($pageSize)->appends($request->query());
        return UserAddressResource::collection($data);
    }
}
