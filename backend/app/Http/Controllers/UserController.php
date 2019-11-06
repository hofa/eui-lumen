<?php

namespace App\Http\Controllers;

use App\Http\Resources\User as UserResource;
use App\Models\Role;
use App\Models\User;
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
        // $menu = new Menu;
        // if (!in_array(1, $rolesIds)) {
        //     $menu->whereHas('roles', function ($obj) use ($rolesIds) {
        //         $obj->whereIn('id', $rolesIds);
        //     });
        // }

        // $data = $menu->where('status', 'Normal')->get();
        $data = $role->getCacheMenuByRoleId($roleIds)->toArray();
        // dd($data);
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
        // $role = new Role;
        // $role->refreshCache();
        // $data = $role->getCacheMenuByRoleId(2);
        // dd($data->values()->all());
        return ['data' => implode(';', $strs)];
    }

    public function postUser(Request $request)
    {
        $statusKeys = array_keys(config('options.status'));
        $this->validate($request, [
            'username' => 'required|unique:user',
            'password' => 'required',
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
        return (new UserResource($user))->additional(['meta' => [
            'message' => '创建成功',
        ]]);
    }

    public function putUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $this->validate($request, [
            'status' => 'required',
        ]);

        $fillFields = ['status'];
        if (!empty($request->input('password'))) {
            $fillFields[] = 'password';
        }
        $data = $request->only($fillFields);
        $data['password'] = Hash::make($request->input('password'));
        $user->fill($data)->save();
        $user->roles()->detach();
        $roleIds = (array) $request->input('role');
        foreach ($roleIds as $id) {
            $role = Role::find($id);
            $user->roles()->attach($role);
        }
        return (new UserResource($user))->additional(['meta' => [
            'message' => '修改成功',
        ]]);
    }

    public function getUser(Request $request)
    {
        $user = new User;
        $createdAt = (array) $request->input('created_at');
        $request->input('username') && $user = $user->where('username', $request->input('username'));
        $request->input('status') && $user = $user->where('status', $request->input('status'));
        // $request->input('role') && $user = $user->where('role', $request->input('role'));
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
        // $data = $user->simplePaginate($pageSize)->appends($request->query());
        $data = $user->paginate($pageSize)->appends($request->query());
        return UserResource::collection($data);
    }
}
