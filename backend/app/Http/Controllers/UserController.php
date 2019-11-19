<?php

namespace App\Http\Controllers;

use App\Helpers\Tools;
use App\Http\Resources\User as UserResource;
use App\Models\ActionLog;
use App\Models\Role;
use App\Models\User;
use App\Models\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function getUserInfo(Request $request)
    {
        $model = Auth::getUser();
        return ['data' => [
            'name' => $model->username,
            'avatar' => '',
        ]];
    }

    public function getPermission(Request $request)
    {
        $role = new Role;
        $model = Auth::getUser();
        $roleIds = $model->roles()->get()->pluck('id')->toArray();
        $data = $role->getCacheMenuByRoleId($roleIds)->toArray();
        $strs = [];
        foreach ($data as $k => $v) {
            $v = (object) $v;
            switch ($v->type) {
                case 'Menu':
                    $strs[] = 'M:' . $v->path;
                    break;
                case 'Node':
                    $strs[] = 'N:' . $v->request_type . ":" . $v->path;
                    break;
                case 'Virtual':
                    $strs[] = 'V:' . $v->path;
                    break;
            }
        }
        return ['data' => implode(';', $strs)];
    }

    public function refreshPermission(Request $request)
    {
        $role = new Role;
        $role->refreshCache();
        return ['meta' => ['message' => '刷新成功']];
    }

    public function postUser(Request $request)
    {
        $statusKeys = array_keys(config('options.status'));
        $this->validate($request, [
            'username' => 'required|unique:user',
            'password' => 'required|password',
            'status' => [
                'required',
                Rule::in($statusKeys),
            ],
        ]);
        $data = $request->only(['username', 'password', 'status']);
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
        $roleIds = (array) $request->input('role');
        foreach ($roleIds as $id) {
            $role = Role::find($id);
            $user->roles()->attach($role);
        }

        UserInfo::create([
            'user_id' => $user->id,
            'from_user_id' => Auth::user()->id,
            'register_ip' => $request->ip(),
            'nickname' => $request->input('username'),
        ]);

        $resource = new UserResource($user);
        ActionLog::create([
            'user_id' => $user->id,
            'action_user_id' => Auth::user()->id,
            'module_id' => $request['menu']['id'],
            'diff' => json_encode($resource->toArray($request), JSON_UNESCAPED_UNICODE),
            'mark' => '创建账号',
            'ip' => $request->ip(),
        ]);
        return $resource->additional(['meta' => [
            'message' => '创建成功',
        ]]);
    }

    public function putUser(Request $request, $id)
    {
        $id = Tools::getIdByHash($id);
        $user = User::findOrFail($id);
        $this->validate($request, [
            'status' => 'required',
        ]);

        $fillFields = ['status'];
        if (!empty($request->input('password'))) {
            $fillFields[] = 'password';
        }
        $data = $request->only($fillFields);
        if (!empty($request->input('password'))) {
            $data['password'] = Hash::make($request->input('password'));
        }
        $oldResource = new UserResource($user);
        // $collection = collect($oldResource->toArray($request));
        $user->fill($data)->save();
        $user->roles()->detach();
        $roleIds = (array) $request->input('role');
        foreach ($roleIds as $id) {
            $role = Role::find($id);
            $user->roles()->attach($role);
        }

        $resource = new UserResource($user);
        // $collection = collect($resource->toArray($request));
        $diff = $collection->diff($oldResource);
        if (!empty($request->input('password'))) {
            $password = $request->input('password');
            $start = mb_substr($password, 0, 2);
            $end = mb_substr($password, -1, 2);
            $length = mb_strlen($password) - 4;
            $password = $start . str_repeat('*', $length) . $end;
            $diff['password'] = $password;
        }

        if (!empty($diff)) {
            ActionLog::create([
                'user_id' => $user->id,
                'action_user_id' => Auth::user()->id,
                'module_id' => $request['menu']['id'],
                'diff' => json_encode($diff, JSON_UNESCAPED_UNICODE),
                'mark' => '修改账号',
                'ip' => $request->ip(),
            ]);
        }
        return $resource->additional(['meta' => [
            'message' => '修改成功',
        ]]);
    }

    public function patchPassword(Request $request)
    {
        $user = Auth::getUser();
        $this->validate($request, [
            'password' => 'required',
        ]);

        $data['password'] = Hash::make($request->input('password'));
        $user->fill($data)->save();
        $password = $request->input('password');
        $start = mb_substr($password, 0, 2);
        $end = mb_substr($password, -1, 2);
        $length = mb_strlen($password) - 4;
        $password = $start . str_repeat('*', $length) . $end;

        $resource = new UserResource($user);
        ActionLog::create([
            'user_id' => Auth::user()->id,
            'action_user_id' => Auth::user()->id,
            'module_id' => $request['menu']['id'],
            'diff' => json_encode(['password' => $password], JSON_UNESCAPED_UNICODE),
            'mark' => '修改登录密码',
            'ip' => $request->ip(),
        ]);
        return $resource->additional(['meta' => [
            'message' => '修改成功',
        ]]);
    }

    public function getUser(Request $request)
    {
        $user = new User;
        // DB::enableQueryLog(); // Enable query log
        $createdAt = (array) $request->input('created_at');
        $field = strtolower($request->input('field'));
        $val = $request->input('val');
        if ($field == 'username' && !empty($val)) {
            $user = $user->where('username', $val);
        } else if (!empty($val)) {
            $user = $user->whereHas('userInfo', function ($r) use ($field, $val) {
                $r->encryptWhere($field, $val);
            });
        }
        // dd($user->get());

        $request->input('status') && $user = $user->where('status', $request->input('status'));
        if (!empty($request->input('role'))) {
            $user = $user->whereHas('roles', function ($r) use ($request) {
                return $r->where('role_id', $request->input('role'));
            });
        }
        isset($createdAt[0]) && !empty($createdAt[0]) && $user = $user->where('created_at', '>=', $createdAt[0]);
        isset($createdAt[1]) && !empty($createdAt[1]) && $user = $user->where('created_at', '<=', $createdAt[1]);
        $sort = $request->input('sort');
        if (!empty($sort)) {
            $sortField = trim(str_replace(['*', '-'], '', $sort));
            $sortBy = $sort[0] == '*' ? 'asc' : 'desc';
            $user = $user->orderBy($sortField, $sortBy);
        }
        $pageSize = $request->input('psize', 20);
        $data = $user->paginate($pageSize)->appends($request->query());
        // dd(DB::getQueryLog());
        return UserResource::collection($data);
    }
}
