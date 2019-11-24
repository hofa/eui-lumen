<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidatorException;
use App\Helpers\Tools;
use App\Http\Resources\UserBank as UserBankResource;
use App\Models\ActionLog;
use App\Models\User;
use App\Models\UserBank;
use App\rules\BankNum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserBankController extends Controller
{
    public function deleteUserBank(Request $request, $id)
    {
        $id = Tools::getIdByHash($id);
        $userBank = UserBank::findOrFail($id);
        $userBank->delete();
        $resource = new UserBankResource($userBank);
        ActionLog::create([
            'user_id' => $userBank->user_id,
            'action_user_id' => Auth::user()->id,
            'module_id' => $request['menu']['id'],
            'diff' => json_encode($resource->toArray($request), JSON_UNESCAPED_UNICODE),
            'mark' => '删除用户银行卡',
            'ip' => $request->ip(),
        ]);
        return $resource->additional(['meta' => ['message' => '删除成功']]);
    }

    public function postUserBank(Request $request)
    {
        $yn = array_keys(config('options.yn'));
        $this->validate($request, [
            'username' => 'required|exists:user',
            'bank_name' => 'required',
            'bank_branch' => 'required',
            'bank_card' => ['required', new BankNum],
            'real_name' => 'required',
            'is_default' => Rule::in($yn),
        ]);

        $user = User::where('username', $request->input('username'))->first();
        $data = $request->only([
            'bank_name',
            'bank_branch',
            'bank_card',
            'real_name',
            'is_default',
        ]);
        $data['user_id'] = $user->id;
        $data['bank_card'] = Tools::encrypt($data['bank_card']);

        $card = UserBank::where('bank_card', $data['bank_card'])->first();
        if (!empty($card)) {
            ValidatorException::setError('bank_card', '银行卡号已存在');
        }

        $isDefault = $request->input('is_default');
        if ($isDefault == 'Yes') {
            UserBank::where('user_id', $user->id)->update(['is_default' => 'No']);
        }
        $userBank = UserBank::create($data);
        $resource = new UserBankResource($userBank);
        ActionLog::create([
            'user_id' => $data['user_id'],
            'action_user_id' => Auth::user()->id,
            'module_id' => $request['menu']['id'],
            'diff' => json_encode($resource->toArray($request), JSON_UNESCAPED_UNICODE),
            'mark' => '新增用户银行卡',
            'ip' => $request->ip(),
        ]);
        return $resource->additional(['meta' => ['message' => '创建成功']]);
    }

    public function putUserBank(Request $request, $id)
    {
        $id = Tools::getIdByHash($id);
        $yn = array_keys(config('options.yn'));
        $userBank = UserBank::findOrFail($id);
        $this->validate($request, [
            // 'username' => 'required|exists:user',
            'bank_name' => 'required',
            'bank_branch' => 'required',
            // 'bank_card' => ['required', Rule::unique('user_bank')->ignore($id), new BankNum],
            'bank_card' => ['required', new BankNum],
            'real_name' => 'required',
            'is_default' => Rule::in($yn),
        ]);
        $data = $request->only([
            'bank_name',
            'bank_branch',
            'bank_card',
            'real_name',
            'is_default',
        ]);
        $data['bank_card'] = Tools::encrypt($data['bank_card']);
        $card = UserBank::where('bank_card', $data['bank_card'])->Where('id', '!=', $id)->first();
        if (!empty($card)) {
            ValidatorException::setError('bank_card', '银行卡号已存在');
        }
        $oldResource = new UserBankResource($userBank);
        $collection = collect($oldResource->toArray($request));
        $isDefault = $request->input('is_default');
        if ($isDefault == 'Yes') {
            UserBank::where('user_id', $userBank->user_id)->update(['is_default' => 'No']);
        }
        $userBank->fill($data)->save();
        $resource = new UserBankResource($userBank);
        $diff = $collection->diff($resource->toArray($request));
        if (!empty($diff)) {
            ActionLog::create([
                'user_id' => $userBank->user_id,
                'action_user_id' => Auth::user()->id,
                'module_id' => $request['menu']['id'],
                'diff' => json_encode($diff, JSON_UNESCAPED_UNICODE),
                'mark' => '修改用户银行卡',
                'ip' => $request->ip(),
            ]);
        }
        return (new UserBankResource($userBank))->additional(['meta' => ['message' => '修改成功']]);
    }

    public function getUserBank(Request $request)
    {
        $userBank = new UserBank;
        $bankCard = $request->input('bank_card');
        $username = $request->input('username');
        $bankCard && $userBank = $userBank->where('bank_card', Tools::encrypt($bankCard));

        if (!empty($username)) {
            $userBank = $userBank->wherehas('user', function ($r) use ($username) {
                return $r->where('username', $username);
            });
        }
        $sort = $request->input('sort');
        if (!empty($sort)) {
            $sortField = trim(str_replace(['*', '-'], '', $sort));
            $sortBy = $sort[0] == '*' ? 'asc' : 'desc';
            $userBank = $userBank->orderBy($sortField, $sortBy);
        }
        $pageSize = $request->input('psize', 20);
        $data = $userBank->paginate($pageSize)->appends($request->query());

        return UserBankResource::collection($data);
    }
}
